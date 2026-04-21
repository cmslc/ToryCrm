<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Services\Totp;
use App\Services\AuditLog;

class TwoFactorController extends Controller
{
    /** Show setup page — generate secret if not yet set up. */
    public function setup()
    {
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$this->userId()]);
        if (!$user) return $this->redirect('login');

        if (!empty($user['totp_enabled'])) {
            return $this->view('auth.2fa-manage', ['user' => $user]);
        }

        // Generate a fresh secret — stash in session until user confirms by entering a valid code
        if (empty($_SESSION['_2fa_setup_secret'])) {
            $_SESSION['_2fa_setup_secret'] = Totp::generateSecret();
        }
        $secret = $_SESSION['_2fa_setup_secret'];
        $otpauthUrl = Totp::otpauthUrl($secret, $user['email'], 'ToryCRM');

        return $this->view('auth.2fa-setup', [
            'user' => $user,
            'secret' => $secret,
            'otpauthUrl' => $otpauthUrl,
        ]);
    }

    /** Verify the one-time code during setup; if ok, enable + generate backup codes. */
    public function enable()
    {
        if (!$this->isPost()) return $this->redirect('settings/2fa');
        $secret = $_SESSION['_2fa_setup_secret'] ?? '';
        $code = trim($this->input('code') ?? '');

        if (!$secret || !Totp::verify($secret, $code)) {
            $this->setFlash('error', 'Mã xác thực không đúng. Vui lòng thử lại.');
            return $this->redirect('settings/2fa');
        }

        $backup = Totp::generateBackupCodes(10);
        Database::update('users', [
            'totp_secret' => $secret,
            'totp_enabled' => 1,
            'totp_enabled_at' => date('Y-m-d H:i:s'),
            'totp_backup_codes' => json_encode($backup),
        ], 'id = ?', [$this->userId()]);

        unset($_SESSION['_2fa_setup_secret']);
        $_SESSION['_2fa_show_backup_codes'] = $backup; // show once

        AuditLog::log('2fa_enabled', 'auth', $this->userId(), 'Bật 2FA');
        $this->setFlash('success', 'Đã bật 2FA. Hãy lưu lại backup codes bên dưới.');
        return $this->redirect('settings/2fa');
    }

    /** Disable 2FA (require current OTP to confirm). */
    public function disable()
    {
        if (!$this->isPost()) return $this->redirect('settings/2fa');
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$this->userId()]);
        if (!$user || empty($user['totp_enabled'])) return $this->redirect('settings/2fa');

        $code = trim($this->input('code') ?? '');
        if (!Totp::verify($user['totp_secret'] ?? '', $code)) {
            $this->setFlash('error', 'Mã OTP không đúng.');
            return $this->redirect('settings/2fa');
        }

        Database::update('users', [
            'totp_secret' => null,
            'totp_enabled' => 0,
            'totp_enabled_at' => null,
            'totp_backup_codes' => null,
        ], 'id = ?', [$this->userId()]);

        AuditLog::log('2fa_disabled', 'auth', $this->userId(), 'Tắt 2FA');
        $this->setFlash('success', 'Đã tắt 2FA.');
        return $this->redirect('settings/2fa');
    }

    /** Regenerate backup codes (require OTP). */
    public function regenerateBackup()
    {
        if (!$this->isPost()) return $this->redirect('settings/2fa');
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$this->userId()]);
        if (!$user || empty($user['totp_enabled'])) return $this->redirect('settings/2fa');

        $code = trim($this->input('code') ?? '');
        if (!Totp::verify($user['totp_secret'] ?? '', $code)) {
            $this->setFlash('error', 'Mã OTP không đúng.');
            return $this->redirect('settings/2fa');
        }

        $backup = Totp::generateBackupCodes(10);
        Database::update('users', ['totp_backup_codes' => json_encode($backup)], 'id = ?', [$this->userId()]);
        $_SESSION['_2fa_show_backup_codes'] = $backup;
        AuditLog::log('2fa_backup_regen', 'auth', $this->userId(), 'Tạo lại backup codes');
        $this->setFlash('success', 'Đã tạo backup codes mới.');
        return $this->redirect('settings/2fa');
    }
}
