<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Services\PluginManager;

class PluginController extends Controller
{
    public function marketplace()
    {
        $tenantId = $this->tenantId();
        $allPlugins = PluginManager::getAvailable();
        $installedIds = PluginManager::getInstalledIds($tenantId);

        $search = $this->input('search');
        $category = $this->input('category');
        $sort = $this->input('sort');

        // Filter
        $plugins = array_filter($allPlugins, function ($p) use ($search, $category) {
            if ($search && stripos($p['name'] . $p['description'], $search) === false) return false;
            if ($category && $p['category'] !== $category) return false;
            return true;
        });

        // Sort
        $plugins = array_values($plugins);
        if ($sort === 'name') {
            usort($plugins, function ($a, $b) { return strcasecmp($a['name'], $b['name']); });
        } elseif ($sort === 'newest') {
            usort($plugins, function ($a, $b) { return ($b['id'] ?? 0) - ($a['id'] ?? 0); });
        }

        // Get categories
        $categories = array_unique(array_column($allPlugins, 'category'));
        sort($categories);

        return $this->view('plugins.plugin-install', [
            'plugins' => $plugins,
            'installedIds' => $installedIds,
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'category' => $category,
                'sort' => $sort,
            ],
        ]);
    }

    public function installed()
    {
        $tenantId = $this->tenantId();
        $plugins = PluginManager::getInstalled($tenantId);

        return $this->view('plugins.installed', [
            'plugins' => $plugins,
        ]);
    }

    public function install($id)
    {
        if (!$this->isPost()) return $this->redirect('plugins/marketplace');

        $tenantId = $this->tenantId();
        $plugin = Database::fetch("SELECT * FROM plugins WHERE id = ?", [$id]);

        if (!$plugin) {
            $this->setFlash('error', 'Plugin không tồn tại.');
            return $this->redirect('plugins/marketplace');
        }

        if (PluginManager::install($tenantId, (int) $id)) {
            $this->setFlash('success', "Đã cài đặt plugin \"{$plugin['name']}\" thành công.");
        } else {
            $this->setFlash('warning', 'Plugin đã được cài đặt trước đó.');
        }

        return $this->redirect('plugins');
    }

    public function uninstall($id)
    {
        if (!$this->isPost()) return $this->redirect('plugins');

        $tenantId = $this->tenantId();
        $plugin = Database::fetch("SELECT * FROM plugins WHERE id = ?", [$id]);

        if (!$plugin) {
            $this->setFlash('error', 'Plugin không tồn tại.');
            return $this->redirect('plugins');
        }

        PluginManager::uninstall($tenantId, (int) $id);
        $this->setFlash('success', "Đã gỡ cài đặt plugin \"{$plugin['name']}\".");
        return $this->redirect('plugins');
    }

    public function toggleActive($id)
    {
        if (!$this->isPost()) return $this->redirect('plugins');

        $tenantId = $this->tenantId();
        PluginManager::toggleActive($tenantId, (int) $id);

        $this->setFlash('success', 'Đã cập nhật trạng thái plugin.');
        return $this->redirect('plugins');
    }

    public function configure($id)
    {
        $tenantId = $this->tenantId();
        $plugin = Database::fetch("SELECT * FROM plugins WHERE id = ?", [$id]);

        if (!$plugin) {
            $this->setFlash('error', 'Plugin không tồn tại.');
            return $this->redirect('plugins');
        }

        // Check if installed
        if (!PluginManager::isInstalled($tenantId, (int) $id)) {
            $this->setFlash('error', 'Plugin chưa được cài đặt.');
            return $this->redirect('plugins/marketplace');
        }

        // Redirect to plugin's own settings page if exists
        $settingsRoutes = [
            'email' => 'email/settings',
        ];
        if (isset($settingsRoutes[$plugin['slug']])) {
            return $this->redirect($settingsRoutes[$plugin['slug']]);
        }

        $configSchema = json_decode($plugin['config'] ?? '{}', true);
        $tenantConfig = PluginManager::getConfig($tenantId, $plugin['slug']) ?: [];

        return $this->view('plugins.configure', [
            'plugin' => $plugin,
            'configSchema' => $configSchema,
            'tenantConfig' => $tenantConfig,
        ]);
    }

    public function saveConfig($id)
    {
        if (!$this->isPost()) return $this->redirect('plugins');

        $tenantId = $this->tenantId();
        $plugin = Database::fetch("SELECT * FROM plugins WHERE id = ?", [$id]);

        if (!$plugin) {
            $this->setFlash('error', 'Plugin không tồn tại.');
            return $this->redirect('plugins');
        }

        $configSchema = json_decode($plugin['config'] ?? '{}', true);
        $fields = $configSchema['fields'] ?? [];

        $config = [];
        foreach ($fields as $field) {
            $key = $field['key'];
            if ($field['type'] === 'checkbox') {
                $config[$key] = $this->input($key) ? true : false;
            } else {
                $config[$key] = $this->input($key, $field['default'] ?? '');
            }
        }

        PluginManager::saveConfig($tenantId, (int) $id, $config);
        $this->setFlash('success', "Đã lưu cấu hình plugin \"{$plugin['name']}\".");
        return $this->redirect('plugins/' . $id . '/configure');
    }
}
