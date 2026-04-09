<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Database::fetchAll(
            "SELECT d.*, u.name as manager_name, p.name as parent_name,
                    (SELECT COUNT(*) FROM users WHERE department_id = d.id AND is_active = 1) as member_count
             FROM departments d
             LEFT JOIN users u ON d.manager_id = u.id
             LEFT JOIN departments p ON d.parent_id = p.id
             WHERE d.tenant_id = ?
             ORDER BY d.sort_order, d.name",
            [$this->tenantId()]
        );

        $users = Database::fetchAll("SELECT id, name FROM users WHERE tenant_id = ? AND is_active = 1 ORDER BY name", [$this->tenantId()]);

        return $this->view('departments.index', [
            'departments' => $departments,
            'users' => $users,
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

        Database::insert('departments', [
            'tenant_id' => $this->tenantId(),
            'name' => $name,
            'parent_id' => $this->input('parent_id') ?: null,
            'manager_id' => $this->input('manager_id') ?: null,
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

        Database::update('departments', [
            'name' => $name,
            'parent_id' => $this->input('parent_id') ?: null,
            'manager_id' => $this->input('manager_id') ?: null,
            'description' => trim($this->input('description') ?? ''),
            'color' => $this->input('color') ?? '#405189',
        ], 'id = ? AND tenant_id = ?', [(int)$id, $this->tenantId()]);

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
            $this->setFlash('success', 'Đã thêm thành viên vào phòng ban.');
        }
        return $this->redirect('departments/' . $id . '/members');
    }

    public function removeMember($id, $userId)
    {
        if (!$this->isPost()) return $this->redirect('departments/' . $id . '/members');

        Database::update('users', ['department_id' => null], 'id = ? AND department_id = ?', [(int)$userId, (int)$id]);
        $this->setFlash('success', 'Đã xóa thành viên khỏi phòng ban.');
        return $this->redirect('departments/' . $id . '/members');
    }
}
