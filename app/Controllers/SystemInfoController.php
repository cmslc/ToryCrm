<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class SystemInfoController extends Controller
{
    public function index()
    {
        if (!$this->isAdminOrManager()) {
            $this->setFlash('error', 'Bạn không có quyền.');
            return $this->redirect('dashboard');
        }

        // Server info
        $server = [
            'php_version' => PHP_VERSION,
            'mysql_version' => Database::fetch("SELECT VERSION() as v")['v'] ?? 'N/A',
            'os' => php_uname('s') . ' ' . php_uname('r'),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'hostname' => gethostname(),
            'server_ip' => $_SERVER['SERVER_ADDR'] ?? 'N/A',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? BASE_PATH,
            'php_memory_limit' => ini_get('memory_limit'),
            'php_max_upload' => ini_get('upload_max_filesize'),
            'php_max_post' => ini_get('post_max_size'),
            'php_max_execution' => ini_get('max_execution_time') . 's',
            'php_extensions' => implode(', ', get_loaded_extensions()),
            'timezone' => date_default_timezone_get(),
            'uptime' => @file_get_contents('/proc/uptime') ? round((float)file_get_contents('/proc/uptime') / 86400, 1) . ' ngày' : 'N/A',
        ];

        // System resources
        $memTotal = $memFree = $memUsed = 0;
        if (file_exists('/proc/meminfo')) {
            $meminfo = file_get_contents('/proc/meminfo');
            preg_match('/MemTotal:\s+(\d+)/', $meminfo, $m); $memTotal = ($m[1] ?? 0) / 1024;
            preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $m); $memFree = ($m[1] ?? 0) / 1024;
            $memUsed = $memTotal - $memFree;
        }
        $diskTotal = @disk_total_space('/') / 1024 / 1024 / 1024;
        $diskFree = @disk_free_space('/') / 1024 / 1024 / 1024;
        $diskUsed = $diskTotal - $diskFree;

        $resources = [
            'ram_total' => round($memTotal) . ' MB',
            'ram_used' => round($memUsed) . ' MB',
            'ram_free' => round($memFree) . ' MB',
            'ram_percent' => $memTotal > 0 ? round($memUsed / $memTotal * 100) : 0,
            'disk_total' => round($diskTotal, 1) . ' GB',
            'disk_used' => round($diskUsed, 1) . ' GB',
            'disk_free' => round($diskFree, 1) . ' GB',
            'disk_percent' => $diskTotal > 0 ? round($diskUsed / $diskTotal * 100) : 0,
        ];

        // Database info
        $dbSize = Database::fetch("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size FROM information_schema.tables WHERE table_schema = DATABASE()")['size'] ?? 0;
        $dbTables = Database::fetch("SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = DATABASE()")['cnt'] ?? 0;

        $topTables = Database::fetchAll(
            "SELECT table_name, table_rows, ROUND((data_length + index_length) / 1024 / 1024, 2) as size_mb
             FROM information_schema.tables WHERE table_schema = DATABASE()
             ORDER BY (data_length + index_length) DESC LIMIT 10"
        );

        $dbStats = [
            'size' => $dbSize . ' MB',
            'tables' => $dbTables,
            'top_tables' => $topTables,
        ];

        // Record counts
        $counts = [];
        $countTables = ['contacts','deals','tasks','orders','tickets','quotations','contracts','fund_transactions','debts','email_messages','activities','users','lead_form_submissions'];
        foreach ($countTables as $t) {
            try {
                $counts[$t] = Database::fetch("SELECT COUNT(*) as c FROM {$t}")['c'] ?? 0;
            } catch (\Exception $e) { $counts[$t] = 0; }
        }

        // Plugins
        $plugins = Database::fetchAll("SELECT p.name, p.slug, tp.is_active FROM plugins p LEFT JOIN tenant_plugins tp ON p.id = tp.plugin_id AND tp.tenant_id = ? ORDER BY p.name", [$this->tenantId()]);

        // CRM version
        $version = '1.0.0';
        if (file_exists(BASE_PATH . '/version.php')) {
            include BASE_PATH . '/version.php';
            $version = defined('APP_VERSION') ? APP_VERSION : '1.0.0';
        }

        return $this->view('system.info', compact('server', 'resources', 'dbStats', 'counts', 'plugins', 'version'));
    }
}
