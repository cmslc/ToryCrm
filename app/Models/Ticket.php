<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class Ticket extends Model
{
    protected string $table = 'tickets';

    public function getWithRelations(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $where = 't.tenant_id = ?';
        $params = [Database::tenantId()];

        if (!empty($filters['search'])) {
            $where .= " AND (t.ticket_code LIKE ? OR t.title LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            $params[] = $s;
            $params[] = $s;
        }

        if (!empty($filters['status'])) {
            $where .= " AND t.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['priority'])) {
            $where .= " AND t.priority = ?";
            $params[] = $filters['priority'];
        }

        if (!empty($filters['category_id'])) {
            $where .= " AND t.category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['assigned_to'])) {
            $where .= " AND t.assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }

        $total = Database::fetch(
            "SELECT COUNT(*) as total FROM tickets t WHERE {$where}", $params
        )['total'];

        $offset = ($page - 1) * $perPage;

        $items = Database::fetchAll(
            "SELECT t.*,
                    tc.name as category_name, tc.color as category_color,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    comp.name as company_name,
                    u.name as assigned_name,
                    uc.name as created_by_name
             FROM tickets t
             LEFT JOIN ticket_categories tc ON t.category_id = tc.id
             LEFT JOIN contacts c ON t.contact_id = c.id
             LEFT JOIN companies comp ON t.company_id = comp.id
             LEFT JOIN users u ON t.assigned_to = u.id
             LEFT JOIN users uc ON t.created_by = uc.id
             WHERE {$where}
             ORDER BY FIELD(t.priority, 'urgent', 'high', 'medium', 'low'), t.created_at DESC
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

    public function getCategories(): array
    {
        return Database::fetchAll("SELECT * FROM ticket_categories ORDER BY sort_order, name");
    }

    public function getComments(int $ticketId): array
    {
        return Database::fetchAll(
            "SELECT tc.*, u.name as user_name
             FROM ticket_comments tc
             LEFT JOIN users u ON tc.user_id = u.id
             WHERE tc.ticket_id = ?
             ORDER BY tc.created_at ASC",
            [$ticketId]
        );
    }

    public function generateCode(): string
    {
        $prefix = 'TK';
        $year = date('y');
        $month = date('m');

        $last = Database::fetch(
            "SELECT ticket_code FROM tickets WHERE ticket_code LIKE ? ORDER BY id DESC LIMIT 1",
            [$prefix . $year . $month . '%']
        );

        $num = $last ? (int)substr($last['ticket_code'], -4) + 1 : 1;
        return $prefix . $year . $month . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    public function getStatsByStatus(): array
    {
        return Database::fetchAll(
            "SELECT status, COUNT(*) as count FROM tickets WHERE tenant_id = ? GROUP BY status",
            [Database::tenantId()]
        );
    }

    public function getOverdue(): array
    {
        return Database::fetchAll(
            "SELECT t.*, u.name as assigned_name
             FROM tickets t LEFT JOIN users u ON t.assigned_to = u.id
             WHERE t.due_date < NOW() AND t.status NOT IN ('resolved', 'closed')
             ORDER BY t.due_date ASC"
        );
    }
}
