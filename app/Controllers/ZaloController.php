<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Services\ZaloService;

class ZaloController extends Controller
{
    /**
     * Zalo OA webhook endpoint (public, no auth)
     */
    public function webhook()
    {
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        if (!$data || empty($data['event_name'])) {
            return $this->json(['error' => 'Invalid payload'], 400);
        }

        ZaloService::handleWebhook($data);

        return $this->json(['status' => 'ok']);
    }

    /**
     * Send message to contact's Zalo
     */
    public function send()
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }

        $data = $this->allInput();
        $contactId = (int) ($data['contact_id'] ?? 0);
        $message = trim($data['message'] ?? '');

        if (empty($message)) {
            $this->setFlash('error', 'Nội dung tin nhắn không được trống.');
            return $this->back();
        }

        // Find contact's zalo_user_id
        $contact = null;
        $zaloUserId = trim($data['zalo_user_id'] ?? '');

        if ($contactId > 0) {
            $contact = $this->findSecure('contacts', $contactId);
            if ($contact) {
                $zaloUserId = $contact['zalo_user_id'] ?? $zaloUserId;
            }
        }

        if (empty($zaloUserId)) {
            $this->setFlash('error', 'Không tìm thấy Zalo User ID của khách hàng.');
            return $this->back();
        }

        $result = ZaloService::sendTextMessage($zaloUserId, $message, $this->tenantId());

        if ($result['success']) {
            $this->setFlash('success', 'Đã gửi tin nhắn Zalo thành công.');
        } else {
            $this->setFlash('error', 'Gửi thất bại: ' . ($result['error'] ?? 'Unknown error'));
        }

        return $this->back();
    }

    /**
     * Show Zalo OA settings page
     */
    public function settings()
    {
        $integration = Database::fetch(
            "SELECT * FROM integrations WHERE tenant_id = ? AND provider = 'zalo_oa' LIMIT 1",
            [$this->tenantId()]
        );

        $config = $integration ? json_decode($integration['config'] ?? '{}', true) : [];

        // Get recent messages
        $messages = [];
        try {
            $messages = Database::fetchAll(
                "SELECT * FROM zalo_messages WHERE tenant_id = ? ORDER BY created_at DESC LIMIT 20",
                [$this->tenantId()]
            );
        } catch (\Throwable $e) {
            // Table may not exist
        }

        $webhookUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
            . '://' . ($_SERVER['HTTP_HOST'] ?? 'your-domain.com') . '/webhooks/zalo';

        return $this->view('integrations.zalo', [
            'integration' => $integration,
            'config' => $config,
            'messages' => $messages,
            'webhookUrl' => $webhookUrl,
        ]);
    }

    /**
     * Save Zalo OA credentials
     */
    public function saveSettings()
    {
        if (!$this->isPost()) {
            return $this->redirect('integrations/zalo');
        }

        $data = $this->allInput();

        $config = [
            'app_id' => trim($data['app_id'] ?? ''),
            'secret_key' => trim($data['secret_key'] ?? ''),
            'oa_id' => trim($data['oa_id'] ?? ''),
            'refresh_token' => trim($data['refresh_token'] ?? ''),
        ];

        if (empty($config['app_id']) || empty($config['secret_key']) || empty($config['oa_id'])) {
            $this->setFlash('error', 'App ID, Secret Key và OA ID không được để trống.');
            return $this->back();
        }

        $existing = Database::fetch(
            "SELECT id, config FROM integrations WHERE tenant_id = ? AND provider = 'zalo_oa' LIMIT 1",
            [$this->tenantId()]
        );

        // Preserve existing tokens if not provided
        if ($existing) {
            $oldConfig = json_decode($existing['config'] ?? '{}', true);
            if (empty($config['refresh_token']) && !empty($oldConfig['refresh_token'])) {
                $config['refresh_token'] = $oldConfig['refresh_token'];
            }
            // Keep existing access token data
            $config['access_token'] = $oldConfig['access_token'] ?? null;
            $config['token_expires_at'] = $oldConfig['token_expires_at'] ?? 0;

            Database::update('integrations', [
                'config' => json_encode($config),
                'is_active' => 1,
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$existing['id']]);
        } else {
            Database::insert('integrations', [
                'tenant_id' => $this->tenantId(),
                'provider' => 'zalo_oa',
                'name' => 'Zalo OA',
                'config' => json_encode($config),
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->setFlash('success', 'Đã lưu cấu hình Zalo OA.');
        return $this->redirect('integrations/zalo');
    }
}
