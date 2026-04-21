<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class AutomationController extends Controller
{
    public function index()
    {
        return $this->redirect('workflows?tab=automation');
    }

    public function create()
    {
        $this->authorize('automation', 'create');
        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 AND u.tenant_id = ? ORDER BY d.name, u.name", [Database::tenantId()]);

        return $this->view('automation.create', ['users' => $users]);
    }

    public function store()
    {
        if (!$this->isPost()) return $this->redirect('automation');
        $this->authorize('automation', 'create');

        $data = $this->allInput();

        $name = trim($data['name'] ?? '');
        $module = trim($data['module'] ?? '');
        $triggerEvent = trim($data['trigger_event'] ?? '');
        $conditions = $data['conditions'] ?? [];
        $actions = $data['actions'] ?? [];

        if (empty($name)) { $this->setFlash('error', 'Tên rule không được để trống.'); return $this->back(); }
        if (empty($module)) { $this->setFlash('error', 'Vui lòng chọn module.'); return $this->back(); }
        if (empty($triggerEvent)) { $this->setFlash('error', 'Vui lòng chọn trigger event.'); return $this->back(); }
        if (empty($actions)) { $this->setFlash('error', 'Vui lòng thêm ít nhất 1 action.'); return $this->back(); }

        // Filter out empty conditions
        $filteredConditions = [];
        if (is_array($conditions)) {
            foreach ($conditions as $condition) {
                if (!empty($condition['field']) && !empty($condition['operator'])) {
                    $filteredConditions[] = $condition;
                }
            }
        }

        // Filter out empty actions
        $filteredActions = [];
        if (is_array($actions)) {
            foreach ($actions as $action) {
                if (!empty($action['type'])) {
                    $filteredActions[] = $action;
                }
            }
        }

        Database::insert('automation_rules', [
            'tenant_id' => Database::tenantId(),
            'name' => $name,
            'module' => $module,
            'trigger_event' => $triggerEvent,
            'conditions' => json_encode($filteredConditions),
            'actions' => json_encode($filteredActions),
            'is_active' => 1,
            'run_count' => 0,
            'created_by' => $this->userId(),
        ]);

        $this->setFlash('success', 'Automation rule đã được tạo.');
        return $this->redirect('automation');
    }

    private function fetchRule($id)
    {
        return Database::fetch("SELECT * FROM automation_rules WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
    }

    public function toggleActive($id)
    {
        if (!$this->isPost()) return $this->redirect('automation');
        $this->authorize('automation', 'edit');

        $rule = $this->fetchRule($id);
        if (!$rule) { $this->setFlash('error', 'Rule không tồn tại.'); return $this->redirect('automation'); }

        Database::update('automation_rules', [
            'is_active' => $rule['is_active'] ? 0 : 1,
        ], 'id = ? AND tenant_id = ?', [$id, Database::tenantId()]);

        $this->setFlash('success', $rule['is_active'] ? 'Đã tắt automation rule.' : 'Đã bật automation rule.');
        return $this->redirect('automation');
    }

    public function delete($id)
    {
        if (!$this->isPost()) return $this->redirect('automation');
        $this->authorize('automation', 'delete');

        $rule = $this->fetchRule($id);
        if (!$rule) { $this->setFlash('error', 'Rule không tồn tại.'); return $this->redirect('automation'); }

        Database::delete('automation_rule_logs', 'rule_id = ?', [$id]);
        Database::delete('automation_rules', 'id = ? AND tenant_id = ?', [$id, Database::tenantId()]);

        $this->setFlash('success', 'Automation rule đã được xóa.');
        return $this->redirect('automation');
    }

    public function logs($id)
    {
        $this->authorize('automation', 'view');
        $rule = $this->fetchRule($id);
        if (!$rule) { $this->setFlash('error', 'Rule không tồn tại.'); return $this->redirect('automation'); }

        // Load logs (logs table may not have tenant_id but gated by rule tenant check above)
        $logs = Database::fetchAll(
            "SELECT * FROM automation_rule_logs WHERE rule_id = ? ORDER BY created_at DESC LIMIT 50",
            [$id]
        );

        return $this->view('automation.logs', ['rule' => $rule, 'logs' => $logs]);
    }
}
