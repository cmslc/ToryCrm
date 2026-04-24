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
                'tasks' => 0, // handled by syncTasksPage
                'accounts' => 0, // handled by syncAccountsPage
                'products' => 0, // handled by syncProductsPage
                'users' => $this->syncUsers($config),
                'campaigns' => $this->syncCampaigns($config),
                'orders_sale' => 0, // handled by syncOrdersPage
                'orders_purchase' => 0, // handled by syncOrdersPage
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
        $errors = 0;
        foreach ($records as $r) {
          try {
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
                'gender' => match($r['gender'] ?? '') { '2' => 'male', '1' => 'female', default => null },
                'date_of_birth' => !empty($r['birthday']) ? $r['birthday'] : null,
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

            // Sync contact persons — dùng PersonService để link vào bảng persons toàn cục
            $contacts = $r['contacts'] ?? [];
            if (!empty($contacts)) {
                Database::query("DELETE FROM contact_persons WHERE contact_id = ?", [$contactId]);
                foreach ($contacts as $idx => $cp) {
                    $cpName = trim($cp['first_name'] ?? '');
                    if (empty($cpName)) continue;
                    $cpName = html_entity_decode($cpName, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                    // Detect title (danh xưng) from name prefix
                    $cpTitle = null;
                    $prefixes = ['anh ' => 'anh', 'chị ' => 'chị', 'ông ' => 'ông', 'bà ' => 'bà', 'mr.' => 'anh', 'mr ' => 'anh', 'ms.' => 'chị', 'ms ' => 'chị', 'mrs.' => 'chị', 'em ' => 'chị'];
                    $nameLower = mb_strtolower($cpName);
                    foreach ($prefixes as $prefix => $title) {
                        if (str_starts_with($nameLower, $prefix)) {
                            $cpTitle = $title;
                            $cpName = trim(mb_substr($cpName, mb_strlen($prefix)));
                            break;
                        }
                    }
                    // Fallback: detect from gender
                    if (!$cpTitle && ($r['gender'] ?? '') === '2') $cpTitle = 'anh';
                    if (!$cpTitle && ($r['gender'] ?? '') === '1') $cpTitle = 'chị';

                    $cpPhone = trim($cp['phone_mobile'] ?? $cp['phone_home'] ?? '') ?: null;
                    $cpEmail = trim($cp['email'] ?? '') ?: null;
                    $personId = \App\Services\PersonService::findOrCreate($tid, $cpPhone, $cpEmail, $cpName);

                    Database::insert('contact_persons', [
                        'tenant_id' => $tid,
                        'contact_id' => $contactId,
                        'person_id' => $personId,
                        'title' => $cpTitle,
                        'full_name' => $cpName,
                        'phone' => $cpPhone,
                        'email' => $cpEmail,
                        'position' => html_entity_decode(trim($cp['title'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?: null,
                        'is_primary' => $idx === 0 ? 1 : 0,
                        'is_active' => 1,
                        'sort_order' => $idx,
                    ]);
                }
            }
            $synced++;
          } catch (\Exception $e) {
            $errors++;
          }
        }

        return $this->json([
            'done' => $page >= $totalPages,
            'synced' => $synced,
            'errors' => $errors,
            'page' => $page,
            'total_pages' => $totalPages,
            'has_more' => $page < $totalPages,
        ]);
    }

    /**
     * Sync products page by page.
     * On page 1, also refreshes reference tables (categories, origins, manufacturers)
     * and builds an in-memory units map — these are small, cheap to fetch once per sync run.
     */
    public function syncProductsPage()
    {
        if (!$this->isPost()) return $this->json(['error' => 'Invalid'], 400);
        $this->authorize('settings', 'manage');
        $config = $this->getConfig();
        if (!$config) return $this->json(['error' => 'Chưa cấu hình'], 400);

        // Disable PHP 30s limit — page may download 20 images serially
        @set_time_limit(0);

        $page = max(1, (int)$this->input('page'));
        $tid = Database::tenantId();

        // Sync reference tables on page 1 so subsequent pages can resolve FKs
        if ($page === 1) {
            try { $this->syncProductReferences($config, $tid); } catch (\Exception $e) {}
        }

        // Build lookup maps (cheap queries, small tables)
        $catMap = $this->buildRefMap('product_categories', $tid);
        $mfgMap = $this->buildRefMap('product_manufacturers', $tid);
        $orgMap = $this->buildRefMap('product_origins', $tid);
        $unitMap = $this->fetchUnitsMap($config);

        $url = $config['api_domain'] . '/api/v3/products?page=' . $page . '&num_per_page=20';
        $result = $this->callApi($config['api_key'], $url);
        if (!$result['success']) return $this->json(['error' => $result['error']], 400);

        $records = $result['data']['records'] ?? [];
        if (empty($records)) return $this->json(['done' => true, 'synced' => 0, 'page' => $page]);

        $totalPages = (int)($result['data']['pagination']['total_page'] ?? 0);
        $synced = 0;

        foreach ($records as $r) {
            $sku = trim($r['product_code'] ?? $r['sku'] ?? '');
            $name = html_entity_decode(trim($r['product_name'] ?? $r['name'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (empty($name)) continue;

            // Fix: decode FIRST, then strip_tags (Getfly returns HTML-encoded HTML)
            $descRaw = html_entity_decode(trim($r['description'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $desc = trim(strip_tags($descRaw));
            $shortDescRaw = html_entity_decode(trim($r['short_description'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $shortDesc = trim(strip_tags($shortDescRaw));

            // Price: Getfly's real field is cover_price, not price/unit_price
            $price = (float) str_replace(',', '', (string)($r['cover_price'] ?? $r['price'] ?? '0'));
            $costPrice = (float) str_replace(',', '', (string)($r['price_average_in'] ?? '0'));
            $priceWholesale = (float) str_replace(',', '', (string)($r['price_wholesale'] ?? '0'));
            $priceOnline = (float) str_replace(',', '', (string)($r['price_online'] ?? '0'));
            $saleoff = (float) str_replace(',', '', (string)($r['saleoff_price'] ?? '0'));
            $discount = (float) str_replace(',', '', (string)($r['discount'] ?? '0'));
            $vat = (float) str_replace(',', '', (string)($r['product_vat'] ?? '0'));
            $weight = ($r['weight'] !== null && $r['weight'] !== '') ? (float) $r['weight'] : null;

            // Image: prefer images[0].origin_src, fall back to thumbnail_file
            $imageUrl = null; $featuredUrl = null;
            if (!empty($r['images']) && is_array($r['images']) && !empty($r['images'][0])) {
                $imageUrl = $r['images'][0]['origin_src'] ?? null;
                $featuredUrl = $r['images'][0]['thumb_src'] ?? ($r['thumbnail_file'] ?? null);
            } elseif (!empty($r['thumbnail_file'])) {
                $featuredUrl = $r['thumbnail_file'];
            }

            // Download to local so we don't depend on Getfly uptime later
            $getflyProductId = (int)($r['product_id'] ?? 0);
            $image = $imageUrl ? $this->downloadProductImage($imageUrl, $getflyProductId, $config) : null;
            $featured = $featuredUrl ? $this->downloadProductImage($featuredUrl, $getflyProductId, $config, 'thumb') : null;

            // Resolve FK IDs from Getfly IDs via our maps (null if not found)
            $categoryId = $catMap[(int)($r['category_id'] ?? 0)] ?? null;
            $originId = $orgMap[(int)($r['origin_id'] ?? 0)] ?? null;
            $mfgId = $mfgMap[(int)($r['manufacturer_id'] ?? 0)] ?? null;

            // Unit: Getfly gives unit_id only; resolve to name string for our schema
            $unitId = (int)($r['unit_id'] ?? 0);
            $unit = $unitMap[$unitId] ?? null;

            // Type: services=1 means service, else physical product
            $type = ($r['services'] ?? '0') === '1' ? 'service' : 'product';

            $data = [
                'tenant_id' => $tid,
                'getfly_id' => (int)($r['product_id'] ?? 0) ?: null,
                'sku' => $sku ?: null,
                'name' => $name,
                'type' => $type,
                'category_id' => $categoryId,
                'origin_id' => $originId,
                'manufacturer_id' => $mfgId,
                'unit' => $unit,
                'short_description' => $shortDesc ?: null,
                'description' => $desc,
                'price' => $price,
                'cost_price' => $costPrice,
                'price_wholesale' => $priceWholesale,
                'price_online' => $priceOnline,
                'saleoff_price' => $saleoff ?: null,
                'discount_percent' => $discount,
                'tax_rate' => $vat,
                'weight' => $weight,
                'image' => $image,
                'featured_image' => $featured,
            ];

            // Prefer getfly_id match; fall back to sku for legacy rows
            $existing = null;
            if (!empty($data['getfly_id'])) {
                $existing = Database::fetch(
                    "SELECT id FROM products WHERE getfly_id = ? AND tenant_id = ?",
                    [$data['getfly_id'], $tid]
                );
            }
            if (!$existing && $sku) {
                $existing = Database::fetch(
                    "SELECT id FROM products WHERE sku = ? AND tenant_id = ?",
                    [$sku, $tid]
                );
            }

            if ($existing) {
                Database::update('products', $data, 'id = ?', [$existing['id']]);
            } else {
                Database::insert('products', $data);
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

    /**
     * Sync product reference tables (categories, origins, manufacturers).
     * Each endpoint returns small data (<100 records typical) — single call each.
     * Upserts by (tenant_id, getfly_id) unique key.
     */
    private function syncProductReferences(array $config, int $tid): void
    {
        // Categories
        $r = $this->callApi($config['api_key'], $config['api_domain'] . '/api/v3/products/categories');
        if ($r['success'] && is_array($r['data'])) {
            foreach ($r['data'] as $c) {
                $gid = (int)($c['category_id'] ?? 0);
                if ($gid <= 0) continue;
                $name = html_entity_decode(trim($c['category_name'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if ($name === '') continue;
                Database::query(
                    "INSERT INTO product_categories (tenant_id, getfly_id, name, description)
                     VALUES (?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description)",
                    [$tid, $gid, $name, $c['description'] ?? null]
                );
            }
        }

        // Manufacturers
        $r = $this->callApi($config['api_key'], $config['api_domain'] . '/api/v3/products/manufacturers');
        if ($r['success'] && is_array($r['data'])) {
            foreach ($r['data'] as $m) {
                $gid = (int)($m['manufacturer_id'] ?? 0);
                if ($gid <= 0) continue;
                $name = html_entity_decode(trim($m['manufacturer_name'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if ($name === '') continue;
                Database::query(
                    "INSERT INTO product_manufacturers (tenant_id, getfly_id, name)
                     VALUES (?, ?, ?)
                     ON DUPLICATE KEY UPDATE name = VALUES(name)",
                    [$tid, $gid, $name]
                );
            }
        }

        // Origins
        $r = $this->callApi($config['api_key'], $config['api_domain'] . '/api/v3/products/origins');
        if ($r['success'] && is_array($r['data'])) {
            foreach ($r['data'] as $o) {
                $gid = (int)($o['origin_id'] ?? 0);
                if ($gid <= 0) continue;
                $name = html_entity_decode(trim($o['origin_name'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if ($name === '') continue;
                Database::query(
                    "INSERT INTO product_origins (tenant_id, getfly_id, name)
                     VALUES (?, ?, ?)
                     ON DUPLICATE KEY UPDATE name = VALUES(name)",
                    [$tid, $gid, $name]
                );
            }
        }
    }

    /** Build a [getfly_id => local_id] map for a reference table. */
    private function buildRefMap(string $table, int $tid): array
    {
        $rows = Database::fetchAll(
            "SELECT id, getfly_id FROM {$table} WHERE tenant_id = ? AND getfly_id IS NOT NULL",
            [$tid]
        );
        $map = [];
        foreach ($rows as $row) {
            $map[(int)$row['getfly_id']] = (int)$row['id'];
        }
        return $map;
    }

    /** Fetch [unit_id => unit_name] from Getfly (no local table — we store the name string). */
    /**
     * Import dimensions, color, weight from a Getfly Excel product export.
     * These 3 fields are NOT returned by Getfly API v3, only via Excel export,
     * so this endpoint fills the gap.
     *
     * Expected columns (from Getfly's danhsachsanpham.xlsx export):
     *   A: MÃ SẢN PHẨM (SKU)
     *   T: KÍCH THƯỚC (dimensions)
     *   U: MÀU SẮC (color)
     *   V: TRỌNG LƯỢNG (GRAM) — converted to kg
     *
     * Matches by SKU, updates only non-empty Excel cells (won't wipe manually-edited data).
     */
    public function importProductExcel()
    {
        if (!$this->isPost()) return $this->redirect('settings/getfly-sync');
        $this->authorize('settings', 'manage');

        // Excel import can do ~6k SKU lookups + ~4k updates → often > 30s
        @set_time_limit(0);
        @ini_set('memory_limit', '256M');

        if (empty($_FILES['excel']['tmp_name']) || $_FILES['excel']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('error', 'Vui lòng chọn file Excel.');
            return $this->redirect('settings/getfly-sync');
        }

        $tmp = $_FILES['excel']['tmp_name'];
        $origName = $_FILES['excel']['name'] ?? '';
        if (!preg_match('/\.xlsx$/i', $origName)) {
            $this->setFlash('error', 'Chỉ chấp nhận file .xlsx (không phải .xls).');
            return $this->redirect('settings/getfly-sync');
        }

        try {
            $rows = \App\Services\XlsxReader::readAllRows($tmp);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Không đọc được file: ' . $e->getMessage());
            return $this->redirect('settings/getfly-sync');
        }

        if (count($rows) < 2) {
            $this->setFlash('error', 'File rỗng hoặc không đúng định dạng.');
            return $this->redirect('settings/getfly-sync');
        }

        // Validate header matches Getfly export format
        $header = $rows[0] ?? [];
        $expectedA = trim($header['A'] ?? '');
        if (stripos($expectedA, 'MÃ SẢN PHẨM') === false) {
            $this->setFlash('error', 'Header không khớp. Cần file export sản phẩm từ Getfly (cột A: MÃ SẢN PHẨM).');
            return $this->redirect('settings/getfly-sync');
        }

        $tid = Database::tenantId();

        // Preload SKU → id map in one query (avoids N+1 fetches)
        $skuRows = Database::fetchAll(
            "SELECT id, sku FROM products WHERE tenant_id = ? AND is_deleted = 0 AND sku IS NOT NULL",
            [$tid]
        );
        $skuMap = [];
        foreach ($skuRows as $r) {
            $skuMap[$r['sku']] = (int) $r['id'];
        }
        unset($skuRows);

        $updated = 0; $notFound = 0; $skipped = 0;
        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            for ($i = 1, $n = count($rows); $i < $n; $i++) {
                $row = $rows[$i];
                $sku = trim($row['A'] ?? '');
                if ($sku === '') { $skipped++; continue; }

                $dimensions = trim($row['T'] ?? '');
                $color = trim($row['U'] ?? '');
                $weightGramRaw = trim($row['V'] ?? '');

                if ($dimensions === '' && $color === '' && ($weightGramRaw === '' || $weightGramRaw === '0')) {
                    $skipped++;
                    continue;
                }

                $update = [];
                if ($dimensions !== '' && $dimensions !== '0') $update['dimensions'] = mb_substr($dimensions, 0, 255);
                if ($color !== '' && $color !== '0') $update['color'] = mb_substr($color, 0, 100);
                if ($weightGramRaw !== '' && is_numeric($weightGramRaw) && (float)$weightGramRaw > 0) {
                    $update['weight'] = round((float)$weightGramRaw / 1000, 3);
                }

                if (empty($update)) { $skipped++; continue; }

                if (!isset($skuMap[$sku])) { $notFound++; continue; }

                Database::update('products', $update, 'id = ?', [$skuMap[$sku]]);
                $updated++;
            }
            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            $this->setFlash('error', 'Lỗi khi cập nhật: ' . $e->getMessage());
            return $this->redirect('settings/getfly-sync');
        }

        $this->setFlash('success', sprintf(
            'Đã cập nhật %d sản phẩm từ Excel. Bỏ qua %d dòng (trống), không khớp SKU %d dòng.',
            $updated, $skipped, $notFound
        ));
        return $this->redirect('settings/getfly-sync');
    }

    /**
     * Import quotations from a Getfly Excel quote export (danhsachbaogia / databaogia).
     *
     * File shape (observed):
     *   - Header row (has column C=customer code):
     *     A=quote_number, C=account_code, D=company_name, E=phone,
     *     F=status ("Đã duyệt"/"Chờ duyệt"), G=date (dd/mm/yyyy), H=owner_name,
     *     I=creator_name, J=first product SKU, K=name, L=desc, M=unit, N=category,
     *     O=quote grand total (formatted "29,808,000")
     *   - Continuation row (C empty): A=product SKU, K=name, L=desc, M=unit, N=category
     *
     * Match strategy:
     *   - Contact by account_code (column C)
     *   - Owner/creator by full_name (column H/I)
     *   - Upsert quotation by quote_number (overwrite line items on re-import)
     */
    public function importQuotationExcel()
    {
        if (!$this->isPost()) return $this->redirect('settings/getfly-sync');
        $this->authorize('settings', 'manage');

        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        if (empty($_FILES['excel']['tmp_name']) || $_FILES['excel']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('error', 'Vui lòng chọn file Excel.');
            return $this->redirect('settings/getfly-sync');
        }

        $origName = $_FILES['excel']['name'] ?? '';
        if (!preg_match('/\.xlsx$/i', $origName)) {
            $this->setFlash('error', 'Chỉ chấp nhận file .xlsx.');
            return $this->redirect('settings/getfly-sync');
        }

        try {
            $rows = \App\Services\XlsxReader::readAllRows($_FILES['excel']['tmp_name']);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Không đọc được file: ' . $e->getMessage());
            return $this->redirect('settings/getfly-sync');
        }

        if (count($rows) < 2) {
            $this->setFlash('error', 'File rỗng.');
            return $this->redirect('settings/getfly-sync');
        }

        // Header validation
        $header = $rows[0] ?? [];
        if (stripos($header['A'] ?? '', 'STT') === false && stripos($header['C'] ?? '', 'khách hàng') === false) {
            $this->setFlash('error', 'Header file không đúng định dạng export báo giá của Getfly.');
            return $this->redirect('settings/getfly-sync');
        }

        $tid = Database::tenantId();

        // Preload lookup maps
        $contactMap = [];
        foreach (Database::fetchAll(
            "SELECT id, account_code FROM contacts WHERE tenant_id = ? AND is_deleted = 0 AND account_code IS NOT NULL",
            [$tid]
        ) as $r) { $contactMap[$r['account_code']] = (int)$r['id']; }

        $userMap = [];
        foreach (Database::fetchAll(
            "SELECT id, name FROM users WHERE tenant_id = ? AND is_active = 1",
            [$tid]
        ) as $u) {
            $userMap[trim(mb_strtolower($u['name']))] = (int)$u['id'];
        }

        $productSkuMap = [];
        foreach (Database::fetchAll(
            "SELECT id, sku FROM products WHERE tenant_id = ? AND is_deleted = 0 AND sku IS NOT NULL",
            [$tid]
        ) as $p) { $productSkuMap[$p['sku']] = (int)$p['id']; }

        // Group rows into quotes
        $quotes = [];
        $current = null;
        for ($i = 1, $n = count($rows); $i < $n; $i++) {
            $row = $rows[$i];
            $hasHeader = !empty(trim($row['C'] ?? ''));
            if ($hasHeader) {
                if ($current) $quotes[] = $current;
                $current = [
                    'quote_number' => trim($row['A'] ?? ''),
                    'account_code' => trim($row['C'] ?? ''),
                    'company_name' => trim($row['D'] ?? ''),
                    'phone' => trim($row['E'] ?? ''),
                    'status_raw' => trim($row['F'] ?? ''),
                    'date_raw' => trim($row['G'] ?? ''),
                    'owner_name' => trim($row['H'] ?? ''),
                    'creator_name' => trim($row['I'] ?? ''),
                    'total_raw' => trim($row['O'] ?? ''),
                    'items' => [],
                ];
                // First product in same row
                $firstSku = trim($row['J'] ?? '');
                $firstName = trim($row['K'] ?? '');
                if ($firstName !== '') {
                    $current['items'][] = [
                        'sku' => $firstSku,
                        'name' => $firstName,
                        'description' => trim($row['L'] ?? ''),
                        'unit' => trim($row['M'] ?? ''),
                        'category' => trim($row['N'] ?? ''),
                    ];
                }
            } elseif ($current) {
                // Continuation row — SKU is in A
                $sku = trim($row['A'] ?? '');
                $name = trim($row['K'] ?? '');
                if ($name !== '') {
                    $current['items'][] = [
                        'sku' => $sku,
                        'name' => $name,
                        'description' => trim($row['L'] ?? ''),
                        'unit' => trim($row['M'] ?? ''),
                        'category' => trim($row['N'] ?? ''),
                    ];
                }
            }
        }
        if ($current) $quotes[] = $current;

        $statusMap = [
            'Đã duyệt' => 'approved',
            'Chờ duyệt' => 'pending',
            'Nháp' => 'draft',
            'Đã gửi' => 'sent',
            'Đã hủy' => 'cancelled',
        ];

        $created = 0; $updated = 0; $skippedNoContact = 0; $skippedNoNumber = 0;
        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            // ---- Phase 1: normalize + validate rows ----
            $validQuotes = [];
            $quoteNumbers = [];
            foreach ($quotes as $q) {
                if ($q['quote_number'] === '') { $skippedNoNumber++; continue; }
                $contactId = $contactMap[$q['account_code']] ?? null;
                if (!$contactId) { $skippedNoContact++; continue; }

                $createdAt = null;
                if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $q['date_raw'], $m)) {
                    $createdAt = sprintf('%04d-%02d-%02d 00:00:00', $m[3], $m[2], $m[1]);
                }
                $q['_contact_id'] = $contactId;
                $q['_owner_id'] = $userMap[mb_strtolower($q['owner_name'])] ?? null;
                $q['_creator_id'] = $userMap[mb_strtolower($q['creator_name'])] ?? null;
                $q['_status'] = $statusMap[$q['status_raw']] ?? 'draft';
                $q['_created_at'] = $createdAt;
                $q['_total'] = (float) str_replace([',', '.', ' '], '', $q['total_raw']);
                $validQuotes[] = $q;
                $quoteNumbers[] = $q['quote_number'];
            }

            // ---- Phase 2: batch-fetch all existing quote IDs by number ----
            $existingIdByNumber = [];
            if ($quoteNumbers) {
                foreach (array_chunk($quoteNumbers, 1000) as $chunk) {
                    $ph = implode(',', array_fill(0, count($chunk), '?'));
                    $rows2 = Database::fetchAll(
                        "SELECT id, quote_number FROM quotations
                         WHERE tenant_id = ? AND quote_number IN ($ph)",
                        array_merge([$tid], $chunk)
                    );
                    foreach ($rows2 as $r) $existingIdByNumber[$r['quote_number']] = (int)$r['id'];
                }
            }

            // ---- Phase 3: split insert vs update; run updates per-row ----
            $insertPayload = [];
            $updatedQuoteIds = []; // to wipe their old items in bulk

            foreach ($validQuotes as $q) {
                $qData = [
                    'quote_number' => $q['quote_number'],
                    'contact_id' => $q['_contact_id'],
                    'contact_phone' => $q['phone'] ?: null,
                    'status' => $q['_status'],
                    'owner_id' => $q['_owner_id'],
                    'total' => $q['_total'],
                    'subtotal' => $q['_total'],
                    'currency' => 'VND',
                    'notes' => 'Imported from Getfly Excel export',
                    'tenant_id' => $tid,
                ];
                if ($q['_created_at']) $qData['created_at'] = $q['_created_at'];

                if (isset($existingIdByNumber[$q['quote_number']])) {
                    $qid = $existingIdByNumber[$q['quote_number']];
                    Database::update('quotations', $qData, 'id = ?', [$qid]);
                    $updatedQuoteIds[] = $qid;
                    $updated++;
                } else {
                    $qData['created_by'] = $q['_creator_id'] ?: $this->userId();
                    $insertPayload[] = $qData;
                    $created++;
                }
            }

            // ---- Phase 4: bulk DELETE old items for updated quotes ----
            if ($updatedQuoteIds) {
                foreach (array_chunk($updatedQuoteIds, 1000) as $chunk) {
                    $ph = implode(',', array_fill(0, count($chunk), '?'));
                    Database::query("DELETE FROM quotation_items WHERE quotation_id IN ($ph)", $chunk);
                }
            }

            // ---- Phase 5: bulk INSERT new quotations ----
            if ($insertPayload) {
                Database::insertBatch('quotations', $insertPayload, 500);
                // Re-fetch generated IDs (safer than relying on lastInsertId sequence)
                $newNumbers = array_column($insertPayload, 'quote_number');
                foreach (array_chunk($newNumbers, 1000) as $chunk) {
                    $ph = implode(',', array_fill(0, count($chunk), '?'));
                    foreach (Database::fetchAll(
                        "SELECT id, quote_number FROM quotations WHERE tenant_id = ? AND quote_number IN ($ph)",
                        array_merge([$tid], $chunk)
                    ) as $r) {
                        $existingIdByNumber[$r['quote_number']] = (int)$r['id'];
                    }
                }
            }

            // ---- Phase 6: bulk INSERT all line items ----
            $itemPayload = [];
            foreach ($validQuotes as $q) {
                $qid = $existingIdByNumber[$q['quote_number']] ?? null;
                if (!$qid) continue;
                $sort = 0;
                foreach ($q['items'] as $item) {
                    $productId = !empty($item['sku']) ? ($productSkuMap[$item['sku']] ?? null) : null;
                    $itemPayload[] = [
                        'quotation_id' => $qid,
                        'product_id' => $productId,
                        'product_name' => mb_substr($item['name'], 0, 255),
                        'description' => $item['description'] !== '' ? mb_substr($item['description'], 0, 65000) : null,
                        'unit' => $item['unit'] !== '' ? mb_substr($item['unit'], 0, 50) : null,
                        'quantity' => 1,
                        'unit_price' => 0,
                        'total' => 0,
                        'sort_order' => $sort++,
                    ];
                }
            }
            if ($itemPayload) {
                Database::insertBatch('quotation_items', $itemPayload, 1000);
            }

            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            $this->setFlash('error', 'Lỗi: ' . $e->getMessage());
            return $this->redirect('settings/getfly-sync');
        }

        $this->setFlash('success', sprintf(
            'Báo giá: %d tạo mới, %d cập nhật. Bỏ qua %d (không tìm KH), %d (thiếu số báo giá).',
            $created, $updated, $skippedNoContact, $skippedNoNumber
        ));
        return $this->redirect('settings/getfly-sync');
    }

    private function fetchUnitsMap(array $config): array
    {
        $r = $this->callApi($config['api_key'], $config['api_domain'] . '/api/v3/products/units');
        $map = [];
        if ($r['success'] && is_array($r['data'])) {
            foreach ($r['data'] as $u) {
                $uid = (int)($u['unit_id'] ?? 0);
                if ($uid <= 0) continue;
                $map[$uid] = html_entity_decode(trim($u['unit_name'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }
        return $map;
    }

    /**
     * Download an image from Getfly to the local uploads dir so we don't
     * depend on Getfly's availability or CDN long-term.
     *
     * Returns the local filename on success, or the original URL as fallback
     * if download failed (so the image still renders via the remote link).
     *
     * Stores as /uploads/products/gf_<product_id>[_thumb].<ext>. If the file
     * already exists (same product_id), re-uses it — the getfly_id is stable
     * per product.
     */
    private function downloadProductImage(string $url, int $getflyProductId, array $config, string $suffix = ''): ?string
    {
        if ($getflyProductId <= 0 || !filter_var($url, FILTER_VALIDATE_URL)) return $url ?: null;

        // SSRF defense: only accept images from the configured Getfly domain
        $urlHost = parse_url($url, PHP_URL_HOST);
        $cfgHost = parse_url($config['api_domain'] ?? '', PHP_URL_HOST);
        if (!$urlHost || !$cfgHost || $urlHost !== $cfgHost) return $url;

        $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) $ext = 'jpg';

        $filename = 'gf_' . $getflyProductId . ($suffix ? '_' . $suffix : '') . '.' . $ext;
        $dir = BASE_PATH . '/public/uploads/products/';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $dest = $dir . $filename;

        // Skip if already downloaded this run/previously
        if (file_exists($dest) && filesize($dest) > 100) return $filename;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $data = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE) ?? '';
        curl_close($ch);

        if (!$data || $code !== 200 || strlen($data) < 100 || !str_starts_with($contentType, 'image/')) {
            return $url; // Fallback to remote URL
        }

        if (@file_put_contents($dest, $data) === false) return $url;
        return $filename;
    }

    /**
     * Sync users with departments (single call, small data)
     */
    private function syncUsers(array $config): int
    {
        $url = $config['api_domain'] . '/api/v3/users';
        $result = $this->callApi($config['api_key'], $url);
        if (!$result['success']) throw new \Exception($result['error']);

        $users = is_array($result['data']) && isset($result['data'][0]) ? $result['data'] : [];
        $tid = Database::tenantId();
        $synced = 0;

        // Build department map: sync departments first
        $deptMap = [];
        $existingDepts = Database::fetchAll("SELECT id, name FROM departments WHERE tenant_id = ?", [$tid]);
        foreach ($existingDepts as $d) {
            $deptMap[mb_strtolower(trim($d['name']))] = $d['id'];
        }

        foreach ($users as $u) {
            $email = trim($u['email'] ?? '');
            if (empty($email)) continue;

            $name = html_entity_decode(trim($u['name'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $mobile = trim($u['mobile'] ?? '') ?: null;
            $deptName = html_entity_decode(trim($u['dept_name'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');

            // Ensure department exists
            $deptId = null;
            if (!empty($deptName)) {
                $deptKey = mb_strtolower($deptName);
                if (isset($deptMap[$deptKey])) {
                    $deptId = $deptMap[$deptKey];
                } else {
                    $deptId = Database::insert('departments', [
                        'tenant_id' => $tid,
                        'name' => $deptName,
                    ]);
                    $deptMap[$deptKey] = $deptId;
                }
            }

            $existing = Database::fetch("SELECT id FROM users WHERE email = ?", [$email]);
            if ($existing) {
                Database::update('users', [
                    'name' => $name,
                    'phone' => $mobile,
                    'department_id' => $deptId,
                ], 'id = ?', [$existing['id']]);
            } else {
                // Create new user
                try {
                    Database::insert('users', [
                        'tenant_id' => $tid,
                        'name' => $name,
                        'email' => $email,
                        'password' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
                        'phone' => $mobile,
                        'department_id' => $deptId,
                        'role_id' => 2, // default staff role
                        'is_active' => 1,
                        'created_by' => $this->userId(),
                    ]);
                } catch (\Exception $e) {
                    // skip duplicate or error
                }
            }
            $synced++;
        }
        return $synced;
    }

    /**
     * Sync campaigns (single call, small data)
     */
    private function syncCampaigns(array $config): int
    {
        $url = $config['api_domain'] . '/api/v3/campaigns';
        $result = $this->callApi($config['api_key'], $url);
        if (!$result['success']) throw new \Exception($result['error']);

        $campaigns = is_array($result['data']) && isset($result['data'][0]) ? $result['data'] : [];
        $tid = Database::tenantId();
        $synced = 0;

        foreach ($campaigns as $c) {
            $name = html_entity_decode(trim($c['campaign_name'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (empty($name)) continue;

            $existing = Database::fetch("SELECT id FROM campaigns WHERE name = ? AND tenant_id = ?", [$name, $tid]);
            if (!$existing) {
                try {
                    Database::insert('campaigns', [
                        'tenant_id' => $tid,
                        'name' => $name,
                        'description' => trim($c['description'] ?? '') ?: null,
                        'start_date' => $c['start_date'] ?? null,
                        'end_date' => $c['end_date'] ?? null,
                        'status' => 'active',
                        'created_by' => $this->userId(),
                    ]);
                } catch (\Exception $e) {}
            }
            $synced++;
        }
        return $synced;
    }

    /**
     * Sync orders month by month (API returns ALL records per date range, no pagination)
     */
    public function syncOrdersPage()
    {
        if (!$this->isPost()) return $this->json(['error' => 'Invalid'], 400);
        $this->authorize('settings', 'manage');
        $config = $this->getConfig();
        if (!$config) return $this->json(['error' => 'Chưa cấu hình'], 400);

        $page = max(0, (int)$this->input('page')); // page = month offset from start
        $orderType = $this->input('order_type') ?: '2';
        $tid = Database::tenantId();

        // Calculate month range: start from 2021-01 + page months
        $startYear = 2021;
        $startMonth = 1;
        $monthOffset = $page;
        $year = $startYear + intdiv($startMonth - 1 + $monthOffset, 12);
        $month = (($startMonth - 1 + $monthOffset) % 12) + 1;

        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));
        $now = date('Y-m-d');

        // Stop if we're past current month
        if ($startDate > $now) {
            return $this->json(['done' => true, 'synced' => 0, 'page' => $page]);
        }

        $url = $config['api_domain'] . '/api/v3/orders?order_type=' . $orderType
            . '&start_date=' . $startDate . '&end_date=' . $endDate;

        $result = $this->callApi($config['api_key'], $url);
        if (!$result['success']) {
            return $this->json(['error' => 'API error: ' . $result['error'], 'page' => $page, 'month' => $startDate], 400);
        }

        $records = $result['data']['records'] ?? [];

        // Status map: Getfly status 1=confirmed, 2=pending, 3=cancelled
        $statusMap = ['1' => 'approved', '2' => 'pending', '3' => 'cancelled'];

        // User map
        $userMap = [];
        $users = Database::fetchAll("SELECT id, name FROM users WHERE is_active = 1");
        foreach ($users as $u) $userMap[(int)($u['id'])] = $u['id'];

        $synced = 0;
        foreach ($records as $r) {
            $orderCode = trim($r['order_code'] ?? '');
            if (empty($orderCode)) continue;

            $status = $statusMap[$r['status'] ?? ''] ?? 'pending';
            $accountCode = $r['account_info']['account_code'] ?? '';
            $assignedUser = (int)($r['assigned_user'] ?? 0);
            $ownerId = $userMap[$assignedUser] ?? null;

            $amount = (float)str_replace(',', '', $r['amount'] ?? '0');
            $discount = (float)str_replace(',', '', $r['discount_amount'] ?? '0');
            $vat = (float)str_replace(',', '', $r['vat_amount'] ?? '0');
            $fAmount = (float)str_replace(',', '', $r['f_amount'] ?? '0');

            $payStatus = 'unpaid';
            if (($r['payment_status'] ?? '') === 'paid') $payStatus = 'paid';
            elseif ($fAmount > 0 && $fAmount < $amount) $payStatus = 'partial';

            $data = [
                'tenant_id' => $tid,
                'order_number' => $orderCode,
                'type' => 'order',
                'status' => $status,
                'contact_id' => null,
                'subtotal' => $amount - $vat,
                'tax_amount' => $vat,
                'discount_amount' => $discount,
                'total' => $amount,
                'paid_amount' => $fAmount,
                'payment_status' => $payStatus,
                'lading_code' => $r['lading_code'] ?? null,
                'shipping_address' => $r['account_info']['address'] ?? null,
                'owner_id' => $ownerId,
                'issued_date' => $r['order_date'] ?? null,
                'created_at' => $r['created_at'] ?? date('Y-m-d H:i:s'),
                'updated_at' => $r['updated_at'] ?? date('Y-m-d H:i:s'),
            ];

            // Lookup contact
            if ($accountCode) {
                $contact = Database::fetch("SELECT id FROM contacts WHERE account_code = ? AND tenant_id = ? LIMIT 1", [$accountCode, $tid]);
                if ($contact) $data['contact_id'] = $contact['id'];
            }

            $existing = Database::fetch("SELECT id FROM orders WHERE order_number = ? AND tenant_id = ?", [$orderCode, $tid]);
            if ($existing) {
                Database::update('orders', [
                    'status' => $data['status'],
                    'total' => $data['total'],
                    'paid_amount' => $data['paid_amount'],
                    'payment_status' => $data['payment_status'],
                ], 'id = ?', [$existing['id']]);
            } else {
                $data['created_by'] = $ownerId;
                Database::insert('orders', $data);
            }
            $synced++;
        }

        // Estimate total months (2021-01 to now)
        $totalMonths = ((int)date('Y') - $startYear) * 12 + (int)date('m');
        $hasMore = $startDate <= date('Y-m-01');

        return $this->json([
            'done' => !$hasMore || $startDate >= date('Y-m-01'),
            'synced' => $synced,
            'page' => $page,
            'month' => sprintf('%02d/%04d', $month, $year),
            'total_pages' => $totalMonths,
            'has_more' => $hasMore && $startDate < date('Y-m-01'),
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
