<?php

namespace App\Services;

use Core\Database;

class PluginManager
{
    /**
     * Get all available plugins
     */
    public static function getAvailable(): array
    {
        return Database::fetchAll("SELECT * FROM plugins ORDER BY category, name");
    }

    /**
     * Get installed plugins for a tenant
     */
    public static function getInstalled(int $tenantId): array
    {
        return Database::fetchAll(
            "SELECT p.*, tp.is_active as tenant_active, tp.config as tenant_config, tp.installed_at as tenant_installed_at
             FROM plugins p
             INNER JOIN tenant_plugins tp ON tp.plugin_id = p.id
             WHERE tp.tenant_id = ?
             ORDER BY tp.installed_at DESC",
            [$tenantId]
        );
    }

    /**
     * Install a plugin for a tenant
     */
    public static function install(int $tenantId, int $pluginId): bool
    {
        $plugin = Database::fetch("SELECT id FROM plugins WHERE id = ?", [$pluginId]);
        if (!$plugin) return false;

        $exists = Database::fetch(
            "SELECT plugin_id FROM tenant_plugins WHERE tenant_id = ? AND plugin_id = ?",
            [$tenantId, $pluginId]
        );
        if ($exists) return false;

        Database::insert('tenant_plugins', [
            'tenant_id' => $tenantId,
            'plugin_id' => $pluginId,
            'is_active' => 1,
            'installed_at' => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    /**
     * Uninstall a plugin for a tenant
     */
    public static function uninstall(int $tenantId, int $pluginId): bool
    {
        Database::delete('tenant_plugins', 'tenant_id = ? AND plugin_id = ?', [$tenantId, $pluginId]);
        return true;
    }

    /**
     * Toggle plugin active status for a tenant
     */
    public static function toggleActive(int $tenantId, int $pluginId): bool
    {
        $record = Database::fetch(
            "SELECT is_active FROM tenant_plugins WHERE tenant_id = ? AND plugin_id = ?",
            [$tenantId, $pluginId]
        );
        if (!$record) return false;

        $newStatus = $record['is_active'] ? 0 : 1;
        Database::query(
            "UPDATE tenant_plugins SET is_active = ? WHERE tenant_id = ? AND plugin_id = ?",
            [$newStatus, $tenantId, $pluginId]
        );

        return true;
    }

    /**
     * Get plugin config for a tenant by plugin slug
     */
    public static function getConfig(int $tenantId, string $pluginSlug): ?array
    {
        $row = Database::fetch(
            "SELECT tp.config as tenant_config, p.config as default_config
             FROM tenant_plugins tp
             INNER JOIN plugins p ON p.id = tp.plugin_id
             WHERE tp.tenant_id = ? AND p.slug = ?",
            [$tenantId, $pluginSlug]
        );

        if (!$row) return null;

        $tenantConfig = json_decode($row['tenant_config'] ?? '{}', true) ?: [];
        return $tenantConfig;
    }

    /**
     * Save plugin config for a tenant
     */
    public static function saveConfig(int $tenantId, int $pluginId, array $config): bool
    {
        Database::query(
            "UPDATE tenant_plugins SET config = ? WHERE tenant_id = ? AND plugin_id = ?",
            [json_encode($config, JSON_UNESCAPED_UNICODE), $tenantId, $pluginId]
        );

        return true;
    }

    /**
     * Check if a plugin is installed for a tenant
     */
    public static function isInstalled(int $tenantId, int $pluginId): bool
    {
        $row = Database::fetch(
            "SELECT plugin_id FROM tenant_plugins WHERE tenant_id = ? AND plugin_id = ?",
            [$tenantId, $pluginId]
        );
        return (bool) $row;
    }

    /**
     * Get installed plugin IDs for a tenant
     */
    public static function getInstalledIds(int $tenantId): array
    {
        $rows = Database::fetchAll(
            "SELECT plugin_id FROM tenant_plugins WHERE tenant_id = ?",
            [$tenantId]
        );
        return array_column($rows, 'plugin_id');
    }
}
