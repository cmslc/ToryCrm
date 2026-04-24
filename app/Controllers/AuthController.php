<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Helpers\Auth;
use App\Services\RateLimiter;
use App\Services\PasswordPolicy;
use App\Services\AuditLog;

class AuthController extends Controller
{
    public function loginForm()
    {
        return $this->view('auth.login');
    }

    public function login()
    {
        if (!$this->isPost()) {
            return $this->redirect('login');
        }

        $email = trim($this->input('email'));
        $password = $this->input('password');

        if (empty($email) || empty($password)) {
            $this->setFlash('error', 'Vui lòng nhập email và mật khẩu.');
            return $this->back();
        }

        // Rate limiting - max 5 attempts per 15 minutes per IP
        $rateLimitKey = 'login:' . client_ip();
        if (!RateLimiter::attempt($rateLimitKey, 5, 15)) {
            $this->setFlash('error', 'Quá nhiều lần thử. Vui lòng chờ 15 phút.');
            return $this->back();
        }

        $user = Database::fetch("SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1", [$email]);

        if (!$user || !Auth::verifyPassword($password, $user['password'])) {
            AuditLog::loginFailed($email);
            $this->setFlash('error', 'Email hoặc mật khẩu không đúng.');
            return $this->back();
        }

        // Clear rate limit on success
        RateLimiter::clear($rateLimitKey);

        // 2FA gate: if enabled, stash user in session as "pending" and redirect to OTP form
        if (!empty($user['totp_enabled']) && !empty($user['totp_secret'])) {
            $_SESSION['_2fa_pending_user_id'] = (int)$user['id'];
            $_SESSION['_2fa_pending_remember'] = (bool)$this->input('remember');
            $_SESSION['_2fa_pending_at'] = time();
            return $this->redirect('login/2fa');
        }

        AuditLog::loginSuccess((int)$user['id'], $email);

        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        Auth::login($user);

        // Remember me - extend session to 30 days
        if ($this->input('remember')) {
            $lifetime = 30 * 24 * 60 * 60; // 30 days
            ini_set('session.gc_maxlifetime', $lifetime);
            session_set_cookie_params($lifetime);
            setcookie(session_name(), session_id(), time() + $lifetime, '/');
        }

        // Update last login
        Database::update('users', [
            'last_login' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$user['id']]);

        // Check password expiry
        if (PasswordPolicy::isExpired($user['password_changed_at'] ?? null)) {
            $this->setFlash('warning', 'Mật khẩu đã hết hạn. Vui lòng đổi mật khẩu mới.');
            return $this->redirect('settings');
        }

        if (PasswordPolicy::isExpiringSoon($user['password_changed_at'] ?? null)) {
            $days = PasswordPolicy::daysUntilExpiry($user['password_changed_at'] ?? null);
            $this->setFlash('warning', "Mật khẩu sẽ hết hạn trong {$days} ngày. Hãy đổi mật khẩu sớm.");
        }

        return $this->redirect('dashboard');
    }

    public function registerForm()
    {
        return $this->view('auth.register');
    }

    public function register()
    {
        if (!$this->isPost()) {
            return $this->redirect('register');
        }

        // Rate limit registration: max 3 accounts per IP per hour (anti-spam)
        $rateLimitKey = 'register:' . client_ip();
        if (!RateLimiter::attempt($rateLimitKey, 3, 60)) {
            $this->setFlash('error', 'Quá nhiều yêu cầu đăng ký. Vui lòng thử lại sau.');
            return $this->back();
        }

        $name = trim($this->input('name'));
        $email = trim($this->input('email'));
        $password = $this->input('password');
        $passwordConfirm = $this->input('password_confirm');

        if (empty($name) || empty($email) || empty($password)) {
            $this->setFlash('error', 'Vui lòng nhập đầy đủ thông tin.');
            return $this->back();
        }

        if ($password !== $passwordConfirm) {
            $this->setFlash('error', 'Mật khẩu xác nhận không khớp.');
            return $this->back();
        }

        // Password policy check
        $pwErrors = PasswordPolicy::validate($password);
        if (!empty($pwErrors)) {
            $this->setFlash('error', implode('. ', $pwErrors));
            return $this->back();
        }

        $existing = Database::fetch("SELECT id FROM users WHERE email = ? LIMIT 1", [$email]);

        if ($existing) {
            $this->setFlash('error', 'Email này đã được sử dụng bởi tài khoản khác.');
            return $this->back();
        }

        $hashedPassword = Auth::hashPassword($password);

        // Resolve tenant_id (from session or default)
        $tenantId = $_SESSION['tenant_id'] ?? null;
        if (!$tenantId) {
            $host = strtok($_SERVER['HTTP_HOST'] ?? '', ':');
            $tenant = Database::fetch("SELECT id FROM tenants WHERE domain = ? AND is_active = 1 LIMIT 1", [$host]);
            if (!$tenant) {
                $tenant = Database::fetch("SELECT id FROM tenants WHERE id = 1 LIMIT 1");
            }
            $tenantId = $tenant['id'] ?? 1;
        }

        $userId = Database::insert('users', [
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
            'role' => 'staff',
            'tenant_id' => (int) $tenantId,
            'is_active' => 1,
        ]);

        $user = Database::fetch("SELECT * FROM users WHERE id = ? LIMIT 1", [$userId]);

        Auth::login($user);

        // Set tenant session for immediate dashboard access
        $_SESSION['tenant_id'] = (int) $tenantId;
        if (empty($_SESSION['tenant'])) {
            $tenant = Database::fetch("SELECT * FROM tenants WHERE id = ? LIMIT 1", [$tenantId]);
            $_SESSION['tenant'] = $tenant ?: ['id' => $tenantId, 'name' => 'Default', 'slug' => 'default'];
        }

        $this->setFlash('success', 'Đăng ký thành công! Chào mừng bạn đến với ToryCRM.');
        return $this->redirect('dashboard');
    }

    public function logout()
    {
        if ($uid = Auth::id()) AuditLog::logout($uid);
        Auth::logout();
        return $this->redirect('login');
    }

    /** Display the 2FA prompt after password-phase success. */
    public function twoFactorForm()
    {
        if (empty($_SESSION['_2fa_pending_user_id'])) return $this->redirect('login');
        return $this->view('auth.2fa');
    }

    /** Verify TOTP code (or backup code) for the pending user and complete login. */
    public function twoFactorVerify()
    {
        if (!$this->isPost()) return $this->redirect('login/2fa');
        $pendingId = (int)($_SESSION['_2fa_pending_user_id'] ?? 0);
        $startedAt = (int)($_SESSION['_2fa_pending_at'] ?? 0);
        if (!$pendingId || (time() - $startedAt) > 300) {
            unset($_SESSION['_2fa_pending_user_id'], $_SESSION['_2fa_pending_remember'], $_SESSION['_2fa_pending_at']);
            $this->setFlash('error', 'Phiên 2FA đã hết hạn. Vui lòng đăng nhập lại.');
            return $this->redirect('login');
        }
        // Rate limit 5/min/IP
        if (!RateLimiter::attempt('2fa:' . client_ip(), 5, 1)) {
            $this->setFlash('error', 'Quá nhiều lần thử. Chờ 1 phút.');
            return $this->back();
        }

        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$pendingId]);
        if (!$user) return $this->redirect('login');

        $code = trim($this->input('code') ?? '');
        $ok = \App\Services\Totp::verify($user['totp_secret'] ?? '', $code);

        // Backup code fallback (8-digit numeric)
        if (!$ok && !empty($user['totp_backup_codes'])) {
            $codes = json_decode($user['totp_backup_codes'], true) ?: [];
            if (in_array($code, $codes, true)) {
                $ok = true;
                // Burn the used code (one-time only)
                $codes = array_values(array_diff($codes, [$code]));
                Database::update('users', ['totp_backup_codes' => json_encode($codes)], 'id = ?', [$pendingId]);
                AuditLog::log('2fa_backup_used', 'auth', $pendingId, 'Đăng nhập bằng backup code');
            }
        }

        if (!$ok) {
            AuditLog::log('2fa_failed', 'auth', $pendingId, 'Sai mã OTP');
            $this->setFlash('error', 'Mã OTP không đúng.');
            return $this->back();
        }

        // Success — finalise login
        unset($_SESSION['_2fa_pending_user_id'], $_SESSION['_2fa_pending_remember'], $_SESSION['_2fa_pending_at']);
        session_regenerate_id(true);
        Auth::login($user);
        Database::update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);
        AuditLog::loginSuccess((int)$user['id'], $user['email']);

        return $this->redirect('dashboard');
    }

    public function forgotForm()
    {
        return $this->view('auth.forgot-password');
    }

    public function forgot()
    {
        if (!$this->isPost()) {
            return $this->redirect('forgot-password');
        }

        $email = trim($this->input('email'));
        if (empty($email)) {
            $this->setFlash('error', 'Vui lòng nhập email.');
            return $this->back();
        }

        // Basic rate limit: max 5 reset requests / hour / email
        $recent = Database::fetch(
            "SELECT COUNT(*) as c FROM password_reset_tokens t
             JOIN users u ON u.id = t.user_id
             WHERE u.email = ? AND t.created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)",
            [$email]
        );
        if ((int)($recent['c'] ?? 0) >= 5) {
            $this->setFlash('success', 'Nếu tài khoản tồn tại, link đặt lại mật khẩu đã được gửi.');
            return $this->back();
        }

        $user = Database::fetch(
            "SELECT id, name, email FROM users WHERE email = ? AND is_active = 1 LIMIT 1",
            [$email]
        );

        if ($user) {
            $token = bin2hex(random_bytes(32)); // 64-char token
            $tokenHash = hash('sha256', $token);
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

            try {
                Database::insert('password_reset_tokens', [
                    'user_id' => $user['id'],
                    'token_hash' => $tokenHash,
                    'expires_at' => $expires,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                ]);

                $resetUrl = url('reset-password/' . $token);
                $brand = \App\Services\BrandingService::get()['name'] ?? 'ToryCRM';
                $body = "<p>Xin chào " . htmlspecialchars($user['name'] ?? '') . ",</p>"
                      . "<p>Bạn vừa yêu cầu đặt lại mật khẩu cho tài khoản <strong>{$brand}</strong>.</p>"
                      . "<p>Nhấn vào link bên dưới để đặt mật khẩu mới (hết hạn sau 1 giờ):</p>"
                      . "<p><a href=\"{$resetUrl}\" style=\"display:inline-block;padding:10px 20px;background:#405189;color:#fff;text-decoration:none;border-radius:4px\">Đặt lại mật khẩu</a></p>"
                      . "<p>Hoặc copy link này: <br><code>{$resetUrl}</code></p>"
                      . "<p>Nếu không phải bạn yêu cầu, bỏ qua email này.</p>";

                \App\Services\MailService::send(
                    $user['email'],
                    "[{$brand}] Đặt lại mật khẩu",
                    $body,
                    $user['name'] ?? null
                );
            } catch (\Throwable $e) {
                error_log('[password-reset] ' . $e->getMessage());
            }
        }

        // Always show same message (prevent email enumeration)
        $this->setFlash('success', 'Nếu tài khoản tồn tại, link đặt lại mật khẩu đã được gửi tới email.');
        return $this->back();
    }

    public function resetForm($token)
    {
        $row = $this->validateResetToken((string) $token);
        if (!$row) {
            $this->setFlash('error', 'Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.');
            return $this->redirect('forgot-password');
        }
        return $this->view('auth.reset-password', ['token' => $token]);
    }

    public function reset($token)
    {
        if (!$this->isPost()) return $this->redirect('reset-password/' . $token);

        $row = $this->validateResetToken((string) $token);
        if (!$row) {
            $this->setFlash('error', 'Link không hợp lệ hoặc đã hết hạn.');
            return $this->redirect('forgot-password');
        }

        $password = (string) $this->input('password', '');
        $confirm = (string) $this->input('password_confirm', '');
        if (strlen($password) < 8) {
            $this->setFlash('error', 'Mật khẩu tối thiểu 8 ký tự.');
            return $this->back();
        }
        if ($password !== $confirm) {
            $this->setFlash('error', 'Mật khẩu xác nhận không khớp.');
            return $this->back();
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        Database::update('users', ['password' => $hash], 'id = ?', [$row['user_id']]);
        Database::update('password_reset_tokens', ['used_at' => date('Y-m-d H:i:s')], 'id = ?', [$row['id']]);
        // Invalidate other outstanding tokens for this user
        Database::query(
            "UPDATE password_reset_tokens SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL AND id != ?",
            [$row['user_id'], $row['id']]
        );

        $this->setFlash('success', 'Đã đặt lại mật khẩu thành công. Vui lòng đăng nhập.');
        return $this->redirect('login');
    }

    private function validateResetToken(string $token): ?array
    {
        if (strlen($token) !== 64) return null;
        $hash = hash('sha256', $token);
        $row = Database::fetch(
            "SELECT id, user_id, expires_at, used_at FROM password_reset_tokens
             WHERE token_hash = ? LIMIT 1",
            [$hash]
        );
        if (!$row) return null;
        if ($row['used_at']) return null;
        if (strtotime($row['expires_at']) < time()) return null;
        return $row;
    }
}
