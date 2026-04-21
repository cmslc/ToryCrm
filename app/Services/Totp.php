<?php

namespace App\Services;

/**
 * TOTP (Time-based One-Time Password) — RFC 6238.
 * Compatible with Google Authenticator, Microsoft Authenticator, Authy, 1Password.
 *
 * SHA-1, 6 digits, 30s window (standard defaults).
 */
class Totp
{
    private const DIGITS = 6;
    private const PERIOD = 30;
    private const ALG = 'sha1';

    /** Generate a new random Base32 secret (160 bits / 32 chars). */
    public static function generateSecret(): string
    {
        $bytes = random_bytes(20);
        return self::base32Encode($bytes);
    }

    /** Build the otpauth:// URL for QR codes. */
    public static function otpauthUrl(string $secret, string $accountName, string $issuer = 'ToryCRM'): string
    {
        $label = rawurlencode($issuer) . ':' . rawurlencode($accountName);
        return 'otpauth://totp/' . $label
             . '?secret=' . $secret
             . '&issuer=' . rawurlencode($issuer)
             . '&algorithm=SHA1&digits=' . self::DIGITS
             . '&period=' . self::PERIOD;
    }

    /**
     * Verify a user-supplied TOTP code against the secret.
     * Accepts current window ±1 step to tolerate clock skew.
     */
    public static function verify(string $secret, string $code, int $window = 1): bool
    {
        $code = preg_replace('/\s+/', '', $code);
        if (!ctype_digit($code) || strlen($code) !== self::DIGITS) return false;

        $key = self::base32Decode($secret);
        if ($key === null) return false;

        $counter = (int) floor(time() / self::PERIOD);
        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals(self::hotp($key, $counter + $i), $code)) return true;
        }
        return false;
    }

    /** Generate 10 backup codes (8 digits each). */
    public static function generateBackupCodes(int $count = 10): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        }
        return $codes;
    }

    private static function hotp(string $key, int $counter): string
    {
        $bin = pack('J', $counter); // 64-bit big-endian
        $hash = hash_hmac(self::ALG, $bin, $key, true);
        $offset = ord($hash[strlen($hash) - 1]) & 0xf;
        $truncated = (ord($hash[$offset]) & 0x7f) << 24
                   | (ord($hash[$offset + 1]) & 0xff) << 16
                   | (ord($hash[$offset + 2]) & 0xff) << 8
                   | (ord($hash[$offset + 3]) & 0xff);
        $code = $truncated % (10 ** self::DIGITS);
        return str_pad((string) $code, self::DIGITS, '0', STR_PAD_LEFT);
    }

    private static function base32Encode(string $bytes): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $out = '';
        $bits = '';
        foreach (str_split($bytes) as $byte) {
            $bits .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        }
        foreach (str_split($bits, 5) as $chunk) {
            if (strlen($chunk) < 5) $chunk = str_pad($chunk, 5, '0');
            $out .= $alphabet[bindec($chunk)];
        }
        return $out;
    }

    private static function base32Decode(string $input): ?string
    {
        $input = strtoupper(preg_replace('/[^A-Z2-7]/', '', $input));
        if ($input === '') return null;
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $bits = '';
        foreach (str_split($input) as $c) {
            $pos = strpos($alphabet, $c);
            if ($pos === false) return null;
            $bits .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }
        $bytes = '';
        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) === 8) $bytes .= chr(bindec($chunk));
        }
        return $bytes;
    }
}
