/**
 * Pushes scraped content (scripts/.cache/content/*.json) into slatestack.
 *
 * Requires:
 *   - the schema collections to exist (run `pnpm seed:eaifr` first)
 *   - DATABASE_URL env var set (resolved from ../backend/.env via tsx --env-file)
 *
 * Idempotent: entries are upserted by (collectionId, slug). Existing
 * entries are overwritten with the scraped data so re-running picks up
 * any edits upstream.
 */

import fs from 'node:fs/promises';
import path from 'node:path';
import postgres from 'postgres';
import { drizzle } from 'drizzle-orm/postgres-js';
import { eq, and } from 'drizzle-orm';
import { pgTable, text, jsonb, boolean, timestamp, integer } from 'drizzle-orm/pg-core';
import { nanoid } from 'nanoid';

// Minimal schema duplication — we only need collection + entry.
// Keeping this file standalone avoids a cross-workspace TS dep.
const collection = pgTable('collection', {
  id: text('id').primaryKey(),
  slug: text('slug').notNull().unique(),
  isForm: boolean('is_form').notNull().default(false),
});

const entry = pgTable('entry', {
  id: text('id').primaryKey(),
  collectionId: text('collectionId').notNull(),
  slug: text('slug').notNull(),
  data: jsonb('data').notNull(),
  status: text('status').notNull().default('draft'),
  position: integer('position').notNull().default(0),
  createdAt: timestamp('createdAt').notNull().defaultNow(),
  updatedAt: timestamp('updatedAt').notNull().defaultNow(),
});

const CONTENT_DIR = path.resolve(process.cwd(), '.cache', 'content');

type SeedEntry = { slug: string; data: Record<string, unknown>; status?: 'draft' | 'published' };

async function main() {
  const url = process.env.DATABASE_URL;
  if (!url) {
    console.error('DATABASE_URL not set. Hint: run from repo root with a .env at backend/.env');
    process.exit(1);
  }

  const sql = postgres(url);
  const db = drizzle(sql);

  console.log('\n=== seed-from-scrape ===\n');

  const files = await fs.readdir(CONTENT_DIR).catch(() => []);
  if (files.length === 0) {
    console.log('No scraped content under scripts/.cache/content/. Run `pnpm scrape` first.');
    await sql.end();
    return;
  }

  let created = 0;
  let updated = 0;

  for (const file of files) {
    if (!file.endsWith('.json')) continue;
    const collSlug = file.replace(/\.json$/, '');

    const [coll] = await db.select().from(collection).where(eq(collection.slug, collSlug)).limit(1);
    if (!coll) {
      console.warn(`  skip ${collSlug}: collection not found (run seed:eaifr first)`);
      continue;
    }

    const raw = await fs.readFile(path.join(CONTENT_DIR, file), 'utf8');
    const entries = JSON.parse(raw) as SeedEntry[];
    console.log(`→ ${collSlug} (${entries.length})`);

    for (const e of entries) {
      const [existing] = await db
        .select()
        .from(entry)
        .where(and(eq(entry.collectionId, coll.id), eq(entry.slug, e.slug)))
        .limit(1);

      if (existing) {
        await db
          .update(entry)
          .set({
            data: e.data,
            status: e.status ?? 'published',
            updatedAt: new Date(),
          })
          .where(eq(entry.id, existing.id));
        updated++;
      } else {
        await db.insert(entry).values({
          id: nanoid(),
          collectionId: coll.id,
          slug: e.slug,
          data: e.data,
          status: e.status ?? 'published',
          position: 0,
        });
        created++;
      }
    }
  }

  console.log(`\n✓ ${created} created, ${updated} updated\n`);
  await sql.end();
}

main().catch((err) => {
  console.error('✗ seed-from-scrape failed:', err);
  process.exit(1);
});
