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

    public static function ask(string $message, int $tenantId, int $userId): string
    {
        // Detect which provider to use
        $deepseekKey = self::getEnvKey('DEEPSEEK_API_KEY');
        $openrouterKey = self::getEnvKey('OPENROUTER_API_KEY');
        $groqKey = self::getEnvKey('GROQ_API_KEY');
        $geminiKey = self::getEnvKey('GEMINI_API_KEY');

        $context = self::buildContext($tenantId, $userId);

        $systemPrompt = "Bạn là ToryCRM AI - trợ lý thông minh cho hệ thống CRM. "
            . "Trả lời ngắn gọn, chính xác, bằng tiếng Việt. Dùng emoji phù hợp. "
            . "Dữ liệu CRM:\n" . $context;

        // Try DeepSeek first (works in Asia/HK)
        if (!empty($deepseekKey)) {
            return self::callDeepSeek($deepseekKey, $systemPrompt, $message);
        }

        // Then OpenRouter
        if (!empty($openrouterKey)) {
            return self::callOpenRouter($openrouterKey, $systemPrompt, $message);
        }

        // Then Groq
        if (!empty($groqKey)) {
            return self::callGroq($groqKey, $systemPrompt, $message);
        }

        // Then Gemini
        if (!empty($geminiKey)) {
            return self::callGemini($geminiKey, $systemPrompt, $message);
        }

        // Fallback rule-based
        return self::fallbackRuleBased($message, $tenantId, $userId);
    }

    private static function callDeepSeek(string $apiKey, string $system, string $message): string
    {
        $url = 'https://api.deepseek.com/chat/completions';
        $payload = json_encode([
            'model' => 'deepseek-chat',
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
            return "⚠️ DeepSeek lỗi: " . $response['error'];
        }

        $data = json_decode($response['body'], true);
        if (isset($data['error'])) {
            return "⚠️ DeepSeek: " . ($data['error']['message'] ?? 'Unknown error');
        }

        return trim($data['choices'][0]['message']['content'] ?? '');
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

    private static function buildContext(int $tenantId, int $userId): string
    {
        $lines = [];
        $contacts = Database::fetch("SELECT COUNT(*) as c FROM contacts WHERE tenant_id = ? AND is_deleted = 0", [$tenantId]);
        $deals = Database::fetch("SELECT COUNT(*) as c, COALESCE(SUM(value),0) as v FROM deals WHERE tenant_id = ? AND status = 'open'", [$tenantId]);
        $wonThisMonth = Database::fetch(
            "SELECT COUNT(*) as c, COALESCE(SUM(value),0) as v FROM deals WHERE tenant_id = ? AND status = 'won' AND MONTH(actual_close_date) = MONTH(CURDATE()) AND YEAR(actual_close_date) = YEAR(CURDATE())",
            [$tenantId]
        );
        $orders = Database::fetch("SELECT COUNT(*) as c, COALESCE(SUM(total),0) as v FROM orders WHERE tenant_id = ? AND is_deleted = 0 AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())", [$tenantId]);
        $overdueTasks = Database::fetch("SELECT COUNT(*) as c FROM tasks WHERE tenant_id = ? AND is_deleted = 0 AND due_date < NOW() AND status != 'done'", [$tenantId]);
        $openTickets = Database::fetch("SELECT COUNT(*) as c FROM tickets WHERE tenant_id = ? AND status IN ('open','in_progress')", [$tenantId]);

        $lines[] = "- Tổng KH: " . ($contacts['c'] ?? 0);
        $lines[] = "- Deal đang mở: " . ($deals['c'] ?? 0) . " (tổng " . number_format($deals['v'] ?? 0) . "đ)";
        $lines[] = "- Deal thắng tháng này: " . ($wonThisMonth['c'] ?? 0) . " (" . number_format($wonThisMonth['v'] ?? 0) . "đ)";
        $lines[] = "- Đơn hàng tháng này: " . ($orders['c'] ?? 0) . " (" . number_format($orders['v'] ?? 0) . "đ)";
        $lines[] = "- Task quá hạn: " . ($overdueTasks['c'] ?? 0);
        $lines[] = "- Ticket đang mở: " . ($openTickets['c'] ?? 0);
        $lines[] = "- Ngày: " . date('d/m/Y');

        $topDeals = Database::fetchAll(
            "SELECT d.title, d.value, c.first_name FROM deals d LEFT JOIN contacts c ON d.contact_id = c.id WHERE d.tenant_id = ? AND d.status = 'open' ORDER BY d.value DESC LIMIT 5",
            [$tenantId]
        );
        if (!empty($topDeals)) {
            $lines[] = "- Top deals:";
            foreach ($topDeals as $d) {
                $lines[] = "  + " . $d['title'] . " - " . number_format($d['value']) . "đ (" . ($d['first_name'] ?? '') . ")";
            }
        }

        // Danh sách KH để AI tra cứu theo tên/SĐT/email
        $allContacts = Database::fetchAll(
            "SELECT c.first_name, c.last_name, c.phone, c.email, c.status, c.position, comp.name as company_name
             FROM contacts c LEFT JOIN companies comp ON c.company_id = comp.id
             WHERE c.tenant_id = ? AND c.is_deleted = 0 ORDER BY c.first_name LIMIT 50",
            [$tenantId]
        );
        if (!empty($allContacts)) {
            $lines[] = "- Danh sách khách hàng:";
            foreach ($allContacts as $c) {
                $name = trim(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? ''));
                $info = $name;
                if ($c['phone']) $info .= " | SĐT: " . $c['phone'];
                if ($c['email']) $info .= " | Email: " . $c['email'];
                if ($c['company_name']) $info .= " | Cty: " . $c['company_name'];
                if ($c['position']) $info .= " | " . $c['position'];
                $info .= " | TT: " . $c['status'];
                $lines[] = "  + " . $info;
            }
        }

        return implode("\n", $lines);
    }

    private static function fallbackRuleBased(string $message, int $tenantId, int $userId): string
    {
        $msg = mb_strtolower(trim($message));

        if (str_contains($msg, 'doanh thu') || str_contains($msg, 'revenue')) {
            $won = Database::fetch("SELECT COUNT(*) as c, COALESCE(SUM(value),0) as v FROM deals WHERE tenant_id = ? AND status = 'won' AND MONTH(actual_close_date) = MONTH(CURDATE()) AND YEAR(actual_close_date) = YEAR(CURDATE())", [$tenantId]);
            $ord = Database::fetch("SELECT COUNT(*) as c, COALESCE(SUM(total),0) as v FROM orders WHERE tenant_id = ? AND is_deleted = 0 AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())", [$tenantId]);
            return "💰 Doanh thu tháng " . date('m/Y') . ":\n• Deal: " . ($won['c'] ?? 0) . " - " . number_format($won['v'] ?? 0) . " VNĐ\n• Đơn hàng: " . ($ord['c'] ?? 0) . " - " . number_format($ord['v'] ?? 0) . " VNĐ";
        }
        if (str_contains($msg, 'quá hạn') || str_contains($msg, 'task')) {
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

        return "Tôi chưa hiểu. Thử hỏi:\n• \"Doanh thu tháng này\"\n• \"Task quá hạn\"\n• \"Pipeline\"\n\n💡 Thêm GROQ_API_KEY hoặc GEMINI_API_KEY vào .env để AI thông minh hơn.";
    }
}
