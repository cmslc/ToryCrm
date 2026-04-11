<?php

namespace App\Middleware;

class CsrfMiddleware
{
    /**
     * Handle CSRF token validation.
     * Returns true if the request is allowed, false otherwise.
     */
    public function handle(): bool
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Allow GET requests
        if (strtoupper($method) === 'GET') {
            return true;
        }

        // Skip CSRF check for API routes
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $uri = trim(parse_url($uri, PHP_URL_PATH), '/');

        if (strpos($uri, 'api/') === 0) {
            return true;
        }

        // Validate CSRF token
        $token       = $_POST['_token'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';

        if (empty($token) || empty($sessionToken) || !hash_equals($sessionToken, $token)) {
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            $wantsJson = strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

            if ($isAjax || $wantsJson) {
                http_response_code(419);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Phiên làm việc hết hạn, vui lòng tải lại trang.']);
                return false;
            }

            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Phiên làm việc hết hạn, vui lòng thử lại.'];

            $referer = $_SERVER['HTTP_REFERER'] ?? '/';
            header('Location: ' . $referer);

            return false;
        }

        return true;
    }
}
