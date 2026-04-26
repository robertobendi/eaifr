/**
 * One-shot scraper for the current EAIFR site at https://eaifr.ictp.it/.
 *
 * Pulls a handful of known pages, normalizes them, and writes
 * scripts/.cache/content/<collection>.json — which seed-from-scrape.ts
 * then pushes into slatestack. The cache is gitignored.
 *
 * Politeness: 1 request per second (adjustable via THROTTLE_MS). Respects
 * `robots.txt` conventions by not following off-domain links and by
 * sending a descriptive User-Agent.
 *
 * Run: `pnpm scrape` (from repo root).
 */

import fs from 'node:fs/promises';
import path from 'node:path';
import TurndownService from 'turndown';

const ORIGIN = 'https://eaifr.ictp.it';
const UA = 'EAIFR-site-rebuild/1.0 (+https://github.com/Labyrica/slatestack)';
const THROTTLE_MS = 1000;
const CACHE_DIR = path.resolve(process.cwd(), '.cache');
const CONTENT_DIR = path.join(CACHE_DIR, 'content');

const turndown = new TurndownService({ headingStyle: 'atx', codeBlockStyle: 'fenced' });
// Strip <script>, <style>, <nav>, <footer> from rich text.
turndown.remove(['script', 'style', 'nav', 'footer']);

// ── tiny HTML utilities (no jsdom dep) ────────────────────────────────

function pickSection(html: string, ...selectors: string[]): string {
  for (const s of selectors) {
    const match = html.match(new RegExp(`<${s}[^>]*>([\\s\\S]*?)</${s}>`, 'i'));
    if (match) return match[1];
  }
  return '';
}

function innerHtml(html: string, tag: string): string | null {
  const m = html.match(new RegExp(`<${tag}[^>]*>([\\s\\S]*?)</${tag}>`, 'i'));
  return m ? m[1] : null;
}

function textOnly(html: string): string {
  return html
    .replace(/<[^>]+>/g, ' ')
    .replace(/&nbsp;/g, ' ')
    .replace(/&amp;/g, '&')
    .replace(/&quot;/g, '"')
    .replace(/&#39;/g, "'")
    .replace(/\s+/g, ' ')
    .trim();
}

function slugify(s: string): string {
  return s
    .toLowerCase()
    .normalize('NFKD')
    .replace(/[̀-ͯ]/g, '')
    .replace(/[^\w\s-]/g, '')
    .trim()
    .replace(/\s+/g, '-')
    .slice(0, 80);
}

const sleep = (ms: number) => new Promise((r) => setTimeout(r, ms));

async function fetchHtml(url: string): Promise<string | null> {
  try {
    const res = await fetch(url, { headers: { 'User-Agent': UA, Accept: 'text/html' } });
    if (!res.ok) {
      console.warn(`  ! ${res.status} ${url}`);
      return null;
    }
    return await res.text();
  } catch (err) {
    console.warn(`  ! fetch error ${url}: ${(err as Error).message}`);
    return null;
  }
}

// ── page-type extractors ──────────────────────────────────────────────

function extractArticle(html: string): { title: string; body: string; summary: string } {
  // ICTP's Drupal theme wraps page content in <main>, usually with an h1.
  const main = pickSection(html, 'main', 'article');
  const titleHtml = innerHtml(main, 'h1') || innerHtml(html, 'title') || '';
  const title = textOnly(titleHtml);
  const bodyHtml = main.replace(new RegExp(`<h1[^>]*>[\\s\\S]*?</h1>`, 'i'), '');
  const body = turndown.turndown(bodyHtml).trim();
  // First 280 chars of the body as the summary, trimmed to sentence boundary.
  const flat = textOnly(bodyHtml).slice(0, 280);
  const cutoff = flat.lastIndexOf('.');
  const summary = cutoff > 120 ? flat.slice(0, cutoff + 1) : flat;
  return { title, body, summary };
}

// ── scrape targets ────────────────────────────────────────────────────

type ScrapeTarget =
  | { kind: 'page'; slug: string; url: string; eyebrow?: string; order?: number }
  | { kind: 'research-area'; slug: string; url: string; accent: 'teal' | 'navy' | 'forest'; short: string }
  | { kind: 'programme'; slug: string; url: string; duration?: string; applicationWindow?: string; applyUrl?: string };

const TARGETS: ScrapeTarget[] = [
  { kind: 'page', slug: 'about',  url: `${ORIGIN}/about-us`,   eyebrow: 'About us',      order: 1 },
  { kind: 'page', slug: 'visit',  url: `${ORIGIN}/visit-us`,    eyebrow: 'Getting here',  order: 4 },
  { kind: 'research-area', slug: 'condensed-matter-physics',                url: `${ORIGIN}/research/condensed-matter-physics`, accent: 'teal',   short: 'Electronic, magnetic and structural properties of quantum materials.' },
  { kind: 'research-area', slug: 'physics-of-the-solid-earth',              url: `${ORIGIN}/research/physics-solid-earth`,      accent: 'forest', short: 'Seismology, geodynamics and natural-hazard research in the East African Rift.' },
  { kind: 'research-area', slug: 'high-energy-cosmology-astroparticle-physics', url: `${ORIGIN}/research/high-energy-cosmology-and-astroparticle-physics`, accent: 'navy', short: 'Frontier theory at the interface of particle physics, cosmology and gravity.' },
  { kind: 'programme', slug: 'msc-physics', url: `${ORIGIN}/degree-programmes`, duration: '2 years', applicationWindow: 'Opens each spring', applyUrl: 'https://www.ictp.it/training/degree-programs' },
];

// ── main ──────────────────────────────────────────────────────────────

type Bucket = { slug: string; data: Record<string, unknown>; status: 'published' };

const buckets: Record<string, Bucket[]> = {
  pages: [],
  'research-areas': [],
  programmes: [],
  news: [],
  events: [],
};

async function main() {
  await fs.mkdir(CONTENT_DIR, { recursive: true });
  console.log(`\n=== scraping ${ORIGIN} ===\n`);

  for (const target of TARGETS) {
    console.log(`→ ${target.kind} · ${target.slug}`);
    const html = await fetchHtml(target.url);
    await sleep(THROTTLE_MS);
    if (!html) continue;

    const a = extractArticle(html);

    switch (target.kind) {
      case 'page':
        buckets.pages.push({
          slug: target.slug,
          status: 'published',
          data: {
            title: a.title || target.slug,
            slug: target.slug,
            eyebrow: target.eyebrow ?? '',
            summary: a.summary,
            body: a.body,
            order: target.order ?? 0,
          },
        });
        break;
      case 'research-area':
        buckets['research-areas'].push({
          slug: target.slug,
          status: 'published',
          data: {
            name: a.title || target.slug,
            slug: target.slug,
            short: target.short,
            overview: a.body,
            accent: target.accent,
          },
        });
        break;
      case 'programme':
        buckets.programmes.push({
          slug: target.slug,
          status: 'published',
          data: {
            name: a.title || 'MSc in Physics',
            slug: target.slug,
            summary: a.summary,
            body: a.body,
            duration: target.duration ?? '',
            applicationWindow: target.applicationWindow ?? '',
            applyUrl: target.applyUrl ?? '',
          },
        });
        break;
    }
  }

  // News: the homepage carries a short news list. We pull the main page and
  // extract the News/Highlights block if it exists. Kept simple — a more
  // faithful scraper would page through /news.
  const home = await fetchHtml(ORIGIN);
  await sleep(THROTTLE_MS);
  if (home) {
    // Heuristic: find <a ...>...</a> inside a section containing "News".
    const newsSection = home.match(/News[\s\S]{0,200}?<ul[^>]*>([\s\S]*?)<\/ul>/i);
    if (newsSection) {
      const items = [...newsSection[1].matchAll(/<a[^>]+href="([^"]+)"[^>]*>([\s\S]*?)<\/a>/gi)];
      for (const [, href, titleHtml] of items.slice(0, 8)) {
        const title = textOnly(titleHtml);
        if (!title) continue;
        const slug = slugify(title);
        buckets.news.push({
          slug,
          status: 'published',
          data: {
            title,
            slug,
            excerpt: '',
            body: `Originally posted at [eaifr.ictp.it](${href.startsWith('http') ? href : ORIGIN + href}).`,
            publishedAt: new Date().toISOString().slice(0, 10),
            tags: ['announcement'],
          },
        });
      }
    }
  }

  // Write out
  for (const [collection, entries] of Object.entries(buckets)) {
    if (entries.length === 0) continue;
    const file = path.join(CONTENT_DIR, `${collection}.json`);
    await fs.writeFile(file, JSON.stringify(entries, null, 2) + '\n', 'utf8');
    console.log(`  wrote ${entries.length.toString().padStart(3)}  ${path.relative(process.cwd(), file)}`);
  }

  console.log('\n✓ scrape complete\n');
}

main().catch((err) => {
  console.error('✗ scrape failed:', err);
  process.exit(1);
});
