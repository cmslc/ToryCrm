<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class AutomationController extends Controller
{
    public function index()
    {
        $rules = Database::fetchAll(
            "SELECT ar.*, u.name as created_by_name
             FROM automation_rules ar
             LEFT JOIN users u ON ar.created_by = u.id
             ORDER BY ar.created_at DESC"
        );

        return $this->view('automation.index', ['rules' => $rules]);
    }

    public function create()
    {
        $users = Database::fetchAll("SELECT id, name FROM users WHERE is_active = 1 ORDER BY name");

        return $this->view('automation.create', ['users' => $users]);
    }

    public function store()
    {
        if (!$this->isPost()) {
            return $this->redirect('automation');
        }

        $data = $this->allInput();

        $name = trim($data['name'] ?? '');
        $module = trim($data['module'] ?? '');
        $triggerEvent = trim($data['trigger_event'] ?? '');
        $conditions = $data['conditions'] ?? [];
        $actions = $data['actions'] ?? [];

        if (empty($name)) {
            $this->setFlash('error', 'Tên rule không được để trống.');
            return $this->back();
        }

        if (empty($module)) {
            $this->setFlash('error', 'Vui lòng chọn module.');
            return $this->back();
        }

        if (empty($triggerEvent)) {
            $this->setFlash('error', 'Vui lòng chọn trigger event.');
            return $this->back();
        }

        if (empty($actions)) {
            $this->setFlash('error', 'Vui lòng thêm ít nhất 1 action.');
            return $this->back();
        }

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

    public function toggleActive($id)
    {
        if (!$this->isPost()) {
            return $this->redirect('automation');
        }

        $rule = Database::fetch("SELECT * FROM automation_rules WHERE id = ?", [$id]);

        if (!$rule) {
            $this->setFlash('error', 'Rule không tồn tại.');
            return $this->redirect('automation');
        }

        Database::update('automation_rules', [
            'is_active' => $rule['is_active'] ? 0 : 1,
        ], 'id = ?', [$id]);

        $this->setFlash('success', $rule['is_active'] ? 'Đã tắt automation rule.' : 'Đã bật automation rule.');
        return $this->redirect('automation');
    }

    public function delete($id)
    {
        if (!$this->isPost()) {
            return $this->redirect('automation');
        }

        $rule = Database::fetch("SELECT * FROM automation_rules WHERE id = ?", [$id]);

        if (!$rule) {
            $this->setFlash('error', 'Rule không tồn tại.');
            return $this->redirect('automation');
        }

        Database::delete('automation_rule_logs', 'rule_id = ?', [$id]);
        Database::delete('automation_rules', 'id = ?', [$id]);

        $this->setFlash('success', 'Automation rule đã được xóa.');
        return $this->redirect('automation');
    }

    public function logs($id)
    {
        $rule = Database::fetch("SELECT * FROM automation_rules WHERE id = ?", [$id]);

        if (!$rule) {
            $this->setFlash('error', 'Rule không tồn tại.');
            return $this->redirect('automation');
        }

        $logs = Database::fetchAll(
            "SELECT arl.*, u.name as triggered_by_name
             FROM automation_rule_logs arl
             LEFT JOIN users u ON arl.triggered_by = u.id
             WHERE arl.rule_id = ?
             ORDER BY arl.created_at DESC
             LIMIT 50",
            [$id]
        );

        return $this->json([
            'rule' => $rule,
            'logs' => $logs,
        ]);
    }
}
