/**
 * Typed view-models for EAIFR content.
 *
 * Each exported function wraps cmsList/cmsOne, parses the raw `data`
 * bag with a zod schema, and returns a narrow type tailored to how the
 * UI consumes it. Keeping this boundary small means the rest of the
 * app never touches `Record<string, unknown>` directly.
 */

import { z } from 'zod';
import { cmsList, cmsOne, type RawEntry } from './cms';

// ── shared helpers ─────────────────────────────────────────────────────

const MediaRefSchema = z
  .object({
    id: z.string().optional(),
    path: z.string().optional(),
    url: z.string().optional(),
    thumbnailPath: z.string().optional(),
    altText: z.string().nullable().optional(),
    width: z.number().optional(),
    height: z.number().optional(),
  })
  .passthrough();

function firstMediaPath(m: unknown): string | null {
  if (!m) return null;
  if (typeof m === 'string') return m;
  if (typeof m === 'object') {
    const parsed = MediaRefSchema.safeParse(m);
    if (!parsed.success) return null;
    return parsed.data.path ?? parsed.data.url ?? null;
  }
  return null;
}

// ── research areas ─────────────────────────────────────────────────────

const AccentSchema = z.enum(['teal', 'navy', 'forest', 'rust']).catch('teal');

const ResearchAreaDataSchema = z.object({
  name: z.string(),
  slug: z.string(),
  short: z.string().optional().default(''),
  overview: z.string().optional().default(''),
  cover: z.unknown().optional(),
  accent: AccentSchema.optional().default('teal'),
});

export type ResearchArea = {
  slug: string;
  name: string;
  short: string;
  overview: string;
  coverPath: string | null;
  accent: z.infer<typeof AccentSchema>;
};

function toResearchArea(e: RawEntry): ResearchArea {
  const d = ResearchAreaDataSchema.parse(e.data);
  return {
    slug: d.slug || e.slug,
    name: d.name,
    short: d.short,
    overview: d.overview,
    coverPath: firstMediaPath(d.cover),
    accent: d.accent,
  };
}

export async function listResearchAreas(): Promise<ResearchArea[]> {
  const r = await cmsList('research-areas', { sort: 'name', limit: 50 });
  return r.data.map(toResearchArea);
}

export async function getResearchArea(slug: string): Promise<ResearchArea | null> {
  const e = await cmsOne('research-areas', slug);
  return e ? toResearchArea(e) : null;
}

// ── news ───────────────────────────────────────────────────────────────

const NewsDataSchema = z.object({
  title: z.string(),
  slug: z.string(),
  excerpt: z.string().optional().default(''),
  body: z.string().optional().default(''),
  publishedAt: z.string().optional(),
  cover: z.unknown().optional(),
  tags: z.array(z.string()).optional().default([]),
});

export type NewsItem = {
  slug: string;
  title: string;
  excerpt: string;
  body: string;
  publishedAt: string | null;
  coverPath: string | null;
  tags: string[];
};

function toNewsItem(e: RawEntry): NewsItem {
  const d = NewsDataSchema.parse(e.data);
  return {
    slug: d.slug || e.slug,
    title: d.title,
    excerpt: d.excerpt,
    body: d.body,
    publishedAt: d.publishedAt ?? null,
    coverPath: firstMediaPath(d.cover),
    tags: d.tags,
  };
}

export async function listNews(limit = 20): Promise<NewsItem[]> {
  const r = await cmsList('news', { sort: '-publishedAt', limit });
  return r.data.map(toNewsItem);
}

export async function getNewsItem(slug: string): Promise<NewsItem | null> {
  const e = await cmsOne('news', slug);
  return e ? toNewsItem(e) : null;
}

// ── events ─────────────────────────────────────────────────────────────

const EventKindSchema = z
  .enum(['school', 'conference', 'workshop', 'colloquium', 'seminar'])
  .catch('seminar');

const EventDataSchema = z.object({
  title: z.string(),
  slug: z.string(),
  summary: z.string().optional().default(''),
  body: z.string().optional().default(''),
  startDate: z.string().optional(),
  endDate: z.string().optional(),
  location: z.string().optional().default(''),
  kind: EventKindSchema.optional().default('seminar'),
  cover: z.unknown().optional(),
  registrationUrl: z.string().optional().default(''),
});

export type EventItem = {
  slug: string;
  title: string;
  summary: string;
  body: string;
  startDate: string | null;
  endDate: string | null;
  location: string;
  kind: z.infer<typeof EventKindSchema>;
  coverPath: string | null;
  registrationUrl: string;
};

function toEventItem(e: RawEntry): EventItem {
  const d = EventDataSchema.parse(e.data);
  return {
    slug: d.slug || e.slug,
    title: d.title,
    summary: d.summary,
    body: d.body,
    startDate: d.startDate ?? null,
    endDate: d.endDate ?? null,
    location: d.location,
    kind: d.kind,
    coverPath: firstMediaPath(d.cover),
    registrationUrl: d.registrationUrl,
  };
}

export async function listEvents(limit = 50): Promise<EventItem[]> {
  const r = await cmsList('events', { sort: '-startDate', limit });
  return r.data.map(toEventItem);
}

export async function listUpcomingEvents(limit = 6): Promise<EventItem[]> {
  const today = new Date().toISOString().slice(0, 10);
  const r = await cmsList('events', {
    where: { startDate: { gte: today } },
    sort: 'startDate',
    limit,
  });
  return r.data.map(toEventItem);
}

export async function getEvent(slug: string): Promise<EventItem | null> {
  const e = await cmsOne('events', slug);
  return e ? toEventItem(e) : null;
}

// ── pages ──────────────────────────────────────────────────────────────

const PageDataSchema = z.object({
  title: z.string(),
  slug: z.string(),
  eyebrow: z.string().optional().default(''),
  summary: z.string().optional().default(''),
  body: z.string().optional().default(''),
  hero: z.unknown().optional(),
  order: z.number().optional().default(0),
});

export type Page = {
  slug: string;
  title: string;
  eyebrow: string;
  summary: string;
  body: string;
  heroPath: string | null;
  order: number;
};

function toPage(e: RawEntry): Page {
  const d = PageDataSchema.parse(e.data);
  return {
    slug: d.slug || e.slug,
    title: d.title,
    eyebrow: d.eyebrow,
    summary: d.summary,
    body: d.body,
    heroPath: firstMediaPath(d.hero),
    order: d.order,
  };
}

export async function getPage(slug: string): Promise<Page | null> {
  const e = await cmsOne('pages', slug);
  return e ? toPage(e) : null;
}

// ── people ─────────────────────────────────────────────────────────────

const RoleSchema = z
  .enum(['director', 'faculty', 'postdoc', 'phd-student', 'msc-student', 'staff', 'affiliate'])
  .catch('faculty');

const PersonDataSchema = z.object({
  fullName: z.string(),
  slug: z.string(),
  role: RoleSchema.optional().default('faculty'),
  title: z.string().optional().default(''),
  affiliation: z.string().optional().default(''),
  email: z.string().optional().default(''),
  bio: z.string().optional().default(''),
  photo: z.unknown().optional(),
});

export type Person = {
  slug: string;
  fullName: string;
  role: z.infer<typeof RoleSchema>;
  title: string;
  affiliation: string;
  email: string;
  bio: string;
  photoPath: string | null;
};

function toPerson(e: RawEntry): Person {
  const d = PersonDataSchema.parse(e.data);
  return {
    slug: d.slug || e.slug,
    fullName: d.fullName,
    role: d.role,
    title: d.title,
    affiliation: d.affiliation,
    email: d.email,
    bio: d.bio,
    photoPath: firstMediaPath(d.photo),
  };
}

export async function listPeople(): Promise<Person[]> {
  const r = await cmsList('people', { sort: 'fullName', limit: 200 });
  return r.data.map(toPerson);
}

export async function getPerson(slug: string): Promise<Person | null> {
  const e = await cmsOne('people', slug);
  return e ? toPerson(e) : null;
}

// ── programmes ─────────────────────────────────────────────────────────

const ProgrammeDataSchema = z.object({
  name: z.string(),
  slug: z.string(),
  summary: z.string().optional().default(''),
  body: z.string().optional().default(''),
  duration: z.string().optional().default(''),
  applicationWindow: z.string().optional().default(''),
  applyUrl: z.string().optional().default(''),
  cover: z.unknown().optional(),
});

export type Programme = {
  slug: string;
  name: string;
  summary: string;
  body: string;
  duration: string;
  applicationWindow: string;
  applyUrl: string;
  coverPath: string | null;
};

function toProgramme(e: RawEntry): Programme {
  const d = ProgrammeDataSchema.parse(e.data);
  return {
    slug: d.slug || e.slug,
    name: d.name,
    summary: d.summary,
    body: d.body,
    duration: d.duration,
    applicationWindow: d.applicationWindow,
    applyUrl: d.applyUrl,
    coverPath: firstMediaPath(d.cover),
  };
}

export async function listProgrammes(): Promise<Programme[]> {
  const r = await cmsList('programmes', { sort: 'name', limit: 20 });
  return r.data.map(toProgramme);
}

export async function getProgramme(slug: string): Promise<Programme | null> {
  const e = await cmsOne('programmes', slug);
  return e ? toProgramme(e) : null;
}

// ── publications ───────────────────────────────────────────────────────

const PublicationDataSchema = z.object({
  title: z.string(),
  slug: z.string(),
  authors: z.string().optional().default(''),
  year: z.number().optional(),
  venue: z.string().optional().default(''),
  doi: z.string().optional().default(''),
  url: z.string().optional().default(''),
});

export type Publication = {
  slug: string;
  title: string;
  authors: string;
  year: number | null;
  venue: string;
  doi: string;
  url: string;
};

export async function listPublications(): Promise<Publication[]> {
  const r = await cmsList('publications', { sort: '-year,title', limit: 200 });
  return r.data.map((e) => {
    const d = PublicationDataSchema.parse(e.data);
    return {
      slug: d.slug || e.slug,
      title: d.title,
      authors: d.authors,
      year: d.year ?? null,
      venue: d.venue,
      doi: d.doi,
      url: d.url,
    };
  });
}

// ── partners ───────────────────────────────────────────────────────────

const TierSchema = z.enum(['principal', 'supporter', 'funder']).catch('supporter');

const PartnerDataSchema = z.object({
  name: z.string(),
  slug: z.string(),
  logo: z.unknown().optional(),
  url: z.string().optional().default(''),
  tier: TierSchema.optional().default('supporter'),
  order: z.number().optional().default(0),
});

export type Partner = {
  slug: string;
  name: string;
  url: string;
  tier: z.infer<typeof TierSchema>;
  logoPath: string | null;
  order: number;
};

export async function listPartners(): Promise<Partner[]> {
  const r = await cmsList('partners', { sort: 'order,name', limit: 100 });
  return r.data.map((e) => {
    const d = PartnerDataSchema.parse(e.data);
    return {
      slug: d.slug || e.slug,
      name: d.name,
      url: d.url,
      tier: d.tier,
      logoPath: firstMediaPath(d.logo),
      order: d.order,
    };
  });
}
