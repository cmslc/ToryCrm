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

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

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

    private static ?array $jsonBody = null;

    private function getJsonBody(): array
    {
        if (self::$jsonBody === null) {
            self::$jsonBody = [];
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (strpos($contentType, 'application/json') !== false) {
                $raw = file_get_contents('php://input');
                if ($raw) self::$jsonBody = json_decode($raw, true) ?: [];
            }
        }
        return self::$jsonBody;
    }

    protected function input(string $key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $this->getJsonBody()[$key] ?? $default;
    }

    protected function allInput(): array
    {
        return array_merge($_GET, $_POST, $this->getJsonBody());
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

    /**
     * Check if current user is admin or manager (can see all data).
     */
    protected function isAdminOrManager(): bool
    {
        $userId = $_SESSION['user']['id'] ?? 0;
        // New group-based: check if in system group
        if (\App\Services\PermissionService::isInSystemGroup($userId)) {
            return true;
        }
        // Fallback to role
        $role = $_SESSION['user']['role'] ?? 'staff';
        return in_array($role, ['admin', 'manager']);
    }

    /**
     * True only for system admin group, not regular managers.
     */
    protected function isSystemAdmin(): bool
    {
        return \App\Services\PermissionService::isInSystemGroup($_SESSION['user']['id'] ?? 0);
    }

    /**
     * SQL suffix for owner-based filter in raw queries.
     * Admin: "", others: " AND col IN (1,2,3)"
     */
    protected function getOwnerScopeSql(string $col = 'owner_id'): string
    {
        if ($this->isSystemAdmin()) return '';
        $ids = $this->getVisibleUserIds();
        if ($ids && count($ids) > 0) {
            return " AND {$col} IN (" . implode(',', array_map('intval', $ids)) . ")";
        }
        return " AND {$col} = " . (int)$this->userId();
    }

    /**
     * Check if current user is department head (trưởng/phó phòng).
     * Returns array of user IDs in their department, or null if not a dept head.
     */
    /**
     * Get all user IDs this user can see data of.
     * Manager/vice_manager: own dept + child depts.
     * Staff: own dept members only.
     */
    protected function getVisibleUserIds(): ?array
    {
        static $visCache = null;
        if ($visCache !== null) return $visCache ?: null;

        $uid = $this->userId();
        $deptId = $_SESSION['user']['department_id'] ?? null;
        if (!$deptId) {
            try {
                $u = Database::fetch("SELECT department_id FROM users WHERE id = ?", [$uid]);
                $deptId = $u['department_id'] ?? null;
                $_SESSION['user']['department_id'] = $deptId;
            } catch (\Exception $e) {}
        }

        if (!$deptId) { $visCache = false; return null; }

        try {
            $dept = Database::fetch("SELECT manager_id, vice_manager_id FROM departments WHERE id = ?", [$deptId]);

            $deptIds = [(int)$deptId];

            // If manager/vice_manager, include child depts
            if ($dept && ($dept['manager_id'] == $uid || $dept['vice_manager_id'] == $uid)) {
                $childIds = $this->getChildDeptIds($deptId);
                $deptIds = array_merge($deptIds, $childIds);
            }

            $placeholders = implode(',', array_fill(0, count($deptIds), '?'));
            $members = Database::fetchAll(
                "SELECT id FROM users WHERE department_id IN ({$placeholders}) AND is_active = 1",
                $deptIds
            );
            $visCache = array_column($members, 'id');

            // Make sure own ID is included
            if (!in_array($uid, $visCache)) $visCache[] = $uid;

            return $visCache;
        } catch (\Exception $e) {}

        $visCache = false;
        return null;
    }

    protected function getDeptMemberIds(): ?array
    {
        static $cache = null;
        if ($cache !== null) return $cache ?: null;

        $uid = $this->userId();
        $deptId = $_SESSION['user']['department_id'] ?? null;
        if (!$deptId) {
            try {
                $u = Database::fetch("SELECT department_id FROM users WHERE id = ?", [$uid]);
                $deptId = $u['department_id'] ?? null;
                $_SESSION['user']['department_id'] = $deptId;
            } catch (\Exception $e) {}
        }

        if (!$deptId) { $cache = false; return null; }

        // Check if user is manager or vice_manager of their department
        try {
            $dept = Database::fetch(
                "SELECT manager_id, vice_manager_id FROM departments WHERE id = ?",
                [$deptId]
            );
            if (!$dept) { $cache = false; return null; }

            if ($dept['manager_id'] == $uid || $dept['vice_manager_id'] == $uid) {
                // Collect this dept + all child depts (recursive)
                $deptIds = $this->getChildDeptIds($deptId);
                $deptIds[] = (int)$deptId;

                $placeholders = implode(',', array_fill(0, count($deptIds), '?'));
                $members = Database::fetchAll(
                    "SELECT id FROM users WHERE department_id IN ({$placeholders}) AND is_active = 1",
                    $deptIds
                );
                $cache = array_column($members, 'id');
                return $cache;
            }
        } catch (\Exception $e) {}

        $cache = false;
        return null;
    }

    /**
     * Get all child department IDs recursively.
     */
    private function getChildDeptIds(int $parentId): array
    {
        $allDepts = Database::fetchAll("SELECT id, parent_id FROM departments");
        $childMap = [];
        foreach ($allDepts as $d) {
            if ($d['parent_id']) {
                $childMap[(int)$d['parent_id']][] = (int)$d['id'];
            }
        }

        $result = [];
        $queue = $childMap[$parentId] ?? [];
        while (!empty($queue)) {
            $id = array_shift($queue);
            $result[] = $id;
            if (isset($childMap[$id])) {
                foreach ($childMap[$id] as $childId) {
                    $queue[] = $childId;
                }
            }
        }
        return $result;
    }

    /**
     * Get owner scope SQL for list queries.
     * Admin/Manager role: no filter (see all).
     * Dept head (trưởng/phó phòng): see department members' data.
     * Staff: only own records.
     * Returns ['where' => string, 'params' => array]
     */
    protected function ownerScope(string $alias = '', string $ownerField = 'owner_id', string $module = ''): array
    {
        // Only system group (admin) sees everything
        $userId = $_SESSION['user']['id'] ?? 0;
        if (\App\Services\PermissionService::isInSystemGroup($userId)) {
            return ['where' => '', 'params' => []];
        }

        // Check view_all permission for specific module
        if ($module && \App\Services\PermissionService::can($module, 'view_all')) {
            return ['where' => '', 'params' => []];
        }

        $col = $alias ? "{$alias}.{$ownerField}" : $ownerField;

        // Get visible user IDs based on department hierarchy
        $visibleIds = $this->getVisibleUserIds();
        if ($visibleIds && count($visibleIds) > 0) {
            $placeholders = implode(',', array_fill(0, count($visibleIds), '?'));
            return [
                'where' => "{$col} IN ({$placeholders})",
                'params' => $visibleIds,
            ];
        }

        // Fallback: own data only
        return [
            'where' => "{$col} = ?",
            'params' => [$this->userId()],
        ];
    }

    /**
     * Check if current user can access a record owned by $ownerId.
     * Admin/Manager: always. Dept head: if owner is in same dept. Staff: only own.
     */
    protected function canAccessOwner(?int $ownerId): bool
    {
        $userId = $_SESSION['user']['id'] ?? 0;
        if (\App\Services\PermissionService::isInSystemGroup($userId)) return true;
        if ($ownerId == $this->userId()) return true;
        $visible = $this->getVisibleUserIds();
        if ($visible && in_array($ownerId, $visible)) return true;
        return false;
    }

    /**
     * Check access via owner, department hierarchy, OR followers.
     */
    protected function canAccessEntity(string $entityType, int $entityId, ?int $ownerId): bool
    {
        if ($this->canAccessOwner($ownerId)) return true;

        $table = $entityType . '_followers';
        $column = $entityType . '_id';
        try {
            return (bool)Database::fetch(
                "SELECT 1 FROM {$table} WHERE {$column} = ? AND user_id = ?",
                [$entityId, $this->userId()]
            );
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get users list filtered by visibility (same dept / child depts).
     * Admin sees all, others see only their dept scope.
     */
    protected function getVisibleUsers(): array
    {
        $userId = $_SESSION['user']['id'] ?? 0;
        $tid = Database::tenantId();

        if (\App\Services\PermissionService::isInSystemGroup($userId)) {
            return Database::fetchAll("SELECT id, name FROM users WHERE is_active = 1 AND tenant_id = ? ORDER BY name", [$tid]);
        }

        $visibleIds = $this->getVisibleUserIds();
        if ($visibleIds && count($visibleIds) > 0) {
            $placeholders = implode(',', array_fill(0, count($visibleIds), '?'));
            return Database::fetchAll("SELECT id, name FROM users WHERE id IN ({$placeholders}) AND is_active = 1 ORDER BY name", $visibleIds);
        }

        return Database::fetchAll("SELECT id, name FROM users WHERE id = ?", [$userId]);
    }

    /**
     * Get visible users with avatar info.
     */
    protected function getVisibleUsersWithAvatar(): array
    {
        $userId = $_SESSION['user']['id'] ?? 0;
        $tid = Database::tenantId();

        if (\App\Services\PermissionService::isInSystemGroup($userId)) {
            return Database::fetchAll("SELECT id, name, avatar FROM users WHERE is_active = 1 AND tenant_id = ? ORDER BY name", [$tid]);
        }

        $visibleIds = $this->getVisibleUserIds();
        if ($visibleIds && count($visibleIds) > 0) {
            $placeholders = implode(',', array_fill(0, count($visibleIds), '?'));
            return Database::fetchAll("SELECT id, name, avatar FROM users WHERE id IN ({$placeholders}) AND is_active = 1 ORDER BY name", $visibleIds);
        }

        return Database::fetchAll("SELECT id, name, avatar FROM users WHERE id = ?", [$userId]);
    }

    /**
     * Check permission. Redirects with error if denied.
     */
    protected function authorize(string $module, string $action): void
    {
        \App\Services\PermissionService::canOrFail($module, $action);
    }

    /**
     * Check permission. Returns bool without redirect.
     */
    protected function can(string $module, string $action): bool
    {
        return \App\Services\PermissionService::can($module, $action);
    }

    /**
     * Find a record by ID with automatic tenant scope.
     * Returns null if not found or belongs to different tenant.
     */
    protected function findSecure(string $table, int $id, string $alias = ''): ?array
    {
        $prefix = $alias ? "{$alias}." : '';
        $conditions = ["{$prefix}id = ?"];
        $params = [$id];

        // Add tenant scope for tenant-scoped tables
        if (Database::isTenantScoped($table)) {
            $conditions[] = "{$prefix}tenant_id = ?";
            $params[] = $this->tenantId();
        }

        // Add soft delete filter
        if (Database::hasSoftDelete($table)) {
            $conditions[] = "{$prefix}is_deleted = 0";
        }

        $where = implode(' AND ', $conditions);
        return Database::fetch("SELECT * FROM `{$table}` WHERE {$where}", $params);
    }

    /**
     * Sanitize orderBy to prevent SQL injection.
     * Only allows column names from whitelist.
     */
    protected function sanitizeOrderBy(string $orderBy, array $allowedColumns = ['id', 'created_at', 'updated_at', 'name', 'title']): string
    {
        $parts = explode(' ', trim($orderBy));
        $column = preg_replace('/[^a-zA-Z0-9_.]/', '', $parts[0] ?? 'id');
        $direction = strtoupper($parts[1] ?? 'DESC');

        if (!in_array($direction, ['ASC', 'DESC'])) $direction = 'DESC';

        // Extract base column name (remove alias prefix like "c.")
        $baseColumn = str_contains($column, '.') ? explode('.', $column)[1] : $column;

        if (!in_array($baseColumn, $allowedColumns)) {
            return 'id DESC';
        }

        return "{$column} {$direction}";
    }
}
