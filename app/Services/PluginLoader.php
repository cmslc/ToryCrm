<?php

namespace App\Services;

/**
 * WordPress-style Plugin Loader.
 *
 * Convention per plugin:
 *   plugins/
 *   └── <slug>/
 *       ├── <slug>.php          Main file with header comment (required)
 *       ├── routes.php          Route registrations (optional — auto-loaded)
 *       ├── Service.php         One service class (optional — auto-loaded)
 *       ├── Controllers/*.php   All controllers (optional — auto-loaded)
 *       ├── views/              Plugin views (rendered via view('plugin:<slug>.<file>', ...))
 *       └── migrations/*.sql    Plugin schema (run manually or via seeder)
 */
class PluginLoader
{
    private static array $loaded = [];
    private static array $viewPaths = [];

    /**
     * Scan and load all active plugins (controllers + service + main file).
     * Routes must be loaded separately via loadRoutes() after the main routes file.
     */
    public static function loadAll(): void
    {
        $pluginsDir = BASE_PATH . '/plugins';
        if (!is_dir($pluginsDir)) return;

        $dirs = glob($pluginsDir . '/*', GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            $slug = basename($dir);
            $mainFile = $dir . '/' . $slug . '.php';

            if (!file_exists($mainFile)) continue;
            if (isset(self::$loaded[$slug])) continue;

            // Only load if plugin is active
            if (function_exists('plugin_active') && !plugin_active($slug)) continue;

            // Autoload Service.php (legacy single-service convention)
            $serviceFile = $dir . '/Service.php';
            if (file_exists($serviceFile)) require_once $serviceFile;

            // Autoload controllers from Controllers/*.php
            $controllersDir = $dir . '/Controllers';
            if (is_dir($controllersDir)) {
                foreach (glob($controllersDir . '/*.php') as $ctrl) {
                    require_once $ctrl;
                }
            }

            // Register view path for this plugin
            self::$viewPaths[$slug] = $dir . '/views';

            // Main plugin file (hooks, global functions, one-off setup)
            require_once $mainFile;
            self::$loaded[$slug] = true;
        }
    }

    /**
     * Register routes from each active plugin's routes.php.
     * Call this AFTER the main routes/web.php has been loaded.
     */
    public static function loadRoutes(): void
    {
        foreach (self::$loaded as $slug => $_) {
            $routesFile = self::path($slug) . '/routes.php';
            if (file_exists($routesFile)) require $routesFile;
        }
    }

    /** Resolve a plugin view path or return null if not found. */
    public static function resolveView(string $slug, string $view): ?string
    {
        if (!isset(self::$viewPaths[$slug])) return null;
        $path = self::$viewPaths[$slug] . '/' . str_replace('.', '/', $view) . '.php';
        return file_exists($path) ? $path : null;
    }

    public static function path(string $slug): string
    {
        return BASE_PATH . '/plugins/' . $slug;
    }

    public static function viewPath(string $slug, string $view): string
    {
        return self::path($slug) . '/views/' . $view . '.php';
    }

    public static function getMeta(string $slug): array
    {
        $mainFile = self::path($slug) . '/' . $slug . '.php';
        if (!file_exists($mainFile)) return [];

        $content = file_get_contents($mainFile, false, null, 0, 2048);
        $headers = [
            'name' => 'Plugin Name', 'description' => 'Description', 'version' => 'Version',
            'author' => 'Author', 'icon' => 'Icon', 'category' => 'Category',
            'slug' => 'Slug', 'modules' => 'Modules',
        ];

        $meta = [];
        foreach ($headers as $key => $label) {
            if (preg_match('/\*\s*' . preg_quote($label) . ':\s*(.+)/i', $content, $m)) {
                $meta[$key] = trim($m[1]);
            }
        }
        return $meta;
    }

    public static function discover(): array
    {
        $pluginsDir = BASE_PATH . '/plugins';
        if (!is_dir($pluginsDir)) return [];

        $plugins = [];
        foreach (glob($pluginsDir . '/*', GLOB_ONLYDIR) as $dir) {
            $slug = basename($dir);
            $mainFile = $dir . '/' . $slug . '.php';
            if (!file_exists($mainFile)) continue;

            $meta = self::getMeta($slug);
            $meta['slug'] = $slug;
            $meta['path'] = $dir;
            $meta['active'] = function_exists('plugin_active') && plugin_active($slug);
            $plugins[$slug] = $meta;
        }
        return $plugins;
    }
}
