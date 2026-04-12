<?php

namespace App\Services;

use Core\Database;

class EmailService
{
    private array $account;

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

    // ---- SMTP Send ----
    public function send(string $to, string $subject, string $body, array $cc = [], array $bcc = [], array $attachments = []): array
    {
        $a = $this->account;
        $from = $a['display_name'] ? "{$a['display_name']} <{$a['email']}>" : $a['email'];

        $headers = [
            'From' => $from,
            'Reply-To' => $a['email'],
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Mailer' => 'ToryCRM',
            'Date' => date('r'),
            'Message-ID' => '<' . uniqid('torycrm_') . '@' . explode('@', $a['email'])[1] . '>',
        ];
        if (!empty($cc)) $headers['Cc'] = implode(', ', $cc);

        // Build message with attachments
        if (!empty($attachments)) {
            $boundary = 'ToryCRM_' . md5(uniqid());
            $headers['Content-Type'] = 'multipart/mixed; boundary="' . $boundary . '"';

            $message = "--{$boundary}\r\n";
            $message .= "Content-Type: text/html; charset=UTF-8\r\n";
            $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $message .= chunk_split(base64_encode($body)) . "\r\n";

            foreach ($attachments as $att) {
                if (!file_exists($att['path'])) continue;
                $message .= "--{$boundary}\r\n";
                $message .= "Content-Type: {$att['mime']}; name=\"{$att['name']}\"\r\n";
                $message .= "Content-Disposition: attachment; filename=\"{$att['name']}\"\r\n";
                $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
                $message .= chunk_split(base64_encode(file_get_contents($att['path']))) . "\r\n";
            }
            $message .= "--{$boundary}--\r\n";
        } else {
            $message = $body;
        }

        // Send via SMTP
        $result = $this->smtpSend($to, $subject, $message, $headers, $bcc);

        if ($result['success']) {
            // Store in sent folder
            Database::insert('email_messages', [
                'tenant_id' => $a['tenant_id'],
                'account_id' => $a['id'],
                'folder' => 'sent',
                'from_email' => $a['email'],
                'from_name' => $a['display_name'],
                'to_emails' => $to,
                'cc_emails' => implode(', ', $cc),
                'subject' => $subject,
                'body_html' => $body,
                'is_read' => 1,
                'sent_at' => date('Y-m-d H:i:s'),
            ]);

            // Auto-link to contact
            $this->autoLinkContact($to);
        }

        return $result;
    }

    private function smtpSend(string $to, string $subject, string $body, array $headers, array $bcc = []): array
    {
        $a = $this->account;
        $host = $a['smtp_host'];
        $port = (int)$a['smtp_port'];
        $user = $a['username'];
        $pass = $a['password'];
        $encryption = $a['smtp_encryption'];

        try {
            $prefix = ($encryption === 'ssl') ? 'ssl://' : '';
            $socket = @fsockopen($prefix . $host, $port, $errno, $errstr, 10);
            if (!$socket) return ['success' => false, 'error' => "Không kết nối được SMTP: {$errstr}"];

            stream_set_timeout($socket, 10);
            $this->smtpRead($socket);

            $this->smtpWrite($socket, "EHLO " . gethostname());
            $this->smtpRead($socket);

            if ($encryption === 'tls') {
                $this->smtpWrite($socket, "STARTTLS");
                $this->smtpRead($socket);
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                $this->smtpWrite($socket, "EHLO " . gethostname());
                $this->smtpRead($socket);
            }

            $this->smtpWrite($socket, "AUTH LOGIN");
            $this->smtpRead($socket);
            $this->smtpWrite($socket, base64_encode($user));
            $this->smtpRead($socket);
            $this->smtpWrite($socket, base64_encode($pass));
            $response = $this->smtpRead($socket);
            if (strpos($response, '235') === false) {
                fclose($socket);
                return ['success' => false, 'error' => 'Xác thực SMTP thất bại'];
            }

            $this->smtpWrite($socket, "MAIL FROM:<{$a['email']}>");
            $this->smtpRead($socket);

            // Recipients
            $allRecipients = array_merge([$to], $bcc);
            foreach (explode(',', implode(',', $allRecipients)) as $rcpt) {
                $rcpt = trim($rcpt);
                if (filter_var($rcpt, FILTER_VALIDATE_EMAIL)) {
                    $this->smtpWrite($socket, "RCPT TO:<{$rcpt}>");
                    $this->smtpRead($socket);
                }
            }

            $this->smtpWrite($socket, "DATA");
            $this->smtpRead($socket);

            $headerStr = '';
            foreach ($headers as $k => $v) $headerStr .= "{$k}: {$v}\r\n";
            $headerStr .= "To: {$to}\r\n";
            $headerStr .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";

            $this->smtpWrite($socket, $headerStr . "\r\n" . $body . "\r\n.");
            $this->smtpRead($socket);

            $this->smtpWrite($socket, "QUIT");
            fclose($socket);

            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function smtpWrite($socket, string $data): void
    {
        fwrite($socket, $data . "\r\n");
    }

    private function smtpRead($socket): string
    {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }
        return $response;
    }

    // ---- IMAP Fetch ----
    public function fetchInbox(int $limit = 50): array
    {
        $a = $this->account;
        $host = $a['imap_host'];
        $port = (int)$a['imap_port'];
        $user = $a['username'];
        $pass = $a['password'];
        $encryption = $a['imap_encryption'];

        $flag = ($encryption === 'ssl') ? '/imap/ssl/novalidate-cert' : '/imap/novalidate-cert';
        $mailbox = "{{$host}:{$port}{$flag}}INBOX";

        $connection = @\imap_open($mailbox, $user, $pass, 0, 1);
        if (!$connection) {
            return ['success' => false, 'error' => 'Không kết nối được IMAP: ' . \imap_last_error()];
        }

        $check = \imap_check($connection);
        $total = $check->Nmsgs;
        $start = max(1, $total - $limit + 1);

        $messages = [];
        for ($i = $total; $i >= $start; $i--) {
            $header = \imap_headerinfo($connection, $i);
            $uid = \imap_uid($connection, $i);

            // Skip if already fetched
            $existing = Database::fetch("SELECT id FROM email_messages WHERE account_id = ? AND message_uid = ?", [$a['id'], (string)$uid]);
            if ($existing) continue;

            $from = $header->from[0] ?? null;
            $fromEmail = $from ? ($from->mailbox . '@' . $from->host) : '';
            $fromName = isset($from->personal) ? \imap_utf8($from->personal) : '';
            $subject = isset($header->subject) ? \imap_utf8($header->subject) : '(Không tiêu đề)';
            $date = isset($header->date) ? date('Y-m-d H:i:s', strtotime($header->date)) : date('Y-m-d H:i:s');

            $toList = [];
            if (!empty($header->to)) {
                foreach ($header->to as $t) $toList[] = $t->mailbox . '@' . $t->host;
            }
            $ccList = [];
            if (!empty($header->cc)) {
                foreach ($header->cc as $c) $ccList[] = $c->mailbox . '@' . $c->host;
            }

            // Get body
            $body = $this->getImapBody($connection, $i);
            $hasAttach = $this->hasAttachments($connection, $i);

            $msgId = Database::insert('email_messages', [
                'tenant_id' => $a['tenant_id'],
                'account_id' => $a['id'],
                'message_uid' => (string)$uid,
                'folder' => 'inbox',
                'from_email' => $fromEmail,
                'from_name' => $fromName,
                'to_emails' => implode(', ', $toList),
                'cc_emails' => implode(', ', $ccList),
                'subject' => mb_substr($subject, 0, 500),
                'body_html' => $body['html'] ?? '',
                'body_text' => $body['text'] ?? '',
                'is_read' => 0,
                'has_attachments' => $hasAttach ? 1 : 0,
                'sent_at' => $date,
            ]);

            // Auto-link contact
            $this->autoLinkContact($fromEmail, $msgId);

            $messages[] = ['id' => $msgId, 'from' => $fromEmail, 'subject' => $subject];
        }

        // Update last sync
        Database::update('email_accounts', ['last_sync' => date('Y-m-d H:i:s')], 'id = ?', [$a['id']]);

        \imap_close($connection);
        return ['success' => true, 'new_count' => count($messages), 'messages' => $messages];
    }

    private function getImapBody($connection, int $msgNo): array
    {
        $body = ['text' => '', 'html' => ''];
        $structure = \imap_fetchstructure($connection, $msgNo);

        if (!isset($structure->parts)) {
            // Simple message
            $content = \imap_fetchbody($connection, $msgNo, '1');
            $content = $this->decodeBody($content, $structure->encoding ?? 0);
            if (isset($structure->subtype) && strtoupper($structure->subtype) === 'HTML') {
                $body['html'] = $content;
            } else {
                $body['text'] = $content;
            }
        } else {
            foreach ($structure->parts as $idx => $part) {
                $partNo = (string)($idx + 1);
                $content = \imap_fetchbody($connection, $msgNo, $partNo);
                $content = $this->decodeBody($content, $part->encoding ?? 0);

                if (strtoupper($part->subtype ?? '') === 'HTML') {
                    $body['html'] = $content;
                } elseif (strtoupper($part->subtype ?? '') === 'PLAIN') {
                    $body['text'] = $content;
                }
            }
        }

        return $body;
    }

    private function decodeBody(string $body, int $encoding): string
    {
        return match ($encoding) {
            3 => base64_decode($body),      // BASE64
            4 => quoted_printable_decode($body), // QUOTED-PRINTABLE
            default => $body,
        };
    }

    private function hasAttachments($connection, int $msgNo): bool
    {
        $structure = \imap_fetchstructure($connection, $msgNo);
        if (!isset($structure->parts)) return false;
        foreach ($structure->parts as $part) {
            if (($part->disposition ?? '') === 'ATTACHMENT' || ($part->ifdisposition && strtoupper($part->disposition) === 'ATTACHMENT')) {
                return true;
            }
        }
        return false;
    }

    private function autoLinkContact(string $email, ?int $msgId = null): void
    {
        $tid = $this->account['tenant_id'];
        $contact = Database::fetch("SELECT id FROM contacts WHERE tenant_id = ? AND email = ? AND is_deleted = 0", [$tid, $email]);
        if ($contact && $msgId) {
            Database::update('email_messages', ['contact_id' => $contact['id']], 'id = ?', [$msgId]);
        }
    }

    // ---- Test Connection ----
    public function testConnection(): array
    {
        $a = $this->account;
        $results = ['imap' => false, 'smtp' => false, 'imap_error' => '', 'smtp_error' => ''];

        // Test IMAP
        $flag = ($a['imap_encryption'] === 'ssl') ? '/imap/ssl/novalidate-cert' : '/imap/novalidate-cert';
        $mailbox = "{{$a['imap_host']}:{$a['imap_port']}{$flag}}INBOX";
        $conn = @\imap_open($mailbox, $a['username'], $a['password'], 0, 1);
        if ($conn) {
            $results['imap'] = true;
            \imap_close($conn);
        } else {
            $results['imap_error'] = \imap_last_error() ?: 'Không kết nối được';
        }

        // Test SMTP
        $prefix = ($a['smtp_encryption'] === 'ssl') ? 'ssl://' : '';
        $socket = @fsockopen($prefix . $a['smtp_host'], (int)$a['smtp_port'], $errno, $errstr, 5);
        if ($socket) {
            $results['smtp'] = true;
            fclose($socket);
        } else {
            $results['smtp_error'] = $errstr ?: 'Không kết nối được';
        }

        return $results;
    }
}
