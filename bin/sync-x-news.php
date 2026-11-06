<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Pebblestack\Core\Database;
use Pebblestack\Services\EntryRepository;

$ch = curl_init('https://x.com/ictpeaifr');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; EAIFR website news sync)',
    CURLOPT_TIMEOUT => 20,
]);
$html = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

if (!is_string($html) || $status !== 200) {
    throw new RuntimeException('Unable to retrieve the EAIFR X profile.');
}

preg_match_all('/\\\\\\"created_at_ms\\\\\\":(\d+).{0,3000}?\\\\\\"full_text\\\\\\":\\\\\\"((?:\\\\\\\\.|[^"\\\\\\\\])*)\\\\\\".{0,3000}?\\\\\\"edit_tweet_ids\\\\\\":\[\\\\\\"(\d+)\\\\\\"\]/s', $html, $matches, PREG_SET_ORDER);

$posts = [];
foreach ($matches as $match) {
    $id = $match[3];
    if (isset($posts[$id])) {
        continue;
    }

    $text = json_decode('"' . $match[2] . '"', true, 512, JSON_THROW_ON_ERROR);
    $text = trim(preg_replace('/https?:\/\/t\.co\/\w+/', '', $text) ?? '');
    if ($text === '') {
        continue;
    }

    $posts[$id] = [
        'timestamp' => (int) floor(((int) $match[1]) / 1000),
        'title' => mb_strimwidth($text, 0, 105, '…'),
        'excerpt' => mb_strimwidth($text, 0, 420, '…'),
    ];
}

uasort($posts, static fn (array $a, array $b): int => $b['timestamp'] <=> $a['timestamp']);
$posts = array_slice($posts, 0, 12, true);

if ($posts === []) {
    throw new RuntimeException('No posts were found on the EAIFR X profile.');
}

$root = realpath(__DIR__ . '/..') ?: __DIR__ . '/..';
$repo = new EntryRepository(new Database($root . '/data/pebblestack.sqlite'));

foreach ($posts as $id => $post) {
    $slug = 'x-' . $id;
    $data = [
        'title' => $post['title'],
        'slug' => $slug,
        'excerpt' => $post['excerpt'],
        'published_at' => $post['timestamp'],
        'tag' => 'announcement',
        'source_url' => 'https://x.com/ictpeaifr/status/' . $id,
    ];
    $existing = $repo->findBySlug('news', $slug);
    if ($existing === null) {
        $repo->create('news', $slug, 'published', $data, $post['timestamp']);
    } else {
        $repo->update($existing->id, $slug, 'published', $data, $post['timestamp']);
    }
}

echo 'Synced ' . count($posts) . " X posts\n";
