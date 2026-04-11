<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Services\PluginManager;

class LogisticsController extends Controller
{
    private function checkPlugin(): bool
    {
        try {
            $installed = PluginManager::getInstalled($this->tenantId());
            foreach ($installed as $p) {
                if ($p['slug'] === 'kho-logistics' && $p['tenant_active']) return true;
            }
        } catch (\Exception $e) {}
        $this->setFlash('error', 'Plugin Kho Logistics chưa được cài đặt.');
        $this->redirect('plugins/marketplace');
        return false;
    }

    // ---- Dashboard ----
    public function index()
    {
        if (!$this->checkPlugin()) return;
        $tid = Database::tenantId();

        $stats = [
            'total' => (int)(Database::fetch("SELECT COUNT(*) as c FROM logistics_packages WHERE tenant_id = ?", [$tid])['c'] ?? 0),
            'warehouse_vn' => (int)(Database::fetch("SELECT COUNT(*) as c FROM logistics_packages WHERE tenant_id = ? AND status = 'warehouse_vn'", [$tid])['c'] ?? 0),
            'shipping' => (int)(Database::fetch("SELECT COUNT(*) as c FROM logistics_packages WHERE tenant_id = ? AND status = 'shipping'", [$tid])['c'] ?? 0),
            'delivered' => (int)(Database::fetch("SELECT COUNT(*) as c FROM logistics_packages WHERE tenant_id = ? AND status = 'delivered'", [$tid])['c'] ?? 0),
            'pending' => (int)(Database::fetch("SELECT COUNT(*) as c FROM logistics_packages WHERE tenant_id = ? AND status IN ('pending','warehouse_cn','packed')", [$tid])['c'] ?? 0),
        ];

        $recentPackages = Database::fetchAll(
            "SELECT lp.*, u.name as received_by_name FROM logistics_packages lp LEFT JOIN users u ON lp.received_by = u.id WHERE lp.tenant_id = ? ORDER BY lp.updated_at DESC LIMIT 20",
            [$tid]
        );

        return $this->view('logistics.index', ['stats' => $stats, 'recentPackages' => $recentPackages]);
    }

    // ---- Warehouse Receive (Nhập kho - Quét mã) ----
    public function receive()
    {
        if (!$this->checkPlugin()) return;
        $tid = Database::tenantId();

        // Today's scan stats
        $todayStats = Database::fetch(
            "SELECT COUNT(*) as total, SUM(result='success') as success, SUM(result='error') as error, SUM(result='duplicate') as duplicate
             FROM logistics_scan_logs WHERE tenant_id = ? AND DATE(created_at) = CURDATE() AND scanned_by = ?",
            [$tid, $this->userId()]
        );

        // Recent scan history (this session)
        $scanHistory = Database::fetchAll(
            "SELECT sl.*, lp.product_name, lp.customer_name FROM logistics_scan_logs sl LEFT JOIN logistics_packages lp ON sl.package_id = lp.id
             WHERE sl.tenant_id = ? AND sl.scanned_by = ? AND DATE(sl.created_at) = CURDATE()
             ORDER BY sl.created_at DESC LIMIT 50",
            [$tid, $this->userId()]
        );

        return $this->view('logistics.receive', [
            'todayStats' => $todayStats,
            'scanHistory' => $scanHistory,
        ]);
    }

    // ---- Scan Barcode (AJAX) ----
    public function scan()
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);
        if (!$this->checkPlugin()) return $this->json(['error' => 'Plugin chưa cài'], 403);

        $tid = Database::tenantId();
        $uid = $this->userId();
        $barcode = trim($this->input('barcode') ?? '');

        if (empty($barcode)) return $this->json(['error' => 'Mã quét trống'], 422);

        // Check if it's a bag code
        if (str_starts_with(strtoupper($barcode), 'BAO-') || str_starts_with(strtoupper($barcode), 'BAG-')) {
            return $this->scanBag($barcode, $tid, $uid);
        }

        // Check if it's a wholesale order code (DH prefix)
        if (str_starts_with(strtoupper($barcode), 'DH')) {
            $order = Database::fetch("SELECT * FROM logistics_orders WHERE tenant_id = ? AND order_code = ? AND type = 'wholesale'", [$tid, $barcode]);
            if ($order) {
                $this->logScan($tid, $barcode, 'package', 'success', null, null, "Đơn sỉ: {$order['received_packages']}/{$order['total_packages']} kiện", $uid);
                return $this->json([
                    'success' => true,
                    'type' => 'wholesale',
                    'message' => "Đơn sỉ {$barcode}: đã nhận {$order['received_packages']}/{$order['total_packages']} kiện",
                    'order' => $order,
                    'need_confirm' => true,
                ]);
            }
        }

        // Find package by code or tracking
        $pkg = Database::fetch(
            "SELECT * FROM logistics_packages WHERE tenant_id = ? AND (package_code = ? OR tracking_code = ? OR tracking_intl = ?)",
            [$tid, $barcode, $barcode, $barcode]
        );

        if (!$pkg) {
            // Auto-create package if not found
            $pkgCode = $this->generateCode('K');
            $pkgId = Database::insert('logistics_packages', [
                'tenant_id' => $tid,
                'package_code' => $pkgCode,
                'tracking_code' => $barcode,
                'status' => 'warehouse_vn',
                'received_by' => $uid,
                'received_at' => date('Y-m-d H:i:s'),
                'created_by' => $uid,
            ]);

            $this->logStatus($pkgId, null, 'warehouse_vn', 'Tự tạo khi quét mã', $uid);
            $this->logScan($tid, $barcode, 'package', 'success', $pkgId, null, 'Tạo mới + nhập kho', $uid);

            return $this->json([
                'success' => true,
                'type' => 'new',
                'message' => 'Tạo kiện mới + nhập kho thành công',
                'package' => ['id' => $pkgId, 'code' => $pkgCode, 'tracking' => $barcode, 'status' => 'warehouse_vn'],
                'need_weight' => true,
            ]);
        }

        // Check if already received
        if (in_array($pkg['status'], ['warehouse_vn', 'delivering', 'delivered'])) {
            $this->logScan($tid, $barcode, 'package', 'duplicate', $pkg['id'], null, 'Đã nhập kho trước đó', $uid);
            return $this->json([
                'success' => false,
                'type' => 'duplicate',
                'message' => 'Kiện hàng đã nhập kho trước đó (' . date('d/m H:i', strtotime($pkg['received_at'] ?? $pkg['updated_at'])) . ')',
                'package' => $pkg,
            ]);
        }

        // Update status to warehouse_vn
        $oldStatus = $pkg['status'];
        Database::update('logistics_packages', [
            'status' => 'warehouse_vn',
            'received_by' => $uid,
            'received_at' => date('Y-m-d H:i:s'),
        ], 'id = ? AND tenant_id = ?', [$pkg['id'], $tid]);

        $this->logStatus($pkg['id'], $oldStatus, 'warehouse_vn', 'Quét mã nhập kho', $uid);
        $this->logScan($tid, $barcode, 'package', 'success', $pkg['id'], null, 'Nhập kho thành công', $uid);

        $pkg['status'] = 'warehouse_vn';
        return $this->json([
            'success' => true,
            'type' => 'receive',
            'message' => 'Nhập kho thành công',
            'package' => $pkg,
            'need_weight' => empty($pkg['weight_actual']),
        ]);
    }

    // ---- Update weight (AJAX) ----
    public function updateWeight()
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);

        $pkgId = (int)$this->input('package_id');
        $weight = (float)$this->input('weight');
        if ($weight <= 0) return $this->json(['error' => 'Cân nặng phải > 0'], 422);

        $length = (float)($this->input('length') ?: 0);
        $width = (float)($this->input('width') ?: 0);
        $height = (float)($this->input('height') ?: 0);

        $update = ['weight_actual' => $weight];
        if ($length > 0) $update['length_cm'] = $length;
        if ($width > 0) $update['width_cm'] = $width;
        if ($height > 0) $update['height_cm'] = $height;
        if ($length > 0 && $width > 0 && $height > 0) {
            $update['weight_volume'] = round($length * $width * $height / 5000, 2);
            $update['cbm'] = round($length * $width * $height / 1000000, 4);
        }

        Database::update('logistics_packages', $update, 'id = ? AND tenant_id = ?', [$pkgId, Database::tenantId()]);
        return $this->json(['success' => true]);
    }

    // ---- Scan bag ----
    private function scanBag(string $barcode, int $tid, int $uid): array
    {
        $bag = Database::fetch("SELECT * FROM logistics_bags WHERE tenant_id = ? AND bag_code = ?", [$tid, $barcode]);

        if (!$bag) {
            $this->logScan($tid, $barcode, 'bag', 'error', null, null, 'Bao không tồn tại', $uid);
            return $this->json(['success' => false, 'type' => 'error', 'message' => 'Bao hàng không tồn tại: ' . $barcode]);
        }

        // Get packages in this bag
        $packages = Database::fetchAll(
            "SELECT * FROM logistics_packages WHERE tenant_id = ? AND bag_id = ?",
            [$tid, $bag['id']]
        );

        $received = 0;
        foreach ($packages as $p) {
            if (in_array($p['status'], ['warehouse_vn', 'delivering', 'delivered'])) { $received++; continue; }

            Database::update('logistics_packages', [
                'status' => 'warehouse_vn', 'received_by' => $uid, 'received_at' => date('Y-m-d H:i:s'),
            ], 'id = ? AND tenant_id = ?', [$p['id'], $tid]);
            $this->logStatus($p['id'], $p['status'], 'warehouse_vn', 'Nhập kho qua bao ' . $barcode, $uid);
            $received++;
        }

        Database::update('logistics_bags', ['status' => 'arrived'], 'id = ? AND tenant_id = ?', [$bag['id'], $tid]);
        $this->logScan($tid, $barcode, 'bag', 'success', null, $bag['id'], "Nhập {$received}/" . count($packages) . " kiện", $uid);

        return $this->json([
            'success' => true,
            'type' => 'bag',
            'message' => "Bao {$barcode}: nhập {$received}/" . count($packages) . " kiện",
            'bag' => $bag,
            'packages' => $packages,
        ]);
    }

    // ---- Package list ----
    public function packages()
    {
        if (!$this->checkPlugin()) return;
        $tid = Database::tenantId();
        $status = $this->input('status');
        $search = $this->input('search');
        $page = max(1, (int)($this->input('page') ?: 1));
        $perPage = 20;

        $where = ["lp.tenant_id = ?"];
        $params = [$tid];
        if ($status) { $where[] = "lp.status = ?"; $params[] = $status; }
        if ($search) { $where[] = "(lp.package_code LIKE ? OR lp.tracking_code LIKE ? OR lp.tracking_intl LIKE ? OR lp.customer_name LIKE ? OR lp.product_name LIKE ?)"; $s = "%{$search}%"; $params = array_merge($params, [$s,$s,$s,$s,$s]); }

        $wc = implode(' AND ', $where);
        $total = (int)(Database::fetch("SELECT COUNT(*) as c FROM logistics_packages lp WHERE {$wc}", $params)['c'] ?? 0);
        $offset = ($page - 1) * $perPage;

        $packages = Database::fetchAll(
            "SELECT lp.*, u.name as received_by_name, cb.name as created_by_name
             FROM logistics_packages lp LEFT JOIN users u ON lp.received_by = u.id LEFT JOIN users cb ON lp.created_by = cb.id
             WHERE {$wc} ORDER BY lp.updated_at DESC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset,
            $params
        );

        $statusCounts = Database::fetchAll("SELECT status, COUNT(*) as count FROM logistics_packages WHERE tenant_id = ? GROUP BY status", [$tid]);

        return $this->view('logistics.packages', [
            'packages' => $packages,
            'statusCounts' => $statusCounts,
            'filters' => ['status' => $status, 'search' => $search],
            'pagination' => ['total' => $total, 'page' => $page, 'total_pages' => ceil($total / $perPage)],
        ]);
    }

    // ---- Create package ----
    public function createPackage()
    {
        if (!$this->isPost()) return $this->redirect('logistics/packages');
        $tid = Database::tenantId();

        $code = $this->input('package_code') ?: $this->generateCode('K');

        Database::insert('logistics_packages', [
            'tenant_id' => $tid,
            'package_code' => $code,
            'tracking_code' => trim($this->input('tracking_code') ?? '') ?: null,
            'tracking_intl' => trim($this->input('tracking_intl') ?? '') ?: null,
            'customer_name' => trim($this->input('customer_name') ?? '') ?: null,
            'customer_phone' => trim($this->input('customer_phone') ?? '') ?: null,
            'product_name' => trim($this->input('product_name') ?? '') ?: null,
            'description' => trim($this->input('description') ?? '') ?: null,
            'weight_actual' => (float)($this->input('weight_actual') ?: 0) ?: null,
            'quantity' => (int)($this->input('quantity') ?: 1),
            'status' => $this->input('status') ?: 'pending',
            'created_by' => $this->userId(),
        ]);

        $this->setFlash('success', 'Đã tạo kiện hàng ' . $code);
        return $this->redirect('logistics/packages');
    }

    // ---- Package detail ----
    public function showPackage($id)
    {
        if (!$this->checkPlugin()) return;
        $pkg = Database::fetch(
            "SELECT lp.*, u.name as received_by_name, cb.name as created_by_name
             FROM logistics_packages lp LEFT JOIN users u ON lp.received_by = u.id LEFT JOIN users cb ON lp.created_by = cb.id
             WHERE lp.id = ? AND lp.tenant_id = ?",
            [(int)$id, Database::tenantId()]
        );
        if (!$pkg) { $this->setFlash('error', 'Kiện hàng không tồn tại.'); return $this->redirect('logistics/packages'); }

        $history = Database::fetchAll(
            "SELECT lsh.*, u.name as changed_by_name FROM logistics_status_history lsh LEFT JOIN users u ON lsh.changed_by = u.id WHERE lsh.package_id = ? ORDER BY lsh.created_at DESC",
            [(int)$id]
        );

        return $this->view('logistics.package-show', ['package' => $pkg, 'history' => $history]);
    }

    // ---- Bags ----
    public function bags()
    {
        if (!$this->checkPlugin()) return;
        $bags = Database::fetchAll(
            "SELECT lb.*, u.name as created_by_name,
                    (SELECT COUNT(*) FROM logistics_packages WHERE bag_id = lb.id) as pkg_count
             FROM logistics_bags lb LEFT JOIN users u ON lb.created_by = u.id
             WHERE lb.tenant_id = ? ORDER BY lb.created_at DESC",
            [Database::tenantId()]
        );
        return $this->view('logistics.bags', ['bags' => $bags]);
    }

    public function createBag()
    {
        if (!$this->isPost()) return $this->redirect('logistics/bags');
        $code = $this->input('bag_code') ?: 'BAO-' . date('ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        Database::insert('logistics_bags', [
            'tenant_id' => Database::tenantId(),
            'bag_code' => $code,
            'note' => trim($this->input('note') ?? '') ?: null,
            'created_by' => $this->userId(),
        ]);

        $this->setFlash('success', 'Đã tạo bao ' . $code);
        return $this->redirect('logistics/bags');
    }

    // ---- Orders ----
    public function orders()
    {
        if (!$this->checkPlugin()) return;
        $tid = Database::tenantId();
        $type = $this->input('type');
        $status = $this->input('status');
        $search = $this->input('search');
        $dateFrom = $this->input('date_from');
        $dateTo = $this->input('date_to');

        $where = ["lo.tenant_id = ?"];
        $params = [$tid];
        if ($type) { $where[] = "lo.type = ?"; $params[] = $type; }
        if ($status) { $where[] = "lo.status = ?"; $params[] = $status; }
        if ($search) {
            $where[] = "(lo.order_code LIKE ? OR lo.customer_name LIKE ? OR lo.customer_phone LIKE ? OR lo.product_name LIKE ?)";
            $s = "%{$search}%"; $params = array_merge($params, [$s,$s,$s,$s]);
        }
        if ($dateFrom) { $where[] = "lo.created_at >= ?"; $params[] = $dateFrom . ' 00:00:00'; }
        if ($dateTo) { $where[] = "lo.created_at <= ?"; $params[] = $dateTo . ' 23:59:59'; }

        $orders = Database::fetchAll(
            "SELECT lo.*, u.name as created_by_name FROM logistics_orders lo LEFT JOIN users u ON lo.created_by = u.id WHERE " . implode(' AND ', $where) . " ORDER BY lo.created_at DESC LIMIT 50",
            $params
        );

        return $this->view('logistics.orders', ['orders' => $orders, 'filters' => ['type' => $type, 'status' => $status, 'search' => $search, 'date_from' => $dateFrom, 'date_to' => $dateTo]]);
    }

    public function createOrder()
    {
        if (!$this->isPost()) return $this->redirect('logistics/orders');

        $code = $this->input('order_code') ?: 'DH' . date('ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $type = $this->input('type') ?: 'retail';

        $customerName = trim($this->input('customer_name') ?? '') ?: null;
        $customerPhone = trim($this->input('customer_phone') ?? '') ?: trim($this->input('customer_phone_display') ?? '') ?: null;
        $customerId = (int)($this->input('customer_id') ?: 0) ?: null;

        $orderId = Database::insert('logistics_orders', [
            'tenant_id' => Database::tenantId(),
            'order_code' => $code,
            'type' => $type,
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'customer_id' => $customerId,
            'product_name' => trim($this->input('product_name') ?? '') ?: null,
            'total_packages' => (int)($this->input('total_packages') ?: 0),
            'total_weight' => (float)($this->input('total_weight') ?: 0),
            'total_cbm' => (float)($this->input('total_cbm') ?: 0),
            'total_amount' => (float)($this->input('total_amount') ?: 0),
            'cod_amount' => (float)($this->input('cod_amount') ?: 0),
            'payment_method' => trim($this->input('payment_method') ?? '') ?: null,
            'note' => trim($this->input('note') ?? '') ?: null,
            'created_by' => $this->userId(),
        ]);

        // Handle images
        if (!empty($_FILES['images']['name'][0])) {
            $uploadDir = BASE_PATH . '/public/uploads/logistics/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $images = [];
            foreach ($_FILES['images']['name'] as $i => $name) {
                if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) continue;
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $filename = 'order_' . $orderId . '_' . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['images']['tmp_name'][$i], $uploadDir . $filename);
                $images[] = $filename;
            }
            if (!empty($images)) {
                Database::update('logistics_orders', ['images' => json_encode($images)], 'id = ?', [$orderId]);
            }
        }

        $this->setFlash('success', 'Đã tạo đơn ' . $code);
        return $this->redirect('logistics/orders');
    }

    public function showOrder($id)
    {
        if (!$this->checkPlugin()) return;
        $tid = Database::tenantId();

        $order = Database::fetch(
            "SELECT lo.*, u.name as created_by_name FROM logistics_orders lo LEFT JOIN users u ON lo.created_by = u.id WHERE lo.id = ? AND lo.tenant_id = ?",
            [(int)$id, $tid]
        );
        if (!$order) { $this->setFlash('error', 'Đơn hàng không tồn tại.'); return $this->redirect('logistics/orders'); }

        // Packages linked to this order
        $packages = Database::fetchAll(
            "SELECT lp.*, u.name as received_by_name FROM logistics_packages lp LEFT JOIN users u ON lp.received_by = u.id WHERE lp.order_id = ? AND lp.tenant_id = ? ORDER BY lp.created_at",
            [(int)$id, $tid]
        );

        // Also find by customer match if no direct link
        if (empty($packages) && $order['customer_name']) {
            $packages = Database::fetchAll(
                "SELECT lp.*, u.name as received_by_name FROM logistics_packages lp LEFT JOIN users u ON lp.received_by = u.id WHERE lp.customer_name = ? AND lp.tenant_id = ? ORDER BY lp.created_at",
                [$order['customer_name'], $tid]
            );
        }

        // Scan history for this order
        $scanLogs = Database::fetchAll(
            "SELECT sl.* FROM logistics_scan_logs sl WHERE sl.tenant_id = ? AND sl.scan_code = ? ORDER BY sl.created_at DESC LIMIT 20",
            [$tid, $order['order_code']]
        );

        return $this->view('logistics.order-show', ['order' => $order, 'packages' => $packages, 'scanLogs' => $scanLogs]);
    }

    public function updateOrder($id)
    {
        if (!$this->isPost()) return $this->redirect('logistics/orders/' . $id);

        $order = Database::fetch("SELECT id FROM logistics_orders WHERE id = ? AND tenant_id = ?", [(int)$id, Database::tenantId()]);
        if (!$order) { $this->setFlash('error', 'Đơn hàng không tồn tại.'); return $this->redirect('logistics/orders'); }

        Database::update('logistics_orders', [
            'customer_name' => trim($this->input('customer_name') ?? '') ?: null,
            'customer_phone' => trim($this->input('customer_phone') ?? '') ?: null,
            'type' => $this->input('type') ?: 'retail',
            'product_name' => trim($this->input('product_name') ?? '') ?: null,
            'total_packages' => (int)($this->input('total_packages') ?: 0),
            'total_weight' => (float)($this->input('total_weight') ?: 0),
            'total_cbm' => (float)($this->input('total_cbm') ?: 0),
            'total_amount' => (float)($this->input('total_amount') ?: 0),
            'cod_amount' => (float)($this->input('cod_amount') ?: 0),
            'payment_method' => trim($this->input('payment_method') ?? '') ?: null,
            'status' => $this->input('status') ?: 'pending',
            'note' => trim($this->input('note') ?? '') ?: null,
        ], 'id = ?', [(int)$id]);

        $this->setFlash('success', 'Đã cập nhật đơn hàng.');
        return $this->redirect('logistics/orders/' . $id);
    }

    public function deleteOrder($id)
    {
        if (!$this->isPost()) return $this->redirect('logistics/orders');
        Database::delete('logistics_orders', 'id = ? AND tenant_id = ?', [(int)$id, Database::tenantId()]);
        $this->setFlash('success', 'Đã xóa đơn hàng.');
        return $this->redirect('logistics/orders');
    }

    public function uploadOrderImage($id)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);
        $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        if (!empty($_FILES['image']) && !in_array($_FILES['image']['type'], $allowed)) return $this->json(['error' => 'Chỉ chấp nhận JPG, PNG, GIF, WebP'], 422);

        $order = Database::fetch("SELECT id, images FROM logistics_orders WHERE id = ? AND tenant_id = ?", [(int)$id, Database::tenantId()]);
        if (!$order) return $this->json(['error' => 'Không tồn tại'], 404);

        if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            return $this->json(['error' => 'Không có file'], 422);
        }

        $file = $_FILES['image'];
        if ($file['size'] > 10 * 1024 * 1024) return $this->json(['error' => 'File quá lớn'], 422);

        $uploadDir = BASE_PATH . '/public/uploads/logistics/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'order_' . $id . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($file['tmp_name'], $uploadDir . $filename);

        $images = json_decode($order['images'] ?? '[]', true) ?: [];
        $images[] = $filename;

        Database::update('logistics_orders', ['images' => json_encode($images)], 'id = ?', [(int)$id]);

        return $this->json(['success' => true, 'url' => url('uploads/logistics/' . $filename), 'filename' => $filename]);
    }

    // ---- Confirm wholesale (AJAX) ----
    public function confirmWholesale()
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);

        $orderId = (int)$this->input('order_id');
        $receivedCount = (int)$this->input('received_count');
        $note = trim($this->input('note') ?? '');

        $order = Database::fetch("SELECT * FROM logistics_orders WHERE id = ? AND tenant_id = ?", [$orderId, Database::tenantId()]);
        if (!$order) return $this->json(['error' => 'Đơn hàng không tồn tại'], 404);

        $newReceived = ($order['received_packages'] ?? 0) + $receivedCount;
        $newStatus = $newReceived >= $order['total_packages'] ? 'completed' : 'partial';

        Database::update('logistics_orders', [
            'received_packages' => $newReceived,
            'status' => $newStatus,
        ], 'id = ?', [$orderId]);

        $this->logScan(Database::tenantId(), $order['order_code'], 'package', 'success', null, null,
            "Nhận {$receivedCount} kiện (tổng {$newReceived}/{$order['total_packages']})" . ($note ? " - {$note}" : ''),
            $this->userId()
        );

        return $this->json([
            'success' => true,
            'message' => "Đã nhận {$receivedCount} kiện cho đơn {$order['order_code']} ({$newReceived}/{$order['total_packages']})",
            'order' => array_merge($order, ['received_packages' => $newReceived, 'status' => $newStatus]),
        ]);
    }

    // ---- Helpers ----
    private function generateCode(string $prefix): string
    {
        return $prefix . date('ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    private function logStatus(int $pkgId, ?string $old, string $new, ?string $note, int $uid): void
    {
        Database::query("INSERT INTO logistics_status_history (package_id, old_status, new_status, note, changed_by) VALUES (?,?,?,?,?)", [$pkgId, $old, $new, $note, $uid]);
    }

    private function logScan(int $tid, string $code, string $type, string $result, ?int $pkgId, ?int $bagId, ?string $msg, int $uid): void
    {
        Database::query("INSERT INTO logistics_scan_logs (tenant_id, scan_code, scan_type, result, package_id, bag_id, message, scanned_by) VALUES (?,?,?,?,?,?,?,?)", [$tid, $code, $type, $result, $pkgId, $bagId, $msg, $uid]);
    }
}
