<?php

namespace App\Services;

/**
 * Queued email service - dispatches emails through the job queue
 * instead of sending synchronously.
 */
class QueuedMailService
{
    /**
     * Queue an email for async sending
     */
    public static function send(string $to, string $subject, string $body, ?string $toName = null, array $attachments = []): int
    {
        return JobQueue::dispatch('App\Jobs\SendEmailJob', [
            'to' => $to,
            'subject' => $subject,
            'body' => $body,
            'toName' => $toName,
            'attachments' => $attachments,
        ], 'email');
    }

    /**
     * Queue a template-based email
     */
    public static function sendTemplate(int $templateId, string $to, array $variables = [], ?string $toName = null): int
    {
        return JobQueue::dispatch('App\Jobs\SendTemplateEmailJob', [
            'template_id' => $templateId,
            'to' => $to,
            'to_name' => $toName,
            'variables' => $variables,
        ], 'email');
    }

    /**
     * Send immediately (bypass queue) - for critical emails like password reset
     */
    public static function sendNow(string $to, string $subject, string $body, ?string $toName = null): bool
    {
        return MailService::send($to, $subject, $body, $toName);
    }
}
