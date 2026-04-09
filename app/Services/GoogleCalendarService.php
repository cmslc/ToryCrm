<?php

namespace App\Services;

use Core\Database;

class GoogleCalendarService
{
    private const GOOGLE_AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const GOOGLE_TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const GOOGLE_CALENDAR_API = 'https://www.googleapis.com/calendar/v3';
    private const SCOPE = 'https://www.googleapis.com/auth/calendar';

    /**
     * Get Google OAuth2 config from integrations table
     */
    private function getConfig(): ?array
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $row = Database::fetch(
            "SELECT config FROM integrations WHERE tenant_id = ? AND provider = 'google_calendar'",
            [$tenantId]
        );
        if (!$row || empty($row['config'])) {
            return null;
        }
        return json_decode($row['config'], true);
    }

    /**
     * Build Google OAuth2 authorization URL
     */
    public function getAuthUrl(int $userId): ?string
    {
        $config = $this->getConfig();
        if (!$config || empty($config['client_id'])) {
            return null;
        }

        $params = [
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect_uri'] ?? $this->getRedirectUri(),
            'response_type' => 'code',
            'scope' => self::SCOPE,
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $userId,
        ];

        return self::GOOGLE_AUTH_URL . '?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for tokens, save to calendar_sync_tokens
     */
    public function handleCallback(string $code, int $userId): bool
    {
        $config = $this->getConfig();
        if (!$config) {
            return false;
        }

        $postData = [
            'code' => $code,
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'redirect_uri' => $config['redirect_uri'] ?? $this->getRedirectUri(),
            'grant_type' => 'authorization_code',
        ];

        $response = $this->httpPost(self::GOOGLE_TOKEN_URL, $postData);
        if (!$response || !isset($response['access_token'])) {
            return false;
        }

        $expiresAt = date('Y-m-d H:i:s', time() + ($response['expires_in'] ?? 3600));

        // Upsert token record
        $existing = Database::fetch(
            "SELECT id FROM calendar_sync_tokens WHERE user_id = ? AND provider = 'google'",
            [$userId]
        );

        $tokenData = [
            'access_token' => $response['access_token'],
            'refresh_token' => $response['refresh_token'] ?? null,
            'token_expires_at' => $expiresAt,
            'is_active' => 1,
        ];

        if ($existing) {
            Database::update('calendar_sync_tokens', $tokenData, 'id = ?', [$existing['id']]);
        } else {
            $tokenData['user_id'] = $userId;
            $tokenData['provider'] = 'google';
            Database::insert('calendar_sync_tokens', $tokenData);
        }

        return true;
    }

    /**
     * Refresh expired access token
     */
    public function refreshToken(int $userId): bool
    {
        $token = Database::fetch(
            "SELECT * FROM calendar_sync_tokens WHERE user_id = ? AND provider = 'google' AND is_active = 1",
            [$userId]
        );

        if (!$token || empty($token['refresh_token'])) {
            return false;
        }

        $config = $this->getConfig();
        if (!$config) {
            return false;
        }

        $postData = [
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'refresh_token' => $token['refresh_token'],
            'grant_type' => 'refresh_token',
        ];

        $response = $this->httpPost(self::GOOGLE_TOKEN_URL, $postData);
        if (!$response || !isset($response['access_token'])) {
            return false;
        }

        $expiresAt = date('Y-m-d H:i:s', time() + ($response['expires_in'] ?? 3600));

        Database::update('calendar_sync_tokens', [
            'access_token' => $response['access_token'],
            'token_expires_at' => $expiresAt,
        ], 'id = ?', [$token['id']]);

        return true;
    }

    /**
     * Get a valid access token, refreshing if expired
     */
    private function getAccessToken(int $userId): ?string
    {
        $token = Database::fetch(
            "SELECT * FROM calendar_sync_tokens WHERE user_id = ? AND provider = 'google' AND is_active = 1",
            [$userId]
        );

        if (!$token) {
            return null;
        }

        // Refresh if expired (with 5 min buffer)
        if (strtotime($token['token_expires_at']) < time() + 300) {
            if (!$this->refreshToken($userId)) {
                return null;
            }
            $token = Database::fetch(
                "SELECT * FROM calendar_sync_tokens WHERE user_id = ? AND provider = 'google' AND is_active = 1",
                [$userId]
            );
        }

        return $token['access_token'] ?? null;
    }

    /**
     * Two-way sync events with Google Calendar
     */
    public function syncEvents(int $userId): array
    {
        $result = ['pulled' => 0, 'pushed' => 0, 'errors' => []];

        $accessToken = $this->getAccessToken($userId);
        if (!$accessToken) {
            $result['errors'][] = 'Không thể lấy access token.';
            return $result;
        }

        // --- PULL: Get events from Google Calendar ---
        $params = http_build_query([
            'maxResults' => 100,
            'timeMin' => date('c', strtotime('-30 days')),
            'timeMax' => date('c', strtotime('+90 days')),
            'singleEvents' => 'true',
            'orderBy' => 'startTime',
        ]);

        $url = self::GOOGLE_CALENDAR_API . '/calendars/primary/events?' . $params;
        $googleEvents = $this->httpGet($url, $accessToken);

        if ($googleEvents && isset($googleEvents['items'])) {
            foreach ($googleEvents['items'] as $gEvent) {
                $googleEventId = $gEvent['id'];
                $existing = Database::fetch(
                    "SELECT id FROM calendar_events WHERE google_event_id = ?",
                    [$googleEventId]
                );

                $startAt = $gEvent['start']['dateTime'] ?? ($gEvent['start']['date'] ?? null);
                $endAt = $gEvent['end']['dateTime'] ?? ($gEvent['end']['date'] ?? null);
                $allDay = isset($gEvent['start']['date']) ? 1 : 0;

                if ($startAt) {
                    $startAt = date('Y-m-d H:i:s', strtotime($startAt));
                }
                if ($endAt) {
                    $endAt = date('Y-m-d H:i:s', strtotime($endAt));
                }

                $eventData = [
                    'title' => $gEvent['summary'] ?? 'Không có tiêu đề',
                    'description' => $gEvent['description'] ?? '',
                    'location' => $gEvent['location'] ?? '',
                    'start_at' => $startAt,
                    'end_at' => $endAt,
                    'all_day' => $allDay,
                    'google_event_id' => $googleEventId,
                    'sync_status' => 'synced',
                ];

                if ($existing) {
                    Database::update('calendar_events', $eventData, 'id = ?', [$existing['id']]);
                } else {
                    $eventData['user_id'] = $userId;
                    $eventData['created_by'] = $userId;
                    $eventData['type'] = 'meeting';
                    $eventData['color'] = '#405189';
                    Database::insert('calendar_events', $eventData);
                }
                $result['pulled']++;
            }
        }

        // --- PUSH: Send local events to Google ---
        $localEvents = Database::fetchAll(
            "SELECT * FROM calendar_events WHERE user_id = ? AND sync_status = 'local' AND google_event_id IS NULL",
            [$userId]
        );

        foreach ($localEvents as $event) {
            $pushed = $this->pushEvent($event['id'], $userId);
            if ($pushed) {
                $result['pushed']++;
            } else {
                $result['errors'][] = "Không thể đẩy sự kiện #{$event['id']} lên Google.";
            }
        }

        // Update last synced timestamp
        Database::update('calendar_sync_tokens', [
            'last_synced_at' => date('Y-m-d H:i:s'),
        ], "user_id = ? AND provider = 'google'", [$userId]);

        return $result;
    }

    /**
     * Push a single local event to Google Calendar
     */
    public function pushEvent(int $eventId, int $userId): bool
    {
        $event = Database::fetch("SELECT * FROM calendar_events WHERE id = ?", [$eventId]);
        if (!$event) {
            return false;
        }

        $accessToken = $this->getAccessToken($userId);
        if (!$accessToken) {
            return false;
        }

        $isAllDay = !empty($event['all_day']);

        $googleEvent = [
            'summary' => $event['title'],
            'description' => $event['description'] ?? '',
            'location' => $event['location'] ?? '',
        ];

        if ($isAllDay) {
            $googleEvent['start'] = ['date' => date('Y-m-d', strtotime($event['start_at']))];
            $googleEvent['end'] = ['date' => date('Y-m-d', strtotime($event['end_at'] ?: $event['start_at']))];
        } else {
            $googleEvent['start'] = ['dateTime' => date('c', strtotime($event['start_at']))];
            $googleEvent['end'] = ['dateTime' => date('c', strtotime($event['end_at'] ?: $event['start_at']))];
        }

        $url = self::GOOGLE_CALENDAR_API . '/calendars/primary/events';
        $response = $this->httpPostJson($url, $googleEvent, $accessToken);

        if ($response && isset($response['id'])) {
            Database::update('calendar_events', [
                'google_event_id' => $response['id'],
                'sync_status' => 'synced',
            ], 'id = ?', [$eventId]);
            return true;
        }

        return false;
    }

    /**
     * Delete an event from Google Calendar
     */
    public function deleteEvent(string $googleEventId, int $userId): bool
    {
        $accessToken = $this->getAccessToken($userId);
        if (!$accessToken) {
            return false;
        }

        $url = self::GOOGLE_CALENDAR_API . '/calendars/primary/events/' . urlencode($googleEventId);

        $context = stream_context_create([
            'http' => [
                'method' => 'DELETE',
                'header' => "Authorization: Bearer {$accessToken}\r\n",
                'ignore_errors' => true,
            ],
        ]);

        @file_get_contents($url, false, $context);

        return true;
    }

    /**
     * Check if user is connected to Google Calendar
     */
    public function isConnected(int $userId): bool
    {
        $token = Database::fetch(
            "SELECT id FROM calendar_sync_tokens WHERE user_id = ? AND provider = 'google' AND is_active = 1",
            [$userId]
        );
        return $token !== null;
    }

    /**
     * Get sync status for user
     */
    public function getSyncStatus(int $userId): ?array
    {
        return Database::fetch(
            "SELECT last_synced_at, token_expires_at, is_active FROM calendar_sync_tokens WHERE user_id = ? AND provider = 'google'",
            [$userId]
        );
    }

    /**
     * Disconnect: remove tokens
     */
    public function disconnect(int $userId): void
    {
        Database::delete('calendar_sync_tokens', "user_id = ? AND provider = 'google'", [$userId]);
    }

    /**
     * Build default redirect URI
     */
    private function getRedirectUri(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . '/integrations/google-calendar/callback';
    }

    /**
     * HTTP POST (form-encoded)
     */
    private function httpPost(string $url, array $data): ?array
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($data),
                'ignore_errors' => true,
                'timeout' => 30,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            return null;
        }

        return json_decode($response, true);
    }

    /**
     * HTTP POST JSON (for Google API calls)
     */
    private function httpPostJson(string $url, array $data, string $accessToken): ?array
    {
        $jsonData = json_encode($data);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Authorization: Bearer {$accessToken}\r\nContent-Type: application/json\r\nContent-Length: " . strlen($jsonData) . "\r\n",
                'content' => $jsonData,
                'ignore_errors' => true,
                'timeout' => 30,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            return null;
        }

        return json_decode($response, true);
    }

    /**
     * HTTP GET with bearer token
     */
    private function httpGet(string $url, string $accessToken): ?array
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "Authorization: Bearer {$accessToken}\r\n",
                'ignore_errors' => true,
                'timeout' => 30,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            return null;
        }

        return json_decode($response, true);
    }
}
