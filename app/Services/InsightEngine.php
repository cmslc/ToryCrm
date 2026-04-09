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
            $daysLabel = $daysLeft <= 0 ? 'hôm nay' : "còn {$daysLeft} ngày";

            $this->insertInsight(
                'deal_closing',
                'Cơ hội sắp chốt: ' . $deal['title'],
                "Cơ hội \"{$deal['title']}\" trị giá " . number_format($deal['value']) . "đ với " . ($contactName ?: 'N/A') . " - {$daysLabel}.",
                '/deals/' . $deal['id'],
                'Xem cơ hội',
                'ri-hand-coin-line',
                'warning',
                90
            );
        }
    }

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
                count($contacts) . '+ khách hàng không hoạt động',
                'Có ' . count($contacts) . ' khách hàng không có hoạt động trong 30+ ngày. Nên liên hệ lại để duy trì mối quan hệ.',
                '/contacts?sort=inactive',
                'Xem danh sách',
                'ri-user-unfollow-line',
                'danger',
                80
            );
        }
    }

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
                $count . ' công việc quá hạn',
                'Bạn có ' . $count . ' công việc đã quá hạn cần xử lý. Hãy ưu tiên hoàn thành hoặc cập nhật trạng thái.',
                '/tasks?filter=overdue',
                'Xem công việc',
                'ri-error-warning-line',
                'danger',
                95
            );
        }
    }

    private function getRevenueForecast(): void
    {
        $thisMonth = Database::fetch(
            "SELECT COALESCE(SUM(value), 0) as total
             FROM deals
             WHERE tenant_id = ? AND status = 'won'
               AND YEAR(actual_close_date) = YEAR(CURDATE())
               AND MONTH(actual_close_date) = MONTH(CURDATE())",
            [$this->tenantId]
        );

        $lastMonth = Database::fetch(
            "SELECT COALESCE(SUM(value), 0) as total
             FROM deals
             WHERE tenant_id = ? AND status = 'won'
               AND YEAR(actual_close_date) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
               AND MONTH(actual_close_date) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))",
            [$this->tenantId]
        );

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
        $arrow = $change >= 0 ? 'tăng' : 'giảm';
        $changeAbs = abs($change);

        $this->insertInsight(
            'revenue_forecast',
            'Dự báo doanh thu tháng này',
            "Doanh thu chốt: " . number_format($thisTotal) . "đ ({$arrow} {$changeAbs}% so với tháng trước). Dự báo từ pipeline: " . number_format($forecast) . "đ.",
            '/reports/revenue',
            'Xem báo cáo',
            'ri-line-chart-line',
            $change >= 0 ? 'success' : 'warning',
            70
        );
    }

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
                count($deals) . ' cơ hội có nguy cơ mất',
                'Có ' . count($deals) . ' cơ hội (tổng ' . number_format($totalValue) . 'đ) không có hoạt động trong 14+ ngày. Nên liên hệ ngay.',
                '/deals?filter=at_risk',
                'Xem cơ hội',
                'ri-alarm-warning-line',
                'danger',
                85
            );
        }
    }

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
                'Nhân viên xuất sắc tháng này',
                $top['name'] . ' dẫn đầu với ' . $top['won_count'] . ' cơ hội chốt thành công, tổng ' . number_format($top['total_value']) . 'đ.',
                '/reports/revenue',
                'Xem chi tiết',
                'ri-trophy-line',
                'success',
                50
            );
        }
    }

    private function insertInsight(
        string $type, string $title, string $message,
        string $actionUrl, string $actionLabel, string $icon,
        string $color, int $priority
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
