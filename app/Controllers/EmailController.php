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

        $accounts = EmailService::getAllAccounts();
        if (empty($accounts)) {
            return $this->view('email.inbox', ['accounts' => [], 'messages' => [], 'total' => 0, 'page' => 1, 'totalPages' => 0, 'folder' => $folder, 'accountId' => 0, 'search' => '', 'unreadCount' => 0]);
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

        return $this->view('email.inbox', compact('accounts', 'messages', 'total', 'page', 'totalPages', 'folder', 'accountId', 'search', 'unreadCount', 'folders'));
    }

    // ---- Read ----
    public function read($id)
    {
        if (!$this->checkPlugin()) return;
        $msg = Database::fetch(
            "SELECT em.*, c.first_name as contact_first, c.last_name as contact_last, ea.email as account_email, ea.display_name as account_name
             FROM email_messages em
             LEFT JOIN contacts c ON em.contact_id = c.id
             LEFT JOIN email_accounts ea ON em.account_id = ea.id
             WHERE em.id = ? AND em.tenant_id = ?",
            [(int)$id, Database::tenantId()]
        );
        if (!$msg) { $this->setFlash('error', 'Email không tồn tại.'); return $this->redirect('email'); }

        // Mark as read
        if (!$msg['is_read']) {
            Database::update('email_messages', ['is_read' => 1], 'id = ?', [$id]);
        }

        return $this->view('email.read', ['message' => $msg]);
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
        $contactEmail = $this->input('to');
        return $this->view('email.compose', compact('accounts', 'replyMsg', 'contactEmail'));
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

        if (empty($to) || empty($subject)) {
            $this->setFlash('error', 'Vui lòng nhập người nhận và tiêu đề.');
            return $this->redirect('email/compose');
        }

        $service = new EmailService($account);
        $result = $service->send($to, $subject, $body, $cc);

        if ($result['success']) {
            $this->setFlash('success', 'Đã gửi email đến ' . $to);
            return $this->redirect('email?folder=sent');
        } else {
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
        $accounts = EmailService::getAllAccounts();
        return $this->view('email.settings', compact('accounts'));
    }

    public function saveAccount()
    {
        if (!$this->isPost()) return $this->redirect('email/settings');
        $tid = Database::tenantId();
        $id = (int)$this->input('id');

        $data = [
            'tenant_id' => $tid,
            'user_id' => $this->userId(),
            'email' => trim($this->input('email') ?? ''),
            'display_name' => trim($this->input('display_name') ?? ''),
            'imap_host' => trim($this->input('imap_host') ?? ''),
            'imap_port' => (int)($this->input('imap_port') ?: 993),
            'imap_encryption' => $this->input('imap_encryption') ?: 'ssl',
            'smtp_host' => trim($this->input('smtp_host') ?? ''),
            'smtp_port' => (int)($this->input('smtp_port') ?: 587),
            'smtp_encryption' => $this->input('smtp_encryption') ?: 'tls',
            'username' => trim($this->input('email') ?? ''),
            'password' => trim($this->input('password') ?? ''),
            'is_default' => $this->input('is_default') ? 1 : 0,
        ];

        if (empty($data['email']) || empty($data['password'])) {
            $this->setFlash('error', 'Vui lòng nhập đầy đủ thông tin.');
            return $this->redirect('email/settings');
        }

        if ($id) {
            if (empty($data['password'])) unset($data['password']);
            unset($data['tenant_id'], $data['user_id']);
            Database::update('email_accounts', $data, 'id = ? AND tenant_id = ?', [$id, $tid]);
        } else {
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

        $msg = 'IMAP: ' . ($result['imap'] ? 'OK' : 'Lỗi - ' . $result['imap_error']);
        $msg .= ' | SMTP: ' . ($result['smtp'] ? 'OK' : 'Lỗi - ' . $result['smtp_error']);
        $this->setFlash($result['imap'] && $result['smtp'] ? 'success' : 'warning', $msg);
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
}
