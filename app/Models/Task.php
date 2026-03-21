<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class Task extends Model
{
    protected string $table = 'tasks';

    public function getWithRelations(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $where = '1=1';
        $params = [];

        if (!empty($filters['search'])) {
            $where .= " AND t.title LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['status'])) {
            $where .= " AND t.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['priority'])) {
            $where .= " AND t.priority = ?";
            $params[] = $filters['priority'];
        }

        if (!empty($filters['assigned_to'])) {
            $where .= " AND t.assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }

        $total = Database::fetch(
            "SELECT COUNT(*) as total FROM tasks t WHERE {$where}",
            $params
        )['total'];

        $offset = ($page - 1) * $perPage;

        $items = Database::fetchAll(
            "SELECT t.*,
                    ua.name as assigned_name,
                    uc.name as creator_name,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    d.title as deal_title
             FROM tasks t
             LEFT JOIN users ua ON t.assigned_to = ua.id
             LEFT JOIN users uc ON t.created_by = uc.id
             LEFT JOIN contacts c ON t.contact_id = c.id
             LEFT JOIN deals d ON t.deal_id = d.id
             WHERE {$where}
             ORDER BY t.due_date ASC, t.priority DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return [
            'items' => $items,
            'total' => (int)$total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage),
        ];
    }

    public function getKanban(?int $userId = null): array
    {
        $statuses = ['todo', 'in_progress', 'review', 'done'];
        $result = [];

        foreach ($statuses as $status) {
            $where = "t.status = ?";
            $params = [$status];

            if ($userId) {
                $where .= " AND t.assigned_to = ?";
                $params[] = $userId;
            }

            $result[$status] = Database::fetchAll(
                "SELECT t.*, ua.name as assigned_name
                 FROM tasks t
                 LEFT JOIN users ua ON t.assigned_to = ua.id
                 WHERE {$where}
                 ORDER BY t.priority DESC, t.due_date ASC",
                $params
            );
        }

        return $result;
    }

    public function getOverdue(?int $userId = null): array
    {
        $where = "t.due_date < NOW() AND t.status != 'done'";
        $params = [];

        if ($userId) {
            $where .= " AND t.assigned_to = ?";
            $params[] = $userId;
        }

        return Database::fetchAll(
            "SELECT t.*, ua.name as assigned_name
             FROM tasks t LEFT JOIN users ua ON t.assigned_to = ua.id
             WHERE {$where} ORDER BY t.due_date ASC",
            $params
        );
    }

    public function countByStatus(?int $userId = null): array
    {
        $where = $userId ? "WHERE assigned_to = ?" : "";
        $params = $userId ? [$userId] : [];

        return Database::fetchAll(
            "SELECT status, COUNT(*) as count FROM tasks {$where} GROUP BY status",
            $params
        );
    }
}
