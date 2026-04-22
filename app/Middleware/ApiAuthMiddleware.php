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

        // Bind tenant_id to the request from the owning user, so Api/*Controller's
        // tenant scope check works the same as web controllers
        if (!empty($keyRecord['user_id'])) {
            $owner = Database::fetch("SELECT tenant_id FROM users WHERE id = ?", [$keyRecord['user_id']]);
            if ($owner && isset($owner['tenant_id'])) {
                $_SESSION['tenant_id'] = (int) $owner['tenant_id'];
            }
        }

        // Optional per-key scope: check permissions JSON against requested resource.
        // Format: {"resources": ["contacts","fund_transactions",...]} or
        //         {"actions": {"orders": ["list","detail"], ...}}
        // Empty/null permissions = full access (back-compat). If set, enforce.
        $permsRaw = $keyRecord['permissions'] ?? null;
        if ($permsRaw) {
            $perms = is_string($permsRaw) ? json_decode($permsRaw, true) : $permsRaw;
            if (is_array($perms) && !self::isResourceAllowed($perms, $_SERVER['REQUEST_URI'] ?? '')) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'API key not authorized for this endpoint']);
                return false;
            }
        }

        return true;
    }

    /**
     * Check whether the request URI matches an allowed resource in the key's
     * permissions JSON. Very lightweight: extracts the resource name after
     * /api/v1/ (e.g. "fund_transactions" from "/api/v1/fund_transactions?foo=1")
     * and checks it against the allowlist.
     */
    private static function isResourceAllowed(array $perms, string $uri): bool
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '';
        if (!preg_match('#/api/v\d+/([a-z_]+)#i', $path, $m)) return false;
        $resource = $m[1];

        // Normalize singular → plural so "contact" matches "contacts" policy
        $plural = $resource . 's';
        $singular = rtrim($resource, 's');

        // Format A: flat resource allowlist
        $resources = $perms['resources'] ?? null;
        if (is_array($resources)) {
            foreach ([$resource, $plural, $singular] as $r) {
                if (in_array($r, $resources, true)) return true;
            }
            return false;
        }

        // Format B: actions map
        $actions = $perms['actions'] ?? null;
        if (is_array($actions)) {
            foreach ([$resource, $plural, $singular] as $r) {
                if (isset($actions[$r]) && is_array($actions[$r]) && count($actions[$r]) > 0) return true;
            }
            return false;
        }

        return true; // unrecognized shape → allow (safer to log warn, but not block)
    }
}
