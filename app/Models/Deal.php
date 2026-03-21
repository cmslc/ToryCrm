<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class Deal extends Model
{
    protected string $table = 'deals';

    public function getWithRelations(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $where = '1=1';
        $params = [];

        if (!empty($filters['search'])) {
            $where .= " AND d.title LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['status'])) {
            $where .= " AND d.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['stage_id'])) {
            $where .= " AND d.stage_id = ?";
            $params[] = $filters['stage_id'];
        }

        if (!empty($filters['owner_id'])) {
            $where .= " AND d.owner_id = ?";
            $params[] = $filters['owner_id'];
        }

        $total = Database::fetch(
            "SELECT COUNT(*) as total FROM deals d WHERE {$where}",
            $params
        )['total'];

        $offset = ($page - 1) * $perPage;

        $items = Database::fetchAll(
            "SELECT d.*,
                    ds.name as stage_name, ds.color as stage_color,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    comp.name as company_name,
                    u.name as owner_name
             FROM deals d
             LEFT JOIN deal_stages ds ON d.stage_id = ds.id
             LEFT JOIN contacts c ON d.contact_id = c.id
             LEFT JOIN companies comp ON d.company_id = comp.id
             LEFT JOIN users u ON d.owner_id = u.id
             WHERE {$where}
             ORDER BY d.created_at DESC
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

    public function getPipeline(): array
    {
        $stages = Database::fetchAll("SELECT * FROM deal_stages ORDER BY sort_order");

        foreach ($stages as &$stage) {
            $stage['deals'] = Database::fetchAll(
                "SELECT d.*,
                        c.first_name as contact_first_name, c.last_name as contact_last_name,
                        comp.name as company_name, u.name as owner_name
                 FROM deals d
                 LEFT JOIN contacts c ON d.contact_id = c.id
                 LEFT JOIN companies comp ON d.company_id = comp.id
                 LEFT JOIN users u ON d.owner_id = u.id
                 WHERE d.stage_id = ? AND d.status = 'open'
                 ORDER BY d.updated_at DESC",
                [$stage['id']]
            );
            $stage['total_value'] = array_sum(array_column($stage['deals'], 'value'));
            $stage['count'] = count($stage['deals']);
        }

        return $stages;
    }

    public function getStages(): array
    {
        return Database::fetchAll("SELECT * FROM deal_stages ORDER BY sort_order");
    }

    public function updateStage(int $dealId, int $stageId): void
    {
        $this->update($dealId, ['stage_id' => $stageId]);
    }

    public function getTotalByStatus(): array
    {
        return Database::fetchAll(
            "SELECT status, COUNT(*) as count, SUM(value) as total_value FROM deals GROUP BY status"
        );
    }

    public function getRevenueByMonth(int $year): array
    {
        return Database::fetchAll(
            "SELECT MONTH(actual_close_date) as month, SUM(value) as revenue
             FROM deals WHERE status = 'won' AND YEAR(actual_close_date) = ?
             GROUP BY MONTH(actual_close_date) ORDER BY month",
            [$year]
        );
    }
}
