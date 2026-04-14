<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Services\PermissionService;

class PermissionGroupController extends Controller
{
    public function index()
    {
        $this->authorize('settings', 'manage');
        $tid = Database::tenantId();

        $groups = Database::fetchAll(
            "SELECT pg.*, (SELECT COUNT(*) FROM user_permission_groups upg WHERE upg.group_id = pg.id) as user_count
             FROM permission_groups pg WHERE pg.tenant_id = ? ORDER BY pg.sort_order, pg.name",
            [$tid]
        );

        // Build tree
        $tree = [];
        $byId = [];
        foreach ($groups as &$g) { $g['children'] = []; $byId[$g['id']] = &$g; }
        unset($g);
        foreach ($groups as &$g) {
            if ($g['parent_id'] && isset($byId[$g['parent_id']])) {
                $byId[$g['parent_id']]['children'][] = &$g;
            } else {
                $tree[] = &$g;
            }
        }
        unset($g);

        $permissions = Database::fetchAll("SELECT * FROM permissions ORDER BY module, FIELD(action, 'view','create','edit','delete','approve','view_all')");

        // Group permissions by module
        $modules = [];
        foreach ($permissions as $p) {
            $modules[$p['module']][] = $p;
        }

        $allUsers = Database::fetchAll(
            "SELECT u.id, u.name, u.avatar, u.email, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name"
        );

        // First group or selected
        $selectedGroupId = $this->input('group') ?: ($groups[0]['id'] ?? 0);
        $selectedGroup = $byId[$selectedGroupId] ?? null;

        $groupPermIds = [];
        $groupUsers = [];
        if ($selectedGroup) {
            $gp = Database::fetchAll("SELECT permission_id FROM group_permissions WHERE group_id = ?", [$selectedGroupId]);
            $groupPermIds = array_column($gp, 'permission_id');

            $groupUsers = Database::fetchAll(
                "SELECT u.id, u.name, u.avatar, u.email FROM users u JOIN user_permission_groups upg ON u.id = upg.user_id WHERE upg.group_id = ?",
                [$selectedGroupId]
            );
        }

        // User-group mappings for the user list tab
        $userGroupMap = [];
        $ugRows = Database::fetchAll(
            "SELECT upg.user_id, pg.id as group_id, pg.name, pg.color FROM user_permission_groups upg JOIN permission_groups pg ON upg.group_id = pg.id WHERE pg.tenant_id = ?",
            [$tid]
        );
        foreach ($ugRows as $row) {
            $userGroupMap[$row['user_id']][] = $row;
        }

        return $this->view('settings.permission-groups', [
            'tree' => $tree,
            'groups' => $groups,
            'modules' => $modules,
            'allUsers' => $allUsers,
            'selectedGroup' => $selectedGroup,
            'selectedGroupId' => (int)$selectedGroupId,
            'groupPermIds' => $groupPermIds,
            'groupUsers' => $groupUsers,
            'userGroupMap' => $userGroupMap,
        ]);
    }

    public function store()
    {
        if (!$this->isPost()) return $this->redirect('settings/permissions');
        $this->authorize('settings', 'manage');

        $name = trim($this->input('name') ?? '');
        if (empty($name)) {
            $this->setFlash('error', 'Tên nhóm không được để trống.');
            return $this->back();
        }

        $slug = preg_replace('/[^a-z0-9-]/', '-', strtolower($this->removeVietnamese($name)));

        Database::insert('permission_groups', [
            'tenant_id' => Database::tenantId(),
            'name' => $name,
            'slug' => $slug,
            'parent_id' => $this->input('parent_id') ?: null,
            'description' => trim($this->input('description') ?? ''),
            'color' => $this->input('color') ?: '#405189',
        ]);

        Database::insert('activities', [
            'type' => 'system',
            'title' => "Tạo nhóm quyền: $name",
            'user_id' => $this->userId(),
            'tenant_id' => Database::tenantId(),
        ]);

        $this->setFlash('success', 'Đã tạo nhóm quyền.');
        return $this->redirect('settings/permissions');
    }

    public function update($id)
    {
        if (!$this->isPost()) return $this->redirect('settings/permissions');
        $this->authorize('settings', 'manage');

        $group = Database::fetch("SELECT * FROM permission_groups WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$group) return $this->redirect('settings/permissions');

        $name = trim($this->input('name') ?? '');
        if (empty($name)) {
            $this->setFlash('error', 'Tên nhóm không được để trống.');
            return $this->back();
        }

        Database::update('permission_groups', [
            'name' => $name,
            'parent_id' => $this->input('parent_id') ?: null,
            'description' => trim($this->input('description') ?? ''),
            'color' => $this->input('color') ?: $group['color'],
        ], 'id = ?', [$id]);

        Database::insert('activities', [
            'type' => 'system',
            'title' => "Cập nhật nhóm quyền: $name",
            'user_id' => $this->userId(),
            'tenant_id' => Database::tenantId(),
        ]);

        $this->setFlash('success', 'Đã cập nhật nhóm quyền.');
        return $this->redirect('settings/permissions?group=' . $id);
    }

    public function destroy($id)
    {
        if (!$this->isPost()) return $this->redirect('settings/permissions');
        $this->authorize('settings', 'manage');

        $group = Database::fetch("SELECT * FROM permission_groups WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$group || $group['is_system']) {
            $this->setFlash('error', 'Không thể xóa nhóm hệ thống.');
            return $this->redirect('settings/permissions');
        }

        // Remove user assignments
        Database::query("DELETE FROM user_permission_groups WHERE group_id = ?", [$id]);
        // Remove permissions
        Database::query("DELETE FROM group_permissions WHERE group_id = ?", [$id]);
        // Set children parent to null
        Database::query("UPDATE permission_groups SET parent_id = NULL WHERE parent_id = ?", [$id]);
        // Delete group
        Database::query("DELETE FROM permission_groups WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);

        Database::insert('activities', [
            'type' => 'system',
            'title' => "Xóa nhóm quyền: " . $group['name'],
            'user_id' => $this->userId(),
            'tenant_id' => Database::tenantId(),
        ]);

        PermissionService::clearCache();
        $this->setFlash('success', 'Đã xóa nhóm quyền.');
        return $this->redirect('settings/permissions');
    }

    public function savePermissions($id)
    {
        if (!$this->isPost()) return $this->redirect('settings/permissions');
        $this->authorize('settings', 'manage');

        $permIds = $this->input('perms') ?? [];
        if (!is_array($permIds)) $permIds = [];

        PermissionService::updateGroupPermissions((int)$id, $permIds);

        $group = Database::fetch("SELECT name FROM permission_groups WHERE id = ?", [$id]);
        Database::insert('activities', [
            'type' => 'system',
            'title' => "Cập nhật phân quyền nhóm: " . ($group['name'] ?? $id),
            'user_id' => $this->userId(),
            'tenant_id' => Database::tenantId(),
        ]);

        $this->setFlash('success', 'Đã lưu phân quyền.');
        return $this->redirect('settings/permissions?group=' . $id);
    }

    public function getPanel($id)
    {
        $this->authorize('settings', 'manage');

        $group = Database::fetch("SELECT * FROM permission_groups WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$group) return $this->json(['error' => 'Not found'], 404);

        $permissions = Database::fetchAll("SELECT * FROM permissions ORDER BY module, FIELD(action, 'view','create','edit','delete','approve','view_all')");
        $modules = [];
        foreach ($permissions as $p) $modules[$p['module']][] = $p;

        $allActions = [];
        foreach ($permissions as $p) {
            if (!in_array($p['action'], $allActions)) $allActions[] = $p['action'];
        }

        $gp = Database::fetchAll("SELECT permission_id FROM group_permissions WHERE group_id = ?", [$id]);
        $groupPermIds = array_column($gp, 'permission_id');

        $groupUsers = Database::fetchAll(
            "SELECT u.id, u.name, u.avatar, u.email FROM users u JOIN user_permission_groups upg ON u.id = upg.user_id WHERE upg.group_id = ?",
            [$id]
        );

        ob_start();
        include BASE_PATH . '/resources/views/settings/_permission-panel.php';
        $html = ob_get_clean();

        return $this->json(['html' => $html]);
    }

    public function addUser($groupId)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);
        $this->authorize('settings', 'manage');

        $userId = (int)$this->input('user_id');
        if (!$userId) return $this->json(['error' => 'User ID required'], 422);

        Database::query(
            "INSERT IGNORE INTO user_permission_groups (user_id, group_id) VALUES (?, ?)",
            [$userId, $groupId]
        );

        $user = Database::fetch("SELECT name FROM users WHERE id = ?", [$userId]);
        $group = Database::fetch("SELECT name FROM permission_groups WHERE id = ?", [$groupId]);
        Database::insert('activities', [
            'type' => 'system',
            'title' => "Thêm user " . ($user['name'] ?? $userId) . " vào nhóm " . ($group['name'] ?? $groupId),
            'user_id' => $this->userId(),
            'tenant_id' => Database::tenantId(),
        ]);

        PermissionService::clearCache();
        return $this->json(['success' => true]);
    }

    public function removeUser($groupId)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);
        $this->authorize('settings', 'manage');

        $userId = (int)$this->input('user_id');

        $user = Database::fetch("SELECT name FROM users WHERE id = ?", [$userId]);
        $group = Database::fetch("SELECT name FROM permission_groups WHERE id = ?", [$groupId]);

        Database::query(
            "DELETE FROM user_permission_groups WHERE user_id = ? AND group_id = ?",
            [$userId, $groupId]
        );

        Database::insert('activities', [
            'type' => 'system',
            'title' => "Xóa user " . ($user['name'] ?? $userId) . " khỏi nhóm " . ($group['name'] ?? $groupId),
            'user_id' => $this->userId(),
            'tenant_id' => Database::tenantId(),
        ]);

        PermissionService::clearCache();
        return $this->json(['success' => true]);
    }

    public function clone($id)
    {
        if (!$this->isPost()) return $this->redirect('settings/permissions');
        $this->authorize('settings', 'manage');

        $tid = Database::tenantId();
        $group = Database::fetch("SELECT * FROM permission_groups WHERE id = ? AND tenant_id = ?", [$id, $tid]);
        if (!$group) {
            $this->setFlash('error', 'Nhóm quyền không tồn tại.');
            return $this->redirect('settings/permissions');
        }

        $newName = 'Copy of ' . $group['name'];
        $slug = preg_replace('/[^a-z0-9-]/', '-', strtolower($this->removeVietnamese($newName)));

        $newId = Database::insert('permission_groups', [
            'tenant_id' => $tid,
            'name' => $newName,
            'slug' => $slug,
            'parent_id' => $group['parent_id'],
            'description' => $group['description'],
            'color' => $group['color'],
        ]);

        // Copy permissions
        $perms = Database::fetchAll("SELECT permission_id FROM group_permissions WHERE group_id = ?", [$id]);
        foreach ($perms as $p) {
            Database::insert('group_permissions', [
                'group_id' => $newId,
                'permission_id' => $p['permission_id'],
            ]);
        }

        Database::insert('activities', [
            'type' => 'system',
            'title' => "Nhân bản nhóm quyền: " . $group['name'] . " -> $newName",
            'user_id' => $this->userId(),
            'tenant_id' => $tid,
        ]);

        $this->setFlash('success', 'Đã nhân bản nhóm quyền.');
        return $this->redirect('settings/permissions?group=' . $newId);
    }

    private function removeVietnamese(string $str): string
    {
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/u", "a", $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/u", "e", $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/u", "i", $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/u", "o", $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/u", "u", $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/u", "y", $str);
        $str = preg_replace("/(đ)/u", "d", $str);
        return $str;
    }
}
