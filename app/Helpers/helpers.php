<?php

/**
 * Global helper functions
 */

function url(string $path = ''): string
{
    $base = rtrim($_ENV['APP_URL'] ?? '', '/');
    return $base . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url($path);
}

function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
}

function verify_csrf(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function old(string $key, $default = ''): string
{
    return htmlspecialchars($_SESSION['old_input'][$key] ?? $default, ENT_QUOTES, 'UTF-8');
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function format_date(string $date, string $format = 'd/m/Y'): string
{
    return date($format, strtotime($date));
}

function format_datetime(string $date): string
{
    return date('d/m/Y H:i', strtotime($date));
}

function format_money($amount): string
{
    return number_format((float) $amount, 0, ',', '.') . ' đ';
}

function time_ago(string $datetime): string
{
    $now = time();
    $time = strtotime($datetime);
    $diff = $now - $time;

    if ($diff < 60) return 'Vừa xong';
    if ($diff < 3600) return floor($diff / 60) . ' phút trước';
    if ($diff < 86400) return floor($diff / 3600) . ' giờ trước';
    if ($diff < 2592000) return floor($diff / 86400) . ' ngày trước';

    return format_date($datetime);
}

function avatar_url(?string $avatar): string
{
    if ($avatar && file_exists(BASE_PATH . '/public/uploads/avatars/' . $avatar)) {
        return url('uploads/avatars/' . $avatar);
    }
    return url('images/default-avatar.png');
}

/**
 * Sanitize color value - only allow valid hex colors
 */
function safe_color(?string $color, string $default = '#405189'): string
{
    if ($color && preg_match('/^#[0-9a-fA-F]{3,6}$/', $color)) {
        return $color;
    }
    return $default;
}
