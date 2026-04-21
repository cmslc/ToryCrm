<?php

namespace App\Services;

/**
 * Symmetric encryption for secrets at rest (API keys, tokens, credentials).
 *
 * Uses libsodium's authenticated secretbox (XSalsa20 + Poly1305).
 * Master key lives in APP_KEY env var (32 bytes hex or base64).
 *
 * Usage:
 *   $ciphertext = Crypto::encrypt($plaintext);      // store this in DB
 *   $plaintext  = Crypto::decrypt($ciphertext);     // decode for use
 */
class Crypto
{
    private static ?string $key = null;

    private static function key(): string
    {
        if (self::$key !== null) return self::$key;
        $raw = $_ENV['APP_KEY'] ?? '';
        if (!$raw) throw new \RuntimeException('APP_KEY not configured');

        // Accept hex (64 chars), base64 (44 chars) or raw 32-byte
        if (strlen($raw) === 64 && ctype_xdigit($raw)) {
            self::$key = hex2bin($raw);
        } elseif (preg_match('/^base64:(.+)$/', $raw, $m)) {
            self::$key = base64_decode($m[1]);
        } else {
            // Derive a 32-byte key from whatever APP_KEY is
            self::$key = hash('sha256', $raw, true);
        }

        if (strlen(self::$key) !== 32) {
            throw new \RuntimeException('APP_KEY must derive to 32 bytes');
        }
        return self::$key;
    }

    public static function encrypt(string $plaintext): string
    {
        if (!function_exists('sodium_crypto_secretbox')) {
            throw new \RuntimeException('libsodium not available');
        }
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ct = sodium_crypto_secretbox($plaintext, $nonce, self::key());
        return 'enc:v1:' . base64_encode($nonce . $ct);
    }

    public static function decrypt(string $ciphertext): ?string
    {
        if (!str_starts_with($ciphertext, 'enc:v1:')) return null;
        $raw = base64_decode(substr($ciphertext, 7), true);
        if ($raw === false || strlen($raw) < SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) return null;

        $nonce = substr($raw, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ct = substr($raw, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $plain = sodium_crypto_secretbox_open($ct, $nonce, self::key());
        return $plain === false ? null : $plain;
    }

    /** True if the value looks encrypted (has the prefix). */
    public static function isEncrypted(string $value): bool
    {
        return str_starts_with($value, 'enc:v1:');
    }
}
