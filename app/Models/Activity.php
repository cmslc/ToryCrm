<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class Activity extends Model
{
    protected string $table = 'activities';

    public function getRecent(int $limit = 20, ?int $userId = null): array
    {
        $where = $userId ? "AND a.user_id = ?" : "";
        $params = $userId ? [$userId] : [];

        return Database::fetchAll(
            "SELECT a.*, u.name as user_name, u.avatar as user_avatar,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    d.title as deal_title, comp.name as company_name
             FROM activities a
             LEFT JOIN users u ON a.user_id = u.id
             LEFT JOIN contacts c ON a.contact_id = c.id
             LEFT JOIN deals d ON a.deal_id = d.id
             LEFT JOIN companies comp ON a.company_id = comp.id
             WHERE 1=1 {$where}
             ORDER BY a.created_at DESC
             LIMIT ?",
            array_merge($params, [$limit])
        );
    }

    public function log(string $type, string $title, array $data = []): int
    {
        return $this->create(array_merge([
            'type' => $type,
            'title' => $title,
            'user_id' => $_SESSION['user']['id'] ?? null,
        ], $data));
    }
}
