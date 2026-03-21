<?php

return [
    'name' => $_ENV['APP_NAME'] ?? 'ToryCRM',
    'url' => $_ENV['APP_URL'] ?? 'http://localhost:8080',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'key' => $_ENV['APP_KEY'] ?? '',
    'timezone' => 'Asia/Ho_Chi_Minh',

    'database' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => $_ENV['DB_PORT'] ?? 3306,
        'name' => $_ENV['DB_DATABASE'] ?? 'torycrm',
        'user' => $_ENV['DB_USERNAME'] ?? 'root',
        'pass' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => 'utf8mb4',
    ],

    'mail' => [
        'host' => $_ENV['MAIL_HOST'] ?? '',
        'port' => $_ENV['MAIL_PORT'] ?? 587,
        'username' => $_ENV['MAIL_USERNAME'] ?? '',
        'password' => $_ENV['MAIL_PASSWORD'] ?? '',
        'from' => $_ENV['MAIL_FROM'] ?? '',
        'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'ToryCRM',
    ],

    'pagination' => [
        'per_page' => 20,
    ],
];
