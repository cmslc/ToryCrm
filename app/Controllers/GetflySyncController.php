<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class GetflySyncController extends Controller
{
    private array $endpoints = [
        'accounts' => [
            'name' => 'Khách hàng',
            'icon' => 'ri-contacts-line',
            'color' => 'primary',
            'api_path' => 'api/v3/accounts',
            'method' => 'GET',
            'description' => 'Đồng bộ danh sách khách hàng, người liên hệ',
            'params' => ['page', 'num_per_page'],
        ],
        'products' => [
            'name' => 'Sản phẩm',
            'icon' => 'ri-shopping-bag-line',
            'color' => 'success',
            'api_path' => 'api/v3/products',
            'method' => 'GET',
            'description' => 'Đồng bộ danh sách sản phẩm, giá, danh mục',
            'params' => ['page', 'num_per_page'],
        ],
        'orders_sale' => [
            'name' => 'Đơn hàng bán',
            'icon' => 'ri-shopping-cart-line',
            'color' => 'info',
            'api_path' => 'api/v3/orders',
            'method' => 'GET',
            'description' => 'Đồng bộ đơn hàng bán (order_type=2)',
            'params' => ['order_type=2', 'start_date', 'end_date', 'page', 'num_per_page'],
        ],
        'orders_purchase' => [
            'name' => 'Đơn mua hàng',
            'icon' => 'ri-truck-line',
            'color' => 'warning',
            'api_path' => 'api/v3/orders',
            'method' => 'GET',
            'description' => 'Đồng bộ đơn mua hàng (order_type=1)',
            'params' => ['order_type=1', 'start_date', 'end_date', 'page', 'num_per_page'],
        ],
        'campaigns' => [
            'name' => 'Chiến dịch',
            'icon' => 'ri-megaphone-line',
            'color' => 'danger',
            'api_path' => 'api/v3/campaigns',
            'method' => 'GET',
            'description' => 'Đồng bộ chiến dịch marketing',
            'params' => ['page', 'num_per_page'],
        ],
        'users' => [
            'name' => 'Nhân viên',
            'icon' => 'ri-team-line',
            'color' => 'secondary',
            'api_path' => 'api/v3/users',
            'method' => 'GET',
            'description' => 'Đồng bộ nhân viên, phòng ban',
            'params' => [],
        ],
        'tasks' => [
            'name' => 'Công việc',
            'icon' => 'ri-task-line',
            'color' => 'dark',
            'api_path' => 'api/v3/tasks',
            'method' => 'GET',
            'description' => 'Đồng bộ công việc, nhiệm vụ',
            'params' => ['page', 'num_per_page'],
        ],
    ];

    public function index()
    {
        $this->authorize('settings', 'manage');

        $config = $this->getConfig();
        $logs = Database::fetchAll(
            "SELECT * FROM getfly_sync_logs WHERE tenant_id = ? ORDER BY started_at DESC LIMIT 20",
            [Database::tenantId()]
        );

        // Test connection if config exists
        $connectionStatus = null;
        if ($config && $config['is_active']) {
            $connectionStatus = $this->testConnection($config);
        }

        return $this->view('settings.getfly-sync', [
            'config' => $config,
            'endpoints' => $this->endpoints,
            'logs' => $logs,
            'connectionStatus' => $connectionStatus,
        ]);
    }

    public function saveConfig()
    {
        if (!$this->isPost()) return $this->redirect('settings/getfly-sync');
        $this->authorize('settings', 'manage');

        $domain = trim($this->input('api_domain') ?? '');
        $apiKey = trim($this->input('api_key') ?? '');

        if (empty($domain) || empty($apiKey)) {
            $this->setFlash('error', 'Vui lòng nhập đầy đủ domain và API key.');
            return $this->back();
        }

        // Normalize domain
        $domain = rtrim($domain, '/');
        if (!str_starts_with($domain, 'http')) {
            $domain = 'https://' . $domain;
        }

        $tid = Database::tenantId();
        $existing = Database::fetch("SELECT id FROM getfly_sync_config WHERE tenant_id = ?", [$tid]);

        if ($existing) {
            Database::update('getfly_sync_config', [
                'api_domain' => $domain,
                'api_key' => $apiKey,
                'is_active' => 1,
            ], 'id = ?', [$existing['id']]);
        } else {
            Database::insert('getfly_sync_config', [
                'tenant_id' => $tid,
                'api_domain' => $domain,
                'api_key' => $apiKey,
                'is_active' => 1,
            ]);
        }

        $this->setFlash('success', 'Đã lưu cấu hình API Getfly.');
        return $this->redirect('settings/getfly-sync');
    }

    public function testApi()
    {
        $this->authorize('settings', 'manage');
        $config = $this->getConfig();
        if (!$config) {
            return $this->json(['error' => 'Chưa cấu hình API'], 400);
        }

        $endpoint = $this->input('endpoint');
        if (!isset($this->endpoints[$endpoint])) {
            return $this->json(['error' => 'Endpoint không hợp lệ'], 400);
        }

        $ep = $this->endpoints[$endpoint];
        $url = $config['api_domain'] . '/' . $ep['api_path'] . '?';

        // Add default params
        if ($endpoint === 'orders_sale') {
            $url .= 'order_type=2&start_date=2026-01-01&end_date=2026-12-31&';
        } elseif ($endpoint === 'orders_purchase') {
            $url .= 'order_type=1&start_date=2026-01-01&end_date=2026-12-31&';
        }
        $url .= 'page=1&num_per_page=1';

        $result = $this->callApi($config['api_key'], $url);

        if ($result['success']) {
            $total = $result['data']['pagination']['total_record'] ?? count($result['data']['records'] ?? []);
            return $this->json([
                'success' => true,
                'total_records' => $total,
                'sample' => $result['data']['records'][0] ?? null,
            ]);
        }

        return $this->json(['error' => $result['error']], 400);
    }

    public function sync()
    {
        if (!$this->isPost()) return $this->json(['error' => 'Invalid'], 400);
        $this->authorize('settings', 'manage');

        $config = $this->getConfig();
        if (!$config) {
            return $this->json(['error' => 'Chưa cấu hình API'], 400);
        }

        $endpoint = $this->input('endpoint');
        if (!isset($this->endpoints[$endpoint])) {
            return $this->json(['error' => 'Endpoint không hợp lệ'], 400);
        }

        // Log start
        $logId = Database::insert('getfly_sync_logs', [
            'tenant_id' => Database::tenantId(),
            'endpoint' => $endpoint,
            'status' => 'running',
        ]);

        // TODO: Implement actual sync logic per endpoint
        // For now, just test connection and log
        $ep = $this->endpoints[$endpoint];
        $url = $config['api_domain'] . '/' . $ep['api_path'] . '?page=1&num_per_page=1';
        if ($endpoint === 'orders_sale') $url .= '&order_type=2&start_date=2020-01-01&end_date=2026-12-31';
        if ($endpoint === 'orders_purchase') $url .= '&order_type=1&start_date=2020-01-01&end_date=2026-12-31';

        $result = $this->callApi($config['api_key'], $url);

        if ($result['success']) {
            $total = $result['data']['pagination']['total_record'] ?? 0;
            Database::update('getfly_sync_logs', [
                'status' => 'success',
                'records_synced' => 0,
                'completed_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$logId]);

            return $this->json([
                'success' => true,
                'message' => "Kết nối thành công. Tìm thấy {$total} records. Chức năng đồng bộ đang được phát triển.",
                'total' => $total,
            ]);
        }

        Database::update('getfly_sync_logs', [
            'status' => 'error',
            'error_message' => $result['error'],
            'completed_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$logId]);

        return $this->json(['error' => $result['error']], 400);
    }

    private function getConfig(): ?array
    {
        try {
            return Database::fetch(
                "SELECT * FROM getfly_sync_config WHERE tenant_id = ? AND is_active = 1 LIMIT 1",
                [Database::tenantId()]
            ) ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function testConnection(array $config): array
    {
        $url = $config['api_domain'] . '/api/v3/accounts?page=1&num_per_page=1';
        $result = $this->callApi($config['api_key'], $url);

        if ($result['success']) {
            return ['status' => 'connected', 'message' => 'Kết nối thành công'];
        }
        return ['status' => 'error', 'message' => $result['error']];
    }

    private function callApi(string $apiKey, string $url): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTPHEADER => [
                'X-API-KEY: ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => 'Lỗi kết nối: ' . $error];
        }

        if ($httpCode !== 200) {
            return ['success' => false, 'error' => "HTTP {$httpCode}"];
        }

        $data = json_decode($response, true);
        if (!$data) {
            return ['success' => false, 'error' => 'Phản hồi không hợp lệ'];
        }

        return ['success' => true, 'data' => $data];
    }
}
