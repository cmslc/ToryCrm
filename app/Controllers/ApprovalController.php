<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Services\ApprovalService;

class ApprovalController extends Controller
{
    public function index()
    {
        $flows = Database::fetchAll(
            "SELECT af.*, u.name as created_by_name,
                    (SELECT COUNT(*) FROM approval_requests ar WHERE ar.flow_id = af.id) as request_count
             FROM approval_flows af
             LEFT JOIN users u ON af.created_by = u.id
             WHERE af.tenant_id = ?
             ORDER BY af.created_at DESC",
            [Database::tenantId()]
        );

        // Attach steps to each flow
        foreach ($flows as &$flow) {
            $flow['steps'] = Database::fetchAll(
                "SELECT fs.*, u.name as approver_name
                 FROM approval_flow_steps fs
                 JOIN users u ON fs.approver_id = u.id
                 WHERE fs.flow_id = ?
                 ORDER BY fs.step_order ASC",
                [$flow['id']]
            );
        }

        return $this->view('approvals.index', [
            'flows' => $flows,
        ]);
    }

    public function pending()
    {
        $pending = ApprovalService::getPendingForUser($this->userId());

        // Enrich with entity titles
        foreach ($pending as &$item) {
            $item['entity_title'] = $this->getEntityTitle($item['entity_type'], $item['entity_id']);
        }

        return $this->view('approvals.pending', [
            'pending' => $pending,
        ]);
    }

    public function create()
    {
        $users = Database::fetchAll("SELECT id, name FROM users WHERE is_active = 1 ORDER BY name");
        $modules = [
            'orders' => 'Đơn hàng',
            'deals' => 'Cơ hội',
            'purchase_orders' => 'Đơn hàng mua',
            'fund_transactions' => 'Giao dịch quỹ',
        ];

        return $this->view('approvals.create', [
            'users' => $users,
            'modules' => $modules,
        ]);
    }

    public function store()
    {
        if (!$this->isPost()) return $this->redirect('approvals');

        $data = $this->allInput();
        $name = trim($data['name'] ?? '');

        if (empty($name)) {
            $this->setFlash('error', 'Tên quy trình không được để trống.');
            return $this->back();
        }

        $conditions = [];
        if (!empty($data['condition_field']) && !empty($data['condition_value'])) {
            $operator = $data['condition_operator'] ?? '=';
            $conditions[$data['condition_field']] = [
                'operator' => $operator,
                'value' => $data['condition_value'],
            ];
        }

        $flowId = Database::insert('approval_flows', [
            'name' => $name,
            'module' => $data['module'] ?? 'orders',
            'conditions' => json_encode($conditions),
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'tenant_id' => Database::tenantId(),
            'created_by' => $this->userId(),
        ]);

        // Save steps
        $approverIds = $data['approver_id'] ?? [];
        $stepLabels = $data['step_label'] ?? [];

        if (is_array($approverIds)) {
            foreach ($approverIds as $i => $approverId) {
                if (empty($approverId)) continue;

                Database::insert('approval_flow_steps', [
                    'flow_id' => $flowId,
                    'step_order' => $i + 1,
                    'step_label' => $stepLabels[$i] ?? ('Bước ' . ($i + 1)),
                    'approver_id' => (int) $approverId,
                ]);
            }
        }

        $this->setFlash('success', 'Đã tạo quy trình phê duyệt.');
        return $this->redirect('approvals');
    }

    public function approve($requestId)
    {
        if (!$this->isPost()) return $this->redirect('approvals/pending');

        $comment = trim($this->input('comment', ''));
        $result = ApprovalService::approve((int) $requestId, $this->userId(), $comment ?: null);

        if ($result) {
            $this->setFlash('success', 'Đã phê duyệt thành công.');
        } else {
            $this->setFlash('error', 'Không thể phê duyệt. Bạn không có quyền hoặc yêu cầu đã xử lý.');
        }

        return $this->redirect('approvals/pending');
    }

    public function reject($requestId)
    {
        if (!$this->isPost()) return $this->redirect('approvals/pending');

        $comment = trim($this->input('comment', ''));
        $result = ApprovalService::reject((int) $requestId, $this->userId(), $comment ?: null);

        if ($result) {
            $this->setFlash('success', 'Đã từ chối yêu cầu.');
        } else {
            $this->setFlash('error', 'Không thể từ chối. Bạn không có quyền hoặc yêu cầu đã xử lý.');
        }

        return $this->redirect('approvals/pending');
    }

    /**
     * Get entity title for display
     */
    private function getEntityTitle(string $entityType, int $entityId): string
    {
        $typeLabels = ['order' => 'Đơn hàng', 'orders' => 'Đơn hàng', 'deal' => 'Cơ hội', 'deals' => 'Cơ hội',
            'fund' => 'Thu/Chi quỹ', 'fund_transactions' => 'Thu/Chi quỹ', 'contract' => 'Hợp đồng', 'contracts' => 'Hợp đồng',
            'purchase_order' => 'Đơn mua', 'purchase_orders' => 'Đơn mua'];

        switch ($entityType) {
            case 'order':
            case 'orders':
                $row = Database::fetch("SELECT order_number FROM orders WHERE id = ?", [$entityId]);
                return $row ? 'Đơn hàng ' . $row['order_number'] : "Đơn hàng #{$entityId}";

            case 'deal':
            case 'deals':
                $row = Database::fetch("SELECT title FROM deals WHERE id = ?", [$entityId]);
                return $row ? $row['title'] : "Cơ hội #{$entityId}";

            case 'purchase_order':
            case 'purchase_orders':
                $row = Database::fetch("SELECT po_number FROM purchase_orders WHERE id = ?", [$entityId]);
                return $row ? 'PO ' . $row['po_number'] : "Đơn mua #{$entityId}";

            case 'fund':
            case 'fund_transactions':
                $row = Database::fetch("SELECT transaction_code FROM fund_transactions WHERE id = ?", [$entityId]);
                return $row ? 'Quỹ ' . $row['transaction_code'] : "Thu/Chi #{$entityId}";

            case 'contract':
            case 'contracts':
                $row = Database::fetch("SELECT title, contract_number FROM contracts WHERE id = ?", [$entityId]);
                return $row ? 'HĐ ' . ($row['contract_number'] ?? $row['title'] ?? '') : "Hợp đồng #{$entityId}";

            default:
                return ($typeLabels[$entityType] ?? $entityType) . " #{$entityId}";
        }
    }
}
