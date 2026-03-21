<?php

namespace App\Middleware;

use Core\Database;

class ApiAuthMiddleware
{
    public function handle(): bool
    {
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

        if (empty($apiKey)) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'API key required']);
            return false;
        }

        $keyRecord = Database::fetch(
            "SELECT * FROM api_keys WHERE api_key = ? AND is_active = 1",
            [$apiKey]
        );

        if (!$keyRecord) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid API key']);
            return false;
        }

        // Check expiration
        if (!empty($keyRecord['expires_at']) && strtotime($keyRecord['expires_at']) < time()) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'API key has expired']);
            return false;
        }

        // Rate limit check: requests in the last hour
        $rateLimit = (int) ($keyRecord['rate_limit'] ?? 1000);
        $requestCount = (int) ($keyRecord['request_count'] ?? 0);
        $lastUsedAt = $keyRecord['last_used_at'] ?? null;

        // Reset counter if last used more than 1 hour ago
        if ($lastUsedAt && strtotime($lastUsedAt) < strtotime('-1 hour')) {
            $requestCount = 0;
        }

        if ($requestCount >= $rateLimit) {
            http_response_code(429);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Rate limit exceeded. Try again later.']);
            return false;
        }

        // Update last_used_at and increment request_count
        Database::update('api_keys', [
            'last_used_at' => date('Y-m-d H:i:s'),
            'request_count' => $requestCount + 1,
        ], 'id = ?', [$keyRecord['id']]);

        // Store API key user info in session for API controllers
        $_SESSION['api_user'] = $keyRecord;

        return true;
    }
}
