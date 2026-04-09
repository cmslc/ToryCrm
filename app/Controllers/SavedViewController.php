<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class SavedViewController extends Controller
{
    /**
     * GET /saved-views/{module} - Return JSON list of saved views for module
     */
    public function index($module)
    {
        $allowedModules = ['contacts', 'deals', 'orders', 'tasks', 'tickets'];
        if (!in_array($module, $allowedModules)) {
            return $this->json(['error' => 'Invalid module'], 422);
        }

        $views = Database::fetchAll(
            "SELECT * FROM saved_views
             WHERE module = ? AND tenant_id = ? AND (user_id = ? OR is_shared = 1)
             ORDER BY is_default DESC, name ASC",
            [$module, Database::tenantId(), $this->userId()]
        );

        return $this->json(['success' => true, 'views' => $views]);
    }

    /**
     * POST /saved-views/store - Save a new view
     */
    public function store()
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }

        $data = $this->allInput();

        $module = $data['module'] ?? '';
        $name = trim($data['name'] ?? '');

        $allowedModules = ['contacts', 'deals', 'orders', 'tasks', 'tickets'];
        if (!in_array($module, $allowedModules)) {
            return $this->json(['error' => 'Module không hợp lệ'], 422);
        }

        if (empty($name)) {
            return $this->json(['error' => 'Tên bộ lọc không được để trống'], 422);
        }

        $viewId = Database::insert('saved_views', [
            'tenant_id' => Database::tenantId(),
            'user_id' => $this->userId(),
            'module' => $module,
            'name' => $name,
            'filters' => $data['filters'] ?? '{}',
            'columns' => $data['columns'] ?? null,
            'sort_by' => $data['sort_by'] ?? null,
            'sort_dir' => $data['sort_dir'] ?? 'DESC',
            'is_shared' => !empty($data['is_shared']) ? 1 : 0,
        ]);

        return $this->json(['success' => true, 'id' => $viewId]);
    }

    /**
     * POST /saved-views/{id}/delete - Delete a saved view
     */
    public function delete($id)
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }

        $view = Database::fetch(
            "SELECT * FROM saved_views WHERE id = ? AND tenant_id = ? AND user_id = ?",
            [$id, Database::tenantId(), $this->userId()]
        );

        if (!$view) {
            return $this->json(['error' => 'Bộ lọc không tồn tại'], 404);
        }

        Database::delete('saved_views', 'id = ?', [$id]);

        return $this->json(['success' => true]);
    }

    /**
     * POST /saved-views/{id}/default - Set as default view
     */
    public function setDefault($id)
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }

        $view = Database::fetch(
            "SELECT * FROM saved_views WHERE id = ? AND tenant_id = ?",
            [$id, Database::tenantId()]
        );

        if (!$view) {
            return $this->json(['error' => 'Bộ lọc không tồn tại'], 404);
        }

        // Unset previous default for same module/user
        Database::update('saved_views', ['is_default' => 0],
            'module = ? AND user_id = ? AND tenant_id = ?',
            [$view['module'], $this->userId(), Database::tenantId()]
        );

        // Set this one as default
        Database::update('saved_views', ['is_default' => 1], 'id = ?', [$id]);

        return $this->json(['success' => true]);
    }
}
