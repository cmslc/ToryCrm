<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Helpers\Auth;
use App\Services\RateLimiter;
use App\Services\PasswordPolicy;

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
        $rateLimitKey = 'login:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        if (!RateLimiter::attempt($rateLimitKey, 5, 15)) {
            $this->setFlash('error', 'Quá nhiều lần thử. Vui lòng chờ 15 phút.');
            return $this->back();
        }

        $user = Database::fetch("SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1", [$email]);

        // Check account locked
        if ($user && $user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $this->setFlash('error', 'Tài khoản đã bị khóa tạm thời. Thử lại sau.');
            return $this->back();
        }

        if (!$user || !Auth::verifyPassword($password, $user['password'])) {
            // Increment login attempts
            if ($user) {
                $attempts = ($user['login_attempts'] ?? 0) + 1;
                $updateData = ['login_attempts' => $attempts];
                if ($attempts >= 5) {
                    $updateData['locked_until'] = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                }
                Database::update('users', $updateData, 'id = ?', [$user['id']]);
            }
            $this->setFlash('error', 'Email hoặc mật khẩu không đúng.');
            return $this->back();
        }

        // Clear rate limit on success
        RateLimiter::clear($rateLimitKey);

        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        Auth::login($user);

        // Update last login
        Database::update('users', [
            'last_login' => date('Y-m-d H:i:s'),
            'login_attempts' => 0,
            'locked_until' => null,
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
        Auth::logout();
        return $this->redirect('login');
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
            $this->setFlash('error', 'Email is required.');
            return $this->back();
        }

        $user = Database::fetch("SELECT id FROM users WHERE email = ? AND is_active = 1 LIMIT 1", [$email]);

        if ($user) {
            // TODO: Generate reset token, save to DB, send reset email
        }

        // Always show success to prevent email enumeration
        $this->setFlash('success', 'If an account with that email exists, a password reset link has been sent.');
        return $this->back();
    }
}
