<?php

namespace App\Services;

use Core\Database;

class PermissionService
{
    private static array $cache = [];
    private static ?array $userGroupIds = null;
    private static ?bool $isSystemGroup = null;
    private static ?array $groupPerms = null;
    private static array $ancestorCache = [];

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

        // Check if permissions were updated since last cache
        if (isset($_SESSION['user']['is_system_group'])) {
            try {
                $updated = Database::fetch("SELECT value FROM tenant_settings WHERE setting_key = 'permissions_updated_at' AND tenant_id = ?", [Database::tenantId()]);
                $cachedAt = $_SESSION['user']['perms_cached_at'] ?? 0;
                if ($updated && (int)$updated['value'] > (int)$cachedAt) {
                    unset($_SESSION['user']['is_system_group'], $_SESSION['user']['permission_group_ids']);
                } else {
                    self::$isSystemGroup = $_SESSION['user']['is_system_group'];
                    return self::$isSystemGroup;
                }
            } catch (\Exception $e) {
                self::$isSystemGroup = $_SESSION['user']['is_system_group'];
                return self::$isSystemGroup;
            }
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
            $_SESSION['user']['perms_cached_at'] = time();
        }

        return self::$isSystemGroup;
    }

    /**
     * Check if any of the user's groups (or their ancestor groups) has a specific permission.
     * Preloads all permissions for the user's groups and ancestors on first call.
     */
    private static function checkGroupPermission(array $groupIds, string $module, string $action): bool
    {
        if (self::$groupPerms === null) {
            self::$groupPerms = [];
            // Only check direct group membership, no ancestor inheritance
            // parent_id is for organizational tree display only
            if (empty($groupIds)) {
                return false;
            }
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
     * Expand group IDs to include all ancestor groups (parent, grandparent, etc.).
     * Results are cached per-request via static property.
     *
     * @param array $groupIds Direct group IDs
     * @return array Expanded list including all ancestor group IDs
     */
    public static function getGroupWithAncestors(array $groupIds): array
    {
        if (empty($groupIds)) {
            return [];
        }

        // Check which group IDs still need ancestor resolution
        $toResolve = [];
        $result = [];
        foreach ($groupIds as $gid) {
            $gid = (int)$gid;
            if (isset(self::$ancestorCache[$gid])) {
                foreach (self::$ancestorCache[$gid] as $id) {
                    $result[$id] = true;
                }
            } else {
                $toResolve[] = $gid;
                $result[$gid] = true;
            }
        }

        if (!empty($toResolve)) {
            // Load parent_id map for all groups in one query
            $allGroups = Database::fetchAll(
                "SELECT id, parent_id FROM permission_groups"
            );
            $parentMap = [];
            foreach ($allGroups as $row) {
                $parentMap[(int)$row['id']] = $row['parent_id'] ? (int)$row['parent_id'] : null;
            }

            // Resolve ancestors for each group that needs it
            foreach ($toResolve as $gid) {
                $chain = [$gid];
                $current = $gid;
                $visited = [$gid => true];
                while (isset($parentMap[$current]) && $parentMap[$current] !== null) {
                    $parent = $parentMap[$current];
                    if (isset($visited[$parent])) {
                        break; // prevent circular references
                    }
                    $chain[] = $parent;
                    $visited[$parent] = true;
                    $result[$parent] = true;
                    $current = $parent;
                }
                self::$ancestorCache[$gid] = $chain;
            }
        }

        return array_keys($result);
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
        foreach (array_unique($permissionIds) as $pid) {
            Database::query(
                "INSERT IGNORE INTO group_permissions (group_id, permission_id) VALUES (?, ?)",
                [$groupId, (int)$pid]
            );
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
        self::$ancestorCache = [];
        // Clear session cache for current user
        unset($_SESSION['user']['permission_group_ids'], $_SESSION['user']['is_system_group']);
        // Invalidate all permission caches by updating global timestamp
        try {
            Database::query("UPDATE tenant_settings SET value = ? WHERE setting_key = 'permissions_updated_at' AND tenant_id = ?", [time(), Database::tenantId()]);
            if (Database::fetch("SELECT 1 FROM tenant_settings WHERE setting_key = 'permissions_updated_at' AND tenant_id = ?", [Database::tenantId()]) === false) {
                Database::query("INSERT IGNORE INTO tenant_settings (tenant_id, setting_key, value) VALUES (?, 'permissions_updated_at', ?)", [Database::tenantId(), time()]);
            }
        } catch (\Exception $e) {
            // tenant_settings table may not exist yet
        }
    }
}
