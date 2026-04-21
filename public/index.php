<?php
/**
 * ToryCRM - Entry Point
 */

if (!defined('BASE_PATH')) define('BASE_PATH', dirname(__DIR__));

// Autoload
require BASE_PATH . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

// Ensure $_ENV is populated (PHP-FPM may use putenv instead)
foreach (['APP_NAME','APP_URL','APP_ENV','APP_DEBUG','APP_KEY','DB_HOST','DB_PORT','DB_DATABASE','DB_USERNAME','DB_PASSWORD','DEEPSEEK_API_KEY','OPENROUTER_API_KEY','GEMINI_API_KEY','GROQ_API_KEY','GOOGLE_MAPS_API_KEY','MAIL_HOST','MAIL_PORT','MAIL_USERNAME','MAIL_PASSWORD','MAIL_FROM','MAIL_FROM_NAME'] as $key) {
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

// Session hardening (must be BEFORE session_start)
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
      || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
      || ($_SERVER['SERVER_PORT'] ?? '') == 443;

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', $https ? '1' : '0');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', '28800'); // 8h absolute
session_start();

// Session timeout: idle >2h OR absolute >8h → force re-login
$now = time();
$idleMax = 7200;      // 2h inactivity
$absoluteMax = 28800; // 8h total
if (!empty($_SESSION['user'])) {
    $loginTime = $_SESSION['login_time'] ?? $now;
    $lastActivity = $_SESSION['_last_activity'] ?? $now;
    if (($now - $lastActivity) > $idleMax || ($now - $loginTime) > $absoluteMax) {
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Phiên đã hết hạn. Vui lòng đăng nhập lại.'];
    } else {
        $_SESSION['_last_activity'] = $now;
    }
}

// Load config
$config = require BASE_PATH . '/config/app.php';

// Boot application
$app = new Core\Application($config);
$app->run();
