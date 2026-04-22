<?php

namespace Core;

class Application
{
    private array $config;
    private static ?Application $instance = null;

    public function __construct(array $config)
    {
        $this->config = $config;
        self::$instance = $this;

        date_default_timezone_set($config['timezone'] ?? 'Asia/Ho_Chi_Minh');

        // Load helper functions
        require_once BASE_PATH . '/app/Helpers/helpers.php';

        Database::init($config['database']);

        // Apply tenant-specific timezone (overrides default from config)
        $this->applyTenantTimezone();

        // Enforce tenant-configured session idle timeout
        $this->enforceSessionTimeout();

        // Load plugins (WordPress-style)
        \App\Services\PluginLoader::loadAll();
    }

    private function enforceSessionTimeout(): void
    {
        if (empty($_SESSION['user'])) return;

        $idleMinutes = (int) tenant_setting('session_timeout', 120);
        $idleMinutes = max(5, min(1440, $idleMinutes));
        $idleMax = $idleMinutes * 60;
        $absoluteMax = 28800; // 8h hard cap

        $now = time();
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

    private function applyTenantTimezone(): void
    {
        if (empty($_SESSION['tenant_id'])) return;
        try {
            $tz = tenant_setting('timezone', null);
            if ($tz && in_array($tz, \DateTimeZone::listIdentifiers(), true)) {
                date_default_timezone_set($tz);
                // Sync MySQL session time_zone so NOW(), CURDATE() match PHP
                $offset = (new \DateTime('now', new \DateTimeZone($tz)))->format('P');
                Database::query("SET time_zone = ?", [$offset]);
            }
        } catch (\Throwable $e) {
            // Silent — keep default timezone
        }
    }

    public static function getInstance(): ?Application
    {
        return self::$instance;
    }

    public function getConfig(?string $key = null, $default = null)
    {
        if ($key === null) return $this->config;
        return $this->config[$key] ?? $default;
    }

    public function run(): void
    {
        $router = new Router();

        // Load routes (main + plugins)
        require BASE_PATH . '/routes/web.php';
        require BASE_PATH . '/routes/api.php';
        \App\Services\PluginLoader::loadRoutes();

        $router->dispatch();
    }
}
