<?php

namespace Core;

class Controller
{
    protected function view(string $view, array $data = []): void
    {
        extract($data);

        $viewPath = BASE_PATH . '/resources/views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            die("View not found: {$view}");
        }

        // Start output buffering for content
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // If view uses layout
        $layoutPath = BASE_PATH . '/resources/views/layouts/app.php';
        if (file_exists($layoutPath) && !isset($noLayout)) {
            require $layoutPath;
        } else {
            echo $content;
        }
    }

    protected function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    protected function redirect(string $url): void
    {
        $url = '/' . ltrim($url, '/');
        header("Location: {$url}");
        exit;
    }

    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        header("Location: {$referer}");
        exit;
    }

    protected function input(string $key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function allInput(): array
    {
        return array_merge($_GET, $_POST);
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    protected function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    protected function userId(): ?int
    {
        return $_SESSION['user']['id'] ?? null;
    }

    protected function tenantId(): int
    {
        return (int) ($_SESSION['tenant_id'] ?? 1);
    }
}
