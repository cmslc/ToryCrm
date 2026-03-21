<?php

namespace App\Services;

use Core\Database;

class PermissionService
{
    /**
     * Cache of permission checks: "role:module:action" => bool
     */
    private static array $cache = [];

    /**
     * Check if the current user has permission for a module/action.
     */
    public static function can(string $module, string $action): bool
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            return false;
        }

        $role = $user['role'] ?? '';

        if ($role === 'admin') {
            return true;
        }

        $cacheKey = "{$role}:{$module}:{$action}";

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $sql = "SELECT COUNT(*) as cnt
                FROM role_permissions rp
                JOIN permissions p ON rp.permission_id = p.id
                WHERE rp.role = ? AND p.module = ? AND p.action = ?";

        $result = Database::fetch($sql, [$role, $module, $action]);
        $allowed = ($result['cnt'] ?? 0) > 0;

        self::$cache[$cacheKey] = $allowed;

        return $allowed;
    }

    /**
     * Check permission or redirect with flash message.
     */
    public static function canOrFail(string $module, string $action): void
    {
        if (!self::can($module, $action)) {
            $_SESSION['flash'] = [
                'type'    => 'danger',
                'message' => 'Bạn không có quyền thực hiện hành động này.'
            ];
            header('Location: /dashboard');
            exit;
        }
    }

    /**
     * Get all permissions for a role as array of "module.action" strings.
     */
    public static function getPermissionsForRole(string $role): array
    {
        $sql = "SELECT p.module, p.action
                FROM role_permissions rp
                JOIN permissions p ON rp.permission_id = p.id
                WHERE rp.role = ?";

        $rows = Database::fetchAll($sql, [$role]);

        $permissions = [];
        foreach ($rows as $row) {
            $permissions[] = $row['module'] . '.' . $row['action'];
        }

        return $permissions;
    }

    /**
     * Replace all permissions for a role with a new set of permission IDs.
     */
    public static function updateRolePermissions(string $role, array $permissionIds): void
    {
        Database::delete('role_permissions', 'role = ?', [$role]);

        foreach ($permissionIds as $permissionId) {
            Database::insert('role_permissions', [
                'role'          => $role,
                'permission_id' => (int) $permissionId
            ]);
        }

        // Clear cache after update
        self::$cache = [];
    }
}
