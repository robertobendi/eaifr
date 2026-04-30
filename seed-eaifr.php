<?php

/**
 * One-shot EAIFR placeholder content seeder. Idempotent — re-running upserts
 * by (collection, slug). Mirrors backend/scripts/seed-eaifr.ts (now removed)
 * minus the slatestack-specific reference fields.
 *
 * Run from the project root *after* /install.php has set up the database:
 *   php seed-eaifr.php
 *
 * Or, on a shared host where SSH/CLI access is unavailable, copy the file
 * into the webroot and visit https://yourdomain.com/seed-eaifr.php?token=<...>
 * (set EAIFR_SEED_TOKEN below or as an environment variable). Delete the
 * file afterwards.
 */

declare(strict_types=1);

if (PHP_SAPI === 'cli-server' || PHP_SAPI === 'apache2handler' || PHP_SAPI === 'fpm-fcgi' || PHP_SAPI === 'cgi-fcgi') {
    // Running over HTTP — require a token to keep this from being exploitable
    // by anyone who happens to land on the URL.
    $expected = getenv('EAIFR_SEED_TOKEN') ?: 'change-me-and-delete-after-running';
    $given    = $_GET['token'] ?? '';
    if (!hash_equals((string) $expected, (string) $given)) {
        http_response_code(403);
        header('Content-Type: text/plain');
        echo "Forbidden. Set EAIFR_SEED_TOKEN and call with ?token=...\n";
        exit;
    }
    header('Content-Type: text/plain; charset=utf-8');
}

require __DIR__ . '/vendor/autoload.php';

use Pebblestack\Core\Database;
use Pebblestack\Services\EntryRepository;

$db   = new Database(__DIR__ . '/data/pebblestack.sqlite');
$repo = new EntryRepository($db);

function upsert(EntryRepository $repo, string $collection, string $slug, array $data, ?int $publishAt = null): string
{
    $existing = $repo->findBySlug($collection, $slug);
    if ($existing === null) {
        $repo->create($collection, $slug, 'published', $data, $publishAt);
        return 'created';
    }
    $repo->update($existing->id, $slug, 'published', $data, $publishAt);
    return 'updated';
}

$entries = [

    // ── Pages ──────────────────────────────────────────────────────────
    ['pages', 'about', [
        'title'   => 'About EAIFR',
        'slug'    => 'about',
        'eyebrow' => 'About us',
        'summary' => 'Established in 2018, EAIFR is a UNESCO Category II institute for basic sciences in Africa — a partnership of the Government and University of Rwanda with the Abdus Salam International Centre for Theoretical Physics (ICTP).',
        'body'    => "## Mission\n\nEAIFR was established in 2018 as a young and dynamic international hub for higher education and research in basic sciences in Africa. It operates as a UNESCO Category II institute, in partnership with the Government and University of Rwanda, and with the Abdus Salam International Centre for Theoretical Physics (ICTP).\n\n## Vision\n\n- Create a community of African scientists in an international network for basic science.\n- Address African challenges in natural hazards, energy, climate, materials and health.\n- Deliver academic excellence — quality over quantity.\n\n## Governance\n\nEAIFR is hosted at the University of Rwanda in Kigali. Scientific direction is provided jointly with ICTP.",
    ]],
    ['pages', 'visit', [
        'title'   => 'Visit us',
        'slug'    => 'visit',
        'eyebrow' => 'Getting here',
        'summary' => 'EAIFR is hosted at the University of Rwanda\'s College of Science and Technology in Kigali. Visitors, students and collaborators are welcome.',
        'body'    => "## Address\n\nEAIFR — East African Institute for Fundamental Research  \nUniversity of Rwanda · College of Science and Technology  \nAvenue de l'Armée, Kigali, Rwanda\n\n## Getting here\n\nKigali International Airport (KGL) is a 20-minute drive from campus. Visa information is available on the Rwanda Directorate General of Immigration and Emigration website. Most visitors are eligible for a visa on arrival.\n\n## Contact\n\nGeneral inquiries: info@eaifr.org",
    ]],
    ['pages', 'apply', [
        'title'   => 'Apply',
        'slug'    => 'apply',
        'eyebrow' => 'Admissions',
        'summary' => 'Applications to the MSc in Physics are accepted annually through the ICTP application portal.',
        'body'    => "## MSc in Physics — How to apply\n\nApplications are submitted via the ICTP application portal. The annual window opens each spring.\n\n### Who can apply\n\nThe programme is open to citizens of African countries holding a BSc in Physics (or a closely related discipline) with strong academic records.\n\n### What you need\n\n- Academic transcripts\n- Two letters of recommendation\n- A statement of purpose\n- An up-to-date CV\n\n### Scholarships\n\nAdmitted applicants receive a fully funded scholarship covering tuition, living stipend, travel and accommodation.",
    ]],
    ['pages', 'contact', [
        'title'   => 'Contact us',
        'slug'    => 'contact',
        'eyebrow' => 'Get in touch',
        'summary' => 'Have a question about admissions, research collaboration, or visiting EAIFR? Send us a note and we\'ll get back to you.',
        'body'    => "Use the form below to contact us. For urgent inquiries, email **info@eaifr.org** directly.",
    ]],

    // ── Research areas ─────────────────────────────────────────────────
    ['research_areas', 'geophysics', [
        'name'     => 'Geophysics',
        'slug'     => 'geophysics',
        'short'    => 'Volcanology & Seismology',
        'overview' => "Monitoring East Africa's volcanic and seismic activity through cutting-edge field stations and real-time data analysis. Our team studies the Virunga volcanic chain and the East African Rift system, contributing to hazard assessment and disaster preparedness across the region.\n\nResearch includes GPS geodesy, InSAR remote sensing, gravimetry, and seismic tomography to understand crustal dynamics and magmatic processes beneath active volcanoes.",
        'accent'   => 'forest',
    ]],
    ['research_areas', 'condensed-matter-physics', [
        'name'     => 'Condensed Matter Physics',
        'slug'     => 'condensed-matter-physics',
        'short'    => 'Materials Discovery & Biophysics',
        'overview' => "We investigate the fundamental properties of matter at different scales via advanced computational methods. Working within a broad collaborative network, we develop and apply state-of-the-art algorithms for electronic structure, computational statistical mechanics, machine learning and data-driven approaches.\n\nApplications range from quantum technologies to the discovery of novel materials, to biophysics and drug design.",
        'accent'   => 'teal',
    ]],

    // ── Programmes ─────────────────────────────────────────────────────
    ['programmes', 'msc-physics', [
        'name'                => 'Master of Science in Physics',
        'slug'                => 'msc-physics',
        'summary'             => 'A two-year, English-language master\'s programme at the University of Rwanda, jointly delivered with ICTP. Fully funded scholarships for admitted African students.',
        'body'                => "## Programme\n\nThe MSc in Physics is a two-year degree programme of the University of Rwanda, with scientific oversight by EAIFR and ICTP.\n\n- **Year 1** — core graduate physics coursework\n- **Year 2** — a research thesis in one of EAIFR's research areas\n\n## Scholarships\n\nFully funded scholarships are available for admitted African students (tuition, stipend, travel, accommodation).\n\n## How to apply\n\nApplications are accepted annually via the ICTP application portal. Visit the Apply page for the current window.",
        'duration'            => '2 years',
        'application_window'  => 'Opens each spring',
        'apply_url'           => 'https://www.ictp.it/training/degree-programs',
    ]],

    // ── Partners ───────────────────────────────────────────────────────
    ['partners', 'ictp',                  ['name' => 'ICTP',                                          'slug' => 'ictp',                  'url' => 'https://www.ictp.it',         'tier' => 'principal',  'order' => 1]],
    ['partners', 'university-of-rwanda',  ['name' => 'University of Rwanda',                          'slug' => 'university-of-rwanda',  'url' => 'https://ur.ac.rw',            'tier' => 'principal',  'order' => 2]],
    ['partners', 'unesco',                ['name' => 'UNESCO',                                        'slug' => 'unesco',                'url' => 'https://www.unesco.org',      'tier' => 'principal',  'order' => 3]],
    ['partners', 'government-of-rwanda',  ['name' => 'Government of Rwanda',                          'slug' => 'government-of-rwanda',  'url' => 'https://www.gov.rw',          'tier' => 'principal',  'order' => 4]],
    ['partners', 'cnr',                   ['name' => 'CNR — Consiglio Nazionale delle Ricerche',       'slug' => 'cnr',                   'url' => 'https://www.cnr.it',          'tier' => 'supporter', 'order' => 10, 'logo' => '/uploads/logos/supporters/cnr.png']],
    ['partners', 'ingv',                  ['name' => 'INGV — Istituto Nazionale di Geofisica',         'slug' => 'ingv',                  'url' => 'https://www.ingv.it',         'tier' => 'supporter', 'order' => 11, 'logo' => '/uploads/logos/supporters/ingv.png']],
    ['partners', 'ird',                   ['name' => 'IRD — Institut de Recherche pour le Développement','slug' => 'ird',                  'url' => 'https://www.ird.fr',          'tier' => 'supporter', 'order' => 12, 'logo' => '/uploads/logos/supporters/ird.png']],
    ['partners', 'uclouvain',             ['name' => 'UCLouvain',                                     'slug' => 'uclouvain',             'url' => 'https://uclouvain.be',        'tier' => 'supporter', 'order' => 13, 'logo' => '/uploads/logos/supporters/uclouvain.png']],
    ['partners', 'ncst-rwanda',           ['name' => 'NCST Rwanda',                                   'slug' => 'ncst-rwanda',           'url' => 'https://www.ncst.gov.rw',     'tier' => 'supporter', 'order' => 14, 'logo' => '/uploads/logos/supporters/ncst-rwanda.png']],
    ['partners', 'cecam',                 ['name' => 'CECAM',                                         'slug' => 'cecam',                 'url' => 'https://www.cecam.org',       'tier' => 'supporter', 'order' => 15, 'logo' => '/uploads/logos/supporters/cecam.png']],
    ['partners', 'marvel',                ['name' => 'NCCR MARVEL',                                   'slug' => 'marvel',                'url' => 'https://nccr-marvel.ch',      'tier' => 'supporter', 'order' => 16, 'logo' => '/uploads/logos/supporters/marvel.png']],
    ['partners', 'ambassade-france',      ['name' => 'Ambassade de France au Rwanda',                  'slug' => 'ambassade-france',      'url' => 'https://rw.ambafrance.org',   'tier' => 'supporter', 'order' => 17, 'logo' => '/uploads/logos/supporters/ambassade-france.png']],
    ['partners', 'republique-francaise',  ['name' => 'République Française',                           'slug' => 'republique-francaise',  'url' => 'https://www.gouvernement.fr', 'tier' => 'supporter', 'order' => 18, 'logo' => '/uploads/logos/supporters/republique-francaise.png']],

    // ── News (one placeholder so the homepage card has something to render) ──
    ['news', 'msc-physics-applications-open-2025', [
        'title'        => 'MSc in Physics — applications open for the 2025 cohort',
        'slug'         => 'msc-physics-applications-open-2025',
        'excerpt'      => 'Applications for the 2025 intake of the EAIFR Master\'s in Physics are now open. Fully funded scholarships available for African applicants.',
        'body'         => "The East African Institute for Fundamental Research (EAIFR), in partnership with the University of Rwanda and ICTP, is accepting applications for the 2025 cohort of its MSc programme in Physics. Fully funded scholarships cover tuition, stipend, travel and accommodation.\n\nSee the MSc programme page for details and the application link.",
        'published_at' => strtotime('2026-03-01'),
        'tag'          => 'admissions',
    ], strtotime('2026-03-01')],
];

echo "=== EAIFR seed ===\n";
foreach ($entries as $row) {
    [$collection, $slug, $data] = [$row[0], $row[1], $row[2]];
    $publishAt = $row[3] ?? null;
    try {
        $action = upsert($repo, $collection, $slug, $data, $publishAt);
        echo "  {$collection}/{$slug} → {$action}\n";
    } catch (\Throwable $e) {
        echo "  {$collection}/{$slug} → FAILED: " . $e->getMessage() . "\n";
    }
}
echo "✓ seed complete\n";
