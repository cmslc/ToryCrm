<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Services\EmailService;

class EmailController extends Controller
{
    private function checkPlugin(): bool
    {
        try {
            $installed = \App\Services\PluginManager::getInstalled($this->tenantId());
            foreach ($installed as $p) {
                if ($p['slug'] === 'email' && $p['tenant_active']) return true;
            }
        } catch (\Exception $e) {}
        $this->setFlash('error', 'Plugin Email chưa được cài đặt.');
        $this->redirect('plugins/marketplace');
        return false;
    }

    // ---- Inbox ----
    public function inbox()
    {
        if (!$this->checkPlugin()) return;
        $tid = Database::tenantId();
        $accountId = (int)($this->input('account') ?: 0);
        $folder = $this->input('folder') ?: 'inbox';
        $search = $this->input('search');
        $page = max(1, (int)($this->input('page') ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Admin sees all, user sees own assigned accounts
        if ($this->isSystemAdmin()) {
            $accounts = EmailService::getAllAccounts();
        } else {
            $accounts = EmailService::getAccountsForUser($this->userId());
        }
        if (empty($accounts)) {
            return $this->view('plugin:email.email.inbox', ['accounts' => [], 'messages' => [], 'total' => 0, 'page' => 1, 'totalPages' => 0, 'folder' => $folder, 'accountId' => 0, 'search' => '', 'unreadCount' => 0, 'folders' => []]);
        }

        $account = $accountId ? EmailService::getAccount($accountId) : $accounts[0];
        $accountId = $account['id'];

        $where = ["em.account_id = ? AND em.folder = ?"];
        $params = [$accountId, $folder];
        if ($search) {
            $where[] = "(em.subject LIKE ? OR em.from_email LIKE ? OR em.from_name LIKE ?)";
            $s = "%{$search}%";
            $params = array_merge($params, [$s, $s, $s]);
        }
        if ($this->input('date_from')) { $where[] = "em.sent_at >= ?"; $params[] = $this->input('date_from') . ' 00:00:00'; }
        if ($this->input('date_to')) { $where[] = "em.sent_at <= ?"; $params[] = $this->input('date_to') . ' 23:59:59'; }
        if ($this->input('has_attach')) { $where[] = "em.has_attachments = 1"; }
        if ($this->input('starred')) { $where[] = "em.is_starred = 1"; }

        $whereSql = implode(' AND ', $where);
        $total = Database::fetch("SELECT COUNT(*) as cnt FROM email_messages em WHERE $whereSql", $params)['cnt'];
        $messages = Database::fetchAll(
            "SELECT em.*, c.first_name as contact_first, c.last_name as contact_last
             FROM email_messages em LEFT JOIN contacts c ON em.contact_id = c.id
             WHERE $whereSql ORDER BY em.sent_at DESC LIMIT $limit OFFSET $offset",
            $params
        );
        $totalPages = ceil($total / $limit);

        $unreadCount = Database::fetch("SELECT COUNT(*) as cnt FROM email_messages WHERE account_id = ? AND folder = 'inbox' AND is_read = 0", [$accountId])['cnt'];

        $folders = Database::fetchAll(
            "SELECT folder, COUNT(*) as cnt, SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread
             FROM email_messages WHERE account_id = ? GROUP BY folder",
            [$accountId]
        );

        return $this->view('plugin:email.email.inbox', compact('accounts', 'messages', 'total', 'page', 'totalPages', 'folder', 'accountId', 'search', 'unreadCount', 'folders'));
    }

    // ---- Read ----
    public function read($id)
    {
        if (!$this->checkPlugin()) return;
        $message = Database::fetch(
            "SELECT em.*, c.first_name as contact_first, c.last_name as contact_last, ea.email as account_email, ea.display_name as account_name
             FROM email_messages em
             LEFT JOIN contacts c ON em.contact_id = c.id
             LEFT JOIN email_accounts ea ON em.account_id = ea.id
             WHERE em.id = ? AND em.tenant_id = ?",
            [(int)$id, Database::tenantId()]
        );
        if (!$message) { $this->setFlash('error', 'Email không tồn tại.'); return $this->redirect('email'); }

        // Fetch body from API if empty
        if (empty($message['body_html']) && empty($message['body_text']) && $message['message_uid']) {
            $account = EmailService::getAccount((int)$message['account_id']);
            if ($account) {
                $service = new EmailService($account);
                $detail = $service->readEmail($account['email'], (int)$message['message_uid']);
                if ($detail['success'] ?? false) {
                    $emailData = $detail['email'] ?? [];
                    $message['body_html'] = $emailData['body_html'] ?? '';
                    $message['body_text'] = $emailData['body_text'] ?? '';
                    $hasAtt = !empty($emailData['attachments']);
                    Database::update('email_messages', [
                        'body_html' => $message['body_html'],
                        'body_text' => $message['body_text'],
                        'has_attachments' => $hasAtt ? 1 : 0,
                    ], 'id = ?', [$id]);
                    $message['has_attachments'] = $hasAtt ? 1 : 0;

                    // Store attachment info locally
                    if ($hasAtt) {
                        foreach ($emailData['attachments'] as $att) {
                            $exists = Database::fetch("SELECT id FROM email_attachments WHERE message_id = ? AND filename = ?", [$id, $att['filename'] ?? '']);
                            if (!$exists) {
                                Database::insert('email_attachments', [
                                    'message_id' => $id,
                                    'filename' => $att['filename'] ?? 'attachment',
                                    'mime_type' => $att['mime'] ?? 'application/octet-stream',
                                    'size' => $att['size'] ?? 0,
                                    'file_path' => $att['download_url'] ?? '',
                                ]);
                            }
                        }
                    }
                }
            }
        }

        // Mark as read
        if (!$message['is_read']) {
            Database::update('email_messages', ['is_read' => 1], 'id = ?', [$id]);
        }

        $accountId = (int)$message['account_id'];
        $accounts = $this->isSystemAdmin() ? EmailService::getAllAccounts() : EmailService::getAccountsForUser($this->userId());
        $folders = Database::fetchAll(
            "SELECT folder, COUNT(*) as cnt, SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread
             FROM email_messages WHERE account_id = ? GROUP BY folder", [$accountId]
        );

        return $this->view('plugin:email.email.read', compact('message', 'accounts', 'accountId', 'folders'));
    }

    // ---- Compose ----
    public function compose()
    {
        if (!$this->checkPlugin()) return;
        $accounts = EmailService::getAllAccounts();
        $replyTo = $this->input('reply_to') ? (int)$this->input('reply_to') : null;
        $replyMsg = null;
        if ($replyTo) {
            $replyMsg = Database::fetch("SELECT * FROM email_messages WHERE id = ? AND tenant_id = ?", [$replyTo, Database::tenantId()]);
        }
        // Draft edit
        $draftId = $this->input('draft') ? (int)$this->input('draft') : null;
        $draftMsg = null;
        if ($draftId) {
            $draftMsg = Database::fetch("SELECT * FROM email_messages WHERE id = ? AND tenant_id = ? AND folder = 'drafts'", [$draftId, Database::tenantId()]);
        }

        // Forward
        $forwardId = $this->input('forward') ? (int)$this->input('forward') : null;
        $forwardMsg = null;
        if ($forwardId) {
            $forwardMsg = Database::fetch("SELECT * FROM email_messages WHERE id = ? AND tenant_id = ?", [$forwardId, Database::tenantId()]);
        }

        $contactEmail = $this->input('to');
        $prefillSubject = $this->input('subject') ?? '';
        $prefillBody = $this->input('body') ?? '';
        $quotationId = $this->input('quotation_id') ? (int)$this->input('quotation_id') : null;

        // Template
        $template = null;
        $templateId = $this->input('template') ? (int)$this->input('template') : null;
        if ($templateId) {
            $template = Database::fetch("SELECT * FROM email_templates WHERE id = ? AND tenant_id = ?", [$templateId, Database::tenantId()]);
        }
        $templates = Database::fetchAll("SELECT id, name FROM email_templates WHERE tenant_id = ? ORDER BY name", [Database::tenantId()]);

        return $this->view('plugin:email.email.compose', compact('accounts', 'replyMsg', 'forwardMsg', 'draftMsg', 'contactEmail', 'prefillSubject', 'prefillBody', 'quotationId', 'template', 'templates'));
    }

    public function send()
    {
        if (!$this->isPost()) return $this->redirect('email/compose');
        $accountId = (int)$this->input('account_id');
        $account = EmailService::getAccount($accountId);
        if (!$account) { $this->setFlash('error', 'Tài khoản email không hợp lệ.'); return $this->redirect('email/compose'); }

        $to = trim($this->input('to') ?? '');
        $subject = trim($this->input('subject') ?? '');
        $body = $this->input('body') ?? '';
        $cc = array_filter(array_map('trim', explode(',', $this->input('cc') ?? '')));
        $isDraft = $this->input('save_draft');

        // Save as draft
        if ($isDraft) {
            $draftId = $this->input('draft_id') ? (int)$this->input('draft_id') : null;
            $draftData = [
                'tenant_id' => Database::tenantId(),
                'account_id' => $account['id'],
                'folder' => 'drafts',
                'from_email' => $account['email'],
                'from_name' => $account['display_name'],
                'to_emails' => $to,
                'cc_emails' => implode(', ', $cc),
                'subject' => $subject,
                'body_html' => $body,
                'is_read' => 1,
                'sent_at' => date('Y-m-d H:i:s'),
            ];
            if ($draftId) {
                Database::update('email_messages', $draftData, 'id = ? AND tenant_id = ? AND folder = ?', [$draftId, Database::tenantId(), 'drafts']);
            } else {
                Database::insert('email_messages', $draftData);
            }
            $this->setFlash('success', 'Đã lưu nháp.');
            return $this->redirect('email?folder=drafts');
        }

        if (empty($to) || empty($subject)) {
            $this->setFlash('error', 'Vui lòng nhập người nhận và tiêu đề.');
            return $this->redirect('email/compose');
        }

        // Handle attachments
        $attachments = [];
        if (!empty($_FILES['attachments']['name'][0])) {
            $uploadDir = BASE_PATH . '/public/uploads/email-attachments/' . Database::tenantId();
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            foreach ($_FILES['attachments']['name'] as $i => $name) {
                if ($_FILES['attachments']['error'][$i] !== UPLOAD_ERR_OK) continue;
                if ($_FILES['attachments']['size'][$i] > 10 * 1024 * 1024) continue; // 10MB max

                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $fileName = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $filePath = $uploadDir . '/' . $fileName;

                if (move_uploaded_file($_FILES['attachments']['tmp_name'][$i], $filePath)) {
                    $attachments[] = [
                        'name' => $name,
                        'path' => $filePath,
                        'mime' => $_FILES['attachments']['type'][$i],
                        'size' => $_FILES['attachments']['size'][$i],
                        'relative' => 'uploads/email-attachments/' . Database::tenantId() . '/' . $fileName,
                    ];
                }
            }
        }

        $service = new EmailService($account);
        $result = $service->send($to, $subject, $body, $cc, [], $attachments);

        // Store attachments in DB if sent
        if ($result['success'] && !empty($attachments)) {
            $lastMsg = Database::fetch("SELECT id FROM email_messages WHERE account_id = ? ORDER BY id DESC LIMIT 1", [$account['id']]);
            if ($lastMsg) {
                foreach ($attachments as $att) {
                    Database::insert('email_attachments', [
                        'message_id' => $lastMsg['id'],
                        'filename' => $att['name'],
                        'mime_type' => $att['mime'],
                        'size' => $att['size'],
                        'file_path' => $att['relative'],
                    ]);
                }
                Database::update('email_messages', ['has_attachments' => 1], 'id = ?', [$lastMsg['id']]);
            }
        }

        if ($result['success']) {
            $this->setFlash('success', 'Đã gửi email đến ' . $to . (!empty($attachments) ? ' (' . count($attachments) . ' đính kèm)' : ''));
            return $this->redirect('email?folder=sent');
        } else {
            // Cleanup uploaded files on failure
            foreach ($attachments as $att) {
                if (file_exists($att['path'])) unlink($att['path']);
            }
            $this->setFlash('error', 'Gửi thất bại: ' . ($result['error'] ?? 'Lỗi không xác định'));
            return $this->redirect('email/compose');
        }
    }

    // ---- Sync (IMAP fetch) ----
    public function sync()
    {
        if (!$this->isPost()) return $this->redirect('email');
        $accountId = (int)$this->input('account_id');
        $account = EmailService::getAccount($accountId ?: 0);
        if (!$account) {
            $accounts = EmailService::getAllAccounts();
            $account = $accounts[0] ?? null;
        }
        if (!$account) { $this->setFlash('error', 'Chưa cấu hình tài khoản email.'); return $this->redirect('email/settings'); }

        $service = new EmailService($account);
        $result = $service->fetchInbox();

        if ($result['success']) {
            $this->setFlash('success', 'Đã đồng bộ ' . $result['new_count'] . ' email mới.');
        } else {
            $this->setFlash('error', 'Lỗi đồng bộ: ' . ($result['error'] ?? ''));
        }
        return $this->redirect('email');
    }

    // ---- Actions ----
    public function toggleStar($id)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);
        $msg = Database::fetch("SELECT is_starred FROM email_messages WHERE id = ? AND tenant_id = ?", [(int)$id, Database::tenantId()]);
        if ($msg) {
            Database::update('email_messages', ['is_starred' => $msg['is_starred'] ? 0 : 1], 'id = ?', [(int)$id]);
        }
        return $this->json(['success' => true]);
    }

    public function downloadAttachment()
    {
        $this->authorize('email', 'view');
        $url = $this->input('file_url');
        $name = $this->input('file_name') ?: 'attachment';
        if (empty($url)) { $this->setFlash('error', 'URL không hợp lệ.'); return $this->redirect('email'); }

        // SSRF defense: validate URL + block internal IPs
        $parts = parse_url($url);
        if (!$parts || !in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'])) {
            $this->setFlash('error', 'URL không hợp lệ.'); return $this->redirect('email');
        }
        $host = $parts['host'] ?? '';
        $ips = @gethostbynamel($host) ?: [];
        foreach ($ips as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                $this->setFlash('error', 'URL nội bộ không được phép.'); return $this->redirect('email');
            }
        }

        // Verify the attachment URL actually belongs to an email_messages row user can access
        $msgId = (int)$this->input('msg_id');
        if ($msgId) {
            $msg = Database::fetch("SELECT id FROM email_messages WHERE id = ? AND tenant_id = ?", [$msgId, Database::tenantId()]);
            if (!$msg) { $this->setFlash('error', 'Không có quyền.'); return $this->redirect('email'); }
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false, // no redirects — could redirect to internal IP
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_MAXFILESIZE => 50 * 1024 * 1024, // 50MB cap
        ]);
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $mime = curl_getinfo($ch, CURLINFO_CONTENT_TYPE) ?: 'application/octet-stream';
        curl_close($ch);

        if ($content === false || $httpCode >= 400) {
            $this->setFlash('error', 'Không tải được file.');
            return $this->redirect('email');
        }
        if (strlen($content) > 50 * 1024 * 1024) {
            $this->setFlash('error', 'File vượt quá 50MB.');
            return $this->redirect('email');
        }

        // Sanitize filename for Content-Disposition
        $safeName = preg_replace('/[^\w\-\.\s]/', '_', $name);

        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $safeName . '"');
        header('Content-Length: ' . strlen($content));
        echo $content;
        exit;
    }

    public function moveToTrash($id)
    {
        if (!$this->isPost()) return $this->redirect('email');
        Database::update('email_messages', ['folder' => 'trash'], 'id = ? AND tenant_id = ?', [(int)$id, Database::tenantId()]);
        $this->setFlash('success', 'Đã chuyển vào thùng rác.');
        return $this->redirect('email');
    }

    public function delete($id)
    {
        if (!$this->isPost()) return $this->redirect('email');
        Database::query("DELETE FROM email_messages WHERE id = ? AND tenant_id = ?", [(int)$id, Database::tenantId()]);
        $this->setFlash('success', 'Đã xóa email.');
        return $this->redirect('email?folder=trash');
    }

    // ---- Settings ----
    public function settings()
    {
        if (!$this->checkPlugin()) return;
        if (!$this->isSystemAdmin()) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('email');
        }
        $accounts = EmailService::getAllAccounts();
        $accountId = $accounts[0]['id'] ?? 0;
        $folders = $accountId ? Database::fetchAll("SELECT folder, COUNT(*) as cnt, SUM(CASE WHEN is_read=0 THEN 1 ELSE 0 END) as unread FROM email_messages WHERE account_id = ? GROUP BY folder", [$accountId]) : [];
        $folder = '';
        return $this->view('plugin:email.email.settings', compact('accounts', 'accountId', 'folders', 'folder'));
    }

    public function saveAccount()
    {
        if (!$this->isPost()) return $this->redirect('email/settings');
        $tid = Database::tenantId();
        $id = (int)$this->input('id');

        $data = [
            'tenant_id' => $tid,
            'user_id' => $this->input('user_id') ? (int)$this->input('user_id') : null,
            'email' => trim($this->input('email') ?? ''),
            'display_name' => trim($this->input('display_name') ?? ''),
            'username' => trim($this->input('email') ?? ''),
            'password' => trim($this->input('api_token') ?? ''),
            'api_token' => trim($this->input('api_token') ?? ''),
            'is_default' => $this->input('is_default') ? 1 : 0,
        ];

        if ($id) {
            // Edit: token optional
            if (empty($data['email'])) {
                $this->setFlash('error', 'Vui lòng nhập email.');
                return $this->redirect('email/settings');
            }
            $updateData = [
                'email' => $data['email'],
                'username' => $data['email'],
                'display_name' => $data['display_name'],
                'user_id' => $data['user_id'],
                'is_default' => $data['is_default'],
            ];
            if (!empty($data['api_token'])) {
                $updateData['api_token'] = $data['api_token'];
                $updateData['password'] = $data['api_token'];
            }
            Database::update('email_accounts', $updateData, 'id = ? AND tenant_id = ?', [$id, $tid]);
        } else {
            if (empty($data['email']) || empty($data['api_token'])) {
                $this->setFlash('error', 'Vui lòng nhập email và API token.');
                return $this->redirect('email/settings');
            }
            Database::insert('email_accounts', $data);
        }

        $this->setFlash('success', 'Đã lưu tài khoản email.');
        return $this->redirect('email/settings');
    }

    public function testAccount()
    {
        if (!$this->isPost()) return $this->redirect('email/settings');
        $accountId = (int)$this->input('account_id');
        $account = EmailService::getAccount($accountId);
        if (!$account) { $this->setFlash('error', 'Không tìm thấy tài khoản.'); return $this->redirect('email/settings'); }

        $service = new EmailService($account);
        $result = $service->testConnection();

        if ($result['api']) {
            $this->setFlash('success', 'Kết nối API thành công cho ' . $result['email']);
        } else {
            $this->setFlash('error', 'Lỗi kết nối: ' . $result['api_error']);
        }
        return $this->redirect('email/settings');
    }

    public function deleteAccount($id)
    {
        if (!$this->isPost()) return $this->redirect('email/settings');
        $tid = Database::tenantId();
        Database::query("DELETE FROM email_messages WHERE account_id = ? AND tenant_id = ?", [(int)$id, $tid]);
        Database::query("DELETE FROM email_accounts WHERE id = ? AND tenant_id = ?", [(int)$id, $tid]);
        $this->setFlash('success', 'Đã xóa tài khoản email.');
        return $this->redirect('email/settings');
    }

    public function saveSignature()
    {
        if (!$this->isPost()) return $this->redirect('email/settings');
        $id = (int)$this->input('account_id');
        $signature = $this->input('signature') ?? '';
        Database::update('email_accounts', ['signature' => $signature], 'id = ? AND tenant_id = ?', [$id, Database::tenantId()]);
        $this->setFlash('success', 'Đã lưu chữ ký.');
        return $this->redirect('email/settings');
    }

    // ---- Bulk Actions ----
    public function bulkAction()
    {
        if (!$this->isPost()) return $this->redirect('email');
        $tid = Database::tenantId();
        $ids = $this->input('email_ids') ?: [];
        $action = $this->input('action');
        if (empty($ids)) { $this->setFlash('error', 'Chưa chọn email.'); return $this->redirect('email'); }

        $ph = implode(',', array_fill(0, count($ids), '?'));
        $params = array_merge($ids, [$tid]);

        if ($action === 'read') {
            Database::query("UPDATE email_messages SET is_read = 1 WHERE id IN ($ph) AND tenant_id = ?", $params);
            $this->setFlash('success', 'Đã đánh dấu đã đọc.');
        } elseif ($action === 'unread') {
            Database::query("UPDATE email_messages SET is_read = 0 WHERE id IN ($ph) AND tenant_id = ?", $params);
            $this->setFlash('success', 'Đã đánh dấu chưa đọc.');
        } elseif ($action === 'trash') {
            Database::query("UPDATE email_messages SET folder = 'trash' WHERE id IN ($ph) AND tenant_id = ?", $params);
            $this->setFlash('success', 'Đã chuyển vào thùng rác.');
        } elseif ($action === 'delete') {
            Database::query("DELETE FROM email_messages WHERE id IN ($ph) AND tenant_id = ?", $params);
            $this->setFlash('success', 'Đã xóa.');
        }
        return $this->redirect('email');
    }

    // ---- Templates ----
    public function templates()
    {
        if (!$this->checkPlugin()) return;
        $tid = Database::tenantId();
        $templates = Database::fetchAll("SELECT * FROM email_templates WHERE tenant_id = ? ORDER BY name", [$tid]);
        $accounts = $this->isSystemAdmin() ? EmailService::getAllAccounts() : EmailService::getAccountsForUser($this->userId());
        $accountId = $accounts[0]['id'] ?? 0;
        $folders = $accountId ? Database::fetchAll("SELECT folder, COUNT(*) as cnt, SUM(CASE WHEN is_read=0 THEN 1 ELSE 0 END) as unread FROM email_messages WHERE account_id = ? GROUP BY folder", [$accountId]) : [];
        $folder = '';
        return $this->view('plugin:email.email.templates', compact('templates', 'accounts', 'accountId', 'folders', 'folder'));
    }

    public function saveTemplate()
    {
        if (!$this->isPost()) return $this->redirect('email/templates');
        $tid = Database::tenantId();
        $id = (int)$this->input('id');
        $data = [
            'name' => trim($this->input('name') ?? ''),
            'subject' => trim($this->input('subject') ?? ''),
            'body' => $this->input('body') ?? '',
        ];
        if (empty($data['name'])) { $this->setFlash('error', 'Nhập tên mẫu.'); return $this->redirect('email/templates'); }

        if ($id) {
            Database::update('email_templates', $data, 'id = ? AND tenant_id = ?', [$id, $tid]);
        } else {
            $data['tenant_id'] = $tid;
            $data['created_by'] = $this->userId();
            Database::insert('email_templates', $data);
        }
        $this->setFlash('success', 'Đã lưu mẫu email.');
        return $this->redirect('email/templates');
    }

    public function deleteTemplate($id)
    {
        if (!$this->isPost()) return $this->redirect('email/templates');
        Database::query("DELETE FROM email_templates WHERE id = ? AND tenant_id = ?", [(int)$id, Database::tenantId()]);
        $this->setFlash('success', 'Đã xóa mẫu.');
        return $this->redirect('email/templates');
    }
}
