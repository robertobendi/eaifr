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
    'visiting-lecturers' => 21,
    'postgraduate-students' => 4,
    'former-permanent-staff' => 4,
];
foreach ($sections as $id => $expected) {
    preg_match('~<section class="people-section" id="' . $id . '"[^>]*>(.*?)</section>~s', $people, $match);
    $section = $match[1] ?? '';
    $check(substr_count($section, 'class="person-card"') === $expected, "Unexpected membership in {$id}");
    if ($id === 'visiting-lecturers') {
        $check(substr_count($section, '/uploads/people/') === 21, 'Missing visiting lecturer portraits');
    }
}
foreach (['ali-a-hassanali', 'sosthene-irambona', 'wala-fathelrahman-ibrahim-elsayed', 'rukaya-aljuneid'] as $slug) {
    $check(!str_contains($people, '/people/' . $slug . '"'), "Inactive person listed: {$slug}");
}
$check(str_contains($people, '/people/esther-mahoro"'), 'Esther missing');
$check(str_contains($people, 'governance-partnerships.png'), 'Updated header mark missing');

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
$check(substr_count($read('publications'), '<li data-publication-item') === 49, 'Institute publications changed');
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

echo "Site content checks passed\n";
