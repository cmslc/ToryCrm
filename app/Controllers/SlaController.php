<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class SlaController extends Controller
{
    public function index()
    {
        $policies = Database::fetchAll(
            "SELECT sp.*, u.name as escalate_to_name
             FROM sla_policies sp
             LEFT JOIN users u ON sp.escalate_to = u.id
             ORDER BY FIELD(sp.priority, 'urgent', 'high', 'medium', 'low'), sp.created_at DESC"
        );

        // Stats
        $totalTickets = (int) (Database::fetch("SELECT COUNT(*) as cnt FROM tickets WHERE sla_policy_id IS NOT NULL")['cnt'] ?? 0);
        $breachedTickets = (int) (Database::fetch("SELECT COUNT(*) as cnt FROM tickets WHERE sla_breached = 1")['cnt'] ?? 0);

        $avgFirstResponse = Database::fetch(
            "SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, first_response_at)) as avg_hours
             FROM tickets WHERE first_response_at IS NOT NULL AND sla_policy_id IS NOT NULL"
        );

        $avgResolution = Database::fetch(
            "SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours
             FROM tickets WHERE resolved_at IS NOT NULL AND sla_policy_id IS NOT NULL"
        );

        return $this->view('sla.index', [
            'policies' => $policies,
            'stats' => [
                'total_tickets' => $totalTickets,
                'breached_tickets' => $breachedTickets,
                'avg_first_response' => round((float) ($avgFirstResponse['avg_hours'] ?? 0), 1),
                'avg_resolution' => round((float) ($avgResolution['avg_hours'] ?? 0), 1),
            ],
        ]);
    }

    public function create()
    {
        $users = Database::fetchAll("SELECT id, name FROM users WHERE is_active = 1 ORDER BY name");

        return $this->view('sla.create', [
            'users' => $users,
        ]);
    }

    public function store()
    {
        if (!$this->isPost()) return $this->redirect('sla');

        $data = $this->allInput();
        $name = trim($data['name'] ?? '');

        if (empty($name)) {
            $this->setFlash('error', 'Tên chính sách không được để trống.');
            return $this->back();
        }

        Database::insert('sla_policies', [
            'name' => $name,
            'priority' => $data['priority'] ?? 'medium',
            'first_response_hours' => (float) ($data['first_response_hours'] ?? 4),
            'resolution_hours' => (float) ($data['resolution_hours'] ?? 24),
            'escalate_to' => !empty($data['escalate_to']) ? $data['escalate_to'] : null,
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'created_by' => $this->userId(),
        ]);

        $this->setFlash('success', 'Chính sách SLA đã được tạo.');
        return $this->redirect('sla');
    }

    public function edit($id)
    {
        $policy = Database::fetch("SELECT * FROM sla_policies WHERE id = ?", [$id]);

        if (!$policy) {
            $this->setFlash('error', 'Chính sách SLA không tồn tại.');
            return $this->redirect('sla');
        }

        $users = Database::fetchAll("SELECT id, name FROM users WHERE is_active = 1 ORDER BY name");

        return $this->view('sla.edit', [
            'policy' => $policy,
            'users' => $users,
        ]);
    }

    public function update($id)
    {
        if (!$this->isPost()) return $this->redirect('sla');

        $policy = Database::fetch("SELECT * FROM sla_policies WHERE id = ?", [$id]);

        if (!$policy) {
            $this->setFlash('error', 'Chính sách SLA không tồn tại.');
            return $this->redirect('sla');
        }

        $data = $this->allInput();
        $name = trim($data['name'] ?? '');

        if (empty($name)) {
            $this->setFlash('error', 'Tên chính sách không được để trống.');
            return $this->back();
        }

        Database::update('sla_policies', [
            'name' => $name,
            'priority' => $data['priority'] ?? 'medium',
            'first_response_hours' => (float) ($data['first_response_hours'] ?? 4),
            'resolution_hours' => (float) ($data['resolution_hours'] ?? 24),
            'escalate_to' => !empty($data['escalate_to']) ? $data['escalate_to'] : null,
            'is_active' => isset($data['is_active']) ? 1 : 0,
        ], 'id = ?', [$id]);

        $this->setFlash('success', 'Chính sách SLA đã được cập nhật.');
        return $this->redirect('sla');
    }

    public function delete($id)
    {
        if (!$this->isPost()) return $this->redirect('sla');

        $policy = Database::fetch("SELECT * FROM sla_policies WHERE id = ?", [$id]);

        if (!$policy) {
            $this->setFlash('error', 'Chính sách SLA không tồn tại.');
            return $this->redirect('sla');
        }

        // Remove SLA reference from tickets
        Database::query("UPDATE tickets SET sla_policy_id = NULL WHERE sla_policy_id = ?", [$id]);
        Database::delete('sla_policies', 'id = ?', [$id]);

        $this->setFlash('success', 'Chính sách SLA đã được xóa.');
        return $this->redirect('sla');
    }
}
