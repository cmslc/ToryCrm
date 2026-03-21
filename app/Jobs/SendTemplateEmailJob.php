<?php

namespace App\Jobs;

use App\Services\MailService;

class SendTemplateEmailJob
{
    public static function handle(array $payload): void
    {
        MailService::sendTemplate(
            $payload['template_id'],
            $payload['to'],
            $payload['variables'] ?? [],
            $payload['to_name'] ?? null
        );
    }
}
