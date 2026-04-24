<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Models\PurchaseOrder;

class PurchaseOrderController extends Controller
{
    use \App\Traits\HasFollowers;

    public function followers($id) {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);
        return $this->json($this->handleFollowers('purchase_order', (int)$id));
    }

    public function changeOwner($id) {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);
        return $this->json($this->handleChangeOwner('purchase_orders', (int)$id));
    }

    public function index()
    {
        $this->authorize('purchase_orders', 'view');
        $model = new PurchaseOrder();
        $page = max(1, (int) $this->input('page') ?: 1);

        $orders = $model->getWithRelations($page, 10, [
            'search' => $this->input('search'),
            'status' => $this->input('status'),
            'payment_status' => $this->input('payment_status'),
        ]);

        return $this->view('purchase-orders.index', [
            'orders' => $orders,
            'filters' => [
                'search' => $this->input('search'),
                'status' => $this->input('status'),
                'payment_status' => $this->input('payment_status'),
                'owner_id' => $this->input('owner_id'),
            ],
            'users' => $this->getVisibleUsersWithAvatar(),
        ]);
    }

    public function create()
    {
        $this->authorize('purchase_orders', 'create');
        $model = new PurchaseOrder();
        $orderCode = $model->generateCode();
        $suppliers = Database::fetchAll("SELECT id, name FROM companies ORDER BY name");
        $products = Database::fetchAll("SELECT id, name, sku, cost_price as price, unit, tax_rate FROM products WHERE is_active = 1 ORDER BY name");
        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name");

        return $this->view('purchase-orders.create', [
            'orderCode' => $orderCode,
            'suppliers' => $suppliers,
            'products' => $products,
            'users' => $users,
        ]);
    }

    public function store()
    {
        if (!$this->isPost()) return $this->redirect('purchase-orders');

        $data = $this->allInput();
        $model = new PurchaseOrder();
        $orderCode = $model->generateCode();

        $orderId = Database::insert('purchase_orders', [
            'order_code' => $orderCode,
            'supplier_id' => !empty($data['supplier_id']) ? $data['supplier_id'] : null,
            'status' => $data['status'] ?? 'draft',
            'discount_amount' => (float)($data['discount_amount'] ?? 0),
            'notes' => trim($data['notes'] ?? ''),
            'expected_date' => !empty($data['expected_date']) ? $data['expected_date'] : null,
            'owner_id' => !empty($data['owner_id']) ? $data['owner_id'] : $this->userId(),
            'created_by' => $this->userId(),
        ]);

        if (!empty($data['items']) && is_array($data['items'])) {
            $sort = 0;
            foreach ($data['items'] as $item) {
                if (empty($item['product_name'])) continue;
                $qty = (float)($item['quantity'] ?? 1);
                $unitPrice = (float)($item['unit_price'] ?? 0);
                $taxRate = (float)($item['tax_rate'] ?? 0);
                $calc = \App\Services\PricingService::lineItem($qty, $unitPrice, $taxRate);
                $taxAmount = $calc['tax'];
                $itemTotal = $calc['total'];

                Database::insert('purchase_order_items', [
                    'purchase_order_id' => $orderId,
                    'product_id' => !empty($item['product_id']) ? $item['product_id'] : null,
                    'product_name' => $item['product_name'],
                    'quantity' => $qty,
                    'unit' => $item['unit'] ?? 'Cái',
                    'unit_price' => $unitPrice,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'total' => $itemTotal,
                    'sort_order' => $sort++,
                ]);
            }
        }

        $model->recalculate($orderId);

        $this->setFlash('success', "Đơn mua {$orderCode} đã được tạo.");
        return $this->redirect('purchase-orders/' . $orderId);
    }

    /** Export purchase orders to CSV with column picking. */
    public function export()
    {
        $this->authorize('purchase_orders', 'view');
        $tid = Database::tenantId();
        $where = ["po.tenant_id = ?"]; $params = [$tid];

        if ($s = $this->input('search')) {
            $where[] = "(po.order_code LIKE ? OR s.name LIKE ?)";
            $like = "%{$s}%";
            $params = array_merge($params, [$like, $like]);
        }
        if ($st = $this->input('status')) { $where[] = "po.status = ?"; $params[] = $st; }
        if ($ps = $this->input('payment_status')) { $where[] = "po.payment_status = ?"; $params[] = $ps; }
        if ($oid = $this->input('owner_id')) { $where[] = "po.owner_id = ?"; $params[] = $oid; }

        $rows = Database::fetchAll(
            "SELECT po.*, s.name as supplier_name, u.name as owner_name
             FROM purchase_orders po
             LEFT JOIN companies s ON po.supplier_id = s.id
             LEFT JOIN users u ON po.owner_id = u.id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY po.created_at DESC",
            $params
        );

        $columns = [
            'order_code'     => ['label' => 'Mã đơn mua'],
            'supplier_name'  => ['label' => 'Nhà cung cấp'],
            'status'         => ['label' => 'Trạng thái'],
            'payment_status' => ['label' => 'Thanh toán'],
            'subtotal'       => ['label' => 'Tạm tính'],
            'discount_amount'=> ['label' => 'Chiết khấu'],
            'tax_amount'     => ['label' => 'Thuế'],
            'total'          => ['label' => 'Tổng tiền'],
            'paid_amount'    => ['label' => 'Đã thanh toán'],
            'currency'       => ['label' => 'Tiền tệ'],
            'expected_date'  => ['label' => 'Ngày dự kiến'],
            'received_date'  => ['label' => 'Ngày nhận'],
            'owner_name'     => ['label' => 'Phụ trách'],
            'notes'          => ['label' => 'Ghi chú'],
            'created_at'     => ['label' => 'Ngày tạo'],
        ];

        $selected = \App\Services\CsvExporter::parseColumnsParam((string)$this->input('columns', ''), $columns);
        \App\Services\CsvExporter::download($rows, $columns, 'purchase_orders_' . date('Ymd_His') . '.csv', $selected);
    }

    public function show($id)
    {
        $this->authorize('purchase_orders', 'view');
        $order = Database::fetch(
            "SELECT po.*, s.name as supplier_name, s.phone as supplier_phone, s.email as supplier_email, s.address as supplier_address,
                    u.name as owner_name, ua.name as approved_by_name
             FROM purchase_orders po
             LEFT JOIN companies s ON po.supplier_id = s.id
             LEFT JOIN users u ON po.owner_id = u.id
             LEFT JOIN users ua ON po.approved_by = ua.id
             WHERE po.id = ?",
            [$id]
        );

        if (!$order) {
            $this->setFlash('error', 'Đơn mua không tồn tại.');
            return $this->redirect('purchase-orders');
        }
        if (!$this->canAccessEntity('purchase_order', (int)$id, $order['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('purchase-orders');
        }

        $model = new PurchaseOrder();
        $items = $model->getItems($id);

        return $this->view('purchase-orders.show', [
            'order' => $order,
            'items' => $items,
        ]);
    }

    public function edit($id)
    {
        $this->authorize('purchase_orders', 'edit');
        $order = Database::fetch("SELECT * FROM purchase_orders WHERE id = ?", [$id]);
        if (!$order) {
            $this->setFlash('error', 'Đơn mua không tồn tại.');
            return $this->redirect('purchase-orders');
        }

        $model = new PurchaseOrder();
        $items = $model->getItems($id);
        $suppliers = Database::fetchAll("SELECT id, name FROM companies ORDER BY name");
        $products = Database::fetchAll("SELECT id, name, sku, cost_price as price, unit, tax_rate FROM products WHERE is_active = 1 ORDER BY name");
        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name");

        return $this->view('purchase-orders.edit', [
            'order' => $order,
            'items' => $items,
            'suppliers' => $suppliers,
            'products' => $products,
            'users' => $users,
        ]);
    }

    public function update($id)
    {
        $this->authorize('purchase_orders', 'edit');
        if (!$this->isPost()) return $this->redirect('purchase-orders/' . $id);

        $order = Database::fetch("SELECT * FROM purchase_orders WHERE id = ?", [$id]);
        if (!$order) {
            $this->setFlash('error', 'Đơn mua không tồn tại.');
            return $this->redirect('purchase-orders');
        }

        $data = $this->allInput();

        Database::update('purchase_orders', [
            'supplier_id' => !empty($data['supplier_id']) ? $data['supplier_id'] : null,
            'status' => $data['status'] ?? $order['status'],
            'discount_amount' => (float)($data['discount_amount'] ?? 0),
            'notes' => trim($data['notes'] ?? ''),
            'payment_status' => $data['payment_status'] ?? $order['payment_status'],
            'paid_amount' => (float)($data['paid_amount'] ?? 0),
            'expected_date' => !empty($data['expected_date']) ? $data['expected_date'] : null,
            'owner_id' => !empty($data['owner_id']) ? $data['owner_id'] : null,
        ], 'id = ?', [$id]);

        // Update items
        Database::delete('purchase_order_items', 'purchase_order_id = ?', [$id]);
        if (!empty($data['items']) && is_array($data['items'])) {
            $sort = 0;
            foreach ($data['items'] as $item) {
                if (empty($item['product_name'])) continue;
                $qty = (float)($item['quantity'] ?? 1);
                $unitPrice = (float)($item['unit_price'] ?? 0);
                $taxRate = (float)($item['tax_rate'] ?? 0);
                $taxAmount = $qty * $unitPrice * $taxRate / 100;

                Database::insert('purchase_order_items', [
                    'purchase_order_id' => $id,
                    'product_id' => !empty($item['product_id']) ? $item['product_id'] : null,
                    'product_name' => $item['product_name'],
                    'quantity' => $qty,
                    'unit' => $item['unit'] ?? 'Cái',
                    'unit_price' => $unitPrice,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'total' => $qty * $unitPrice + $taxAmount,
                    'sort_order' => $sort++,
                ]);
            }
        }

        $model = new PurchaseOrder();
        $model->recalculate($id);

        $this->setFlash('success', 'Đơn mua đã được cập nhật.');
        return $this->redirect('purchase-orders/' . $id);
    }

    public function approve($id)
    {
        if (!$this->isPost()) return $this->redirect('purchase-orders/' . $id);

        $order = Database::fetch("SELECT * FROM purchase_orders WHERE id = ?", [$id]);
        if (!$order || !in_array($order['status'], ['draft', 'pending'])) {
            $this->setFlash('error', 'Chỉ duyệt được đơn ở trạng thái Nháp hoặc Chờ duyệt.');
            return $this->redirect('purchase-orders/' . $id);
        }

        Database::update('purchase_orders', [
            'status' => 'approved',
            'approved_by' => $this->userId(),
            'approved_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        Database::insert('activities', [
            'type' => 'system',
            'title' => "Duyệt đơn mua: {$order['order_code']}",
            'user_id' => $this->userId(),
        ]);

        $this->setFlash('success', "Đã duyệt đơn mua {$order['order_code']}.");
        return $this->redirect('purchase-orders/' . $id);
    }

    public function cancel($id)
    {
        if (!$this->isPost()) return $this->redirect('purchase-orders/' . $id);

        $order = Database::fetch("SELECT * FROM purchase_orders WHERE id = ?", [$id]);
        if (!$order || $order['status'] === 'completed') {
            $this->setFlash('error', 'Không thể hủy đơn đã hoàn thành.');
            return $this->redirect('purchase-orders/' . $id);
        }

        Database::update('purchase_orders', ['status' => 'cancelled'], 'id = ?', [$id]);

        Database::insert('activities', [
            'type' => 'system',
            'title' => "Hủy đơn mua: {$order['order_code']}",
            'user_id' => $this->userId(),
        ]);

        $this->setFlash('success', "Đã hủy đơn mua {$order['order_code']}.");
        return $this->redirect('purchase-orders/' . $id);
    }

    public function delete($id)
    {
        $this->authorize('purchase_orders', 'delete');
        if (!$this->isPost()) return $this->redirect('purchase-orders');

        $order = Database::fetch("SELECT * FROM purchase_orders WHERE id = ?", [$id]);
        if (!$order) { $this->setFlash('error', 'Đơn mua không tồn tại.'); return $this->redirect('purchase-orders'); }

        // Soft delete via status
        Database::update('purchase_orders', ['status' => 'cancelled'], 'id = ?', [$id]);

        Database::insert('activities', [
            'type' => 'system',
            'title' => "Xóa đơn mua: {$order['order_code']}",
            'user_id' => $this->userId(),
        ]);

        $this->setFlash('success', 'Đã xóa đơn mua.');
        return $this->redirect('purchase-orders');
    }

    public function payment($id)
    {
        if (!$this->isPost()) return $this->redirect('purchase-orders/' . $id);

        $order = Database::fetch("SELECT * FROM purchase_orders WHERE id = ?", [$id]);
        if (!$order) { $this->setFlash('error', 'Đơn mua không tồn tại.'); return $this->redirect('purchase-orders'); }

        $amount = (float)$this->input('amount');
        if ($amount <= 0) {
            $this->setFlash('error', 'Số tiền phải lớn hơn 0.');
            return $this->back();
        }

        $totalPaid = (float)($order['paid_amount'] ?? 0) + $amount;
        $paymentStatus = $totalPaid >= (float)$order['total'] ? 'paid' : ($totalPaid > 0 ? 'partial' : 'unpaid');

        Database::update('purchase_orders', [
            'paid_amount' => $totalPaid,
            'payment_status' => $paymentStatus,
        ], 'id = ?', [$id]);

        Database::insert('activities', [
            'type' => 'system',
            'title' => "Thanh toán " . format_money($amount) . " cho đơn mua {$order['order_code']}",
            'user_id' => $this->userId(),
        ]);

        $this->setFlash('success', 'Đã ghi nhận thanh toán ' . format_money($amount) . '.');
        return $this->redirect('purchase-orders/' . $id);
    }
}
