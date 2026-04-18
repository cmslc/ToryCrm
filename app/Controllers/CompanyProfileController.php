<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class CompanyProfileController extends Controller
{
    public function index()
    {
        $this->authorize('settings', 'manage');
        $tid = Database::tenantId();

        $profiles = Database::fetchAll(
            "SELECT * FROM company_profiles WHERE tenant_id = ? ORDER BY is_default DESC, name",
            [$tid]
        );

        return $this->view('settings.company-profiles', [
            'profiles' => $profiles,
        ]);
    }

    public function store()
    {
        if (!$this->isPost()) return $this->redirect('settings/company-profiles');
        $this->authorize('settings', 'manage');

        $data = $this->allInput();
        $tid = Database::tenantId();

        if (empty(trim($data['name'] ?? ''))) {
            $this->setFlash('error', 'Vui lòng nhập tên công ty.');
            return $this->back();
        }

        if (!empty($data['is_default'])) {
            Database::query("UPDATE company_profiles SET is_default = 0 WHERE tenant_id = ?", [$tid]);
        }

        Database::insert('company_profiles', [
            'tenant_id' => $tid,
            'name' => trim($data['name']),
            'short_name' => trim($data['short_name'] ?? '') ?: null,
            'tax_code' => trim($data['tax_code'] ?? '') ?: null,
            'address' => trim($data['address'] ?? '') ?: null,
            'phone' => trim($data['phone'] ?? '') ?: null,
            'fax' => trim($data['fax'] ?? '') ?: null,
            'email' => trim($data['email'] ?? '') ?: null,
            'website' => trim($data['website'] ?? '') ?: null,
            'representative' => trim($data['representative'] ?? '') ?: null,
            'representative_title' => trim($data['representative_title'] ?? '') ?: null,
            'bank_account' => trim($data['bank_account'] ?? '') ?: null,
            'bank_name' => trim($data['bank_name'] ?? '') ?: null,
            'logo' => $this->handleLogoUpload(),
            'is_default' => !empty($data['is_default']) ? 1 : 0,
        ]);

        $this->setFlash('success', 'Đã thêm công ty.');
        return $this->redirect('settings/company-profiles');
    }

    public function update($id)
    {
        if (!$this->isPost()) return $this->redirect('settings/company-profiles');
        $this->authorize('settings', 'manage');

        $tid = Database::tenantId();
        $data = $this->allInput();

        if (!empty($data['is_default'])) {
            Database::query("UPDATE company_profiles SET is_default = 0 WHERE tenant_id = ?", [$tid]);
        }

        Database::update('company_profiles', [
            'name' => trim($data['name'] ?? ''),
            'short_name' => trim($data['short_name'] ?? '') ?: null,
            'tax_code' => trim($data['tax_code'] ?? '') ?: null,
            'address' => trim($data['address'] ?? '') ?: null,
            'phone' => trim($data['phone'] ?? '') ?: null,
            'fax' => trim($data['fax'] ?? '') ?: null,
            'email' => trim($data['email'] ?? '') ?: null,
            'website' => trim($data['website'] ?? '') ?: null,
            'representative' => trim($data['representative'] ?? '') ?: null,
            'representative_title' => trim($data['representative_title'] ?? '') ?: null,
            'bank_account' => trim($data['bank_account'] ?? '') ?: null,
            'bank_name' => trim($data['bank_name'] ?? '') ?: null,
            'is_default' => !empty($data['is_default']) ? 1 : 0,
        ], 'id = ? AND tenant_id = ?', [$id, $tid]);

        $logo = $this->handleLogoUpload();
        if ($logo) {
            Database::update('company_profiles', ['logo' => $logo], 'id = ?', [$id]);
        }

        $this->setFlash('success', 'Đã cập nhật.');
        return $this->redirect('settings/company-profiles');
    }

    public function delete($id)
    {
        if (!$this->isPost()) return $this->redirect('settings/company-profiles');
        $this->authorize('settings', 'manage');

        Database::delete('company_profiles', 'id = ? AND tenant_id = ?', [$id, Database::tenantId()]);
        $this->setFlash('success', 'Đã xóa.');
        return $this->redirect('settings/company-profiles');
    }

    private function handleLogoUpload(): ?string
    {
        if (empty($_FILES['logo']['name']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) return null;

        $file = $_FILES['logo'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','gif','svg','webp'])) return null;
        if ($file['size'] > 2 * 1024 * 1024) return null;

        $dir = 'uploads/company-logos';
        if (!is_dir(BASE_PATH . '/public/' . $dir)) {
            mkdir(BASE_PATH . '/public/' . $dir, 0755, true);
        }

        $fileName = uniqid() . '.' . $ext;
        $filePath = $dir . '/' . $fileName;
        move_uploaded_file($file['tmp_name'], BASE_PATH . '/public/' . $filePath);

        return $filePath;
    }
}
