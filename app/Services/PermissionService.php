<?php

namespace App\Services;

use Core\Database;

class PermissionService
{
    private static array $cache = [];
    private static ?array $userGroupIds = null;
    private static ?bool $isSystemGroup = null;
    private static ?array $groupPerms = null;

    /**
     * Check if the current user has permission for a module/action.
     * Uses group-based system first, falls back to role-based.
     */
    public static function can(string $module, string $action): bool
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user) return false;

        $userId = $user['id'] ?? 0;
        $cacheKey = "u{$userId}:{$module}:{$action}";

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        // Check if user is in system (admin) group
        if (self::isInSystemGroup($userId)) {
            self::$cache[$cacheKey] = true;
            return true;
        }

        $groupIds = self::getUserGroupIds($userId);

        if (!empty($groupIds)) {
            // Group-based check
            $allowed = self::checkGroupPermission($groupIds, $module, $action);
        } else {
            // Legacy role-based fallback
            $role = $user['role'] ?? '';
            if ($role === 'admin') {
                self::$cache[$cacheKey] = true;
                return true;
            }
            $result = Database::fetch(
                "SELECT COUNT(*) as cnt FROM role_permissions rp JOIN permissions p ON rp.permission_id = p.id WHERE rp.role = ? AND p.module = ? AND p.action = ?",
                [$role, $module, $action]
            );
            $allowed = ($result['cnt'] ?? 0) > 0;
        }

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
     * Get group IDs for a user (cached per request).
     */
    public static function getUserGroupIds(int $userId): array
    {
        if (self::$userGroupIds !== null) return self::$userGroupIds;

        // Try session cache first
        if (!empty($_SESSION['user']['permission_group_ids'])) {
            self::$userGroupIds = $_SESSION['user']['permission_group_ids'];
            return self::$userGroupIds;
        }

        $rows = Database::fetchAll(
            "SELECT group_id FROM user_permission_groups WHERE user_id = ?",
            [$userId]
        );
        self::$userGroupIds = array_column($rows, 'group_id');

        // Cache in session
        if (isset($_SESSION['user'])) {
            $_SESSION['user']['permission_group_ids'] = self::$userGroupIds;
        }

        return self::$userGroupIds;
    }

    /**
     * Check if user belongs to a system (admin) group.
     */
    public static function isInSystemGroup(int $userId): bool
    {
        if (self::$isSystemGroup !== null) return self::$isSystemGroup;

        if (isset($_SESSION['user']['is_system_group'])) {
            self::$isSystemGroup = $_SESSION['user']['is_system_group'];
            return self::$isSystemGroup;
        }

        $groupIds = self::getUserGroupIds($userId);
        if (empty($groupIds)) {
            self::$isSystemGroup = false;
            return false;
        }

        $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
        $result = Database::fetch(
            "SELECT COUNT(*) as cnt FROM permission_groups WHERE id IN ({$placeholders}) AND is_system = 1",
            $groupIds
        );
        self::$isSystemGroup = ($result['cnt'] ?? 0) > 0;

        if (isset($_SESSION['user'])) {
            $_SESSION['user']['is_system_group'] = self::$isSystemGroup;
        }

        return self::$isSystemGroup;
    }

    /**
     * Check if any of the user's groups has a specific permission.
     * Preloads all permissions for the user's groups on first call.
     */
    private static function checkGroupPermission(array $groupIds, string $module, string $action): bool
    {
        if (self::$groupPerms === null) {
            self::$groupPerms = [];
            $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
            $rows = Database::fetchAll(
                "SELECT p.module, p.action FROM group_permissions gp JOIN permissions p ON gp.permission_id = p.id WHERE gp.group_id IN ({$placeholders})",
                $groupIds
            );
            foreach ($rows as $row) {
                self::$groupPerms[$row['module'] . ':' . $row['action']] = true;
            }
        }

        return isset(self::$groupPerms[$module . ':' . $action]);
    }

    /**
     * Get all permissions for a group as array of "module.action" strings.
     */
    public static function getPermissionsForGroup(int $groupId): array
    {
        $rows = Database::fetchAll(
            "SELECT p.module, p.action FROM group_permissions gp JOIN permissions p ON gp.permission_id = p.id WHERE gp.group_id = ?",
            [$groupId]
        );
        return array_map(fn($r) => $r['module'] . '.' . $r['action'], $rows);
    }

    /**
     * Replace all permissions for a group.
     */
    public static function updateGroupPermissions(int $groupId, array $permissionIds): void
    {
        Database::query("DELETE FROM group_permissions WHERE group_id = ?", [$groupId]);
        foreach ($permissionIds as $pid) {
            Database::insert('group_permissions', [
                'group_id' => $groupId,
                'permission_id' => (int)$pid,
            ]);
        }
        self::clearCache();
    }

    /**
     * Legacy: Get all permissions for a role.
     */
    public static function getPermissionsForRole(string $role): array
    {
        $rows = Database::fetchAll(
            "SELECT p.module, p.action FROM role_permissions rp JOIN permissions p ON rp.permission_id = p.id WHERE rp.role = ?",
            [$role]
        );
        return array_map(fn($r) => $r['module'] . '.' . $r['action'], $rows);
    }

    /**
     * Legacy: Replace all permissions for a role.
     */
    public static function updateRolePermissions(string $role, array $permissionIds): void
    {
        Database::delete('role_permissions', 'role = ?', [$role]);
        foreach ($permissionIds as $pid) {
            Database::insert('role_permissions', [
                'role' => $role,
                'permission_id' => (int)$pid,
            ]);
        }
        self::clearCache();
    }

    /**
     * Clear all caches.
     */
    public static function clearCache(): void
    {
        self::$cache = [];
        self::$userGroupIds = null;
        self::$isSystemGroup = null;
        self::$groupPerms = null;
        // Clear session cache
        unset($_SESSION['user']['permission_group_ids'], $_SESSION['user']['is_system_group']);
    }
}
