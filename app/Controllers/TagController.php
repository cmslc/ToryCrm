<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Services\TagService;

class TagController extends Controller
{
    public function index()
    {
        return $this->redirect('settings/contact-statuses?tab=tags');
    }

    public function store()
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }

        $name = trim($this->input('name', ''));
        $color = trim($this->input('color', '#6c757d'));
        $tenantId = Database::tenantId();

        if (empty($name)) {
            return $this->json(['error' => 'Tên nhãn không được để trống'], 422);
        }

        // Check duplicate name
        $existing = Database::fetch(
            "SELECT id FROM tags WHERE tenant_id = ? AND name = ?",
            [$tenantId, $name]
        );
        if ($existing) {
            return $this->json(['error' => 'Nhãn này đã tồn tại'], 422);
        }

        $tagId = TagService::createTag($name, $color, $tenantId);

        $tag = Database::fetch("SELECT * FROM tags WHERE id = ?", [$tagId]);

        return $this->json(['success' => true, 'tag' => $tag]);
    }

    public function update($id)
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }

        $name = trim($this->input('name', ''));
        $color = trim($this->input('color', '#6c757d'));

        if (empty($name)) {
            return $this->json(['error' => 'Tên nhãn không được để trống'], 422);
        }

        Database::query(
            "UPDATE tags SET name = ?, color = ? WHERE id = ?",
            [$name, $color, $id]
        );

        return $this->json(['success' => true]);
    }

    public function delete($id)
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }

        TagService::deleteTag((int) $id);

        // Check if AJAX
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return $this->json(['success' => true]);
        }

        $this->setFlash('success', 'Đã xóa nhãn.');
        return $this->redirect('tags');
    }

    public function search()
    {
        $query = trim($this->input('q', ''));
        $tenantId = Database::tenantId();

        $tags = TagService::search($query, $tenantId);

        return $this->json(['tags' => $tags]);
    }

    public function assign()
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }

        $entityType = trim($this->input('entity_type', ''));
        $entityId = (int) $this->input('entity_id', 0);
        $tagIds = $this->input('tag_ids', []);

        if (empty($entityType) || $entityId <= 0) {
            return $this->json(['error' => 'Thiếu thông tin entity'], 422);
        }

        if (!is_array($tagIds)) {
            $tagIds = [];
        }

        TagService::syncTags($entityType, $entityId, $tagIds);

        return $this->json(['success' => true]);
    }
}
