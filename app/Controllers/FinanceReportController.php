<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class FinanceReportController extends Controller
{
    public function index()
    {
        if (!$this->isAdminOrManager()) {
            $this->setFlash('error', 'Bạn không có quyền xem báo cáo tài chính.');
            return $this->redirect('dashboard');
        }
        $tid = $this->tenantId();
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');

        // Revenue this month: won deals + completed orders
        $dealRevenue = (float) (Database::fetch(
            "SELECT COALESCE(SUM(value), 0) as total FROM deals
             WHERE status = 'won' AND tenant_id = ? AND actual_close_date BETWEEN ? AND ?",
            [$tid, $monthStart, $monthEnd]
        )['total'] ?? 0);

        $orderRevenue = 0;
        try {
            $orderRevenue = (float) (Database::fetch(
                "SELECT COALESCE(SUM(total), 0) as total FROM orders
                 WHERE type = 'order' AND status = 'completed' AND tenant_id = ? AND created_at BETWEEN ? AND ?",
                [$tid, $monthStart . ' 00:00:00', $monthEnd . ' 23:59:59']
            )['total'] ?? 0);
        } catch (\Throwable $e) {}

        $totalRevenue = $dealRevenue + $orderRevenue;

        // Expenses this month (fund payments confirmed)
        $totalExpense = 0;
        try {
            $totalExpense = (float) (Database::fetch(
                "SELECT COALESCE(SUM(amount), 0) as total FROM fund_transactions
                 WHERE type = 'payment' AND status = 'confirmed' AND tenant_id = ?
                 AND transaction_date BETWEEN ? AND ?",
                [$tid, $monthStart, $monthEnd]
            )['total'] ?? 0);
        } catch (\Throwable $e) {}

        $netProfit = $totalRevenue - $totalExpense;

        // Receivables (orders with outstanding balance)
        $receivables = 0;
        try {
            $receivables = (float) (Database::fetch(
                "SELECT COALESCE(SUM(total - COALESCE(paid_amount, 0)), 0) as total
                 FROM orders WHERE type = 'order' AND status IN ('approved','completed','partial_paid')
                 AND tenant_id = ? AND total > COALESCE(paid_amount, 0)",
                [$tid]
            )['total'] ?? 0);
        } catch (\Throwable $e) {}

        return $this->view('finance-reports.index', [
            'totalRevenue' => $totalRevenue,
            'totalExpense' => $totalExpense,
            'netProfit' => $netProfit,
            'receivables' => $receivables,
        ]);
    }

    public function profitLoss()
    {
        if (!$this->isAdminOrManager()) { $this->setFlash("error", "Bạn không có quyền."); return $this->redirect("dashboard"); }
        $tid = $this->tenantId();
        $periodType = $this->input('period_type', 'month'); // month, quarter, year
        $year = (int) ($this->input('year') ?: date('Y'));
        $month = (int) ($this->input('month') ?: date('n'));
        $quarter = (int) ($this->input('quarter') ?: ceil(date('n') / 3));

        // Determine date range
        switch ($periodType) {
            case 'quarter':
                $startMonth = ($quarter - 1) * 3 + 1;
                $dateFrom = sprintf('%d-%02d-01', $year, $startMonth);
                $dateTo = date('Y-m-t', strtotime(sprintf('%d-%02d-01', $year, $startMonth + 2)));
                $prevFrom = sprintf('%d-%02d-01', $year, $startMonth - 3 > 0 ? $startMonth - 3 : $startMonth - 3 + 12);
                $prevYear = $startMonth - 3 > 0 ? $year : $year - 1;
                $prevStartMonth = $startMonth - 3 > 0 ? $startMonth - 3 : $startMonth - 3 + 12;
                $prevFrom = sprintf('%d-%02d-01', $prevYear, $prevStartMonth);
                $prevTo = date('Y-m-t', strtotime(sprintf('%d-%02d-01', $prevYear, $prevStartMonth + 2)));
                break;
            case 'year':
                $dateFrom = "{$year}-01-01";
                $dateTo = "{$year}-12-31";
                $prevFrom = ($year - 1) . "-01-01";
                $prevTo = ($year - 1) . "-12-31";
                break;
            default: // month
                $dateFrom = sprintf('%d-%02d-01', $year, $month);
                $dateTo = date('Y-m-t', strtotime($dateFrom));
                $prevDate = strtotime('-1 month', strtotime($dateFrom));
                $prevFrom = date('Y-m-01', $prevDate);
                $prevTo = date('Y-m-t', $prevDate);
                break;
        }

        // Current period revenues
        $dealRevenue = (float) (Database::fetch(
            "SELECT COALESCE(SUM(value), 0) as total FROM deals
             WHERE status = 'won' AND tenant_id = ? AND actual_close_date BETWEEN ? AND ?",
            [$tid, $dateFrom, $dateTo]
        )['total'] ?? 0);

        $orderRevenue = 0;
        try {
            $orderRevenue = (float) (Database::fetch(
                "SELECT COALESCE(SUM(total), 0) as total FROM orders
                 WHERE type = 'order' AND status = 'completed' AND tenant_id = ?
                 AND created_at BETWEEN ? AND ?",
                [$tid, $dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']
            )['total'] ?? 0);
        } catch (\Throwable $e) {}

        $totalRevenue = $dealRevenue + $orderRevenue;

        // Expenses by category
        $expenses = [];
        try {
            $expenses = Database::fetchAll(
                "SELECT COALESCE(NULLIF(category, ''), 'Khác') as category, SUM(amount) as total
                 FROM fund_transactions
                 WHERE type = 'payment' AND status = 'confirmed' AND tenant_id = ?
                 AND transaction_date BETWEEN ? AND ?
                 GROUP BY COALESCE(NULLIF(category, ''), 'Khác')
                 ORDER BY total DESC",
                [$tid, $dateFrom, $dateTo]
            );
        } catch (\Throwable $e) {}

        $totalExpense = array_sum(array_column($expenses, 'total'));
        $netProfit = $totalRevenue - $totalExpense;

        // Previous period for comparison
        $prevDealRevenue = (float) (Database::fetch(
            "SELECT COALESCE(SUM(value), 0) as total FROM deals
             WHERE status = 'won' AND tenant_id = ? AND actual_close_date BETWEEN ? AND ?",
            [$tid, $prevFrom, $prevTo]
        )['total'] ?? 0);

        $prevOrderRevenue = 0;
        try {
            $prevOrderRevenue = (float) (Database::fetch(
                "SELECT COALESCE(SUM(total), 0) as total FROM orders
                 WHERE type = 'order' AND status = 'completed' AND tenant_id = ?
                 AND created_at BETWEEN ? AND ?",
                [$tid, $prevFrom . ' 00:00:00', $prevTo . ' 23:59:59']
            )['total'] ?? 0);
        } catch (\Throwable $e) {}

        $prevRevenue = $prevDealRevenue + $prevOrderRevenue;

        $prevExpense = 0;
        try {
            $prevExpense = (float) (Database::fetch(
                "SELECT COALESCE(SUM(amount), 0) as total FROM fund_transactions
                 WHERE type = 'payment' AND status = 'confirmed' AND tenant_id = ?
                 AND transaction_date BETWEEN ? AND ?",
                [$tid, $prevFrom, $prevTo]
            )['total'] ?? 0);
        } catch (\Throwable $e) {}

        $revenueChange = $prevRevenue > 0 ? (($totalRevenue - $prevRevenue) / $prevRevenue * 100) : 0;
        $expenseChange = $prevExpense > 0 ? (($totalExpense - $prevExpense) / $prevExpense * 100) : 0;

        // Monthly chart data for the year
        $monthlyRevenue = array_fill(0, 12, 0);
        $monthlyExpense = array_fill(0, 12, 0);

        $dealByMonth = Database::fetchAll(
            "SELECT MONTH(actual_close_date) as m, SUM(value) as total
             FROM deals WHERE status = 'won' AND tenant_id = ? AND YEAR(actual_close_date) = ?
             GROUP BY MONTH(actual_close_date)",
            [$tid, $year]
        );
        foreach ($dealByMonth as $r) $monthlyRevenue[$r['m'] - 1] += (float) $r['total'];

        try {
            $orderByMonth = Database::fetchAll(
                "SELECT MONTH(created_at) as m, SUM(total) as total
                 FROM orders WHERE type = 'order' AND status = 'completed' AND tenant_id = ? AND YEAR(created_at) = ?
                 GROUP BY MONTH(created_at)",
                [$tid, $year]
            );
            foreach ($orderByMonth as $r) $monthlyRevenue[$r['m'] - 1] += (float) $r['total'];
        } catch (\Throwable $e) {}

        try {
            $expByMonth = Database::fetchAll(
                "SELECT MONTH(transaction_date) as m, SUM(amount) as total
                 FROM fund_transactions WHERE type = 'payment' AND status = 'confirmed' AND tenant_id = ? AND YEAR(transaction_date) = ?
                 GROUP BY MONTH(transaction_date)",
                [$tid, $year]
            );
            foreach ($expByMonth as $r) $monthlyExpense[$r['m'] - 1] += (float) $r['total'];
        } catch (\Throwable $e) {}

        return $this->view('finance-reports.profit-loss', [
            'periodType' => $periodType,
            'year' => $year,
            'month' => $month,
            'quarter' => $quarter,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'dealRevenue' => $dealRevenue,
            'orderRevenue' => $orderRevenue,
            'totalRevenue' => $totalRevenue,
            'expenses' => $expenses,
            'totalExpense' => $totalExpense,
            'netProfit' => $netProfit,
            'revenueChange' => $revenueChange,
            'expenseChange' => $expenseChange,
            'prevRevenue' => $prevRevenue,
            'prevExpense' => $prevExpense,
            'monthlyRevenue' => $monthlyRevenue,
            'monthlyExpense' => $monthlyExpense,
        ]);
    }

    public function cashFlow()
    {
        if (!$this->isAdminOrManager()) { $this->setFlash('error', 'Bạn không có quyền.'); return $this->redirect('dashboard'); }
        $tid = $this->tenantId();
        $year = (int) ($this->input('year') ?: date('Y'));

        $monthlyReceipts = array_fill(0, 12, 0);
        $monthlyPayments = array_fill(0, 12, 0);

        try {
            $receipts = Database::fetchAll(
                "SELECT MONTH(transaction_date) as m, SUM(amount) as total
                 FROM fund_transactions WHERE type = 'receipt' AND status = 'confirmed'
                 AND tenant_id = ? AND YEAR(transaction_date) = ?
                 GROUP BY MONTH(transaction_date)",
                [$tid, $year]
            );
            foreach ($receipts as $r) $monthlyReceipts[$r['m'] - 1] = (float) $r['total'];
        } catch (\Throwable $e) {}

        try {
            $payments = Database::fetchAll(
                "SELECT MONTH(transaction_date) as m, SUM(amount) as total
                 FROM fund_transactions WHERE type = 'payment' AND status = 'confirmed'
                 AND tenant_id = ? AND YEAR(transaction_date) = ?
                 GROUP BY MONTH(transaction_date)",
                [$tid, $year]
            );
            foreach ($payments as $r) $monthlyPayments[$r['m'] - 1] = (float) $r['total'];
        } catch (\Throwable $e) {}

        // Opening balance (sum of all confirmed transactions before this year)
        $openingBalance = 0;
        try {
            $ob = Database::fetch(
                "SELECT
                    COALESCE(SUM(CASE WHEN type = 'receipt' THEN amount ELSE 0 END), 0) -
                    COALESCE(SUM(CASE WHEN type = 'payment' THEN amount ELSE 0 END), 0) as balance
                 FROM fund_transactions WHERE status = 'confirmed' AND tenant_id = ?
                 AND transaction_date < ?",
                [$tid, "{$year}-01-01"]
            );
            $openingBalance = (float) ($ob['balance'] ?? 0);
        } catch (\Throwable $e) {}

        $totalIn = array_sum($monthlyReceipts);
        $totalOut = array_sum($monthlyPayments);
        $netFlow = $totalIn - $totalOut;

        return $this->view('finance-reports.cash-flow', [
            'year' => $year,
            'monthlyReceipts' => $monthlyReceipts,
            'monthlyPayments' => $monthlyPayments,
            'openingBalance' => $openingBalance,
            'totalIn' => $totalIn,
            'totalOut' => $totalOut,
            'netFlow' => $netFlow,
        ]);
    }

    public function aging()
    {
        if (!$this->isAdminOrManager()) { $this->setFlash('error', 'Bạn không có quyền.'); return $this->redirect('dashboard'); }
        $tid = $this->tenantId();

        // Get orders with outstanding balance
        $orders = [];
        try {
            $orders = Database::fetchAll(
                "SELECT o.id, o.total, COALESCE(o.paid_amount, 0) as paid_amount,
                        o.created_at, o.contact_id,
                        CONCAT(COALESCE(c.first_name, ''), ' ', COALESCE(c.last_name, '')) as customer_name,
                        comp.name as company_name,
                        DATEDIFF(NOW(), o.created_at) as days_overdue
                 FROM orders o
                 LEFT JOIN contacts c ON o.contact_id = c.id
                 LEFT JOIN companies comp ON o.company_id = comp.id
                 WHERE o.type = 'order' AND o.tenant_id = ?
                 AND o.status IN ('approved','completed','partial_paid')
                 AND o.total > COALESCE(o.paid_amount, 0)
                 ORDER BY days_overdue DESC",
                [$tid]
            );
        } catch (\Throwable $e) {}

        // Group by customer and age bucket
        $agingData = [];
        $totals = ['current' => 0, 'd1_30' => 0, 'd31_60' => 0, 'd61_90' => 0, 'd90_plus' => 0, 'total' => 0];

        foreach ($orders as $order) {
            $outstanding = $order['total'] - $order['paid_amount'];
            $days = (int) $order['days_overdue'];
            $customerKey = $order['contact_id'] ?: 'unknown';
            $customerName = trim($order['customer_name']) ?: ($order['company_name'] ?: 'Không xác định');

            if (!isset($agingData[$customerKey])) {
                $agingData[$customerKey] = [
                    'name' => $customerName,
                    'current' => 0, 'd1_30' => 0, 'd31_60' => 0, 'd61_90' => 0, 'd90_plus' => 0, 'total' => 0,
                ];
            }

            $bucket = match (true) {
                $days <= 0 => 'current',
                $days <= 30 => 'd1_30',
                $days <= 60 => 'd31_60',
                $days <= 90 => 'd61_90',
                default => 'd90_plus',
            };

            $agingData[$customerKey][$bucket] += $outstanding;
            $agingData[$customerKey]['total'] += $outstanding;
            $totals[$bucket] += $outstanding;
            $totals['total'] += $outstanding;
        }

        // Sort by total descending
        uasort($agingData, fn($a, $b) => $b['total'] <=> $a['total']);

        return $this->view('finance-reports.aging', [
            'agingData' => $agingData,
            'totals' => $totals,
        ]);
    }
}
