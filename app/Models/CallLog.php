<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class CallLog extends Model
{
    protected string $table = 'call_logs';

    public function getWithRelations(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        $where = '1=1';
        $params = [];

        if (!empty($filters['search'])) {
            $where .= " AND (cl.caller_number LIKE ? OR cl.callee_number LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$s, $s, $s, $s]);
        }

        if (!empty($filters['call_type'])) {
            $where .= " AND cl.call_type = ?";
            $params[] = $filters['call_type'];
        }

        if (!empty($filters['status'])) {
            $where .= " AND cl.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['user_id'])) {
            $where .= " AND cl.user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['date_from'])) {
            $where .= " AND DATE(cl.started_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where .= " AND DATE(cl.started_at) <= ?";
            $params[] = $filters['date_to'];
        }

        $total = Database::fetch(
            "SELECT COUNT(*) as total FROM call_logs cl
             LEFT JOIN contacts c ON cl.contact_id = c.id
             WHERE {$where}", $params
        )['total'];

        $offset = ($page - 1) * $perPage;

        $items = Database::fetchAll(
            "SELECT cl.*,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    comp.name as company_name,
                    u.name as user_name
             FROM call_logs cl
             LEFT JOIN contacts c ON cl.contact_id = c.id
             LEFT JOIN companies comp ON cl.company_id = comp.id
             LEFT JOIN users u ON cl.user_id = u.id
             WHERE {$where}
             ORDER BY cl.started_at DESC
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

    public function getStats(string $dateFrom = null, string $dateTo = null): array
    {
        $where = '1=1';
        $params = [];
        if ($dateFrom) { $where .= " AND DATE(started_at) >= ?"; $params[] = $dateFrom; }
        if ($dateTo) { $where .= " AND DATE(started_at) <= ?"; $params[] = $dateTo; }

        return Database::fetch(
            "SELECT
                COUNT(*) as total_calls,
                SUM(CASE WHEN call_type='inbound' THEN 1 ELSE 0 END) as inbound,
                SUM(CASE WHEN call_type='outbound' THEN 1 ELSE 0 END) as outbound,
                SUM(CASE WHEN status='answered' THEN 1 ELSE 0 END) as answered,
                SUM(CASE WHEN status='missed' THEN 1 ELSE 0 END) as missed,
                COALESCE(AVG(CASE WHEN status='answered' THEN duration END), 0) as avg_duration
             FROM call_logs WHERE {$where}",
            $params
        ) ?: [];
    }

    public function formatDuration(int $seconds): string
    {
        if ($seconds < 60) return $seconds . 's';
        $min = floor($seconds / 60);
        $sec = $seconds % 60;
        return "{$min}m {$sec}s";
    }
}
