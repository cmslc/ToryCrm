<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class DashboardController extends Controller
{
    public function index()
    {
        // Summary stats
        $totalContacts = Database::fetch("SELECT COUNT(*) as count FROM contacts")['count'];
        $totalCompanies = Database::fetch("SELECT COUNT(*) as count FROM companies")['count'];
        $totalDeals = Database::fetch("SELECT COUNT(*) as count FROM deals")['count'];
        $totalTasks = Database::fetch("SELECT COUNT(*) as count FROM tasks")['count'];
        $totalRevenue = Database::fetch("SELECT COALESCE(SUM(value), 0) as total FROM deals WHERE status = 'won'")['total'];

        $stats = [
            'total_contacts' => $totalContacts,
            'total_companies' => $totalCompanies,
            'total_deals' => $totalDeals,
            'total_tasks' => $totalTasks,
            'total_revenue' => $totalRevenue,
        ];

        // Recent contacts
        $recentContacts = Database::fetchAll(
            "SELECT c.*, comp.name as company_name
             FROM contacts c
             LEFT JOIN companies comp ON c.company_id = comp.id
             ORDER BY c.created_at DESC
             LIMIT 5"
        );

        // Recent activities
        $recentActivities = Database::fetchAll(
            "SELECT a.*, u.name as user_name
             FROM activities a
             LEFT JOIN users u ON a.user_id = u.id
             ORDER BY a.created_at DESC
             LIMIT 5"
        );

        // Deal pipeline summary
        $pipelineSummary = Database::fetchAll(
            "SELECT ds.name, ds.color, COUNT(d.id) as count, COALESCE(SUM(d.value), 0) as total_value
             FROM deal_stages ds
             LEFT JOIN deals d ON d.stage_id = ds.id AND d.status = 'open'
             GROUP BY ds.id, ds.name, ds.color
             ORDER BY ds.sort_order"
        );

        // Overdue tasks
        $overdueTasks = Database::fetchAll(
            "SELECT t.*, u.name as assigned_name
             FROM tasks t
             LEFT JOIN users u ON t.assigned_to = u.id
             WHERE t.due_date < NOW() AND t.status != 'done'
             ORDER BY t.due_date ASC
             LIMIT 5"
        );

        // Task stats for chart
        $taskCounts = Database::fetchAll("SELECT status, COUNT(*) as count FROM tasks GROUP BY status");
        $taskStats = [0, 0, 0, 0]; // todo, in_progress, review, done
        $statusMap = ['todo' => 0, 'in_progress' => 1, 'review' => 2, 'done' => 3];
        foreach ($taskCounts as $tc) {
            if (isset($statusMap[$tc['status']])) {
                $taskStats[$statusMap[$tc['status']]] = (int)$tc['count'];
            }
        }

        // Revenue by month
        $year = date('Y');
        $revenueRows = Database::fetchAll(
            "SELECT MONTH(actual_close_date) as month, SUM(value) as revenue
             FROM deals WHERE status = 'won' AND YEAR(actual_close_date) = ?
             GROUP BY MONTH(actual_close_date)",
            [$year]
        );
        $revenueData = array_fill(0, 12, 0);
        foreach ($revenueRows as $r) {
            $revenueData[$r['month'] - 1] = (float)$r['revenue'];
        }

        // Orders stats (wrapped in try-catch in case migration not yet applied)
        try {
            $totalOrders = Database::fetch("SELECT COUNT(*) as count FROM orders WHERE type = 'order'")['count'] ?? 0;
            $orderRevenue = Database::fetch("SELECT COALESCE(SUM(total), 0) as total FROM orders WHERE type = 'order' AND status = 'completed'")['total'] ?? 0;
        } catch (\Exception $e) {
            $totalOrders = 0;
            $orderRevenue = 0;
        }

        // Ticket stats
        try {
            $openTickets = Database::fetch("SELECT COUNT(*) as count FROM tickets WHERE status NOT IN ('resolved', 'closed')")['count'] ?? 0;
        } catch (\Exception $e) {
            $openTickets = 0;
        }
        $stats['open_tickets'] = $openTickets;

        $stats['total_orders'] = $totalOrders;
        $stats['order_revenue'] = $orderRevenue;

        // Today's calendar events
        try {
            $todayEvents = Database::fetchAll(
                "SELECT ce.*, c.first_name as contact_first_name, c.last_name as contact_last_name
                 FROM calendar_events ce
                 LEFT JOIN contacts c ON ce.contact_id = c.id
                 WHERE (ce.user_id = ? OR ce.created_by = ?) AND DATE(ce.start_at) = CURDATE()
                 ORDER BY ce.start_at ASC
                 LIMIT 5",
                [$this->userId(), $this->userId()]
            );
        } catch (\Exception $e) {
            $todayEvents = [];
        }

        return $this->view('dashboard.index', [
            'stats' => $stats,
            'recentContacts' => $recentContacts,
            'recentActivities' => $recentActivities,
            'pipelineSummary' => $pipelineSummary,
            'overdueTasks' => $overdueTasks,
            'taskStats' => $taskStats,
            'revenueData' => $revenueData,
            'todayEvents' => $todayEvents,
        ]);
    }
}
