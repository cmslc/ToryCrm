<?php

namespace App\Jobs;

use App\Models\Webhook;

class TriggerWebhookJob
{
    /**
     * Handle the webhook trigger job.
     */
    public static function handle(array $payload): void
    {
        $event = $payload['event'] ?? '';
        $data  = $payload['data'] ?? [];

        Webhook::trigger($event, $data);
    }
}
