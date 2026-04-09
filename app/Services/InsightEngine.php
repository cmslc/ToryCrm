<?php

namespace App\Services;

use Core\Database;

class InsightEngine
{
    private int $tenantId;
    private int $userId;

    public function generateDailyInsights(int $tenantId, int $userId): void
    {
        $this->tenantId = $tenantId;
        $this->userId = $userId;

        // Clear old insights for today (regenerate)
        Database::query(
            "DELETE FROM smart_insights WHERE tenant_id = ? AND user_id = ? AND DATE(created_at) = CURDATE()",
            [$tenantId, $userId]
        );

        $this->getDealsToClose();
        $this->getInactiveContacts();
        $this->getOverdueTasks();
        $this->getRevenueForecast();
        $this->getAtRiskDeals();
        $this->getTopPerformers();
    }

    /**
     * Deals with expected_close_date within 7 days
     */
    private function getDealsToClose(): void
    {
        $deals = Database::fetchAll(
            "SELECT d.id, d.title, d.value, d.expected_close_date, c.first_name, c.last_name
             FROM deals d
             LEFT JOIN contacts c ON d.contact_id = c.id
             WHERE d.tenant_id = ? AND d.status = 'open'
               AND d.expected_close_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
             ORDER BY d.value DESC
             LIMIT 5",
            [$this->tenantId]
        );

        foreach ($deals as $deal) {
            $contactName = trim(($deal['first_name'] ?? '') . ' ' . ($deal['last_name'] ?? ''));
            $daysLeft = (int)((strtotime($deal['expected_close_date']) - time()) / 86400);
            $daysLabel = $daysLeft <= 0 ? 'hom nay' : "con {$daysLeft} ngay";

            $this->insertInsight(
                'deal_closing',
                'Co hoi sap chot: ' . $deal['title'],
                "Co hoi \"" . $deal['title'] . "\" tri gia " . number_format($deal['value']) . "d voi " . ($contactName ?: 'N/A') . " - {$daysLabel}.",
                '/deals/' . $deal['id'],
                'Xem co hoi',
                'ri-hand-coin-line',
                'warning',
                90
            );
        }
    }

    /**
     * Contacts with no activity in 30+ days
     */
    private function getInactiveContacts(): void
    {
        $contacts = Database::fetchAll(
            "SELECT c.id, c.first_name, c.last_name, c.email,
                    MAX(a.created_at) as last_activity,
                    DATEDIFF(NOW(), COALESCE(MAX(a.created_at), c.created_at)) as days_inactive
             FROM contacts c
             LEFT JOIN activities a ON a.contact_id = c.id
             WHERE c.tenant_id = ? AND c.is_deleted = 0
             GROUP BY c.id, c.first_name, c.last_name, c.email, c.created_at
             HAVING days_inactive >= 30
             ORDER BY days_inactive DESC
             LIMIT 5",
            [$this->tenantId]
        );

        if (count($contacts) > 0) {
            $this->insertInsight(
                'inactive_contacts',
                count($contacts) . '+ khach hang khong hoat dong',
                'Co ' . count($contacts) . ' khach hang khong co hoat dong trong 30+ ngay. Nen lien he lai de duy tri moi quan he.',
                '/contacts?sort=inactive',
                'Xem danh sach',
                'ri-user-unfollow-line',
                'danger',
                80
            );
        }
    }

    /**
     * Overdue tasks
     */
    private function getOverdueTasks(): void
    {
        $result = Database::fetch(
            "SELECT COUNT(*) as count
             FROM tasks
             WHERE tenant_id = ? AND is_deleted = 0
               AND due_date < NOW() AND status != 'done'",
            [$this->tenantId]
        );

        $count = (int)($result['count'] ?? 0);

        if ($count > 0) {
            $this->insertInsight(
                'overdue_tasks',
                $count . ' cong viec qua han',
                'Ban co ' . $count . ' cong viec da qua han can xu ly. Hay uu tien hoan thanh hoac cap nhat trang thai.',
                '/tasks?filter=overdue',
                'Xem cong viec',
                'ri-error-warning-line',
                'danger',
                95
            );
        }
    }

    /**
     * Revenue forecast - compare to last month
     */
    private function getRevenueForecast(): void
    {
        // This month's won deals
        $thisMonth = Database::fetch(
            "SELECT COALESCE(SUM(value), 0) as total
             FROM deals
             WHERE tenant_id = ? AND status = 'won'
               AND YEAR(actual_close_date) = YEAR(CURDATE())
               AND MONTH(actual_close_date) = MONTH(CURDATE())",
            [$this->tenantId]
        );

        // Last month's won deals
        $lastMonth = Database::fetch(
            "SELECT COALESCE(SUM(value), 0) as total
             FROM deals
             WHERE tenant_id = ? AND status = 'won'
               AND YEAR(actual_close_date) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
               AND MONTH(actual_close_date) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))",
            [$this->tenantId]
        );

        // Pipeline value (open deals weighted by stage probability)
        $pipeline = Database::fetch(
            "SELECT COALESCE(SUM(d.value * COALESCE(ds.probability, 50) / 100), 0) as forecast
             FROM deals d
             LEFT JOIN deal_stages ds ON d.stage_id = ds.id
             WHERE d.tenant_id = ? AND d.status = 'open'",
            [$this->tenantId]
        );

        $thisTotal = (float)($thisMonth['total'] ?? 0);
        $lastTotal = (float)($lastMonth['total'] ?? 0);
        $forecast = (float)($pipeline['forecast'] ?? 0);

        $change = $lastTotal > 0 ? round(($thisTotal - $lastTotal) / $lastTotal * 100) : 0;
        $arrow = $change >= 0 ? 'tang' : 'giam';
        $changeAbs = abs($change);

        $this->insertInsight(
            'revenue_forecast',
            'Du bao doanh thu thang nay',
            "Doanh thu chot: " . number_format($thisTotal) . "d ({$arrow} {$changeAbs}% so voi thang truoc). Du bao tu pipeline: " . number_format($forecast) . "d.",
            '/reports/revenue',
            'Xem bao cao',
            'ri-line-chart-line',
            $change >= 0 ? 'success' : 'warning',
            70
        );
    }

    /**
     * Open deals with no activity in 14+ days
     */
    private function getAtRiskDeals(): void
    {
        $deals = Database::fetchAll(
            "SELECT d.id, d.title, d.value,
                    DATEDIFF(NOW(), COALESCE(MAX(a.created_at), d.created_at)) as days_inactive
             FROM deals d
             LEFT JOIN activities a ON a.deal_id = d.id
             WHERE d.tenant_id = ? AND d.status = 'open'
             GROUP BY d.id, d.title, d.value, d.created_at
             HAVING days_inactive >= 14
             ORDER BY d.value DESC
             LIMIT 5",
            [$this->tenantId]
        );

        if (count($deals) > 0) {
            $totalValue = array_sum(array_column($deals, 'value'));
            $this->insertInsight(
                'at_risk_deals',
                count($deals) . ' co hoi co nguy co mat',
                'Co ' . count($deals) . ' co hoi (tong ' . number_format($totalValue) . 'd) khong co hoat dong trong 14+ ngay. Nen lien he ngay.',
                '/deals?filter=at_risk',
                'Xem co hoi',
                'ri-alarm-warning-line',
                'danger',
                85
            );
        }
    }

    /**
     * Users with most won deals this month
     */
    private function getTopPerformers(): void
    {
        $performers = Database::fetchAll(
            "SELECT u.name, COUNT(d.id) as won_count, SUM(d.value) as total_value
             FROM deals d
             JOIN users u ON d.owner_id = u.id
             WHERE d.tenant_id = ? AND d.status = 'won'
               AND YEAR(d.actual_close_date) = YEAR(CURDATE())
               AND MONTH(d.actual_close_date) = MONTH(CURDATE())
             GROUP BY u.id, u.name
             ORDER BY total_value DESC
             LIMIT 3",
            [$this->tenantId]
        );

        if (count($performers) > 0) {
            $top = $performers[0];
            $this->insertInsight(
                'top_performers',
                'Nhan vien xuat sac thang nay',
                $top['name'] . ' dan dau voi ' . $top['won_count'] . ' co hoi chot thanh cong, tong ' . number_format($top['total_value']) . 'd.',
                '/reports/revenue',
                'Xem chi tiet',
                'ri-trophy-line',
                'success',
                50
            );
        }
    }

    /**
     * Insert an insight record
     */
    private function insertInsight(
        string $type,
        string $title,
        string $message,
        string $actionUrl,
        string $actionLabel,
        string $icon,
        string $color,
        int $priority
    ): void {
        try {
            Database::query(
                "INSERT INTO smart_insights (tenant_id, user_id, type, title, message, action_url, action_label, icon, color, priority, expires_at, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 DAY), NOW())",
                [$this->tenantId, $this->userId, $type, $title, $message, $actionUrl, $actionLabel, $icon, $color, $priority]
            );
        } catch (\Exception $e) {
            // Silently fail
        }
    }
}
