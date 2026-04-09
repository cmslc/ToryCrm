<?php

namespace App\Services;

use Core\Database;

class RevenueAnalytics
{
    /**
     * Take a daily revenue snapshot
     */
    public function takeSnapshot(int $tenantId): void
    {
        $today = date('Y-m-d');

        // Check if already taken today
        $existing = Database::fetch(
            "SELECT id FROM revenue_snapshots WHERE tenant_id = ? AND date = ?",
            [$tenantId, $today]
        );

        $totalContacts = (int)(Database::fetch(
            "SELECT COUNT(*) as c FROM contacts WHERE tenant_id = ? AND is_deleted = 0",
            [$tenantId]
        )['c'] ?? 0);

        $newContacts = (int)(Database::fetch(
            "SELECT COUNT(*) as c FROM contacts WHERE tenant_id = ? AND is_deleted = 0 AND DATE(created_at) = CURDATE()",
            [$tenantId]
        )['c'] ?? 0);

        $totalDeals = (int)(Database::fetch(
            "SELECT COUNT(*) as c FROM deals WHERE tenant_id = ?",
            [$tenantId]
        )['c'] ?? 0);

        $wonDeals = (int)(Database::fetch(
            "SELECT COUNT(*) as c FROM deals WHERE tenant_id = ? AND status = 'won'
             AND YEAR(actual_close_date) = YEAR(CURDATE()) AND MONTH(actual_close_date) = MONTH(CURDATE())",
            [$tenantId]
        )['c'] ?? 0);

        $lostDeals = (int)(Database::fetch(
            "SELECT COUNT(*) as c FROM deals WHERE tenant_id = ? AND status = 'lost'
             AND YEAR(actual_close_date) = YEAR(CURDATE()) AND MONTH(actual_close_date) = MONTH(CURDATE())",
            [$tenantId]
        )['c'] ?? 0);

        $dealRevenue = (float)(Database::fetch(
            "SELECT COALESCE(SUM(value), 0) as total FROM deals WHERE tenant_id = ? AND status = 'won'
             AND YEAR(actual_close_date) = YEAR(CURDATE()) AND MONTH(actual_close_date) = MONTH(CURDATE())",
            [$tenantId]
        )['total'] ?? 0);

        $orderRevenue = 0;
        try {
            $orderRevenue = (float)(Database::fetch(
                "SELECT COALESCE(SUM(total), 0) as total FROM orders WHERE tenant_id = ? AND status = 'completed'
                 AND YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())",
                [$tenantId]
            )['total'] ?? 0);
        } catch (\Exception $e) {}

        $avgDealSize = $wonDeals > 0 ? round($dealRevenue / $wonDeals, 2) : 0;

        $avgCloseDays = (int)(Database::fetch(
            "SELECT COALESCE(AVG(DATEDIFF(actual_close_date, created_at)), 0) as avg_days
             FROM deals WHERE tenant_id = ? AND status = 'won' AND actual_close_date IS NOT NULL",
            [$tenantId]
        )['avg_days'] ?? 0);

        $closedDeals = $wonDeals + $lostDeals;
        $conversionRate = $closedDeals > 0 ? round(($wonDeals / $closedDeals) * 100, 2) : 0;

        $data = [
            'total_contacts' => $totalContacts,
            'new_contacts' => $newContacts,
            'total_deals' => $totalDeals,
            'won_deals' => $wonDeals,
            'lost_deals' => $lostDeals,
            'deal_revenue' => $dealRevenue,
            'order_revenue' => $orderRevenue,
            'avg_deal_size' => $avgDealSize,
            'avg_close_days' => $avgCloseDays,
            'conversion_rate' => $conversionRate,
        ];

        if ($existing) {
            Database::update('revenue_snapshots', $data, 'tenant_id = ? AND date = ?', [$tenantId, $today]);
        } else {
            $data['tenant_id'] = $tenantId;
            $data['date'] = $today;
            Database::query(
                "INSERT INTO revenue_snapshots (tenant_id, date, total_contacts, new_contacts, total_deals, won_deals, lost_deals, deal_revenue, order_revenue, avg_deal_size, avg_close_days, conversion_rate)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [$tenantId, $today, $totalContacts, $newContacts, $totalDeals, $wonDeals, $lostDeals, $dealRevenue, $orderRevenue, $avgDealSize, $avgCloseDays, $conversionRate]
            );
        }
    }

    /**
     * Monthly revenue trend for last N months
     */
    public function getMonthlyTrend(int $tenantId, int $months = 12): array
    {
        return Database::fetchAll(
            "SELECT
                DATE_FORMAT(date, '%Y-%m') as month,
                MAX(deal_revenue) as deal_revenue,
                MAX(order_revenue) as order_revenue,
                MAX(won_deals) as won_deals,
                MAX(new_contacts) as new_contacts
             FROM revenue_snapshots
             WHERE tenant_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
             GROUP BY DATE_FORMAT(date, '%Y-%m')
             ORDER BY month ASC",
            [$tenantId, $months]
        );
    }

    /**
     * Sales velocity: avg days from deal created to won, by stage
     */
    public function getSalesVelocity(int $tenantId): array
    {
        return Database::fetchAll(
            "SELECT ds.name as stage_name, ds.color,
                    COUNT(d.id) as deal_count,
                    COALESCE(AVG(DATEDIFF(d.actual_close_date, d.created_at)), 0) as avg_days
             FROM deals d
             JOIN deal_stages ds ON d.stage_id = ds.id
             WHERE d.tenant_id = ? AND d.status = 'won' AND d.actual_close_date IS NOT NULL
             GROUP BY ds.id, ds.name, ds.color
             ORDER BY ds.sort_order",
            [$tenantId]
        );
    }

    /**
     * Conversion funnel: count deals per stage
     */
    public function getConversionFunnel(int $tenantId): array
    {
        return Database::fetchAll(
            "SELECT ds.name, ds.color, COUNT(d.id) as count, COALESCE(SUM(d.value), 0) as total_value
             FROM deal_stages ds
             LEFT JOIN deals d ON d.stage_id = ds.id AND d.status = 'open' AND d.tenant_id = ?
             GROUP BY ds.id, ds.name, ds.color
             ORDER BY ds.sort_order",
            [$tenantId]
        );
    }

    /**
     * Top deals by contact source
     */
    public function getTopDealsBySource(int $tenantId): array
    {
        return Database::fetchAll(
            "SELECT COALESCE(c.source, 'Khong ro') as source,
                    COUNT(d.id) as deal_count,
                    COALESCE(SUM(d.value), 0) as total_value
             FROM deals d
             LEFT JOIN contacts c ON d.contact_id = c.id
             WHERE d.tenant_id = ? AND d.status = 'won'
             GROUP BY c.source
             ORDER BY total_value DESC
             LIMIT 10",
            [$tenantId]
        );
    }

    /**
     * Cohort analysis: contacts grouped by creation month, track conversion
     */
    public function getCohortAnalysis(int $tenantId): array
    {
        return Database::fetchAll(
            "SELECT
                DATE_FORMAT(c.created_at, '%Y-%m') as cohort_month,
                COUNT(DISTINCT c.id) as total_contacts,
                COUNT(DISTINCT d.id) as total_deals,
                COUNT(DISTINCT CASE WHEN d.status = 'won' THEN d.id END) as won_deals,
                COALESCE(SUM(CASE WHEN d.status = 'won' THEN d.value ELSE 0 END), 0) as revenue
             FROM contacts c
             LEFT JOIN deals d ON d.contact_id = c.id
             WHERE c.tenant_id = ? AND c.is_deleted = 0
             GROUP BY DATE_FORMAT(c.created_at, '%Y-%m')
             ORDER BY cohort_month DESC
             LIMIT 12",
            [$tenantId]
        );
    }
}
