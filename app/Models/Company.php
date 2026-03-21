<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class Company extends Model
{
    protected string $table = 'companies';

    public function getWithRelations(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $where = '1=1';
        $params = [];

        if (!empty($filters['search'])) {
            $where .= " AND (c.name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$search, $search, $search]);
        }

        if (!empty($filters['industry'])) {
            $where .= " AND c.industry = ?";
            $params[] = $filters['industry'];
        }

        $total = Database::fetch(
            "SELECT COUNT(*) as total FROM companies c WHERE {$where}",
            $params
        )['total'];

        $offset = ($page - 1) * $perPage;

        $items = Database::fetchAll(
            "SELECT c.*,
                    u.name as owner_name,
                    (SELECT COUNT(*) FROM contacts WHERE company_id = c.id) as contact_count,
                    (SELECT COUNT(*) FROM deals WHERE company_id = c.id AND status = 'open') as deal_count
             FROM companies c
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
            "SELECT c.*, u.name as owner_name
             FROM companies c
             LEFT JOIN users u ON c.owner_id = u.id
             WHERE c.id = ?",
            [$id]
        );
    }

    public function search(string $term): array
    {
        $search = '%' . $term . '%';
        return Database::fetchAll(
            "SELECT id, name, email FROM companies WHERE name LIKE ? OR email LIKE ? LIMIT 10",
            [$search, $search]
        );
    }
}
