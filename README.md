# EAIFR website

A full rebuild of https://eaifr.ictp.it/ — the public site of the **East African Institute for Fundamental Research** (UNESCO Category II · Kigali · in partnership with ICTP).

- **`backend/`** — [slatestack](https://github.com/Labyrica/slatestack) CMS: Fastify + Drizzle + Postgres, admin SPA at `/admin`.
- **`web/`** — Next.js 15 public site (App Router, React 19, Tailwind v4). Brochure-faithful design system.
- **`scripts/`** — one-shot tools (scrape the old site, seed content).
- **`assets-source/`** — the print brochure and raw brand assets that drove the visual design.
- **`.planning/`** — the implementation plan (`plan-v1.md`).

## Architecture at a glance

```
┌──────────────┐         ┌──────────────────┐
│ web (Next.js)│ ──RSC──▶│  api (slatestack) │ ──▶  Postgres
│    :3001     │ ◀──────│   :3000 + /admin  │       :5432
└──────────────┘         └──────────────────┘
    public site            content API + admin
```

The web app consumes `GET /api/content/:collection` and `GET /api/content/:collection/:slug` — both unauthenticated — and ISR-caches responses for 60 s. Editorial staff manage content via slatestack's admin SPA, reachable at http://localhost:3000/admin.

## Quick start

Prerequisites: Docker + Docker Compose (recommended) *or* Node ≥ 20 and pnpm ≥ 10.

### Docker (easiest — boots the whole stack)

```bash
cp .env.example .env
#  ↳ fill in BETTER_AUTH_SECRET (openssl rand -hex 32), ADMIN_PASSWORD, METRICS_SALT

docker compose up -d --build
#  ↳ db      (5432)
#  ↳ api     (3000 · admin at /admin)
#  ↳ web     (3001)

# On first boot, seed the EAIFR content model:
docker compose exec api pnpm seed:eaifr
```

Visit:

- http://localhost:3001 — public site
- http://localhost:3000/admin — content admin (log in with `ADMIN_EMAIL` / `ADMIN_PASSWORD`)

### Local dev (hot reload)

```bash
cp .env.example .env
pnpm install            # installs root + workspaces

# terminal 1 — Postgres
docker compose up -d db

# terminal 2 — backend + admin
cd backend && pnpm dev                           # Fastify on :3000
cd backend/admin && pnpm dev                     # admin UI on :5173

# terminal 3 — web
cd web && pnpm dev                               # Next.js on :3001

# One-time content seed
cd backend && pnpm seed:eaifr
```

## Content model

Seeded by `backend/scripts/seed-eaifr.ts`, editable in the slatestack admin:

| Slug | What it holds |
|---|---|
| `pages` | Singleton long-form pages: About, Visit, Apply |
| `news` | Announcements, admissions calls, highlights |
| `events` | Schools, conferences, workshops, colloquia, seminars |
| `research-areas` | The three EAIFR research programmes |
| `people` | Faculty, postdocs, PhD / MSc students, staff |
| `publications` | Peer-reviewed papers |
| `programmes` | Degree programmes (MSc in Physics, …) |
| `partners` | Supporting institutions |

Every collection is accessed from the web app through `web/src/lib/content.ts`, which returns typed view-models parsed via zod. To add a new field, edit the collection definition in `backend/scripts/seed-eaifr.ts`, re-run the seed (idempotent), and update the matching `*DataSchema` in `web/src/lib/content.ts`.

## Scrape the current ICTP site

The first pass of content can be bootstrapped from https://eaifr.ictp.it/ :

```bash
pnpm scrape             # writes scripts/.cache/content/*.json
pnpm seed:from-scrape   # imports into slatestack
```

Both steps are idempotent. The cache is gitignored (derivative content; re-scrape any time).

## Design system

The palette, typography, and section motifs come straight from the print brochure in `assets-source/EAIFR_brochure_latest.html`:

- **Typefaces** — Lora serif (headlines + body) + Lato sans (labels, UI)
- **Palette** — navy `#0B3D5B`, teal `#0D8080`, gold `#D4A843`, cream `#F5F0E6`, forest `#20603D`, rust `#C75C2E`
- **Section rule** — every panel carries a 4 px teal top border (`.panel` in `globals.css`)
- **Eyebrow labels** — uppercase, letter-spaced-wide sans, in one of five tones
- **Gold bar** — 54 × 4 accent under every hero title
- **Dot grids** — small gold / teal motifs in section corners

Tokens live in `web/src/app/globals.css`. Primitives in `web/src/components/brand/`.

## Layout

```
.
├── backend/                       ← slatestack (Fastify + Drizzle + Postgres)
│   ├── src/                       ← API, auth, modules, schema
│   ├── admin/                     ← React admin SPA
│   └── scripts/seed-eaifr.ts      ← creates EAIFR collections + placeholder entries
├── web/                           ← Next.js 15 public site
│   ├── src/app/                   ← routes (App Router)
│   ├── src/components/
│   │   ├── brand/                 ← Eyebrow, GoldBar, DotGrid, Greeting
│   │   ├── content/               ← ResearchCard, EventCard, NewsCard, TagPill, VisionItem, PartnerLogos
│   │   ├── layout/                ← Header, Footer, SectionPanel, MetricsTracker
│   │   ├── page-sections/         ← HomeHero, HomeVision, HomeResearchPreview, HomeEducation, …
│   │   └── ui/                    ← Button, Prose
│   └── src/lib/
│       ├── cms.ts                 ← typed fetch wrapper over slatestack
│       └── content.ts             ← zod-parsed view-models (news, events, people, …)
├── scripts/                       ← scraper + seed-from-scrape
├── assets-source/                 ← original brochure HTML/PDF + brand assets
├── .planning/plan-v1.md           ← approved implementation plan
├── docker-compose.yml             ← full-stack boot
├── .env.example
├── pnpm-workspace.yaml
└── package.json                   ← orchestration scripts
```

## What's out of scope (follow-up)

- **Contact / application forms** — deferred. `/contact` and `/apply` render the designed UI with email fallback; wire to `POST /api/forms/:slug` when ready.
- **i18n (FR / RW)** — the brochure greeting ("Murakaza neza") implies Kinyarwanda support is desirable.
- **Search**, **draft previews** through slatestack's preview-token flow.

## License

Source code: MIT. Content and brand marks: © EAIFR / ICTP / UNESCO — used with permission.
