<?php

namespace App\Services;

use Core\Database;

class AiService
{
    /**
     * Gọi Gemini API (miễn phí)
     * Cần set GEMINI_API_KEY trong .env
     */
    public static function ask(string $message, int $tenantId, int $userId): string
    {
        // Try multiple ways to get key (PHP-FPM quirks)
        $apiKey = $_ENV['GEMINI_API_KEY'] ?? '';
        if (empty($apiKey)) $apiKey = getenv('GEMINI_API_KEY') ?: '';
        if (empty($apiKey)) {
            // Read directly from .env as last resort
            $envFile = BASE_PATH . '/.env';
            if (file_exists($envFile)) {
                $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (str_starts_with($line, 'GEMINI_API_KEY=')) {
                        $apiKey = trim(substr($line, 15));
                        break;
                    }
                }
            }
        }

        // Nếu chưa có API key → fallback về rule-based
        if (empty($apiKey)) {
            return self::fallbackRuleBased($message, $tenantId, $userId);
        }

        // Lấy context CRM cho AI
        $context = self::buildContext($tenantId, $userId);

        $systemPrompt = "Bạn là ToryCRM AI - trợ lý thông minh cho hệ thống quản lý khách hàng ToryCRM. "
            . "Trả lời ngắn gọn, chính xác, bằng tiếng Việt. "
            . "Dữ liệu CRM hiện tại:\n" . $context;

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;

        $payload = json_encode([
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [['text' => $systemPrompt . "\n\nCâu hỏi: " . $message]]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 500,
            ]
        ]);

        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $payload,
                'timeout' => 15,
            ]
        ];

        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($response === false || empty($response)) {
                return "⚠️ Không kết nối được Gemini API: " . ($curlError ?: 'Timeout');
            }

            $data = json_decode($response, true);

            if (isset($data['error'])) {
                return "⚠️ Gemini API lỗi: " . ($data['error']['message'] ?? 'HTTP ' . $httpCode);
            }

            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if ($text) {
                return trim($text);
            }

            return self::fallbackRuleBased($message, $tenantId, $userId);
        } catch (\Exception $e) {
            return "⚠️ Lỗi AI: " . $e->getMessage();
        }
    }

    /**
     * Build CRM context cho AI
     */
    private static function buildContext(int $tenantId, int $userId): string
    {
        $lines = [];

        // Stats tổng quan
        $contacts = Database::fetch("SELECT COUNT(*) as c FROM contacts WHERE tenant_id = ? AND is_deleted = 0", [$tenantId]);
        $deals = Database::fetch("SELECT COUNT(*) as c, COALESCE(SUM(value),0) as v FROM deals WHERE tenant_id = ? AND status = 'open'", [$tenantId]);
        $wonThisMonth = Database::fetch(
            "SELECT COUNT(*) as c, COALESCE(SUM(value),0) as v FROM deals WHERE tenant_id = ? AND status = 'won' AND MONTH(actual_close_date) = MONTH(CURDATE()) AND YEAR(actual_close_date) = YEAR(CURDATE())",
            [$tenantId]
        );
        $orders = Database::fetch("SELECT COUNT(*) as c, COALESCE(SUM(total),0) as v FROM orders WHERE tenant_id = ? AND is_deleted = 0 AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())", [$tenantId]);
        $overdueTasks = Database::fetch("SELECT COUNT(*) as c FROM tasks WHERE tenant_id = ? AND is_deleted = 0 AND due_date < NOW() AND status != 'done'", [$tenantId]);
        $openTickets = Database::fetch("SELECT COUNT(*) as c FROM tickets WHERE tenant_id = ? AND status IN ('open','in_progress')", [$tenantId]);

        $lines[] = "- Tổng khách hàng: " . ($contacts['c'] ?? 0);
        $lines[] = "- Deal đang mở: " . ($deals['c'] ?? 0) . " (tổng " . number_format($deals['v'] ?? 0) . "đ)";
        $lines[] = "- Deal thắng tháng này: " . ($wonThisMonth['c'] ?? 0) . " (" . number_format($wonThisMonth['v'] ?? 0) . "đ)";
        $lines[] = "- Đơn hàng tháng này: " . ($orders['c'] ?? 0) . " (" . number_format($orders['v'] ?? 0) . "đ)";
        $lines[] = "- Task quá hạn: " . ($overdueTasks['c'] ?? 0);
        $lines[] = "- Ticket đang mở: " . ($openTickets['c'] ?? 0);

        // Top 5 deals đang mở
        $topDeals = Database::fetchAll(
            "SELECT d.title, d.value, c.first_name, c.last_name FROM deals d LEFT JOIN contacts c ON d.contact_id = c.id WHERE d.tenant_id = ? AND d.status = 'open' ORDER BY d.value DESC LIMIT 5",
            [$tenantId]
        );
        if (!empty($topDeals)) {
            $lines[] = "- Top 5 deal đang mở:";
            foreach ($topDeals as $d) {
                $lines[] = "  + " . $d['title'] . " - " . number_format($d['value']) . "đ (" . trim(($d['first_name'] ?? '') . ' ' . ($d['last_name'] ?? '')) . ")";
            }
        }

        // KH không hoạt động
        $inactive = Database::fetch(
            "SELECT COUNT(*) as c FROM contacts c LEFT JOIN activities a ON a.contact_id = c.id WHERE c.tenant_id = ? AND c.is_deleted = 0 GROUP BY c.id HAVING MAX(a.created_at) < DATE_SUB(NOW(), INTERVAL 14 DAY) OR MAX(a.created_at) IS NULL",
            [$tenantId]
        );

        $lines[] = "- Tháng hiện tại: " . date('m/Y');
        $lines[] = "- Ngày hiện tại: " . date('d/m/Y');

        return implode("\n", $lines);
    }

    /**
     * Fallback rule-based khi không có API key
     */
    private static function fallbackRuleBased(string $message, int $tenantId, int $userId): string
    {
        $msg = mb_strtolower(trim($message));

        // Doanh thu
        if (str_contains($msg, 'doanh thu') || str_contains($msg, 'revenue')) {
            $wonDeals = Database::fetch(
                "SELECT COUNT(*) as cnt, COALESCE(SUM(value), 0) as total FROM deals WHERE tenant_id = ? AND status = 'won' AND MONTH(actual_close_date) = MONTH(CURDATE()) AND YEAR(actual_close_date) = YEAR(CURDATE())",
                [$tenantId]
            );
            $orders = Database::fetch(
                "SELECT COUNT(*) as cnt, COALESCE(SUM(total), 0) as total FROM orders WHERE tenant_id = ? AND is_deleted = 0 AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())",
                [$tenantId]
            );
            return "💰 Doanh thu tháng " . date('m/Y') . ":\n"
                . "• Deal thắng: " . ($wonDeals['cnt'] ?? 0) . " deal - " . number_format($wonDeals['total'] ?? 0) . " VNĐ\n"
                . "• Đơn hàng: " . ($orders['cnt'] ?? 0) . " đơn - " . number_format($orders['total'] ?? 0) . " VNĐ";
        }

        // Công việc quá hạn
        if (str_contains($msg, 'quá hạn') || str_contains($msg, 'overdue') || str_contains($msg, 'task')) {
            $tasks = Database::fetchAll(
                "SELECT t.title, t.due_date, u.name as assigned_name FROM tasks t LEFT JOIN users u ON t.assigned_to = u.id WHERE t.tenant_id = ? AND t.is_deleted = 0 AND t.due_date < NOW() AND t.status != 'done' ORDER BY t.due_date ASC LIMIT 5",
                [$tenantId]
            );
            if (empty($tasks)) return "✅ Không có công việc quá hạn!";
            $text = "⚠️ " . count($tasks) . " công việc quá hạn:\n";
            foreach ($tasks as $t) {
                $text .= "• " . $t['title'] . " (hạn " . date('d/m', strtotime($t['due_date'])) . " - " . ($t['assigned_name'] ?? 'Chưa gán') . ")\n";
            }
            return trim($text);
        }

        // KH cần liên hệ
        if (str_contains($msg, 'liên hệ') || str_contains($msg, 'khách hàng') || str_contains($msg, 'inactive')) {
            $contacts = Database::fetchAll(
                "SELECT c.first_name, c.last_name, DATEDIFF(NOW(), COALESCE(MAX(a.created_at), c.created_at)) as days FROM contacts c LEFT JOIN activities a ON a.contact_id = c.id WHERE c.tenant_id = ? AND c.is_deleted = 0 GROUP BY c.id, c.first_name, c.last_name, c.created_at HAVING days >= 14 ORDER BY days DESC LIMIT 5",
                [$tenantId]
            );
            if (empty($contacts)) return "✅ Tất cả khách hàng đều hoạt động!";
            $text = "📞 Khách hàng cần liên hệ lại:\n";
            foreach ($contacts as $c) {
                $text .= "• " . trim($c['first_name'] . ' ' . ($c['last_name'] ?? '')) . " - " . $c['days'] . " ngày không hoạt động\n";
            }
            return trim($text);
        }

        // Pipeline
        if (str_contains($msg, 'pipeline') || str_contains($msg, 'phễu')) {
            $stages = Database::fetchAll(
                "SELECT ds.name, COUNT(d.id) as cnt, COALESCE(SUM(d.value),0) as total FROM deal_stages ds LEFT JOIN deals d ON d.stage_id = ds.id AND d.status = 'open' AND d.tenant_id = ? GROUP BY ds.id, ds.name ORDER BY ds.sort_order",
                [$tenantId]
            );
            $text = "📊 Pipeline hiện tại:\n";
            foreach ($stages as $s) {
                $text .= "• " . $s['name'] . ": " . $s['cnt'] . " deal (" . number_format($s['total']) . "đ)\n";
            }
            return trim($text);
        }

        return "Xin lỗi, tôi chưa hiểu yêu cầu. Thử hỏi:\n"
            . "• \"Doanh thu tháng này\"\n"
            . "• \"Công việc quá hạn\"\n"
            . "• \"Khách hàng cần liên hệ\"\n"
            . "• \"Pipeline\"\n\n"
            . "💡 Để tôi thông minh hơn, thêm GEMINI_API_KEY vào file .env";
    }
}
