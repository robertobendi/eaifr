<?php

/**
 * Front-controller router for the PHP built-in server (`php -S`). Mirrors
 * what the Apache .htaccess does in production: pass real files through,
 * route everything else into index.php.
 *
 * Used by bin/export.php when crawling the site for static export, and also
 * usable for local dev:
 *
 *     php -S 127.0.0.1:8000 -t . bin/router.php
 */

declare(strict_types=1);

$root = realpath(__DIR__ . '/..') ?: __DIR__ . '/..';
$path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$path = is_string($path) && $path !== '' ? $path : '/';

// Let the built-in server serve any concrete file that exists on disk
// (uploads, favicon, etc.). Skip the /admin and /install bypasses — those
// must go through index.php so the router can dispatch them.
if ($path !== '/' && is_file($root . $path)) {
    return false;
}

require $root . '/index.php';
