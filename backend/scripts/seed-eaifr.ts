/**
 * Seed the EAIFR content model into slatestack.
 *
 * Creates the 8 core collections and a handful of placeholder entries so the
 * web app has something to render on first boot. Idempotent: re-running is
 * safe — collections are upserted by slug, entries by (collectionId, slug).
 *
 * Run with: pnpm seed:eaifr    (from repo root)
 *   or:     pnpm exec tsx --env-file=../.env scripts/seed-eaifr.ts
 */

import { eq, and, notInArray } from 'drizzle-orm';
import { nanoid } from 'nanoid';
import { db } from '../src/shared/database/index.js';
import { collection, entry } from '../src/shared/database/schema.js';
import type { FieldDefinition } from '../src/modules/content/content.types.js';

type CollectionDef = {
  name: string;
  slug: string;
  fields: FieldDefinition[];
  isForm?: boolean;
};

const f = {
  string: (name: string, label: string, opts: Partial<FieldDefinition> = {}): FieldDefinition => ({
    id: nanoid(10), name, label, type: 'string', ...opts,
  }),
  text: (name: string, label: string, opts: Partial<FieldDefinition> = {}): FieldDefinition => ({
    id: nanoid(10), name, label, type: 'text', ...opts,
  }),
  rich: (name: string, label: string, opts: Partial<FieldDefinition> = {}): FieldDefinition => ({
    id: nanoid(10), name, label, type: 'rich-text', ...opts,
  }),
  number: (name: string, label: string, opts: Partial<FieldDefinition> = {}): FieldDefinition => ({
    id: nanoid(10), name, label, type: 'number', ...opts,
  }),
  bool: (name: string, label: string, opts: Partial<FieldDefinition> = {}): FieldDefinition => ({
    id: nanoid(10), name, label, type: 'boolean', ...opts,
  }),
  date: (name: string, label: string, opts: Partial<FieldDefinition> = {}): FieldDefinition => ({
    id: nanoid(10), name, label, type: 'date', ...opts,
  }),
  select: (name: string, label: string, options: string[], opts: Partial<FieldDefinition> = {}): FieldDefinition => ({
    id: nanoid(10), name, label, type: 'select', options, ...opts,
  }),
  multiSelect: (name: string, label: string, options: string[], opts: Partial<FieldDefinition> = {}): FieldDefinition => ({
    id: nanoid(10), name, label, type: 'multi-select', options, ...opts,
  }),
  media: (name: string, label: string, opts: Partial<FieldDefinition> = {}): FieldDefinition => ({
    id: nanoid(10), name, label, type: 'media', ...opts,
  }),
  slugFrom: (name: string, label: string, source: string): FieldDefinition => ({
    id: nanoid(10), name, label, type: 'slug', generateFrom: source, unique: true, required: true,
  }),
  ref: (name: string, label: string, target: string, opts: Partial<FieldDefinition> = {}): FieldDefinition => ({
    id: nanoid(10), name, label, type: 'reference', referenceCollection: target, labelField: 'title', ...opts,
  }),
  multiRef: (name: string, label: string, target: string, opts: Partial<FieldDefinition> = {}): FieldDefinition => ({
    id: nanoid(10), name, label, type: 'multi-reference', referenceCollection: target, labelField: 'title', ...opts,
  }),
};

const COLLECTIONS: CollectionDef[] = [
  {
    name: 'Pages',
    slug: 'pages',
    fields: [
      f.string('title', 'Title', { required: true }),
      f.slugFrom('slug', 'Slug', 'title'),
      f.string('eyebrow', 'Eyebrow label'),
      f.text('summary', 'Short summary', { maxLength: 320 }),
      f.rich('body', 'Body'),
      f.media('hero', 'Hero image'),
      f.number('order', 'Nav order', { min: 0 }),
    ],
  },
  {
    name: 'News',
    slug: 'news',
    fields: [
      f.string('title', 'Title', { required: true }),
      f.slugFrom('slug', 'Slug', 'title'),
      f.text('excerpt', 'Excerpt', { maxLength: 320 }),
      f.rich('body', 'Body'),
      f.date('publishedAt', 'Published on', { required: true }),
      f.media('cover', 'Cover image'),
      f.multiSelect('tags', 'Tags', ['research', 'admissions', 'events', 'announcement', 'partnership']),
    ],
  },
  {
    name: 'Events',
    slug: 'events',
    fields: [
      f.string('title', 'Title', { required: true }),
      f.slugFrom('slug', 'Slug', 'title'),
      f.text('summary', 'Summary', { maxLength: 400 }),
      f.rich('body', 'Body'),
      f.date('startDate', 'Start date', { required: true }),
      f.date('endDate', 'End date'),
      f.string('location', 'Location'),
      f.select('kind', 'Type', ['school', 'conference', 'workshop', 'colloquium', 'seminar']),
      f.media('cover', 'Cover image'),
      f.string('registrationUrl', 'Registration URL'),
    ],
  },
  {
    name: 'Research areas',
    slug: 'research-areas',
    fields: [
      f.string('name', 'Name', { required: true }),
      f.slugFrom('slug', 'Slug', 'name'),
      f.string('short', 'Short tagline', { maxLength: 180 }),
      f.rich('overview', 'Overview'),
      f.media('cover', 'Cover image'),
      f.select('accent', 'Accent color', ['teal', 'navy', 'forest', 'rust']),
    ],
  },
  {
    name: 'People',
    slug: 'people',
    fields: [
      f.string('fullName', 'Full name', { required: true }),
      f.slugFrom('slug', 'Slug', 'fullName'),
      f.select('role', 'Role', ['director', 'faculty', 'postdoc', 'phd-student', 'msc-student', 'staff', 'affiliate']),
      f.string('title', 'Title / Position'),
      f.string('affiliation', 'Affiliation'),
      f.string('email', 'Email'),
      f.text('bio', 'Biography'),
      f.media('photo', 'Portrait'),
      f.multiRef('areas', 'Research areas', 'research-areas'),
    ],
  },
  {
    name: 'Publications',
    slug: 'publications',
    fields: [
      f.string('title', 'Title', { required: true }),
      f.slugFrom('slug', 'Slug', 'title'),
      f.string('authors', 'Authors'),
      f.number('year', 'Year', { min: 2015, max: 2100 }),
      f.string('venue', 'Venue'),
      f.string('doi', 'DOI'),
      f.string('url', 'URL'),
      f.ref('area', 'Research area', 'research-areas'),
    ],
  },
  {
    name: 'Programmes',
    slug: 'programmes',
    fields: [
      f.string('name', 'Name', { required: true }),
      f.slugFrom('slug', 'Slug', 'name'),
      f.text('summary', 'Summary', { maxLength: 400 }),
      f.rich('body', 'Body'),
      f.string('duration', 'Duration'),
      f.string('applicationWindow', 'Application window'),
      f.string('applyUrl', 'Application URL'),
      f.media('cover', 'Cover image'),
    ],
  },
  {
    name: 'Partners',
    slug: 'partners',
    fields: [
      f.string('name', 'Name', { required: true }),
      f.slugFrom('slug', 'Slug', 'name'),
      f.media('logo', 'Logo'),
      f.string('url', 'URL'),
      f.select('tier', 'Tier', ['principal', 'supporter', 'funder']),
      f.number('order', 'Display order', { min: 0 }),
    ],
  },
];

// ── placeholder entries ────────────────────────────────────────────────
// Kept minimal — the real content scraper (scripts/scrape-ictp.ts) adds
// more. These ensure the site renders on a fresh DB without scraping.

type SeedEntry = { slug: string; data: Record<string, unknown>; status?: 'draft' | 'published' };

const ENTRIES: Record<string, SeedEntry[]> = {
  'research-areas': [
    {
      slug: 'geophysics',
      data: {
        name: 'Geophysics',
        slug: 'geophysics',
        short: 'Volcanology & Seismology',
        overview:
          "Monitoring East Africa's volcanic and seismic activity through cutting-edge field stations and real-time data analysis. Our team studies the Virunga volcanic chain and the East African Rift system, contributing to hazard assessment and disaster preparedness across the region. Research includes GPS geodesy, InSAR remote sensing, gravimetry, and seismic tomography to understand crustal dynamics and magmatic processes beneath active volcanoes.",
        accent: 'forest',
      },
      status: 'published',
    },
    {
      slug: 'condensed-matter-physics',
      data: {
        name: 'Condensed Matter Physics',
        slug: 'condensed-matter-physics',
        short: 'Materials Discovery & Biophysics',
        overview:
          'We investigate the fundamental properties of matter at different scales via advanced computational methods. Working within a broad collaborative network, we develop and apply state-of-the-art algorithms for electronic structure, computational statistical mechanics, machine learning and data-driven approaches. Applications range from quantum technologies to the discovery of novel materials, to biophysics and drug design.',
        accent: 'teal',
      },
      status: 'published',
    },
  ],
  programmes: [
    {
      slug: 'msc-physics',
      data: {
        name: 'Master of Science in Physics',
        slug: 'msc-physics',
        summary: 'A two-year, English-language master\'s programme at the University of Rwanda, jointly delivered with ICTP. Fully funded scholarships for admitted African students.',
        body: '## Programme\nThe MSc in Physics is a two-year degree programme of the University of Rwanda, with scientific oversight by EAIFR and ICTP.\n\n- **Year 1** — core graduate physics coursework\n- **Year 2** — a research thesis in one of EAIFR\'s three areas\n\n## Scholarships\nFully funded scholarships are available for admitted African students (tuition, stipend, travel, accommodation).\n\n## How to apply\nApplications are accepted annually via the ICTP application portal. Visit the Apply page for the current window.',
        duration: '2 years',
        applicationWindow: 'Opens each spring',
        applyUrl: 'https://www.ictp.it/training/degree-programs',
      },
      status: 'published',
    },
  ],
  pages: [
    {
      slug: 'about',
      data: {
        title: 'About EAIFR',
        slug: 'about',
        eyebrow: 'About us',
        summary: 'Established in 2018, EAIFR is a UNESCO Category II institute for basic sciences in Africa — a partnership of the Government and University of Rwanda with the Abdus Salam International Centre for Theoretical Physics (ICTP).',
        body: '## Mission\nEAIFR was established in 2018 as a young and dynamic international hub for higher education and research in basic sciences in Africa. It operates as a UNESCO Category II institute, in partnership with the Government and University of Rwanda, and with the Abdus Salam International Centre for Theoretical Physics (ICTP).\n\n## Vision\n- Create a community of African scientists in an international network for basic science.\n- Address African challenges in natural hazards, energy, climate, materials and health.\n- Deliver academic excellence — quality over quantity.\n\n## Governance\nEAIFR is hosted at the University of Rwanda in Kigali. Scientific direction is provided jointly with ICTP.',
        order: 1,
      },
      status: 'published',
    },
    {
      slug: 'visit',
      data: {
        title: 'Visit us',
        slug: 'visit',
        eyebrow: 'Getting here',
        summary: 'EAIFR is hosted at the University of Rwanda\'s College of Science and Technology in Kigali. Visitors, students and collaborators are welcome.',
        body: '## Address\nEAIFR — East African Institute for Fundamental Research  \nUniversity of Rwanda · College of Science and Technology  \nAvenue de l\'Armée, Kigali, Rwanda\n\n## Getting here\nKigali International Airport (KGL) is a 20-minute drive from campus. Visa information is available on the Rwanda Directorate General of Immigration and Emigration website. Most visitors are eligible for a visa on arrival.\n\n## Contact\nGeneral inquiries: info@eaifr.org',
        order: 4,
      },
      status: 'published',
    },
    {
      slug: 'apply',
      data: {
        title: 'Apply',
        slug: 'apply',
        eyebrow: 'Admissions',
        summary: 'Applications to the MSc in Physics are accepted annually through the ICTP application portal.',
        body: '## MSc in Physics — How to apply\n\nApplications are submitted via the ICTP application portal. The annual window opens each spring.\n\n### Who can apply\nThe programme is open to citizens of African countries holding a BSc in Physics (or a closely related discipline) with strong academic records.\n\n### What you need\n- Academic transcripts\n- Two letters of recommendation\n- A statement of purpose\n- An up-to-date CV\n\n### Scholarships\nAdmitted applicants receive a fully funded scholarship covering tuition, living stipend, travel and accommodation.\n\nThe application link is on the MSc programme page.',
        order: 3,
      },
      status: 'published',
    },
  ],
  partners: [
    // Principal tier — the composite UNESCO/Gov/UR/ICTP strip is rendered
    // separately from the supporter grid (see HomePartners).
    { slug: 'ictp', data: { name: 'ICTP', slug: 'ictp', url: 'https://www.ictp.it', tier: 'principal', order: 1 }, status: 'published' },
    { slug: 'university-of-rwanda', data: { name: 'University of Rwanda', slug: 'university-of-rwanda', url: 'https://ur.ac.rw', tier: 'principal', order: 2 }, status: 'published' },
    { slug: 'unesco', data: { name: 'UNESCO', slug: 'unesco', url: 'https://www.unesco.org', tier: 'principal', order: 3 }, status: 'published' },
    { slug: 'government-of-rwanda', data: { name: 'Government of Rwanda', slug: 'government-of-rwanda', url: 'https://www.gov.rw', tier: 'principal', order: 4 }, status: 'published' },

    // Supporter tier — rendered as a logo grid under "In collaboration with".
    // logo paths are web-relative (served from web/public/logos/supporters).
    { slug: 'cnr',               data: { name: 'CNR — Consiglio Nazionale delle Ricerche',   slug: 'cnr',               url: 'https://www.cnr.it',              tier: 'supporter', order: 10, logo: '/logos/supporters/cnr.png' },                status: 'published' },
    { slug: 'ingv',              data: { name: 'INGV — Istituto Nazionale di Geofisica',      slug: 'ingv',              url: 'https://www.ingv.it',             tier: 'supporter', order: 11, logo: '/logos/supporters/ingv.png' },               status: 'published' },
    { slug: 'ird',               data: { name: 'IRD — Institut de Recherche pour le Développement', slug: 'ird',        url: 'https://www.ird.fr',              tier: 'supporter', order: 12, logo: '/logos/supporters/ird.png' },                status: 'published' },
    { slug: 'uclouvain',         data: { name: 'UCLouvain',                                   slug: 'uclouvain',         url: 'https://uclouvain.be',            tier: 'supporter', order: 13, logo: '/logos/supporters/uclouvain.png' },          status: 'published' },
    { slug: 'ncst-rwanda',       data: { name: 'NCST Rwanda',                                 slug: 'ncst-rwanda',       url: 'https://www.ncst.gov.rw',         tier: 'supporter', order: 14, logo: '/logos/supporters/ncst-rwanda.png' },        status: 'published' },
    { slug: 'cecam',             data: { name: 'CECAM',                                       slug: 'cecam',             url: 'https://www.cecam.org',           tier: 'supporter', order: 15, logo: '/logos/supporters/cecam.png' },              status: 'published' },
    { slug: 'marvel',            data: { name: 'NCCR MARVEL',                                 slug: 'marvel',            url: 'https://nccr-marvel.ch',          tier: 'supporter', order: 16, logo: '/logos/supporters/marvel.png' },             status: 'published' },
    { slug: 'ambassade-france',  data: { name: 'Ambassade de France au Rwanda',               slug: 'ambassade-france',  url: 'https://rw.ambafrance.org',       tier: 'supporter', order: 17, logo: '/logos/supporters/ambassade-france.png' },   status: 'published' },
    { slug: 'republique-francaise', data: { name: 'République Française',                      slug: 'republique-francaise', url: 'https://www.gouvernement.fr', tier: 'supporter', order: 18, logo: '/logos/supporters/republique-francaise.png' }, status: 'published' },
  ],
  news: [
    {
      slug: 'msc-physics-applications-open-2025',
      data: {
        title: 'MSc in Physics — applications open for the 2025 cohort',
        slug: 'msc-physics-applications-open-2025',
        excerpt: 'Applications for the 2025 intake of the EAIFR Master\'s in Physics are now open. Fully funded scholarships available for African applicants.',
        body: 'The East African Institute for Fundamental Research (EAIFR), in partnership with the University of Rwanda and ICTP, is accepting applications for the 2025 cohort of its MSc programme in Physics. Fully funded scholarships cover tuition, stipend, travel and accommodation.\n\nSee the MSc programme page for details and the application link.',
        publishedAt: '2026-03-01',
        tags: ['admissions', 'announcement'],
      },
      status: 'published',
    },
  ],
};

// ── runner ────────────────────────────────────────────────────────────

async function upsertCollection(def: CollectionDef) {
  const existing = await db.select().from(collection).where(eq(collection.slug, def.slug)).limit(1);
  if (existing.length > 0) {
    await db
      .update(collection)
      .set({
        name: def.name,
        fields: def.fields,
        isForm: def.isForm ?? false,
        updatedAt: new Date(),
      })
      .where(eq(collection.id, existing[0].id));
    return { id: existing[0].id, slug: def.slug, action: 'updated' as const };
  }
  const id = nanoid();
  await db.insert(collection).values({
    id,
    name: def.name,
    slug: def.slug,
    fields: def.fields,
    isForm: def.isForm ?? false,
  });
  return { id, slug: def.slug, action: 'created' as const };
}

/**
 * Collections we manage exhaustively: every entry that isn't in the seed
 * list is deleted on each run, so renamed/removed items don't linger.
 * Collections NOT in this set are treated as "add/update only" — user-
 * authored entries in the admin are preserved.
 */
const EXHAUSTIVE_COLLECTIONS = new Set(['research-areas', 'partners']);

async function pruneEntries(
  collectionSlug: string,
  collectionId: string,
  keepSlugs: string[],
) {
  if (keepSlugs.length === 0) return 0;
  const deleted = await db
    .delete(entry)
    .where(
      and(
        eq(entry.collectionId, collectionId),
        notInArray(entry.slug, keepSlugs),
      ),
    )
    .returning({ slug: entry.slug });
  if (deleted.length > 0) {
    for (const d of deleted) {
      console.log(`  ${collectionSlug}/${d.slug.padEnd(38)} deleted`);
    }
  }
  return deleted.length;
}

async function upsertEntry(collectionId: string, e: SeedEntry) {
  const existing = await db
    .select()
    .from(entry)
    .where(and(eq(entry.collectionId, collectionId), eq(entry.slug, e.slug)))
    .limit(1);

  if (existing.length > 0) {
    await db
      .update(entry)
      .set({
        data: e.data,
        status: e.status ?? 'published',
        updatedAt: new Date(),
      })
      .where(eq(entry.id, existing[0].id));
    return 'updated' as const;
  }

  await db.insert(entry).values({
    id: nanoid(),
    collectionId,
    slug: e.slug,
    data: e.data,
    status: e.status ?? 'published',
    position: 0,
  });
  return 'created' as const;
}

async function main() {
  console.log('\n=== EAIFR seed ===\n');

  const collIdBySlug = new Map<string, string>();

  for (const def of COLLECTIONS) {
    const r = await upsertCollection(def);
    collIdBySlug.set(r.slug, r.id);
    console.log(`  collection ${r.slug.padEnd(20)} ${r.action}`);
  }

  console.log('');

  for (const [slug, entries] of Object.entries(ENTRIES)) {
    const collId = collIdBySlug.get(slug);
    if (!collId) {
      console.warn(`  skip entries for unknown collection '${slug}'`);
      continue;
    }
    if (EXHAUSTIVE_COLLECTIONS.has(slug)) {
      await pruneEntries(slug, collId, entries.map((e) => e.slug));
    }
    for (const e of entries) {
      const a = await upsertEntry(collId, e);
      console.log(`  ${slug}/${e.slug.padEnd(38)} ${a}`);
    }
  }

  console.log('\n✓ seed complete\n');
  process.exit(0);
}

main().catch((err) => {
  console.error('✗ seed failed:', err);
  process.exit(1);
});
