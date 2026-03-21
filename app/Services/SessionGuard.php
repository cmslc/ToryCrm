<?php

namespace App\Services;

class SessionGuard
{
    /**
     * Regenerate the session ID and delete the old session.
     */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    /**
     * Validate that the current request fingerprint matches the one stored in the session.
     */
    public static function validateFingerprint(): bool
    {
        if (!isset($_SESSION['_fingerprint'])) {
            return false;
        }

        return hash_equals($_SESSION['_fingerprint'], self::buildFingerprint());
    }

    /**
     * Store a fingerprint derived from the user agent and accept-language header.
     */
    public static function setFingerprint(): void
    {
        $_SESSION['_fingerprint'] = self::buildFingerprint();
    }

    /**
     * Check whether the session has exceeded the maximum lifetime (seconds).
     */
    public static function isExpired(int $maxLifetime = 7200): bool
    {
        if (!isset($_SESSION['_last_activity'])) {
            return true;
        }

        return (time() - (int) $_SESSION['_last_activity']) > $maxLifetime;
    }

    /**
     * Update the last-activity timestamp to the current time.
     */
    public static function touch(): void
    {
        $_SESSION['_last_activity'] = time();
    }

    /**
     * Build an MD5 fingerprint from the user agent and accept-language header.
     */
    private static function buildFingerprint(): string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $acceptLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';

        return md5($userAgent . $acceptLang);
    }
}
