<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class ReportController extends Controller
{
    public function index()
    {
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
            "SELECT COALESCE(c.city, c.province, 'Chưa xác định') as province, COUNT(*) as count
             FROM contacts c WHERE {$baseWhere} {$dateWhere}
             GROUP BY province ORDER BY count DESC LIMIT 15",
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
}
