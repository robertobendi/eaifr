<?php

/**
 * Static-site exporter. Boots a PHP built-in server in the background, crawls
 * every public URL (derived from collections + DB), and writes the rendered
 * HTML to ./dist/ as a tree of index.html files that GitHub Pages can serve.
 *
 * The export is designed for GitHub Pages project-page hosting where the site
 * lives under a sub-path (e.g. /eaifr). The base path is applied at the
 * rewrite stage rather than during render — PHP emits root-relative URLs as
 * usual, and this script prefixes every `href="/…"`, `src="/…"`, etc. with
 * the configured base path. Set EAIFR_STATIC_BASE_PATH="" for root hosting.
 *
 *     php bin/export.php
 *
 * Env vars:
 *   EAIFR_STATIC_BASE_PATH   URL prefix (default "/eaifr"; "" for root)
 *   EAIFR_STATIC_BASE_URL    Canonical origin for sitemap.xml + robots.txt
 *                            (default "https://example.github.io")
 *   EAIFR_EXPORT_DIST        Output directory (default "dist")
 *   EAIFR_EXPORT_PORT        Port for the dev server (default 8765)
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Pebblestack\Core\Database;
use Pebblestack\Services\EntryRepository;

$root     = realpath(__DIR__ . '/..') ?: __DIR__ . '/..';
$basePath = rtrim((string) (getenv('EAIFR_STATIC_BASE_PATH') !== false ? getenv('EAIFR_STATIC_BASE_PATH') : '/eaifr'), '/');
$baseUrl  = rtrim((string) (getenv('EAIFR_STATIC_BASE_URL')  ?: 'https://example.github.io'), '/');
$distDir  = $root . '/' . (getenv('EAIFR_EXPORT_DIST') ?: 'dist');
$port     = (int) (getenv('EAIFR_EXPORT_PORT') ?: '8765');
$hostPort = '127.0.0.1:' . $port;

if (!is_file($root . '/data/pebblestack.sqlite')) {
    fwrite(STDERR, "data/pebblestack.sqlite not found. Run bin/install-ci.php first.\n");
    exit(1);
}

// Force the page template into static-export branch (mailto, not form).
putenv('EAIFR_STATIC_EXPORT=1');

// ── 1. Clean output directory ───────────────────────────────────────────
if (is_dir($distDir)) {
    rrmdir($distDir);
}
mkdir($distDir, 0775, true);

// ── 2. Boot `php -S` in the background ──────────────────────────────────
// Use PHP_BINARY (absolute path to the current interpreter) so this works
// even when `php` isn't on the shell's PATH — e.g. Homebrew installs that
// shim PHP behind `/opt/homebrew/bin` only on login shells.
$logFile = sys_get_temp_dir() . '/eaifr-export-' . getmypid() . '.log';
$cmd = sprintf(
    'EAIFR_STATIC_EXPORT=1 %s -S %s -t %s %s > %s 2>&1 & echo $!',
    escapeshellarg(PHP_BINARY),
    escapeshellarg($hostPort),
    escapeshellarg($root),
    escapeshellarg($root . '/bin/router.php'),
    escapeshellarg($logFile),
);
$pid = (int) trim((string) shell_exec($cmd));
if ($pid <= 0) {
    fwrite(STDERR, "Failed to start PHP built-in server.\n");
    exit(1);
}
register_shutdown_function(function () use ($pid, $logFile): void {
    if ($pid > 0 && function_exists('posix_kill')) {
        @posix_kill($pid, 15);
    }
    if (is_file($logFile)) {
        @unlink($logFile);
    }
});

// Wait for the port to accept connections.
$ready = false;
for ($i = 0; $i < 100; $i++) {
    $sock = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.2);
    if ($sock) {
        fclose($sock);
        $ready = true;
        break;
    }
    usleep(50_000);
}
if (!$ready) {
    fwrite(STDERR, "Server did not start in time. Log:\n" . @file_get_contents($logFile) . "\n");
    exit(1);
}

// ── 3. Build the URL list from collections + DB ─────────────────────────
$db = new Database($root . '/data/pebblestack.sqlite');
$repo = new EntryRepository($db);

$urls = ['/'];
$collections = require $root . '/config/collections.php';

foreach ($collections as $name => $cfg) {
    if (!empty($cfg['is_form'])) {
        continue;
    }
    $route = $cfg['route'] ?? null;
    if (!is_string($route) || $route === '') {
        continue;
    }

    // List route: /news/{slug} → /news (only if a list_template is declared).
    if (preg_match('#^(.+?)/\{[a-zA-Z_]+\}$#', $route, $m) && !empty($cfg['list_template'])) {
        $urls[] = $m[1];
    }

    // Detail routes: one per published entry.
    $orderBy = $cfg['order_by'] ?? 'updated_at DESC';
    foreach ($repo->listPublished((string) $name, (string) $orderBy, 9999) as $entry) {
        // The catch-all /{slug} pages collection includes `home` — that's
        // already served at /.
        if ($name === 'pages' && $entry->slug === 'home') {
            continue;
        }
        $urls[] = preg_replace('#\{[a-zA-Z_]+\}#', $entry->slug, $route);
    }
}

$urls = array_values(array_unique($urls));
sort($urls);

// ── 4. Fetch each URL via curl ──────────────────────────────────────────
$fetch = static function (string $path) use ($hostPort): array {
    $ch = curl_init('http://' . $hostPort . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_USERAGENT      => 'eaifr-static-export/1.0',
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    return [$code, is_string($body) ? $body : ''];
};

$ok = 0;
$failed = [];
foreach ($urls as $url) {
    [$code, $body] = $fetch($url);
    if ($code !== 200) {
        $failed[] = sprintf('%3d %s', $code, $url);
        continue;
    }
    $body = rewriteHtmlPaths($body, $basePath);
    writePage($distDir, $url, $body);
    $ok++;
    echo "✓ {$url}\n";
}

// ── 5. SEO endpoints ────────────────────────────────────────────────────
[$code, $body] = $fetch('/sitemap.xml');
if ($code === 200) {
    // PHP emitted urls of the form http://127.0.0.1:8765/news/foo. Swap the
    // origin for the canonical one and prepend the base path.
    $body = str_replace('http://' . $hostPort, $baseUrl . $basePath, $body);
    file_put_contents($distDir . '/sitemap.xml', $body);
    echo "✓ /sitemap.xml\n";
}

[$code, $body] = $fetch('/robots.txt');
if ($code === 200) {
    $body = str_replace('http://' . $hostPort, $baseUrl . $basePath, $body);
    file_put_contents($distDir . '/robots.txt', $body);
    echo "✓ /robots.txt\n";
}

// ── 6. 404 page (GH Pages serves /404.html on unknown paths) ────────────
[$code, $body] = $fetch('/__not_a_real_page_' . bin2hex(random_bytes(4)));
if ($code === 404 && $body !== '') {
    file_put_contents($distDir . '/404.html', rewriteHtmlPaths($body, $basePath));
    echo "✓ /404.html\n";
}

// ── 7. Copy uploads/ ────────────────────────────────────────────────────
$uploadsSrc = $root . '/uploads';
$uploadsDst = $distDir . '/uploads';
if (is_dir($uploadsSrc)) {
    rcopy($uploadsSrc, $uploadsDst);
    echo "✓ uploads/\n";
}

// ── 8. GH Pages markers ─────────────────────────────────────────────────
// .nojekyll: serve files literally (no Jekyll preprocessing, lets us keep
// directories that start with an underscore if we ever add any).
touch($distDir . '/.nojekyll');

echo "\n";
echo "Exported {$ok} pages to {$distDir}\n";
if ($failed !== []) {
    echo "Failed:\n  " . implode("\n  ", $failed) . "\n";
    exit(2);
}
exit(0);

// ─────────────────────────────────────────────────────────────────────────

/**
 * Prefix root-relative attribute values with $basePath. Skips protocol-
 * relative URLs (//cdn.example) and anything not starting with a single "/".
 */
function rewriteHtmlPaths(string $html, string $basePath): string
{
    if ($basePath === '') {
        return $html;
    }
    $pattern = '/(\s(?:href|src|action|poster|data-src)=")\/(?!\/)/i';
    $rewritten = preg_replace_callback(
        $pattern,
        static fn (array $m): string => $m[1] . $basePath . '/',
        $html,
    );
    return $rewritten ?? $html;
}

function writePage(string $distDir, string $url, string $body): void
{
    if ($url === '/') {
        $dir = $distDir;
    } else {
        $dir = $distDir . rtrim($url, '/');
    }
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    file_put_contents($dir . '/index.html', $body);
}

function rrmdir(string $path): void
{
    if (!is_dir($path)) {
        return;
    }
    $it = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
        \RecursiveIteratorIterator::CHILD_FIRST,
    );
    foreach ($it as $entry) {
        /** @var \SplFileInfo $entry */
        if ($entry->isDir()) {
            @rmdir($entry->getPathname());
        } else {
            @unlink($entry->getPathname());
        }
    }
    @rmdir($path);
}

function rcopy(string $src, string $dst): void
{
    if (!is_dir($dst)) {
        mkdir($dst, 0775, true);
    }
    $it = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($src, \FilesystemIterator::SKIP_DOTS),
        \RecursiveIteratorIterator::SELF_FIRST,
    );
    foreach ($it as $entry) {
        /** @var \SplFileInfo $entry */
        $target = $dst . DIRECTORY_SEPARATOR . $it->getSubPathname();
        if ($entry->isDir()) {
            if (!is_dir($target)) {
                mkdir($target, 0775, true);
            }
        } else {
            copy($entry->getPathname(), $target);
        }
    }
}
