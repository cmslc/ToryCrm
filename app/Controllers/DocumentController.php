<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class DocumentController extends Controller
{
    private function checkPlugin(): bool
    {
        try {
            $installed = \App\Services\PluginManager::getInstalled($this->tenantId());
            foreach ($installed as $p) {
                if ($p['slug'] === 'documents' && $p['tenant_active']) return true;
            }
        } catch (\Exception $e) {}
        $this->setFlash('error', 'Plugin Tài liệu chưa được cài đặt.');
        $this->redirect('plugins/marketplace');
        return false;
    }

    public function index()
    {
        if (!$this->checkPlugin()) return;
        $tid = Database::tenantId();
        $category = $this->input('category');
        $search = $this->input('search');
        $entityType = $this->input('entity_type');

        $where = ["d.tenant_id = ?"];
        $params = [$tid];
        if ($category) { $where[] = "d.category = ?"; $params[] = $category; }
        if ($search) { $where[] = "(d.title LIKE ? OR d.file_name LIKE ?)"; $s = "%{$search}%"; $params = array_merge($params, [$s, $s]); }
        if ($entityType) { $where[] = "d.entity_type = ?"; $params[] = $entityType; }

        $whereSql = implode(' AND ', $where);
        $page = max(1, (int)($this->input('page') ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $total = Database::fetch("SELECT COUNT(*) as cnt FROM documents d WHERE $whereSql", $params)['cnt'];
        $docs = Database::fetchAll(
            "SELECT d.*, u.name as uploaded_by_name FROM documents d LEFT JOIN users u ON d.uploaded_by = u.id WHERE $whereSql ORDER BY d.created_at DESC LIMIT $limit OFFSET $offset",
            $params
        );

        $categories = Database::fetchAll("SELECT DISTINCT category FROM documents WHERE tenant_id = ? AND category IS NOT NULL ORDER BY category", [$tid]);
        $totalPages = ceil($total / $limit);
        $filters = compact('category', 'search', 'entityType');

        return $this->view('documents.index', compact('docs', 'categories', 'page', 'totalPages', 'total', 'filters'));
    }

    public function upload()
    {
        if (!$this->isPost()) return $this->redirect('documents');
        $tid = Database::tenantId();

        $file = $_FILES['file'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('error', 'Vui lòng chọn file.');
            return $this->redirect('documents');
        }

        $maxSize = 20 * 1024 * 1024; // 20MB
        if ($file['size'] > $maxSize) {
            $this->setFlash('error', 'File quá lớn (tối đa 20MB).');
            return $this->redirect('documents');
        }

        $uploadDir = BASE_PATH . '/public/uploads/documents/' . $tid;
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $filePath = 'uploads/documents/' . $tid . '/' . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $fileName)) {
            $this->setFlash('error', 'Không thể lưu file.');
            return $this->redirect('documents');
        }

        Database::insert('documents', [
            'tenant_id' => $tid,
            'title' => trim($this->input('title') ?? '') ?: pathinfo($file['name'], PATHINFO_FILENAME),
            'file_name' => $file['name'],
            'file_path' => $filePath,
            'file_size' => $file['size'],
            'file_type' => $ext,
            'category' => trim($this->input('category') ?? '') ?: null,
            'entity_type' => $this->input('entity_type') ?: null,
            'entity_id' => $this->input('entity_id') ? (int)$this->input('entity_id') : null,
            'is_shared' => $this->input('is_shared') ? 1 : 0,
            'note' => trim($this->input('note') ?? '') ?: null,
            'uploaded_by' => $this->userId(),
        ]);

        $this->setFlash('success', 'Đã tải lên tài liệu.');
        return $this->redirect('documents');
    }

    public function download($id)
    {
        $tid = Database::tenantId();
        $doc = Database::fetch("SELECT * FROM documents WHERE id = ? AND tenant_id = ?", [$id, $tid]);
        if (!$doc) {
            $this->setFlash('error', 'Tài liệu không tồn tại.');
            return $this->redirect('documents');
        }

        $fullPath = BASE_PATH . '/public/' . $doc['file_path'];
        if (!file_exists($fullPath)) {
            $this->setFlash('error', 'File không tồn tại trên server.');
            return $this->redirect('documents');
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $doc['file_name'] . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    public function delete($id)
    {
        if (!$this->isPost()) return $this->redirect('documents');
        $tid = Database::tenantId();
        $doc = Database::fetch("SELECT * FROM documents WHERE id = ? AND tenant_id = ?", [$id, $tid]);
        if (!$doc) {
            $this->setFlash('error', 'Không tìm thấy.');
            return $this->redirect('documents');
        }

        // Delete file
        $fullPath = BASE_PATH . '/public/' . $doc['file_path'];
        if (file_exists($fullPath)) unlink($fullPath);

        Database::query("DELETE FROM documents WHERE id = ? AND tenant_id = ?", [$id, $tid]);
        $this->setFlash('success', 'Đã xóa tài liệu.');
        return $this->redirect('documents');
    }
}
