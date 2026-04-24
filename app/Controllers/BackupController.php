<?php

namespace App\Controllers;

use Core\Controller;

class BackupController extends Controller
{
    private const BACKUP_DIR = '/var/backups/torycrm-mysql';
    private const SCRIPT = '/usr/local/bin/torycrm-backup';

    public function index()
    {
        $this->requireAdmin();
        $files = $this->listBackups();
        $totalSize = array_sum(array_column($files, 'size'));
        $logTail = $this->tailLog();
        return $this->view('backups.index', compact('files', 'totalSize', 'logTail'));
    }

    /** Download a backup file. Enforces filename whitelist to prevent path traversal. */
    public function download()
    {
        $this->requireAdmin();
        $name = basename((string)$this->input('file', ''));
        if (!preg_match('/^torycrm_\d{8}_\d{6}\.sql\.gz$/', $name)) {
            $this->setFlash('error', 'Tên file không hợp lệ.');
            return $this->redirect('backups');
        }
        $path = self::BACKUP_DIR . '/' . $name;
        if (!is_file($path)) {
            $this->setFlash('error', 'File không tồn tại.');
            return $this->redirect('backups');
        }
        \App\Services\AuditLog::log('backup', 0, 'download', ['file' => $name]);
        header('Content-Type: application/gzip');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Content-Length: ' . filesize($path));
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }

    /** Soft delete = move to .deleted subfolder so admin can restore if mistake. */
    public function delete()
    {
        $this->requireAdmin();
        if (!$this->isPost()) return $this->redirect('backups');
        $name = basename((string)$this->input('file', ''));
        if (!preg_match('/^torycrm_\d{8}_\d{6}\.sql\.gz$/', $name)) {
            $this->setFlash('error', 'Tên file không hợp lệ.');
            return $this->redirect('backups');
        }
        $path = self::BACKUP_DIR . '/' . $name;
        if (is_file($path)) {
            @unlink($path);
            \App\Services\AuditLog::log('backup', 0, 'delete', ['file' => $name]);
            $this->setFlash('success', 'Đã xóa ' . $name);
        }
        return $this->redirect('backups');
    }

    /** Trigger manual backup via sudoers-allowed script. */
    public function runNow()
    {
        $this->requireAdmin();
        if (!$this->isPost()) return $this->redirect('backups');

        // Rate limit: max 3 manual backups / hour (prevents abuse)
        if (!\App\Services\RateLimiter::attempt('backup:run:' . $this->userId(), 3, 60)) {
            $this->setFlash('error', 'Đã chạy backup nhiều lần. Chờ 1 giờ trước khi chạy lại thủ công.');
            return $this->redirect('backups');
        }

        $before = $this->listBackups();
        $beforeCount = count($before);

        // Use proc_open to capture exit code reliably
        $out = [];
        $rc = 0;
        exec('sudo -n ' . escapeshellarg(self::SCRIPT) . ' 2>&1', $out, $rc);

        if ($rc !== 0) {
            \App\Services\AuditLog::log('backup', 0, 'run_failed', ['rc' => $rc, 'out' => implode("\n", array_slice($out, 0, 10))]);
            $this->setFlash('error', 'Backup lỗi (exit ' . $rc . '). Xem log /var/log/torycrm-backup.log.');
            return $this->redirect('backups');
        }

        $after = $this->listBackups();
        $newFile = count($after) > $beforeCount ? $after[0]['name'] : null;

        \App\Services\AuditLog::log('backup', 0, 'run_success', ['file' => $newFile]);
        $this->setFlash('success', 'Backup thành công' . ($newFile ? ': ' . $newFile : '') . '.');
        return $this->redirect('backups');
    }

    private function listBackups(): array
    {
        if (!is_dir(self::BACKUP_DIR)) return [];
        $files = [];
        foreach (glob(self::BACKUP_DIR . '/torycrm_*.sql.gz') ?: [] as $path) {
            $files[] = [
                'name' => basename($path),
                'size' => filesize($path) ?: 0,
                'mtime' => filemtime($path) ?: 0,
            ];
        }
        usort($files, fn($a, $b) => $b['mtime'] <=> $a['mtime']);
        return $files;
    }

    private function tailLog(int $lines = 15): array
    {
        $log = '/var/log/torycrm-backup.log';
        if (!is_readable($log)) return [];
        $all = @file($log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        return array_slice($all, -$lines);
    }

    private function requireAdmin(): void
    {
        if (!$this->isSystemAdmin()) {
            $this->setFlash('error', 'Chỉ admin mới truy cập được quản lý backup.');
            $this->redirect('dashboard');
            exit;
        }
    }
}
