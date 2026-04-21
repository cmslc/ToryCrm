<?php

namespace App\Services;

use Core\Database;

class CommissionService
{
    /**
     * Calculate commission when a deal is won.
     */
    public function calculateForDeal(int $dealId): ?int
    {
        $deal = Database::fetch(
            "SELECT d.*, u.name as owner_name FROM deals d LEFT JOIN users u ON d.owner_id = u.id WHERE d.id = ?",
            [$dealId]
        );
        if (!$deal || $deal['status'] !== 'won') return null;

        $tid = $deal['tenant_id'] ?? Database::tenantId();

        // Find matching commission rules for deals
        $rules = Database::fetchAll(
            "SELECT * FROM commission_rules WHERE tenant_id = ? AND is_active = 1 AND apply_to = 'deal' ORDER BY min_value ASC",
            [$tid]
        );

        foreach ($rules as $rule) {
            if ($rule['min_value'] > 0 && $deal['value'] < $rule['min_value']) continue;

            $amount = $rule['type'] === 'percent'
                ? $deal['value'] * $rule['value'] / 100
                : $rule['value'];

            // Check if commission already exists for this deal + rule
            $exists = Database::fetch(
                "SELECT id FROM commissions WHERE entity_type = 'deal' AND entity_id = ? AND rule_id = ?",
                [$dealId, $rule['id']]
            );
            if ($exists) continue;

            $commissionId = Database::insert('commissions', [
                'tenant_id' => $tid,
                'user_id' => $deal['owner_id'],
                'entity_type' => 'deal',
                'entity_id' => $dealId,
                'rule_id' => $rule['id'],
                'base_amount' => $deal['value'],
                'rate' => $rule['value'],
                'rate_type' => $rule['type'],
                'amount' => round($amount, 0),
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            return $commissionId;
        }

        return null;
    }

    /**
     * Calculate commission when an order is completed.
     */
    public function calculateForOrder(int $orderId): ?int
    {
        $order = Database::fetch(
            "SELECT o.*, u.name as owner_name FROM orders o LEFT JOIN users u ON o.created_by = u.id WHERE o.id = ?",
            [$orderId]
        );
        if (!$order) return null;

        $tid = $order['tenant_id'] ?? Database::tenantId();

        $rules = Database::fetchAll(
            "SELECT * FROM commission_rules WHERE tenant_id = ? AND is_active = 1 AND apply_to = 'order' ORDER BY min_value ASC",
            [$tid]
        );

        foreach ($rules as $rule) {
            $orderTotal = $order['total'] ?? $order['grand_total'] ?? 0;
            if ($rule['min_value'] > 0 && $orderTotal < $rule['min_value']) continue;

            $amount = $rule['type'] === 'percent'
                ? $orderTotal * $rule['value'] / 100
                : $rule['value'];

            $exists = Database::fetch(
                "SELECT id FROM commissions WHERE entity_type = 'order' AND entity_id = ? AND rule_id = ?",
                [$orderId, $rule['id']]
            );
            if ($exists) continue;

            $commissionId = Database::insert('commissions', [
                'tenant_id' => $tid,
                'user_id' => $order['created_by'],
                'entity_type' => 'order',
                'entity_id' => $orderId,
                'rule_id' => $rule['id'],
                'base_amount' => $orderTotal,
                'rate' => $rule['value'],
                'rate_type' => $rule['type'],
                'amount' => round($amount, 0),
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            return $commissionId;
        }

        return null;
    }

    /**
     * Get commissions for a user, optionally filtered by period (YYYY-MM).
     */
    public function getForUser(int $userId, ?string $period = null, ?int $tenantId = null): array
    {
        $where = "c.user_id = ?";
        $params = [$userId];
        if ($tenantId) {
            $where .= " AND c.tenant_id = ?";
            $params[] = $tenantId;
        }

        if ($period) {
            $where .= " AND DATE_FORMAT(c.created_at, '%Y-%m') = ?";
            $params[] = $period;
        }

        return Database::fetchAll(
            "SELECT c.*, u.name as user_name, cr.name as rule_name
             FROM commissions c
             LEFT JOIN users u ON c.user_id = u.id
             LEFT JOIN commission_rules cr ON c.rule_id = cr.id
             WHERE {$where}
             ORDER BY c.created_at DESC",
            $params
        );
    }

    /**
     * Get total commissions by user for a period.
     */
    public function getSummary(int $tenantId, ?string $period = null): array
    {
        $where = "c.tenant_id = ?";
        $params = [$tenantId];

        if ($period) {
            $where .= " AND DATE_FORMAT(c.created_at, '%Y-%m') = ?";
            $params[] = $period;
        }

        return Database::fetchAll(
            "SELECT c.user_id, u.name as user_name,
                    SUM(CASE WHEN c.status = 'pending' THEN c.amount ELSE 0 END) as pending_total,
                    SUM(CASE WHEN c.status = 'approved' THEN c.amount ELSE 0 END) as approved_total,
                    SUM(CASE WHEN c.status = 'paid' THEN c.amount ELSE 0 END) as paid_total,
                    SUM(c.amount) as total,
                    COUNT(*) as count
             FROM commissions c
             LEFT JOIN users u ON c.user_id = u.id
             WHERE {$where}
             GROUP BY c.user_id, u.name
             ORDER BY total DESC",
            $params
        );
    }

    /**
     * Approve a commission.
     */
    public function approve(int $commissionId): bool
    {
        $commission = Database::fetch("SELECT * FROM commissions WHERE id = ?", [$commissionId]);
        if (!$commission || $commission['status'] !== 'pending') return false;

        Database::update('commissions', [
            'status' => 'approved',
            'approved_at' => date('Y-m-d H:i:s'),
            'approved_by' => $_SESSION['user']['id'] ?? null,
        ], 'id = ?', [$commissionId]);

        return true;
    }

    /**
     * Mark commission as paid.
     */
    public function markPaid(int $commissionId): bool
    {
        $commission = Database::fetch("SELECT * FROM commissions WHERE id = ?", [$commissionId]);
        if (!$commission || $commission['status'] !== 'approved') return false;

        Database::update('commissions', [
            'status' => 'paid',
            'paid_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$commissionId]);

        return true;
    }

    /**
     * Get monthly commission report for a year.
     */
    public function getMonthlyReport(int $tenantId, int $year): array
    {
        return Database::fetchAll(
            "SELECT c.user_id, u.name as user_name,
                    MONTH(c.created_at) as month,
                    SUM(c.amount) as total
             FROM commissions c
             LEFT JOIN users u ON c.user_id = u.id
             WHERE c.tenant_id = ? AND YEAR(c.created_at) = ?
             GROUP BY c.user_id, u.name, MONTH(c.created_at)
             ORDER BY u.name, month",
            [$tenantId, $year]
        );
    }
}
