<?php

namespace App\Controllers;

use Core\Controller;
use App\Services\ImportService;
use App\Services\FileUploadService;

class ImportExportController extends Controller
{
    public function index()
    {
        return $this->redirect('contacts');
    }

    public function importContacts()
    {
        if (!$this->isPost()) {
            return $this->redirect('import-export');
        }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('error', 'Vui lòng chọn file CSV để import.');
            return $this->back();
        }

        $file = $_FILES['file'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($extension !== 'csv') {
            $this->setFlash('error', 'Chỉ hỗ trợ file CSV.');
            return $this->back();
        }

        $uploaded = FileUploadService::upload($file, 'imports', 'import', null);

        if (!$uploaded) {
            $this->setFlash('error', 'Không thể upload file. Vui lòng thử lại.');
            return $this->back();
        }

        $filePath = BASE_PATH . '/public/' . $uploaded['file_path'];
        $result = ImportService::importContacts($filePath, $this->userId());

        if (!empty($result['errors'])) {
            $errorMsg = "Import hoàn tất: {$result['success']}/{$result['total']} thành công. Lỗi: " . implode('; ', array_slice($result['errors'], 0, 5));
            $this->setFlash('warning', $errorMsg);
        } else {
            $this->setFlash('success', "Import thành công {$result['success']}/{$result['total']} khách hàng.");
        }

        return $this->redirect('import-export');
    }

    public function importProducts()
    {
        if (!$this->isPost()) {
            return $this->redirect('import-export');
        }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('error', 'Vui lòng chọn file CSV để import.');
            return $this->back();
        }

        $file = $_FILES['file'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($extension !== 'csv') {
            $this->setFlash('error', 'Chỉ hỗ trợ file CSV.');
            return $this->back();
        }

        $uploaded = FileUploadService::upload($file, 'imports', 'import', null);

        if (!$uploaded) {
            $this->setFlash('error', 'Không thể upload file. Vui lòng thử lại.');
            return $this->back();
        }

        $filePath = BASE_PATH . '/public/' . $uploaded['file_path'];
        $result = ImportService::importProducts($filePath, $this->userId());

        if (!empty($result['errors'])) {
            $errorMsg = "Import hoàn tất: {$result['success']}/{$result['total']} thành công. Lỗi: " . implode('; ', array_slice($result['errors'], 0, 5));
            $this->setFlash('warning', $errorMsg);
        } else {
            $this->setFlash('success', "Import thành công {$result['success']}/{$result['total']} sản phẩm.");
        }

        return $this->redirect('import-export');
    }

    public function exportContacts()
    {
        $this->authorize('contacts', 'view');
        if (!\App\Services\RateLimiter::attempt('export:' . $this->userId(), 10, 60)) {
            $this->setFlash('error', 'Vượt giới hạn 10 export/giờ. Thử lại sau.');
            return $this->redirect('contacts');
        }
        \App\Services\AuditLog::log('export', 'contacts', null, 'Export contacts');

        $tid = \Core\Database::tenantId();
        $where = ["c.is_deleted = 0", "c.tenant_id = ?"];
        $params = [$tid];
        if ($df = $this->input('date_from')) { $where[] = "DATE(c.created_at) >= ?"; $params[] = $df; }
        if ($dt = $this->input('date_to')) { $where[] = "DATE(c.created_at) <= ?"; $params[] = $dt; }
        if ($s = $this->input('search')) {
            $where[] = "(c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ? OR c.phone LIKE ? OR c.company_name LIKE ?)";
            $like = "%{$s}%"; $params = array_merge($params, [$like, $like, $like, $like, $like]);
        }
        if ($st = $this->input('status')) { $where[] = "c.status = ?"; $params[] = $st; }
        if ($sid = $this->input('source_id')) { $where[] = "c.source_id = ?"; $params[] = $sid; }
        if ($oid = $this->input('owner_id')) { $where[] = "c.owner_id = ?"; $params[] = $oid; }

        $rows = \Core\Database::fetchAll(
            "SELECT c.*, cs.name as source_name, u.name as owner_name, c.company_name
             FROM contacts c
             LEFT JOIN contact_sources cs ON c.source_id = cs.id
             LEFT JOIN users u ON c.owner_id = u.id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY c.created_at DESC",
            $params
        );

        $columns = [
            'account_code'      => ['label' => 'Mã KH'],
            'first_name'        => ['label' => 'Tên'],
            'last_name'         => ['label' => 'Họ'],
            'full_name'         => ['label' => 'Họ và tên'],
            'title'             => ['label' => 'Danh xưng'],
            'position'          => ['label' => 'Chức vụ'],
            'email'             => ['label' => 'Email'],
            'phone'             => ['label' => 'SĐT'],
            'mobile'            => ['label' => 'Di động'],
            'fax'               => ['label' => 'Fax'],
            'date_of_birth'     => ['label' => 'Ngày sinh'],
            'gender'            => ['label' => 'Giới tính'],
            'company_name'      => ['label' => 'Công ty'],
            'company_phone'     => ['label' => 'SĐT công ty'],
            'company_email'     => ['label' => 'Email công ty'],
            'tax_code'          => ['label' => 'Mã số thuế'],
            'website'           => ['label' => 'Website'],
            'industry'          => ['label' => 'Ngành nghề'],
            'company_size'      => ['label' => 'Quy mô'],
            'address'           => ['label' => 'Địa chỉ'],
            'ward'              => ['label' => 'Phường/Xã'],
            'district'          => ['label' => 'Quận/Huyện'],
            'province'          => ['label' => 'Tỉnh/TP'],
            'city'              => ['label' => 'Thành phố'],
            'country'           => ['label' => 'Quốc gia'],
            'source_name'       => ['label' => 'Nguồn'],
            'status'            => ['label' => 'Trạng thái'],
            'customer_group'    => ['label' => 'Nhóm KH'],
            'total_revenue'     => ['label' => 'Doanh thu'],
            'owner_name'        => ['label' => 'Phụ trách'],
            'description'       => ['label' => 'Mô tả'],
            'last_activity_at'  => ['label' => 'Tương tác cuối'],
            'created_at'        => ['label' => 'Ngày tạo'],
        ];
        $selected = \App\Services\CsvExporter::parseColumnsParam((string)$this->input('columns', ''), $columns);
        \App\Services\CsvExporter::download($rows, $columns, 'contacts_' . date('Ymd_His') . '.csv', $selected);
    }

    public function exportProducts()
    {
        $this->authorize('products', 'view');
        if (!\App\Services\RateLimiter::attempt('export:' . $this->userId(), 10, 60)) {
            $this->setFlash('error', 'Vượt giới hạn 10 export/giờ. Thử lại sau.');
            return $this->redirect('products');
        }
        \App\Services\AuditLog::log('export', 'products', null, 'Export products');

        $tid = \Core\Database::tenantId();
        $where = ["p.is_deleted = 0", "p.tenant_id = ?"];
        $params = [$tid];
        if ($df = $this->input('date_from')) { $where[] = "DATE(p.created_at) >= ?"; $params[] = $df; }
        if ($dt = $this->input('date_to')) { $where[] = "DATE(p.created_at) <= ?"; $params[] = $dt; }
        if ($s = $this->input('search')) {
            $where[] = "(p.name LIKE ? OR p.sku LIKE ?)";
            $like = "%{$s}%"; $params = array_merge($params, [$like, $like]);
        }
        if ($cid = $this->input('category_id')) { $where[] = "p.category_id = ?"; $params[] = $cid; }
        if ($t = $this->input('type')) { $where[] = "p.type = ?"; $params[] = $t; }

        $rows = \Core\Database::fetchAll(
            "SELECT p.*, pc.name as category_name,
                    pm.name as manufacturer_name, po.name as origin_name
             FROM products p
             LEFT JOIN product_categories pc ON p.category_id = pc.id
             LEFT JOIN product_manufacturers pm ON p.manufacturer_id = pm.id
             LEFT JOIN product_origins po ON p.origin_id = po.id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY p.created_at DESC",
            $params
        );

        $columns = [
            'sku'               => ['label' => 'SKU'],
            'barcode'           => ['label' => 'Barcode'],
            'name'              => ['label' => 'Tên sản phẩm'],
            'type'              => ['label' => 'Loại'],
            'unit'              => ['label' => 'Đơn vị'],
            'category_name'     => ['label' => 'Danh mục'],
            'manufacturer_name' => ['label' => 'Nhà sản xuất'],
            'origin_name'       => ['label' => 'Xuất xứ'],
            'price'             => ['label' => 'Giá bán'],
            'price_wholesale'   => ['label' => 'Giá sỉ'],
            'price_online'      => ['label' => 'Giá online'],
            'saleoff_price'     => ['label' => 'Giá khuyến mại'],
            'discount_percent'  => ['label' => 'Chiết khấu %'],
            'cost_price'        => ['label' => 'Giá vốn'],
            'tax_rate'          => ['label' => 'Thuế %'],
            'weight'            => ['label' => 'Trọng lượng'],
            'dimensions'        => ['label' => 'Kích thước'],
            'color'             => ['label' => 'Màu'],
            'stock_quantity'    => ['label' => 'Tồn kho'],
            'min_stock'         => ['label' => 'Tồn tối thiểu'],
            'is_active'         => ['label' => 'Đang bán'],
            'short_description' => ['label' => 'Mô tả ngắn'],
            'description'       => ['label' => 'Mô tả'],
            'created_at'        => ['label' => 'Ngày tạo'],
        ];
        $selected = \App\Services\CsvExporter::parseColumnsParam((string)$this->input('columns', ''), $columns);
        \App\Services\CsvExporter::download($rows, $columns, 'products_' . date('Ymd_His') . '.csv', $selected);
    }

    public function downloadTemplate($type)
    {
        if ($type === 'contacts') {
            $filename = 'template_contacts.csv';
            $headers = ['first_name', 'last_name', 'email', 'phone', 'company', 'source', 'status'];
        } elseif ($type === 'products') {
            $filename = 'template_products.csv';
            $headers = ['name', 'sku', 'type', 'unit', 'price', 'cost_price', 'category', 'description'];
        } else {
            $this->setFlash('error', 'Template không hợp lệ.');
            return $this->redirect('import-export');
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF"; // UTF-8 BOM

        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);
        fclose($output);
        exit;
    }
}
