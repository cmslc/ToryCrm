<?php

namespace App\Services;

use Core\Database;

class VoipService
{
    private const CALL_URL = 'https://api.stringee.com/v1/call2/callout';
    private const CALL_STATUS_URL = 'https://api.stringee.com/v1/call2/';

    /**
     * Initiate a call via Stringee REST API
     */
    public static function makeCall(string $from, string $to, int $userId): array
    {
        $tenantId = (int) ($_SESSION['tenant_id'] ?? 1);

        $integration = Database::fetch(
            "SELECT * FROM integrations WHERE tenant_id = ? AND provider = 'voip_stringee' LIMIT 1",
            [$tenantId]
        );

        if (!$integration) {
            return ['success' => false, 'error' => 'Chưa cấu hình VoIP/Stringee'];
        }

        $config = json_decode($integration['config'] ?? '{}', true);
        $apiKeySid = $config['api_key_sid'] ?? '';
        $apiKeySecret = $config['api_key_secret'] ?? '';

        if (empty($apiKeySid) || empty($apiKeySecret)) {
            return ['success' => false, 'error' => 'Thiếu API Key SID hoặc Secret'];
        }

        $token = self::generateApiToken($apiKeySid, $apiKeySecret);

        $payload = json_encode([
            'from' => ['type' => 'internal', 'number' => $from, 'alias' => $from],
            'to' => [['type' => 'external', 'number' => $to, 'alias' => $to]],
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", [
                    'Content-Type: application/json',
                    'X-STRINGEE-AUTH: ' . $token,
                ]),
                'content' => $payload,
                'timeout' => 15,
            ],
        ]);

        $response = @file_get_contents(self::CALL_URL, false, $context);
        $result = $response ? json_decode($response, true) : null;

        $callId = $result['callId'] ?? uniqid('call_');

        // Log call
        try {
            Database::insert('call_logs', [
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'call_type' => 'outbound',
                'caller_number' => $from,
                'callee_number' => $to,
                'status' => ($result && ($result['r'] ?? -1) === 0) ? 'answered' : 'failed',
                'provider_code' => $callId,
                'notes' => 'Stringee click-to-call',
                'started_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // silently continue
        }

        if ($result && ($result['r'] ?? -1) === 0) {
            return ['success' => true, 'call_id' => $callId];
        }

        return ['success' => false, 'error' => $result['message'] ?? 'Không thể thực hiện cuộc gọi'];
    }

    /**
     * Get call status from Stringee
     */
    public static function getCallStatus(string $callId): array
    {
        $tenantId = (int) ($_SESSION['tenant_id'] ?? 1);

        $integration = Database::fetch(
            "SELECT * FROM integrations WHERE tenant_id = ? AND provider = 'voip_stringee' LIMIT 1",
            [$tenantId]
        );

        if (!$integration) {
            return ['success' => false, 'error' => 'Chưa cấu hình VoIP'];
        }

        $config = json_decode($integration['config'] ?? '{}', true);
        $token = self::generateApiToken($config['api_key_sid'] ?? '', $config['api_key_secret'] ?? '');

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => 'X-STRINGEE-AUTH: ' . $token,
                'timeout' => 10,
            ],
        ]);

        $response = @file_get_contents(self::CALL_STATUS_URL . $callId, false, $context);
        $result = $response ? json_decode($response, true) : null;

        if ($result && ($result['r'] ?? -1) === 0) {
            return ['success' => true, 'data' => $result];
        }

        return ['success' => false, 'error' => 'Không lấy được trạng thái cuộc gọi'];
    }

    /**
     * Handle call events from Stringee webhook
     */
    public static function handleCallEvent(array $data): void
    {
        $callId = $data['call_id'] ?? $data['callId'] ?? '';
        $callStatus = $data['call_status'] ?? $data['callStatus'] ?? '';
        $duration = (int) ($data['duration'] ?? 0);

        if (empty($callId)) {
            return;
        }

        // Map Stringee statuses to DB ENUM: answered, missed, busy, failed, voicemail
        $statusMap = [
            'ringing' => null, // don't update yet
            'answered' => 'answered',
            'ended' => 'answered',
            'busy' => 'busy',
            'no-answer' => 'missed',
        ];

        $mappedStatus = $statusMap[$callStatus] ?? null;

        try {
            $existing = Database::fetch(
                "SELECT id FROM call_logs WHERE provider_code = ? LIMIT 1",
                [$callId]
            );

            if ($existing) {
                $updateData = [];

                if ($duration > 0) {
                    $updateData['duration'] = $duration;
                }

                if ($callStatus === 'answered') {
                    $updateData['answered_at'] = date('Y-m-d H:i:s');
                    $updateData['status'] = 'answered';
                }

                if ($callStatus === 'ended') {
                    $updateData['ended_at'] = date('Y-m-d H:i:s');
                    if ($duration > 0) {
                        $updateData['duration'] = $duration;
                    }
                }

                if ($callStatus === 'busy') {
                    $updateData['status'] = 'busy';
                }

                if ($callStatus === 'no-answer') {
                    $updateData['status'] = 'missed';
                }

                if (!empty($updateData)) {
                    Database::update('call_logs', $updateData, 'id = ?', [$existing['id']]);
                }
            }
        } catch (\Throwable $e) {
            // silently continue
        }
    }

    /**
     * Generate Stringee client JWT token for browser-based calling
     */
    public static function generateToken(int $userId): ?string
    {
        $tenantId = (int) ($_SESSION['tenant_id'] ?? 1);

        $integration = Database::fetch(
            "SELECT * FROM integrations WHERE tenant_id = ? AND provider = 'voip_stringee' LIMIT 1",
            [$tenantId]
        );

        if (!$integration) {
            return null;
        }

        $config = json_decode($integration['config'] ?? '{}', true);
        $apiKeySid = $config['api_key_sid'] ?? '';
        $apiKeySecret = $config['api_key_secret'] ?? '';

        if (empty($apiKeySid) || empty($apiKeySecret)) {
            return null;
        }

        $now = time();
        $exp = $now + 3600; // 1 hour

        $header = self::base64UrlEncode(json_encode([
            'cty' => 'stringee-api;v=1',
            'typ' => 'JWT',
            'alg' => 'HS256',
        ]));

        $payload = self::base64UrlEncode(json_encode([
            'jti' => $apiKeySid . '-' . $now,
            'iss' => $apiKeySid,
            'exp' => $exp,
            'userId' => 'user_' . $userId,
        ]));

        $signature = self::base64UrlEncode(
            hash_hmac('sha256', $header . '.' . $payload, $apiKeySecret, true)
        );

        return $header . '.' . $payload . '.' . $signature;
    }

    /**
     * Generate API auth token for REST API calls
     */
    private static function generateApiToken(string $apiKeySid, string $apiKeySecret): string
    {
        $now = time();
        $exp = $now + 3600;

        $header = self::base64UrlEncode(json_encode([
            'cty' => 'stringee-api;v=1',
            'typ' => 'JWT',
            'alg' => 'HS256',
        ]));

        $payload = self::base64UrlEncode(json_encode([
            'jti' => $apiKeySid . '-' . $now,
            'iss' => $apiKeySid,
            'exp' => $exp,
            'rest_api' => true,
        ]));

        $signature = self::base64UrlEncode(
            hash_hmac('sha256', $header . '.' . $payload, $apiKeySecret, true)
        );

        return $header . '.' . $payload . '.' . $signature;
    }

    /**
     * Base64 URL encode (no padding)
     */
    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
