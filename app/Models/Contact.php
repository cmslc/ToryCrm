<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class Contact extends Model
{
    protected string $table = 'contacts';

    public function getWithRelations(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $where = '1=1';
        $params = [];

        if (!empty($filters['search'])) {
            $where .= " AND (c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$search, $search, $search, $search]);
        }

        if (!empty($filters['status'])) {
            $where .= " AND c.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['source_id'])) {
            $where .= " AND c.source_id = ?";
            $params[] = $filters['source_id'];
        }

        if (!empty($filters['owner_id'])) {
            $where .= " AND c.owner_id = ?";
            $params[] = $filters['owner_id'];
        }

        $total = Database::fetch(
            "SELECT COUNT(*) as total FROM contacts c WHERE {$where}",
            $params
        )['total'];

        $offset = ($page - 1) * $perPage;

        $items = Database::fetchAll(
            "SELECT c.*,
                    comp.name as company_name,
                    cs.name as source_name, cs.color as source_color,
                    u.name as owner_name
             FROM contacts c
             LEFT JOIN companies comp ON c.company_id = comp.id
             LEFT JOIN contact_sources cs ON c.source_id = cs.id
             LEFT JOIN users u ON c.owner_id = u.id
             WHERE {$where}
             ORDER BY c.created_at DESC
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

    public function getDetail(int $id): ?array
    {
        return Database::fetch(
            "SELECT c.*,
                    comp.name as company_name,
                    cs.name as source_name, cs.color as source_color,
                    u.name as owner_name
             FROM contacts c
             LEFT JOIN companies comp ON c.company_id = comp.id
             LEFT JOIN contact_sources cs ON c.source_id = cs.id
             LEFT JOIN users u ON c.owner_id = u.id
             WHERE c.id = ?",
            [$id]
        );
    }

    public function countByStatus(): array
    {
        return Database::fetchAll(
            "SELECT status, COUNT(*) as count FROM contacts GROUP BY status"
        );
    }

    public function getRecent(int $limit = 5): array
    {
        return Database::fetchAll(
            "SELECT c.*, comp.name as company_name
             FROM contacts c
             LEFT JOIN companies comp ON c.company_id = comp.id
             ORDER BY c.created_at DESC LIMIT ?",
            [$limit]
        );
    }

    public function search(string $term): array
    {
        $search = '%' . $term . '%';
        return Database::fetchAll(
            "SELECT id, first_name, last_name, email, phone
             FROM contacts
             WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ?
             LIMIT 10",
            [$search, $search, $search]
        );
    }
}
