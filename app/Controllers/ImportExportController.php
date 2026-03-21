<?php

namespace App\Controllers;

use Core\Controller;
use App\Services\ImportService;
use App\Services\FileUploadService;

class ImportExportController extends Controller
{
    public function index()
    {
        return $this->view('import.index');
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
        $filters = [];

        $dateFrom = $this->input('date_from');
        $dateTo = $this->input('date_to');

        if ($dateFrom) {
            $filters['date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $filters['date_to'] = $dateTo;
        }

        $csv = ImportService::exportContacts($filters);
        $filename = 'contacts_' . date('Y-m-d_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        echo $csv;
        exit;
    }

    public function exportProducts()
    {
        $filters = [];

        $dateFrom = $this->input('date_from');
        $dateTo = $this->input('date_to');

        if ($dateFrom) {
            $filters['date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $filters['date_to'] = $dateTo;
        }

        $csv = ImportService::exportProducts($filters);
        $filename = 'products_' . date('Y-m-d_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        echo $csv;
        exit;
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
