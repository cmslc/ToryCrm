<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Services\GoogleCalendarService;

class GoogleCalendarController extends Controller
{
    private GoogleCalendarService $googleCalendar;

    public function __construct()
    {
        $this->googleCalendar = new GoogleCalendarService();
    }

    /**
     * GET - Show Google Calendar settings & sync status
     */
    public function settings()
    {
        $tenantId = $this->tenantId();

        $integration = Database::fetch(
            "SELECT * FROM integrations WHERE tenant_id = ? AND provider = 'google_calendar'",
            [$tenantId]
        );

        $config = $integration ? json_decode($integration['config'] ?? '{}', true) : [];
        $isConnected = $this->googleCalendar->isConnected($this->userId());
        $syncStatus = $this->googleCalendar->getSyncStatus($this->userId());

        return $this->view('integrations.google-calendar', [
            'config' => $config,
            'integration' => $integration,
            'isConnected' => $isConnected,
            'syncStatus' => $syncStatus,
        ]);
    }

    /**
     * POST - Save Google Calendar client_id, client_secret to integrations
     */
    public function saveSettings()
    {
        if (!$this->isPost()) {
            return $this->redirect('integrations/google-calendar');
        }

        $tenantId = $this->tenantId();
        $data = $this->allInput();

        $config = json_encode([
            'client_id' => trim($data['client_id'] ?? ''),
            'client_secret' => trim($data['client_secret'] ?? ''),
            'redirect_uri' => trim($data['redirect_uri'] ?? ''),
        ]);

        $existing = Database::fetch(
            "SELECT id FROM integrations WHERE tenant_id = ? AND provider = 'google_calendar'",
            [$tenantId]
        );

        if ($existing) {
            Database::update('integrations', [
                'config' => $config,
                'name' => 'Google Calendar',
            ], 'id = ?', [$existing['id']]);
        } else {
            Database::insert('integrations', [
                'tenant_id' => $tenantId,
                'provider' => 'google_calendar',
                'name' => 'Google Calendar',
                'config' => $config,
                'is_active' => 1,
                'created_by' => $this->userId(),
            ]);
        }

        $this->setFlash('success', 'Đã lưu cấu hình Google Calendar.');
        return $this->redirect('integrations/google-calendar');
    }

    /**
     * GET - Redirect to Google OAuth consent screen
     */
    public function connect()
    {
        $authUrl = $this->googleCalendar->getAuthUrl($this->userId());

        if (!$authUrl) {
            $this->setFlash('error', 'Vui lòng cấu hình Client ID và Client Secret trước.');
            return $this->redirect('integrations/google-calendar');
        }

        header("Location: {$authUrl}");
        exit;
    }

    /**
     * GET - Handle OAuth callback from Google
     */
    public function callback()
    {
        $code = $_GET['code'] ?? '';
        $state = (int)($_GET['state'] ?? 0);

        if (empty($code)) {
            $this->setFlash('error', 'Không nhận được mã xác thực từ Google.');
            return $this->redirect('integrations/google-calendar');
        }

        // Use state as userId, fallback to session userId
        $userId = $state ?: $this->userId();

        $success = $this->googleCalendar->handleCallback($code, $userId);

        if ($success) {
            $this->setFlash('success', 'Kết nối Google Calendar thành công!');
        } else {
            $this->setFlash('error', 'Kết nối thất bại. Vui lòng thử lại.');
        }

        return $this->redirect('integrations/google-calendar');
    }

    /**
     * POST - Disconnect Google Calendar (remove tokens)
     */
    public function disconnect()
    {
        if (!$this->isPost()) {
            return $this->redirect('integrations/google-calendar');
        }

        $this->googleCalendar->disconnect($this->userId());
        $this->setFlash('success', 'Đã ngắt kết nối Google Calendar.');
        return $this->redirect('integrations/google-calendar');
    }

    /**
     * POST - Trigger manual sync
     */
    public function sync()
    {
        if (!$this->isPost()) {
            return $this->redirect('integrations/google-calendar');
        }

        if (!$this->googleCalendar->isConnected($this->userId())) {
            $this->setFlash('error', 'Chưa kết nối Google Calendar.');
            return $this->redirect('integrations/google-calendar');
        }

        $result = $this->googleCalendar->syncEvents($this->userId());

        if (!empty($result['errors'])) {
            $this->setFlash('error', 'Đồng bộ có lỗi: ' . implode(', ', $result['errors']));
        } else {
            $this->setFlash('success', "Đồng bộ thành công! Đã tải {$result['pulled']} sự kiện, đẩy {$result['pushed']} sự kiện.");
        }

        return $this->redirect('integrations/google-calendar');
    }

    /**
     * GET - Return JSON sync status
     */
    public function status()
    {
        $isConnected = $this->googleCalendar->isConnected($this->userId());
        $syncStatus = $this->googleCalendar->getSyncStatus($this->userId());

        return $this->json([
            'connected' => $isConnected,
            'last_synced_at' => $syncStatus['last_synced_at'] ?? null,
            'is_active' => (bool)($syncStatus['is_active'] ?? false),
        ]);
    }
}
