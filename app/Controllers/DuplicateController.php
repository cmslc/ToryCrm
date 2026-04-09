<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Services\DuplicateDetector;

class DuplicateController extends Controller
{
    public function index()
    {
        $tenantId = Database::tenantId();
        $entityType = $this->input('type', '');

        $groups = DuplicateDetector::getGroups($tenantId, $entityType);

        $contactCount = 0;
        $companyCount = 0;
        foreach ($groups as $g) {
            if ($g['entity_type'] === 'contact') $contactCount++;
            if ($g['entity_type'] === 'company') $companyCount++;
        }

        return $this->view('duplicates.index', [
            'groups' => $groups,
            'contactCount' => $contactCount,
            'companyCount' => $companyCount,
            'filterType' => $entityType,
        ]);
    }

    public function scan()
    {
        if (!$this->isPost()) {
            return $this->redirect('duplicates');
        }

        $tenantId = Database::tenantId();

        $contactGroups = DuplicateDetector::scanContacts($tenantId);
        $companyGroups = DuplicateDetector::scanCompanies($tenantId);

        $this->setFlash('success', "Đã quét xong. Tìm thấy {$contactGroups} nhóm trùng khách hàng, {$companyGroups} nhóm trùng doanh nghiệp.");
        return $this->redirect('duplicates');
    }

    public function merge($groupId)
    {
        if (!$this->isPost()) {
            return $this->redirect('duplicates');
        }

        $keepId = (int) $this->input('keep_id', 0);

        if ($keepId <= 0) {
            $this->setFlash('error', 'Vui lòng chọn bản ghi cần giữ lại.');
            return $this->redirect('duplicates');
        }

        $result = DuplicateDetector::merge((int) $groupId, $keepId);

        if ($result) {
            $this->setFlash('success', 'Đã gộp thành công.');
        } else {
            $this->setFlash('error', 'Không thể gộp. Vui lòng thử lại.');
        }

        return $this->redirect('duplicates');
    }

    public function ignore($groupId)
    {
        if (!$this->isPost()) {
            return $this->redirect('duplicates');
        }

        DuplicateDetector::ignore((int) $groupId);

        $this->setFlash('success', 'Đã bỏ qua nhóm trùng lặp.');
        return $this->redirect('duplicates');
    }
}
