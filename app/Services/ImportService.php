<?php

namespace App\Services;

use Core\Database;

class ImportService
{
    public static function importContacts(string $filePath, int $userId): array
    {
        $result = ['total' => 0, 'success' => 0, 'errors' => []];
        $tenantId = $_SESSION['tenant_id'] ?? 1;

        try {
            $handle = fopen($filePath, 'r');
            if ($handle === false) {
                $result['errors'][] = 'Không thể mở file CSV.';
                return $result;
            }

            $headers = fgetcsv($handle);
            if ($headers === false) {
                fclose($handle);
                $result['errors'][] = 'File CSV không có dữ liệu.';
                return $result;
            }

            // Remove BOM if present
            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
            $headers = array_map('trim', $headers);
            $headers = array_map('strtolower', $headers);
            $row = 1;

            while (($data = fgetcsv($handle)) !== false) {
                $row++;
                $result['total']++;

                if (count($data) < count($headers)) {
                    $data = array_pad($data, count($headers), '');
                }
                $record = array_combine($headers, $data);

                if (empty(trim($record['first_name'] ?? ''))) {
                    $result['errors'][] = "Dòng {$row}: Thiếu first_name.";
                    continue;
                }

                $email = trim($record['email'] ?? '');
                if (!empty($email)) {
                    $existing = Database::fetch(
                        "SELECT id FROM contacts WHERE email = ? AND tenant_id = ? LIMIT 1",
                        [$email, $tenantId]
                    );
                    if ($existing) {
                        $result['errors'][] = "Dòng {$row}: Email {$email} đã tồn tại.";
                        continue;
                    }
                }

                Database::insert('contacts', [
                    'tenant_id'  => $tenantId,
                    'first_name' => trim($record['first_name'] ?? ''),
                    'last_name'  => trim($record['last_name'] ?? ''),
                    'email'      => $email,
                    'phone'      => trim($record['phone'] ?? ''),
                    'status'     => trim($record['status'] ?? 'new'),
                    'owner_id'   => $userId,
                    'created_by' => $userId,
                ]);

                $result['success']++;
            }

            fclose($handle);

            Database::insert('import_logs', [
                'module'        => 'contacts',
                'file_name'     => basename($filePath),
                'total_rows'    => $result['total'],
                'success_count' => $result['success'],
                'error_count'   => count($result['errors']),
                'errors'        => json_encode($result['errors']),
                'status'        => 'completed',
                'created_by'    => $userId,
            ]);
        } catch (\Exception $e) {
            $result['errors'][] = 'Lỗi hệ thống: ' . $e->getMessage();
        }

        return $result;
    }

    public static function importProducts(string $filePath, int $userId): array
    {
        $result = ['total' => 0, 'success' => 0, 'errors' => []];
        $tenantId = $_SESSION['tenant_id'] ?? 1;

        try {
            $handle = fopen($filePath, 'r');
            if ($handle === false) {
                $result['errors'][] = 'Không thể mở file CSV.';
                return $result;
            }

            $headers = fgetcsv($handle);
            if ($headers === false) {
                fclose($handle);
                $result['errors'][] = 'File CSV không có dữ liệu.';
                return $result;
            }

            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
            $headers = array_map('trim', $headers);
            $headers = array_map('strtolower', $headers);
            $row = 1;

            while (($data = fgetcsv($handle)) !== false) {
                $row++;
                $result['total']++;

                if (count($data) < count($headers)) {
                    $data = array_pad($data, count($headers), '');
                }
                $record = array_combine($headers, $data);

                if (empty(trim($record['name'] ?? ''))) {
                    $result['errors'][] = "Dòng {$row}: Thiếu name.";
                    continue;
                }

                $sku = trim($record['sku'] ?? '');
                if (!empty($sku)) {
                    $existing = Database::fetch(
                        "SELECT id FROM products WHERE sku = ? AND tenant_id = ? LIMIT 1",
                        [$sku, $tenantId]
                    );
                    if ($existing) {
                        $result['errors'][] = "Dòng {$row}: SKU {$sku} đã tồn tại.";
                        continue;
                    }
                }

                Database::insert('products', [
                    'tenant_id'   => $tenantId,
                    'name'        => trim($record['name'] ?? ''),
                    'sku'         => $sku ?: null,
                    'type'        => trim($record['type'] ?? 'product'),
                    'unit'        => trim($record['unit'] ?? 'Cái'),
                    'price'       => (float)($record['price'] ?? 0),
                    'cost_price'  => (float)($record['cost_price'] ?? 0),
                    'description' => trim($record['description'] ?? ''),
                    'is_active'   => 1,
                    'created_by'  => $userId,
                ]);

                $result['success']++;
            }

            fclose($handle);

            Database::insert('import_logs', [
                'module'        => 'products',
                'file_name'     => basename($filePath),
                'total_rows'    => $result['total'],
                'success_count' => $result['success'],
                'error_count'   => count($result['errors']),
                'errors'        => json_encode($result['errors']),
                'status'        => 'completed',
                'created_by'    => $userId,
            ]);
        } catch (\Exception $e) {
            $result['errors'][] = 'Lỗi hệ thống: ' . $e->getMessage();
        }

        return $result;
    }

    public static function exportContacts(array $filters = []): string
    {
        $where = 'c.is_deleted = 0';
        $params = [];
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $where .= ' AND c.tenant_id = ?';
        $params[] = $tenantId;

        if (!empty($filters['status'])) {
            $where .= ' AND c.status = ?';
            $params[] = $filters['status'];
        }

        $contacts = Database::fetchAll(
            "SELECT c.*, comp.name as company_name, cs.name as source_name
             FROM contacts c
             LEFT JOIN companies comp ON c.company_id = comp.id
             LEFT JOIN contact_sources cs ON c.source_id = cs.id
             WHERE {$where}
             ORDER BY c.created_at DESC",
            $params
        );

        $output = fopen('php://temp', 'r+');
        fputcsv($output, ['first_name', 'last_name', 'email', 'phone', 'company', 'source', 'status', 'city', 'created_at']);

        foreach ($contacts as $c) {
            fputcsv($output, [
                $c['first_name'] ?? '', $c['last_name'] ?? '', $c['email'] ?? '',
                $c['phone'] ?? '', $c['company_name'] ?? '', $c['source_name'] ?? '',
                $c['status'] ?? '', $c['city'] ?? '', $c['created_at'] ?? '',
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        return $csv;
    }

    public static function exportProducts(array $filters = []): string
    {
        $where = 'p.is_deleted = 0';
        $params = [];
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $where .= ' AND p.tenant_id = ?';
        $params[] = $tenantId;

        $products = Database::fetchAll(
            "SELECT p.*, pc.name as category_name
             FROM products p
             LEFT JOIN product_categories pc ON p.category_id = pc.id
             WHERE {$where}
             ORDER BY p.name",
            $params
        );

        $output = fopen('php://temp', 'r+');
        fputcsv($output, ['name', 'sku', 'type', 'unit', 'price', 'cost_price', 'category', 'description']);

        foreach ($products as $p) {
            fputcsv($output, [
                $p['name'] ?? '', $p['sku'] ?? '', $p['type'] ?? '', $p['unit'] ?? '',
                $p['price'] ?? '', $p['cost_price'] ?? '', $p['category_name'] ?? '',
                $p['description'] ?? '',
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        return $csv;
    }
}
