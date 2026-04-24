<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use Core\Database;

class MailService
{
    /**
     * Send an email via SMTP using PHPMailer.
     */
    public static function send(string $to, string $subject, string $body, ?string $toName = null, array $attachments = []): bool
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST'] ?? 'localhost';
            $mail->Port       = $_ENV['MAIL_PORT'] ?? 587;
            $mail->Username   = $_ENV['MAIL_USERNAME'] ?? '';
            $mail->Password   = $_ENV['MAIL_PASSWORD'] ?? '';
            $mail->SMTPAuth   = true;
            $mail->SMTPSecure = 'tls';
            $mail->CharSet    = 'UTF-8';

            // Priority: tenant setting > .env > hardcoded fallback
            $fromEmail = tenant_setting('email_from_email') ?: ($_ENV['MAIL_FROM'] ?? 'noreply@example.com');
            $fromName  = tenant_setting('email_from_name')  ?: ($_ENV['MAIL_FROM_NAME'] ?? 'ToryCRM');
            $mail->setFrom($fromEmail, $fromName);

            $mail->addAddress($to, $toName ?? '');
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            foreach ($attachments as $attachment) {
                if (is_array($attachment)) {
                    $mail->addAttachment($attachment['path'], $attachment['name'] ?? '');
                } else {
                    $mail->addAttachment($attachment);
                }
            }

            $mail->send();

            try {
                Database::insert('email_logs', [
                    'to_email'   => $to,
                    'to_name'    => $toName,
                    'subject'    => $subject,
                    'body'       => $body,
                    'status'     => 'sent',
                    'sent_at'    => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            } catch (\Throwable $ignore) {}

            return true;
        } catch (\Exception $e) {
            try {
                Database::insert('email_logs', [
                    'to_email'      => $to,
                    'to_name'       => $toName,
                    'subject'       => $subject,
                    'body'          => $body,
                    'status'        => 'failed',
                    'error_message' => $e->getMessage(),
                    'created_at'    => date('Y-m-d H:i:s'),
                ]);
            } catch (\Throwable $ignore) {}
            error_log('[MailService] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send an email using a stored template with variable substitution.
     */
    public static function sendTemplate(int $templateId, string $to, array $variables = []): bool
    {
        try {
            $template = Database::fetch("SELECT * FROM email_templates WHERE id = ?", [$templateId]);

            if (!$template) {
                return false;
            }

            $subject = $template['subject'];
            $body    = $template['body'];

            foreach ($variables as $key => $value) {
                $subject = str_replace('{{' . $key . '}}', $value, $subject);
                $body    = str_replace('{{' . $key . '}}', $value, $body);
            }

            return self::send($to, $subject, $body);
        } catch (\Exception $e) {
            return false;
        }
    }
}
