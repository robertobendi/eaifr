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
    if ($collection === 'publications' && $publishAt === null && isset($data['year'])) {
        $publishAt = strtotime((int) $data['year'] . '-01-01 00:00:00 UTC');
    }
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
        'body'    => "## Mission\n\nEAIFR was established in 2018 as a young and dynamic international hub for higher education and research in basic sciences in Africa. It operates as a UNESCO Category II institute, in partnership with the Government and University of Rwanda, and with the Abdus Salam International Centre for Theoretical Physics (ICTP).\n\n## Vision\n\n- Create a community of African scientists in an international network for basic science.\n- Address African challenges in natural hazards, energy, climate, materials and health.\n- Deliver academic excellence — quality over quantity.\n\n## Governance & partnerships\n\nEAIFR operates under the auspices of UNESCO and is hosted at the University of Rwanda in Kigali. Its institutional partners are the Government of Rwanda, the University of Rwanda, and ICTP as a partner institute.",
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
        'summary' => 'EAIFR offers MSc and PhD physics programmes at the University of Rwanda.',
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
        'summary'             => 'A two-year, English-language master\'s programme at the University of Rwanda. Fully funded scholarships for admitted African students.',
        'body'                => "## Programme\n\nThe MSc in Physics is a two-year degree programme of the University of Rwanda.\n\n- **Year 1** — core graduate physics coursework\n- **Year 2** — a research thesis in one of EAIFR's research areas\n\n## Scholarships\n\nFully funded scholarships are available for admitted African students (tuition, stipend, travel, accommodation).\n\n## How to apply\n\nApplications are accepted annually via the ICTP application portal. Visit the Apply page for the current window.",
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

    ['publications', 'first-principles-simulation-ionizing-radiation-damage', ['title' => 'First-Principles Simulation of Ionizing Radiation Damage in Molecular Systems over Short Time Scales', 'slug' => 'first-principles-simulation-ionizing-radiation-damage', 'authors' => 'Naija, P.; Alvarez Ibarra, A.; Flores-Moreno, R.; Guillaumont, D.; de la Lande, A.', 'year' => 2026, 'venue' => 'Journal of Chemical Theory and Computation', 'url' => 'https://doi.org/10.1021/acs.jctc.6c01190']],
    ['publications', 'imaging-northern-zagros-collision-zone', ['title' => 'Imaging the northern Zagros collision zone by ambient noise and earthquake surface wave tomography', 'slug' => 'imaging-northern-zagros-collision-zone', 'authors' => 'Zarunizadeh, Z.; Aoudia, A.; Motaghi, K.; Pachhai, S.; Manu-Marfo, D.; Yang, Y.; Abdulnaby, W.; Ai, Y.; Chen, L.', 'year' => 2026, 'venue' => 'Tectonophysics, 231041', 'url' => 'https://doi.org/10.1016/j.tecto.2025.231041', 'area_slug' => 'geophysics', 'people_slugs' => 'daniel-manu-marfo']],
    ['publications', 'neural-network-potential-pure-silica-zeolites', ['title' => 'Efficient Development of a Neural Network Potential for Pure Silica Zeolites', 'slug' => 'neural-network-potential-pure-silica-zeolites', 'authors' => 'Ogenyi, S.J.; Shaidu, Y.; Seriani, N.; Ojo, O.A.', 'year' => 2026, 'venue' => 'Physical Chemistry Chemical Physics', 'url' => 'https://doi.org/10.1039/D5CP04897D', 'area_slug' => 'condensed-matter-physics', 'people_slugs' => 'sunday-joseph-ogenyi']],
    ['publications', 'magma-to-macrophage', ['title' => 'From Magma to Macrophage: the grand challenges of volcanic environments', 'slug' => 'magma-to-macrophage', 'authors' => 'Mériaux, C.A. et al.', 'year' => 2026, 'venue' => 'Bulletin of Volcanology, 88, 22', 'url' => 'https://doi.org/10.1007/s00445-026-01936-2', 'area_slug' => 'geophysics', 'people_slugs' => 'catherine-a-meriaux,edward-ntim-gasu']],
    ['publications', 'challenges-needs-african-geoscience-research', ['title' => 'Challenges and needs of African geoscience research', 'slug' => 'challenges-needs-african-geoscience-research', 'authors' => 'Asrat, A.; Keane, C.; Mériaux, C.A.; Mhopjeni, K.; Nickless, E.; Boland, M.; Lopes, O.A.', 'year' => 2025, 'venue' => 'Nature Geoscience, pp. 1–3', 'url' => 'https://doi.org/10.1038/s41561-025-01808-y', 'area_slug' => 'geophysics', 'people_slugs' => 'catherine-a-meriaux']],
    ['publications', 'rigid-rotor-ph3-h2', ['title' => 'Rigid rotor state-to-state cross-sections and rates of the PH3 + H2 collision', 'slug' => 'rigid-rotor-ph3-h2', 'authors' => 'Najar, F.; Badri, A.; Tela, H.T.; Ndengué, S.; Tasinato, N.', 'year' => 2025, 'venue' => 'Physical Chemistry Chemical Physics, 27(36), pp. 19500–19508', 'url' => 'https://doi.org/10.1039/D5CP02236C', 'people_slugs' => 'steve-ndengue']],
    ['publications', 'new-potential-energy-surface-po-h2', ['title' => 'A New Potential Energy Surface of the PO+-H2 Complex and Intermolecular Rovibrational State Calculations', 'slug' => 'new-potential-energy-surface-po-h2', 'authors' => 'Tela, H.T.; Bop, C.T.; Lique, F.; Ndengué, S.', 'year' => 2025, 'venue' => 'The Journal of Physical Chemistry A, 129(28), p. 6247', 'url' => 'https://doi.org/10.1021/acs.jpca.5c02638', 'people_slugs' => 'steve-ndengue']],
    ['publications', 'subsurface-lateral-magma-propagation', ['title' => 'Subsurface lateral magma propagation from Nyiragongo volcano in the Western Rift Zone of the East African Rift', 'slug' => 'subsurface-lateral-magma-propagation', 'authors' => 'Pinel, V.; Mériaux, C.A.', 'year' => 2025, 'venue' => 'Journal of African Earth Sciences, 226, p. 105569', 'url' => 'https://doi.org/10.1016/j.jafrearsci.2025.105569', 'area_slug' => 'geophysics', 'people_slugs' => 'catherine-a-meriaux']],
    ['publications', 'hematite-o2-adsorption', ['title' => 'Impact of hematite surface magnetic configurations and strain on O2 adsorption', 'slug' => 'hematite-o2-adsorption', 'authors' => 'Elsayed, W.; Raji, A.T.; Akin-Ojo, O.; Stojić, N.; Seriani, N.', 'year' => 2025, 'venue' => 'The European Physical Journal B, 98(6), p. 122', 'url' => 'https://doi.org/10.1140/epjb/s10051-025-00978-3', 'area_slug' => 'condensed-matter-physics', 'people_slugs' => 'wala-fathelrahman-ibrahim-elsayed,omololu-akin-ojo']],
    ['publications', 'variational-quantum-subspace-construction', ['title' => 'Variational quantum subspace construction via symmetry-preserving cost functions', 'slug' => 'variational-quantum-subspace-construction', 'authors' => 'Akande, H.A.; Perrin, A.; Senjean, B.; Saubanère, M.', 'year' => 2025, 'venue' => 'Physical Review A, 112(3), p. 032623', 'url' => 'https://doi.org/10.1103/6hpp-zl5h', 'people_slugs' => 'matthieu-saubanere']],

    ['publications', 'dimensional-transition-bi2se3-bi2te3-topological-insulators', ['title' => 'Deciphering dimensional transition effects in Bi2Se3 and Bi2Te3 topological insulators', 'slug' => 'dimensional-transition-bi2se3-bi2te3-topological-insulators', 'authors' => 'Hailouf, H.E.; Obodo, K.O.; Aourag, H.; Rani, U.; Kamlesh, P.K.; Reggab, K.; Verma, M.L.; Goumri-Said, S.', 'year' => 2024, 'venue' => 'Materials Science in Semiconductor Processing, 186, p. 109019', 'url' => 'https://doi.org/10.1016/j.mssp.2024.109019', 'people_slugs' => 'kingsley-onyebuchi-obodo']],
    ['publications', 'magnetic-sorbents-sequestration-contaminants', ['title' => 'The application of magnetic sorbents for the sequestration of pesticides, pharmaceuticals, and perfluoroalkyl and polyfluoroalkyl substances from aqueous solutions', 'slug' => 'magnetic-sorbents-sequestration-contaminants', 'authors' => 'Obodo, K.O.', 'year' => 2024, 'venue' => 'Environmental Applications of Magnetic Sorbents, pp. 6-1', 'url' => 'https://iopscience.iop.org/book/edit/978-0-7503-5909-2/chapter/bk978-0-7503-5909-2ch6.pdf', 'people_slugs' => 'kingsley-onyebuchi-obodo']],
    ['publications', 'electronic-optical-properties-sn-ge-perovskite', ['title' => 'Electronic and Optical Properties of Sn/Ge-Substituted 2D Dion-Jacobson Phase (3-AMPY)4Pb4I16 Perovskite for Photovoltaic Applications: A First Principle Study', 'slug' => 'electronic-optical-properties-sn-ge-perovskite', 'authors' => 'Asefa, F.G.; Arega, A.A.; Kebede, G.G.; Mekonnen, Y.S.; Obodo, K.O.', 'year' => 2024, 'venue' => 'The Journal of Physical Chemistry C, 128(34), pp. 14158–14167', 'url' => 'https://doi.org/10.1021/acs.jpcc.4c02098', 'people_slugs' => 'kingsley-onyebuchi-obodo']],
    ['publications', 'surface-segregation-pt3nb-pt3ti', ['title' => 'Surface Segregation in Pt3Nb and Pt3Ti using Density Functional Theory-based Methods', 'slug' => 'surface-segregation-pt3nb-pt3ti', 'authors' => 'Obodo, K.O.; Kima, L.; Adeyinka, A.S.; Rai, D.P.', 'year' => 2024, 'venue' => 'Advanced Materials and Nano Systems: Theory and Experiment (Part 3), pp. 202–219', 'url' => 'https://www.benthamdirect.com/content/books/9789815223101.chapter-7', 'people_slugs' => 'kingsley-onyebuchi-obodo']],

    ['publications', 'bf-theory-homology-3-spheres', ['title' => '3 definitions of BF theory on homology 3-spheres', 'slug' => 'bf-theory-homology-3-spheres', 'authors' => 'Blau, M.; Kakona, M.; Thompson, G.', 'year' => 2023, 'venue' => 'Journal of High Energy Physics, 2023(4), pp. 1–45', 'url' => 'https://doi.org/10.1007/JHEP04(2023)146']],
    ['publications', 'gravitino-dark-matter-non-thermal-leptogenesis', ['title' => 'Observable r, gravitino dark matter, and non-thermal leptogenesis in no-scale supergravity', 'slug' => 'gravitino-dark-matter-non-thermal-leptogenesis', 'authors' => 'Ahmed, W.; Moosa, M.; Munir, S.; Zubair, U.', 'year' => 2023, 'venue' => 'Journal of High Energy Physics, 2023(5), pp. 1–21', 'url' => 'https://doi.org/10.1007/JHEP05(2023)011', 'people_slugs' => 'shoaib-munir']],
    ['publications', 'tev-scale-leptoquark-searches-lhc', ['title' => 'TeV-scale leptoquark searches at the LHC and their E6SSM interpretation', 'slug' => 'tev-scale-leptoquark-searches-lhc', 'authors' => 'Ali, M.; Khalil, S.; Moretti, S.; Munir, S.; Nevzorov, R.; Nikitenko, A.; Waltari, H.', 'year' => 2023, 'venue' => 'Journal of High Energy Physics, 2023(3), pp. 1–21', 'url' => 'https://doi.org/10.1007/JHEP03(2023)117', 'people_slugs' => 'shoaib-munir']],
    ['publications', 'electronic-absorption-spectrum-no2', ['title' => 'Temperature dependence of the electronic absorption spectrum of NO2', 'slug' => 'electronic-absorption-spectrum-no2', 'authors' => 'Ndengué, S.; Quintas-Sánchez, E.; Dawes, R.; Blackstone, C.C.; Osborn, D.L.', 'year' => 2023, 'venue' => 'The Journal of Physical Chemistry A, 127(29), pp. 6051–6062', 'url' => 'https://doi.org/10.1021/acs.jpca.3c02832', 'people_slugs' => 'steve-ndengue']],
    ['publications', 'thiocyanate-induced-n-activation-pyridinedicarbonyl', ['title' => 'Thiocyanate-induced N-activation and nucleophilic 1,2-dearomatization of pyridinedicarbonyl dichloride: a synthetic route to a novel 1,2-dihydropyridine-based heterocyclic compound', 'slug' => 'thiocyanate-induced-n-activation-pyridinedicarbonyl', 'authors' => 'Mukiza, J.; Habarurema, G.; Nkuranga, J.B.; Sezirahiga, J.; Akin-Ojo, O.; Nkurunziza, J.B.; Kampire, E.; Gakuba, E.; Blacque, O.', 'year' => 2023, 'venue' => 'Journal of the Iranian Chemical Society, 20(10), pp. 2657–2664', 'url' => 'https://doi.org/10.1007/s13738-023-02863-1', 'people_slugs' => 'omololu-akin-ojo']],
    ['publications', 'oeindo-excitation-energies-uv-vis', ['title' => 'oeINDO: Efficient determination of excitation energies and UV–Vis absorption spectra of nano-sized Zn, Cd, S and their complexes', 'slug' => 'oeindo-excitation-energies-uv-vis', 'authors' => 'Oyeniyi, E.; Akin-Ojo, O.; Popoola, O.O.', 'year' => 2023, 'venue' => 'Computational and Theoretical Chemistry, 1223, p. 114096', 'url' => 'https://doi.org/10.1016/j.comptc.2023.114096', 'people_slugs' => 'omololu-akin-ojo']],
    ['publications', 'dissipation-rate-turbulent-kinetic-energy', ['title' => 'An improved method for estimating the dissipation rate of turbulent kinetic energy using structure functions evaluated from the motion of finite-sized neutrally buoyant particles', 'slug' => 'dissipation-rate-turbulent-kinetic-energy', 'authors' => 'Teixeira, M.A.; Mériaux, C.A.', 'year' => 2023, 'venue' => 'Physics of Fluids, 35(6)', 'url' => 'https://doi.org/10.1063/5.0148473', 'area_slug' => 'geophysics', 'people_slugs' => 'catherine-a-meriaux']],
    ['publications', 'all-scales-understand-rifts', ['title' => 'All scales must be considered to understand rifts', 'slug' => 'all-scales-understand-rifts', 'authors' => 'Ebinger, C.; van Wijk, J.; Olaka, L.; Mériaux, C.; Fontijn, K.', 'year' => 2023, 'venue' => 'Nature Reviews Earth & Environment, 4(4), pp. 209–210', 'url' => 'https://doi.org/10.1038/s43017-023-00408-x', 'area_slug' => 'geophysics', 'people_slugs' => 'catherine-a-meriaux']],
    ['publications', 'rovibrational-states-h2o-hcn-heterodimer', ['title' => 'Rovibrational states calculations of the H2O-HCN heterodimer with the multiconfiguration time dependent Hartree method', 'slug' => 'rovibrational-states-h2o-hcn-heterodimer', 'authors' => 'Tela, H.T.; Quintas-Sánchez, E.; Dubernet, M.L.; Scribano, Y.; Dawes, R.; Gatti, F.; Ndengué, S.', 'year' => 2023, 'venue' => 'Physical Chemistry Chemical Physics, 25(46), pp. 31813–31824', 'url' => 'https://doi.org/10.1039/D3CP03225F', 'people_slugs' => 'steve-ndengue']],

    ['publications', 'massive-ray-singer-torsion-path-integrals', ['title' => 'Massive Ray-Singer torsion and path integrals', 'slug' => 'massive-ray-singer-torsion-path-integrals', 'authors' => 'Blau, M.; Kakona, M.; Thompson, G.', 'year' => 2022, 'venue' => 'Journal of High Energy Physics, 2022(8), pp. 1–53', 'url' => 'https://doi.org/10.1007/JHEP08(2022)230']],
    ['publications', 'dark-matter-cp-violating-nmssm', ['title' => 'Dark matter in the CP-violating NMSSM', 'slug' => 'dark-matter-cp-violating-nmssm', 'authors' => 'Ahmed, W.; Goodsell, M.; Munir, S.', 'year' => 2022, 'venue' => 'The European Physical Journal C, 82(6), p. 539', 'url' => 'https://doi.org/10.1140/epjc/s10052-022-10449-z', 'people_slugs' => 'shoaib-munir']],
    ['publications', 'dft-hexagonal-mx2-zrxo-heterostructures', ['title' => 'DFT investigation of the electronic and optical properties of hexagonal MX2/ZrXO (M = W, Mo and X = S, Se) van der Waals heterostructures for photovoltaic solar cell application', 'slug' => 'dft-hexagonal-mx2-zrxo-heterostructures', 'authors' => 'Kassaye Sibhatu, A.; Teshome, T.; Akin-Ojo, O.; Yimam, A.; Asres, G.A.', 'year' => 2022, 'venue' => 'RSC Advances, 12(47), pp. 30838–30845', 'url' => 'https://doi.org/10.1039/D2RA05310A', 'people_slugs' => 'omololu-akin-ojo']],
    ['publications', 'electronic-structure-magnetism-sr4ru3o10', ['title' => 'Electronic structure and magnetism of the triple-layered ruthenate Sr4Ru3O10', 'slug' => 'electronic-structure-magnetism-sr4ru3o10', 'authors' => 'Gebreyesus, G.; Ngabonziza, P.; Nagura, J.; Seriani, N.; Akin-Ojo, O.; Martin, R.M.', 'year' => 2022, 'venue' => 'Physical Review B, 105(16), p. 165119', 'url' => 'https://doi.org/10.1103/PhysRevB.105.165119', 'people_slugs' => 'omololu-akin-ojo']],
    ['publications', 'induced-dipole-methane-aggregation-water', ['title' => 'Contribution of the Induced-Dipole Interaction to Methane Aggregation in Water', 'slug' => 'induced-dipole-methane-aggregation-water', 'authors' => 'Akin-Ojo, O.', 'year' => 2022, 'venue' => 'The Journal of Physical Chemistry B, 126(13), pp. 2552–2556', 'url' => 'https://doi.org/10.1021/acs.jpcb.2c00518', 'people_slugs' => 'omololu-akin-ojo']],
    ['publications', 'sintering-temperature-ceramic-water-filters', ['title' => 'Effects of sintering temperature on the filtration and mechanical properties of ceramic water filters', 'slug' => 'sintering-temperature-ceramic-water-filters', 'authors' => 'Omoniyi, O.A.; Salifu, A.A.; Obayemi, J.D.; Oyewole, O.K.; Nigay, P.M.; Akin-Ojo, O.; Soboyejo, W.O.', 'year' => 2022, 'venue' => 'Cogent Engineering, 9(1), p. 2119536', 'url' => 'https://doi.org/10.1080/23311916.2021.2006112', 'people_slugs' => 'omololu-akin-ojo']],
    ['publications', 'hydroxyapatite-bone-particle-ceramic-water-filters', ['title' => 'Hydroxyapatite and bone particle-doped ceramic water filters for the removal of fluoride and bacteria', 'slug' => 'hydroxyapatite-bone-particle-ceramic-water-filters', 'authors' => 'Omoniyi, O.A.; Salifu, A.A.; Obayemi, J.D.; Oyewole, O.K.; Nigay, P.M.; Akin-Ojo, O.; Soboyejo, W.O.', 'year' => 2022, 'venue' => 'Cogent Engineering, 9(1), p. 2006112', 'url' => 'https://doi.org/10.1080/23311916.2022.2119536', 'people_slugs' => 'omololu-akin-ojo']],
    ['publications', 'vent-geometry-growth-lava-domes', ['title' => 'The impact of vent geometry on the growth of lava domes', 'slug' => 'vent-geometry-growth-lava-domes', 'authors' => 'Mériaux, C.A.; May, D.A.; Jaupart, C.', 'year' => 2022, 'venue' => 'Geophysical Journal International, 229(3), pp. 1680–1694', 'url' => 'https://doi.org/10.1093/gji/ggac005', 'area_slug' => 'geophysics', 'people_slugs' => 'catherine-a-meriaux']],
    ['publications', 'filtering-turbulence-properties-finite-particles', ['title' => 'Estimating the filtering of turbulence properties by finite-sized particles using analytical energy spectra', 'slug' => 'filtering-turbulence-properties-finite-particles', 'authors' => 'Teixeira, M.A.; Mériaux, C.A.', 'year' => 2022, 'venue' => 'Physics of Fluids, 34(4)', 'url' => 'https://doi.org/10.1063/5.0084622', 'area_slug' => 'geophysics', 'people_slugs' => 'catherine-a-meriaux']],

    ['publications', 'excursion-set-peaks-energy-haloes', ['title' => 'Excursion set peaks in energy as a model for haloes', 'slug' => 'excursion-set-peaks-energy-haloes', 'authors' => 'Musso, M.; Sheth, R.K.', 'year' => 2021, 'venue' => 'Monthly Notices of the Royal Astronomical Society, 508(3), pp. 3634–3648', 'url' => 'https://doi.org/10.1093/mnras/stab2640']],
    ['publications', 'quantum-interference-higgs-pair-production', ['title' => 'Quantum interference effects in Higgs boson pair-production beyond the Standard Model', 'slug' => 'quantum-interference-higgs-pair-production', 'authors' => 'Das, B.; Moretti, S.; Munir, S.; Poulose, P.', 'year' => 2021, 'venue' => 'The European Physical Journal C, 81(4), p. 347', 'url' => 'https://doi.org/10.1140/epjc/s10052-021-09023-w', 'people_slugs' => 'shoaib-munir']],
    ['publications', 'low-lying-electronic-states-no2', ['title' => 'The low-lying electronic states of NO2: Potential energy and dipole surfaces, bound states, and electronic absorption spectrum', 'slug' => 'low-lying-electronic-states-no2', 'authors' => 'Ndengué, S.; Quintas-Sánchez, E.; Dawes, R.; Osborn, D.', 'year' => 2021, 'venue' => 'The Journal of Physical Chemistry A, 125(25), pp. 5519–5533', 'url' => 'https://doi.org/10.1021/acs.jpca.1c03482', 'people_slugs' => 'steve-ndengue']],
    ['publications', 'rotationally-inelastic-collisions-oh-he', ['title' => 'Temperature-dependent rotationally inelastic collisions of OH− and He', 'slug' => 'rotationally-inelastic-collisions-oh-he', 'authors' => 'Endres, E.S.; Ndengué, S.; Lakhmanskaya, O.; Lee, S.; Gianturco, F.A.; Dawes, R.; Wester, R.', 'year' => 2021, 'venue' => 'Physical Review A, 103(5), p. 052807', 'url' => 'https://doi.org/10.1103/PhysRevA.103.052807', 'people_slugs' => 'steve-ndengue']],
    ['publications', 'raman-frequencies-diamond-non-hydrostatic-pressure', ['title' => 'Raman frequencies of diamond under non-hydrostatic pressure', 'slug' => 'raman-frequencies-diamond-non-hydrostatic-pressure', 'authors' => 'Mohammed Idris Bakhit, A.; Mutisya, S.; Scandolo, S.', 'year' => 2021, 'venue' => 'Applied Physics Letters, 119(21)', 'url' => 'https://doi.org/10.1063/5.0069818']],

    ['publications', 'peaks-theory-primordial-black-holes', ['title' => 'Application of peaks theory to the abundance of primordial black holes', 'slug' => 'peaks-theory-primordial-black-holes', 'authors' => 'Young, S.; Musso, M.', 'year' => 2020, 'venue' => 'Journal of Cosmology and Astroparticle Physics, 2020(11), p. 022', 'url' => 'https://doi.org/10.1088/1475-7516/2020/11/022']],
    ['publications', 'cosmic-web-connectivity-galaxy-properties', ['title' => 'The impact of the connectivity of the cosmic web on the physical properties of galaxies at its nodes', 'slug' => 'cosmic-web-connectivity-galaxy-properties', 'authors' => 'Kraljic, K.; Pichon, C.; Codis, S.; Laigle, C.; Davé, R.; Dubois, Y.; Hwang, H.S.; Pogosyan, D.; Arnouts, S.; Devriendt, J.; Musso, M.', 'year' => 2020, 'venue' => 'Monthly Notices of the Royal Astronomical Society, 491(3), pp. 4294–4309', 'url' => 'https://doi.org/10.1093/mnras/stz3319']],
    ['publications', 'h-hd-d-h2-chemical-reaction', ['title' => 'Theoretical investigation of the H + HD → D + H2 chemical reaction for astrophysical applications: A state-to-state quasi-classical study', 'slug' => 'h-hd-d-h2-chemical-reaction', 'authors' => 'Bossion, D.; Ndengué, S.; Meyer, H.D.; Gatti, F.; Scribano, Y.', 'year' => 2020, 'venue' => 'The Journal of Chemical Physics, 153(8)', 'url' => 'https://doi.org/10.1063/5.0017697', 'people_slugs' => 'steve-ndengue']],
    ['publications', 'rotationally-inelastic-scattering-o3-ar', ['title' => 'Rotationally inelastic scattering of O3–Ar: state-to-state rates with the multiconfigurational time dependent Hartree method', 'slug' => 'rotationally-inelastic-scattering-o3-ar', 'authors' => 'Sur, S.; Ndengué, S.A.; Quintas-Sánchez, E.; Bop, C.; Lique, F.; Dawes, R.', 'year' => 2020, 'venue' => 'Physical Chemistry Chemical Physics, 22(4), pp. 1869–1880', 'url' => 'https://doi.org/10.1039/C9CP06501F', 'people_slugs' => 'steve-ndengue']],
    ['publications', 'physics-in-africa', ['title' => 'Physics in Africa', 'slug' => 'physics-in-africa', 'authors' => 'Sa’id, R.S.; Fuwape, I.; Dikandé, A.M.; Mimouni, J.; Hasford, F.; Haynes, D.; Gledhill, I.; Amolo, G.; Akin-Ojo, O.; Eassa, N.', 'year' => 2020, 'venue' => 'Nature Reviews Physics, 2(10), pp. 520–523', 'url' => 'https://doi.org/10.1038/s42254-020-0239-8', 'people_slugs' => 'omololu-akin-ojo']],
    ['publications', 'cosmic-peaks-filaments-walls-merge', ['title' => 'When do cosmic peaks, filaments, or walls merge? A theory of critical events in a multiscale landscape', 'slug' => 'cosmic-peaks-filaments-walls-merge', 'authors' => 'Cadiou, C.; Pichon, C.; Codis, S.; Musso, M.; Pogosyan, D.; Dubois, Y.; Cardoso, J.F.; Prunet, S.', 'year' => 2020, 'venue' => 'Monthly Notices of the Royal Astronomical Society, 496(4), pp. 4787–4821', 'url' => 'https://doi.org/10.1093/mnras/staa1853']],

    ['publications', 'galaxies-oriented-saddle-cosmic-web', ['title' => 'Galaxies flowing in the oriented saddle frame of the cosmic web', 'slug' => 'galaxies-oriented-saddle-cosmic-web', 'authors' => 'Kraljic, K.; Pichon, C.; Dubois, Y.; Codis, S.; Cadiou, C.; Devriendt, J.; Musso, M.; Welker, C.; Arnouts, S.; Hwang, H.S.; Laigle, C.', 'year' => 2019, 'venue' => 'Monthly Notices of the Royal Astronomical Society, 483(3), pp. 3227–3254', 'url' => 'https://doi.org/10.1093/mnras/sty3216']],
    ['publications', 'electroweak-production-multiple-scalars-2hdm', ['title' => 'Electroweak production of multiple (pseudo) scalars in the 2HDM', 'slug' => 'electroweak-production-multiple-scalars-2hdm', 'authors' => 'Enberg, R.; Klemm, W.; Moretti, S.; Munir, S.', 'year' => 2019, 'venue' => 'The European Physical Journal C, 79(6), p. 512', 'url' => 'https://doi.org/10.1140/epjc/s10052-019-7025-8', 'people_slugs' => 'shoaib-munir']],
    ['publications', 'inelastic-rotational-cross-sections-five-atom', ['title' => 'State-to-state inelastic rotational cross sections in five-atom systems with the multiconfiguration time dependent Hartree method', 'slug' => 'inelastic-rotational-cross-sections-five-atom', 'authors' => 'Ndengué, S.; Scribano, Y.; Gatti, F.; Dawes, R.', 'year' => 2019, 'venue' => 'The Journal of Chemical Physics, 151(13)', 'url' => 'https://doi.org/10.1063/1.5119381', 'people_slugs' => 'steve-ndengue']],
    ['publications', 'excitation-energies-quantum-dots', ['title' => 'Efficient determination of excitation energies and absorption spectra for quantum dots and large systems from ab initio data', 'slug' => 'excitation-energies-quantum-dots', 'authors' => 'Oyeniyi, E.; Akin-Ojo, O.', 'year' => 2019, 'venue' => 'Chemical Physics Letters, 721, pp. 12–17', 'url' => 'https://doi.org/10.1016/j.cplett.2019.02.014', 'people_slugs' => 'omololu-akin-ojo']],
    ['publications', 'methane-molecules-aggregate-water', ['title' => 'Does a pair of methane molecules aggregate in water?', 'slug' => 'methane-molecules-aggregate-water', 'authors' => 'Akin-Ojo, O.; Szalewicz, K.', 'year' => 2019, 'venue' => 'The Journal of Chemical Physics, 150(8)', 'url' => 'https://doi.org/10.1063/1.5083826', 'people_slugs' => 'omololu-akin-ojo']],

    ['publications', 'cosmic-web-assembly-bias', ['title' => 'How does the cosmic web impact assembly bias?', 'slug' => 'cosmic-web-assembly-bias', 'authors' => 'Musso, M.; Cadiou, C.; Pichon, C.; Codis, S.; Kraljic, K.; Dubois, Y.', 'year' => 2018, 'venue' => 'Monthly Notices of the Royal Astronomical Society, 476(4), pp. 4877–4906', 'url' => 'https://doi.org/10.1093/mnras/sty191']],

    ['events', 'quantum-computing-materials-2026', ['title' => 'Seminar: Quantum Computing Materials', 'slug' => 'quantum-computing-materials-2026', 'start_date' => strtotime('2026-09-23 16:00'), 'end_date' => strtotime('2026-09-23 17:00'), 'kind' => 'seminar', 'location' => 'EAIFR'], strtotime('2026-09-23 16:00')],
    ['events', 'msc-physics-thesis-defense-2026', ['title' => 'MSc Physics Thesis Defense', 'slug' => 'msc-physics-thesis-defense-2026', 'start_date' => strtotime('2026-09-09 16:00'), 'end_date' => strtotime('2026-09-09 17:00'), 'kind' => 'seminar', 'location' => 'EAIFR'], strtotime('2026-09-09 16:00')],
    ['events', 'digital-transformation-data-science-2026', ['title' => 'School: Digital Transformation and Data Science in the Energy and Industrial Sector', 'slug' => 'digital-transformation-data-science-2026', 'start_date' => strtotime('2026-08-24'), 'end_date' => strtotime('2026-08-28'), 'kind' => 'school', 'location' => 'EAIFR', 'cover_image' => 'https://eaifr.org/media/30316/image.png'], strtotime('2026-08-24')],
    ['events', 'modelling-debris-flows-flash-floods-2026', ['title' => 'Workshop: Modelling debris flows and flash floods', 'slug' => 'modelling-debris-flows-flash-floods-2026', 'start_date' => strtotime('2026-07-07'), 'end_date' => strtotime('2026-07-08'), 'kind' => 'workshop', 'location' => 'EAIFR', 'cover_image' => 'https://eaifr.org/media/30307/flyer_v2.jpg'], strtotime('2026-07-07')],
    ['events', 'geo-eaifr-webinar-june-2026', ['title' => 'GEO@EAIFR Webinar Series 2026', 'slug' => 'geo-eaifr-webinar-june-2026', 'start_date' => strtotime('2026-06-25 16:00'), 'end_date' => strtotime('2026-06-25 17:00'), 'kind' => 'seminar', 'location' => 'Online'], strtotime('2026-06-25 16:00')],
    ['events', 'optical-properties-proteins-2026', ['title' => 'Seminar: Optical Properties of Non-aromatic Proteins', 'slug' => 'optical-properties-proteins-2026', 'start_date' => strtotime('2026-06-04 16:00'), 'end_date' => strtotime('2026-06-04 17:00'), 'kind' => 'seminar', 'location' => 'EAIFR'], strtotime('2026-06-04 16:00')],
    ['events', 'natural-hazards-symposium-2026', ['title' => 'Symposium Roundtables: Natural Hazards: the importance of physical & numerical modelling', 'slug' => 'natural-hazards-symposium-2026', 'start_date' => strtotime('2026-02-18 09:00'), 'end_date' => strtotime('2026-02-18 18:00'), 'kind' => 'colloquium', 'location' => 'EAIFR'], strtotime('2026-02-18 09:00')],
    ['events', 'end-of-year-geo-webinar-2025', ['title' => 'End-of-Year Special GEO@EAIFR Webinar', 'slug' => 'end-of-year-geo-webinar-2025', 'start_date' => strtotime('2025-12-17 17:00'), 'end_date' => strtotime('2025-12-17 20:00'), 'kind' => 'seminar', 'location' => 'Online', 'cover_image' => 'https://eaifr.org/media/23536/clive_oppenheimer_2025-001.png'], strtotime('2025-12-17 17:00')],
    ['events', 'volcanic-hydrothermal-tectonic-gases-2025', ['title' => 'Training: Theoretical and practical aspects of volcanic, hydrothermal and tectonic gases using gas geochemistry', 'slug' => 'volcanic-hydrothermal-tectonic-gases-2025', 'start_date' => strtotime('2025-12-16'), 'kind' => 'workshop', 'location' => 'EAIFR', 'cover_image' => 'https://eaifr.org/media/23534/holhraun-gas.jpg'], strtotime('2025-12-16')],
    ['events', 'going-beyond-atomistic-scales-2025', ['title' => 'Going Beyond Atomistic Scales', 'slug' => 'going-beyond-atomistic-scales-2025', 'start_date' => strtotime('2025-12-05 16:00'), 'end_date' => strtotime('2025-12-05 18:00'), 'kind' => 'seminar', 'location' => 'EAIFR', 'cover_image' => 'https://eaifr.org/media/23524/steffen_wolf_webinar.png'], strtotime('2025-12-05 16:00')],
    ['events', 'volcanic-processes-conference-2025', ['title' => 'Conference on Volcanic Processes: a Variety of Length and Time Scales', 'slug' => 'volcanic-processes-conference-2025', 'start_date' => strtotime('2025-09-01'), 'end_date' => strtotime('2025-09-05'), 'kind' => 'conference', 'location' => 'EAIFR'], strtotime('2025-09-01')],
    ['events', 'remote-sensing-natural-hazards-2025', ['title' => 'Training on the use of remote sensing to manage natural hazards', 'slug' => 'remote-sensing-natural-hazards-2025', 'start_date' => strtotime('2025-08-28'), 'kind' => 'workshop', 'location' => 'EAIFR'], strtotime('2025-08-28')],
    ['events', 'statistical-physics-2025', ['title' => 'Satellite Meeting of the 29th International Conference on Statistical Physics', 'slug' => 'statistical-physics-2025', 'start_date' => strtotime('2025-07-05'), 'end_date' => strtotime('2025-07-10'), 'kind' => 'conference', 'location' => 'EAIFR'], strtotime('2025-07-05')],
    ['events', 'african-biophysics-workshop-2024', ['title' => 'African Biophysics Workshop on Experimental and Computational Sciences', 'slug' => 'african-biophysics-workshop-2024', 'start_date' => strtotime('2024-12-09'), 'end_date' => strtotime('2024-12-13'), 'kind' => 'workshop', 'location' => 'EAIFR', 'cover_image' => 'https://eaifr.org/media/23457/poster_3973.png'], strtotime('2024-12-09')],
    ['events', 'western-rwanda-symposium-2024', ['title' => 'Symposium: Western Rwanda and its environmental challenges', 'slug' => 'western-rwanda-symposium-2024', 'start_date' => strtotime('2024-11-15'), 'end_date' => strtotime('2024-11-16'), 'kind' => 'colloquium', 'location' => 'EAIFR', 'cover_image' => 'https://eaifr.org/media/23472/backdrop-banner-verii-01.jpg'], strtotime('2024-11-15')],
    ['events', 'geodetic-techniques-volcano-monitoring-2024', ['title' => 'Training in Geodetic Techniques for Volcano and Tectonic Monitoring', 'slug' => 'geodetic-techniques-volcano-monitoring-2024', 'start_date' => strtotime('2024-08-08'), 'end_date' => strtotime('2024-08-09'), 'kind' => 'workshop', 'location' => 'EAIFR'], strtotime('2024-08-08')],
    ['events', 'dark-side-universe-2023', ['title' => '17th International Workshop on the Dark Side of the Universe', 'slug' => 'dark-side-universe-2023', 'start_date' => strtotime('2023-07-10'), 'end_date' => strtotime('2023-07-14'), 'kind' => 'workshop', 'location' => 'EAIFR'], strtotime('2023-07-10')],
    ['events', 'computational-geodynamics-2023', ['title' => 'Joint ICTP-EAIFR-IUGG Workshop on Computational Geodynamics', 'slug' => 'computational-geodynamics-2023', 'start_date' => strtotime('2023-07-03'), 'end_date' => strtotime('2023-07-07'), 'kind' => 'workshop', 'location' => 'EAIFR', 'cover_image' => 'https://eaifr.org/media/22582/draft_3849.jpg'], strtotime('2023-07-03')],
    ['events', 'african-school-electronic-structure-2023', ['title' => '7th African School on Electronic Structure Methods and Applications', 'slug' => 'african-school-electronic-structure-2023', 'start_date' => strtotime('2023-06-12'), 'end_date' => strtotime('2023-06-23'), 'kind' => 'school', 'location' => 'EAIFR'], strtotime('2023-06-12')],
    ['events', 'african-physical-society-conference-2020', ['title' => 'African Physical Society International Conference 2020', 'slug' => 'african-physical-society-conference-2020', 'start_date' => strtotime('2020-11-18'), 'end_date' => strtotime('2020-11-20'), 'kind' => 'conference', 'location' => 'Online'], strtotime('2020-11-18')],
    ['events', 'eaifr-inauguration-2018', ['title' => 'ICTP-EAIFR Inauguration', 'slug' => 'eaifr-inauguration-2018', 'start_date' => strtotime('2018-10-18'), 'kind' => 'colloquium', 'location' => 'Kigali, Rwanda'], strtotime('2018-10-18')],

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

foreach (json_decode(file_get_contents(__DIR__ . '/data/x-posts.json'), true, 512, JSON_THROW_ON_ERROR) as $post) {
    $entries[] = ['news', $post['slug'], $post, $post['published_at']];
}

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
