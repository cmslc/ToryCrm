<?php

namespace App\Services;

/**
 * WordPress-style Plugin Loader
 *
 * Scans plugins/ directory, loads active plugins' main file.
 * Each plugin folder must have a main .php file with the same name as the folder.
 *
 * Structure:
 *   plugins/
 *   ├── activity-exchange/
 *   │   ├── activity-exchange.php   (main file with header comment)
 *   │   ├── Service.php
 *   │   └── views/
 *   │       └── feed.php
 *   └── another-plugin/
 *       └── another-plugin.php
 */
class PluginLoader
{
    private static array $loaded = [];

    /**
     * Scan and load all active plugins.
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

            // Autoload Service.php if exists
            $serviceFile = $dir . '/Service.php';
            if (file_exists($serviceFile)) {
                require_once $serviceFile;
            }

            // Load main plugin file
            require_once $mainFile;
            self::$loaded[$slug] = true;
        }
    }

    /**
     * Get plugin path.
     */
    public static function path(string $slug): string
    {
        return BASE_PATH . '/plugins/' . $slug;
    }

    /**
     * Get plugin view path.
     */
    public static function viewPath(string $slug, string $view): string
    {
        return self::path($slug) . '/views/' . $view . '.php';
    }

    /**
     * Get parsed plugin metadata from header comment.
     */
    public static function getMeta(string $slug): array
    {
        $mainFile = self::path($slug) . '/' . $slug . '.php';
        if (!file_exists($mainFile)) return [];

        $content = file_get_contents($mainFile, false, null, 0, 2048);
        $headers = [
            'name' => 'Plugin Name',
            'description' => 'Description',
            'version' => 'Version',
            'author' => 'Author',
            'icon' => 'Icon',
            'category' => 'Category',
            'slug' => 'Slug',
            'modules' => 'Modules',
        ];

        $meta = [];
        foreach ($headers as $key => $label) {
            if (preg_match('/\*\s*' . preg_quote($label) . ':\s*(.+)/i', $content, $m)) {
                $meta[$key] = trim($m[1]);
            }
        }
        return $meta;
    }

    /**
     * List all discovered plugins (active or not).
     */
    public static function discover(): array
    {
        $pluginsDir = BASE_PATH . '/plugins';
        if (!is_dir($pluginsDir)) return [];

        $plugins = [];
        $dirs = glob($pluginsDir . '/*', GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
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
