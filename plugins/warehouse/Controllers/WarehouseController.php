<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class WarehouseController extends Controller
{
    // ---- Warehouses ----
    public function index()
    {
        $this->authorize('logistics', 'view');
        $tid = Database::tenantId();
        $warehouses = Database::fetchAll(
            "SELECT w.*, u.name as manager_name,
                    (SELECT COUNT(DISTINCT product_id) FROM stock WHERE warehouse_id = w.id AND quantity > 0) as product_count,
                    (SELECT COALESCE(SUM(quantity), 0) FROM stock WHERE warehouse_id = w.id) as total_stock
             FROM warehouses w LEFT JOIN users u ON w.manager_id = u.id
             WHERE w.tenant_id = ? ORDER BY w.is_default DESC, w.name",
            [$tid]
        );

        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.tenant_id = ? AND u.is_active = 1 ORDER BY d.name, u.name", [$tid]);

        return $this->view('plugin:warehouse.index', ['warehouses' => $warehouses, 'users' => $users]);
    }

    public function store()
    {
        if (!$this->isPost()) return $this->redirect('warehouses');
        $this->authorize('logistics', 'create');

        $name = trim($this->input('name') ?? '');
        if (empty($name)) { $this->setFlash('error', 'Tên kho không được để trống.'); return $this->back(); }

        Database::insert('warehouses', [
            'name' => $name,
            'code' => trim($this->input('code') ?? '') ?: null,
            'address' => trim($this->input('address') ?? '') ?: null,
            'phone' => trim($this->input('phone') ?? '') ?: null,
            'manager_id' => $this->input('manager_id') ?: null,
            'description' => trim($this->input('description') ?? '') ?: null,
        ]);

        $this->setFlash('success', 'Đã tạo kho "' . $name . '".');
        return $this->redirect('warehouses');
    }

    public function show($id)
    {
        $this->authorize('logistics', 'view');
        $tid = Database::tenantId();
        $warehouse = Database::fetch(
            "SELECT w.*, u.name as manager_name FROM warehouses w LEFT JOIN users u ON w.manager_id = u.id WHERE w.id = ? AND w.tenant_id = ?",
            [(int)$id, $tid]
        );
        if (!$warehouse) { $this->setFlash('error', 'Kho không tồn tại.'); return $this->redirect('warehouses'); }

        // Stock list
        $stocks = Database::fetchAll(
            "SELECT s.*, p.name as product_name, p.sku, p.unit, p.price
             FROM stock s JOIN products p ON s.product_id = p.id
             WHERE s.warehouse_id = ? ORDER BY p.name",
            [(int)$id]
        );

        // Recent movements
        $movements = Database::fetchAll(
            "SELECT sm.*, u.name as created_by_name,
                    (SELECT SUM(quantity) FROM stock_movement_items WHERE movement_id = sm.id) as total_qty,
                    (SELECT COUNT(*) FROM stock_movement_items WHERE movement_id = sm.id) as item_count
             FROM stock_movements sm LEFT JOIN users u ON sm.created_by = u.id
             WHERE sm.warehouse_id = ? OR sm.to_warehouse_id = ?
             ORDER BY sm.created_at DESC LIMIT 20",
            [(int)$id, (int)$id]
        );

        // Low stock alerts
        $lowStock = Database::fetchAll(
            "SELECT s.*, p.name as product_name, p.sku
             FROM stock s JOIN products p ON s.product_id = p.id
             WHERE s.warehouse_id = ? AND s.min_quantity > 0 AND s.quantity <= s.min_quantity
             ORDER BY (s.quantity / s.min_quantity) ASC",
            [(int)$id]
        );

        return $this->view('plugin:warehouse.show', [
            'warehouse' => $warehouse,
            'stocks' => $stocks,
            'movements' => $movements,
            'lowStock' => $lowStock,
        ]);
    }

    public function update($id)
    {
        $this->authorize('logistics', 'edit');
        if (!$this->isPost()) return $this->redirect('warehouses');
        $name = trim($this->input('name') ?? '');
        if (empty($name)) { $this->setFlash('error', 'Tên kho không được để trống.'); return $this->back(); }

        Database::update('warehouses', [
            'name' => $name,
            'code' => trim($this->input('code') ?? '') ?: null,
            'address' => trim($this->input('address') ?? '') ?: null,
            'phone' => trim($this->input('phone') ?? '') ?: null,
            'manager_id' => $this->input('manager_id') ?: null,
            'description' => trim($this->input('description') ?? '') ?: null,
        ], 'id = ? AND tenant_id = ?', [(int)$id, Database::tenantId()]);

        $this->setFlash('success', 'Đã cập nhật kho.');
        return $this->redirect('warehouses/' . $id);
    }

    public function delete($id)
    {
        $this->authorize('logistics', 'edit');
        if (!$this->isPost()) return $this->redirect('warehouses');
        $stockCount = Database::fetch("SELECT COUNT(*) as c FROM stock WHERE warehouse_id = ? AND quantity > 0", [(int)$id])['c'] ?? 0;
        if ($stockCount > 0) {
            $this->setFlash('error', 'Kho đang có hàng tồn, không thể xóa.');
            return $this->back();
        }
        Database::delete('warehouses', 'id = ? AND tenant_id = ?', [(int)$id, Database::tenantId()]);
        $this->setFlash('success', 'Đã xóa kho.');
        return $this->redirect('warehouses');
    }

    // ---- Stock Movements ----
    public function movements()
    {
        $tid = Database::tenantId();
        $type = $this->input('type');
        $where = ["sm.tenant_id = ?"];
        $params = [$tid];
        if ($type) { $where[] = "sm.type = ?"; $params[] = $type; }

        $movements = Database::fetchAll(
            "SELECT sm.*, u.name as created_by_name, w.name as warehouse_name, tw.name as to_warehouse_name,
                    (SELECT SUM(quantity) FROM stock_movement_items WHERE movement_id = sm.id) as total_qty,
                    (SELECT COUNT(*) FROM stock_movement_items WHERE movement_id = sm.id) as item_count
             FROM stock_movements sm
             LEFT JOIN users u ON sm.created_by = u.id
             LEFT JOIN warehouses w ON sm.warehouse_id = w.id
             LEFT JOIN warehouses tw ON sm.to_warehouse_id = tw.id
             WHERE " . implode(' AND ', $where) . " ORDER BY sm.created_at DESC LIMIT 50",
            $params
        );

        $warehouses = Database::fetchAll("SELECT id, name FROM warehouses WHERE tenant_id = ? AND is_active = 1 ORDER BY name", [$tid]);
        $products = Database::fetchAll("SELECT id, name, sku, unit, price FROM products WHERE tenant_id = ? AND is_deleted = 0 ORDER BY name", [$tid]);

        return $this->view('plugin:warehouse.movements', [
            'movements' => $movements,
            'warehouses' => $warehouses,
            'products' => $products,
            'filters' => ['type' => $type],
        ]);
    }

    public function createMovement()
    {
        $this->authorize('logistics', 'create');
        if (!$this->isPost()) return $this->redirect('warehouses/movements');

        $type = $this->input('type');
        $warehouseId = (int)$this->input('warehouse_id');
        $toWarehouseId = $this->input('to_warehouse_id') ? (int)$this->input('to_warehouse_id') : null;
        $productIds = $this->input('product_id') ?? [];
        $quantities = $this->input('quantity') ?? [];
        $prices = $this->input('unit_price') ?? [];
        $note = trim($this->input('note') ?? '');

        if (!$warehouseId || empty($productIds)) {
            $this->setFlash('error', 'Chọn kho và ít nhất 1 sản phẩm.');
            return $this->back();
        }

        $typeLabels = ['import'=>'NK','export'=>'XK','transfer'=>'CK','adjustment'=>'DC'];
        $code = ($typeLabels[$type] ?? 'PK') . date('ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        $movementId = Database::insert('stock_movements', [
            'tenant_id' => Database::tenantId(),
            'code' => $code,
            'type' => $type,
            'warehouse_id' => $warehouseId,
            'to_warehouse_id' => $toWarehouseId,
            'reference_type' => 'manual',
            'status' => 'confirmed',
            'note' => $note ?: null,
            'created_by' => $this->userId(),
            'confirmed_by' => $this->userId(),
            'confirmed_at' => date('Y-m-d H:i:s'),
        ]);

        foreach ($productIds as $i => $pid) {
            $qty = (float)($quantities[$i] ?? 0);
            if ($qty <= 0 || !$pid) continue;

            Database::insert('stock_movement_items', [
                'movement_id' => $movementId,
                'product_id' => (int)$pid,
                'quantity' => $qty,
                'unit_price' => (float)($prices[$i] ?? 0),
            ]);

            // Update stock
            $this->updateStock($warehouseId, (int)$pid, $type === 'export' || $type === 'transfer' ? -$qty : $qty);

            // Transfer: add to destination
            if ($type === 'transfer' && $toWarehouseId) {
                $this->updateStock($toWarehouseId, (int)$pid, $qty);
            }
        }

        $typeNames = ['import'=>'nhập kho','export'=>'xuất kho','transfer'=>'chuyển kho','adjustment'=>'điều chỉnh'];
        $this->setFlash('success', 'Đã tạo phiếu ' . ($typeNames[$type] ?? $type) . ' ' . $code);
        return $this->redirect('warehouses/movements');
    }

    public function showMovement($id)
    {
        $movement = Database::fetch(
            "SELECT sm.*, u.name as created_by_name, cu.name as confirmed_by_name,
                    w.name as warehouse_name, tw.name as to_warehouse_name
             FROM stock_movements sm
             LEFT JOIN users u ON sm.created_by = u.id
             LEFT JOIN users cu ON sm.confirmed_by = cu.id
             LEFT JOIN warehouses w ON sm.warehouse_id = w.id
             LEFT JOIN warehouses tw ON sm.to_warehouse_id = tw.id
             WHERE sm.id = ? AND sm.tenant_id = ?",
            [(int)$id, Database::tenantId()]
        );
        if (!$movement) { $this->setFlash('error', 'Phiếu không tồn tại.'); return $this->redirect('warehouses/movements'); }

        $items = Database::fetchAll(
            "SELECT smi.*, p.name as product_name, p.sku, p.unit
             FROM stock_movement_items smi JOIN products p ON smi.product_id = p.id
             WHERE smi.movement_id = ?",
            [(int)$id]
        );

        return $this->view('plugin:warehouse.movement-show', ['movement' => $movement, 'items' => $items]);
    }

    // ---- Stock Check ----
    public function checks()
    {
        $checks = Database::fetchAll(
            "SELECT sc.*, w.name as warehouse_name, u.name as checked_by_name,
                    (SELECT COUNT(*) FROM stock_check_items WHERE check_id = sc.id) as item_count
             FROM stock_checks sc
             LEFT JOIN warehouses w ON sc.warehouse_id = w.id
             LEFT JOIN users u ON sc.checked_by = u.id
             WHERE sc.tenant_id = ? ORDER BY sc.created_at DESC",
            [Database::tenantId()]
        );

        $warehouses = Database::fetchAll("SELECT id, name FROM warehouses WHERE tenant_id = ? AND is_active = 1", [Database::tenantId()]);

        return $this->view('plugin:warehouse.checks', ['checks' => $checks, 'warehouses' => $warehouses]);
    }

    public function createCheck()
    {
        if (!$this->isPost()) return $this->redirect('warehouses/checks');

        $whId = (int)$this->input('warehouse_id');
        if (!$whId) { $this->setFlash('error', 'Chọn kho.'); return $this->back(); }

        $code = 'KK' . date('ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        $checkId = Database::insert('stock_checks', [
            'warehouse_id' => $whId,
            'code' => $code,
            'status' => 'draft',
            'checked_by' => $this->userId(),
        ]);

        // Pre-fill with current stock
        $stocks = Database::fetchAll("SELECT product_id, quantity FROM stock WHERE warehouse_id = ? AND quantity > 0", [$whId]);
        foreach ($stocks as $s) {
            Database::insert('stock_check_items', [
                'check_id' => $checkId,
                'product_id' => $s['product_id'],
                'system_qty' => $s['quantity'],
                'actual_qty' => $s['quantity'],
                'difference' => 0,
            ]);
        }

        $this->setFlash('success', 'Đã tạo phiếu kiểm kho ' . $code);
        return $this->redirect('warehouses/checks/' . $checkId);
    }

    public function showCheck($id)
    {
        $check = Database::fetch(
            "SELECT sc.*, w.name as warehouse_name, u.name as checked_by_name
             FROM stock_checks sc LEFT JOIN warehouses w ON sc.warehouse_id = w.id LEFT JOIN users u ON sc.checked_by = u.id
             WHERE sc.id = ? AND sc.tenant_id = ?",
            [(int)$id, Database::tenantId()]
        );
        if (!$check) { $this->setFlash('error', 'Phiếu không tồn tại.'); return $this->redirect('warehouses/checks'); }

        $items = Database::fetchAll(
            "SELECT sci.*, p.name as product_name, p.sku, p.unit
             FROM stock_check_items sci JOIN products p ON sci.product_id = p.id
             WHERE sci.check_id = ?",
            [(int)$id]
        );

        return $this->view('plugin:warehouse.check-show', ['check' => $check, 'items' => $items]);
    }

    public function updateCheck($id)
    {
        if (!$this->isPost()) return $this->redirect('warehouses/checks/' . $id);

        $itemIds = $this->input('item_id') ?? [];
        $actuals = $this->input('actual_qty') ?? [];
        $notes = $this->input('item_note') ?? [];

        foreach ($itemIds as $i => $itemId) {
            $actual = (float)($actuals[$i] ?? 0);
            $item = Database::fetch("SELECT system_qty FROM stock_check_items WHERE id = ?", [(int)$itemId]);
            $diff = $actual - ($item['system_qty'] ?? 0);

            Database::update('stock_check_items', [
                'actual_qty' => $actual,
                'difference' => $diff,
                'note' => trim($notes[$i] ?? '') ?: null,
            ], 'id = ?', [(int)$itemId]);
        }

        $this->setFlash('success', 'Đã cập nhật phiếu kiểm kho.');
        return $this->redirect('warehouses/checks/' . $id);
    }

    public function completeCheck($id)
    {
        $this->authorize('logistics', 'edit');
        if (!$this->isPost()) return $this->redirect('warehouses/checks/' . $id);

        $check = Database::fetch("SELECT * FROM stock_checks WHERE id = ? AND tenant_id = ?", [(int)$id, Database::tenantId()]);
        if (!$check || $check['status'] !== 'draft') {
            $this->setFlash('error', 'Phiếu không hợp lệ.');
            return $this->back();
        }

        // Apply differences as adjustment
        $items = Database::fetchAll("SELECT * FROM stock_check_items WHERE check_id = ? AND difference != 0", [(int)$id]);
        if (!empty($items)) {
            $movementId = Database::insert('stock_movements', [
                'tenant_id' => Database::tenantId(),
                'code' => 'DC-' . $check['code'],
                'type' => 'adjustment',
                'warehouse_id' => $check['warehouse_id'],
                'reference_type' => 'stock_check',
                'reference_id' => $id,
                'status' => 'confirmed',
                'note' => 'Điều chỉnh từ kiểm kho ' . $check['code'],
                'created_by' => $this->userId(),
                'confirmed_by' => $this->userId(),
                'confirmed_at' => date('Y-m-d H:i:s'),
            ]);

            foreach ($items as $item) {
                Database::insert('stock_movement_items', [
                    'movement_id' => $movementId,
                    'product_id' => $item['product_id'],
                    'quantity' => abs($item['difference']),
                ]);
                $this->updateStock($check['warehouse_id'], $item['product_id'], $item['difference']);
            }
        }

        Database::update('stock_checks', ['status' => 'completed', 'completed_at' => date('Y-m-d H:i:s')], 'id = ?', [(int)$id]);
        $this->setFlash('success', 'Đã hoàn thành kiểm kho.');
        return $this->redirect('warehouses/checks/' . $id);
    }

    // ---- Report ----
    public function report()
    {
        $tid = Database::tenantId();
        $whId = $this->input('warehouse_id');

        $warehouses = Database::fetchAll("SELECT id, name FROM warehouses WHERE tenant_id = ? AND is_active = 1", [$tid]);

        $where = "w.tenant_id = ?";
        $params = [$tid];
        if ($whId) { $where .= " AND s.warehouse_id = ?"; $params[] = (int)$whId; }

        $stockReport = Database::fetchAll(
            "SELECT p.id, p.name, p.sku, p.unit, p.price,
                    COALESCE(SUM(s.quantity), 0) as total_qty,
                    COALESCE(SUM(s.quantity * p.price), 0) as total_value,
                    MIN(s.min_quantity) as min_qty
             FROM products p
             LEFT JOIN stock s ON s.product_id = p.id
             LEFT JOIN warehouses w ON s.warehouse_id = w.id AND {$where}
             WHERE p.tenant_id = ? AND p.is_deleted = 0
             GROUP BY p.id, p.name, p.sku, p.unit, p.price
             ORDER BY p.name",
            array_merge($params, [$tid])
        );

        $totalValue = array_sum(array_column($stockReport, 'total_value'));
        $totalProducts = count(array_filter($stockReport, fn($r) => $r['total_qty'] > 0));
        $lowStockCount = count(array_filter($stockReport, fn($r) => $r['min_qty'] > 0 && $r['total_qty'] <= $r['min_qty']));

        return $this->view('plugin:warehouse.report', [
            'stockReport' => $stockReport,
            'warehouses' => $warehouses,
            'totalValue' => $totalValue,
            'totalProducts' => $totalProducts,
            'lowStockCount' => $lowStockCount,
            'filters' => ['warehouse_id' => $whId],
        ]);
    }

    // ---- Settings ----
    public function settings()
    {
        $tenant = Database::fetch("SELECT settings FROM tenants WHERE id = ?", [$this->tenantId()]);
        $settings = json_decode($tenant['settings'] ?? '{}', true);
        $whConfig = $settings['warehouse'] ?? [];

        return $this->view('plugin:warehouse.settings', ['config' => $whConfig]);
    }

    public function saveSettings()
    {
        if (!$this->isPost()) return $this->redirect('warehouses/settings');

        $tenant = Database::fetch("SELECT settings FROM tenants WHERE id = ?", [$this->tenantId()]);
        $settings = json_decode($tenant['settings'] ?? '{}', true);

        $settings['warehouse'] = [
            'auto_export_on_order' => $this->input('auto_export_on_order') ? true : false,
            'auto_import_on_purchase' => $this->input('auto_import_on_purchase') ? true : false,
            'show_stock_on_product' => $this->input('show_stock_on_product') ? true : false,
            'low_stock_notification' => $this->input('low_stock_notification') ? true : false,
            'default_warehouse_id' => (int)($this->input('default_warehouse_id') ?: 0),
        ];

        Database::update('tenants', ['settings' => json_encode($settings)], 'id = ?', [$this->tenantId()]);
        $this->setFlash('success', 'Đã lưu cài đặt kho.');
        return $this->redirect('warehouses/settings');
    }

    /**
     * Check if a warehouse integration feature is enabled
     */
    public static function isEnabled(string $feature): bool
    {
        try {
            $tenant = Database::fetch("SELECT settings FROM tenants WHERE id = ?", [Database::tenantId()]);
            $settings = json_decode($tenant['settings'] ?? '{}', true);
            return (bool)($settings['warehouse'][$feature] ?? false);
        } catch (\Exception $e) {
            return false;
        }
    }

    // ---- Helpers ----
    private function updateStock(int $warehouseId, int $productId, float $qty): void
    {
        $existing = Database::fetch("SELECT id, quantity FROM stock WHERE warehouse_id = ? AND product_id = ?", [$warehouseId, $productId]);
        if ($existing) {
            Database::update('stock', ['quantity' => (float)$existing['quantity'] + $qty], 'id = ?', [$existing['id']]);
        } else {
            Database::query("INSERT INTO stock (warehouse_id, product_id, quantity) VALUES (?, ?, ?)", [$warehouseId, $productId, max(0, $qty)]);
        }
    }
}
