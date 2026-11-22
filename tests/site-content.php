<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$dist = $argv[1] ?? $root . '/dist';
$read = static function (string $path) use ($dist): string {
    $file = $dist . '/' . $path . '/index.html';
    if (!is_file($file)) {
        throw new RuntimeException("Missing exported page: {$path}");
    }
    return file_get_contents($file);
};
$check = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$people = $read('people');
$sections = [
    'permanent-staff' => 4,
    'visiting-lecturers' => 20,
    'postgraduate-students' => 4,
    'former-permanent-staff' => 5,
];
foreach ($sections as $id => $expected) {
    preg_match('~<section class="people-section" id="' . $id . '"[^>]*>(.*?)</section>~s', $people, $match);
    $section = $match[1] ?? '';
    $check(substr_count($section, 'class="person-card"') === $expected, "Unexpected membership in {$id}");
    if ($id === 'visiting-lecturers') {
        $check(substr_count($section, '/uploads/people/') === 20, 'Missing visiting lecturer portraits');
    }
}
foreach (['ali-a-hassanali', 'sosthene-irambona', 'wala-fathelrahman-ibrahim-elsayed', 'rukaya-aljuneid'] as $slug) {
    $check(!str_contains($people, '/people/' . $slug . '"'), "Inactive person listed: {$slug}");
}
$check(str_contains($people, '/people/esther-mahoro"'), 'Esther missing');
$check(str_contains($people, 'governance-partnerships.png'), 'Updated header mark missing');

$check(substr_count($people, '/people/marcello-musso"') === 1, 'Musso must appear exactly once');
$profile = $read('people/catherine-a-meriaux');
foreach (['Research interests', 'Current projects', 'Biography', 'E-LIEU', 'Google Scholar'] as $text) {
    $check(str_contains($profile, $text), "Catherine profile missing {$text}");
}
$check(substr_count($profile, 'href="https://doi.org/') === 40, 'Incomplete personal bibliography');

$about = $read('about');
$check(str_contains($about, 'tripartite agreement'), 'Updated About text missing');
$check(str_contains($about, 'Mike Hughes'), 'Steering Committee missing');
$check(!str_contains($about, 'XXX'), 'Unconfirmed name published');
$check(str_contains($read('academics'), 'Postgraduate studies'), 'Postgraduate title missing');
$check(str_contains($read('academics/msc-physics'), 'Symmetries and Bands'), 'MSc courses missing');
$check(str_contains($read('msc-application-guidelines'), 'Two recommendation letters'), 'Application documents missing');
$check(substr_count($read('msc-faqs'), '<h2>') === 7, 'Incomplete MSc FAQs');
$check(substr_count($read('publications'), '<li data-publication-item') === 48, 'Institute publications changed');
$check(substr_count($read('news'), '<blockquote class="twitter-tweet"') >= 6, 'Tweet fallback lost');


// Email addresses must never be exported whole; layout.twig rebuilds them in the browser.
$pages = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dist, FilesystemIterator::SKIP_DOTS));
foreach ($pages as $page) {
    if ($page->getExtension() !== 'html') {
        continue;
    }
    $check(
        preg_match('~[\w.+-]+@[\w-]+\.[a-z]{2,}~i', file_get_contents($page->getPathname()), $found) !== 1,
        'Unprotected email address ' . ($found[0] ?? '') . ' in ' . $page->getPathname()
    );
}
$check(str_contains($profile, 'data-eu="cmeriaux"'), 'Director email link missing');
foreach (['daniel-manu-marfo', 'edward-ntim-gasu'] as $slug) {
    $check(str_contains($read('people/' . $slug), 'Biography'), "Staff profile text missing: {$slug}");
}
$check(str_contains($read('research/geophysics'), 'Nyiragongo and Nyamuragira'), 'Geophysics research text not updated');
$check(str_contains($read('research/condensed-matter-physics'), 'from the atomistic to the continuum'), 'Condensed matter research text not updated');

// Academic navigation and admission actions must survive the static export.
foreach (['academics/msc-physics', 'academics/phd-physics', 'msc-application-guidelines', 'msc-faqs'] as $path) {
    $html = $read($path);
    $check(str_contains($html, 'aria-label="On this page"'), "Missing section navigation: {$path}");
    preg_match_all('/href="#(section-[0-9]+)"/', $html, $anchors);
    foreach ($anchors[1] as $anchor) {
        $check(str_contains($html, 'id="' . $anchor . '"'), "Broken section link: {$path}#{$anchor}");
    }
}
foreach (['academics/msc-physics', 'academics/phd-physics', 'apply'] as $path) {
    $check(!preg_match('~<a[^>]+class="btn[^>]*>Apply(?:\s|<|&|$)~u', $read($path)), "Apply button shown without a confirmed open call: {$path}");
}
$check(str_contains($read('academics'), 'degrees awarded by the University of Rwanda'), 'Degree-awarding explanation missing');
$check(str_contains($read('apply'), 'Find your application route'), 'Structured application routes missing');
$check(str_contains($read('research'), 'Publications in the index'), 'Research output context missing');
$check(str_contains($read('events'), 'event-record__media'), 'Standard event layout missing');
$check(!str_contains($read('about'), 'Contact ? Murakaza neza'), 'Footer greeting was not removed');

echo "Site content checks passed\n";
