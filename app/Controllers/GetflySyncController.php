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

        // Orders API returns ALL records (ignores pagination, huge response)
        // For test: just verify connection with accounts endpoint instead
        if (in_array($endpoint, ['orders_sale', 'orders_purchase'])) {
            $testUrl = $config['api_domain'] . '/api/v3/accounts?page=1&num_per_page=1';
            $testResult = $this->callApi($config['api_key'], $testUrl);
            if ($testResult['success']) {
                return $this->json([
                    'success' => true,
                    'total_records' => 0,
                    'extra' => ' — API kết nối OK',
                    'sample' => null,
                    'hide_count' => true,
                ]);
            }
            return $this->json(['error' => $testResult['error']], 400);
        }

        $url .= 'page=1&num_per_page=1';

        $result = $this->callApi($config['api_key'], $url);

        if ($result['success']) {
            $data = $result['data'];
            // Handle different response formats:
            // 1. {records:[], pagination:{total_records:N}} - accounts, products
            // 2. {data:[], pagination:{}} - tasks
            // 3. [...] - plain array - users, campaigns
            // 4. {records:[]} - orders (no useful pagination)
            if (isset($data['pagination'])) {
                $items = $data['records'] ?? $data['data'] ?? [];
                $total = $data['pagination']['total_records'] ?? $data['pagination']['total_record'] ?? count($items);
                $sample = $items[0] ?? null;
            } elseif (isset($data['data']) && is_array($data['data'])) {
                $total = count($data['data']);
                $sample = $data['data'][0] ?? null;
            } elseif (isset($data['records'])) {
                $total = count($data['records']);
                $sample = $data['records'][0] ?? null;
            } elseif (is_array($data) && isset($data[0])) {
                $total = count($data);
                $sample = $data[0] ?? null;
            } else {
                $total = 0;
                $sample = null;
            }
            $extra = '';
            if (in_array($endpoint, ['orders_sale', 'orders_purchase'])) {
                $extra = ' (hôm nay)';
            }
            return $this->json([
                'success' => true,
                'total_records' => $total,
                'extra' => $extra,
                'sample' => $sample,
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

        try {
            $synced = match ($endpoint) {
                'tasks' => $this->syncTasks($config),
                default => $this->syncPlaceholder($config, $endpoint),
            };

            Database::update('getfly_sync_logs', [
                'status' => 'success',
                'records_synced' => $synced,
                'completed_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$logId]);

            return $this->json([
                'success' => true,
                'message' => "Đã đồng bộ {$synced} records thành công.",
                'total' => $synced,
            ]);
        } catch (\Exception $e) {
            Database::update('getfly_sync_logs', [
                'status' => 'error',
                'error_message' => $e->getMessage(),
                'completed_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$logId]);

            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Sync tasks page by page (called via AJAX per page)
     */
    public function syncTasksPage()
    {
        if (!$this->isPost()) return $this->json(['error' => 'Invalid'], 400);
        $this->authorize('settings', 'manage');

        $config = $this->getConfig();
        if (!$config) return $this->json(['error' => 'Chưa cấu hình'], 400);

        $page = max(1, (int)$this->input('page'));
        $perPage = 30;
        $tid = Database::tenantId();

        $url = $config['api_domain'] . '/api/v3/tasks?page=' . $page . '&num_per_page=' . $perPage;
        $result = $this->callApi($config['api_key'], $url);

        if (!$result['success']) {
            return $this->json(['error' => 'API error: ' . $result['error']], 400);
        }

        $tasks = $result['data']['data'] ?? [];
        if (empty($tasks)) {
            return $this->json(['done' => true, 'synced' => 0, 'page' => $page]);
        }

        // Build user map
        static $userMap = null;
        if ($userMap === null) {
            $userMap = [];
            $users = Database::fetchAll("SELECT id, name FROM users WHERE is_active = 1");
            foreach ($users as $u) $userMap[mb_strtolower($u['name'])] = $u['id'];
        }

        $statusMap = ['1' => 'todo', '6' => 'in_progress', '11' => 'review', '14' => 'done'];
        $synced = 0;

        foreach ($tasks as $t) {
            $taskCode = trim($t['task_code'] ?? '');
            if (empty($taskCode)) continue;

            $status = $statusMap[$t['task_status'] ?? ''] ?? 'todo';
            $assignedTo = $userMap[mb_strtolower(trim($t['receiver_name'] ?? ''))] ?? null;
            $createdBy = $userMap[mb_strtolower(trim($t['creator_name'] ?? ''))] ?? null;

            $desc = trim($t['task_description'] ?? '');
            $desc = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $desc));
            $desc = html_entity_decode($desc, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $desc = preg_replace('/\n{3,}/', "\n\n", trim($desc));

            $data = [
                'tenant_id' => $tid,
                'task_code' => $taskCode,
                'title' => html_entity_decode(trim($t['task_name'] ?? 'Không tiêu đề'), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'description' => $desc,
                'status' => $status,
                'progress' => (int)($t['task_progress'] ?? 0),
                'start_date' => $t['task_start_date'] ?? null,
                'due_date' => $t['task_end_date'] ?? null,
                'color' => ($t['task_color'] ?? '#ffffff') !== '#ffffff' ? $t['task_color'] : null,
                'is_important' => (int)($t['star'] ?? 0),
                'assigned_to' => $assignedTo,
                'created_by' => $createdBy,
            ];

            $existing = Database::fetch("SELECT id FROM tasks WHERE task_code = ? AND tenant_id = ?", [$taskCode, $tid]);
            if ($existing) {
                Database::update('tasks', [
                    'title' => $data['title'], 'description' => $data['description'],
                    'status' => $data['status'], 'progress' => $data['progress'],
                    'due_date' => $data['due_date'], 'assigned_to' => $data['assigned_to'],
                ], 'id = ?', [$existing['id']]);
            } else {
                if ($status === 'done') $data['completed_at'] = $data['due_date'] ?? date('Y-m-d H:i:s');
                Database::insert('tasks', $data);
            }
            $synced++;
        }

        $hasMore = count($tasks) >= $perPage;
        return $this->json([
            'done' => !$hasMore,
            'synced' => $synced,
            'page' => $page,
            'has_more' => $hasMore,
        ]);
    }

    private function syncPlaceholder(array $config, string $endpoint): int
    {
        throw new \Exception('Chức năng đồng bộ đang được phát triển.');
    }

    /**
     * Sync accounts page by page
     */
    public function syncAccountsPage()
    {
        if (!$this->isPost()) return $this->json(['error' => 'Invalid'], 400);
        $this->authorize('settings', 'manage');
        $config = $this->getConfig();
        if (!$config) return $this->json(['error' => 'Chưa cấu hình'], 400);

        $page = max(1, (int)$this->input('page'));
        $tid = Database::tenantId();

        $url = $config['api_domain'] . '/api/v3/accounts?page=' . $page . '&num_per_page=50';
        $result = $this->callApi($config['api_key'], $url);
        if (!$result['success']) return $this->json(['error' => $result['error']], 400);

        $records = $result['data']['records'] ?? [];
        if (empty($records)) return $this->json(['done' => true, 'synced' => 0, 'page' => $page]);

        $totalPages = (int)($result['data']['pagination']['total_page'] ?? 0);

        // User map
        $userMap = [];
        $users = Database::fetchAll("SELECT id, name, email FROM users WHERE is_active = 1");
        foreach ($users as $u) {
            $userMap[mb_strtolower($u['name'])] = $u['id'];
            if ($u['email']) $userMap[mb_strtolower($u['email'])] = $u['id'];
        }

        // Source map
        $sourceMap = [];
        $sources = Database::fetchAll("SELECT id, name FROM contact_sources");
        foreach ($sources as $s) $sourceMap[mb_strtolower($s['name'])] = $s['id'];

        // Status map
        $statusMap = [];
        $statuses = Database::fetchAll("SELECT slug, name FROM contact_statuses WHERE tenant_id = ?", [$tid]);
        foreach ($statuses as $s) $statusMap[mb_strtolower(trim($s['name']))] = $s['slug'];

        $synced = 0;
        foreach ($records as $r) {
            $code = trim($r['account_code'] ?? '');
            if (empty($code)) continue;

            $ownerEmail = mb_strtolower(trim($r['manager_email'] ?? ''));
            $ownerId = $userMap[$ownerEmail] ?? null;
            $sourceId = $sourceMap[mb_strtolower(trim($r['account_source'] ?? ''))] ?? null;
            $statusSlug = $statusMap[mb_strtolower(trim($r['relation_name'] ?? ''))] ?? 'new';

            $data = [
                'tenant_id' => $tid,
                'account_code' => $code,
                'company_name' => html_entity_decode(trim($r['account_name'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'phone' => trim($r['phone'] ?? '') ?: null,
                'email' => trim($r['email'] ?? '') ?: null,
                'address' => html_entity_decode(trim($r['address'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'website' => trim($r['website'] ?? '') ?: null,
                'tax_code' => trim($r['sic_code'] ?? '') ?: null,
                'description' => html_entity_decode(strip_tags(trim($r['description'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'status' => $statusSlug,
                'customer_group' => trim($r['account_type'] ?? '') ?: null,
                'province' => trim($r['province_name'] ?? '') ?: null,
                'industry' => trim($r['industry_name'] ?? '') ?: null,
                'total_revenue' => (float)str_replace(',', '', $r['revenue'] ?? '0'),
                'source_id' => $sourceId,
                'owner_id' => $ownerId,
            ];

            $existing = Database::fetch("SELECT id FROM contacts WHERE account_code = ? AND tenant_id = ?", [$code, $tid]);
            if ($existing) {
                Database::update('contacts', $data, 'id = ?', [$existing['id']]);
                $contactId = $existing['id'];
            } else {
                $parts = explode(' ', $data['company_name'], 2);
                $data['first_name'] = $parts[0];
                $data['last_name'] = $parts[1] ?? '';
                $data['created_by'] = $ownerId;
                $contactId = Database::insert('contacts', $data);
            }

            // Sync contact persons
            $contacts = $r['contacts'] ?? [];
            if (!empty($contacts)) {
                Database::query("DELETE FROM contact_persons WHERE contact_id = ?", [$contactId]);
                foreach ($contacts as $idx => $cp) {
                    $cpName = trim($cp['first_name'] ?? '');
                    if (empty($cpName)) continue;
                    Database::insert('contact_persons', [
                        'tenant_id' => $tid,
                        'contact_id' => $contactId,
                        'full_name' => html_entity_decode($cpName, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                        'phone' => trim($cp['phone_mobile'] ?? $cp['phone_home'] ?? '') ?: null,
                        'email' => trim($cp['email'] ?? '') ?: null,
                        'position' => html_entity_decode(trim($cp['title'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?: null,
                        'is_primary' => $idx === 0 ? 1 : 0,
                        'sort_order' => $idx,
                    ]);
                }
            }
            $synced++;
        }

        return $this->json([
            'done' => $page >= $totalPages,
            'synced' => $synced,
            'page' => $page,
            'total_pages' => $totalPages,
            'has_more' => $page < $totalPages,
        ]);
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
            CURLOPT_TIMEOUT => 120,
            CURLOPT_CONNECTTIMEOUT => 15,
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
