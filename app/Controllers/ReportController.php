<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class ReportController extends Controller
{
    public function index()
    {
        $this->authorize('reports', 'view');
        return $this->view('reports.index');
    }

    public function customers()
    {
        $tid = Database::tenantId();
        $dateFrom = $this->input('date_from');
        $dateTo = $this->input('date_to');
        $period = $this->input('period');

        // Auto date range from period
        if ($period && !$dateFrom) {
            switch ($period) {
                case 'today': $dateFrom = $dateTo = date('Y-m-d'); break;
                case 'yesterday': $dateFrom = $dateTo = date('Y-m-d', strtotime('-1 day')); break;
                case 'this_week': $dateFrom = date('Y-m-d', strtotime('monday this week')); $dateTo = date('Y-m-d'); break;
                case 'this_month': $dateFrom = date('Y-m-01'); $dateTo = date('Y-m-d'); break;
                case 'this_quarter':
                    $q = ceil(date('n') / 3);
                    $dateFrom = date('Y-' . str_pad(($q - 1) * 3 + 1, 2, '0', STR_PAD_LEFT) . '-01');
                    $dateTo = date('Y-m-d');
                    break;
                case 'this_year': $dateFrom = date('Y-01-01'); $dateTo = date('Y-m-d'); break;
            }
        }

        $dateWhere = '';
        $dateParams = [];
        if ($dateFrom) { $dateWhere .= " AND c.created_at >= ?"; $dateParams[] = $dateFrom . ' 00:00:00'; }
        if ($dateTo) { $dateWhere .= " AND c.created_at <= ?"; $dateParams[] = $dateTo . ' 23:59:59'; }

        $baseWhere = "c.is_deleted = 0 AND c.tenant_id = ?";
        $baseParams = [$tid];

        // 1. Theo nguồn (account_source)
        $bySource = Database::fetchAll(
            "SELECT cs.name, cs.color, COUNT(c.id) as count
             FROM contact_sources cs
             LEFT JOIN contacts c ON c.source_id = cs.id AND {$baseWhere} {$dateWhere}
             GROUP BY cs.id, cs.name, cs.color ORDER BY count DESC",
            array_merge($baseParams, $dateParams)
        );

        // 2. Theo trạng thái
        $byStatus = Database::fetchAll(
            "SELECT c.status, COUNT(*) as count FROM contacts c
             WHERE {$baseWhere} {$dateWhere}
             GROUP BY c.status",
            array_merge($baseParams, $dateParams)
        );

        // 3. Tỷ lệ KH theo User (người phụ trách)
        $byOwner = Database::fetchAll(
            "SELECT u.name, COUNT(c.id) as count
             FROM users u
             LEFT JOIN contacts c ON c.owner_id = u.id AND {$baseWhere} {$dateWhere}
             WHERE u.is_active = 1 AND u.tenant_id = ?
             GROUP BY u.id, u.name ORDER BY count DESC",
            array_merge($baseParams, $dateParams, [$tid])
        );

        // 4. KH theo tháng
        $byMonth = Database::fetchAll(
            "SELECT MONTH(c.created_at) as month, COUNT(*) as count
             FROM contacts c WHERE {$baseWhere} AND YEAR(c.created_at) = YEAR(NOW()) {$dateWhere}
             GROUP BY MONTH(c.created_at) ORDER BY month",
            array_merge($baseParams, $dateParams)
        );
        $monthData = array_fill(0, 12, 0);
        foreach ($byMonth as $m) { $monthData[$m['month'] - 1] = (int)$m['count']; }

        // 5. Theo nhóm KH (account_type) - NEW
        $byType = Database::fetchAll(
            "SELECT ct.name, ct.color, COUNT(ctp.contact_id) as count
             FROM contact_types ct
             LEFT JOIN contact_type_pivot ctp ON ctp.type_id = ct.id
             LEFT JOIN contacts c ON c.id = ctp.contact_id AND {$baseWhere} {$dateWhere}
             GROUP BY ct.id, ct.name, ct.color ORDER BY count DESC",
            array_merge($baseParams, $dateParams)
        );

        // 6. KH theo Tỉnh/Thành phố - NEW
        $byProvince = Database::fetchAll(
            "SELECT province, COUNT(*) as count FROM (
                SELECT COALESCE(c.city, c.province, 'Chưa xác định') as province
                FROM contacts c WHERE {$baseWhere} {$dateWhere}
             ) sub GROUP BY province ORDER BY count DESC LIMIT 15",
            array_merge($baseParams, $dateParams)
        );

        // 7. Theo Ngành nghề - NEW
        $byIndustry = Database::fetchAll(
            "SELECT COALESCE(i.name, 'Chưa phân loại') as industry, COUNT(c.id) as count
             FROM contacts c
             LEFT JOIN industries i ON c.industry_id = i.id
             WHERE {$baseWhere} {$dateWhere}
             GROUP BY industry ORDER BY count DESC",
            array_merge($baseParams, $dateParams)
        );

        // 8. Trạng thái Mối quan hệ - NEW
        $byRelation = Database::fetchAll(
            "SELECT COALESCE(cr.name, 'Chưa phân loại') as relation, COUNT(c.id) as count
             FROM contacts c
             LEFT JOIN contact_relations cr ON c.relation_id = cr.id
             WHERE {$baseWhere} {$dateWhere}
             GROUP BY relation ORDER BY count DESC",
            array_merge($baseParams, $dateParams)
        );

        // 9. Theo công ty (top 10)
        $byCompany = Database::fetchAll(
            "SELECT comp.name, COUNT(c.id) as count
             FROM companies comp
             JOIN contacts c ON c.company_id = comp.id AND {$baseWhere} {$dateWhere}
             GROUP BY comp.id, comp.name ORDER BY count DESC LIMIT 10",
            array_merge($baseParams, $dateParams)
        );

        return $this->view('reports.customers', [
            'bySource' => $bySource,
            'byStatus' => $byStatus,
            'byOwner' => $byOwner,
            'monthData' => $monthData,
            'byType' => $byType,
            'byProvince' => $byProvince,
            'byIndustry' => $byIndustry,
            'byRelation' => $byRelation,
            'byCompany' => $byCompany,
            'filters' => [
                'period' => $period,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    public function revenue()
    {
        $tid = Database::tenantId();
        $year = (int)($this->input('year') ?: date('Y'));

        $dealRevenue = Database::fetchAll(
            "SELECT MONTH(actual_close_date) as month, SUM(value) as revenue, COUNT(*) as count
             FROM deals WHERE status = 'won' AND YEAR(actual_close_date) = ? AND tenant_id = ?
             GROUP BY MONTH(actual_close_date) ORDER BY month",
            [$year, $tid]
        );
        $dealData = array_fill(0, 12, 0);
        foreach ($dealRevenue as $r) { $dealData[$r['month'] - 1] = (float)$r['revenue']; }

        try {
            $orderRevenue = Database::fetchAll(
                "SELECT MONTH(created_at) as month, SUM(total) as revenue, COUNT(*) as count
                 FROM orders WHERE type = 'order' AND status = 'completed' AND YEAR(created_at) = ? AND tenant_id = ?
                 GROUP BY MONTH(created_at) ORDER BY month",
                [$year, $tid]
            );
        } catch (\Exception $e) { $orderRevenue = []; }
        $orderData = array_fill(0, 12, 0);
        foreach ($orderRevenue as $r) { $orderData[$r['month'] - 1] = (float)$r['revenue']; }

        $topDeals = Database::fetchAll(
            "SELECT d.title, d.value, c.first_name, c.last_name, comp.name as company_name
             FROM deals d
             LEFT JOIN contacts c ON d.contact_id = c.id
             LEFT JOIN companies comp ON d.company_id = comp.id
             WHERE d.status = 'won' AND YEAR(d.actual_close_date) = ? AND d.tenant_id = ?
             ORDER BY d.value DESC LIMIT 10",
            [$year, $tid]
        );

        $byOwner = Database::fetchAll(
            "SELECT u.name, SUM(d.value) as revenue, COUNT(d.id) as count
             FROM deals d JOIN users u ON d.owner_id = u.id
             WHERE d.status = 'won' AND YEAR(d.actual_close_date) = ? AND d.tenant_id = ?
             GROUP BY u.id, u.name ORDER BY revenue DESC",
            [$year, $tid]
        );

        return $this->view('reports.revenue', [
            'year' => $year,
            'dealData' => $dealData,
            'orderData' => $orderData,
            'topDeals' => $topDeals,
            'byOwner' => $byOwner,
        ]);
    }

    public function deals()
    {
        $this->authorize('reports', 'view');
        $tid = Database::tenantId();
        $year = (int)($this->input('year') ?: date('Y'));

        // Pipeline: deals by stage
        $byStage = Database::fetchAll(
            "SELECT ds.name as stage, ds.color, COUNT(d.id) as count, COALESCE(SUM(d.value),0) as total_value
             FROM deal_stages ds
             LEFT JOIN deals d ON d.stage_id = ds.id AND d.status = 'open' AND d.tenant_id = ?
             GROUP BY ds.id, ds.name, ds.color
             ORDER BY ds.sort_order",
            [$tid]
        );

        // Win/Loss by month
        $wonByMonth = Database::fetchAll(
            "SELECT MONTH(actual_close_date) as month, COUNT(*) as count, SUM(value) as value
             FROM deals WHERE status = 'won' AND YEAR(actual_close_date) = ? AND tenant_id = ?              GROUP BY MONTH(actual_close_date)",
            [$year, $tid]
        );
        $lostByMonth = Database::fetchAll(
            "SELECT MONTH(actual_close_date) as month, COUNT(*) as count
             FROM deals WHERE status = 'lost' AND YEAR(actual_close_date) = ? AND tenant_id = ?              GROUP BY MONTH(actual_close_date)",
            [$year, $tid]
        );
        $wonData = array_fill(0, 12, 0);
        $lostData = array_fill(0, 12, 0);
        foreach ($wonByMonth as $r) { $wonData[$r['month'] - 1] = (int)$r['count']; }
        foreach ($lostByMonth as $r) { $lostData[$r['month'] - 1] = (int)$r['count']; }

        // Conversion rate
        $totalDeals = (int)(Database::fetch(
            "SELECT COUNT(*) as cnt FROM deals WHERE YEAR(created_at) = ? AND tenant_id = ?",
            [$year, $tid]
        )['cnt'] ?? 0);
        $totalWon = array_sum($wonData);
        $conversionRate = $totalDeals > 0 ? round($totalWon / $totalDeals * 100, 1) : 0;

        // Average close time (days)
        $avgClose = Database::fetch(
            "SELECT AVG(DATEDIFF(actual_close_date, created_at)) as avg_days
             FROM deals WHERE status = 'won' AND YEAR(actual_close_date) = ? AND tenant_id = ?",
            [$year, $tid]
        );
        $avgCloseDays = round((float)($avgClose['avg_days'] ?? 0));

        // Loss reasons
        $lossReasons = Database::fetchAll(
            "SELECT COALESCE(loss_reason, 'Không rõ') as reason, COUNT(*) as count
             FROM deals WHERE status = 'lost' AND YEAR(actual_close_date) = ? AND tenant_id = ?              GROUP BY loss_reason ORDER BY count DESC LIMIT 10",
            [$year, $tid]
        );

        // By owner
        $byOwner = Database::fetchAll(
            "SELECT u.name, COUNT(d.id) as total,
                    SUM(d.status='won') as won, SUM(d.status='lost') as lost,
                    COALESCE(SUM(CASE WHEN d.status='won' THEN d.value END),0) as won_value
             FROM deals d JOIN users u ON d.owner_id = u.id
             WHERE YEAR(d.created_at) = ? AND d.tenant_id = ?              GROUP BY u.id, u.name ORDER BY won_value DESC",
            [$year, $tid]
        );

        return $this->view('reports.deals', [
            'year' => $year,
            'byStage' => $byStage,
            'wonData' => $wonData,
            'lostData' => $lostData,
            'totalDeals' => $totalDeals,
            'totalWon' => $totalWon,
            'conversionRate' => $conversionRate,
            'avgCloseDays' => $avgCloseDays,
            'lossReasons' => $lossReasons,
            'byOwner' => $byOwner,
        ]);
    }

    public function orders()
    {
        $this->authorize('reports', 'view');
        $tid = Database::tenantId();
        $year = (int)($this->input('year') ?: date('Y'));

        // Orders by status
        $byStatus = Database::fetchAll(
            "SELECT status, COUNT(*) as count, COALESCE(SUM(total),0) as total_value
             FROM orders WHERE YEAR(created_at) = ? AND tenant_id = ?
             GROUP BY status ORDER BY count DESC",
            [$year, $tid]
        );

        // Orders by month
        $orderByMonth = Database::fetchAll(
            "SELECT MONTH(created_at) as month, COUNT(*) as count, COALESCE(SUM(total),0) as revenue
             FROM orders WHERE YEAR(created_at) = ? AND tenant_id = ? AND type = 'order'
             GROUP BY MONTH(created_at)",
            [$year, $tid]
        );
        $monthCount = array_fill(0, 12, 0);
        $monthRevenue = array_fill(0, 12, 0);
        foreach ($orderByMonth as $r) {
            $monthCount[$r['month'] - 1] = (int)$r['count'];
            $monthRevenue[$r['month'] - 1] = (float)$r['revenue'];
        }

        // Top products
        $topProducts = Database::fetchAll(
            "SELECT p.name, SUM(oi.quantity) as qty, SUM(oi.quantity * oi.unit_price) as revenue
             FROM order_items oi
             JOIN orders o ON oi.order_id = o.id
             JOIN products p ON oi.product_id = p.id
             WHERE YEAR(o.created_at) = ? AND o.tenant_id = ? AND o.type = 'order'
             GROUP BY p.id, p.name ORDER BY revenue DESC LIMIT 10",
            [$year, $tid]
        );

        // By sales person
        $bySales = Database::fetchAll(
            "SELECT u.name, COUNT(o.id) as count, COALESCE(SUM(o.total),0) as revenue
             FROM orders o JOIN users u ON o.created_by = u.id
             WHERE YEAR(o.created_at) = ? AND o.tenant_id = ? AND o.type = 'order'
             GROUP BY u.id, u.name ORDER BY revenue DESC",
            [$year, $tid]
        );

        // KPI
        $totalOrders = array_sum($monthCount);
        $totalRevenue = array_sum($monthRevenue);
        $avgOrderValue = $totalOrders > 0 ? round($totalRevenue / $totalOrders) : 0;

        return $this->view('reports.orders', [
            'year' => $year,
            'byStatus' => $byStatus,
            'monthCount' => $monthCount,
            'monthRevenue' => $monthRevenue,
            'topProducts' => $topProducts,
            'bySales' => $bySales,
            'totalOrders' => $totalOrders,
            'totalRevenue' => $totalRevenue,
            'avgOrderValue' => $avgOrderValue,
        ]);
    }

    public function tasks()
    {
        $this->authorize('reports', 'view');
        $tid = Database::tenantId();

        // By status
        $byStatus = Database::fetchAll(
            "SELECT status, COUNT(*) as count FROM tasks
             WHERE tenant_id = ? AND is_deleted = 0 AND parent_id IS NULL
             GROUP BY status",
            [$tid]
        );

        // Completion rate
        $total = 0; $done = 0;
        foreach ($byStatus as $r) {
            $total += (int)$r['count'];
            if ($r['status'] === 'done') $done = (int)$r['count'];
        }
        $completionRate = $total > 0 ? round($done / $total * 100, 1) : 0;

        // Overdue tasks
        $overdue = (int)(Database::fetch(
            "SELECT COUNT(*) as cnt FROM tasks
             WHERE tenant_id = ? AND is_deleted = 0 AND parent_id IS NULL
             AND status NOT IN ('done') AND due_date < NOW()",
            [$tid]
        )['cnt'] ?? 0);

        // By priority
        $byPriority = Database::fetchAll(
            "SELECT priority, COUNT(*) as count FROM tasks
             WHERE tenant_id = ? AND is_deleted = 0 AND parent_id IS NULL
             GROUP BY priority",
            [$tid]
        );

        // By assignee
        $byAssignee = Database::fetchAll(
            "SELECT u.name,
                    COUNT(t.id) as total,
                    SUM(t.status='done') as done,
                    SUM(t.status != 'done' AND t.due_date < NOW()) as overdue
             FROM tasks t JOIN users u ON t.assigned_to = u.id
             WHERE t.tenant_id = ? AND t.is_deleted = 0 AND t.parent_id IS NULL
             GROUP BY u.id, u.name ORDER BY total DESC",
            [$tid]
        );

        // Completed by month (this year)
        $year = (int)date('Y');
        $completedByMonth = Database::fetchAll(
            "SELECT MONTH(completed_at) as month, COUNT(*) as count FROM tasks
             WHERE tenant_id = ? AND is_deleted = 0 AND status = 'done'
             AND YEAR(completed_at) = ?
             GROUP BY MONTH(completed_at)",
            [$tid, $year]
        );
        $monthDone = array_fill(0, 12, 0);
        foreach ($completedByMonth as $r) { $monthDone[$r['month'] - 1] = (int)$r['count']; }

        // Average completion time
        $avgTime = Database::fetch(
            "SELECT AVG(DATEDIFF(completed_at, created_at)) as avg_days FROM tasks
             WHERE tenant_id = ? AND is_deleted = 0 AND status = 'done' AND completed_at IS NOT NULL
             AND YEAR(completed_at) = ?",
            [$tid, $year]
        );
        $avgCompletionDays = round((float)($avgTime['avg_days'] ?? 0));

        return $this->view('reports.tasks', [
            'byStatus' => $byStatus,
            'completionRate' => $completionRate,
            'overdue' => $overdue,
            'total' => $total,
            'done' => $done,
            'byPriority' => $byPriority,
            'byAssignee' => $byAssignee,
            'monthDone' => $monthDone,
            'avgCompletionDays' => $avgCompletionDays,
        ]);
    }

    public function staff()
    {
        $this->authorize('reports', 'view');
        $tid = Database::tenantId();
        $year = (int)($this->input('year') ?: date('Y'));

        // Staff performance: deals + orders + tasks
        $staffPerf = Database::fetchAll(
            "SELECT u.id, u.name, d.name as dept_name,
                    (SELECT COUNT(*) FROM deals dl WHERE dl.owner_id = u.id AND dl.status = 'won' AND YEAR(dl.actual_close_date) = ? AND dl.tenant_id = ?) as deals_won,
                    (SELECT COALESCE(SUM(dl.value),0) FROM deals dl WHERE dl.owner_id = u.id AND dl.status = 'won' AND YEAR(dl.actual_close_date) = ? AND dl.tenant_id = ?) as deal_revenue,
                    (SELECT COUNT(*) FROM orders o WHERE o.created_by = u.id AND YEAR(o.created_at) = ? AND o.tenant_id = ? AND o.type = 'order') as orders_count,
                    (SELECT COALESCE(SUM(o.total),0) FROM orders o WHERE o.created_by = u.id AND YEAR(o.created_at) = ? AND o.tenant_id = ? AND o.type = 'order') as order_revenue,
                    (SELECT COUNT(*) FROM tasks t WHERE t.assigned_to = u.id AND t.status = 'done' AND YEAR(t.completed_at) = ? AND t.tenant_id = ?) as tasks_done,
                    (SELECT COUNT(*) FROM tasks t WHERE t.assigned_to = u.id AND t.is_deleted = 0 AND t.tenant_id = ?) as tasks_total,
                    (SELECT COUNT(*) FROM activities a WHERE a.user_id = u.id AND YEAR(a.created_at) = ? AND a.tenant_id = ?) as activities
             FROM users u
             LEFT JOIN departments d ON u.department_id = d.id
             WHERE u.is_active = 1 AND u.tenant_id = ?
             ORDER BY deal_revenue DESC",
            [$year, $tid, $year, $tid, $year, $tid, $year, $tid, $year, $tid, $tid, $year, $tid, $tid]
        );

        // Commission summary
        $commissions = Database::fetchAll(
            "SELECT u.name,
                    COALESCE(SUM(CASE WHEN c.status = 'paid' THEN c.amount END),0) as paid,
                    COALESCE(SUM(CASE WHEN c.status = 'approved' THEN c.amount END),0) as approved,
                    COALESCE(SUM(CASE WHEN c.status = 'pending' THEN c.amount END),0) as pending
             FROM commissions c JOIN users u ON c.user_id = u.id
             WHERE YEAR(c.created_at) = ? AND c.tenant_id = ?
             GROUP BY u.id, u.name ORDER BY paid DESC",
            [$year, $tid]
        );

        return $this->view('reports.staff', [
            'year' => $year,
            'staffPerf' => $staffPerf,
            'commissions' => $commissions,
        ]);
    }
}
