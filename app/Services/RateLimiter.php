<?php

namespace App\Services;

use Core\Database;

class RateLimiter
{
    /**
     * Attempt an action. Returns true if allowed, false if rate-limited.
     */
    public static function attempt(string $key, int $maxAttempts = 5, int $decayMinutes = 15): bool
    {
        $sql = "SELECT id, attempts FROM rate_limits
                WHERE `key` = ? AND last_attempt_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)
                LIMIT 1";

        $record = Database::fetch($sql, [$key, $decayMinutes]);

        if ($record) {
            if ((int) $record['attempts'] >= $maxAttempts) {
                return false;
            }

            Database::update('rate_limits', [
                'attempts'        => $record['attempts'] + 1,
                'last_attempt_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$record['id']]);

            return true;
        }

        Database::insert('rate_limits', [
            'key'             => $key,
            'attempts'        => 1,
            'last_attempt_at' => date('Y-m-d H:i:s')
        ]);

        return true;
    }

    /**
     * Return the number of remaining attempts for the given key.
     */
    public static function remaining(string $key, int $maxAttempts = 5, int $decayMinutes = 15): int
    {
        $sql = "SELECT attempts FROM rate_limits
                WHERE `key` = ? AND last_attempt_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)
                LIMIT 1";

        $record = Database::fetch($sql, [$key, $decayMinutes]);

        if (!$record) {
            return $maxAttempts;
        }

        $remaining = $maxAttempts - (int) $record['attempts'];

        return max(0, $remaining);
    }

    /**
     * Clear all rate-limit records for a given key.
     */
    public static function clear(string $key): void
    {
        Database::delete('rate_limits', '`key` = ?', [$key]);
    }

    /**
     * Delete stale rate-limit records older than 1 hour.
     */
    public static function cleanup(): void
    {
        Database::query(
            "DELETE FROM rate_limits WHERE last_attempt_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)"
        );
    }
}
