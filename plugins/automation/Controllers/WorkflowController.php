<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class WorkflowController extends Controller
{
    public function index()
    {
        $this->authorize('automation', 'view');
        $tid = Database::tenantId();
        $workflows = Database::fetchAll(
            "SELECT w.*, u.name as created_by_name
             FROM workflows w
             LEFT JOIN users u ON w.created_by = u.id
             WHERE w.tenant_id = ?
             ORDER BY w.created_at DESC",
            [$tid]
        );

        $rules = Database::fetchAll(
            "SELECT ar.*, u.name as created_by_name
             FROM automation_rules ar
             LEFT JOIN users u ON ar.created_by = u.id
             WHERE ar.tenant_id = ?
             ORDER BY ar.created_at DESC",
            [$tid]
        );

        return $this->view('plugin:automation.workflows.index', [
            'workflows' => $workflows,
            'rules' => $rules,
        ]);
    }

    public function create()
    {
        $this->authorize('automation', 'create');
        return $this->view('plugin:automation.workflows.create');
    }

    public function store()
    {
        if (!$this->isPost()) return $this->redirect('workflows');
        $this->authorize('automation', 'create');

        $data = $this->allInput();
        $name = trim($data['name'] ?? '');

        if (empty($name)) {
            $this->setFlash('error', 'Tên workflow không được để trống.');
            return $this->back();
        }

        Database::insert('workflows', [
            'tenant_id' => Database::tenantId(),
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

    private function fetchWorkflow($id)
    {
        return Database::fetch("SELECT * FROM workflows WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
    }

    public function edit($id)
    {
        $this->authorize('automation', 'edit');
        $workflow = $this->fetchWorkflow($id);
        if (!$workflow) { $this->setFlash('error', 'Workflow không tồn tại.'); return $this->redirect('workflows'); }
        return $this->view('plugin:automation.workflows.edit', ['workflow' => $workflow]);
    }

    public function update($id)
    {
        if (!$this->isPost()) return $this->redirect('workflows');
        $this->authorize('automation', 'edit');

        $workflow = $this->fetchWorkflow($id);
        if (!$workflow) { $this->setFlash('error', 'Workflow không tồn tại.'); return $this->redirect('workflows'); }

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
        ], 'id = ? AND tenant_id = ?', [$id, Database::tenantId()]);

        $this->setFlash('success', 'Workflow đã được cập nhật.');
        return $this->redirect('workflows');
    }

    public function delete($id)
    {
        if (!$this->isPost()) return $this->redirect('workflows');
        $this->authorize('automation', 'delete');

        $workflow = $this->fetchWorkflow($id);
        if (!$workflow) { $this->setFlash('error', 'Workflow không tồn tại.'); return $this->redirect('workflows'); }

        Database::delete('workflow_logs', 'workflow_id = ?', [$id]);
        Database::delete('workflows', 'id = ? AND tenant_id = ?', [$id, Database::tenantId()]);

        $this->setFlash('success', 'Workflow đã được xóa.');
        return $this->redirect('workflows');
    }

    public function toggleActive($id)
    {
        if (!$this->isPost()) return $this->redirect('workflows');
        $this->authorize('automation', 'edit');

        $workflow = $this->fetchWorkflow($id);
        if (!$workflow) { $this->setFlash('error', 'Workflow không tồn tại.'); return $this->redirect('workflows'); }

        Database::update('workflows', [
            'is_active' => $workflow['is_active'] ? 0 : 1,
        ], 'id = ? AND tenant_id = ?', [$id, Database::tenantId()]);

        $this->setFlash('success', $workflow['is_active'] ? 'Đã tắt workflow.' : 'Đã bật workflow.');
        return $this->redirect('workflows');
    }

    public function logs($id)
    {
        $this->authorize('automation', 'view');
        $workflow = $this->fetchWorkflow($id);
        if (!$workflow) return $this->json(['error' => 'Workflow không tồn tại'], 404);

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
