<?php
/**
 * ToryCRM - Entry Point
 */

define('BASE_PATH', dirname(__DIR__));

// Autoload
require BASE_PATH . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

// Ensure $_ENV is populated (PHP-FPM may use putenv instead)
foreach (['APP_NAME','APP_URL','APP_ENV','APP_DEBUG','APP_KEY','DB_HOST','DB_PORT','DB_DATABASE','DB_USERNAME','DB_PASSWORD','GEMINI_API_KEY','GROQ_API_KEY','MAIL_HOST','MAIL_PORT','MAIL_USERNAME','MAIL_PASSWORD','MAIL_FROM','MAIL_FROM_NAME'] as $key) {
    if (!isset($_ENV[$key]) && getenv($key) !== false) {
        $_ENV[$key] = getenv($key);
    }
}

// Error handling
if ($_ENV['APP_DEBUG'] ?? false) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Session
session_start();

// Load config
$config = require BASE_PATH . '/config/app.php';

// Boot application
$app = new Core\Application($config);
$app->run();
