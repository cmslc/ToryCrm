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

        // Load routes
        require BASE_PATH . '/routes/web.php';
        require BASE_PATH . '/routes/api.php';

        $router->dispatch();
    }
}
