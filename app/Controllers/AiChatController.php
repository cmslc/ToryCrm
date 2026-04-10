<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class AiChatController extends Controller
{
    public function index()
    {
        $pageTitle = 'AI Trợ lý';
        return $this->view('ai-chat.index', compact('pageTitle'));
    }

    public function send()
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }

        $message = trim($this->input('message', ''));
        if (empty($message)) {
            return $this->json(['error' => 'Tin nhắn không được để trống'], 400);
        }

        $tid = $this->tenantId();
        $uid = $this->userId();

        // Save user message
        $this->saveMessage($tid, $uid, 'user', $message);

        // Process with AI (Gemini API or fallback rule-based)
        $response = \App\Services\AiService::ask($message, $tid, $uid);

        // Save assistant response
        $this->saveMessage($tid, $uid, 'assistant', $response);

        return $this->json([
            'success' => true,
            'message' => $response,
            'timestamp' => date('H:i'),
        ]);
    }

    public function history()
    {
        $tid = $this->tenantId();
        $uid = $this->userId();

        $messages = Database::fetchAll(
            "SELECT role, content as message, created_at FROM ai_chat_history
             WHERE tenant_id = ? AND user_id = ?
             ORDER BY created_at ASC
             LIMIT 200",
            [$tid, $uid]
        );

        return $this->json(['messages' => $messages]);
    }

    public function clear()
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }

        $tid = $this->tenantId();
        $uid = $this->userId();

        Database::query(
            "DELETE FROM ai_chat_history WHERE tenant_id = ? AND user_id = ?",
            [$tid, $uid]
        );

        return $this->json(['success' => true]);
    }

    private function saveMessage(int $tid, int $uid, string $role, string $message): void
    {
        try {
            Database::query(
                "INSERT INTO ai_chat_history (tenant_id, user_id, role, content, created_at) VALUES (?, ?, ?, ?, NOW())",
                [$tid, $uid, $role, $message]
            );
        } catch (\Exception $e) {
            // Table may not exist yet
        }
    }

    private function processMessage(string $message, int $tid, int $uid): string
    {
        $msg = mb_strtolower(trim($message));

        // 1. Tóm tắt deal
        if (preg_match('/tóm tắt deal\s+(.+)/iu', $msg, $m)) {
            return $this->summarizeDeal(trim($m[1]), $tid);
        }

        // 2. Khách hàng cần liên hệ
        if (str_contains($msg, 'khách hàng cần liên hệ') || str_contains($msg, 'khach hang can lien he')) {
            return $this->inactiveContacts($tid);
        }

        // 3. Doanh thu tháng này
        if (str_contains($msg, 'doanh thu tháng này') || str_contains($msg, 'doanh thu thang nay') || str_contains($msg, 'doanh thu')) {
            return $this->monthlyRevenue($tid);
        }

        // 4. Công việc quá hạn
        if (str_contains($msg, 'công việc quá hạn') || str_contains($msg, 'cong viec qua han') || str_contains($msg, 'task quá hạn')) {
            return $this->overdueTasks($tid, $uid);
        }

        // 5. Tạo task
        if (preg_match('/tạo task\s+(.+)/iu', $msg, $m)) {
            return $this->createTask(trim($m[1]), $tid, $uid);
        }

        // 6. Soạn email follow-up
        if (preg_match('/soạn email follow[- ]?up\s+(cho\s+)?(.+)/iu', $msg, $m)) {
            return $this->draftFollowUpEmail(trim($m[2]), $tid);
        }

        // 7. Thống kê pipeline
        if (str_contains($msg, 'thống kê pipeline') || str_contains($msg, 'thong ke pipeline') || str_contains($msg, 'pipeline')) {
            return $this->pipelineStats($tid);
        }

        // Default
        return "Xin lỗi, tôi chưa hiểu yêu cầu. Bạn có thể thử hỏi:\n• Tóm tắt deal [tên deal]\n• Khách hàng cần liên hệ\n• Doanh thu tháng này\n• Công việc quá hạn\n• Tạo task [tiêu đề]\n• Soạn email follow-up cho [tên khách hàng]\n• Thống kê pipeline";
    }

    private function summarizeDeal(string $name, int $tid): string
    {
        $deal = Database::fetch(
            "SELECT d.*, c.first_name, c.last_name, u.name as owner_name
             FROM deals d
             LEFT JOIN contacts c ON d.contact_id = c.id
             LEFT JOIN users u ON d.user_id = u.id
             WHERE d.tenant_id = ? AND LOWER(d.title) LIKE ?
             LIMIT 1",
            [$tid, '%' . mb_strtolower($name) . '%']
        );

        if (!$deal) {
            return "Không tìm thấy deal nào có tên \"$name\".";
        }

        $contactName = trim(($deal['first_name'] ?? '') . ' ' . ($deal['last_name'] ?? ''));
        $value = number_format((float)($deal['value'] ?? 0), 0, ',', '.');

        return "📋 Deal: {$deal['title']}\n" .
               "• Giá trị: " . $value . " VNĐ\n" .
               "• Giai đoạn: " . ($deal['stage'] ?? 'N/A') . "\n" .
               "• Trạng thái: " . ($deal['status'] ?? 'N/A') . "\n" .
               "• Khách hàng: " . ($contactName ?: 'Chưa gán') . "\n" .
               "• Người phụ trách: " . ($deal['owner_name'] ?? 'Chưa gán') . "\n" .
               "• Ngày tạo: " . ($deal['created_at'] ?? 'N/A');
    }

    private function inactiveContacts(int $tid): string
    {
        $contacts = Database::fetchAll(
            "SELECT c.id, c.first_name, c.last_name, c.email, c.phone,
                    MAX(a.created_at) as last_activity
             FROM contacts c
             LEFT JOIN activities a ON a.contact_id = c.id
             WHERE c.tenant_id = ? AND c.is_deleted = 0
             GROUP BY c.id, c.first_name, c.last_name, c.email, c.phone
             HAVING last_activity IS NULL OR last_activity < DATE_SUB(NOW(), INTERVAL 14 DAY)
             ORDER BY last_activity ASC
             LIMIT 10",
            [$tid]
        );

        if (empty($contacts)) {
            return "Tất cả khách hàng đều đã được liên hệ trong 14 ngày qua. Tuyệt vời!";
        }

        $lines = ["📞 Khách hàng cần liên hệ (chưa có hoạt động 14+ ngày):"];
        foreach ($contacts as $i => $c) {
            $name = trim($c['first_name'] . ' ' . $c['last_name']);
            $lastAct = $c['last_activity'] ? date('d/m/Y', strtotime($c['last_activity'])) : 'Chưa bao giờ';
            $lines[] = ($i + 1) . ". $name - Liên hệ cuối: $lastAct";
        }
        $lines[] = "\nTổng: " . count($contacts) . " khách hàng cần chú ý.";

        return implode("\n", $lines);
    }

    private function monthlyRevenue(int $tid): string
    {
        // Revenue from won deals this month
        $dealRevenue = Database::fetch(
            "SELECT COUNT(*) as cnt, COALESCE(SUM(value), 0) as total
             FROM deals
             WHERE tenant_id = ? AND status = 'won'
             AND MONTH(updated_at) = MONTH(CURDATE()) AND YEAR(updated_at) = YEAR(CURDATE())",
            [$tid]
        );

        // Revenue from orders this month
        $orderRevenue = Database::fetch(
            "SELECT COUNT(*) as cnt, COALESCE(SUM(total), 0) as total
             FROM orders
             WHERE tenant_id = ? AND is_deleted = 0
             AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())",
            [$tid]
        );

        $dealTotal = number_format((float)($dealRevenue['total'] ?? 0), 0, ',', '.');
        $orderTotal = number_format((float)($orderRevenue['total'] ?? 0), 0, ',', '.');

        return "💰 Doanh thu tháng " . date('m/Y') . ":\n" .
               "• Deal thắng: " . ($dealRevenue['cnt'] ?? 0) . " deal - " . $dealTotal . " VNĐ\n" .
               "• Đơn hàng: " . ($orderRevenue['cnt'] ?? 0) . " đơn - " . $orderTotal . " VNĐ";
    }

    private function overdueTasks(int $tid, int $uid): string
    {
        $tasks = Database::fetchAll(
            "SELECT id, title, due_date, priority
             FROM tasks
             WHERE tenant_id = ? AND (assigned_to = ? OR user_id = ?)
             AND status NOT IN ('done','cancelled')
             AND due_date < CURDATE()
             ORDER BY due_date ASC
             LIMIT 10",
            [$tid, $uid, $uid]
        );

        if (empty($tasks)) {
            return "Bạn không có công việc nào quá hạn. Tiếp tục phát huy!";
        }

        $lines = ["⚠️ Công việc quá hạn:"];
        foreach ($tasks as $i => $t) {
            $dueDate = date('d/m/Y', strtotime($t['due_date']));
            $priority = match($t['priority'] ?? 'medium') {
                'high' => '🔴',
                'medium' => '🟡',
                'low' => '🟢',
                default => '⚪',
            };
            $lines[] = ($i + 1) . ". $priority {$t['title']} (hạn: $dueDate)";
        }
        $lines[] = "\nTổng: " . count($tasks) . " công việc quá hạn.";

        return implode("\n", $lines);
    }

    private function createTask(string $title, int $tid, int $uid): string
    {
        try {
            Database::query(
                "INSERT INTO tasks (tenant_id, user_id, assigned_to, title, status, priority, created_at, updated_at)
                 VALUES (?, ?, ?, ?, 'todo', 'medium', NOW(), NOW())",
                [$tid, $uid, $uid, $title]
            );
            return "✅ Đã tạo task: \"$title\"\nTrạng thái: Cần làm | Ưu tiên: Trung bình\nBạn có thể xem trong mục Công việc.";
        } catch (\Exception $e) {
            return "Không thể tạo task. Vui lòng thử lại sau.";
        }
    }

    private function draftFollowUpEmail(string $contactName, int $tid): string
    {
        $contact = Database::fetch(
            "SELECT first_name, last_name, email
             FROM contacts
             WHERE tenant_id = ? AND (LOWER(CONCAT(first_name, ' ', last_name)) LIKE ? OR LOWER(first_name) LIKE ?)
             AND is_deleted = 0
             LIMIT 1",
            [$tid, '%' . mb_strtolower($contactName) . '%', '%' . mb_strtolower($contactName) . '%']
        );

        $name = $contact ? trim($contact['first_name'] . ' ' . $contact['last_name']) : $contactName;
        $email = $contact['email'] ?? '[email]';

        return "📧 Email follow-up cho $name:\n\n" .
               "Đến: $email\n" .
               "Tiêu đề: Theo dõi - Cập nhật từ cuộc trao đổi trước\n\n" .
               "Kính gửi Anh/Chị $name,\n\n" .
               "Cảm ơn Anh/Chị đã dành thời gian trao đổi trước đó. Tôi viết email này để theo dõi và cập nhật thêm thông tin.\n\n" .
               "Nếu Anh/Chị có bất kỳ câu hỏi nào hoặc cần thêm thông tin, xin đừng ngần ngại liên hệ với tôi.\n\n" .
               "Trân trọng,\n" .
               ($_SESSION['user']['name'] ?? 'Nhân viên kinh doanh');
    }

    private function pipelineStats(int $tid): string
    {
        $stages = Database::fetchAll(
            "SELECT stage, COUNT(*) as cnt, COALESCE(SUM(value), 0) as total
             FROM deals
             WHERE tenant_id = ? AND status = 'open'
             GROUP BY stage
             ORDER BY FIELD(stage, 'lead','qualified','proposal','negotiation','won','lost')",
            [$tid]
        );

        if (empty($stages)) {
            return "Hiện tại không có deal nào trong pipeline.";
        }

        $lines = ["📊 Thống kê Pipeline:"];
        $totalDeals = 0;
        $totalValue = 0;
        foreach ($stages as $s) {
            $value = number_format((float)$s['total'], 0, ',', '.');
            $lines[] = "• " . ucfirst($s['stage']) . ": {$s['cnt']} deal - $value VNĐ";
            $totalDeals += (int)$s['cnt'];
            $totalValue += (float)$s['total'];
        }
        $lines[] = "\nTổng: $totalDeals deal | " . number_format($totalValue, 0, ',', '.') . " VNĐ";

        return implode("\n", $lines);
    }
}
