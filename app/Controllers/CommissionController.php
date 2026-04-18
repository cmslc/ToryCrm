<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Services\CommissionService;

class CommissionController extends Controller
{
    private CommissionService $service;

    public function __construct()
    {
        $this->service = new CommissionService();
    }

    public function index()
    {
        $tid = $this->tenantId();
        $page = max(1, (int) $this->input('page', 1));
        $perPage = in_array((int)$this->input('per_page'), [10,20,50,100]) ? (int)$this->input('per_page') : 20;
        $offset = ($page - 1) * $perPage;

        // Filters
        $userId = $this->input('user_id');
        $status = $this->input('status');
        $period = $this->input('period');
        $entityType = $this->input('entity_type');

        $where = "c.tenant_id = ?";
        $params = [$tid];

        if ($userId) {
            $where .= " AND c.user_id = ?";
            $params[] = $userId;
        }
        if ($status) {
            $where .= " AND c.status = ?";
            $params[] = $status;
        }
        if ($period) {
            $where .= " AND DATE_FORMAT(c.created_at, '%Y-%m') = ?";
            $params[] = $period;
        }
        if ($entityType) {
            $where .= " AND c.entity_type = ?";
            $params[] = $entityType;
        }

        $where .= $this->getOwnerScopeSql('c.user_id');

        $total = (int) Database::fetch(
            "SELECT COUNT(*) as cnt FROM commissions c WHERE {$where}",
            $params
        )['cnt'];

        $commissions = Database::fetchAll(
            "SELECT c.*, u.name as user_name, cr.name as rule_name
             FROM commissions c
             LEFT JOIN users u ON c.user_id = u.id
             LEFT JOIN commission_rules cr ON c.rule_id = cr.id
             WHERE {$where}
             ORDER BY c.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        // Summary for current period
        $currentMonth = $period ?: date('Y-m');
        $summary = Database::fetch(
            "SELECT
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as paid,
                SUM(amount) as total
             FROM commissions WHERE tenant_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ?" . $this->getOwnerScopeSql('user_id'),
            [$tid, $currentMonth]
        );

        $users = Database::fetchAll(
            "SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.tenant_id = ? AND u.is_active = 1 ORDER BY d.name, u.name",
            [$tid]
        );

        $totalPages = ceil($total / $perPage);

        // Chart: commission by employee
        $byEmployee = Database::fetchAll(
            "SELECT u.name, SUM(c.amount) as total FROM commissions c JOIN users u ON c.user_id = u.id
             WHERE c.tenant_id = ? AND DATE_FORMAT(c.created_at, '%Y-%m') = ?
             GROUP BY c.user_id, u.name ORDER BY total DESC LIMIT 10",
            [$tid, $currentMonth]
        );

        return $this->view('commissions.index', [
            'commissions' => $commissions,
            'summary' => $summary,
            'users' => $users,
            'byEmployee' => $byEmployee,
            'filters' => [
                'user_id' => $userId,
                'status' => $status,
                'period' => $period ?: date('Y-m'),
                'entity_type' => $entityType,
            ],
            'pagination' => [
                'page' => $page,
                'total' => $total,
                'total_pages' => $totalPages,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function rules()
    {
        $tid = $this->tenantId();
        $rules = Database::fetchAll(
            "SELECT * FROM commission_rules WHERE tenant_id = ? ORDER BY created_at DESC",
            [$tid]
        );

        return $this->view('commissions.rules', ['rules' => $rules]);
    }

    public function createRule()
    {
        return $this->view('commissions.rule_form', ['rule' => null]);
    }

    public function storeRule()
    {
        if (!$this->isPost()) return $this->redirect('commissions/rules');

        $data = $this->allInput();

        Database::insert('commission_rules', [
            'tenant_id' => $this->tenantId(),
            'name' => trim($data['name'] ?? ''),
            'type' => $data['type'] ?? 'percent',
            'value' => (float) ($data['value'] ?? 0),
            'apply_to' => $data['apply_to'] ?? 'deal',
            'min_value' => (float) ($data['min_value'] ?? 0),
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->setFlash('success', 'Quy tắc hoa hồng đã được tạo.');
        return $this->redirect('commissions/rules');
    }

    public function editRule($id)
    {
        $rule = Database::fetch(
            "SELECT * FROM commission_rules WHERE id = ? AND tenant_id = ?",
            [$id, $this->tenantId()]
        );
        if (!$rule) {
            $this->setFlash('error', 'Quy tắc không tồn tại.');
            return $this->redirect('commissions/rules');
        }

        return $this->view('commissions.rule_form', ['rule' => $rule]);
    }

    public function updateRule($id)
    {
        if (!$this->isPost()) return $this->redirect('commissions/rules');

        $rule = Database::fetch(
            "SELECT * FROM commission_rules WHERE id = ? AND tenant_id = ?",
            [$id, $this->tenantId()]
        );
        if (!$rule) {
            $this->setFlash('error', 'Quy tắc không tồn tại.');
            return $this->redirect('commissions/rules');
        }

        $data = $this->allInput();

        Database::update('commission_rules', [
            'name' => trim($data['name'] ?? ''),
            'type' => $data['type'] ?? 'percent',
            'value' => (float) ($data['value'] ?? 0),
            'apply_to' => $data['apply_to'] ?? 'deal',
            'min_value' => (float) ($data['min_value'] ?? 0),
            'is_active' => isset($data['is_active']) ? 1 : 0,
        ], 'id = ?', [$id]);

        $this->setFlash('success', 'Quy tắc hoa hồng đã được cập nhật.');
        return $this->redirect('commissions/rules');
    }

    public function deleteRule($id)
    {
        if (!$this->isPost()) return $this->redirect('commissions/rules');

        Database::delete('commission_rules', 'id = ? AND tenant_id = ?', [$id, $this->tenantId()]);
        $this->setFlash('success', 'Quy tắc đã được xóa.');
        return $this->redirect('commissions/rules');
    }

    public function approve($id)
    {
        if (!$this->isPost()) return $this->redirect('commissions');

        if ($this->service->approve($id)) {
            $this->setFlash('success', 'Hoa hồng đã được duyệt.');
        } else {
            $this->setFlash('error', 'Không thể duyệt hoa hồng này.');
        }
        return $this->redirect('commissions');
    }

    public function markPaid($id)
    {
        if (!$this->isPost()) return $this->redirect('commissions');

        if ($this->service->markPaid($id)) {
            $this->setFlash('success', 'Hoa hồng đã được đánh dấu đã trả.');
        } else {
            $this->setFlash('error', 'Không thể đánh dấu đã trả.');
        }
        return $this->redirect('commissions');
    }

    public function bulkApprove()
    {
        if (!$this->isPost()) return $this->redirect('commissions');

        $ids = $this->input('ids');
        if (!is_array($ids)) $ids = explode(',', $ids ?? '');

        $count = 0;
        foreach ($ids as $id) {
            if ($this->service->approve((int) $id)) $count++;
        }

        $this->setFlash('success', "Đã duyệt {$count} hoa hồng.");
        return $this->redirect('commissions');
    }

    public function bulkPaid()
    {
        if (!$this->isPost()) return $this->redirect('commissions');

        $ids = $this->input('ids');
        if (!is_array($ids)) $ids = explode(',', $ids ?? '');

        $count = 0;
        foreach ($ids as $id) {
            if ($this->service->markPaid((int) $id)) $count++;
        }

        $this->setFlash('success', "Đã đánh dấu đã trả {$count} hoa hồng.");
        return $this->redirect('commissions');
    }

    public function myCommissions()
    {
        $userId = $this->userId();
        $period = $this->input('period');

        $commissions = $this->service->getForUser($userId, $period);

        // Monthly summary for chart (last 12 months)
        $tid = $this->tenantId();
        $monthlyData = Database::fetchAll(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(amount) as total
             FROM commissions WHERE user_id = ? AND tenant_id = ?
             AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY DATE_FORMAT(created_at, '%Y-%m')
             ORDER BY month",
            [$userId, $tid]
        );

        $totalPending = 0;
        $totalApproved = 0;
        $totalPaid = 0;
        foreach ($commissions as $c) {
            match ($c['status']) {
                'pending' => $totalPending += $c['amount'],
                'approved' => $totalApproved += $c['amount'],
                'paid' => $totalPaid += $c['amount'],
                default => null,
            };
        }

        return $this->view('commissions.my', [
            'commissions' => $commissions,
            'monthlyData' => $monthlyData,
            'totalPending' => $totalPending,
            'totalApproved' => $totalApproved,
            'totalPaid' => $totalPaid,
            'filters' => ['period' => $period],
        ]);
    }

    public function report()
    {
        $tid = $this->tenantId();
        $year = (int) ($this->input('year') ?: date('Y'));

        $monthlyReport = $this->service->getMonthlyReport($tid, $year);

        // Organize data by user
        $userData = [];
        foreach ($monthlyReport as $row) {
            $uid = $row['user_id'];
            if (!isset($userData[$uid])) {
                $userData[$uid] = [
                    'name' => $row['user_name'],
                    'months' => array_fill(1, 12, 0),
                    'total' => 0,
                ];
            }
            $userData[$uid]['months'][(int) $row['month']] = (float) $row['total'];
            $userData[$uid]['total'] += (float) $row['total'];
        }

        return $this->view('commissions.report', [
            'year' => $year,
            'userData' => $userData,
        ]);
    }

    public function exportCsv()
    {
        $tid = $this->tenantId();
        $period = $this->input('period') ?: date('Y-m');
        $commissions = Database::fetchAll(
            "SELECT c.*, u.name as user_name FROM commissions c LEFT JOIN users u ON c.user_id = u.id WHERE c.tenant_id = ? AND DATE_FORMAT(c.created_at, '%Y-%m') = ? ORDER BY u.name",
            [$tid, $period]
        );
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="hoa-hong-' . $period . '.csv"');
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Nhân viên','Loại','Giá trị GD','Tỷ lệ','Số tiền HH','Trạng thái','Ngày']);
        $sl = ['pending'=>'Chờ duyệt','approved'=>'Đã duyệt','paid'=>'Đã trả'];
        foreach ($commissions as $c) {
            fputcsv($out, [$c['user_name'], $c['entity_type'], $c['base_amount'], $c['rate'].'%', $c['amount'], $sl[$c['status']] ?? '', $c['created_at']]);
        }
        fclose($out);
        exit;
    }
}
