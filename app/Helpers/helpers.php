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

/**
 * Best-effort client IP. Trusts X-Forwarded-For only if REMOTE_ADDR is a
 * reverse proxy (loopback or RFC1918). Returns REMOTE_ADDR otherwise.
 */
function client_ip(): string
{
    $remote = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $isPrivate = filter_var($remote, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    if ($isPrivate && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $forwarded = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] ?? '';
        $forwarded = trim($forwarded);
        if (filter_var($forwarded, FILTER_VALIDATE_IP)) return $forwarded;
    }
    return $remote;
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

function format_date(string $date, ?string $format = null): string
{
    $format = $format ?? tenant_setting('date_format', 'd/m/Y');
    return date($format, strtotime($date));
}

function format_datetime(string $date): string
{
    $format = tenant_setting('date_format', 'd/m/Y');
    return date($format . ' H:i', strtotime($date));
}

function format_money($amount, ?string $currency = null): string
{
    $currency = $currency ?? tenant_setting('currency', 'VND');
    $map = [
        'VND' => ['sym' => 'đ', 'decimals' => 0, 'right' => true],
        'USD' => ['sym' => '$', 'decimals' => 2, 'right' => false],
        'EUR' => ['sym' => '€', 'decimals' => 2, 'right' => false],
        'JPY' => ['sym' => '¥', 'decimals' => 0, 'right' => false],
        'CNY' => ['sym' => '¥', 'decimals' => 2, 'right' => false],
    ];
    $cfg = $map[$currency] ?? $map['VND'];
    $num = number_format((float) $amount, $cfg['decimals'], ',', '.');
    return $cfg['right'] ? ($num . ' ' . $cfg['sym']) : ($cfg['sym'] . $num);
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

function product_image_url(?string $image): ?string
{
    if (empty($image)) return null;
    // Already an absolute URL (e.g. synced from Getfly)
    if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) return $image;
    return url('uploads/products/' . $image);
}

function tenant_setting(string $key, $default = null)
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            $row = \Core\Database::fetch(
                "SELECT settings FROM tenants WHERE id = ?",
                [$_SESSION['tenant_id'] ?? 1]
            );
            $all = json_decode($row['settings'] ?? '{}', true) ?: [];
            $cache = $all['general'] ?? [];
        } catch (\Exception $e) {
            $cache = [];
        }
    }
    return $cache[$key] ?? $default;
}

function plugin_active(string $slug): bool
{
    static $cache = [];
    if (isset($cache[$slug])) return $cache[$slug];
    try {
        $row = \Core\Database::fetch(
            "SELECT tp.is_active FROM tenant_plugins tp JOIN plugins p ON tp.plugin_id = p.id WHERE tp.tenant_id = ? AND p.slug = ?",
            [$_SESSION['tenant_id'] ?? 1, $slug]
        );
        $cache[$slug] = (bool)($row['is_active'] ?? false);
    } catch (\Exception $e) {
        $cache[$slug] = true; // Default to true if table doesn't exist
    }
    return $cache[$slug];
}

function upload_avatar(string $inputName, string $dir, ?string $oldFile = null): ?string
{
    if (empty($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) return null;

    $file = $_FILES[$inputName];
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed)) return null;
    $maxMb = max(1, min(100, (int) tenant_setting('upload_limit', 10)));
    if ($file['size'] > $maxMb * 1024 * 1024) return null;

    $uploadDir = BASE_PATH . '/public/uploads/' . $dir . '/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    // Delete old file
    if ($oldFile && file_exists($uploadDir . $oldFile)) unlink($uploadDir . $oldFile);

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $dir . '_' . uniqid() . '.' . $ext;
    move_uploaded_file($file['tmp_name'], $uploadDir . $filename);
    return $filename;
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

function user_avatar(?string $name, string $color = 'primary', ?string $avatar = null, string $dir = 'avatars'): string
{
    if (empty($name)) return '-';

    // Auto-lookup avatar from users table if not provided
    if ($avatar === null) {
        static $avatarCache = [];
        $cacheKey = trim($name);
        if (!isset($avatarCache[$cacheKey])) {
            try {
                $row = \Core\Database::fetch("SELECT avatar FROM users WHERE name = ? LIMIT 1", [$cacheKey]);
                $avatarCache[$cacheKey] = $row['avatar'] ?? '';
            } catch (\Exception $e) {
                $avatarCache[$cacheKey] = '';
            }
        }
        $avatar = $avatarCache[$cacheKey] ?: null;
    }

    if ($avatar) {
        // Support both "filename.jpg" and "uploads/avatars/filename.jpg" formats
        $filePath = (str_starts_with($avatar, 'uploads/')) ? $avatar : 'uploads/' . $dir . '/' . $avatar;
        if (file_exists(BASE_PATH . '/public/' . $filePath)) {
            $src = url($filePath);
            return '<div class="d-flex align-items-center gap-2"><div class="avatar-xs"><img src="' . $src . '" class="rounded-circle object-fit-cover" style="width:100%;height:100%"></div>' . e($name) . '</div>';
        }
    }
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
