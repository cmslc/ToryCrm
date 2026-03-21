<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class Campaign extends Model
{
    protected string $table = 'campaigns';

    public function getWithRelations(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $where = 'c.tenant_id = ?';
        $params = [Database::tenantId()];

        if (!empty($filters['search'])) {
            $where .= " AND (c.campaign_code LIKE ? OR c.name LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            $params[] = $s;
            $params[] = $s;
        }

        if (!empty($filters['type'])) {
            $where .= " AND c.type = ?";
            $params[] = $filters['type'];
        }

        if (!empty($filters['status'])) {
            $where .= " AND c.status = ?";
            $params[] = $filters['status'];
        }

        $total = Database::fetch(
            "SELECT COUNT(*) as total FROM campaigns c WHERE {$where}", $params
        )['total'];

        $offset = ($page - 1) * $perPage;

        $items = Database::fetchAll(
            "SELECT c.*, u.name as owner_name, uc.name as created_by_name
             FROM campaigns c
             LEFT JOIN users u ON c.owner_id = u.id
             LEFT JOIN users uc ON c.created_by = uc.id
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

    public function getContacts(int $campaignId, int $page = 1, int $perPage = 50): array
    {
        $total = Database::fetch(
            "SELECT COUNT(*) as total FROM campaign_contacts WHERE campaign_id = ?",
            [$campaignId]
        )['total'];

        $offset = ($page - 1) * $perPage;

        $items = Database::fetchAll(
            "SELECT cc.*, c.first_name, c.last_name, c.email, c.phone
             FROM campaign_contacts cc
             JOIN contacts c ON cc.contact_id = c.id
             WHERE cc.campaign_id = ?
             ORDER BY cc.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            [$campaignId]
        );

        return [
            'items' => $items,
            'total' => (int)$total,
            'page' => $page,
            'total_pages' => ceil($total / $perPage),
        ];
    }

    public function getContactStats(int $campaignId): array
    {
        return Database::fetchAll(
            "SELECT status, COUNT(*) as count FROM campaign_contacts WHERE campaign_id = ? GROUP BY status",
            [$campaignId]
        );
    }

    public function generateCode(): string
    {
        $prefix = 'CD';
        $year = date('y');
        $last = Database::fetch(
            "SELECT campaign_code FROM campaigns WHERE campaign_code LIKE ? ORDER BY id DESC LIMIT 1",
            [$prefix . $year . '%']
        );
        $num = $last ? (int)substr($last['campaign_code'], -4) + 1 : 1;
        return $prefix . $year . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}
