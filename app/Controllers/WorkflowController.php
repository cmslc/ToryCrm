<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class WorkflowController extends Controller
{
    public function index()
    {
        $workflows = Database::fetchAll(
            "SELECT w.*, u.name as created_by_name
             FROM workflows w
             LEFT JOIN users u ON w.created_by = u.id
             ORDER BY w.created_at DESC"
        );

        return $this->view('workflows.index', [
            'workflows' => $workflows,
        ]);
    }

    public function create()
    {
        return $this->view('workflows.create');
    }

    public function store()
    {
        if (!$this->isPost()) {
            return $this->redirect('workflows');
        }

        $data = $this->allInput();
        $name = trim($data['name'] ?? '');

        if (empty($name)) {
            $this->setFlash('error', 'Tên workflow không được để trống.');
            return $this->back();
        }

        Database::insert('workflows', [
            'name' => $name,
            'description' => trim($data['description'] ?? ''),
            'trigger_type' => trim($data['trigger_type'] ?? ''),
            'trigger_config' => $data['trigger_config'] ?? '{}',
            'nodes' => $data['nodes'] ?? '[]',
            'edges' => $data['edges'] ?? '[]',
            'is_active' => 0,
            'run_count' => 0,
            'created_by' => $this->userId(),
        ]);

        $this->setFlash('success', 'Workflow đã được tạo.');
        return $this->redirect('workflows');
    }

    public function edit($id)
    {
        $workflow = Database::fetch("SELECT * FROM workflows WHERE id = ?", [$id]);

        if (!$workflow) {
            $this->setFlash('error', 'Workflow không tồn tại.');
            return $this->redirect('workflows');
        }

        return $this->view('workflows.edit', [
            'workflow' => $workflow,
        ]);
    }

    public function update($id)
    {
        if (!$this->isPost()) {
            return $this->redirect('workflows');
        }

        $workflow = Database::fetch("SELECT * FROM workflows WHERE id = ?", [$id]);

        if (!$workflow) {
            $this->setFlash('error', 'Workflow không tồn tại.');
            return $this->redirect('workflows');
        }

        $data = $this->allInput();
        $name = trim($data['name'] ?? '');

        if (empty($name)) {
            $this->setFlash('error', 'Tên workflow không được để trống.');
            return $this->back();
        }

        Database::update('workflows', [
            'name' => $name,
            'description' => trim($data['description'] ?? ''),
            'trigger_type' => trim($data['trigger_type'] ?? ''),
            'trigger_config' => $data['trigger_config'] ?? '{}',
            'nodes' => $data['nodes'] ?? '[]',
            'edges' => $data['edges'] ?? '[]',
        ], 'id = ?', [$id]);

        $this->setFlash('success', 'Workflow đã được cập nhật.');
        return $this->redirect('workflows');
    }

    public function delete($id)
    {
        if (!$this->isPost()) {
            return $this->redirect('workflows');
        }

        $workflow = Database::fetch("SELECT * FROM workflows WHERE id = ?", [$id]);

        if (!$workflow) {
            $this->setFlash('error', 'Workflow không tồn tại.');
            return $this->redirect('workflows');
        }

        Database::delete('workflow_logs', 'workflow_id = ?', [$id]);
        Database::delete('workflows', 'id = ?', [$id]);

        $this->setFlash('success', 'Workflow đã được xóa.');
        return $this->redirect('workflows');
    }

    public function toggleActive($id)
    {
        if (!$this->isPost()) {
            return $this->redirect('workflows');
        }

        $workflow = Database::fetch("SELECT * FROM workflows WHERE id = ?", [$id]);

        if (!$workflow) {
            $this->setFlash('error', 'Workflow không tồn tại.');
            return $this->redirect('workflows');
        }

        Database::update('workflows', [
            'is_active' => $workflow['is_active'] ? 0 : 1,
        ], 'id = ?', [$id]);

        $this->setFlash('success', $workflow['is_active'] ? 'Đã tắt workflow.' : 'Đã bật workflow.');
        return $this->redirect('workflows');
    }

    public function logs($id)
    {
        $workflow = Database::fetch("SELECT * FROM workflows WHERE id = ?", [$id]);

        if (!$workflow) {
            return $this->json(['error' => 'Workflow không tồn tại'], 404);
        }

        $logs = Database::fetchAll(
            "SELECT wl.*
             FROM workflow_logs wl
             WHERE wl.workflow_id = ?
             ORDER BY wl.created_at DESC
             LIMIT 50",
            [$id]
        );

        return $this->json([
            'workflow' => $workflow,
            'logs' => $logs,
        ]);
    }
}
