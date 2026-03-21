#!/usr/bin/env php
<?php

// ToryCRM Job Queue Worker

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

// Ensure ENV vars are available via $_ENV
foreach (['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'] as $key) {
    if (!isset($_ENV[$key]) && getenv($key) !== false) {
        $_ENV[$key] = getenv($key);
    }
}

$config = require BASE_PATH . '/config/app.php';
Core\Database::init($config['database']);

$queue   = $argv[1] ?? 'default';
$maxJobs = (int) ($argv[2] ?? 50);

echo "[Worker] Starting on queue '{$queue}', max {$maxJobs} jobs...\n";

$processed = App\Services\JobQueue::runWorker($queue, $maxJobs);
echo "[Worker] Processed {$processed} jobs.\n";

// Cleanup old rate limits
App\Services\RateLimiter::cleanup();
echo "[Worker] Cleanup done.\n";
