# EAIFR website

A PHP rebuild of [eaifr.ictp.it](https://eaifr.ictp.it/) — the public site of the **East African Institute for Fundamental Research** (UNESCO Category II · Kigali · in partnership with ICTP).

Built on **[Pebblestack](https://github.com/Labyrica/pebblestack)** (PHP 8.2 + SQLite + Twig) so the site drops onto Hostinger or any cheap shared PHP host with **no Node, no MySQL, no Docker**.

```
.
├── index.php              ← front controller
├── install.php            ← first-run installer (visit /install.php after upload)
├── seed-eaifr.php         ← one-shot placeholder content seeder
├── .htaccess              ← rewrites + security
├── config/
│   ├── app.php            ← app name, active theme
│   └── collections.php    ← EAIFR's 8 content collections + contact form
├── templates/
│   ├── admin/             ← Pebblestack admin UI (don't edit)
│   └── theme/eaifr/       ← the EAIFR public-facing theme (Lora + Lato, brochure palette)
├── src/                   ← Pebblestack runtime (don't edit)
├── data/
│   ├── pebblestack.sqlite ← single-file database (created on install)
│   └── migrations/        ← additive schema migrations
├── uploads/               ← media library + committed brand assets (img/, logos/, svg/)
├── vendor/                ← Composer deps (shipped — drop-in deployable)
├── assets-source/         ← original brochure HTML/PDF + brand assets (reference only)
└── .planning/             ← original implementation plan (historical reference)
```

## Deploy to Hostinger (or any PHP 8.2+ shared host)

1. Push this repo to your host via FTP / Hostinger File Manager / `git clone`. The whole tree goes into `public_html/` (yes, including `vendor/` — `composer install` is not required on the host).
2. Visit `https://yourdomain.com/install.php`. Pick a site name, admin email, password.
3. Visit `https://yourdomain.com/seed-eaifr.php?token=<your-token>` to seed the placeholder EAIFR content (research areas, MSc programme, partners, sample news, About / Visit / Apply / Contact pages). Set `EAIFR_SEED_TOKEN` in your environment first, or edit the default value at the top of `seed-eaifr.php`. **Delete the file after running it.**
4. Sign in at `/admin` and start editing.

Public site lives at `/`. Admin lives at `/admin`. The contact form posts to `/forms/contact` and submissions are visible in the admin under "Contact".

## Deploy to GitHub Pages

GH Pages can't run PHP, so the repo also ships a static-export pipeline that snapshots every public page to plain HTML and lets GitHub Pages serve it. The PHP/SQLite stack stays the source of truth for content; the export is just a build artifact.

1. In the repo on GitHub, go to **Settings → Pages** and set **Source** to **GitHub Actions**.
2. Push to `main`. The `.github/workflows/pages.yml` workflow installs PHP, runs `bin/install-ci.php` (fresh DB + seed), runs `bin/export.php` (crawl → `dist/`), and deploys `dist/` via `actions/deploy-pages`.
3. The published URL is `https://<owner>.github.io/<repo>/`. The workflow auto-derives the URL prefix (`EAIFR_STATIC_BASE_PATH=/<repo>`) so links work under the sub-path; for a custom domain at the root, set `EAIFR_STATIC_BASE_PATH=""` and add a `CNAME` file under `dist/`.

What changes vs the Hostinger deploy:

- **No admin.** GH Pages is read-only. Edit content locally (or on Hostinger), commit a fresh seed to `seed-eaifr.php`, and the next CI run rebuilds the site. Long-term, switch to a richer seed source if needed.
- **Contact form → mailto.** When `EAIFR_STATIC_EXPORT=1` is set, the contact page renders a styled mailto block (`templates/theme/eaifr/partials/contact-mailto.twig`) instead of the POST form. Hostinger still gets the real form.
- **No `/admin`, no `/install`, no `/forms/contact`.** Those PHP-only routes simply aren't crawled.

To preview the static build locally:

```bash
php bin/install-ci.php           # fresh DB + seed (warning: rewrites data/pebblestack.sqlite)
EAIFR_STATIC_BASE_PATH=/eaifr php bin/export.php

# serve dist/ under the /eaifr sub-path to simulate GH Pages
mkdir -p /tmp/preview/eaifr && cp -r dist/. /tmp/preview/eaifr/
php -S 127.0.0.1:8000 -t /tmp/preview
# open http://127.0.0.1:8000/eaifr/
```

## Local development

PHP 8.2+ with the `pdo_sqlite`, `mbstring`, and `json` extensions:

```bash
php -S localhost:8000          # in the repo root
# Visit http://localhost:8000/install.php to set up the DB.
# Then http://localhost:8000/seed-eaifr.php to load placeholder content.
# Public:  http://localhost:8000/
# Admin:   http://localhost:8000/admin
```

`composer install --no-dev --optimize-autoloader` is only needed if you change `composer.json` (the repo already ships `vendor/`).

## Content model

Defined in `config/collections.php` (the source of truth) and seeded with placeholder entries by `seed-eaifr.php`. The admin auto-generates CRUD UI from this config:

| Slug             | What it holds                                                | Public route                |
|------------------|--------------------------------------------------------------|-----------------------------|
| `pages`          | Long-form pages: About, Visit, Apply, Contact (+ the form)   | `/{slug}`                   |
| `news`           | Announcements, admissions calls, highlights                  | `/news` · `/news/{slug}`    |
| `events`         | Schools, conferences, workshops, colloquia, seminars         | `/events` · `/events/{slug}`|
| `research_areas` | EAIFR's research programmes (Geophysics, Condensed matter…)  | `/research` · `/research/{slug}` |
| `people`         | Faculty, postdocs, PhD/MSc students, staff                   | `/people` · `/people/{slug}`|
| `publications`   | Peer-reviewed papers                                         | `/publications` · `/publications/{slug}` |
| `programmes`     | Degree programmes (MSc in Physics, …)                        | `/academics` · `/academics/{slug}` |
| `partners`       | Supporting institutions (rendered as logo strips)            | (no public page)            |
| `contact`        | Contact form — public submission endpoint                    | `POST /forms/contact`       |

Field types available: `text`, `textarea`, `markdown`, `slug`, `boolean`, `number`, `select` (with `options`), `datetime`, `url`. There's no relation type — cross-collection links (e.g. a person's research area) are stored as a slug string and looked up in the template.

## Theme — `templates/theme/eaifr/`

The visual design is lifted directly from the print brochure in `assets-source/EAIFR_brochure_latest.html`:

- **Typefaces** — Lora serif (headlines + body) + Lato sans (labels, UI). Loaded from Google Fonts in `layout.twig`.
- **Palette** — navy `#0B3D5B`, teal `#0D8080`, gold `#D4A843`, cream `#F5F0E6`, forest `#20603D`, rust `#C75C2E`. Defined as CSS custom properties in `layout.twig`.
- **Section rule** — every panel carries a 4 px teal top border (`.panel`).
- **Eyebrow labels** — uppercase, letter-spaced sans (`.eyebrow`), in five tones.
- **Gold bar** — 54 × 4 accent under hero titles (`.gold-bar`).
- **Dot grids** — small gold / teal motifs in section corners (`.dot-grid`).
- **Grain** — subtle paper texture on large color fields (`.grain` / `.grain-dark`).

Theme files:

| File                              | What it does                                                     |
|-----------------------------------|------------------------------------------------------------------|
| `layout.twig`                     | Base layout, design tokens, header, footer.                      |
| `home.twig`                       | Brochure-faithful homepage: hero + vision + research preview + education + global reach + events/news + partners. Pulls live from `research_areas`, `programmes`, `events`, `news`, `partners`. |
| `page.twig`                       | Generic page (About / Visit / Apply / Contact). Conditionally renders the contact form when slug == `contact`. |
| `news.twig` · `news-list.twig`    | News detail / index.                                             |
| `event.twig` · `event-list.twig`  | Event detail / index.                                            |
| `research.twig` · `research-list.twig` | Research-area detail / index.                              |
| `person.twig` · `people-list.twig`| Person detail / index.                                           |
| `publication.twig` · `publications-list.twig` | Publication detail / index.                          |
| `programme.twig` · `programmes-list.twig` | Programme detail / index.                                |
| `partials/`                       | Shared header, footer, hero, news-card, event-card, contact-form. |
| `404.twig` · `form-result.twig`   | Error and form-submission pages.                                 |

## Twig helpers (added on top of stock Pebblestack)

`templates/theme/eaifr/home.twig` uses two custom Twig functions registered in `src/Core/App.php`:

- `entries('research_areas', 6)` — list published entries from any collection.
- `entry('programmes', 'msc-physics')` — fetch a single published entry by slug.

## What's deferred (follow-up)

- **Form anti-spam** beyond the built-in honeypot + per-IP rate limit (Cloudflare Turnstile would be the natural drop-in).
- **i18n (FR / RW)** — the brochure greeting ("Murakaza neza") implies Kinyarwanda support is desirable.
- **Search** across collections.

## License

Source code: MIT. Content and brand marks: © EAIFR / ICTP / UNESCO — used with permission.
