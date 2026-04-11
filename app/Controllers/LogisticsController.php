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
        $whLocation = $this->input('warehouse_location') ?: 'vn';
        $targetStatus = ($whLocation === 'cn') ? 'warehouse_cn' : 'warehouse_vn';

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
                'status' => $targetStatus,
                'warehouse_location' => $whLocation,
                'received_by' => $uid,
                'received_at' => date('Y-m-d H:i:s'),
                'created_by' => $uid,
            ]);

            $whLabel = $whLocation === 'cn' ? 'Kho TQ' : 'Kho VN';
            $this->logStatus($pkgId, null, $targetStatus, 'Tự tạo khi quét mã - ' . $whLabel, $uid);
            $this->logScan($tid, $barcode, 'package', 'success', $pkgId, null, 'Tạo mới + nhập ' . $whLabel, $uid);

            return $this->json([
                'success' => true,
                'type' => 'new',
                'message' => 'Tạo kiện mới + nhập ' . $whLabel . ' thành công',
                'package' => ['id' => $pkgId, 'code' => $pkgCode, 'tracking' => $barcode, 'status' => $targetStatus],
                'need_weight' => true,
            ]);
        }

        // Validate status transition
        $allowedForCn = ['pending']; // Only pending can enter CN warehouse
        $allowedForVn = ['packed','shipping','warehouse_cn']; // Only packed/shipping/CN-warehouse can enter VN
        $alreadyReceived = ($whLocation === 'cn' && !in_array($pkg['status'], $allowedForCn))
            || ($whLocation === 'vn' && !in_array($pkg['status'], $allowedForVn));
        if ($alreadyReceived) {
            $this->logScan($tid, $barcode, 'package', 'duplicate', $pkg['id'], null, 'Đã nhập kho trước đó', $uid);
            return $this->json([
                'success' => false,
                'type' => 'duplicate',
                'message' => 'Kiện hàng đã nhập kho trước đó (' . date('d/m H:i', strtotime($pkg['received_at'] ?? $pkg['updated_at'])) . ')',
                'package' => $pkg,
            ]);
        }

        // Update status
        $oldStatus = $pkg['status'];
        $whLabel = $whLocation === 'cn' ? 'Kho TQ' : 'Kho VN';
        Database::update('logistics_packages', [
            'status' => $targetStatus,
            'warehouse_location' => $whLocation,
            'received_by' => $uid,
            'received_at' => date('Y-m-d H:i:s'),
        ], 'id = ? AND tenant_id = ?', [$pkg['id'], $tid]);

        $this->logStatus($pkg['id'], $oldStatus, $targetStatus, 'Quét mã nhập ' . $whLabel, $uid);
        $this->logScan($tid, $barcode, 'package', 'success', $pkg['id'], null, 'Nhập ' . $whLabel . ' thành công', $uid);

        // Auto-complete bag/shipment
        if ($pkg['bag_id']) self::checkAutoComplete((int)$pkg['bag_id']);
        if ($pkg['shipment_id']) self::checkShipmentAutoArrival((int)$pkg['shipment_id']);

        $pkg['status'] = $targetStatus;
        return $this->json([
            'success' => true,
            'type' => 'receive',
            'message' => 'Nhập ' . $whLabel . ' thành công',
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
        $skipped = 0;
        foreach ($packages as $p) {
            if (in_array($p['status'], ['warehouse_vn', 'delivered'])) { $received++; $skipped++; continue; }

            // Only accept packages in valid states for VN receive
            if (!in_array($p['status'], ['packed', 'shipping', 'warehouse_cn'])) { $skipped++; continue; }

            Database::update('logistics_packages', [
                'status' => 'warehouse_vn', 'warehouse_location' => 'vn', 'received_by' => $uid, 'received_at' => date('Y-m-d H:i:s'),
            ], 'id = ? AND tenant_id = ?', [$p['id'], $tid]);
            $this->logStatus($p['id'], $p['status'], 'warehouse_vn', 'Nhập kho qua bao ' . $barcode, $uid);
            $received++;
        }

        Database::update('logistics_bags', ['status' => 'arrived'], 'id = ? AND tenant_id = ?', [$bag['id'], $tid]);
        self::checkAutoComplete((int)$bag['id']);
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
            'status' => 'open',
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

    // ---- Shipments ----
    public function shipments()
    {
        if (!$this->checkPlugin()) return;
        $tid = Database::tenantId();
        $status = $this->input('status');

        $where = ["ls.tenant_id = ?"];
        $params = [$tid];
        if ($status) { $where[] = "ls.status = ?"; $params[] = $status; }

        $shipments = Database::fetchAll(
            "SELECT ls.*, u.name as created_by_name FROM logistics_shipments ls LEFT JOIN users u ON ls.created_by = u.id WHERE " . implode(' AND ', $where) . " ORDER BY ls.created_at DESC",
            $params
        );

        return $this->view('logistics.shipments', ['shipments' => $shipments, 'filters' => ['status' => $status]]);
    }

    public function createShipment()
    {
        if (!$this->isPost()) return $this->redirect('logistics/shipments');
        $code = $this->input('shipment_code') ?: 'LH' . date('ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        Database::insert('logistics_shipments', [
            'tenant_id' => Database::tenantId(),
            'shipment_code' => $code,
            'origin' => $this->input('origin') ?: 'CN',
            'destination' => $this->input('destination') ?: 'VN',
            'vehicle_info' => trim($this->input('vehicle_info') ?? '') ?: null,
            'status' => 'preparing',
            'note' => trim($this->input('note') ?? '') ?: null,
            'created_by' => $this->userId(),
        ]);

        $this->setFlash('success', 'Đã tạo lô hàng ' . $code);
        return $this->redirect('logistics/shipments');
    }

    public function createShipmentFromOrders()
    {
        if (!$this->isPost()) return $this->redirect('logistics/orders');
        $tid = Database::tenantId();
        $orderIds = $this->input('order_ids') ?? [];
        if (empty($orderIds)) { $this->setFlash('error', 'Chưa chọn đơn hàng.'); return $this->back(); }

        $code = $this->input('shipment_code') ?: 'LH' . date('ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        $shipmentId = Database::insert('logistics_shipments', [
            'tenant_id' => $tid,
            'shipment_code' => $code,
            'origin' => $this->input('origin') ?: 'CN',
            'destination' => $this->input('destination') ?: 'VN',
            'vehicle_info' => trim($this->input('vehicle_info') ?? '') ?: null,
            'status' => 'preparing',
            'note' => trim($this->input('note') ?? '') ?: null,
            'created_by' => $this->userId(),
        ]);

        // Link packages from selected orders to shipment
        $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
        $pkgs = Database::fetchAll(
            "SELECT id FROM logistics_packages WHERE tenant_id = ? AND order_id IN ({$placeholders})",
            array_merge([$tid], $orderIds)
        );
        foreach ($pkgs as $p) {
            Database::update('logistics_packages', ['shipment_id' => $shipmentId, 'status' => 'shipping'], 'id = ?', [$p['id']]);
        }

        // Also link packages by customer_name match
        $orders = Database::fetchAll("SELECT id, customer_name FROM logistics_orders WHERE id IN ({$placeholders})", $orderIds);
        foreach ($orders as $o) {
            if (!$o['customer_name']) continue;
            $morePkgs = Database::fetchAll(
                "SELECT id FROM logistics_packages WHERE tenant_id = ? AND customer_name = ? AND shipment_id IS NULL AND status IN ('warehouse_cn','packed')",
                [$tid, $o['customer_name']]
            );
            foreach ($morePkgs as $mp) {
                Database::update('logistics_packages', ['shipment_id' => $shipmentId, 'status' => 'shipping'], 'id = ?', [$mp['id']]);
            }
        }

        $this->recalcShipment($shipmentId);

        $this->setFlash('success', 'Đã tạo lô ' . $code . ' với ' . count($orderIds) . ' đơn hàng');
        return $this->redirect('logistics/shipments/' . $shipmentId);
    }

    public function showShipment($id)
    {
        if (!$this->checkPlugin()) return;
        $tid = Database::tenantId();
        $shipment = Database::fetch(
            "SELECT ls.*, u.name as created_by_name FROM logistics_shipments ls LEFT JOIN users u ON ls.created_by = u.id WHERE ls.id = ? AND ls.tenant_id = ?",
            [(int)$id, $tid]
        );
        if (!$shipment) { $this->setFlash('error', 'Lô hàng không tồn tại.'); return $this->redirect('logistics/shipments'); }

        $packages = Database::fetchAll(
            "SELECT lp.* FROM logistics_packages lp WHERE lp.shipment_id = ? AND lp.tenant_id = ? ORDER BY lp.created_at",
            [(int)$id, $tid]
        );

        $bags = Database::fetchAll(
            "SELECT lb.*, (SELECT COUNT(*) FROM logistics_packages WHERE bag_id = lb.id) as pkg_count FROM logistics_bags lb WHERE lb.shipment_id = ? AND lb.tenant_id = ?",
            [(int)$id, $tid]
        );

        return $this->view('logistics.shipment-show', ['shipment' => $shipment, 'packages' => $packages, 'bags' => $bags]);
    }

    public function updateShipmentStatus($id)
    {
        if (!$this->isPost()) return $this->redirect('logistics/shipments/' . $id);
        $tid = Database::tenantId();
        $newStatus = $this->input('status');
        $validStatuses = ['preparing','in_transit','arrived','completed','cancelled'];
        if (!in_array($newStatus, $validStatuses)) { $this->setFlash('error', 'Trạng thái không hợp lệ.'); return $this->back(); }

        $update = ['status' => $newStatus];
        if ($newStatus === 'in_transit') $update['departed_at'] = date('Y-m-d H:i:s');
        if ($newStatus === 'arrived') $update['arrived_at'] = date('Y-m-d H:i:s');
        if ($newStatus === 'completed') $update['completed_at'] = date('Y-m-d H:i:s');

        Database::update('logistics_shipments', $update, 'id = ? AND tenant_id = ?', [(int)$id, $tid]);

        // Auto-receive packages when shipment arrived
        if ($newStatus === 'arrived') {
            $pkgs = Database::fetchAll("SELECT id, status FROM logistics_packages WHERE shipment_id = ? AND tenant_id = ? AND status IN ('packed','shipping')", [(int)$id, $tid]);
            foreach ($pkgs as $p) {
                Database::update('logistics_packages', ['status' => 'warehouse_vn', 'received_by' => $this->userId(), 'received_at' => date('Y-m-d H:i:s')], 'id = ? AND tenant_id = ?', [$p['id'], $tid]);
                $this->logStatus($p['id'], $p['status'], 'warehouse_vn', 'Auto nhập kho khi lô hàng đến', $this->userId());
            }
        }

        $this->setFlash('success', 'Đã cập nhật trạng thái lô hàng.');
        return $this->redirect('logistics/shipments/' . $id);
    }

    public function addOrdersToShipment($id)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);
        $tid = Database::tenantId();
        $orderIds = $this->input('order_ids') ?? [];
        if (empty($orderIds)) return $this->json(['error' => 'Chưa chọn đơn'], 422);

        $shipment = Database::fetch("SELECT id, status FROM logistics_shipments WHERE id = ? AND tenant_id = ?", [(int)$id, $tid]);
        if (!$shipment) return $this->json(['error' => 'Lô không tồn tại'], 404);
        if ($shipment['status'] !== 'preparing') return $this->json(['error' => 'Lô đã xuất phát, không thể thêm'], 422);

        $placeholders = implode(',', array_fill(0, count($orderIds), '?'));

        // Link packages from orders
        $pkgs = Database::fetchAll(
            "SELECT id FROM logistics_packages WHERE tenant_id = ? AND order_id IN ({$placeholders}) AND (shipment_id IS NULL OR shipment_id = ?)",
            array_merge([$tid], $orderIds, [(int)$id])
        );
        foreach ($pkgs as $p) {
            Database::update('logistics_packages', ['shipment_id' => (int)$id, 'status' => 'shipping'], 'id = ?', [$p['id']]);
        }

        // Also by customer_name
        $orders = Database::fetchAll("SELECT customer_name FROM logistics_orders WHERE id IN ({$placeholders})", $orderIds);
        foreach ($orders as $o) {
            if (!$o['customer_name']) continue;
            $morePkgs = Database::fetchAll(
                "SELECT id FROM logistics_packages WHERE tenant_id = ? AND customer_name = ? AND shipment_id IS NULL AND status IN ('warehouse_cn','packed')",
                [$tid, $o['customer_name']]
            );
            foreach ($morePkgs as $mp) {
                Database::update('logistics_packages', ['shipment_id' => (int)$id, 'status' => 'shipping'], 'id = ?', [$mp['id']]);
            }
        }

        $this->recalcShipment((int)$id);

        $totalAdded = (int)(Database::fetch("SELECT COUNT(*) as c FROM logistics_packages WHERE shipment_id = ?", [(int)$id])['c'] ?? 0);
        return $this->json(['success' => true, 'message' => 'Đã xếp ' . $totalAdded . ' kiện vào lô', 'total' => $totalAdded]);
    }

    public function removeFromShipment($id)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);
        $tid = Database::tenantId();
        $type = $this->input('type');
        $itemId = (int)$this->input('item_id');

        if ($type === 'bag') {
            Database::update('logistics_bags', ['shipment_id' => null], 'id = ? AND tenant_id = ?', [$itemId, $tid]);
            Database::query("UPDATE logistics_packages SET shipment_id = NULL WHERE bag_id = ? AND tenant_id = ?", [$itemId, $tid]);
        } else {
            Database::update('logistics_packages', ['shipment_id' => null], 'id = ? AND tenant_id = ?', [$itemId, $tid]);
        }

        $this->recalcShipment((int)$id);
        return $this->json(['success' => true]);
    }

    public function addToShipment($id)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);
        $tid = Database::tenantId();
        $type = $this->input('type'); // package or bag
        $itemId = (int)($this->input('item_id') ?: 0);
        $code = trim($this->input('code') ?? '');

        // Find by code if no item_id
        if (!$itemId && $code) {
            if ($type === 'bag') {
                $found = Database::fetch("SELECT id FROM logistics_bags WHERE tenant_id = ? AND bag_code = ?", [$tid, $code]);
            } else {
                $found = Database::fetch("SELECT id FROM logistics_packages WHERE tenant_id = ? AND (package_code = ? OR tracking_code = ? OR tracking_intl = ?)", [$tid, $code, $code, $code]);
            }
            if (!$found) return $this->json(['error' => 'Không tìm thấy: ' . $code], 404);
            $itemId = (int)$found['id'];
        }
        if (!$itemId) return $this->json(['error' => 'Thiếu mã'], 422);

        if ($type === 'bag') {
            Database::update('logistics_bags', ['shipment_id' => (int)$id], 'id = ? AND tenant_id = ?', [$itemId, $tid]);
            // Also assign packages in bag
            Database::query("UPDATE logistics_packages SET shipment_id = ? WHERE bag_id = ? AND tenant_id = ?", [(int)$id, $itemId, $tid]);
            // Update bag count
            $bagPkgCount = (int)(Database::fetch("SELECT COUNT(*) as c FROM logistics_packages WHERE bag_id = ?", [$itemId])['c'] ?? 0);
            $totalWeight = (float)(Database::fetch("SELECT COALESCE(SUM(weight_actual),0) as w FROM logistics_packages WHERE bag_id = ?", [$itemId])['w'] ?? 0);
            Database::update('logistics_bags', ['total_packages' => $bagPkgCount, 'total_weight' => $totalWeight], 'id = ?', [$itemId]);
        } else {
            Database::update('logistics_packages', ['shipment_id' => (int)$id], 'id = ? AND tenant_id = ?', [$itemId, $tid]);
        }

        // Recalc shipment totals
        $this->recalcShipment((int)$id);
        return $this->json(['success' => true]);
    }

    private function recalcShipment(int $shipmentId): void
    {
        $stats = Database::fetch(
            "SELECT COUNT(*) as total_packages, COALESCE(SUM(weight_actual),0) as total_weight, COALESCE(SUM(cbm),0) as total_cbm FROM logistics_packages WHERE shipment_id = ?",
            [$shipmentId]
        );
        $bagCount = (int)(Database::fetch("SELECT COUNT(*) as c FROM logistics_bags WHERE shipment_id = ?", [$shipmentId])['c'] ?? 0);
        Database::update('logistics_shipments', [
            'total_packages' => (int)($stats['total_packages'] ?? 0),
            'total_bags' => $bagCount,
            'total_weight' => (float)($stats['total_weight'] ?? 0),
            'total_cbm' => (float)($stats['total_cbm'] ?? 0),
        ], 'id = ?', [$shipmentId]);
    }

    // ---- Delivery ----
    public function deliveries()
    {
        if (!$this->checkPlugin()) return;
        $deliveries = Database::fetchAll(
            "SELECT ld.*, u.name as delivered_by_name FROM logistics_deliveries ld LEFT JOIN users u ON ld.delivered_by = u.id WHERE ld.tenant_id = ? ORDER BY ld.created_at DESC",
            [Database::tenantId()]
        );
        return $this->view('logistics.deliveries', ['deliveries' => $deliveries]);
    }

    public function createDelivery()
    {
        if (!$this->isPost()) return $this->redirect('logistics/deliveries');
        $tid = Database::tenantId();
        $code = 'GH' . date('ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        $orderId = (int)($this->input('order_id') ?: 0) ?: null;
        $pkgId = (int)($this->input('package_id') ?: 0) ?: null;

        $deliveryId = Database::insert('logistics_deliveries', [
            'tenant_id' => $tid,
            'delivery_code' => $code,
            'order_id' => $orderId,
            'package_id' => $pkgId,
            'customer_name' => trim($this->input('customer_name') ?? '') ?: null,
            'customer_phone' => trim($this->input('customer_phone') ?? '') ?: null,
            'delivery_type' => $this->input('delivery_type') ?: 'full',
            'total_packages' => (int)($this->input('total_packages') ?: 1),
            'cod_amount' => (float)($this->input('cod_amount') ?: 0),
            'note' => trim($this->input('note') ?? '') ?: null,
            'created_by' => $this->userId(),
        ]);

        $this->setFlash('success', 'Đã tạo phiếu giao ' . $code);
        return $this->redirect('logistics/deliveries');
    }

    public function markDelivered($id)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);
        $tid = Database::tenantId();

        $delivery = Database::fetch("SELECT * FROM logistics_deliveries WHERE id = ? AND tenant_id = ?", [(int)$id, $tid]);
        if (!$delivery) return $this->json(['error' => 'Phiếu không tồn tại'], 404);

        $codCollected = (float)($this->input('cod_collected') ?: 0);
        $codMethod = $this->input('cod_method') ?: null;
        $deliveredPkgs = (int)($this->input('delivered_packages') ?: $delivery['total_packages']);

        Database::update('logistics_deliveries', [
            'status' => 'delivered',
            'delivered_packages' => $deliveredPkgs,
            'cod_collected' => $codCollected,
            'cod_method' => $codMethod,
            'delivered_by' => $this->userId(),
            'delivered_at' => date('Y-m-d H:i:s'),
            'delivery_type' => $deliveredPkgs < $delivery['total_packages'] ? 'partial' : 'full',
        ], 'id = ? AND tenant_id = ?', [(int)$id, $tid]);

        // Update linked package/order status
        if ($delivery['package_id']) {
            $pkgStatus = $deliveredPkgs >= $delivery['total_packages'] ? 'delivered' : 'delivering';
            Database::update('logistics_packages', [
                'status' => $pkgStatus, 'delivered_by' => $this->userId(), 'delivered_at' => date('Y-m-d H:i:s'),
            ], 'id = ? AND tenant_id = ?', [$delivery['package_id'], $tid]);
            $this->logStatus($delivery['package_id'], 'warehouse_vn', $pkgStatus, $pkgStatus === 'delivered' ? 'Giao hàng thành công' : 'Giao 1 phần', $this->userId());
        }

        if ($delivery['order_id']) {
            $order = Database::fetch("SELECT * FROM logistics_orders WHERE id = ?", [$delivery['order_id']]);
            if ($order) {
                $newReceived = ($order['received_packages'] ?? 0) + $deliveredPkgs;
                $newStatus = $newReceived >= $order['total_packages'] ? 'completed' : 'partial';
                Database::update('logistics_orders', ['received_packages' => $newReceived, 'status' => $newStatus], 'id = ?', [$delivery['order_id']]);
            }
        }

        return $this->json(['success' => true, 'message' => 'Đã xác nhận giao hàng']);
    }

    // ---- Shipping Calculator ----
    public function shippingCalculator()
    {
        if (!$this->checkPlugin()) return;
        $rates = Database::fetchAll("SELECT * FROM logistics_shipping_rates WHERE tenant_id = ? AND is_active = 1 ORDER BY cargo_type, name", [Database::tenantId()]);
        return $this->view('logistics.calculator', ['rates' => $rates]);
    }

    public function saveRate()
    {
        if (!$this->isPost()) return $this->redirect('logistics/calculator');
        Database::insert('logistics_shipping_rates', [
            'tenant_id' => Database::tenantId(),
            'name' => trim($this->input('name') ?? ''),
            'cargo_type' => $this->input('cargo_type') ?: 'easy',
            'rate_per_kg' => (float)($this->input('rate_per_kg') ?: 0),
            'rate_per_cbm' => (float)($this->input('rate_per_cbm') ?: 0),
            'min_weight' => (float)($this->input('min_weight') ?: 0),
            'max_weight' => (float)($this->input('max_weight') ?: 0),
            'origin' => $this->input('origin') ?: 'CN',
            'destination' => $this->input('destination') ?: 'VN',
        ]);
        $this->setFlash('success', 'Đã thêm bảng giá.');
        return $this->redirect('logistics/calculator');
    }

    public function deleteRate($id)
    {
        if (!$this->isPost()) return $this->redirect('logistics/calculator');
        Database::delete('logistics_shipping_rates', 'id = ? AND tenant_id = ?', [(int)$id, Database::tenantId()]);
        $this->setFlash('success', 'Đã xóa.');
        return $this->redirect('logistics/calculator');
    }

    // ---- Reports ----
    public function reports()
    {
        if (!$this->checkPlugin()) return;
        $tid = Database::tenantId();
        $dateFrom = $this->input('date_from') ?: date('Y-m-01');
        $dateTo = $this->input('date_to') ?: date('Y-m-d');

        $receiveStats = Database::fetch(
            "SELECT COUNT(*) as total, SUM(CASE WHEN status IN ('warehouse_vn','delivering','delivered') THEN 1 ELSE 0 END) as received, COALESCE(SUM(weight_actual),0) as total_weight
             FROM logistics_packages WHERE tenant_id = ? AND created_at BETWEEN ? AND ?",
            [$tid, $dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']
        );

        $deliveryStats = Database::fetch(
            "SELECT COUNT(*) as total, COALESCE(SUM(cod_collected),0) as total_cod,
                    SUM(CASE WHEN cod_method='cash' THEN cod_collected ELSE 0 END) as cod_cash,
                    SUM(CASE WHEN cod_method='transfer' THEN cod_collected ELSE 0 END) as cod_transfer
             FROM logistics_deliveries WHERE tenant_id = ? AND status = 'delivered' AND delivered_at BETWEEN ? AND ?",
            [$tid, $dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']
        );

        $orderStats = Database::fetch(
            "SELECT COUNT(*) as total, SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed,
                    COALESCE(SUM(total_amount),0) as total_amount
             FROM logistics_orders WHERE tenant_id = ? AND created_at BETWEEN ? AND ?",
            [$tid, $dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']
        );

        $dailyReceive = Database::fetchAll(
            "SELECT DATE(received_at) as day, COUNT(*) as count, COALESCE(SUM(weight_actual),0) as weight
             FROM logistics_packages WHERE tenant_id = ? AND received_at BETWEEN ? AND ? AND status IN ('warehouse_vn','delivering','delivered')
             GROUP BY DATE(received_at) ORDER BY day",
            [$tid, $dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']
        );

        return $this->view('logistics.reports', [
            'receiveStats' => $receiveStats,
            'deliveryStats' => $deliveryStats,
            'orderStats' => $orderStats,
            'dailyReceive' => $dailyReceive,
            'filters' => ['date_from' => $dateFrom, 'date_to' => $dateTo],
        ]);
    }

    // ---- Auto-complete helpers ----
    public static function checkAutoComplete(int $bagId): void
    {
        try {
            $bag = Database::fetch("SELECT id, status FROM logistics_bags WHERE id = ?", [$bagId]);
            if (!$bag || $bag['status'] === 'completed') return;

            $total = (int)(Database::fetch("SELECT COUNT(*) as c FROM logistics_packages WHERE bag_id = ?", [$bagId])['c'] ?? 0);
            $received = (int)(Database::fetch("SELECT COUNT(*) as c FROM logistics_packages WHERE bag_id = ? AND status IN ('warehouse_vn','delivered')", [$bagId])['c'] ?? 0);

            if ($total > 0 && $received >= $total) {
                Database::update('logistics_bags', ['status' => 'completed'], 'id = ?', [$bagId]);
            }
        } catch (\Exception $e) {}
    }

    public static function checkShipmentAutoArrival(int $shipmentId): void
    {
        try {
            $shipment = Database::fetch("SELECT id, status FROM logistics_shipments WHERE id = ?", [$shipmentId]);
            if (!$shipment || in_array($shipment['status'], ['arrived','completed'])) return;

            $total = (int)(Database::fetch("SELECT COUNT(*) as c FROM logistics_packages WHERE shipment_id = ?", [$shipmentId])['c'] ?? 0);
            $received = (int)(Database::fetch("SELECT COUNT(*) as c FROM logistics_packages WHERE shipment_id = ? AND status IN ('warehouse_vn','delivered')", [$shipmentId])['c'] ?? 0);

            if ($total > 0 && $received >= $total) {
                Database::update('logistics_shipments', ['status' => 'arrived', 'arrived_at' => date('Y-m-d H:i:s')], 'id = ?', [$shipmentId]);
            }
        } catch (\Exception $e) {}
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
