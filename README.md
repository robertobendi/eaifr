# EAIFR website

A PHP rebuild of [eaifr.ictp.it](https://eaifr.ictp.it/) — the public site of the **East African Institute for Fundamental Research** (UNESCO Category II · Kigali · in partnership with ICTP).

**Live site:** https://robertobendi.github.io/eaifr/

Built on **[Pebblestack](https://github.com/Labyrica/pebblestack)** (PHP 8.2 + SQLite + Twig) — no Node, no MySQL, no Docker. The PHP/SQLite stack is the source of truth for content; GitHub Pages serves a static snapshot built from it on every push to `main`.

## Local development

PHP 8.2+ with the `pdo_sqlite`, `mbstring`, and `json` extensions:

```bash
php -S localhost:8000          # in the repo root
# Visit http://localhost:8000/install.php   to set up the DB.
# Then    http://localhost:8000/seed-eaifr.php   to load placeholder content.
# Public: http://localhost:8000/
# Admin:  http://localhost:8000/admin
```

The repo ships `vendor/`, so `composer install` is only needed if you change `composer.json`.

## Deploy

- **GitHub Pages** (live) — `.github/workflows/pages.yml` runs `bin/install-ci.php` (fresh DB + seed) then `bin/export.php` (crawl → static `dist/`) and publishes via GitHub Actions. Enable it once under **Settings → Pages → Source: GitHub Actions**. Read-only: the contact form renders as a `mailto:` block, and `/admin` isn't exported. Edit content locally, update the seed, and push to rebuild.
- **Hostinger / any PHP 8.2+ shared host** — upload the whole tree (including `vendor/`) to `public_html/`, visit `/install.php`, then `/seed-eaifr.php`, then sign in at `/admin`. Full admin + live contact form.

## Layout

```
index.php / install.php / seed-eaifr.php  ← entrypoints
config/collections.php                    ← content model (source of truth)
templates/theme/eaifr/                     ← the public theme (Lora + Lato, brochure palette)
templates/admin/  src/  vendor/            ← Pebblestack runtime (don't edit)
data/pebblestack.sqlite                    ← single-file database
uploads/                                   ← media + brand assets
bin/                                       ← install-ci.php + export.php (static build)
```

## Content model

Defined in `config/collections.php`; the admin auto-generates CRUD from it.

| Slug             | What it holds                          | Public route                     |
|------------------|----------------------------------------|----------------------------------|
| `pages`          | About, Visit, Apply, Contact           | `/{slug}`                        |
| `news`           | Announcements, admissions calls        | `/news` · `/news/{slug}`         |
| `events`         | Schools, conferences, seminars         | `/events` · `/events/{slug}`     |
| `research_areas` | Research programmes                    | `/research` · `/research/{slug}` |
| `people`         | Faculty, postdocs, students, staff     | `/people` · `/people/{slug}`     |
| `publications`   | Peer-reviewed papers                   | `/publications` · `/publications/{slug}` |
| `programmes`     | Degree programmes (MSc in Physics…)    | `/academics` · `/academics/{slug}` |
| `partners`       | Supporting institutions (logo strips)  | (no public page)                 |
| `contact`        | Contact form submission endpoint       | `POST /forms/contact`            |

The theme is lifted from the print brochure (`assets-source/EAIFR_brochure_latest.html`) — Lora + Lato, navy/teal/gold/cream palette, defined as CSS tokens in `templates/theme/eaifr/layout.twig`.

## License

Source code: MIT. Content and brand marks: © EAIFR / ICTP / UNESCO — used with permission.
