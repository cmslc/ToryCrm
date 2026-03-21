<?php

namespace App\Services;

class CacheService
{
    /**
     * Resolve the cache directory path.
     */
    private static function cacheDir(): string
    {
        return BASE_PATH . '/storage/cache/';
    }

    /**
     * Convert a cache key to a file path (md5-hashed filename).
     */
    private static function path(string $key): string
    {
        return self::cacheDir() . md5($key) . '.cache';
    }

    /**
     * Ensure the cache directory exists.
     */
    private static function ensureDirectory(): void
    {
        $dir = self::cacheDir();

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * Retrieve a cached value or return $default if missing/expired.
     */
    public static function get(string $key, $default = null)
    {
        $file = self::path($key);

        if (!file_exists($file)) {
            return $default;
        }

        $raw = file_get_contents($file);

        if ($raw === false) {
            return $default;
        }

        $data = unserialize($raw);

        if (!is_array($data) || !isset($data['expires_at'], $data['value'])) {
            return $default;
        }

        if ($data['expires_at'] !== 0 && $data['expires_at'] < time()) {
            @unlink($file);
            return $default;
        }

        return $data['value'];
    }

    /**
     * Store a value in the cache with a TTL (seconds).
     */
    public static function set(string $key, $value, int $ttl = 3600): void
    {
        self::ensureDirectory();

        $data = serialize([
            'expires_at' => $ttl > 0 ? time() + $ttl : 0,
            'value'      => $value
        ]);

        file_put_contents(self::path($key), $data, LOCK_EX);
    }

    /**
     * Check whether a non-expired cache entry exists for the given key.
     */
    public static function has(string $key): bool
    {
        return self::get($key, '__CACHE_MISS__') !== '__CACHE_MISS__';
    }

    /**
     * Remove a single cache entry.
     */
    public static function forget(string $key): void
    {
        $file = self::path($key);

        if (file_exists($file)) {
            @unlink($file);
        }
    }

    /**
     * Delete all cache files.
     */
    public static function flush(): void
    {
        $dir = self::cacheDir();

        if (!is_dir($dir)) {
            return;
        }

        $files = glob($dir . '*.cache');

        if ($files) {
            foreach ($files as $file) {
                @unlink($file);
            }
        }
    }

    /**
     * Get a cached value or compute it via the callback and store the result.
     */
    public static function remember(string $key, int $ttl, callable $callback)
    {
        $value = self::get($key, '__CACHE_MISS__');

        if ($value !== '__CACHE_MISS__') {
            return $value;
        }

        $value = $callback();
        self::set($key, $value, $ttl);

        return $value;
    }
}
