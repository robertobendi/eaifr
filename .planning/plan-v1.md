# EAIFR Website Rebuild — Plan

## Context

The current site at https://eaifr.ictp.it/ needs a full rebuild. The user has:

- A **new visual identity** designed and captured in a physical brochure (`EAFIR/EAIFR_brochure_latest.html`) — Lora serif + Lato sans, teal/navy/gold/forest palette, uppercase tracked labels, gold accent bar, dot motifs, Virunga mountain imagery.
- A **self-hosted headless CMS** of their choice — [slatestack](https://github.com/Labyrica/slatestack.git): Fastify + Drizzle + Postgres + React admin, exposing `GET /api/content/:collection[/:slug]` publicly and a forms/media/metrics system.
- A bag of **assets** already in `EAFIR/` (logos, Virunga imagery, calibration maps, thesis templates, a collaboration SVG).

**Goal of this plan**: turn this repo (currently just the `EAFIR/` asset folder and a fresh git history) into a well-organized monorepo that contains the slatestack backend, a new Next.js 15 public site faithful to the brochure design, seeded with real content scraped from the current ICTP site, and a single `docker compose up` that brings the whole stack online. Forms are stubbed as static pages; they can be wired to slatestack's form API in a follow-up.

## Outcome

After execution:

```
/Users/Roberto/Repos/eaifr/
├── README.md                    ← monorepo overview + how to run
├── docker-compose.yml           ← root stack: db + api + web
├── .env.example                 ← shared env template
├── .gitignore
├── backend/                     ← slatestack (already cloned)
│   └── ... (mostly untouched; add seed + CORS origin)
├── web/                         ← NEW: Next.js 15 App Router public site
│   ├── src/app/...
│   ├── src/components/...
│   ├── src/lib/...
│   ├── public/
│   ├── tailwind.config.ts
│   └── package.json
├── scripts/                     ← NEW: one-shot tools
│   ├── scrape-ictp.ts           ← pulls current eaifr.ictp.it content
│   └── seed-from-scrape.ts      ← pushes scraped content into slatestack
├── assets-source/               ← renamed from EAFIR/ (brand + brochure artifacts, raw)
└── .planning/plan-v1.md         ← archived copy of this plan
```

Notes:

- `EAFIR/` is renamed to `assets-source/` to signal it's the raw source pool, not the app itself. The brochure HTML/PDFs stay inside as design reference.
- `backend/` keeps its own git history (the clone's `.git` is **removed** so this monorepo has one history). Version of slatestack at clone time is recorded in `backend/version.json` which already exists.

## Visual design system (locked in, extracted from the brochure)

Tokens — copied verbatim from `EAFIR/EAIFR_brochure_latest.html` :root so the site inherits the exact same palette:

| Token | Value | Use |
|---|---|---|
| `--navy` | `#0B3D5B` | headings on light, contact panel bg |
| `--deep-navy` | `#061E2F` | hero bg, footer |
| `--teal` | `#0D8080` | accents, section top-borders (4px), primary actions |
| `--bright-teal` | `#11AAAA` | inline links |
| `--gold` | `#D4A843` | highlights, bar under hero, italic greeting |
| `--cream` | `#F5F0E6` | card bg, welcome panel |
| `--warm-bg` | `#EFEBE2` | page/section bg |
| `--dark` | `#1B2838` | body headings on light |
| `--mid` | `#4A5568` | body copy |
| `--light` | `#8CA0B3` | subdued copy on dark |
| `--forest` / `--deep-forest` | `#20603D` / `#143D28` | education section gradient |
| `--rust` | `#C75C2E` | conference/events accent tag |

Fonts — Lora (serif) for display + body, Lato (sans) for labels/UI. Loaded via `next/font`.

Reusable components the brochure implies (and the site will ship):

- `SectionLabel` — uppercase 8px letter-spaced-3 eyebrow (the `.lbl` class)
- `GoldBar` — 45×4 rounded gold divider under hero titles
- `DotGrid` — small 3×N or 4×N dot motif for section corners
- `TagPill` — colored-border pill (Research teal / Education navy / Conferences rust / Schools forest / Workshops purple)
- `VisionItem` — teal circle + checkmark + prose
- `Greeting` — italic gold "Murakaza neza" / tagline style
- `MountainWatermark` — low-opacity Virunga backdrop for hero-ish sections
- `ForestPanel` / `CreamPanel` / `NavyPanel` / `WarmPanel` — the four brochure-panel backgrounds
- `DonutStat`, `StatCard` — for events/impact pages
- `PartnerLogos` — the grayscale logo strip

Styling: **Tailwind v4** (CSS-first, aligns well with `@theme` directive and maps cleanly to the CSS variables above). Typography plugin for the prose body. No separate CSS-in-JS library.

## Information architecture (mirrors eaifr.ictp.it + brochure)

Top-level nav: **About · Research · Academics · Events · News · Visit · Publications**

Routes (Next.js App Router):

```
/                           Home (brochure-style hero + vision + research preview + events preview + partners)
/about                      About EAIFR (mission, history, governance)
/research                   Research overview → list of areas
/research/[slug]            Research area detail
  e.g. /research/condensed-matter-physics
       /research/physics-of-the-solid-earth
       /research/high-energy-cosmology-astroparticle-physics
/academics                  Degree programmes overview
/academics/msc-physics      MSc in Physics detail + static "How to apply" page (stub)
/events                     Events index (upcoming + past filterable)
/events/[slug]              Event detail
/news                       News index
/news/[slug]                News detail
/visit                      Visit us (location, contact, travel tips)
/publications               Publications index (filterable by area/year)
/people                     Faculty & staff directory
/people/[slug]              Person detail
/contact                    Contact page (static, email + address; form stubbed)
/apply                      "How to apply" static page linking to ICTP application portal
```

## Backend work (slatestack)

The content model uses slatestack's existing collection system (no core changes). I'll write a seed script that creates these collections via the admin API on first boot:

| Collection slug | Purpose | Key fields |
|---|---|---|
| `pages` | singleton-ish long-form pages (About, Visit, etc.) | `title`, `slug`, `hero`, `body` (rich-text), `order` |
| `news` | news & announcements | `title`, `slug`, `excerpt`, `body`, `publishedAt`, `cover` (media), `tags` (multi-select) |
| `events` | conferences/schools/workshops | `title`, `slug`, `summary`, `body`, `startDate`, `endDate`, `location`, `type` (select: school/conference/workshop/colloquium), `cover`, `registrationUrl` |
| `research-areas` | the 3 areas | `name`, `slug`, `short` (string), `overview` (rich-text), `cover`, `color` (select: teal/navy/forest), `leads` (multi-reference → people) |
| `people` | faculty, staff, students | `fullName`, `slug`, `role` (select), `affiliation`, `email`, `bio`, `photo`, `areas` (multi-reference → research-areas) |
| `publications` | papers | `title`, `authors` (string), `year`, `venue`, `doi`, `url`, `area` (reference → research-areas) |
| `programmes` | degree programmes | `name`, `slug`, `summary`, `body`, `duration`, `applicationWindow` |
| `partners` | supporting institutions | `name`, `logo`, `url`, `tier` (select: principal/supporter) |

Changes to `backend/`:

1. **CORS origin** — add `http://localhost:3001` (web dev) and an env-driven prod origin to `backend/src/app.ts` line 52. Read from `WEB_ORIGIN` env.
2. **Seed script** — new file `backend/scripts/seed-eaifr.ts` that uses the existing admin-API auth flow (or direct DB access via `shared/database`) to upsert the 8 collection schemas + placeholder entries. Hook into `package.json` as `seed:eaifr`.
3. **No schema/model changes** needed — slatestack's generic content model covers everything.

## Content migration (scrape → seed)

Separate from the backend seed, a one-shot `scripts/scrape-ictp.ts` :

1. Crawls eaifr.ictp.it pages listed in the IA above.
2. For each page: extracts title, body HTML → markdown (via `turndown`), images (downloads to `scripts/.cache/media/`), dates, authors.
3. Writes normalized JSON to `scripts/.cache/content/{collection}.json`.

Then `scripts/seed-from-scrape.ts` reads those JSONs and POSTs to slatestack's admin API to create entries. Images uploaded via `/api/media` first, then referenced. Everything is idempotent (uses slug as upsert key).

This is a separate step from `seed:eaifr` so you can re-scrape without re-seeding schemas, and the scrape output is gitignored (it's derivative of a third-party site).

## Frontend work (web/)

**Stack**: Next.js 15 App Router, React 19, TypeScript strict, Tailwind v4, `next/font` (Lora + Lato), `next/image` for all raster images, `@tanstack/react-query` optional (not needed for first pass — RSC fetches are enough), `zod` for API response parsing.

**API client**: tiny wrapper in `web/src/lib/cms.ts` — one function `cms<T>(path, { fields?, where?, sort?, populate?, limit?, offset? })` that builds the slatestack query string, uses `fetch` with Next's `revalidate` (ISR), and parses with zod. No SDK dependency.

**Component library** lives in `web/src/components/`:

```
components/
├── ui/              ← primitives (Button, Link, Prose)
├── brand/           ← SectionLabel, GoldBar, DotGrid, MountainWatermark, Greeting
├── content/         ← TagPill, VisionItem, PartnerLogos, DonutStat, StatCard
├── layout/          ← Header, Footer, NavBar, PageShell, SectionPanel (forest/cream/navy/warm)
└── page-sections/   ← HomeHero, HomeVision, HomeResearchPreview, HomeEventsPreview, ContactPanel
```

**Styling approach**:

- `web/src/app/globals.css` defines the CSS variables (same names as brochure).
- `tailwind.config.ts` extends `theme.colors` / `theme.fontFamily` / `theme.borderRadius` to expose them as classes (`bg-navy`, `text-gold`, `font-serif`, etc.).
- Base rules: all sections have `border-t-4 border-teal` as per the brochure.

**Assets**: `assets-source/` → copied/resized into `web/public/` during a simple `pnpm assets` step (logos, virunga bg, dot maps). Kept out of `web/public/` directly so we don't carry 150MB of raw sources in the served site.

**Metrics**: the `<head>` includes slatestack's metrics pixel (`POST /api/metrics/pageview`) via a small RSC-friendly snippet — gives the privacy-friendly analytics EAIFR already has infrastructure for.

**Forms (stubbed)**: `/contact` and `/apply` pages render the designed UI with a "Form coming soon — contact info@eaifr.org" callout. No POST wiring yet; the component boundary is clean so swapping to `POST /api/forms/:slug` is a localized change later.

## Repository-level work

- `docker-compose.yml` at root — three services (`db`, `api`, `web`), shared network, volumes for uploads + postgres. Re-uses the `backend/docker-compose.yml` db+api definitions and adds the `web` service (Next.js standalone build).
- `.env.example` at root — single source of truth (DB creds, admin creds, Better-Auth secret, `WEB_ORIGIN`, `NEXT_PUBLIC_CMS_URL`).
- `README.md` — "How to run" (docker / local dev), "How to edit content" (admin URL, default creds), "Content model reference", "Design tokens".
- `pnpm-workspace.yaml` — workspace with `backend`, `backend/admin`, `web`.
- Top-level `package.json` with orchestration scripts: `pnpm dev` runs all three; `pnpm seed`; `pnpm scrape`.

## Critical files this plan touches

Create:
- `web/**` (new Next.js app — full tree)
- `scripts/scrape-ictp.ts`
- `scripts/seed-from-scrape.ts`
- `backend/scripts/seed-eaifr.ts`
- `docker-compose.yml` (root)
- `pnpm-workspace.yaml` (root)
- `package.json` (root)
- `README.md` (root)
- `.env.example` (root)
- `.gitignore` (root)

Modify:
- `backend/src/app.ts` line 52 — CORS origins from env

Move / rename:
- `EAFIR/` → `assets-source/` (preserve all contents; it's a rename, not a rewrite)

Leave alone:
- All other slatestack code (this is a data/config integration, not a core modification)

## Verification

**Backend comes up**:
```
docker compose up -d db api
curl -sf http://localhost:3000/health
curl -sf http://localhost:3000/api/content/news | jq .meta
```

**Admin is reachable** at `http://localhost:3000/admin`, logged in with `ADMIN_EMAIL` / `ADMIN_PASSWORD` from `.env`.

**Seed runs clean**:
```
pnpm seed:eaifr             # creates 8 collections
pnpm scrape                 # writes scripts/.cache
pnpm seed:from-scrape       # pushes entries
```

**Frontend builds and renders**:
```
pnpm --filter web build
pnpm --filter web start     # http://localhost:3001
```

Manually verify in a browser:
- Home page shows Virunga hero, vision checklist, gold accent bar, 5 What-We-Do tag pills (Research/Education/Conferences/Schools/Workshops), research preview cards, events preview, partner logos strip.
- `/research/condensed-matter-physics` renders with the same teal top border and a detail layout consistent with the brochure Research panel.
- `/academics/msc-physics` shows the forest-gradient hero (matches the brochure Education panel).
- `/events` shows scraped events; empty state if none.
- `/visit` shows address + Google Maps embed.
- Mobile viewport: layout reflows cleanly (brochure is print; web must be responsive).
- Lighthouse: SEO ≥ 95, Accessibility ≥ 95, Performance ≥ 90 on the home page.

**Integration smoke test** — the frontend fetches from slatestack over the docker network (`api:3000`) in container mode and `localhost:3000` in dev mode — both paths exercised.

## Out of scope (explicit, for a follow-up)

- Wiring `/contact` and `/apply` to `POST /api/forms/:slug` (user deferred this).
- Multi-language (French / Kinyarwanda) — the brochure greeting "Murakaza neza" implies i18n is desirable, but not planned for this pass.
- Search across content.
- A dedicated alumni page.
- Preview-token flow for draft content (slatestack supports it; not wired into the frontend here).

## Open questions / assumptions

- **Assumption**: the `EAFIR` folder contents (logos, map SVGs, Virunga JPGs) are licensed for the site. If not, swap-in is a localized `web/public/` change.
- **Assumption**: scraping eaifr.ictp.it is acceptable (ICTP/EAIFR is the user's own institute). A short User-Agent + 1 req/sec politeness throttle is built into the scraper.
- **Assumption**: `ictp.it` redirection / domain strategy is handled outside this repo. The new site will serve from whatever domain points at it; no hard-coded absolute URLs.
