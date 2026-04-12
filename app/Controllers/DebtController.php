<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class DebtController extends Controller
{
    public function index()
    {
        $type = $this->input('type') ?: 'receivable';
        $status = $this->input('status');
        $contactId = $this->input('contact_id');
        $companyId = $this->input('company_id');
        $dateFrom = $this->input('date_from');
        $dateTo = $this->input('date_to');
        $page = max(1, (int) $this->input('page') ?: 1);
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $where = ["d.type = ?"];
        $params = [$type];

        if ($status) {
            $where[] = "d.status = ?";
            $params[] = $status;
        }
        if ($contactId) {
            $where[] = "d.contact_id = ?";
            $params[] = $contactId;
        }
        if ($companyId) {
            $where[] = "d.company_id = ?";
            $params[] = $companyId;
        }
        if ($dateFrom) {
            $where[] = "d.due_date >= ?";
            $params[] = $dateFrom;
        }
        if ($dateTo) {
            $where[] = "d.due_date <= ?";
            $params[] = $dateTo;
        }

        $ownerScope = $this->ownerScope('d', 'created_by');
        if ($ownerScope['where']) { $where[] = $ownerScope['where']; $params = array_merge($params, $ownerScope['params']); }

        $whereClause = implode(' AND ', $where);

        $total = Database::fetch(
            "SELECT COUNT(*) as count FROM debts d WHERE {$whereClause}",
            $params
        )['count'];

        $debts = Database::fetchAll(
            "SELECT d.*,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    comp.name as company_name,
                    o.order_number
             FROM debts d
             LEFT JOIN contacts c ON d.contact_id = c.id
             LEFT JOIN companies comp ON d.company_id = comp.id
             LEFT JOIN orders o ON d.order_id = o.id
             WHERE {$whereClause}
             ORDER BY d.due_date ASC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $totalPages = ceil($total / $perPage);

        // Summary cards (same scope as list)
        $summaryWhere = "1=1";
        $summaryParams = [];
        if (!$this->isAdminOrManager()) {
            $summaryWhere = "created_by = ?";
            $summaryParams = [$this->userId()];
        }
        $summary = Database::fetch(
            "SELECT
                COALESCE(SUM(CASE WHEN type = 'receivable' THEN amount - paid_amount ELSE 0 END), 0) as total_receivable,
                COALESCE(SUM(CASE WHEN type = 'payable' THEN amount - paid_amount ELSE 0 END), 0) as total_payable,
                COALESCE(SUM(CASE WHEN status = 'overdue' THEN amount - paid_amount ELSE 0 END), 0) as total_overdue,
                COALESCE(SUM(CASE WHEN status = 'paid' AND MONTH(updated_at) = MONTH(CURDATE()) AND YEAR(updated_at) = YEAR(CURDATE()) THEN paid_amount ELSE 0 END), 0) as collected_this_month
             FROM debts WHERE {$summaryWhere}",
            $summaryParams
        );

        $contacts = Database::fetchAll("SELECT id, first_name, last_name FROM contacts ORDER BY first_name");
        $companies = Database::fetchAll("SELECT id, name FROM companies ORDER BY name");

        // Aging data
        $aging = Database::fetch(
            "SELECT
                COALESCE(SUM(CASE WHEN due_date >= CURDATE() THEN amount - paid_amount ELSE 0 END), 0) as current_due,
                COALESCE(SUM(CASE WHEN DATEDIFF(CURDATE(), due_date) BETWEEN 1 AND 30 THEN amount - paid_amount ELSE 0 END), 0) as overdue_30,
                COALESCE(SUM(CASE WHEN DATEDIFF(CURDATE(), due_date) BETWEEN 31 AND 60 THEN amount - paid_amount ELSE 0 END), 0) as overdue_60,
                COALESCE(SUM(CASE WHEN DATEDIFF(CURDATE(), due_date) BETWEEN 61 AND 90 THEN amount - paid_amount ELSE 0 END), 0) as overdue_90,
                COALESCE(SUM(CASE WHEN DATEDIFF(CURDATE(), due_date) > 90 THEN amount - paid_amount ELSE 0 END), 0) as overdue_90plus
             FROM debts WHERE status NOT IN ('paid','written_off') AND {$summaryWhere}",
            $summaryParams
        );

        // Overdue count
        $overdueCount = Database::fetch("SELECT COUNT(*) as cnt FROM debts WHERE status NOT IN ('paid','written_off') AND due_date < CURDATE() AND {$summaryWhere}", $summaryParams)['cnt'];

        return $this->view('debts.index', [
            'debts' => [
                'items' => $debts,
                'total' => $total,
                'page' => $page,
                'total_pages' => $totalPages,
            ],
            'summary' => $summary,
            'aging' => $aging,
            'overdueCount' => $overdueCount,
            'contacts' => $contacts,
            'companies' => $companies,
            'filters' => [
                'type' => $type,
                'status' => $status,
                'contact_id' => $contactId,
                'company_id' => $companyId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    public function show($id)
    {
        $debt = Database::fetch(
            "SELECT d.*,
                    c.first_name as contact_first_name, c.last_name as contact_last_name, c.email as contact_email, c.phone as contact_phone,
                    comp.name as company_name,
                    o.order_number,
                    u.name as created_by_name
             FROM debts d
             LEFT JOIN contacts c ON d.contact_id = c.id
             LEFT JOIN companies comp ON d.company_id = comp.id
             LEFT JOIN orders o ON d.order_id = o.id
             LEFT JOIN users u ON d.created_by = u.id
             WHERE d.id = ?",
            [$id]
        );

        if (!$debt) {
            $this->setFlash('error', 'Công nợ không tồn tại.');
            return $this->redirect('debts');
        }

        $payments = Database::fetchAll(
            "SELECT dp.*, u.name as recorded_by_name
             FROM debt_payments dp
             LEFT JOIN users u ON dp.created_by = u.id
             WHERE dp.debt_id = ?
             ORDER BY dp.paid_at DESC",
            [$id]
        );

        return $this->view('debts.show', [
            'debt' => $debt,
            'payments' => $payments,
        ]);
    }

    public function create()
    {
        $contacts = Database::fetchAll("SELECT id, first_name, last_name FROM contacts ORDER BY first_name");
        $companies = Database::fetchAll("SELECT id, name FROM companies ORDER BY name");
        $orders = Database::fetchAll("SELECT id, order_number, total FROM orders WHERE is_deleted = 0 ORDER BY created_at DESC LIMIT 200");

        return $this->view('debts.create', [
            'contacts' => $contacts,
            'companies' => $companies,
            'orders' => $orders,
        ]);
    }

    public function store()
    {
        if (!$this->isPost()) return $this->redirect('debts');

        $data = $this->allInput();
        $amount = (float) ($data['amount'] ?? 0);

        if ($amount <= 0) {
            $this->setFlash('error', 'Số tiền phải lớn hơn 0.');
            return $this->back();
        }

        $dueDate = !empty($data['due_date']) ? $data['due_date'] : date('Y-m-d', strtotime('+30 days'));

        $id = Database::insert('debts', [
            'type' => $data['type'] ?? 'receivable',
            'contact_id' => !empty($data['contact_id']) ? $data['contact_id'] : null,
            'company_id' => !empty($data['company_id']) ? $data['company_id'] : null,
            'order_id' => !empty($data['order_id']) ? $data['order_id'] : null,
            'amount' => $amount,
            'paid_amount' => 0,
            'due_date' => $dueDate,
            'status' => 'open',
            'note' => trim($data['note'] ?? ''),
            'created_by' => $this->userId(),
        ]);

        $this->setFlash('success', 'Công nợ đã được tạo thành công.');
        return $this->redirect('debts/' . $id);
    }

    public function addPayment($id)
    {
        if (!$this->isPost()) return $this->redirect('debts/' . $id);

        $debt = Database::fetch("SELECT * FROM debts WHERE id = ?", [$id]);
        if (!$debt) {
            $this->setFlash('error', 'Công nợ không tồn tại.');
            return $this->redirect('debts');
        }

        $data = $this->allInput();
        $paymentAmount = (float) ($data['amount'] ?? 0);
        $remaining = $debt['amount'] - $debt['paid_amount'];

        if ($paymentAmount <= 0) {
            $this->setFlash('error', 'Số tiền thanh toán phải lớn hơn 0.');
            return $this->back();
        }

        if ($paymentAmount > $remaining) {
            $this->setFlash('error', 'Số tiền thanh toán không được vượt quá số còn lại (' . format_money($remaining) . ').');
            return $this->back();
        }

        Database::beginTransaction();
        try {
            // Insert payment record
            Database::insert('debt_payments', [
                'debt_id' => $id,
                'amount' => $paymentAmount,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'paid_at' => !empty($data['paid_at']) ? $data['paid_at'] : date('Y-m-d'),
                'note' => trim($data['note'] ?? ''),
                'created_by' => $this->userId(),
            ]);

            // Update debt
            $newPaidAmount = $debt['paid_amount'] + $paymentAmount;
            $newStatus = ($newPaidAmount >= $debt['amount']) ? 'paid' : 'partial';

            Database::update('debts', [
                'paid_amount' => $newPaidAmount,
                'status' => $newStatus,
            ], 'id = ?', [$id]);

            // Optionally create fund transaction
            if (!empty($data['create_fund_transaction'])) {
                $txType = $debt['type'] === 'receivable' ? 'receipt' : 'payment';
                Database::insert('fund_transactions', [
                    'transaction_code' => strtoupper($txType === 'receipt' ? 'PT' : 'PC') . '-' . date('ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                    'type' => $txType,
                    'amount' => $paymentAmount,
                    'category' => $debt['type'] === 'receivable' ? 'Thu công nợ' : 'Trả công nợ',
                    'description' => 'Thanh toán công nợ #' . $id,
                    'contact_id' => $debt['contact_id'],
                    'company_id' => $debt['company_id'],
                    'transaction_date' => !empty($data['paid_at']) ? $data['paid_at'] : date('Y-m-d'),
                    'status' => 'confirmed',
                    'created_by' => $this->userId(),
                ]);
            }

            Database::commit();
            $this->setFlash('success', 'Đã ghi nhận thanh toán ' . format_money($paymentAmount) . '.');
        } catch (\Throwable $e) {
            Database::rollback();
            $this->setFlash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }

        return $this->redirect('debts/' . $id);
    }

    public function aging()
    {
        $type = $this->input('type') ?: 'receivable';

        $debts = Database::fetchAll(
            "SELECT d.*,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    comp.name as company_name
             FROM debts d
             LEFT JOIN contacts c ON d.contact_id = c.id
             LEFT JOIN companies comp ON d.company_id = comp.id
             WHERE d.type = ? AND d.status IN ('open', 'partial', 'overdue')
             ORDER BY d.due_date ASC",
            [$type]
        );

        // Group by contact/company with age buckets
        $agingData = [];
        $totals = ['0_30' => 0, '31_60' => 0, '61_90' => 0, '90_plus' => 0, 'total' => 0];
        $today = new \DateTime();

        foreach ($debts as $debt) {
            $remaining = $debt['amount'] - $debt['paid_amount'];
            if ($remaining <= 0) continue;

            $contactKey = ($debt['contact_first_name'] ?? '') . ' ' . ($debt['contact_last_name'] ?? '');
            $companyKey = $debt['company_name'] ?? '';
            $key = trim($contactKey) ?: $companyKey ?: 'Không xác định';

            if (!isset($agingData[$key])) {
                $agingData[$key] = ['name' => $key, '0_30' => 0, '31_60' => 0, '61_90' => 0, '90_plus' => 0, 'total' => 0];
            }

            $dueDate = new \DateTime($debt['due_date']);
            $diff = $today->diff($dueDate);
            $daysOverdue = $dueDate < $today ? $diff->days : -$diff->days;

            if ($daysOverdue <= 0) {
                $bucket = '0_30'; // Not yet due
            } elseif ($daysOverdue <= 30) {
                $bucket = '0_30';
            } elseif ($daysOverdue <= 60) {
                $bucket = '31_60';
            } elseif ($daysOverdue <= 90) {
                $bucket = '61_90';
            } else {
                $bucket = '90_plus';
            }

            $agingData[$key][$bucket] += $remaining;
            $agingData[$key]['total'] += $remaining;
            $totals[$bucket] += $remaining;
            $totals['total'] += $remaining;
        }

        return $this->view('debts.aging', [
            'agingData' => array_values($agingData),
            'totals' => $totals,
            'type' => $type,
        ]);
    }

    public function byContact()
    {
        $type = $this->input('type') ?: 'receivable';

        $data = Database::fetchAll(
            "SELECT
                COALESCE(CONCAT(c.first_name, ' ', c.last_name), comp.name, 'Không xác định') as name,
                d.contact_id, d.company_id,
                COUNT(*) as debt_count,
                SUM(d.amount) as total_amount,
                SUM(d.paid_amount) as total_paid,
                SUM(d.amount - d.paid_amount) as total_remaining,
                SUM(CASE WHEN d.status = 'overdue' THEN d.amount - d.paid_amount ELSE 0 END) as overdue_amount
             FROM debts d
             LEFT JOIN contacts c ON d.contact_id = c.id
             LEFT JOIN companies comp ON d.company_id = comp.id
             WHERE d.type = ?
             GROUP BY d.contact_id, d.company_id
             ORDER BY total_remaining DESC",
            [$type]
        );

        return $this->view('debts.by_contact', [
            'data' => $data,
            'type' => $type,
        ]);
    }
}
