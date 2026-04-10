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

function due_label(?string $dueDate, string $status = ''): string
{
    if (empty($dueDate)) return '-';
    $due = strtotime($dueDate);
    $now = time();
    $diffDays = (int) floor(($now - $due) / 86400);

    if ($status === 'done') {
        return '<span class="text-success"><i class="ri-check-line me-1"></i>' . date('d/m/Y', $due) . '</span>';
    }
    if ($diffDays > 0) {
        return '<span class="text-danger fw-medium"><i class="ri-alarm-warning-line me-1"></i>Chậm ' . $diffDays . ' ngày</span>';
    }
    if ($diffDays === 0) {
        return '<span class="text-warning fw-medium"><i class="ri-time-line me-1"></i>Hôm nay</span>';
    }
    $remaining = abs($diffDays);
    if ($remaining <= 3) {
        return '<span class="text-warning">Còn ' . $remaining . ' ngày</span>';
    }
    return '<span class="text-muted">' . date('d/m/Y', $due) . '</span>';
}

function created_ago(?string $datetime): string
{
    if (empty($datetime)) return '-';
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) return 'Vừa xong';
    if ($diff < 3600) return floor($diff / 60) . ' phút trước';
    if ($diff < 86400) return floor($diff / 3600) . ' giờ trước';
    $days = (int) floor($diff / 86400);
    if ($days === 0) return 'Hôm nay';
    if ($days === 1) return 'Hôm qua';
    if ($days < 30) return $days . ' ngày trước';
    if ($days < 365) return floor($days / 30) . ' tháng trước';
    return date('d/m/Y', $time);
}

function user_avatar(?string $name, string $color = 'primary'): string
{
    if (empty($name)) return '-';
    $initial = mb_strtoupper(mb_substr(trim($name), 0, 1));
    return '<div class="d-flex align-items-center gap-2"><div class="avatar-xs"><div class="avatar-title rounded-circle bg-' . $color . '-subtle text-' . $color . '">' . $initial . '</div></div>' . e($name) . '</div>';
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
