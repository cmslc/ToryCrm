<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Models\InstallationRequest;

class InstallationRequestController extends Controller
{
    private const STATUSES = ['pending','scheduled','completed','cancelled'];

    public static function statusLabel(string $s): string
    {
        return [
            'pending'   => 'Chờ điều phối',
            'scheduled' => 'Đã điều phối',
            'completed' => 'Đã thi công',
            'cancelled' => 'Đã hủy',
        ][$s] ?? $s;
    }

    public static function statusColor(string $s): string
    {
        return [
            'pending'   => 'warning',
            'scheduled' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
        ][$s] ?? 'secondary';
    }

    public function index()
    {
        $this->authorize('installation_requests', 'view');
        $search = trim((string)$this->input('search'));
        $status = $this->input('status');
        $ownerId = $this->input('owner_id');
        $page = max(1, (int) $this->input('page') ?: 1);
        $perPage = in_array((int)$this->input('per_page'), [10,20,50,100]) ? (int)$this->input('per_page') : 20;
        $offset = ($page - 1) * $perPage;

        $where = ["ir.is_deleted = 0", "ir.tenant_id = ?"];
        $params = [Database::tenantId()];

        if ($search !== '') {
            $where[] = "(ir.code LIKE ? OR ir.customer_contact_name LIKE ? OR ir.installation_address LIKE ? OR o.order_number LIKE ? OR c.full_name LIKE ? OR c.company_name LIKE ?)";
            $s = "%{$search}%";
            $params = array_merge($params, [$s, $s, $s, $s, $s, $s]);
        }

        if ($status && in_array($status, self::STATUSES, true)) {
            $where[] = "ir.status = ?";
            $params[] = $status;
        }

        if ($ownerId) {
            $where[] = "ir.owner_id = ?";
            $params[] = $ownerId;
        }

        $ownerScope = $this->ownerScope('ir', 'owner_id', 'installation_requests');
        if ($ownerScope['where']) {
            $where[] = $ownerScope['where'];
            $params = array_merge($params, $ownerScope['params']);
        }

        $whereClause = implode(' AND ', $where);

        $total = (int)(Database::fetch(
            "SELECT COUNT(*) as count
             FROM installation_requests ir
             LEFT JOIN orders o ON ir.order_id = o.id
             LEFT JOIN contacts c ON ir.contact_id = c.id
             WHERE {$whereClause}",
            $params
        )['count'] ?? 0);

        $rows = Database::fetchAll(
            "SELECT ir.*,
                    o.order_number,
                    c.full_name as c_full_name, c.company_name as c_company_name,
                    u.name as owner_name, u.avatar as owner_avatar
             FROM installation_requests ir
             LEFT JOIN orders o ON ir.order_id = o.id
             LEFT JOIN contacts c ON ir.contact_id = c.id
             LEFT JOIN users u ON ir.owner_id = u.id
             WHERE {$whereClause}
             ORDER BY ir.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $scopeWhere = ["ir.is_deleted = 0", "ir.tenant_id = ?"];
        $scopeParams = [Database::tenantId()];
        $os = $this->ownerScope('ir', 'owner_id', 'installation_requests');
        if ($os['where']) { $scopeWhere[] = $os['where']; $scopeParams = array_merge($scopeParams, $os['params']); }
        $scopeClause = implode(' AND ', $scopeWhere);
        $statusCounts = Database::fetchAll("SELECT status, COUNT(*) as count FROM installation_requests ir WHERE {$scopeClause} GROUP BY status", $scopeParams);
        $totalAll = array_sum(array_column($statusCounts, 'count'));

        return $this->view('installation-requests.index', [
            'requests' => [
                'items' => $rows,
                'total' => $total,
                'page' => $page,
                'total_pages' => (int)ceil($total / $perPage),
            ],
            'statusCounts' => $statusCounts,
            'totalAll' => $totalAll,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'owner_id' => $ownerId,
                'per_page' => $perPage,
            ],
            'users' => $this->getVisibleUsersWithAvatar(),
        ]);
    }

    public function create()
    {
        $this->authorize('installation_requests', 'create');
        $tid = Database::tenantId();
        $model = new InstallationRequest();
        $code = $model->generateCode();

        $fromOrderId = (int)$this->input('order_id');
        $order = null;
        $items = [];
        $contact = null;
        if ($fromOrderId) {
            $order = Database::fetch(
                "SELECT o.*, c.full_name as c_full_name, c.company_name as c_company_name,
                        c.account_code as c_account_code, c.address as c_address,
                        c.phone as c_phone, c.company_phone as c_company_phone
                 FROM orders o
                 LEFT JOIN contacts c ON o.contact_id = c.id
                 WHERE o.id = ? AND o.tenant_id = ?",
                [$fromOrderId, $tid]
            );
            if ($order) {
                $items = Database::fetchAll(
                    "SELECT oi.*, p.sku as p_sku, p.color as p_color, p.dimensions as p_dimensions
                     FROM order_items oi
                     LEFT JOIN products p ON oi.product_id = p.id
                     WHERE oi.order_id = ?
                     ORDER BY oi.sort_order, oi.id",
                    [$fromOrderId]
                );
                if ($order['contact_id']) {
                    $contact = Database::fetch(
                        "SELECT id, full_name, company_name, account_code, address, phone, company_phone FROM contacts WHERE id = ?",
                        [$order['contact_id']]
                    );
                }
            }
        } elseif ($preContactId = (int)$this->input('contact_id')) {
            $contact = Database::fetch(
                "SELECT id, full_name, company_name, account_code, address, phone, company_phone FROM contacts WHERE id = ? AND tenant_id = ?",
                [$preContactId, $tid]
            );
        }

        $me = Database::fetch("SELECT u.name, u.phone, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.id = ?", [$this->userId()]);

        return $this->view('installation-requests.create', [
            'code' => $code,
            'order' => $order,
            'contact' => $contact,
            'orderItems' => $items,
            'me' => $me,
            'users' => $this->getVisibleUsersWithAvatar(),
        ]);
    }

    public function store()
    {
        if (!$this->isPost()) return $this->redirect('installation-requests');
        $this->authorize('installation_requests', 'create');

        $data = $this->allInput();
        $tid = Database::tenantId();
        $model = new InstallationRequest();
        $code = $model->generateCode();

        Database::beginTransaction();
        try {
            $requestId = Database::insert('installation_requests', [
                'tenant_id' => $tid,
                'code' => $code,
                'order_id' => !empty($data['order_id']) ? (int)$data['order_id'] : null,
                'contact_id' => !empty($data['contact_id']) ? (int)$data['contact_id'] : null,
                'contact_person_id' => !empty($data['contact_person_id']) ? (int)$data['contact_person_id'] : null,
                'department' => trim($data['department'] ?? '') ?: null,
                'requester_name' => trim($data['requester_name'] ?? '') ?: null,
                'requester_phone' => trim($data['requester_phone'] ?? '') ?: null,
                'contractor' => trim($data['contractor'] ?? '') ?: null,
                'installation_address' => trim($data['installation_address'] ?? '') ?: null,
                'customer_contact_name' => trim($data['customer_contact_name'] ?? '') ?: null,
                'customer_contact_phone' => trim($data['customer_contact_phone'] ?? '') ?: null,
                'requested_date' => !empty($data['requested_date']) ? $data['requested_date'] : null,
                'execution_date' => !empty($data['execution_date']) ? $data['execution_date'] : null,
                'installer_name' => trim($data['installer_name'] ?? '') ?: null,
                'condition_report' => trim($data['condition_report'] ?? '') ?: null,
                'status' => in_array($data['status'] ?? '', self::STATUSES, true) ? $data['status'] : 'pending',
                'notes' => trim($data['notes'] ?? '') ?: null,
                'owner_id' => !empty($data['owner_id']) ? (int)$data['owner_id'] : $this->userId(),
                'created_by' => $this->userId(),
            ]);

            $this->saveItems($requestId, $data['items'] ?? []);

            Database::commit();
        } catch (\Exception $e) {
            Database::rollback();
            $this->setFlash('error', 'Lỗi tạo yêu cầu thi công: ' . $e->getMessage());
            return $this->back();
        }

        Database::insert('activities', [
            'tenant_id' => $tid,
            'type' => 'deal',
            'title' => "Yêu cầu thi công tạo mới: {$code}",
            'user_id' => $this->userId(),
        ]);

        $this->setFlash('success', "Đã tạo yêu cầu thi công {$code}.");
        return $this->redirect('installation-requests/' . $requestId);
    }

    public function show($id)
    {
        $this->authorize('installation_requests', 'view');
        $request = $this->loadRequest((int)$id);
        if (!$request) return $this->redirect('installation-requests');

        if (!$this->canAccessEntity('installation_request', (int)$request['id'], $request['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('installation-requests');
        }

        $items = (new InstallationRequest())->getItems((int)$id);

        return $this->view('installation-requests.show', [
            'request' => $request,
            'items' => $items,
        ]);
    }

    public function edit($id)
    {
        $this->authorize('installation_requests', 'edit');
        $request = $this->loadRequest((int)$id);
        if (!$request) return $this->redirect('installation-requests');

        if (!$this->canAccessEntity('installation_request', (int)$request['id'], $request['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('installation-requests');
        }

        $items = (new InstallationRequest())->getItems((int)$id);

        return $this->view('installation-requests.edit', [
            'request' => $request,
            'items' => $items,
            'users' => $this->getVisibleUsersWithAvatar(),
        ]);
    }

    public function update($id)
    {
        if (!$this->isPost()) return $this->redirect('installation-requests/' . $id);
        $this->authorize('installation_requests', 'edit');

        $request = $this->loadRequest((int)$id);
        if (!$request) return $this->redirect('installation-requests');

        if (!$this->canAccessEntity('installation_request', (int)$request['id'], $request['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền.');
            return $this->redirect('installation-requests');
        }

        $data = $this->allInput();

        Database::beginTransaction();
        try {
            Database::update('installation_requests', [
                'contact_id' => !empty($data['contact_id']) ? (int)$data['contact_id'] : null,
                'contact_person_id' => !empty($data['contact_person_id']) ? (int)$data['contact_person_id'] : null,
                'department' => trim($data['department'] ?? '') ?: null,
                'requester_name' => trim($data['requester_name'] ?? '') ?: null,
                'requester_phone' => trim($data['requester_phone'] ?? '') ?: null,
                'contractor' => trim($data['contractor'] ?? '') ?: null,
                'installation_address' => trim($data['installation_address'] ?? '') ?: null,
                'customer_contact_name' => trim($data['customer_contact_name'] ?? '') ?: null,
                'customer_contact_phone' => trim($data['customer_contact_phone'] ?? '') ?: null,
                'requested_date' => !empty($data['requested_date']) ? $data['requested_date'] : null,
                'execution_date' => !empty($data['execution_date']) ? $data['execution_date'] : null,
                'installer_name' => trim($data['installer_name'] ?? '') ?: null,
                'condition_report' => trim($data['condition_report'] ?? '') ?: null,
                'status' => in_array($data['status'] ?? '', self::STATUSES, true) ? $data['status'] : $request['status'],
                'notes' => trim($data['notes'] ?? '') ?: null,
                'owner_id' => !empty($data['owner_id']) ? (int)$data['owner_id'] : $request['owner_id'],
            ], 'id = ?', [$id]);

            Database::query("DELETE FROM installation_request_items WHERE request_id = ?", [$id]);
            $this->saveItems((int)$id, $data['items'] ?? []);

            Database::commit();
        } catch (\Exception $e) {
            Database::rollback();
            $this->setFlash('error', 'Lỗi cập nhật: ' . $e->getMessage());
            return $this->back();
        }

        $this->setFlash('success', 'Đã cập nhật yêu cầu thi công.');
        return $this->redirect('installation-requests/' . $id);
    }

    public function delete($id)
    {
        if (!$this->isPost()) return $this->redirect('installation-requests');
        $this->authorize('installation_requests', 'delete');

        $request = $this->loadRequest((int)$id);
        if (!$request) return $this->redirect('installation-requests');

        if (!$this->canAccessEntity('installation_request', (int)$request['id'], $request['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền.');
            return $this->redirect('installation-requests');
        }

        Database::update('installation_requests', ['is_deleted' => 1], 'id = ?', [$id]);
        $this->setFlash('success', 'Đã xóa yêu cầu thi công.');
        return $this->redirect('installation-requests');
    }

    public function pdf($id)
    {
        $this->authorize('installation_requests', 'view');
        $request = $this->loadRequest((int)$id);
        if (!$request) return $this->redirect('installation-requests');

        $items = (new InstallationRequest())->getItems((int)$id);

        $templateId = (int)($this->input('template_id') ?: 0);
        $replacements = \App\Services\DocumentService::installationReplacements($request, $items);
        $rendered = \App\Services\DocumentService::render('installation', $templateId ?: null, $replacements);

        $noLayout = true;
        $title = 'YCTC ' . ($request['code'] ?? '');

        if ($rendered !== null) {
            echo '<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><title>' . htmlspecialchars($title) . '</title>'
                . '<style>body{font-family:"Segoe UI",Arial,sans-serif;font-size:13px;color:#333;padding:40px;max-width:900px;margin:0 auto}'
                . 'table{width:100%;border-collapse:collapse;margin:10px 0}th,td{padding:8px;border:1px solid #ddd;font-size:12px}'
                . 'h2{color:#405189}.no-print{text-align:center;margin-bottom:20px}@media print{.no-print{display:none}body{padding:20px}}</style>'
                . '</head><body><div class="no-print"><button onclick="window.print()" style="padding:10px 30px;background:#405189;color:#fff;border:none;border-radius:4px;cursor:pointer">In / Lưu PDF</button></div>'
                . $rendered
                . '<script>setTimeout(function(){window.print();},400);</script>'
                . '</body></html>';
            return;
        }

        // Fallback built-in template
        echo $this->renderBuiltInPdf($request, $items);
    }

    private function renderBuiltInPdf(array $request, array $items): string
    {
        $cp = Database::fetch(
            "SELECT * FROM company_profiles WHERE tenant_id = ? AND is_default = 1 AND is_active = 1 LIMIT 1",
            [Database::tenantId()]
        ) ?: [];

        ob_start();
        include __DIR__ . '/../../resources/views/installation-requests/pdf.php';
        return (string)ob_get_clean();
    }

    private function saveItems(int $requestId, $items): void
    {
        if (!is_array($items) || empty($items)) return;
        $sort = 0;
        foreach ($items as $item) {
            $name = trim((string)($item['product_name'] ?? ''));
            if ($name === '') continue;
            Database::insert('installation_request_items', [
                'request_id' => $requestId,
                'product_id' => !empty($item['product_id']) ? (int)$item['product_id'] : null,
                'product_name' => $name,
                'product_sku' => trim((string)($item['product_sku'] ?? '')) ?: null,
                'size_color' => trim((string)($item['size_color'] ?? '')) ?: null,
                'unit' => trim((string)($item['unit'] ?? 'Chiếc')) ?: 'Chiếc',
                'quantity' => (float)($item['quantity'] ?? 0),
                'check_status' => trim((string)($item['check_status'] ?? '')) ?: null,
                'notes' => trim((string)($item['notes'] ?? '')) ?: null,
                'sort_order' => $sort++,
            ]);
        }
    }

    private function loadRequest(int $id): ?array
    {
        $row = Database::fetch(
            "SELECT ir.*,
                    o.order_number, o.id as o_id,
                    c.full_name as c_full_name, c.company_name as c_company_name,
                    c.account_code as c_account_code, c.address as c_address,
                    c.phone as c_phone, c.company_phone as c_company_phone,
                    u.name as owner_name, u.avatar as owner_avatar,
                    uc.name as created_by_name
             FROM installation_requests ir
             LEFT JOIN orders o ON ir.order_id = o.id
             LEFT JOIN contacts c ON ir.contact_id = c.id
             LEFT JOIN users u ON ir.owner_id = u.id
             LEFT JOIN users uc ON ir.created_by = uc.id
             WHERE ir.id = ? AND ir.tenant_id = ? AND ir.is_deleted = 0",
            [$id, Database::tenantId()]
        );
        if (!$row) {
            $this->setFlash('error', 'Yêu cầu thi công không tồn tại.');
            return null;
        }
        return $row;
    }

    public function updateStatus($id)
    {
        if (!$this->isPost()) return $this->redirect('installation-requests/' . $id);
        $this->authorize('installation_requests', 'edit');

        $request = $this->loadRequest((int)$id);
        if (!$request) return $this->redirect('installation-requests');

        if (!$this->canAccessEntity('installation_request', (int)$request['id'], $request['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền.');
            return $this->redirect('installation-requests');
        }

        $status = $this->input('status');
        if (!in_array($status, self::STATUSES, true)) {
            $this->setFlash('error', 'Trạng thái không hợp lệ.');
            return $this->back();
        }

        Database::update('installation_requests', ['status' => $status], 'id = ?', [$id]);
        $this->setFlash('success', 'Đã cập nhật trạng thái.');
        return $this->redirect('installation-requests/' . $id);
    }
}
