<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Models\Webhook;

class WebhookController extends Controller
{
    public function index()
    {
        $webhooks = Database::fetchAll(
            "SELECT w.*, u.name as created_by_name
             FROM webhooks w
             LEFT JOIN users u ON w.created_by = u.id
             ORDER BY w.created_at DESC"
        );

        return $this->view('webhooks.index', ['webhooks' => $webhooks]);
    }

    public function create()
    {
        return $this->view('webhooks.create', ['events' => Webhook::EVENTS]);
    }

    public function store()
    {
        if (!$this->isPost()) return $this->redirect('webhooks');

        $data = $this->allInput();
        $name = trim($data['name'] ?? '');
        $url = trim($data['url'] ?? '');

        if (empty($name) || empty($url)) {
            $this->setFlash('error', 'Tên và URL không được để trống.');
            return $this->back();
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->setFlash('error', 'URL không hợp lệ.');
            return $this->back();
        }

        $events = $data['events'] ?? [];
        if (empty($events)) {
            $this->setFlash('error', 'Vui lòng chọn ít nhất 1 event.');
            return $this->back();
        }

        Database::insert('webhooks', [
            'name' => $name,
            'url' => $url,
            'secret_key' => $data['secret_key'] ?? bin2hex(random_bytes(16)),
            'events' => json_encode(array_values($events)),
            'is_active' => 1,
            'created_by' => $this->userId(),
        ]);

        $this->setFlash('success', 'Webhook đã được tạo.');
        return $this->redirect('webhooks');
    }

    public function show($id)
    {
        $webhook = Database::fetch(
            "SELECT w.*, u.name as created_by_name FROM webhooks w LEFT JOIN users u ON w.created_by = u.id WHERE w.id = ?",
            [$id]
        );

        if (!$webhook) {
            $this->setFlash('error', 'Webhook không tồn tại.');
            return $this->redirect('webhooks');
        }

        $model = new Webhook();
        $logs = $model->getLogs($id, 50);

        return $this->view('webhooks.show', [
            'webhook' => $webhook,
            'logs' => $logs,
        ]);
    }

    public function toggleActive($id)
    {
        if (!$this->isPost()) return $this->redirect('webhooks');

        $webhook = Database::fetch("SELECT * FROM webhooks WHERE id = ?", [$id]);
        if (!$webhook) return $this->redirect('webhooks');

        Database::update('webhooks', [
            'is_active' => $webhook['is_active'] ? 0 : 1,
        ], 'id = ?', [$id]);

        $this->setFlash('success', $webhook['is_active'] ? 'Đã tắt webhook.' : 'Đã bật webhook.');
        return $this->redirect('webhooks');
    }

    public function delete($id)
    {
        Database::delete('webhook_logs', 'webhook_id = ?', [$id]);
        Database::delete('webhooks', 'id = ?', [$id]);
        $this->setFlash('success', 'Webhook đã được xóa.');
        return $this->redirect('webhooks');
    }
}
