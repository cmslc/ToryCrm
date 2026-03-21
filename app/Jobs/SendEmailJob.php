<?php

namespace App\Jobs;

use App\Services\MailService;

class SendEmailJob
{
    /**
     * Handle the send email job.
     */
    public static function handle(array $payload): void
    {
        $to          = $payload['to'] ?? '';
        $subject     = $payload['subject'] ?? '';
        $body        = $payload['body'] ?? '';
        $toName      = $payload['toName'] ?? null;
        $attachments = $payload['attachments'] ?? [];

        MailService::send($to, $subject, $body, $toName, $attachments);
    }
}
