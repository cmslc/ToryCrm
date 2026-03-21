<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class Webhook extends Model
{
    protected string $table = 'webhooks';

    const EVENTS = [
        'customer.created', 'customer.updated', 'customer.deleted',
        'order.created', 'order.approved',
        'product.created', 'product.updated',
        'campaign.created', 'campaign.updated',
        'opportunity.created', 'opportunity.updated',
        'task.created', 'task.updated',
        'ticket.created', 'ticket.updated',
    ];

    public function getLogs(int $webhookId, int $limit = 20): array
    {
        return Database::fetchAll(
            "SELECT * FROM webhook_logs WHERE webhook_id = ? ORDER BY created_at DESC LIMIT {$limit}",
            [$webhookId]
        );
    }

    public static function trigger(string $event, array $data): void
    {
        $webhooks = Database::fetchAll(
            "SELECT * FROM webhooks WHERE is_active = 1 AND JSON_CONTAINS(events, ?)",
            [json_encode($event)]
        );

        foreach ($webhooks as $webhook) {
            $payload = [
                'event' => $event,
                'data' => $data,
                'domain' => $_ENV['APP_URL'] ?? '',
                'timestamp' => time(),
            ];

            if ($webhook['secret_key']) {
                $payload['secret_key'] = $webhook['secret_key'];
            }

            // Log the webhook attempt
            $logId = Database::insert('webhook_logs', [
                'webhook_id' => $webhook['id'],
                'event' => $event,
                'payload' => json_encode($payload),
                'status' => 'pending',
            ]);

            // Fire async (best effort - in production use queue)
            try {
                $ch = curl_init($webhook['url']);
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => json_encode($payload),
                    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_CONNECTTIMEOUT => 5,
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $duration = (int)(curl_getinfo($ch, CURLINFO_TOTAL_TIME) * 1000);
                curl_close($ch);

                Database::update('webhook_logs', [
                    'response_code' => $httpCode,
                    'response_body' => substr($response ?: '', 0, 2000),
                    'duration_ms' => $duration,
                    'status' => ($httpCode >= 200 && $httpCode < 300) ? 'success' : 'failed',
                ], 'id = ?', [$logId]);

                Database::update('webhooks', [
                    'last_triggered_at' => date('Y-m-d H:i:s'),
                    'last_response_code' => $httpCode,
                    'fail_count' => ($httpCode >= 200 && $httpCode < 300) ? 0 : $webhook['fail_count'] + 1,
                ], 'id = ?', [$webhook['id']]);
            } catch (\Exception $e) {
                Database::update('webhook_logs', [
                    'response_body' => $e->getMessage(),
                    'status' => 'failed',
                ], 'id = ?', [$logId]);
            }
        }
    }
}
