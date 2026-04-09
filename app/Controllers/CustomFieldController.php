<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Services\CustomFieldService;

class CustomFieldController extends Controller
{
    private array $modules = [
        'contacts' => 'Khách hàng',
        'deals' => 'Cơ hội',
        'orders' => 'Đơn hàng',
        'tasks' => 'Công việc',
        'tickets' => 'Ticket',
        'products' => 'Sản phẩm',
    ];

    public function index()
    {
        $tenantId = Database::tenantId();
        $activeModule = $this->input('module', 'contacts');

        $fieldsByModule = [];
        foreach ($this->modules as $key => $label) {
            $fieldsByModule[$key] = CustomFieldService::getFields($key, $tenantId);
        }

        return $this->view('custom-fields.index', [
            'modules' => $this->modules,
            'fieldsByModule' => $fieldsByModule,
            'activeModule' => $activeModule,
        ]);
    }

    public function create()
    {
        return $this->view('custom-fields.create', [
            'modules' => $this->modules,
            'module' => $this->input('module', 'contacts'),
        ]);
    }

    public function store()
    {
        if (!$this->isPost()) return $this->redirect('custom-fields');

        $data = $this->allInput();
        $label = trim($data['field_label'] ?? '');
        $key = trim($data['field_key'] ?? '');
        $module = $data['module'] ?? 'contacts';

        if (empty($label) || empty($key)) {
            $this->setFlash('error', 'Tên trường và khóa trường không được để trống.');
            return $this->back();
        }

        // Slugify key
        $key = preg_replace('/[^a-z0-9_]/', '_', strtolower($key));

        // Check unique key per module+tenant
        $existing = Database::fetch(
            "SELECT id FROM custom_field_definitions WHERE field_key = ? AND module = ? AND tenant_id = ?",
            [$key, $module, Database::tenantId()]
        );

        if ($existing) {
            $this->setFlash('error', 'Khóa trường đã tồn tại trong module này.');
            return $this->back();
        }

        // Get max sort_order
        $maxOrder = Database::fetch(
            "SELECT MAX(sort_order) as max_order FROM custom_field_definitions WHERE module = ? AND tenant_id = ?",
            [$module, Database::tenantId()]
        );

        Database::insert('custom_field_definitions', [
            'module' => $module,
            'field_label' => $label,
            'field_key' => $key,
            'field_type' => $data['field_type'] ?? 'text',
            'options' => trim($data['options'] ?? ''),
            'default_value' => trim($data['default_value'] ?? ''),
            'placeholder' => trim($data['placeholder'] ?? ''),
            'is_required' => isset($data['is_required']) ? 1 : 0,
            'is_filterable' => isset($data['is_filterable']) ? 1 : 0,
            'show_in_list' => isset($data['show_in_list']) ? 1 : 0,
            'sort_order' => ($maxOrder['max_order'] ?? 0) + 1,
            'tenant_id' => Database::tenantId(),
            'created_by' => $this->userId(),
        ]);

        $this->setFlash('success', 'Đã tạo trường tùy chỉnh.');
        return $this->redirect('custom-fields?module=' . $module);
    }

    public function edit($id)
    {
        $field = Database::fetch(
            "SELECT * FROM custom_field_definitions WHERE id = ? AND tenant_id = ?",
            [$id, Database::tenantId()]
        );

        if (!$field) {
            $this->setFlash('error', 'Trường không tồn tại.');
            return $this->redirect('custom-fields');
        }

        return $this->view('custom-fields.create', [
            'modules' => $this->modules,
            'module' => $field['module'],
            'field' => $field,
        ]);
    }

    public function update($id)
    {
        if (!$this->isPost()) return $this->redirect('custom-fields');

        $field = Database::fetch(
            "SELECT * FROM custom_field_definitions WHERE id = ? AND tenant_id = ?",
            [$id, Database::tenantId()]
        );

        if (!$field) {
            $this->setFlash('error', 'Trường không tồn tại.');
            return $this->redirect('custom-fields');
        }

        $data = $this->allInput();
        $label = trim($data['field_label'] ?? '');

        if (empty($label)) {
            $this->setFlash('error', 'Tên trường không được để trống.');
            return $this->back();
        }

        Database::update('custom_field_definitions', [
            'field_label' => $label,
            'field_type' => $data['field_type'] ?? 'text',
            'options' => trim($data['options'] ?? ''),
            'default_value' => trim($data['default_value'] ?? ''),
            'placeholder' => trim($data['placeholder'] ?? ''),
            'is_required' => isset($data['is_required']) ? 1 : 0,
            'is_filterable' => isset($data['is_filterable']) ? 1 : 0,
            'show_in_list' => isset($data['show_in_list']) ? 1 : 0,
        ], 'id = ?', [$id]);

        $this->setFlash('success', 'Đã cập nhật trường tùy chỉnh.');
        return $this->redirect('custom-fields?module=' . $field['module']);
    }

    public function delete($id)
    {
        if (!$this->isPost()) return $this->redirect('custom-fields');

        $field = Database::fetch(
            "SELECT * FROM custom_field_definitions WHERE id = ? AND tenant_id = ?",
            [$id, Database::tenantId()]
        );

        if (!$field) {
            $this->setFlash('error', 'Trường không tồn tại.');
            return $this->redirect('custom-fields');
        }

        // Delete values first
        Database::delete('custom_field_values', 'field_id = ?', [$id]);
        Database::delete('custom_field_definitions', 'id = ?', [$id]);

        $this->setFlash('success', 'Đã xóa trường tùy chỉnh.');
        return $this->redirect('custom-fields?module=' . $field['module']);
    }

    public function reorder()
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }

        $ids = $_POST['ids'] ?? [];
        if (!is_array($ids)) {
            return $this->json(['error' => 'Dữ liệu không hợp lệ'], 422);
        }

        foreach ($ids as $order => $id) {
            Database::update('custom_field_definitions', [
                'sort_order' => $order + 1,
            ], 'id = ? AND tenant_id = ?', [(int) $id, Database::tenantId()]);
        }

        return $this->json(['success' => true]);
    }
}
