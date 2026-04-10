<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Services\InsightEngine;
use App\Services\HealthScoreCalculator;
use App\Services\RevenueAnalytics;

class DashboardController extends Controller
{
    public function index()
    {
        $tid = $this->tenantId();
        $uid = $this->userId();

        // Generate insights if none exist today
        try {
            $todayInsights = Database::fetch(
                "SELECT COUNT(*) as c FROM smart_insights WHERE tenant_id = ? AND user_id = ? AND DATE(created_at) = CURDATE()",
                [$tid, $uid]
            );
            if (((int)($todayInsights['c'] ?? 0)) === 0) {
                $engine = new InsightEngine();
                $engine->generateDailyInsights($tid, $uid);
            }
        } catch (\Exception $e) {
            // Table may not exist yet
        }

        // Smart insights (not dismissed, today)
        $insights = [];
        try {
            $insights = Database::fetchAll(
                "SELECT * FROM smart_insights
                 WHERE tenant_id = ? AND user_id = ? AND is_dismissed = 0
                 ORDER BY priority DESC, created_at DESC
                 LIMIT 4",
                [$tid, $uid]
            );
        } catch (\Exception $e) {}

        // ---- Stats with last month comparison ----
        $totalContacts = (int)(Database::fetch("SELECT COUNT(*) as c FROM contacts WHERE is_deleted=0 AND tenant_id=?", [$tid])['c'] ?? 0);
        $lastMonthContacts = (int)(Database::fetch(
            "SELECT COUNT(*) as c FROM contacts WHERE is_deleted=0 AND tenant_id=? AND created_at < DATE_FORMAT(CURDATE(), '%Y-%m-01')",
            [$tid]
        )['c'] ?? 0);

        $totalDeals = (int)(Database::fetch("SELECT COUNT(*) as c FROM deals WHERE tenant_id=? AND status='open'", [$tid])['c'] ?? 0);
        $lastMonthDeals = (int)(Database::fetch(
            "SELECT COUNT(*) as c FROM deals WHERE tenant_id=? AND status='open' AND created_at < DATE_FORMAT(CURDATE(), '%Y-%m-01')",
            [$tid]
        )['c'] ?? 0);

        $totalRevenue = (float)(Database::fetch("SELECT COALESCE(SUM(value),0) as total FROM deals WHERE status='won' AND tenant_id=?", [$tid])['total'] ?? 0);
        $thisMonthRevenue = (float)(Database::fetch(
            "SELECT COALESCE(SUM(value),0) as total FROM deals WHERE status='won' AND tenant_id=?
             AND YEAR(actual_close_date)=YEAR(CURDATE()) AND MONTH(actual_close_date)=MONTH(CURDATE())",
            [$tid]
        )['total'] ?? 0);
        $lastMonthRevenue = (float)(Database::fetch(
            "SELECT COALESCE(SUM(value),0) as total FROM deals WHERE status='won' AND tenant_id=?
             AND YEAR(actual_close_date)=YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
             AND MONTH(actual_close_date)=MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))",
            [$tid]
        )['total'] ?? 0);

        $totalOrders = 0;
        $lastMonthOrders = 0;
        try {
            $totalOrders = (int)(Database::fetch("SELECT COUNT(*) as c FROM orders WHERE type='order' AND tenant_id=?", [$tid])['c'] ?? 0);
            $lastMonthOrders = (int)(Database::fetch(
                "SELECT COUNT(*) as c FROM orders WHERE type='order' AND tenant_id=? AND created_at < DATE_FORMAT(CURDATE(), '%Y-%m-01')",
                [$tid]
            )['c'] ?? 0);
        } catch (\Exception $e) {}

        $stats = [
            'total_contacts' => $totalContacts,
            'contacts_change' => $lastMonthContacts > 0 ? round(($totalContacts - $lastMonthContacts) / $lastMonthContacts * 100) : 0,
            'total_deals' => $totalDeals,
            'deals_change' => $lastMonthDeals > 0 ? round(($totalDeals - $lastMonthDeals) / $lastMonthDeals * 100) : 0,
            'total_revenue' => $totalRevenue,
            'this_month_revenue' => $thisMonthRevenue,
            'revenue_change' => $lastMonthRevenue > 0 ? round(($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue * 100) : 0,
            'total_orders' => $totalOrders,
            'orders_change' => $lastMonthOrders > 0 ? round(($totalOrders - $lastMonthOrders) / $lastMonthOrders * 100) : 0,
        ];

        // ---- Health scores distribution ----
        $healthDist = ['low' => 0, 'medium' => 0, 'high' => 0, 'critical' => 0];
        $criticalContacts = [];
        try {
            $healthRows = Database::fetchAll(
                "SELECT hs.churn_risk, COUNT(*) as cnt
                 FROM health_scores hs
                 JOIN contacts c ON c.id = hs.contact_id AND c.tenant_id = ? AND c.is_deleted = 0
                 GROUP BY hs.churn_risk",
                [$tid]
            );
            foreach ($healthRows as $hr) {
                $healthDist[$hr['churn_risk']] = (int)$hr['cnt'];
            }
            $criticalContacts = Database::fetchAll(
                "SELECT c.id, c.first_name, c.last_name, c.email, hs.overall_score, hs.churn_risk, hs.days_since_interaction
                 FROM health_scores hs
                 JOIN contacts c ON c.id = hs.contact_id AND c.tenant_id = ? AND c.is_deleted = 0
                 WHERE hs.churn_risk IN ('critical', 'high')
                 ORDER BY hs.overall_score ASC
                 LIMIT 5",
                [$tid]
            );
        } catch (\Exception $e) {}

        // ---- Revenue chart data (last 12 months) ----
        $year = date('Y');
        $revenueRows = Database::fetchAll(
            "SELECT MONTH(actual_close_date) as month, SUM(value) as revenue
             FROM deals WHERE status = 'won' AND YEAR(actual_close_date) = ? AND tenant_id = ?
             GROUP BY MONTH(actual_close_date)",
            [$year, $tid]
        );
        $revenueData = array_fill(0, 12, 0);
        foreach ($revenueRows as $r) {
            $revenueData[$r['month'] - 1] = (float)$r['revenue'];
        }

        // ---- Pipeline / Funnel ----
        $pipelineSummary = Database::fetchAll(
            "SELECT ds.name, ds.color, COUNT(d.id) as count, COALESCE(SUM(d.value), 0) as total_value
             FROM deal_stages ds
             LEFT JOIN deals d ON d.stage_id = ds.id AND d.status = 'open' AND d.tenant_id = ?
             GROUP BY ds.id, ds.name, ds.color
             ORDER BY ds.sort_order",
            [$tid]
        );

        // ---- Action items: overdue tasks ----
        $overdueTasks = Database::fetchAll(
            "SELECT t.*, u.name as assigned_name
             FROM tasks t
             LEFT JOIN users u ON t.assigned_to = u.id
             WHERE t.tenant_id = ? AND t.is_deleted = 0 AND t.due_date < NOW() AND t.status != 'done'
             ORDER BY t.due_date ASC
             LIMIT 5",
            [$tid]
        );

        // ---- Action items: deals closing soon (7 days) ----
        $dealsClosingSoon = Database::fetchAll(
            "SELECT d.id, d.title, d.value, d.expected_close_date, c.first_name, c.last_name
             FROM deals d
             LEFT JOIN contacts c ON d.contact_id = c.id
             WHERE d.tenant_id = ? AND d.status = 'open'
               AND d.expected_close_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
             ORDER BY d.expected_close_date ASC
             LIMIT 5",
            [$tid]
        );

        // ---- Action items: inactive contacts (30+ days) ----
        $inactiveContacts = Database::fetchAll(
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
            [$tid]
        );

        // ---- Today's calendar events ----
        $todayEvents = [];
        try {
            $todayEvents = Database::fetchAll(
                "SELECT ce.*, c.first_name as contact_first_name, c.last_name as contact_last_name
                 FROM calendar_events ce
                 LEFT JOIN contacts c ON ce.contact_id = c.id
                 WHERE (ce.user_id = ? OR ce.created_by = ?) AND DATE(ce.start_at) = CURDATE()
                 ORDER BY ce.start_at ASC
                 LIMIT 5",
                [$uid, $uid]
            );
        } catch (\Exception $e) {}

        // ---- Recent activities ----
        $recentActivities = Database::fetchAll(
            "SELECT a.*, u.name as user_name
             FROM activities a
             LEFT JOIN users u ON a.user_id = u.id
             WHERE a.tenant_id = ?
             ORDER BY a.created_at DESC
             LIMIT 8",
            [$tid]
        );

        // ---- Deal status distribution (for donut chart) ----
        $dealStatusDist = Database::fetchAll(
            "SELECT status, COUNT(*) as count, COALESCE(SUM(value),0) as total FROM deals WHERE tenant_id = ? GROUP BY status",
            [$tid]
        );

        // ---- Top 5 staff by revenue ----
        $topStaff = Database::fetchAll(
            "SELECT u.id, u.name, u.avatar, COUNT(d.id) as deal_count, COALESCE(SUM(d.value),0) as revenue
             FROM deals d JOIN users u ON d.user_id = u.id
             WHERE d.tenant_id = ? AND d.status = 'won'
             GROUP BY u.id, u.name, u.avatar
             ORDER BY revenue DESC LIMIT 5",
            [$tid]
        );

        // ---- Contact source distribution ----
        $sourceDist = Database::fetchAll(
            "SELECT COALESCE(cs.name, 'Không rõ') as source_name, COUNT(*) as count
             FROM contacts c LEFT JOIN contact_sources cs ON c.source_id = cs.id
             WHERE c.tenant_id = ? AND c.is_deleted = 0
             GROUP BY cs.name ORDER BY count DESC LIMIT 8",
            [$tid]
        );

        // ---- Last month revenue for comparison ----
        $lastMonthRevenueData = array_fill(0, 12, 0);
        $lastYear = date('Y', strtotime('-1 year'));
        $lmRows = Database::fetchAll(
            "SELECT MONTH(actual_close_date) as month, SUM(value) as revenue
             FROM deals WHERE status = 'won' AND YEAR(actual_close_date) = ? AND tenant_id = ?
             GROUP BY MONTH(actual_close_date)",
            [$lastYear, $tid]
        );
        foreach ($lmRows as $r) $lastMonthRevenueData[$r['month'] - 1] = (float)$r['revenue'];

        // ---- Task completion rate this week/month ----
        $taskTotal = (int)(Database::fetch("SELECT COUNT(*) as c FROM tasks WHERE tenant_id = ? AND is_deleted = 0 AND parent_id IS NULL", [$tid])['c'] ?? 0);
        $taskDone = (int)(Database::fetch("SELECT COUNT(*) as c FROM tasks WHERE tenant_id = ? AND is_deleted = 0 AND parent_id IS NULL AND status = 'done'", [$tid])['c'] ?? 0);
        $taskWeekDone = (int)(Database::fetch("SELECT COUNT(*) as c FROM tasks WHERE tenant_id = ? AND is_deleted = 0 AND parent_id IS NULL AND status = 'done' AND completed_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)", [$tid])['c'] ?? 0);
        $taskWeekTotal = (int)(Database::fetch("SELECT COUNT(*) as c FROM tasks WHERE tenant_id = ? AND is_deleted = 0 AND parent_id IS NULL AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)", [$tid])['c'] ?? 0);

        return $this->view('dashboard.index', [
            'insights' => $insights,
            'stats' => $stats,
            'healthDist' => $healthDist,
            'criticalContacts' => $criticalContacts,
            'revenueData' => $revenueData,
            'lastMonthRevenueData' => $lastMonthRevenueData,
            'pipelineSummary' => $pipelineSummary,
            'overdueTasks' => $overdueTasks,
            'dealsClosingSoon' => $dealsClosingSoon,
            'inactiveContacts' => $inactiveContacts,
            'todayEvents' => $todayEvents,
            'recentActivities' => $recentActivities,
            'dealStatusDist' => $dealStatusDist,
            'topStaff' => $topStaff,
            'sourceDist' => $sourceDist,
            'taskStats' => ['total' => $taskTotal, 'done' => $taskDone, 'week_done' => $taskWeekDone, 'week_total' => $taskWeekTotal],
        ]);
    }

    /**
     * Dismiss a smart insight via AJAX
     */
    public function dismissInsight(int $id)
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }

        $tid = $this->tenantId();
        $uid = $this->userId();

        try {
            Database::update(
                'smart_insights',
                ['is_dismissed' => 1],
                'id = ? AND tenant_id = ? AND user_id = ?',
                [$id, $tid, $uid]
            );
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed'], 500);
        }
    }
}
