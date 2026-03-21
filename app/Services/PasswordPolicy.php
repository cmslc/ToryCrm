<?php

namespace App\Services;

use Core\Database;

class PasswordPolicy
{
    // Minimum password length
    const MIN_LENGTH = 8;

    // Password must contain: uppercase, lowercase, number
    const REQUIRE_UPPERCASE = true;
    const REQUIRE_LOWERCASE = true;
    const REQUIRE_NUMBER = true;
    const REQUIRE_SPECIAL = false;

    // Password expires after N days (0 = never)
    const EXPIRY_DAYS = 90;

    // Warn user N days before expiry
    const EXPIRY_WARNING_DAYS = 7;

    /**
     * Validate password strength
     * Returns array of error messages (empty = valid)
     */
    public static function validate(string $password): array
    {
        $errors = [];

        if (strlen($password) < self::MIN_LENGTH) {
            $errors[] = "Mật khẩu phải có ít nhất " . self::MIN_LENGTH . " ký tự";
        }

        if (self::REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
            $errors[] = "Mật khẩu phải chứa ít nhất 1 chữ hoa (A-Z)";
        }

        if (self::REQUIRE_LOWERCASE && !preg_match('/[a-z]/', $password)) {
            $errors[] = "Mật khẩu phải chứa ít nhất 1 chữ thường (a-z)";
        }

        if (self::REQUIRE_NUMBER && !preg_match('/[0-9]/', $password)) {
            $errors[] = "Mật khẩu phải chứa ít nhất 1 chữ số (0-9)";
        }

        if (self::REQUIRE_SPECIAL && !preg_match('/[!@#$%^&*()_+\-=\[\]{};:\'",.<>?\\/]/', $password)) {
            $errors[] = "Mật khẩu phải chứa ít nhất 1 ký tự đặc biệt (!@#$%...)";
        }

        // Check common weak passwords
        $weak = ['password', '12345678', 'qwerty123', 'abc12345', 'admin123', 'letmein1'];
        if (in_array(strtolower($password), $weak)) {
            $errors[] = "Mật khẩu quá phổ biến, vui lòng chọn mật khẩu khác";
        }

        return $errors;
    }

    /**
     * Check if a password meets the policy (boolean shorthand)
     */
    public static function isValid(string $password): bool
    {
        return empty(self::validate($password));
    }

    /**
     * Get formatted policy description for display
     */
    public static function getRules(): array
    {
        $rules = [];
        $rules[] = "Ít nhất " . self::MIN_LENGTH . " ký tự";
        if (self::REQUIRE_UPPERCASE) $rules[] = "Chứa chữ hoa (A-Z)";
        if (self::REQUIRE_LOWERCASE) $rules[] = "Chứa chữ thường (a-z)";
        if (self::REQUIRE_NUMBER) $rules[] = "Chứa chữ số (0-9)";
        if (self::REQUIRE_SPECIAL) $rules[] = "Chứa ký tự đặc biệt";
        return $rules;
    }

    /**
     * Check if user's password is expired
     */
    public static function isExpired(?string $passwordChangedAt): bool
    {
        if (self::EXPIRY_DAYS <= 0 || !$passwordChangedAt) return false;

        $changedAt = strtotime($passwordChangedAt);
        $expiresAt = $changedAt + (self::EXPIRY_DAYS * 86400);

        return time() > $expiresAt;
    }

    /**
     * Check if user should be warned about expiring password
     */
    public static function isExpiringSoon(?string $passwordChangedAt): bool
    {
        if (self::EXPIRY_DAYS <= 0 || !$passwordChangedAt) return false;

        $changedAt = strtotime($passwordChangedAt);
        $expiresAt = $changedAt + (self::EXPIRY_DAYS * 86400);
        $warningAt = $expiresAt - (self::EXPIRY_WARNING_DAYS * 86400);

        return time() > $warningAt && time() < $expiresAt;
    }

    /**
     * Get days until password expires
     */
    public static function daysUntilExpiry(?string $passwordChangedAt): int
    {
        if (self::EXPIRY_DAYS <= 0 || !$passwordChangedAt) return -1;

        $changedAt = strtotime($passwordChangedAt);
        $expiresAt = $changedAt + (self::EXPIRY_DAYS * 86400);
        $remaining = ($expiresAt - time()) / 86400;

        return max(0, (int) ceil($remaining));
    }

    /**
     * Get password strength score (0-4)
     */
    public static function getStrength(string $password): int
    {
        $score = 0;
        if (strlen($password) >= 8) $score++;
        if (strlen($password) >= 12) $score++;
        if (preg_match('/[A-Z]/', $password) && preg_match('/[a-z]/', $password)) $score++;
        if (preg_match('/[0-9]/', $password) && preg_match('/[^a-zA-Z0-9]/', $password)) $score++;
        return $score;
    }

    /**
     * Get strength label in Vietnamese
     */
    public static function getStrengthLabel(string $password): string
    {
        $score = self::getStrength($password);
        return match($score) {
            0 => 'Rất yếu',
            1 => 'Yếu',
            2 => 'Trung bình',
            3 => 'Mạnh',
            4 => 'Rất mạnh',
            default => 'Không xác định',
        };
    }
}
