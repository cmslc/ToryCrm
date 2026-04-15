<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class PositionController extends Controller
{
    public function index()
    {
        $this->authorize('settings', 'manage');
        $tid = Database::tenantId();

        $positions = Database::fetchAll(
            "SELECT p.*, (SELECT COUNT(*) FROM users u WHERE u.position_id = p.id) as user_count
             FROM positions p WHERE p.tenant_id = ? ORDER BY p.sort_order, p.name",
            [$tid]
        );

        return $this->view('settings.positions', ['positions' => $positions]);
    }

    public function store()
    {
        if (!$this->isPost()) return $this->redirect('settings/positions');
        $this->authorize('settings', 'manage');

        $name = trim($this->input('name') ?? '');
        if (empty($name)) {
            $this->setFlash('error', 'Tên chức vụ không được để trống.');
            return $this->back();
        }

        $maxSort = Database::fetch("SELECT MAX(sort_order) as m FROM positions WHERE tenant_id = ?", [Database::tenantId()])['m'] ?? 0;

        Database::insert('positions', [
            'tenant_id' => Database::tenantId(),
            'name' => $name,
            'description' => trim($this->input('description') ?? ''),
            'sort_order' => $maxSort + 1,
        ]);

        $this->setFlash('success', 'Đã thêm chức vụ.');
        return $this->redirect('settings/positions');
    }

    public function update($id)
    {
        if (!$this->isPost()) return $this->redirect('settings/positions');
        $this->authorize('settings', 'manage');

        $name = trim($this->input('name') ?? '');
        if (empty($name)) {
            $this->setFlash('error', 'Tên chức vụ không được để trống.');
            return $this->back();
        }

        Database::update('positions', [
            'name' => $name,
            'description' => trim($this->input('description') ?? ''),
        ], 'id = ? AND tenant_id = ?', [$id, Database::tenantId()]);

        $this->setFlash('success', 'Đã cập nhật chức vụ.');
        return $this->redirect('settings/positions');
    }

    public function delete($id)
    {
        if (!$this->isPost()) return $this->redirect('settings/positions');
        $this->authorize('settings', 'manage');

        // Unset position from users
        Database::query("UPDATE users SET position_id = NULL WHERE position_id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        Database::query("DELETE FROM positions WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);

        $this->setFlash('success', 'Đã xóa chức vụ.');
        return $this->redirect('settings/positions');
    }
}
