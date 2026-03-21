<?php

namespace App\Services;

class TwoFactorAuth
{
    private static string $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate a random 16-character base32-encoded secret.
     */
    public static function generateSecret(): string
    {
        $secret = '';
        $chars  = self::$base32Chars;

        for ($i = 0; $i < 16; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }

        return $secret;
    }

    /**
     * Return an otpauth:// URI suitable for QR-code generation.
     */
    public static function getQrUrl(string $secret, string $email, string $issuer = 'ToryCRM'): string
    {
        $label   = rawurlencode($issuer . ':' . $email);
        $params  = http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
            'digits' => 6,
            'period' => 30
        ]);

        return "otpauth://totp/{$label}?{$params}";
    }

    /**
     * Verify a 6-digit TOTP code against the secret.
     * Allows +/- 1 time-slice drift (30-second windows).
     */
    public static function verifyCode(string $secret, string $code): bool
    {
        $currentSlice = (int) floor(time() / 30);

        for ($offset = -1; $offset <= 1; $offset++) {
            $expected = self::generateTOTP($secret, $currentSlice + $offset);
            if (hash_equals($expected, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Decode a base32-encoded string into raw bytes.
     */
    private static function base32Decode(string $b32): string
    {
        $b32    = strtoupper(rtrim($b32, '='));
        $length = strlen($b32);
        $buffer = 0;
        $bits   = 0;
        $output = '';

        for ($i = 0; $i < $length; $i++) {
            $val = strpos(self::$base32Chars, $b32[$i]);

            if ($val === false) {
                continue;
            }

            $buffer = ($buffer << 5) | $val;
            $bits  += 5;

            if ($bits >= 8) {
                $bits  -= 8;
                $output .= chr(($buffer >> $bits) & 0xFF);
            }
        }

        return $output;
    }

    /**
     * Generate a 6-digit TOTP code for a given time slice using HMAC-SHA1.
     */
    private static function generateTOTP(string $secret, int $timeSlice): string
    {
        $key  = self::base32Decode($secret);
        $time = pack('N*', 0, $timeSlice);

        $hash  = hash_hmac('sha1', $time, $key, true);
        $offset = ord($hash[19]) & 0x0F;

        $code = (
            ((ord($hash[$offset])     & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8)  |
             (ord($hash[$offset + 3]) & 0xFF)
        ) % 1000000;

        return str_pad((string) $code, 6, '0', STR_PAD_LEFT);
    }
}
