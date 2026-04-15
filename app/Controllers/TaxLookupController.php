<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class TaxLookupController extends Controller
{
    public function lookup()
    {
        $taxCode = trim($this->input('tax_code') ?? '');
        if (empty($taxCode)) return $this->json(['error' => 'MST không được trống'], 422);

        // Check cache first (24h)
        try {
            $cached = Database::fetch(
                "SELECT * FROM tax_lookup_cache WHERE tax_code = ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)",
                [$taxCode]
            );
            if ($cached) {
                return $this->json(['success' => true, 'data' => [
                    'name' => $cached['company_name'],
                    'address' => $cached['address'],
                ]]);
            }
        } catch (\Exception $e) {}

        // Try API 1: VietQR
        $result = $this->tryVietQR($taxCode);

        // Try API 2: OpenAPI.vn
        if (!$result) {
            $result = $this->tryOpenAPI($taxCode);
        }

        // Try API 3: WifiCity
        if (!$result) {
            $result = $this->tryWifiCity($taxCode);
        }


        if ($result) {
            // Cache result
            try {
                Database::query(
                    "INSERT INTO tax_lookup_cache (tax_code, company_name, address, created_at) VALUES (?, ?, ?, NOW())
                     ON DUPLICATE KEY UPDATE company_name = VALUES(company_name), address = VALUES(address), created_at = NOW()",
                    [$taxCode, $result['name'], $result['address']]
                );
            } catch (\Exception $e) {}

            return $this->json(['success' => true, 'data' => $result]);
        }

        return $this->json(['error' => 'Không tìm thấy doanh nghiệp'], 404);
    }

    private function tryVietQR(string $taxCode): ?array
    {
        $ctx = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]);
        $response = @file_get_contents("https://api.vietqr.io/v2/business/{$taxCode}", false, $ctx);
        if (!$response) return null;

        $data = json_decode($response, true);
        if (($data['code'] ?? '') === '00' && !empty($data['data'])) {
            return [
                'name' => $data['data']['name'] ?? '',
                'address' => $data['data']['address'] ?? '',
            ];
        }
        return null;
    }

    private function tryOpenAPI(string $taxCode): ?array
    {
        $ctx = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]);
        $response = @file_get_contents("https://api.openapi.vn/company/{$taxCode}", false, $ctx);
        if (!$response) return null;

        $data = json_decode($response, true);
        if (!empty($data['name'])) {
            return [
                'name' => $data['name'] ?? '',
                'address' => $data['address'] ?? '',
            ];
        }
        return null;
    }

    private function tryWifiCity(string $taxCode): ?array
    {
        $ctx = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]);
        $response = @file_get_contents("https://thongtindoanhnghiep.co/api/company/{$taxCode}", false, $ctx);
        if (!$response) return null;

        $data = json_decode($response, true);
        if (!empty($data['Title'])) {
            return [
                'name' => $data['Title'] ?? '',
                'address' => $data['DiaChi'] ?? '',
            ];
        }
        return null;
    }

}
