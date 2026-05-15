<?php

/**
 * CI bootstrap: install Pebblestack into a fresh data/pebblestack.sqlite and
 * load the EAIFR placeholder seed. The committed .gitignore excludes the DB
 * file itself, so every CI run starts from a clean slate.
 *
 * Idempotent — re-running on an existing DB will skip the install step and
 * still upsert the seed entries.
 *
 *     php bin/install-ci.php
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Pebblestack\Core\Database;
use Pebblestack\Services\Installer;

$root = realpath(__DIR__ . '/..') ?: __DIR__ . '/..';
$dbPath = $root . '/data/pebblestack.sqlite';

if (!is_dir(dirname($dbPath))) {
    mkdir(dirname($dbPath), 0775, true);
}

$db = new Database($dbPath);
$installer = new Installer($root, $db);

if (!$installer->isInstalled()) {
    $installer->install(
        email:    'ci@example.invalid',
        password: bin2hex(random_bytes(16)),
        name:     'CI',
        siteName: 'EAIFR',
    );
    echo "✓ installed schema + admin\n";
} else {
    echo "= schema already present\n";
}

// Run the seed in the same process. Pretend we're on CLI so the token
// gate skips and we never emit Content-Type headers.
require $root . '/seed-eaifr.php';
