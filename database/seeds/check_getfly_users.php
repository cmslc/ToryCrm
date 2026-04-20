<?php
require __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();
Core\Database::init([
    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'port' => $_ENV['DB_PORT'] ?? '3306',
    'name' => $_ENV['DB_DATABASE'] ?? 'torycrm',
    'user' => $_ENV['DB_USERNAME'] ?? 'root',
    'pass' => $_ENV['DB_PASSWORD'] ?? '',
]);

$cfg = Core\Database::fetch('SELECT * FROM getfly_sync_config WHERE is_active = 1 LIMIT 1');
if (!$cfg) { echo "No config\n"; exit; }

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $cfg['api_domain'] . '/api/v3/users',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_HTTPHEADER => ['X-API-KEY: ' . $cfg['api_key'], 'Content-Type: application/json'],
    CURLOPT_SSL_VERIFYPEER => false,
]);
$r = curl_exec($ch);
curl_close($ch);

$data = json_decode($r, true);
if (is_array($data) && isset($data[0])) {
    echo "Fields: " . json_encode(array_keys($data[0])) . "\n\n";
    echo "Sample:\n" . json_encode($data[0], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
} else {
    echo substr($r, 0, 1000) . "\n";
}
