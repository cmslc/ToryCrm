<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Database::fetchAll(
            "SELECT d.*, u.name as manager_name, u.avatar as manager_avatar,
                    vm.name as vice_manager_name, vm.avatar as vice_manager_avatar,
                    p.name as parent_name,
                    (SELECT COUNT(*) FROM users WHERE department_id = d.id AND is_active = 1) as member_count
             FROM departments d
             LEFT JOIN users u ON d.manager_id = u.id
             LEFT JOIN users vm ON d.vice_manager_id = vm.id
             LEFT JOIN departments p ON d.parent_id = p.id
             WHERE d.tenant_id = ?
             ORDER BY d.sort_order, d.name",
            [$this->tenantId()]
        );

        $users = Database::fetchAll("SELECT users.id, users.name, users.department_id, d2.name as dept_name FROM users LEFT JOIN departments d2 ON d2.id = users.department_id WHERE users.tenant_id = ? AND users.is_active = 1 ORDER BY users.name", [$this->tenantId()]);

        $positions = Database::fetchAll(
            "SELECT p.*, (SELECT COUNT(*) FROM users u WHERE u.position_id = p.id) as user_count
             FROM positions p WHERE p.tenant_id = ? ORDER BY p.sort_order, p.name",
            [$this->tenantId()]
        );

        return $this->view('departments.index', [
            'departments' => $departments,
            'users' => $users,
            'positions' => $positions,
        ]);
    }

    public function store()
    {
        if (!$this->isPost()) return $this->redirect('departments');

        $name = trim($this->input('name') ?? '');
        if (empty($name)) {
            $this->setFlash('error', 'Tên phòng ban không được để trống.');
            return $this->back();
        }

        $tid = $this->tenantId();
        $validUser = fn($uid) => $uid && Database::fetch("SELECT id FROM users WHERE id = ? AND tenant_id = ?", [(int)$uid, $tid]) ? (int)$uid : null;
        $validDept = fn($did) => $did && Database::fetch("SELECT id FROM departments WHERE id = ? AND tenant_id = ?", [(int)$did, $tid]) ? (int)$did : null;

        Database::insert('departments', [
            'tenant_id' => $tid,
            'name' => $name,
            'parent_id' => $validDept($this->input('parent_id')),
            'manager_id' => $validUser($this->input('manager_id')),
            'vice_manager_id' => $validUser($this->input('vice_manager_id')),
            'description' => trim($this->input('description') ?? ''),
            'color' => $this->input('color') ?? '#405189',
        ]);

        $this->setFlash('success', 'Đã tạo phòng ban "' . $name . '".');
        return $this->redirect('departments');
    }

    public function update($id)
    {
        if (!$this->isPost()) return $this->redirect('departments');

        $name = trim($this->input('name') ?? '');
        if (empty($name)) {
            $this->setFlash('error', 'Tên phòng ban không được để trống.');
            return $this->back();
        }

        $tid = $this->tenantId();
        $validUser = fn($uid) => $uid && Database::fetch("SELECT id FROM users WHERE id = ? AND tenant_id = ?", [(int)$uid, $tid]) ? (int)$uid : null;
        $validDept = fn($did) => $did && (int)$did !== (int)$id && Database::fetch("SELECT id FROM departments WHERE id = ? AND tenant_id = ?", [(int)$did, $tid]) ? (int)$did : null;

        Database::update('departments', [
            'name' => $name,
            'parent_id' => $validDept($this->input('parent_id')),
            'manager_id' => $validUser($this->input('manager_id')),
            'vice_manager_id' => $validUser($this->input('vice_manager_id')),
            'description' => trim($this->input('description') ?? ''),
            'color' => $this->input('color') ?? '#405189',
        ], 'id = ? AND tenant_id = ?', [(int)$id, $tid]);

        $this->setFlash('success', 'Đã cập nhật phòng ban.');
        return $this->redirect('departments');
    }

    public function delete($id)
    {
        if (!$this->isPost()) return $this->redirect('departments');

        $dept = Database::fetch("SELECT * FROM departments WHERE id = ? AND tenant_id = ?", [(int)$id, $this->tenantId()]);
        if (!$dept) {
            $this->setFlash('error', 'Phòng ban không tồn tại.');
            return $this->redirect('departments');
        }

        $memberCount = Database::fetch("SELECT COUNT(*) as cnt FROM users WHERE department_id = ?", [(int)$id]);
        if (($memberCount['cnt'] ?? 0) > 0) {
            $this->setFlash('warning', 'Phòng ban đang có ' . $memberCount['cnt'] . ' thành viên. Hãy chuyển họ sang phòng khác trước.');
            return $this->back();
        }

        $childCount = Database::fetch("SELECT COUNT(*) as cnt FROM departments WHERE parent_id = ?", [(int)$id]);
        if (($childCount['cnt'] ?? 0) > 0) {
            $this->setFlash('warning', 'Phòng ban có phòng con. Hãy xóa hoặc chuyển phòng con trước.');
            return $this->back();
        }

        Database::delete('departments', 'id = ? AND tenant_id = ?', [(int)$id, $this->tenantId()]);
        $this->setFlash('success', 'Đã xóa phòng ban.');
        return $this->redirect('departments');
    }

    public function members($id)
    {
        $dept = Database::fetch("SELECT * FROM departments WHERE id = ? AND tenant_id = ?", [(int)$id, $this->tenantId()]);
        if (!$dept) {
            $this->setFlash('error', 'Phòng ban không tồn tại.');
            return $this->redirect('departments');
        }

        $members = Database::fetchAll(
            "SELECT id, name, email, phone, role, last_login FROM users WHERE department_id = ? AND is_active = 1 ORDER BY name",
            [(int)$id]
        );

        $allUsers = Database::fetchAll(
            "SELECT id, name FROM users WHERE tenant_id = ? AND is_active = 1 AND (department_id IS NULL OR department_id != ?) ORDER BY name",
            [$this->tenantId(), (int)$id]
        );

        return $this->view('departments.members', [
            'department' => $dept,
            'members' => $members,
            'allUsers' => $allUsers,
        ]);
    }

    public function addMember($id)
    {
        if (!$this->isPost()) return $this->redirect('departments/' . $id . '/members');

        $userId = (int) $this->input('user_id');
        if ($userId) {
            Database::update('users', ['department_id' => (int)$id], 'id = ?', [$userId]);
            $user = Database::fetch("SELECT name FROM users WHERE id = ?", [$userId]);
            Database::insert('activities', [
                'type' => 'system', 'title' => 'Thêm vào phòng ban: ' . ($user['name'] ?? ''),
                'user_id' => $this->userId(), 'tenant_id' => $this->tenantId(),
            ]);
            $this->setFlash('success', 'Đã thêm thành viên vào phòng ban.');
        }
        return $this->redirect('departments/' . $id);
    }

    public function removeMember($id, $userId)
    {
        if (!$this->isPost()) return $this->redirect('departments/' . $id . '/members');

        $user = Database::fetch("SELECT name FROM users WHERE id = ?", [(int)$userId]);
        Database::update('users', ['department_id' => null], 'id = ? AND department_id = ?', [(int)$userId, (int)$id]);

        // Log
        Database::insert('activities', [
            'type' => 'system', 'title' => 'Xóa khỏi phòng ban: ' . ($user['name'] ?? ''),
            'user_id' => $this->userId(), 'tenant_id' => $this->tenantId(),
        ]);

        $this->setFlash('success', 'Đã xóa thành viên khỏi phòng ban.');
        return $this->redirect('departments/' . $id);
    }

    // ---- Show (detail page with stats) ----
    public function show($id)
    {
        $tid = $this->tenantId();
        $dept = Database::fetch(
            "SELECT d.*, u.name as manager_name, u.avatar as manager_avatar,
                    vm.name as vice_manager_name, vm.avatar as vice_manager_avatar, p.name as parent_name
             FROM departments d LEFT JOIN users u ON d.manager_id = u.id
             LEFT JOIN users vm ON d.vice_manager_id = vm.id
             LEFT JOIN departments p ON d.parent_id = p.id
             WHERE d.id = ? AND d.tenant_id = ?",
            [(int)$id, $tid]
        );
        if (!$dept) { $this->setFlash('error', 'Phòng ban không tồn tại.'); return $this->redirect('departments'); }

        $members = Database::fetchAll(
            "SELECT id, name, email, role, avatar, last_login FROM users WHERE department_id = ? AND is_active = 1 ORDER BY name",
            [(int)$id]
        );

        $childDepts = Database::fetchAll(
            "SELECT d.*, (SELECT COUNT(*) FROM users WHERE department_id = d.id AND is_active = 1) as member_count
             FROM departments d WHERE d.parent_id = ? AND d.tenant_id = ?",
            [(int)$id, $tid]
        );

        // Stats
        $memberIds = array_column($members, 'id');
        $stats = ['deals' => 0, 'revenue' => 0, 'tasks_done' => 0, 'tasks_total' => 0, 'contacts' => 0];
        if (!empty($memberIds)) {
            $ph = implode(',', array_fill(0, count($memberIds), '?'));
            $stats['deals'] = (int)(Database::fetch("SELECT COUNT(*) as c FROM deals WHERE owner_id IN ({$ph}) AND status = 'won'", $memberIds)['c'] ?? 0);
            $stats['revenue'] = (float)(Database::fetch("SELECT COALESCE(SUM(value),0) as v FROM deals WHERE owner_id IN ({$ph}) AND status = 'won'", $memberIds)['v'] ?? 0);
            $stats['tasks_total'] = (int)(Database::fetch("SELECT COUNT(*) as c FROM tasks WHERE assigned_to IN ({$ph}) AND is_deleted = 0 AND parent_id IS NULL", $memberIds)['c'] ?? 0);
            $stats['tasks_done'] = (int)(Database::fetch("SELECT COUNT(*) as c FROM tasks WHERE assigned_to IN ({$ph}) AND is_deleted = 0 AND parent_id IS NULL AND status = 'done'", $memberIds)['c'] ?? 0);
            $stats['contacts'] = (int)(Database::fetch("SELECT COUNT(*) as c FROM contacts WHERE owner_id IN ({$ph}) AND is_deleted = 0", $memberIds)['c'] ?? 0);
        }

        // KPI
        $kpi = null;
        try {
            $kpi = Database::fetch("SELECT * FROM department_kpi WHERE department_id = ? AND period = ? ORDER BY id DESC LIMIT 1", [(int)$id, date('Y-m')]);
        } catch (\Exception $e) {}

        // Recent activity log
        $activityLog = [];
        if (!empty($memberIds)) {
            $ph = implode(',', array_fill(0, count($memberIds), '?'));
            $activityLog = Database::fetchAll(
                "SELECT a.*, u.name as user_name FROM activities a LEFT JOIN users u ON a.user_id = u.id WHERE a.user_id IN ({$ph}) ORDER BY a.created_at DESC LIMIT 10",
                $memberIds
            );
        }

        // Positions
        $positions = [];
        try {
            $positions = Database::fetchAll(
                "SELECT dp.*, u.name as user_name, u.avatar FROM department_positions dp JOIN users u ON dp.user_id = u.id WHERE dp.department_id = ? ORDER BY dp.sort_order, dp.position",
                [(int)$id]
            );
        } catch (\Exception $e) {}

        return $this->view('departments.show', [
            'department' => $dept,
            'members' => $members,
            'childDepts' => $childDepts,
            'stats' => $stats,
            'kpi' => $kpi,
            'activityLog' => $activityLog,
            'positions' => $positions,
        ]);
    }

    // ---- Org Chart ----
    public function orgChart()
    {
        $departments = Database::fetchAll(
            "SELECT d.*, u.name as manager_name, u.avatar as manager_avatar,
                    (SELECT COUNT(*) FROM users WHERE department_id = d.id AND is_active = 1) as member_count
             FROM departments d LEFT JOIN users u ON d.manager_id = u.id
             WHERE d.tenant_id = ? ORDER BY d.sort_order, d.name",
            [$this->tenantId()]
        );

        return $this->view('departments.org-chart', ['departments' => $departments]);
    }

    // ---- Bulk Move Members ----
    public function bulkMove()
    {
        if (!$this->isPost()) return $this->redirect('departments');

        $userIds = $this->input('user_ids') ?? [];
        $targetDeptId = (int)$this->input('target_department_id');

        if (empty($userIds) || !$targetDeptId) {
            $this->setFlash('error', 'Chọn nhân viên và phòng ban đích.');
            return $this->back();
        }

        $ph = implode(',', array_fill(0, count($userIds), '?'));
        Database::query("UPDATE users SET department_id = ? WHERE id IN ({$ph}) AND tenant_id = ?",
            array_merge([$targetDeptId], $userIds, [$this->tenantId()])
        );

        Database::insert('activities', [
            'type' => 'system', 'title' => 'Chuyển ' . count($userIds) . ' nhân viên sang phòng ban mới',
            'user_id' => $this->userId(), 'tenant_id' => $this->tenantId(),
        ]);

        $this->setFlash('success', 'Đã chuyển ' . count($userIds) . ' nhân viên.');
        return $this->back();
    }

    // ---- Positions ----
    public function savePosition($id)
    {
        if (!$this->isPost()) return $this->redirect('departments/' . $id);

        $userId = (int)$this->input('user_id');
        $position = trim($this->input('position') ?? '');
        if (!$userId) {
            $this->setFlash('error', 'Chọn nhân viên.');
            return $this->back();
        }

        try {
            $existing = Database::fetch("SELECT id FROM department_positions WHERE department_id = ? AND user_id = ?", [(int)$id, $userId]);
            if (empty($position)) {
                if ($existing) Database::query("DELETE FROM department_positions WHERE id = ?", [$existing['id']]);
            } elseif ($existing) {
                Database::update('department_positions', ['position' => $position], 'id = ?', [$existing['id']]);
            } else {
                Database::query("INSERT INTO department_positions (department_id, user_id, position) VALUES (?, ?, ?)", [(int)$id, $userId, $position]);
            }
        } catch (\Exception $e) {}

        $this->setFlash('success', 'Đã cập nhật vị trí.');
        return $this->redirect('departments/' . $id);
    }

    public function deletePosition($id, $posId)
    {
        if (!$this->isPost()) return $this->redirect('departments/' . $id);
        Database::query("DELETE FROM department_positions WHERE id = ? AND department_id = ?", [(int)$posId, (int)$id]);
        $this->setFlash('success', 'Đã xóa vị trí.');
        return $this->redirect('departments/' . $id);
    }

    // ---- KPI Comparison ----
    public function kpiComparison()
    {
        $this->authorize('reports', 'view');
        $tid = $this->tenantId();
        $period = $this->input('period') ?: date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $period)) $period = date('Y-m');

        $departments = Database::fetchAll(
            "SELECT d.id, d.name, d.color,
                    (SELECT COUNT(*) FROM users WHERE department_id = d.id AND is_active = 1 AND tenant_id = ?) as member_count
             FROM departments d WHERE d.tenant_id = ? ORDER BY d.name",
            [$tid, $tid]
        );

        $comparison = [];
        foreach ($departments as $d) {
            $kpi = null;
            try {
                $kpi = Database::fetch("SELECT * FROM department_kpi WHERE department_id = ? AND period = ?", [$d['id'], $period]);
            } catch (\Exception $e) {}

            $memberIds = array_column(Database::fetchAll("SELECT id FROM users WHERE department_id = ? AND is_active = 1 AND tenant_id = ?", [$d['id'], $tid]), 'id');
            $stats = ['deals' => 0, 'revenue' => 0, 'tasks_done' => 0, 'contacts' => 0];
            if (!empty($memberIds)) {
                $ph = implode(',', array_fill(0, count($memberIds), '?'));
                $params = array_merge($memberIds, [$tid]);
                $stats['deals'] = (int)(Database::fetch("SELECT COUNT(*) as c FROM deals WHERE owner_id IN ({$ph}) AND status = 'won' AND tenant_id = ?", $params)['c'] ?? 0);
                $stats['revenue'] = (float)(Database::fetch("SELECT COALESCE(SUM(value),0) as v FROM deals WHERE owner_id IN ({$ph}) AND status = 'won' AND tenant_id = ?", $params)['v'] ?? 0);
                $stats['tasks_done'] = (int)(Database::fetch("SELECT COUNT(*) as c FROM tasks WHERE assigned_to IN ({$ph}) AND is_deleted = 0 AND parent_id IS NULL AND status = 'done' AND tenant_id = ?", $params)['c'] ?? 0);
                $stats['contacts'] = (int)(Database::fetch("SELECT COUNT(*) as c FROM contacts WHERE owner_id IN ({$ph}) AND is_deleted = 0 AND tenant_id = ?", $params)['c'] ?? 0);
            }

            $comparison[] = array_merge($d, ['kpi' => $kpi, 'stats' => $stats]);
        }

        return $this->view('departments.kpi-comparison', compact('comparison', 'period'));
    }

    // ---- Save KPI ----
    public function saveKpi($id)
    {
        if (!$this->isPost()) return $this->redirect('departments/' . $id);

        $period = $this->input('period') ?: date('Y-m');

        try {
            // Check if table exists, create if not
            Database::query("CREATE TABLE IF NOT EXISTS department_kpi (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                department_id INT UNSIGNED NOT NULL,
                period VARCHAR(7) NOT NULL,
                target_revenue DECIMAL(15,2) DEFAULT 0,
                target_deals INT DEFAULT 0,
                target_tasks INT DEFAULT 0,
                target_contacts INT DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_dept_period (department_id, period)
            )");

            $existing = Database::fetch("SELECT id FROM department_kpi WHERE department_id = ? AND period = ?", [(int)$id, $period]);
            if ($existing) {
                Database::update('department_kpi', [
                    'target_revenue' => (float)($this->input('target_revenue') ?? 0),
                    'target_deals' => (int)($this->input('target_deals') ?? 0),
                    'target_tasks' => (int)($this->input('target_tasks') ?? 0),
                    'target_contacts' => (int)($this->input('target_contacts') ?? 0),
                ], 'id = ?', [$existing['id']]);
            } else {
                Database::query("INSERT INTO department_kpi (department_id, period, target_revenue, target_deals, target_tasks, target_contacts) VALUES (?,?,?,?,?,?)", [
                    (int)$id, $period,
                    (float)($this->input('target_revenue') ?? 0),
                    (int)($this->input('target_deals') ?? 0),
                    (int)($this->input('target_tasks') ?? 0),
                    (int)($this->input('target_contacts') ?? 0),
                ]);
            }
        } catch (\Exception $e) {}

        $this->setFlash('success', 'Đã lưu KPI phòng ban.');
        return $this->redirect('departments/' . $id);
    }
}
