<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Services\BrandingService;
use App\Services\FileUploadService;

class WhiteLabelController extends Controller
{
    public function settings()
    {
        $branding = BrandingService::get();

        return $this->view('settings.white-label', [
            'branding' => $branding,
        ]);
    }

    public function save()
    {
        if (!$this->isPost()) return $this->redirect('settings/white-label');

        $data = $this->allInput();

        $branding = [
            'name' => trim($data['name'] ?? 'ToryCRM'),
            'primary_color' => trim($data['primary_color'] ?? '#405189'),
            'sidebar_color' => trim($data['sidebar_color'] ?? ''),
            'login_bg' => trim($data['login_bg'] ?? ''),
            'custom_css' => trim($data['custom_css'] ?? ''),
        ];

        // Handle logo upload
        $current = BrandingService::get();
        $branding['logo_url'] = $current['logo_url'] ?? '';
        $branding['favicon_url'] = $current['favicon_url'] ?? '';

        if (!empty($_FILES['logo']['tmp_name'])) {
            $uploaded = FileUploadService::upload($_FILES['logo'], 'branding');
            if ($uploaded) {
                $branding['logo_url'] = $uploaded;
            }
        }

        if (!empty($_FILES['favicon']['tmp_name'])) {
            $uploaded = FileUploadService::upload($_FILES['favicon'], 'branding');
            if ($uploaded) {
                $branding['favicon_url'] = $uploaded;
            }
        }

        if (BrandingService::save($branding)) {
            $this->setFlash('success', 'Đã lưu cài đặt thương hiệu.');
        } else {
            $this->setFlash('error', 'Không thể lưu cài đặt. Vui lòng thử lại.');
        }

        return $this->redirect('settings/white-label');
    }
}
