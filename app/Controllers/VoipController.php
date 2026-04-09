<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Services\VoipService;

class VoipController extends Controller
{
    /**
     * Show VoIP settings page
     */
    public function settings()
    {
        $integration = Database::fetch(
            "SELECT * FROM integrations WHERE tenant_id = ? AND provider = 'voip_stringee' LIMIT 1",
            [$this->tenantId()]
        );

        $config = $integration ? json_decode($integration['config'] ?? '{}', true) : [];

        // Get users for extension mapping
        $users = Database::fetchAll(
            "SELECT id, name, email FROM users WHERE tenant_id = ? AND is_active = 1 ORDER BY name",
            [$this->tenantId()]
        );

        // Get recent call logs
        $callLogs = [];
        try {
            $callLogs = Database::fetchAll(
                "SELECT cl.*, u.name as user_name
                 FROM call_logs cl
                 LEFT JOIN users u ON cl.user_id = u.id
                 WHERE cl.tenant_id = ?
                 ORDER BY cl.created_at DESC LIMIT 20",
                [$this->tenantId()]
            );
        } catch (\Throwable $e) {
            // Table may not exist
        }

        return $this->view('integrations.voip', [
            'integration' => $integration,
            'config' => $config,
            'users' => $users,
            'callLogs' => $callLogs,
        ]);
    }

    /**
     * Save Stringee VoIP credentials
     */
    public function saveSettings()
    {
        if (!$this->isPost()) {
            return $this->redirect('integrations/voip');
        }

        $data = $this->allInput();

        $config = [
            'api_key_sid' => trim($data['api_key_sid'] ?? ''),
            'api_key_secret' => trim($data['api_key_secret'] ?? ''),
            'phone_from' => trim($data['phone_from'] ?? ''),
            'extensions' => [],
        ];

        // Parse extension mapping
        $extUsers = $data['ext_user_id'] ?? [];
        $extNumbers = $data['ext_number'] ?? [];
        if (is_array($extUsers) && is_array($extNumbers)) {
            foreach ($extUsers as $i => $uid) {
                if (!empty($uid) && !empty($extNumbers[$i] ?? '')) {
                    $config['extensions'][] = [
                        'user_id' => (int) $uid,
                        'number' => trim($extNumbers[$i]),
                    ];
                }
            }
        }

        if (empty($config['api_key_sid']) || empty($config['api_key_secret'])) {
            $this->setFlash('error', 'API Key SID và Secret không được để trống.');
            return $this->back();
        }

        $existing = Database::fetch(
            "SELECT id FROM integrations WHERE tenant_id = ? AND provider = 'voip_stringee' LIMIT 1",
            [$this->tenantId()]
        );

        if ($existing) {
            Database::update('integrations', [
                'config' => json_encode($config),
                'is_active' => 1,
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$existing['id']]);
        } else {
            Database::insert('integrations', [
                'tenant_id' => $this->tenantId(),
                'provider' => 'voip_stringee',
                'name' => 'VoIP Stringee',
                'config' => json_encode($config),
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->setFlash('success', 'Đã lưu cấu hình VoIP/Stringee.');
        return $this->redirect('integrations/voip');
    }

    /**
     * Initiate a call
     */
    public function makeCall()
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }

        $data = $this->allInput();
        $to = trim($data['phone'] ?? '');

        if (empty($to)) {
            return $this->json(['success' => false, 'error' => 'Số điện thoại không được trống']);
        }

        // Get from number from config
        $integration = Database::fetch(
            "SELECT * FROM integrations WHERE tenant_id = ? AND provider = 'voip_stringee' LIMIT 1",
            [$this->tenantId()]
        );

        if (!$integration) {
            return $this->json(['success' => false, 'error' => 'Chưa cấu hình VoIP']);
        }

        $config = json_decode($integration['config'] ?? '{}', true);
        $from = $config['phone_from'] ?? '';

        // Check if user has an extension
        $extensions = $config['extensions'] ?? [];
        foreach ($extensions as $ext) {
            if ((int) ($ext['user_id'] ?? 0) === $this->userId()) {
                $from = $ext['number'];
                break;
            }
        }

        if (empty($from)) {
            return $this->json(['success' => false, 'error' => 'Chưa cấu hình số gọi đi']);
        }

        $result = VoipService::makeCall($from, $to, $this->userId());

        return $this->json($result);
    }

    /**
     * Call event webhook (public, no auth)
     */
    public function callEvent()
    {
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        if (!$data) {
            return $this->json(['error' => 'Invalid payload'], 400);
        }

        VoipService::handleCallEvent($data);

        return $this->json(['status' => 'ok']);
    }

    /**
     * Return JWT token for Stringee client SDK
     */
    public function token()
    {
        $token = VoipService::generateToken($this->userId());

        if (!$token) {
            return $this->json(['success' => false, 'error' => 'Không thể tạo token'], 500);
        }

        return $this->json(['success' => true, 'token' => $token]);
    }
}
