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
        'summary' => 'EAIFR offers MSc and PhD physics programmes at the University of Rwanda, with scientific oversight by ICTP.',
        'body'    => "## MSc in Physics\n\nThe MSc is a two-year programme. Applicants should hold, or be completing, a BSc in physics or a closely related field and be fluent in English. Applications are submitted through the [ICTP application portal](https://www.ictp.it/training/degree-programs).\n\n## PhD in Physics\n\nThe research-led PhD has a maximum duration of four years and offers specialisation in High Energy Physics, Condensed Matter Physics, or Solid Earth Geophysics.\n\n### PhD eligibility\n\n- A bachelor's degree with honours in physics, mathematics, or a related field, with a cumulative average of at least 70%.\n- A master's degree in physics, mathematics, or a related field.\n- Selection is based on the university record and academic recommendations.\n\n### PhD application documents\n\n- Degree certificates and academic transcripts in English\n- Evidence of English-language proficiency\n- Contact details for two referees\n- An up-to-date CV and ID or passport copy\n- A proposal for the intended PhD research\n\nApplicants are encouraged to contact EAIFR scientific staff about shared research interests. Send PhD applications to [admissions@eaifr.org](mailto:admissions@eaifr.org), copying [admissions.pgs@ur.ac.rw](mailto:admissions.pgs@ur.ac.rw). Application and selection dates are to be determined.",
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
    ['programmes', 'phd-physics', [
        'name'                => 'Doctor of Philosophy in Physics',
        'slug'                => 'phd-physics',
        'summary'             => 'A research-led PhD in physics with specialisations in high energy physics, condensed matter physics, and solid earth geophysics.',
        'body'                => "## PhD Programme\n\nThe PhD programme at EAIFR is in physics, with research specialization in one of the following broad areas:\n\n1. High Energy Physics\n2. Condensed Matter Physics\n3. Solid Earth Geophysics\n\nAdmitted students will be taught and supervised by EAIFR resident scientists and co-supervised by scientists at ICTP Trieste and possibly others around the world based upon mutual interests and the research proposal of the students.\n\n## Duration of the PhD Programme\n\nMaximum time duration of four years.\n\n## Mode of attendance\n\nThe PhD programme is completed through in-depth research that breaks new ground in the field and contributes to overall scientific knowledge. Research publications are expected from students as well as a final dissertation thesis before graduation.\n\n## Key dates\n\n- PhD application deadline: To be determined\n- Deadline for notification of selection: To be determined\n- Registration and commencement of research: To be determined\n\n## Minimum qualifications\n\n- Bachelor's degree with honours in physics, mathematics, or a related field, with distinction (cumulative grade point average of at least 70%).\n- Master's degree in physics, mathematics, or a related field.\n- Selection of candidates will be based on their university record and on academic recommendations.\n\n## Required documents\n\n- Copies of degree, academic transcripts and certificates (in English).\n- Documents that show that the students can follow instructions in the English language.\n- Contact details of two referees who can make comprehensive comments about the applicant's abilities, past studies, and work.\n- Updated CV and ID or Passport copy\n- A proposal of the intended PhD research\n\n## How to apply\n\nSend an email with the documents mentioned above to [admissions@eaifr.org](mailto:admissions@eaifr.org) with a copy to [admissions.pgs@ur.ac.rw](mailto:admissions.pgs@ur.ac.rw).",
        'duration'            => 'Up to 4 years',
        'application_window'  => 'Dates to be determined',
        'apply_url'           => 'https://eaifr.ictp.it/degree-programmes/phd-programme/',
    ]],

    // ── People ────────────────────────────────────────────────────────
    ['people', 'edward-ntim-gasu', ['full_name' => 'Edward Ntim Gasu', 'slug' => 'edward-ntim-gasu', 'role' => 'staff', 'title' => 'Scientific Programmer', 'affiliation' => 'EAIFR', 'photo' => 'https://eaifr.ictp.it/media/23484/crpd32_ed.png?crop=0.0000000000000019489172180848,0,0,0.076894223555890748&cropmode=percentage&width=350&height=350']],
    ['people', 'daniel-manu-marfo', ['full_name' => 'Daniel Manu-Marfo', 'slug' => 'daniel-manu-marfo', 'role' => 'faculty', 'title' => 'Senior Lecturer', 'affiliation' => 'EAIFR', 'photo' => 'https://eaifr.ictp.it/media/23466/manu_marfo_092022.jpg?crop=0,0.017774475199702763,0.0000000000000003248195363475,0.20459223481330138&cropmode=percentage&width=350&height=350']],
    ['people', 'catherine-a-meriaux', ['full_name' => 'Catherine A. Meriaux', 'slug' => 'catherine-a-meriaux', 'role' => 'director', 'title' => 'Professor of Geophysics; Director', 'affiliation' => 'EAIFR', 'area_slug' => 'geophysics', 'photo' => 'https://eaifr.ictp.it/media/23462/pxl_20231121_124407667mp.jpg?crop=0.17349613145888193,0.3058538798553847,0.32706324204279674,0.3195656502702834&cropmode=percentage&width=350&height=350']],
    ['people', 'sylvane-ndayikunda', ['full_name' => 'Sylvane Ndayikunda', 'slug' => 'sylvane-ndayikunda', 'role' => 'staff', 'title' => 'Administrative Assistant', 'affiliation' => 'EAIFR', 'email' => 'admin-assist@eaifr.org', 'photo' => 'https://eaifr.ictp.it/media/28991/ms_sylvane_ndayikunda.jpg']],

    ['people', 'eleonora-luppi', ['full_name' => 'Eleonora Luppi', 'slug' => 'eleonora-luppi', 'role' => 'visiting-lecturer', 'title' => 'Mathematical Methods for Physicists', 'affiliation' => 'Sorbonne University, Paris, France']],
    ['people', 'andrea-marini', ['full_name' => 'Andrea Marini', 'slug' => 'andrea-marini', 'role' => 'visiting-lecturer', 'title' => 'Quantum Mechanics I & II and Many Body Physics', 'affiliation' => 'Istituto di Struttura della Materia – CNR, Rome, Italy']],
    ['people', 'sara-bonella', ['full_name' => 'Sara Bonella', 'slug' => 'sara-bonella', 'role' => 'visiting-lecturer', 'title' => 'Statistical Physics', 'affiliation' => 'École Polytechnique Fédérale de Lausanne, Switzerland']],
    ['people', 'ali-a-hassanali', ['full_name' => 'Ali A. Hassanali', 'slug' => 'ali-a-hassanali', 'role' => 'visiting-lecturer', 'title' => 'Biological Physics', 'affiliation' => 'Abdus Salam International Centre for Theoretical Physics, Trieste, Italy']],
    ['people', 'david-herrera-marti', ['full_name' => 'David Herrera Marti', 'slug' => 'david-herrera-marti', 'role' => 'visiting-lecturer', 'title' => 'Electromagnetism and Relativity & Statistical Physics II', 'affiliation' => 'CEA List']],
    ['people', 'enrique-rico-ortega', ['full_name' => 'Enrique Rico Ortega', 'slug' => 'enrique-rico-ortega', 'role' => 'visiting-lecturer', 'title' => 'Quantum Mechanics II', 'affiliation' => 'CERN, Department of Theoretical Physics']],
    ['people', 'dave-may', ['full_name' => 'Dave May', 'slug' => 'dave-may', 'role' => 'visiting-lecturer', 'title' => 'Numerical Methods I', 'affiliation' => 'Scripps Institution of Oceanography, San Diego, USA']],
    ['people', 'matthieu-saubanere', ['full_name' => 'Matthieu Saubanère', 'slug' => 'matthieu-saubanere', 'role' => 'visiting-lecturer', 'title' => 'Electrons and Phonons in Solids', 'affiliation' => 'CNRS, University of Bordeaux, Talence, France']],
    ['people', 'fulvio-baldovin', ['full_name' => 'Fulvio Baldovin', 'slug' => 'fulvio-baldovin', 'role' => 'visiting-lecturer', 'title' => 'Advanced Statistical Mechanics', 'affiliation' => 'Università di Padova, Italy']],
    ['people', 'pascal-viot', ['full_name' => 'Pascal Viot', 'slug' => 'pascal-viot', 'role' => 'visiting-lecturer', 'title' => 'Electromagnetism and Relativity', 'affiliation' => 'Sorbonne University, Paris, France']],
    ['people', 'mario-amado-montero', ['full_name' => 'Mario Amado Montero', 'slug' => 'mario-amado-montero', 'role' => 'visiting-lecturer', 'title' => 'Quantum Mechanics I', 'affiliation' => 'Universidad de Salamanca, Spain']],
    ['people', 'olindo-corrandini', ['full_name' => 'Olindo Corrandini', 'slug' => 'olindo-corrandini', 'role' => 'visiting-lecturer', 'title' => 'Mathematical Methods for Physicists', 'affiliation' => 'Università di Modena e Reggio Emilia, Italy']],
    ['people', 'arthur-france-lanord', ['full_name' => 'Arthur France-Lanord', 'slug' => 'arthur-france-lanord', 'role' => 'visiting-lecturer', 'title' => 'Numerical Methods II', 'affiliation' => 'Sorbonne University, Paris, France']],
    ['people', 'flavio-siro-brigiano', ['full_name' => 'Flavio Siro Brigiano', 'slug' => 'flavio-siro-brigiano', 'role' => 'visiting-lecturer', 'title' => 'Numerical Methods II', 'affiliation' => 'Sorbonne University, Paris, France']],
    ['people', 'alberto-zobelli', ['full_name' => 'Alberto Zobelli', 'slug' => 'alberto-zobelli', 'role' => 'visiting-lecturer', 'title' => 'Solid State Physics', 'affiliation' => 'Université Paris-Saclay, France']],
    ['people', 'arjun-narayanan', ['full_name' => 'Arjun Narayanan', 'slug' => 'arjun-narayanan', 'role' => 'visiting-lecturer', 'title' => 'Biological Physics', 'affiliation' => 'New York University Abu Dhabi, UAE']],
    ['people', 'marcello-musso', ['full_name' => 'Marcello Musso', 'slug' => 'marcello-musso', 'role' => 'visiting-lecturer', 'title' => 'Classical Mechanics', 'affiliation' => 'Universidad de Salamanca, Spain']],
    ['people', 'andy-hooper', ['full_name' => 'Andy Hooper', 'slug' => 'andy-hooper', 'role' => 'visiting-lecturer', 'title' => 'Physics of Volcanoes, Earthquakes, Geodesy, Geophysics, InSAR and Machine Learning', 'affiliation' => 'University of Leeds, UK', 'area_slug' => 'geophysics']],
    ['people', 'alberto-armigliato', ['full_name' => 'Alberto Armigliato', 'slug' => 'alberto-armigliato', 'role' => 'visiting-lecturer', 'title' => 'Mechanics of Earthquakes', 'affiliation' => 'University of Bologna, Italy', 'area_slug' => 'geophysics']],
    ['people', 'lorenzo-mantiloni', ['full_name' => 'Lorenzo Mantiloni', 'slug' => 'lorenzo-mantiloni', 'role' => 'visiting-lecturer', 'title' => 'Physics of Volcanoes', 'affiliation' => 'University of Exeter, UK', 'area_slug' => 'geophysics']],
    ['people', 'cesare-angeli', ['full_name' => 'Cesare Angeli', 'slug' => 'cesare-angeli', 'role' => 'visiting-lecturer', 'title' => 'Theoretical Seismology', 'affiliation' => 'University of Bologna, Italy', 'area_slug' => 'geophysics']],
    ['people', 'emanuele-coccia', ['full_name' => 'Emanuele Coccia', 'slug' => 'emanuele-coccia', 'role' => 'visiting-lecturer', 'title' => 'Mathematical Methods for Physics', 'affiliation' => 'University of Trieste, Italy']],

    ['people', 'celine-uwinema', ['full_name' => 'Celine Uwinema', 'slug' => 'celine-uwinema', 'role' => 'phd-student', 'title' => 'PhD student — Geophysics', 'affiliation' => 'EAIFR', 'area_slug' => 'geophysics', 'photo' => 'https://eaifr.ictp.it/media/23530/celine_photo.jpg?width=202&height=260']],
    ['people', 'sylvain-barayagwiza', ['full_name' => 'Sylvain Barayagwiza', 'slug' => 'sylvain-barayagwiza', 'role' => 'phd-student', 'title' => 'PhD student — Geophysics', 'affiliation' => 'EAIFR', 'area_slug' => 'geophysics', 'photo' => 'https://eaifr.ictp.it/media/23531/sylvain_photo.png?width=227&height=288']],
    ['people', 'sosthene-irambona', ['full_name' => 'Sosthène Irambona', 'slug' => 'sosthene-irambona', 'role' => 'phd-student', 'title' => 'PhD student — Condensed Matter Physics', 'affiliation' => 'EAIFR', 'area_slug' => 'condensed-matter-physics', 'photo' => 'https://eaifr.ictp.it/media/11052/irambonasosthene.jpeg?width=213&height=284']],
    ['people', 'sunday-joseph-ogenyi', ['full_name' => 'Sunday Joseph Ogenyi', 'slug' => 'sunday-joseph-ogenyi', 'role' => 'phd-student', 'title' => 'PhD student — Condensed Matter Physics', 'affiliation' => 'EAIFR', 'area_slug' => 'condensed-matter-physics', 'photo' => 'https://eaifr.ictp.it/media/11053/img_sundayoge.jpg?width=166&height=221']],
    ['people', 'wala-fathelrahman-ibrahim-elsayed', ['full_name' => 'Wala Fathelrahman Ibrahim Elsayed', 'slug' => 'wala-fathelrahman-ibrahim-elsayed', 'role' => 'phd-student', 'title' => 'PhD student — Condensed Matter Physics', 'affiliation' => 'EAIFR', 'area_slug' => 'condensed-matter-physics', 'photo' => 'https://eaifr.ictp.it/media/11059/wala_elsayed.jpg?width=183&height=244']],
    ['people', 'rukaya-aljuneid', ['full_name' => 'Rukaya Aljuneid', 'slug' => 'rukaya-aljuneid', 'role' => 'msc-student', 'title' => 'MSc research student — Poisson MaZe dynamics for water', 'affiliation' => 'EAIFR']],
    ['people', 'esther-mahoro', ['full_name' => 'Esther Mahoro', 'slug' => 'esther-mahoro', 'role' => 'msc-student', 'title' => 'MSc research student — Free energy calculations with constrained dynamics: Moony', 'affiliation' => 'EAIFR']],
    ['people', 'rodes-kwarisima', ['full_name' => 'Rodes Kwarisima', 'slug' => 'rodes-kwarisima', 'role' => 'msc-student', 'title' => 'MSc research student — Structural stability of SrNbO₃ and Sr₂Nb₂O₇', 'affiliation' => 'EAIFR']],
    ['people', 'ashuja-ibyanyagasani', ['full_name' => 'Ashuja Ibyanyagasani', 'slug' => 'ashuja-ibyanyagasani', 'role' => 'msc-student', 'title' => 'MSc research student — GW–BSE analysis of tin dioxide', 'affiliation' => 'EAIFR']],
    ['people', 'alberto-c-tairo', ['full_name' => 'Alberto C. Tairo', 'slug' => 'alberto-c-tairo', 'role' => 'msc-student', 'title' => 'MSc research student — Real-space energy functional', 'affiliation' => 'EAIFR']],
    ['people', 'emmanuel-umeh', ['full_name' => 'Emmanuel Umeh', 'slug' => 'emmanuel-umeh', 'role' => 'msc-student', 'title' => 'MSc research student — Quantum-classical molecular dynamics', 'affiliation' => 'EAIFR']],

    ['people', 'omololu-akin-ojo', ['full_name' => 'Omololu Akin-Ojo', 'slug' => 'omololu-akin-ojo', 'role' => 'former-staff', 'title' => 'First Director of ICTP-EAIFR', 'affiliation' => 'University of Ibadan, Nigeria']],
    ['people', 'shoaib-munir', ['full_name' => 'Shoaib Munir', 'slug' => 'shoaib-munir', 'role' => 'former-staff', 'title' => 'Former Senior Lecturer', 'affiliation' => 'High Point University, USA']],
    ['people', 'steve-ndengue', ['full_name' => 'Steve Ndengué', 'slug' => 'steve-ndengue', 'role' => 'former-staff', 'title' => 'Former Senior Lecturer', 'affiliation' => 'Haverford College, USA']],
    ['people', 'kingsley-onyebuchi-obodo', ['full_name' => 'Kingsley Onyebuchi Obodo', 'slug' => 'kingsley-onyebuchi-obodo', 'role' => 'former-staff', 'title' => 'Former Senior Lecturer', 'affiliation' => 'North-West University Potchefstroom, South Africa']],

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
