<?php

namespace App\Services;

use Core\Database;

class BrandingService
{
    private static ?array $cached = null;

    private static array $defaults = [
        'name' => 'ToryCRM',
        'logo_url' => '',
        'favicon_url' => '',
        'primary_color' => '#405189',
        'sidebar_color' => '',
        'login_bg' => '',
        'custom_css' => '',
    ];

    /**
     * Get branding settings for current tenant (cached)
     */
    public static function get(): array
    {
        if (self::$cached !== null) {
            return self::$cached;
        }

        $tenantId = (int) ($_SESSION['tenant_id'] ?? 1);

        try {
            $tenant = Database::fetch(
                "SELECT settings FROM tenants WHERE id = ?",
                [$tenantId]
            );

            if ($tenant && !empty($tenant['settings'])) {
                $settings = json_decode($tenant['settings'], true) ?: [];
                $branding = $settings['branding'] ?? [];
                self::$cached = array_merge(self::$defaults, $branding);
            } else {
                self::$cached = self::$defaults;
            }
        } catch (\Throwable $e) {
            self::$cached = self::$defaults;
        }

        return self::$cached;
    }

    /**
     * Save branding settings to tenant
     */
    public static function save(array $data): bool
    {
        $tenantId = (int) ($_SESSION['tenant_id'] ?? 1);

        $branding = [];
        foreach (self::$defaults as $key => $default) {
            $branding[$key] = $data[$key] ?? $default;
        }

        try {
            $tenant = Database::fetch(
                "SELECT settings FROM tenants WHERE id = ?",
                [$tenantId]
            );

            $settings = [];
            if ($tenant && !empty($tenant['settings'])) {
                $settings = json_decode($tenant['settings'], true) ?: [];
            }

            $settings['branding'] = $branding;

            Database::update('tenants', [
                'settings' => json_encode($settings, JSON_UNESCAPED_UNICODE),
            ], 'id = ?', [$tenantId]);

            // Clear cache
            self::$cached = null;

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get CSS variables string from branding settings
     */
    public static function getCssVariables(): string
    {
        $branding = self::get();
        $css = '';

        if (!empty($branding['primary_color']) && $branding['primary_color'] !== '#405189') {
            $css .= ":root { --vz-primary: {$branding['primary_color']}; --vz-primary-rgb: " . self::hexToRgb($branding['primary_color']) . "; }\n";
        }

        if (!empty($branding['sidebar_color'])) {
            $css .= "[data-sidebar=dark] .app-menu { background-color: {$branding['sidebar_color']} !important; }\n";
        }

        if (!empty($branding['custom_css'])) {
            $css .= $branding['custom_css'] . "\n";
        }

        return $css;
    }

    /**
     * Convert hex color to RGB string
     */
    private static function hexToRgb(string $hex): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return "{$r}, {$g}, {$b}";
    }
}
