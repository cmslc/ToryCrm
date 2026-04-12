<?php

namespace App\Services;

use Core\Database;

class EmailService
{
    private array $account;
    private string $apiBase = 'https://getcodemail.com/api/v1';

    public function __construct(array $account)
    {
        $this->account = $account;
    }

    public static function getAccount(int $accountId): ?array
    {
        return Database::fetch("SELECT * FROM email_accounts WHERE id = ? AND tenant_id = ?", [$accountId, Database::tenantId()]);
    }

    public static function getDefaultAccount(?int $userId = null): ?array
    {
        $tid = Database::tenantId();
        if ($userId) {
            $acc = Database::fetch("SELECT * FROM email_accounts WHERE tenant_id = ? AND user_id = ? AND is_active = 1 ORDER BY is_default DESC LIMIT 1", [$tid, $userId]);
            if ($acc) return $acc;
        }
        return Database::fetch("SELECT * FROM email_accounts WHERE tenant_id = ? AND is_active = 1 ORDER BY is_default DESC LIMIT 1", [$tid]);
    }

    public static function getAllAccounts(): array
    {
        return Database::fetchAll("SELECT ea.*, u.name as user_name FROM email_accounts ea LEFT JOIN users u ON ea.user_id = u.id WHERE ea.tenant_id = ? ORDER BY ea.is_default DESC, ea.email", [Database::tenantId()]);
    }

    public static function getAccountsForUser(int $userId): array
    {
        $tid = Database::tenantId();
        return Database::fetchAll(
            "SELECT ea.*, u.name as user_name FROM email_accounts ea LEFT JOIN users u ON ea.user_id = u.id WHERE ea.tenant_id = ? AND (ea.user_id = ? OR ea.user_id IS NULL) AND ea.is_active = 1 ORDER BY ea.is_default DESC, ea.email",
            [$tid, $userId]
        );
    }

    // ---- API Call ----
    private function apiCall(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->apiBase . $endpoint;
        $token = $this->account['api_token'] ?? '';

        $ch = curl_init();
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
        ];
        if ($token) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        if ($method === 'GET') {
            if (!empty($data)) $url .= '?' . http_build_query($data);
        } else {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        if ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => 'Lỗi kết nối: ' . $error];
        }

        $result = json_decode($response, true);
        if (!$result) {
            return ['success' => false, 'error' => 'Phản hồi không hợp lệ (HTTP ' . $httpCode . ')'];
        }

        return $result;
    }

    // ---- Send Email via API ----
    public function send(string $to, string $subject, string $body, array $cc = [], array $bcc = []): array
    {
        $data = [
            'to' => $to,
            'subject' => $subject,
            'body' => $body,
        ];
        if (!empty($cc)) $data['cc'] = implode(',', $cc);
        if (!empty($bcc)) $data['bcc'] = implode(',', $bcc);

        $result = $this->apiCall('POST', '/send', $data);

        if ($result['success'] ?? false) {
            // Store in local sent folder
            Database::insert('email_messages', [
                'tenant_id' => $this->account['tenant_id'],
                'account_id' => $this->account['id'],
                'folder' => 'sent',
                'from_email' => $this->account['email'],
                'from_name' => $this->account['display_name'],
                'to_emails' => $to,
                'cc_emails' => implode(', ', $cc),
                'subject' => $subject,
                'body_html' => $body,
                'is_read' => 1,
                'sent_at' => date('Y-m-d H:i:s'),
            ]);
            $this->autoLinkContact($to);
        }

        return $result;
    }

    // ---- Fetch Inbox via API ----
    public function fetchInbox(): array
    {
        $email = $this->account['email'];
        $result = $this->apiCall('GET', '/inbox/' . urlencode($email));

        if (!($result['success'] ?? false)) {
            return ['success' => false, 'error' => $result['error'] ?? 'Lỗi lấy inbox', 'new_count' => 0];
        }

        $emails = $result['emails'] ?? [];
        $newCount = 0;

        foreach ($emails as $email) {
            $uid = (string)($email['id'] ?? '');
            // Skip if already fetched
            $existing = Database::fetch("SELECT id FROM email_messages WHERE account_id = ? AND message_uid = ?", [$this->account['id'], $uid]);
            if ($existing) continue;

            $msgId = Database::insert('email_messages', [
                'tenant_id' => $this->account['tenant_id'],
                'account_id' => $this->account['id'],
                'message_uid' => $uid,
                'folder' => 'inbox',
                'from_email' => $email['from_address'] ?? $email['from'] ?? '',
                'from_name' => $email['from_name'] ?? '',
                'to_emails' => is_array($email['to_addresses'] ?? null) ? implode(', ', $email['to_addresses']) : ($email['to'] ?? ''),
                'cc_emails' => is_array($email['cc_addresses'] ?? null) ? implode(', ', $email['cc_addresses']) : '',
                'subject' => mb_substr($email['subject'] ?? '(Không tiêu đề)', 0, 500),
                'body_html' => $email['body_html'] ?? '',
                'body_text' => $email['body_text'] ?? strip_tags($email['body_html'] ?? ''),
                'is_read' => ($email['is_read'] ?? false) ? 1 : 0,
                'has_attachments' => ($email['has_attachments'] ?? false) ? 1 : 0,
                'sent_at' => $email['received_at'] ?? $email['sent_at'] ?? $email['date'] ?? date('Y-m-d H:i:s'),
            ]);

            $this->autoLinkContact($email['from_address'] ?? $email['from'] ?? '', $msgId);
            $newCount++;
        }

        // Update last sync
        Database::update('email_accounts', ['last_sync' => date('Y-m-d H:i:s')], 'id = ?', [$this->account['id']]);

        return ['success' => true, 'new_count' => $newCount];
    }

    // ---- Read Email Detail via API ----
    public function readEmail(string $email, int $emailId): array
    {
        return $this->apiCall('GET', '/read/' . urlencode($email) . '/' . $emailId);
    }

    // ---- Test Connection ----
    public function testConnection(): array
    {
        $email = $this->account['email'];
        $result = $this->apiCall('GET', '/inbox/' . urlencode($email));
        $inboxOk = ($result['success'] ?? false);

        return [
            'api' => $inboxOk,
            'api_error' => $inboxOk ? '' : ($result['error'] ?? 'Token không hợp lệ hoặc lỗi kết nối'),
            'email' => $this->account['email'],
        ];
    }

    // ---- Auto-link Contact ----
    private function autoLinkContact(string $email, ?int $msgId = null): void
    {
        if (empty($email)) return;
        $tid = $this->account['tenant_id'];
        $contact = Database::fetch("SELECT id FROM contacts WHERE tenant_id = ? AND email = ? AND is_deleted = 0", [$tid, $email]);
        if ($contact && $msgId) {
            Database::update('email_messages', ['contact_id' => $contact['id']], 'id = ?', [$msgId]);
        }
    }
}
