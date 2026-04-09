<?php

namespace App\Services;

use Core\Database;

class ZaloService
{
    private const TOKEN_URL = 'https://oauth.zaloapp.com/v4/oa/access_token';
    private const MESSAGE_URL = 'https://openapi.zalo.me/v3.0/oa/message/cs';
    private const TEMPLATE_URL = 'https://openapi.zalo.me/v3.0/oa/message/promotion';
    private const FOLLOWER_URL = 'https://openapi.zalo.me/v3.0/oa/user/getlist';

    /**
     * Get or refresh access token for tenant
     */
    public static function getAccessToken(int $tenantId): ?string
    {
        $integration = Database::fetch(
            "SELECT * FROM integrations WHERE tenant_id = ? AND provider = 'zalo_oa' LIMIT 1",
            [$tenantId]
        );

        if (!$integration) {
            return null;
        }

        $config = json_decode($integration['config'] ?? '{}', true);
        $accessToken = $config['access_token'] ?? null;
        $expiresAt = $config['token_expires_at'] ?? 0;

        // Token still valid
        if ($accessToken && time() < $expiresAt) {
            return $accessToken;
        }

        // Need to refresh
        $refreshToken = $config['refresh_token'] ?? null;
        $appId = $config['app_id'] ?? null;
        $secretKey = $config['secret_key'] ?? null;

        if (!$refreshToken || !$appId || !$secretKey) {
            return null;
        }

        $postData = http_build_query([
            'refresh_token' => $refreshToken,
            'app_id' => $appId,
            'grant_type' => 'refresh_token',
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", [
                    'Content-Type: application/x-www-form-urlencoded',
                    'secret_key: ' . $secretKey,
                ]),
                'content' => $postData,
                'timeout' => 10,
            ],
        ]);

        $response = @file_get_contents(self::TOKEN_URL, false, $context);

        if ($response === false) {
            return null;
        }

        $result = json_decode($response, true);

        if (!isset($result['access_token'])) {
            return null;
        }

        // Update stored tokens
        $config['access_token'] = $result['access_token'];
        $config['refresh_token'] = $result['refresh_token'] ?? $refreshToken;
        $config['token_expires_at'] = time() + ($result['expires_in'] ?? 3600);

        Database::update('integrations', [
            'config' => json_encode($config),
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$integration['id']]);

        return $result['access_token'];
    }

    /**
     * Send text message via Zalo OA
     */
    public static function sendTextMessage(string $zaloUserId, string $content, int $tenantId): array
    {
        $accessToken = self::getAccessToken($tenantId);

        if (!$accessToken) {
            return ['success' => false, 'error' => 'Không thể lấy access token Zalo OA'];
        }

        $payload = json_encode([
            'recipient' => ['user_id' => $zaloUserId],
            'message' => ['text' => $content],
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", [
                    'Content-Type: application/json',
                    'access_token: ' . $accessToken,
                ]),
                'content' => $payload,
                'timeout' => 10,
            ],
        ]);

        $response = @file_get_contents(self::MESSAGE_URL, false, $context);
        $result = $response ? json_decode($response, true) : null;

        // Log message
        try {
            Database::insert('zalo_messages', [
                'tenant_id' => $tenantId,
                'zalo_user_id' => $zaloUserId,
                'direction' => 'outbound',
                'message_type' => 'text',
                'content' => $content,
                'status' => ($result && ($result['error'] ?? -1) === 0) ? 'sent' : 'failed',
                'zalo_message_id' => $result['data']['message_id'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // Table may not exist yet, silently continue
        }

        if ($result && ($result['error'] ?? -1) === 0) {
            return ['success' => true, 'data' => $result];
        }

        return ['success' => false, 'error' => $result['message'] ?? 'Gửi tin nhắn thất bại'];
    }

    /**
     * Send template message via Zalo OA
     */
    public static function sendTemplateMessage(string $zaloUserId, string $templateId, array $params, int $tenantId): array
    {
        $accessToken = self::getAccessToken($tenantId);

        if (!$accessToken) {
            return ['success' => false, 'error' => 'Không thể lấy access token Zalo OA'];
        }

        $payload = json_encode([
            'recipient' => ['user_id' => $zaloUserId],
            'message' => [
                'attachment' => [
                    'type' => 'template',
                    'payload' => [
                        'template_type' => 'promotion',
                        'template_id' => $templateId,
                        'elements' => $params,
                    ],
                ],
            ],
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", [
                    'Content-Type: application/json',
                    'access_token: ' . $accessToken,
                ]),
                'content' => $payload,
                'timeout' => 10,
            ],
        ]);

        $response = @file_get_contents(self::TEMPLATE_URL, false, $context);
        $result = $response ? json_decode($response, true) : null;

        // Log message
        try {
            Database::insert('zalo_messages', [
                'tenant_id' => $tenantId,
                'zalo_user_id' => $zaloUserId,
                'direction' => 'outbound',
                'message_type' => 'template',
                'content' => json_encode(['template_id' => $templateId, 'params' => $params]),
                'status' => ($result && ($result['error'] ?? -1) === 0) ? 'sent' : 'failed',
                'zalo_message_id' => $result['data']['message_id'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // silently continue
        }

        if ($result && ($result['error'] ?? -1) === 0) {
            return ['success' => true, 'data' => $result];
        }

        return ['success' => false, 'error' => $result['message'] ?? 'Gửi template thất bại'];
    }

    /**
     * Process incoming Zalo webhook
     */
    public static function handleWebhook(array $data): void
    {
        $event = $data['event_name'] ?? '';
        $appId = $data['app_id'] ?? '';
        $oaId = $data['oa_id'] ?? '';

        // Find tenant by OA ID
        $integration = Database::fetch(
            "SELECT * FROM integrations WHERE provider = 'zalo_oa' AND JSON_EXTRACT(config, '$.oa_id') = ?",
            [$oaId]
        );

        if (!$integration) {
            // Try searching with app_id fallback
            $integration = Database::fetch(
                "SELECT * FROM integrations WHERE provider = 'zalo_oa' AND JSON_EXTRACT(config, '$.app_id') = ?",
                [$appId]
            );
        }

        $tenantId = $integration['tenant_id'] ?? 1;

        switch ($event) {
            case 'user_send_text':
                self::handleIncomingText($data, $tenantId);
                break;

            case 'user_send_image':
                self::handleIncomingImage($data, $tenantId);
                break;

            case 'follow':
                self::handleFollow($data, $tenantId);
                break;

            case 'unfollow':
                self::handleUnfollow($data, $tenantId);
                break;
        }
    }

    /**
     * Handle incoming text message
     */
    private static function handleIncomingText(array $data, int $tenantId): void
    {
        $sender = $data['sender'] ?? [];
        $zaloUserId = $sender['id'] ?? '';
        $message = $data['message'] ?? [];
        $text = $message['text'] ?? '';
        $msgId = $message['msg_id'] ?? '';

        if (empty($zaloUserId) || empty($text)) {
            return;
        }

        // Log incoming message
        try {
            Database::insert('zalo_messages', [
                'tenant_id' => $tenantId,
                'zalo_user_id' => $zaloUserId,
                'direction' => 'inbound',
                'message_type' => 'text',
                'content' => $text,
                'zalo_message_id' => $msgId,
                'status' => 'delivered',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // silently continue
        }

        // Try to match contact by zalo_user_id
        self::matchOrCreateConversation($zaloUserId, $text, $tenantId);
    }

    /**
     * Handle incoming image message
     */
    private static function handleIncomingImage(array $data, int $tenantId): void
    {
        $sender = $data['sender'] ?? [];
        $zaloUserId = $sender['id'] ?? '';
        $message = $data['message'] ?? [];
        $msgId = $message['msg_id'] ?? '';

        try {
            Database::insert('zalo_messages', [
                'tenant_id' => $tenantId,
                'zalo_user_id' => $zaloUserId,
                'direction' => 'inbound',
                'message_type' => 'image',
                'content' => json_encode($message['attachments'] ?? []),
                'zalo_message_id' => $msgId,
                'status' => 'delivered',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // silently continue
        }
    }

    /**
     * Handle follow event
     */
    private static function handleFollow(array $data, int $tenantId): void
    {
        $follower = $data['follower'] ?? [];
        $zaloUserId = $follower['id'] ?? '';

        if (empty($zaloUserId)) {
            return;
        }

        try {
            // Check if already tracked
            $existing = Database::fetch(
                "SELECT id FROM zalo_followers WHERE tenant_id = ? AND zalo_user_id = ?",
                [$tenantId, $zaloUserId]
            );

            if ($existing) {
                Database::update('zalo_followers', [
                    'is_following' => 1,
                    'followed_at' => date('Y-m-d H:i:s'),
                ], 'id = ?', [$existing['id']]);
            } else {
                Database::insert('zalo_followers', [
                    'tenant_id' => $tenantId,
                    'zalo_user_id' => $zaloUserId,
                    'is_following' => 1,
                    'followed_at' => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (\Throwable $e) {
            // silently continue
        }
    }

    /**
     * Handle unfollow event
     */
    private static function handleUnfollow(array $data, int $tenantId): void
    {
        $follower = $data['follower'] ?? [];
        $zaloUserId = $follower['id'] ?? '';

        if (empty($zaloUserId)) {
            return;
        }

        try {
            Database::update('zalo_followers', [
                'is_following' => 0,
                'unfollowed_at' => date('Y-m-d H:i:s'),
            ], 'tenant_id = ? AND zalo_user_id = ?', [$tenantId, $zaloUserId]);
        } catch (\Throwable $e) {
            // silently continue
        }
    }

    /**
     * Match contact by zalo_user_id and create/update conversation
     */
    private static function matchOrCreateConversation(string $zaloUserId, string $text, int $tenantId): void
    {
        // Try to find contact by zalo_user_id field
        $contact = Database::fetch(
            "SELECT id, first_name, last_name FROM contacts WHERE tenant_id = ? AND zalo_user_id = ? AND is_deleted = 0 LIMIT 1",
            [$tenantId, $zaloUserId]
        );

        $contactName = $contact
            ? trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? ''))
            : 'Zalo User ' . substr($zaloUserId, -6);

        // Create or update conversation
        try {
            $conversation = Database::fetch(
                "SELECT id FROM conversations WHERE tenant_id = ? AND channel = 'zalo' AND channel_id = ?",
                [$tenantId, $zaloUserId]
            );

            if ($conversation) {
                Database::update('conversations', [
                    'last_message' => mb_substr($text, 0, 255),
                    'last_message_at' => date('Y-m-d H:i:s'),
                    'unread_count' => Database::fetch(
                        "SELECT unread_count FROM conversations WHERE id = ?",
                        [$conversation['id']]
                    )['unread_count'] + 1,
                    'status' => 'open',
                ], 'id = ?', [$conversation['id']]);
            } else {
                Database::insert('conversations', [
                    'tenant_id' => $tenantId,
                    'contact_id' => $contact['id'] ?? null,
                    'channel' => 'zalo',
                    'channel_id' => $zaloUserId,
                    'subject' => 'Zalo - ' . $contactName,
                    'last_message' => mb_substr($text, 0, 255),
                    'last_message_at' => date('Y-m-d H:i:s'),
                    'unread_count' => 1,
                    'status' => 'open',
                ]);
            }
        } catch (\Throwable $e) {
            // silently continue
        }
    }

    /**
     * Get list of OA followers
     */
    public static function getFollowers(int $tenantId): array
    {
        $accessToken = self::getAccessToken($tenantId);

        if (!$accessToken) {
            return ['success' => false, 'error' => 'Không thể lấy access token'];
        }

        $url = self::FOLLOWER_URL . '?data=' . urlencode(json_encode(['offset' => 0, 'count' => 50]));

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => 'access_token: ' . $accessToken,
                'timeout' => 10,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
        $result = $response ? json_decode($response, true) : null;

        if ($result && ($result['error'] ?? -1) === 0) {
            return ['success' => true, 'data' => $result['data'] ?? []];
        }

        return ['success' => false, 'error' => $result['message'] ?? 'Lấy danh sách follower thất bại'];
    }
}
