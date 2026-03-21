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
        // Khách hàng theo nguồn
        $bySource = Database::fetchAll(
            "SELECT cs.name, cs.color, COUNT(c.id) as count
             FROM contact_sources cs
             LEFT JOIN contacts c ON c.source_id = cs.id
             GROUP BY cs.id, cs.name, cs.color
             ORDER BY count DESC"
        );

        // Khách hàng theo trạng thái
        $byStatus = Database::fetchAll(
            "SELECT status, COUNT(*) as count FROM contacts GROUP BY status"
        );

        // Khách hàng theo người phụ trách
        $byOwner = Database::fetchAll(
            "SELECT u.name, COUNT(c.id) as count
             FROM users u
             LEFT JOIN contacts c ON c.owner_id = u.id
             WHERE u.is_active = 1
             GROUP BY u.id, u.name
             ORDER BY count DESC
             LIMIT 10"
        );

        // Khách hàng theo tháng (năm nay)
        $byMonth = Database::fetchAll(
            "SELECT MONTH(created_at) as month, COUNT(*) as count
             FROM contacts WHERE YEAR(created_at) = YEAR(NOW())
             GROUP BY MONTH(created_at)
             ORDER BY month"
        );

        $monthData = array_fill(0, 12, 0);
        foreach ($byMonth as $m) {
            $monthData[$m['month'] - 1] = (int)$m['count'];
        }

        // Khách hàng theo công ty (top 10)
        $byCompany = Database::fetchAll(
            "SELECT comp.name, COUNT(c.id) as count
             FROM companies comp
             LEFT JOIN contacts c ON c.company_id = comp.id
             GROUP BY comp.id, comp.name
             HAVING count > 0
             ORDER BY count DESC
             LIMIT 10"
        );

        return $this->view('reports.customers', [
            'bySource' => $bySource,
            'byStatus' => $byStatus,
            'byOwner' => $byOwner,
            'monthData' => $monthData,
            'byCompany' => $byCompany,
        ]);
    }

    public function revenue()
    {
        $year = (int)($this->input('year') ?: date('Y'));

        // Doanh thu deals won
        $dealRevenue = Database::fetchAll(
            "SELECT MONTH(actual_close_date) as month, SUM(value) as revenue, COUNT(*) as count
             FROM deals WHERE status = 'won' AND YEAR(actual_close_date) = ?
             GROUP BY MONTH(actual_close_date) ORDER BY month",
            [$year]
        );

        $dealData = array_fill(0, 12, 0);
        foreach ($dealRevenue as $r) {
            $dealData[$r['month'] - 1] = (float)$r['revenue'];
        }

        // Doanh thu đơn hàng
        try {
            $orderRevenue = Database::fetchAll(
                "SELECT MONTH(created_at) as month, SUM(total) as revenue, COUNT(*) as count
                 FROM orders WHERE type = 'order' AND status = 'completed' AND YEAR(created_at) = ?
                 GROUP BY MONTH(created_at) ORDER BY month",
                [$year]
            );
        } catch (\Exception $e) {
            $orderRevenue = [];
        }

        $orderData = array_fill(0, 12, 0);
        foreach ($orderRevenue as $r) {
            $orderData[$r['month'] - 1] = (float)$r['revenue'];
        }

        // Top deals
        $topDeals = Database::fetchAll(
            "SELECT d.title, d.value, c.first_name, c.last_name, comp.name as company_name
             FROM deals d
             LEFT JOIN contacts c ON d.contact_id = c.id
             LEFT JOIN companies comp ON d.company_id = comp.id
             WHERE d.status = 'won' AND YEAR(d.actual_close_date) = ?
             ORDER BY d.value DESC LIMIT 10",
            [$year]
        );

        // Revenue by owner
        $byOwner = Database::fetchAll(
            "SELECT u.name, SUM(d.value) as revenue, COUNT(d.id) as count
             FROM deals d
             JOIN users u ON d.owner_id = u.id
             WHERE d.status = 'won' AND YEAR(d.actual_close_date) = ?
             GROUP BY u.id, u.name
             ORDER BY revenue DESC",
            [$year]
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
