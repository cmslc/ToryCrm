<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Models\FundTransaction;

class FundController extends Controller
{
    public function index()
    {
        $this->authorize('fund', 'view');
        $model = new FundTransaction();
        $page = max(1, (int) $this->input('page') ?: 1);

        $filters = [
            'search' => $this->input('search'),
            'type' => $this->input('type'),
            'status' => $this->input('status'),
            'fund_account_id' => $this->input('fund_account_id'),
            'date_from' => $this->input('date_from'),
            'date_to' => $this->input('date_to'),
        ];
        if (!$this->isSystemAdmin()) {
            $visibleIds = $this->getVisibleUserIds();
            $filters['created_by_in'] = $visibleIds ?: [$this->userId()];
        }
        $transactions = $model->getWithRelations($page, 10, $filters);

        $accounts = $model->getAccounts();
        $summary = $model->getSummary(
            $this->input('date_from') ?: date('Y-m-01'),
            $this->input('date_to') ?: date('Y-m-t')
        );

        // Monthly chart data (6 months)
        $monthlyChart = Database::fetchAll(
            "SELECT DATE_FORMAT(transaction_date, '%Y-%m') as month,
                    SUM(CASE WHEN type='receipt' AND status='confirmed' THEN amount ELSE 0 END) as receipt,
                    SUM(CASE WHEN type='payment' AND status='confirmed' THEN amount ELSE 0 END) as payment
             FROM fund_transactions WHERE tenant_id = ? AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
             GROUP BY DATE_FORMAT(transaction_date, '%Y-%m') ORDER BY month",
            [Database::tenantId()]
        );

        // Category breakdown
        $categories = Database::fetchAll(
            "SELECT category, SUM(amount) as total, type FROM fund_transactions
             WHERE tenant_id = ? AND status = 'confirmed' AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
             GROUP BY category, type ORDER BY total DESC LIMIT 10",
            [Database::tenantId()]
        );

        return $this->view('fund.index', [
            'transactions' => $transactions,
            'accounts' => $accounts,
            'summary' => $summary,
            'monthlyChart' => $monthlyChart,
            'categories' => $categories,
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        $this->authorize('fund', 'create');
        $model = new FundTransaction();
        $type = $this->input('type') ?: 'receipt';
        $code = $model->generateCode($type);
        $accounts = $model->getAccounts();
        $contacts = Database::fetchAll("SELECT id, first_name, last_name FROM contacts ORDER BY first_name");
        $companies = Database::fetchAll("SELECT id, name FROM companies ORDER BY name");

        return $this->view('fund.create', [
            'type' => $type,
            'transactionCode' => $code,
            'accounts' => $accounts,
            'contacts' => $contacts,
            'companies' => $companies,
        ]);
    }

    public function store()
    {
        if (!$this->isPost()) return $this->redirect('fund');
        $this->authorize('fund', 'create');

        $data = $this->allInput();
        $type = $data['type'] ?? 'receipt';
        $amount = (float)($data['amount'] ?? 0);

        if ($amount <= 0) {
            $this->setFlash('error', 'Số tiền phải lớn hơn 0.');
            return $this->back();
        }

        $model = new FundTransaction();
        $code = $model->generateCode($type);

        $id = Database::insert('fund_transactions', [
            'transaction_code' => $code,
            'type' => $type,
            'fund_account_id' => !empty($data['fund_account_id']) ? $data['fund_account_id'] : null,
            'amount' => $amount,
            'category' => trim($data['category'] ?? ''),
            'description' => trim($data['description'] ?? ''),
            'contact_id' => !empty($data['contact_id']) ? $data['contact_id'] : null,
            'company_id' => !empty($data['company_id']) ? $data['company_id'] : null,
            'order_id' => !empty($data['order_id']) ? $data['order_id'] : null,
            'transaction_date' => !empty($data['transaction_date']) ? $data['transaction_date'] : date('Y-m-d'),
            'status' => $data['status'] ?? 'draft',
            'created_by' => $this->userId(),
        ]);

        $label = $type === 'receipt' ? 'Phiếu thu' : 'Phiếu chi';
        $this->setFlash('success', "{$label} {$code} đã được tạo.");
        return $this->redirect('fund/' . $id);
    }

    public function pdf($id)
    {
        $this->authorize('fund', 'view');
        $transaction = Database::fetch(
            "SELECT ft.*, fa.name as fund_account_name,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    comp.name as company_name, u.name as created_by_name
             FROM fund_transactions ft
             LEFT JOIN fund_accounts fa ON ft.fund_account_id = fa.id
             LEFT JOIN contacts c ON ft.contact_id = c.id
             LEFT JOIN companies comp ON ft.company_id = comp.id
             LEFT JOIN users u ON ft.created_by = u.id
             WHERE ft.id = ?",
            [$id]
        );

        if (!$transaction) {
            $this->setFlash('error', 'Phiếu không tồn tại.');
            return $this->redirect('fund');
        }

        echo \App\Services\PdfService::fundTransactionHtml($transaction);
    }

    public function edit($id)
    {
        $this->authorize('fund', 'edit');
        $transaction = Database::fetch("SELECT * FROM fund_transactions WHERE id = ?", [$id]);
        if (!$transaction) {
            $this->setFlash('error', 'Phiếu không tồn tại.');
            return $this->redirect('fund');
        }

        if ($transaction['status'] !== 'draft') {
            $this->setFlash('error', 'Chỉ có thể sửa phiếu ở trạng thái Nháp.');
            return $this->redirect('fund/' . $id);
        }

        $model = new FundTransaction();
        $accounts = $model->getAccounts();
        $contacts = Database::fetchAll("SELECT id, first_name, last_name FROM contacts ORDER BY first_name");
        $companies = Database::fetchAll("SELECT id, name FROM companies ORDER BY name");

        return $this->view('fund.edit', [
            'transaction' => $transaction,
            'accounts' => $accounts,
            'contacts' => $contacts,
            'companies' => $companies,
        ]);
    }

    public function update($id)
    {
        if (!$this->isPost()) return $this->redirect('fund/' . $id);
        $this->authorize('fund', 'edit');

        $transaction = Database::fetch("SELECT * FROM fund_transactions WHERE id = ?", [$id]);
        if (!$transaction || $transaction['status'] !== 'draft') {
            $this->setFlash('error', 'Không thể sửa phiếu này.');
            return $this->redirect('fund/' . $id);
        }

        $data = $this->allInput();
        $amount = (float)($data['amount'] ?? 0);
        if ($amount <= 0) {
            $this->setFlash('error', 'Số tiền phải lớn hơn 0.');
            return $this->back();
        }

        Database::update('fund_transactions', [
            'fund_account_id' => !empty($data['fund_account_id']) ? $data['fund_account_id'] : null,
            'amount' => $amount,
            'category' => trim($data['category'] ?? ''),
            'description' => trim($data['description'] ?? ''),
            'contact_id' => !empty($data['contact_id']) ? $data['contact_id'] : null,
            'company_id' => !empty($data['company_id']) ? $data['company_id'] : null,
            'transaction_date' => !empty($data['transaction_date']) ? $data['transaction_date'] : date('Y-m-d'),
        ], 'id = ?', [$id]);

        $this->setFlash('success', 'Phiếu đã được cập nhật.');
        return $this->redirect('fund/' . $id);
    }

    public function show($id)
    {
        $this->authorize('fund', 'view');
        $transaction = Database::fetch(
            "SELECT ft.*,
                    fa.name as fund_account_name, fa.type as fund_account_type,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    comp.name as company_name,
                    u.name as created_by_name,
                    uc.name as confirmed_by_name
             FROM fund_transactions ft
             LEFT JOIN fund_accounts fa ON ft.fund_account_id = fa.id
             LEFT JOIN contacts c ON ft.contact_id = c.id
             LEFT JOIN companies comp ON ft.company_id = comp.id
             LEFT JOIN users u ON ft.created_by = u.id
             LEFT JOIN users uc ON ft.confirmed_by = uc.id
             WHERE ft.id = ?",
            [$id]
        );

        if (!$transaction) {
            $this->setFlash('error', 'Phiếu không tồn tại.');
            return $this->redirect('fund');
        }

        return $this->view('fund.show', ['transaction' => $transaction]);
    }

    public function confirm($id)
    {
        if (!$this->isPost()) return $this->redirect('fund/' . $id);
        $this->authorize('fund', 'approve');

        $transaction = Database::fetch("SELECT * FROM fund_transactions WHERE id = ?", [$id]);
        if (!$transaction || $transaction['status'] !== 'draft') {
            $this->setFlash('error', 'Không thể xác nhận phiếu này.');
            return $this->redirect('fund/' . $id);
        }

        Database::update('fund_transactions', [
            'status' => 'confirmed',
            'confirmed_by' => $this->userId(),
            'confirmed_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        // Update fund account balance
        if ($transaction['fund_account_id']) {
            $sign = $transaction['type'] === 'receipt' ? '+' : '-';
            Database::query(
                "UPDATE fund_accounts SET balance = balance {$sign} ? WHERE id = ?",
                [$transaction['amount'], $transaction['fund_account_id']]
            );
        }

        $this->setFlash('success', 'Phiếu đã được xác nhận.');
        return $this->redirect('fund/' . $id);
    }

    public function cancel($id)
    {
        if (!$this->isPost()) return $this->redirect('fund/' . $id);
        $this->authorize('fund', 'edit');

        $transaction = Database::fetch("SELECT * FROM fund_transactions WHERE id = ?", [$id]);
        if (!$transaction) {
            $this->setFlash('error', 'Phiếu không tồn tại.');
            return $this->redirect('fund');
        }

        // Reverse balance if was confirmed
        if ($transaction['status'] === 'confirmed' && $transaction['fund_account_id']) {
            $sign = $transaction['type'] === 'receipt' ? '-' : '+';
            Database::query(
                "UPDATE fund_accounts SET balance = balance {$sign} ? WHERE id = ?",
                [$transaction['amount'], $transaction['fund_account_id']]
            );
        }

        Database::update('fund_transactions', ['status' => 'cancelled'], 'id = ?', [$id]);

        $this->setFlash('success', 'Phiếu đã được hủy.');
        return $this->redirect('fund/' . $id);
    }

    public function delete($id)
    {
        $this->authorize('fund', 'delete');
        $transaction = Database::fetch("SELECT * FROM fund_transactions WHERE id = ?", [$id]);
        if ($transaction && $transaction['status'] === 'confirmed') {
            $this->setFlash('error', 'Không thể xóa phiếu đã xác nhận. Hãy hủy trước.');
            return $this->redirect('fund/' . $id);
        }

        Database::delete('fund_transactions', 'id = ?', [$id]);
        $this->setFlash('success', 'Phiếu đã được xóa.');
        return $this->redirect('fund');
    }
}
