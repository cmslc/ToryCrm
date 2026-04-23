<?php

namespace App\Services;

use Core\Database;

class AiService
{
    private static function getEnvKey(string $key): string
    {
        $val = $_ENV[$key] ?? '';
        if (empty($val)) $val = getenv($key) ?: '';
        if (empty($val) && defined('BASE_PATH')) {
            $envFile = BASE_PATH . '/.env';
            if (file_exists($envFile)) {
                foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                    if (str_starts_with($line, $key . '=')) {
                        $val = trim(substr($line, strlen($key) + 1));
                        break;
                    }
                }
            }
        }
        return $val;
    }

    public static function ask(string $message, int $tenantId, int $userId, array $options = []): string
    {
        $t0 = microtime(true);

        // Load toggle states from tenant settings
        $tenant = Database::fetch("SELECT settings FROM tenants WHERE id = ?", [$tenantId]);
        $settings = json_decode($tenant['settings'] ?? '{}', true);
        $apiEnabled = $settings['ai']['api_enabled'] ?? [];
        $on = function($key) use ($apiEnabled) {
            return !isset($apiEnabled[$key]) || $apiEnabled[$key];
        };

        // Honor include_crm_context from settings (default ON for back-compat)
        $includeContext = $settings['ai']['include_crm_context'] ?? true;
        if (isset($options['include_context'])) $includeContext = (bool) $options['include_context'];

        // Owner scope: visible user IDs passed from controller (null = no restriction)
        $visibleUserIds = $options['visible_user_ids'] ?? null;

        // Detect which provider to use
        $deepseekKey = $on('deepseek') ? self::getEnvKey('DEEPSEEK_API_KEY') : '';
        $openrouterKey = $on('openrouter') ? self::getEnvKey('OPENROUTER_API_KEY') : '';
        $groqKey = $on('groq') ? self::getEnvKey('GROQ_API_KEY') : '';
        $geminiKey = $on('gemini') ? self::getEnvKey('GEMINI_API_KEY') : '';

        $context = $includeContext ? self::buildContext($tenantId, $userId, $visibleUserIds) : '';

        $systemPrompt = "Bạn là ToryCRM AI - trợ lý thông minh cho hệ thống CRM. "
            . "Trả lời ngắn gọn, chính xác, bằng tiếng Việt. Dùng emoji phù hợp. "
            . "Khi cần data cụ thể (khách hàng, đơn hàng, báo giá, sản phẩm, doanh thu), "
            . "HÃY GỌI TOOL phù hợp thay vì đoán. Tool có sẵn theo nhóm: "
            . "[Khách hàng] search_contacts, get_contact_detail; "
            . "[Bán hàng] search_orders, get_order_detail, search_quotations, get_quotation_detail, search_products; "
            . "[Báo cáo] get_revenue, get_top_customers, get_top_sellers, revenue_breakdown, product_sales_stats, outstanding_orders, quotation_conversion_rate.";
        if ($context !== '') $systemPrompt .= "\n\nThống kê tổng hợp (có sẵn, khỏi cần gọi tool):\n" . $context;

        $provider = 'fallback';
        $response = '';
        $error = null;
        try {
            if (!empty($deepseekKey)) {
                $provider = 'deepseek';
                $response = self::callDeepSeek($deepseekKey, $systemPrompt, $message, $tenantId, $visibleUserIds);
            } elseif (!empty($openrouterKey)) {
                $provider = 'openrouter';
                $response = self::callOpenRouter($openrouterKey, $systemPrompt, $message);
            } elseif (!empty($groqKey)) {
                $provider = 'groq';
                $response = self::callGroq($groqKey, $systemPrompt, $message);
            } elseif (!empty($geminiKey)) {
                $provider = 'gemini';
                $response = self::callGemini($geminiKey, $systemPrompt, $message);
            } else {
                $response = self::fallbackRuleBased($message, $tenantId, $userId);
            }
        } catch (\Throwable $e) {
            $error = mb_substr($e->getMessage(), 0, 250);
            $response = "Lỗi khi gọi AI: " . $error;
        }

        // Audit log — what context was sent to which provider
        try {
            Database::insert('ai_query_logs', [
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'provider' => $provider,
                'message_len' => mb_strlen($message),
                'context_included' => $context !== '' ? 1 : 0,
                'context_size' => mb_strlen($context),
                'context_preview' => $context !== '' ? mb_substr($context, 0, 500) : null,
                'response_len' => mb_strlen($response),
                'response_time_ms' => (int) round((microtime(true) - $t0) * 1000),
                'error' => $error,
            ]);
        } catch (\Exception $e) { /* non-fatal */ }

        return $response;
    }

    /**
     * DeepSeek with function calling support. Accepts optional tool context
     * (tenantId + visibleUserIds) — when provided, the model can call tools
     * defined in AiToolService. Loops up to 3 rounds of tool calls before
     * forcing a final answer.
     */
    private static function callDeepSeek(string $apiKey, string $system, string $message, ?int $tenantId = null, ?array $visibleUserIds = null): string
    {
        $url = 'https://api.deepseek.com/chat/completions';
        $messages = [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $message],
        ];
        $useTools = $tenantId !== null;
        $tools = $useTools ? AiToolService::definitions() : null;

        for ($round = 0; $round < 3; $round++) {
            $payload = [
                'model' => 'deepseek-chat',
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 800,
            ];
            if ($useTools) {
                $payload['tools'] = $tools;
                $payload['tool_choice'] = 'auto';
            }

            $response = self::curlPost($url, json_encode($payload), [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ]);
            if (!$response['success']) return "⚠️ DeepSeek lỗi: " . $response['error'];
            $data = json_decode($response['body'], true);
            if (isset($data['error'])) return "⚠️ DeepSeek: " . ($data['error']['message'] ?? 'Unknown');

            $msg = $data['choices'][0]['message'] ?? [];
            $toolCalls = $msg['tool_calls'] ?? [];

            if (!empty($toolCalls) && $useTools) {
                // Append assistant message + tool results, loop
                $messages[] = $msg;
                foreach ($toolCalls as $tc) {
                    $args = json_decode($tc['function']['arguments'] ?? '{}', true) ?: [];
                    $result = AiToolService::execute(
                        $tc['function']['name'] ?? '',
                        $args,
                        $tenantId,
                        $visibleUserIds
                    );
                    $messages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $tc['id'] ?? '',
                        'content' => json_encode($result, JSON_UNESCAPED_UNICODE),
                    ];
                }
                continue; // ask AI again with tool results
            }

            return trim($msg['content'] ?? '');
        }
        return trim($msg['content'] ?? 'AI không đưa ra câu trả lời sau nhiều vòng gọi tool.');
    }

    private static function callOpenRouter(string $apiKey, string $system, string $message): string
    {
        $url = 'https://openrouter.ai/api/v1/chat/completions';
        $models = [
            'meta-llama/llama-3.3-70b-instruct:free',
            'google/gemma-3-27b-it:free',
            'nousresearch/hermes-3-llama-3.1-405b:free',
            'mistralai/mistral-small-3.1-24b-instruct:free',
        ];
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
            'HTTP-Referer: https://torycrm.com',
            'X-Title: ToryCRM',
        ];

        foreach ($models as $model) {
            $payload = json_encode([
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $message],
                ],
                'temperature' => 0.7,
                'max_tokens' => 500,
            ]);

            $response = self::curlPost($url, $payload, $headers);
            if (!$response['success']) continue;

            $data = json_decode($response['body'], true);
            if (isset($data['error']['code']) && $data['error']['code'] == 429) continue;
            if (isset($data['error'])) continue;

            $text = trim($data['choices'][0]['message']['content'] ?? '');
            if (!empty($text)) return $text;
        }

        // All models failed
        return self::fallbackRuleBased($message, 0, 0);
    }

    private static function callGroq(string $apiKey, string $system, string $message): string
    {
        $url = 'https://api.groq.com/openai/v1/chat/completions';
        $payload = json_encode([
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $message],
            ],
            'temperature' => 0.7,
            'max_tokens' => 500,
        ]);

        $response = self::curlPost($url, $payload, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ]);

        if (!$response['success']) {
            return "⚠️ Groq API lỗi: " . $response['error'];
        }

        $data = json_decode($response['body'], true);
        if (isset($data['error'])) {
            return "⚠️ Groq: " . ($data['error']['message'] ?? 'Unknown error');
        }

        return trim($data['choices'][0]['message']['content'] ?? '');
    }

    private static function callGemini(string $apiKey, string $system, string $message): string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;
        $payload = json_encode([
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $system . "\n\nCâu hỏi: " . $message]]]
            ],
            'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 500],
        ]);

        $response = self::curlPost($url, $payload, ['Content-Type: application/json']);

        if (!$response['success']) {
            return "⚠️ Gemini API lỗi: " . $response['error'];
        }

        $data = json_decode($response['body'], true);
        if (isset($data['error'])) {
            return "⚠️ Gemini: " . ($data['error']['message'] ?? 'Unknown error');
        }

        return trim($data['candidates'][0]['content']['parts'][0]['text'] ?? '');
    }

    private static function curlPost(string $url, string $payload, array $headers): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $body = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($body === false || empty($body)) {
            return ['success' => false, 'error' => $error ?: 'Timeout', 'body' => ''];
        }
        return ['success' => true, 'error' => '', 'body' => $body];
    }

    /**
     * Build a compact summary of CRM state to prime the LLM.
     *
     * $visibleUserIds:
     *   null → no scope restriction (admin / view_all permission)
     *   array of user IDs → restrict owner-based entities to these users
     *
     * PII-conscious: no phone/email of random customers, only aggregate counts
     * and top-5 deals with owner context.
     */
    private static function buildContext(int $tenantId, int $userId, ?array $visibleUserIds = null): string
    {
        // Build AND clause + param for owner scope on different column names
        $scopeFor = function (string $col) use ($visibleUserIds) {
            if ($visibleUserIds === null) return ['', []];
            $ids = array_values(array_unique(array_map('intval', $visibleUserIds)));
            if (!$ids) return [" AND {$col} = 0", []]; // no visibility = empty set
            $ph = implode(',', array_fill(0, count($ids), '?'));
            return [" AND {$col} IN ({$ph})", $ids];
        };

        $lines = [];

        [$sc, $sp] = $scopeFor('c.owner_id');
        $contacts = Database::fetch(
            "SELECT COUNT(*) as c FROM contacts c WHERE c.tenant_id = ? AND c.is_deleted = 0{$sc}",
            array_merge([$tenantId], $sp)
        );

        [$sc, $sp] = $scopeFor('d.owner_id');
        $deals = Database::fetch(
            "SELECT COUNT(*) as c, COALESCE(SUM(d.value),0) as v FROM deals d WHERE d.tenant_id = ? AND d.status = 'open'{$sc}",
            array_merge([$tenantId], $sp)
        );

        [$sc, $sp] = $scopeFor('d.owner_id');
        $wonThisMonth = Database::fetch(
            "SELECT COUNT(*) as c, COALESCE(SUM(d.value),0) as v FROM deals d
             WHERE d.tenant_id = ? AND d.status = 'won'
             AND MONTH(d.actual_close_date) = MONTH(CURDATE())
             AND YEAR(d.actual_close_date) = YEAR(CURDATE()){$sc}",
            array_merge([$tenantId], $sp)
        );

        [$sc, $sp] = $scopeFor('o.owner_id');
        $orders = Database::fetch(
            "SELECT COUNT(*) as c, COALESCE(SUM(o.total),0) as v FROM orders o
             WHERE o.tenant_id = ? AND o.is_deleted = 0
             AND MONTH(o.created_at) = MONTH(CURDATE())
             AND YEAR(o.created_at) = YEAR(CURDATE()){$sc}",
            array_merge([$tenantId], $sp)
        );

        [$sc, $sp] = $scopeFor('t.assigned_to');
        $overdueTasks = Database::fetch(
            "SELECT COUNT(*) as c FROM tasks t
             WHERE t.tenant_id = ? AND t.is_deleted = 0 AND t.due_date < NOW() AND t.status != 'done'{$sc}",
            array_merge([$tenantId], $sp)
        );

        [$sc, $sp] = $scopeFor('tk.assigned_to');
        $openTickets = Database::fetch(
            "SELECT COUNT(*) as c FROM tickets tk
             WHERE tk.tenant_id = ? AND tk.status IN ('open','in_progress'){$sc}",
            array_merge([$tenantId], $sp)
        );

        $scopeNote = $visibleUserIds !== null ? ' (trong phạm vi của bạn)' : '';
        $lines[] = "- Tổng KH{$scopeNote}: " . ($contacts['c'] ?? 0);
        $lines[] = "- Deal đang mở: " . ($deals['c'] ?? 0) . " (tổng " . number_format($deals['v'] ?? 0) . "đ)";
        $lines[] = "- Deal thắng tháng này: " . ($wonThisMonth['c'] ?? 0) . " (" . number_format($wonThisMonth['v'] ?? 0) . "đ)";
        $lines[] = "- Đơn hàng tháng này: " . ($orders['c'] ?? 0) . " (" . number_format($orders['v'] ?? 0) . "đ)";
        $lines[] = "- Task quá hạn: " . ($overdueTasks['c'] ?? 0);
        $lines[] = "- Ticket đang mở: " . ($openTickets['c'] ?? 0);
        $lines[] = "- Ngày: " . date('d/m/Y');

        // Top deals — name + value only, no PII (phone/email stripped)
        [$sc, $sp] = $scopeFor('d.owner_id');
        $topDeals = Database::fetchAll(
            "SELECT d.title, d.value FROM deals d
             WHERE d.tenant_id = ? AND d.status = 'open'{$sc}
             ORDER BY d.value DESC LIMIT 5",
            array_merge([$tenantId], $sp)
        );
        if (!empty($topDeals)) {
            $lines[] = "- Top 5 deals mở:";
            foreach ($topDeals as $d) {
                $lines[] = "  + " . $d['title'] . " - " . number_format($d['value']) . "đ";
            }
        }

        // NOTE: full customer list (50 rows with phone/email) removed from context.
        // AI can still answer customer-specific queries via fallbackRuleBased which
        // does a targeted lookup when user asks about a specific phone/name.

        return implode("\n", $lines);
    }

    private static function fallbackRuleBased(string $message, int $tenantId, int $userId): string
    {
        $msg = mb_strtolower(trim($message));

        // Tra cứu khách hàng theo SĐT
        if (preg_match('/(\d{9,11})/', $msg, $m)) {
            $phone = $m[1];
            $contacts = Database::fetchAll(
                "SELECT c.id, c.first_name, c.last_name, c.phone, c.email, c.status, c.position, comp.name as company_name
                 FROM contacts c LEFT JOIN companies comp ON c.company_id = comp.id
                 WHERE c.tenant_id = ? AND c.is_deleted = 0 AND REPLACE(REPLACE(c.phone, ' ', ''), '.', '') LIKE ?
                 LIMIT 5",
                [$tenantId, '%' . $phone . '%']
            );
            if (!empty($contacts)) {
                $text = "📱 Tìm thấy " . count($contacts) . " khách hàng với SĐT chứa {$phone}:\n";
                foreach ($contacts as $c) {
                    $name = trim(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? ''));
                    $text .= "• **{$name}**";
                    if ($c['phone']) $text .= " | SĐT: " . $c['phone'];
                    if ($c['email']) $text .= " | " . $c['email'];
                    if ($c['company_name']) $text .= " | Cty: " . $c['company_name'];
                    if ($c['position']) $text .= " | " . $c['position'];
                    $text .= " | TT: " . $c['status'] . "\n";
                }
                return trim($text);
            }
            return "❌ Không tìm thấy khách hàng nào với SĐT: {$phone}";
        }

        // Tra cứu khách hàng theo tên/email
        $searchKeywords = ['khách hàng', 'khach hang', 'liên hệ', 'lien he', 'contact', 'ai là', 'tìm', 'tra cứu', 'của ai', 'số này', 'email'];
        $isSearch = false;
        foreach ($searchKeywords as $kw) {
            if (str_contains($msg, $kw)) { $isSearch = true; break; }
        }
        if ($isSearch) {
            // Extract search term: remove keywords, keep the rest
            $searchTerm = $msg;
            foreach ($searchKeywords as $kw) {
                $searchTerm = str_replace($kw, '', $searchTerm);
            }
            $searchTerm = trim(preg_replace('/\s+/', ' ', $searchTerm));
            if (mb_strlen($searchTerm) >= 2) {
                $contacts = Database::fetchAll(
                    "SELECT c.id, c.first_name, c.last_name, c.phone, c.email, c.status, comp.name as company_name
                     FROM contacts c LEFT JOIN companies comp ON c.company_id = comp.id
                     WHERE c.tenant_id = ? AND c.is_deleted = 0
                       AND (CONCAT(c.first_name, ' ', c.last_name) LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)
                     LIMIT 5",
                    [$tenantId, '%' . $searchTerm . '%', '%' . $searchTerm . '%', '%' . $searchTerm . '%']
                );
                if (!empty($contacts)) {
                    $text = "🔍 Tìm thấy " . count($contacts) . " khách hàng:\n";
                    foreach ($contacts as $c) {
                        $name = trim(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? ''));
                        $text .= "• **{$name}**";
                        if ($c['phone']) $text .= " | " . $c['phone'];
                        if ($c['email']) $text .= " | " . $c['email'];
                        if ($c['company_name']) $text .= " | " . $c['company_name'];
                        $text .= "\n";
                    }
                    return trim($text);
                }
                return "❌ Không tìm thấy khách hàng nào khớp: \"{$searchTerm}\"";
            }
        }

        if (str_contains($msg, 'doanh thu') || str_contains($msg, 'revenue')) {
            $won = Database::fetch("SELECT COUNT(*) as c, COALESCE(SUM(value),0) as v FROM deals WHERE tenant_id = ? AND status = 'won' AND MONTH(actual_close_date) = MONTH(CURDATE()) AND YEAR(actual_close_date) = YEAR(CURDATE())", [$tenantId]);
            $ord = Database::fetch("SELECT COUNT(*) as c, COALESCE(SUM(total),0) as v FROM orders WHERE tenant_id = ? AND is_deleted = 0 AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())", [$tenantId]);
            return "💰 Doanh thu tháng " . date('m/Y') . ":\n• Deal: " . ($won['c'] ?? 0) . " - " . number_format($won['v'] ?? 0) . " VNĐ\n• Đơn hàng: " . ($ord['c'] ?? 0) . " - " . number_format($ord['v'] ?? 0) . " VNĐ";
        }
        if (str_contains($msg, 'quá hạn') || str_contains($msg, 'task') || str_contains($msg, 'công việc')) {
            $tasks = Database::fetchAll("SELECT t.title, t.due_date FROM tasks t WHERE t.tenant_id = ? AND t.is_deleted = 0 AND t.due_date < NOW() AND t.status != 'done' ORDER BY t.due_date LIMIT 5", [$tenantId]);
            if (empty($tasks)) return "✅ Không có task quá hạn!";
            $text = "⚠️ " . count($tasks) . " task quá hạn:\n";
            foreach ($tasks as $t) $text .= "• " . $t['title'] . " (hạn " . date('d/m', strtotime($t['due_date'])) . ")\n";
            return trim($text);
        }
        if (str_contains($msg, 'pipeline') || str_contains($msg, 'phễu')) {
            $stages = Database::fetchAll("SELECT ds.name, COUNT(d.id) as c, COALESCE(SUM(d.value),0) as v FROM deal_stages ds LEFT JOIN deals d ON d.stage_id = ds.id AND d.status = 'open' AND d.tenant_id = ? GROUP BY ds.id, ds.name ORDER BY ds.sort_order", [$tenantId]);
            $text = "📊 Pipeline:\n";
            foreach ($stages as $s) $text .= "• " . $s['name'] . ": " . $s['c'] . " deal (" . number_format($s['v']) . "đ)\n";
            return trim($text);
        }
        if (str_contains($msg, 'tổng quan') || str_contains($msg, 'báo cáo') || str_contains($msg, 'thống kê')) {
            $contacts = Database::fetch("SELECT COUNT(*) as c FROM contacts WHERE tenant_id = ? AND is_deleted = 0", [$tenantId]);
            $deals = Database::fetch("SELECT COUNT(*) as c, COALESCE(SUM(value),0) as v FROM deals WHERE tenant_id = ? AND status = 'open'", [$tenantId]);
            $overdue = Database::fetch("SELECT COUNT(*) as c FROM tasks WHERE tenant_id = ? AND is_deleted = 0 AND due_date < NOW() AND status != 'done'", [$tenantId]);
            return "📊 Tổng quan:\n• Khách hàng: " . number_format($contacts['c'] ?? 0) . "\n• Deal đang mở: " . ($deals['c'] ?? 0) . " (" . number_format($deals['v'] ?? 0) . "đ)\n• Task quá hạn: " . ($overdue['c'] ?? 0) . "\n• Ngày: " . date('d/m/Y');
        }

        return "Tôi có thể giúp bạn:\n• Tra cứu SĐT/tên khách hàng\n• \"Doanh thu tháng này\"\n• \"Task quá hạn\" / \"Công việc\"\n• \"Pipeline\" / \"Tổng quan\"\n\n💡 Cấu hình API key tại Cài đặt → API để AI thông minh hơn.";
    }
}
