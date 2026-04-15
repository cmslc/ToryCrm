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
            'tax_code' => trim($data['tax_code'] ?? ''),
            'address' => trim($data['address'] ?? ''),
            'branch_address' => trim($data['branch_address'] ?? ''),
            'email' => trim($data['email'] ?? ''),
            'phone' => trim($data['phone'] ?? ''),
            'fax' => trim($data['fax'] ?? ''),
            'website' => trim($data['website'] ?? ''),
            'representative' => trim($data['representative'] ?? ''),
            'representative_title' => trim($data['representative_title'] ?? ''),
            'bank_account' => trim($data['bank_account'] ?? ''),
            'bank_name' => trim($data['bank_name'] ?? ''),
            'primary_color' => trim($data['primary_color'] ?? '#405189'),
            'sidebar_color' => trim($data['sidebar_color'] ?? ''),
            'login_bg' => trim($data['login_bg'] ?? ''),
            'custom_css' => trim($data['custom_css'] ?? ''),
        ];

        // Handle logo upload
        $current = BrandingService::get();
        $currentLogo = $current['logo_url'] ?? '';
        if (is_array($currentLogo)) $currentLogo = $currentLogo['file_path'] ?? '';
        $branding['logo_url'] = $currentLogo;
        $currentFav = $current['favicon_url'] ?? '';
        if (is_array($currentFav)) $currentFav = $currentFav['file_path'] ?? '';
        $branding['favicon_url'] = $currentFav;

        if (!empty($_FILES['logo']['tmp_name'])) {
            $uploaded = FileUploadService::upload($_FILES['logo'], 'branding');
            if ($uploaded) {
                $branding['logo_url'] = is_array($uploaded) ? ($uploaded['file_path'] ?? $uploaded) : $uploaded;
            }
        }

        if (!empty($_FILES['favicon']['tmp_name'])) {
            $uploaded = FileUploadService::upload($_FILES['favicon'], 'branding');
            if ($uploaded) {
                $branding['favicon_url'] = is_array($uploaded) ? ($uploaded['file_path'] ?? $uploaded) : $uploaded;
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
