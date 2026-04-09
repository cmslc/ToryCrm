<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class ContactStatusController extends Controller
{
    public function index()
    {
        $statuses = Database::fetchAll(
            "SELECT * FROM contact_statuses WHERE tenant_id = ? ORDER BY sort_order",
            [$this->tenantId()]
        );

        return $this->view('settings.contact-statuses', [
            'statuses' => $statuses,
        ]);
    }

    public function store()
    {
        if (!$this->isPost()) return $this->redirect('settings/contact-statuses');

        $name = trim($this->input('name') ?? '');
        $color = $this->input('color') ?? 'secondary';
        $icon = $this->input('icon') ?? 'ri-circle-line';

        if (empty($name)) {
            $this->setFlash('error', 'Tên trạng thái không được để trống.');
            return $this->back();
        }

        $slug = $this->slugify($name);

        $exists = Database::fetch(
            "SELECT id FROM contact_statuses WHERE tenant_id = ? AND slug = ?",
            [$this->tenantId(), $slug]
        );
        if ($exists) {
            $this->setFlash('error', 'Trạng thái này đã tồn tại.');
            return $this->back();
        }

        $maxOrder = Database::fetch(
            "SELECT MAX(sort_order) as mx FROM contact_statuses WHERE tenant_id = ?",
            [$this->tenantId()]
        );

        Database::insert('contact_statuses', [
            'tenant_id' => $this->tenantId(),
            'slug' => $slug,
            'name' => $name,
            'color' => $color,
            'icon' => $icon,
            'sort_order' => ($maxOrder['mx'] ?? 0) + 1,
        ]);

        $this->setFlash('success', 'Đã thêm trạng thái "' . $name . '".');
        return $this->redirect('settings/contact-statuses');
    }

    public function update($id)
    {
        if (!$this->isPost()) return $this->redirect('settings/contact-statuses');

        $name = trim($this->input('name') ?? '');
        $color = $this->input('color') ?? 'secondary';
        $icon = $this->input('icon') ?? 'ri-circle-line';

        if (empty($name)) {
            $this->setFlash('error', 'Tên trạng thái không được để trống.');
            return $this->back();
        }

        Database::update('contact_statuses', [
            'name' => $name,
            'color' => $color,
            'icon' => $icon,
        ], 'id = ? AND tenant_id = ?', [(int)$id, $this->tenantId()]);

        $this->setFlash('success', 'Đã cập nhật trạng thái.');
        return $this->redirect('settings/contact-statuses');
    }

    public function delete($id)
    {
        if (!$this->isPost()) return $this->redirect('settings/contact-statuses');

        $status = Database::fetch(
            "SELECT * FROM contact_statuses WHERE id = ? AND tenant_id = ?",
            [(int)$id, $this->tenantId()]
        );

        if (!$status) {
            $this->setFlash('error', 'Trạng thái không tồn tại.');
            return $this->redirect('settings/contact-statuses');
        }

        if ($status['is_default']) {
            $this->setFlash('error', 'Không thể xóa trạng thái mặc định.');
            return $this->back();
        }

        // Count contacts using this status
        $count = Database::fetch(
            "SELECT COUNT(*) as cnt FROM contacts WHERE status = ? AND tenant_id = ?",
            [$status['slug'], $this->tenantId()]
        );

        if (($count['cnt'] ?? 0) > 0) {
            $this->setFlash('warning', 'Có ' . $count['cnt'] . ' khách hàng đang dùng trạng thái này. Hãy chuyển họ sang trạng thái khác trước.');
            return $this->back();
        }

        Database::delete('contact_statuses', 'id = ? AND tenant_id = ?', [(int)$id, $this->tenantId()]);
        $this->setFlash('success', 'Đã xóa trạng thái.');
        return $this->redirect('settings/contact-statuses');
    }

    public function reorder()
    {
        $ids = $this->input('ids');
        if (!is_array($ids)) return $this->json(['error' => 'Invalid data'], 400);

        foreach ($ids as $order => $id) {
            Database::update('contact_statuses', ['sort_order' => $order + 1], 'id = ? AND tenant_id = ?', [(int)$id, $this->tenantId()]);
        }

        return $this->json(['success' => true]);
    }

    public function setDefault($id)
    {
        if (!$this->isPost()) return $this->redirect('settings/contact-statuses');

        Database::query("UPDATE contact_statuses SET is_default = 0 WHERE tenant_id = ?", [$this->tenantId()]);
        Database::update('contact_statuses', ['is_default' => 1], 'id = ? AND tenant_id = ?', [(int)$id, $this->tenantId()]);

        $this->setFlash('success', 'Đã đặt trạng thái mặc định.');
        return $this->redirect('settings/contact-statuses');
    }

    private function slugify(string $text): string
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[àáạảãâầấậẩẫăằắặẳẵ]/u', 'a', $text);
        $text = preg_replace('/[èéẹẻẽêềếệểễ]/u', 'e', $text);
        $text = preg_replace('/[ìíịỉĩ]/u', 'i', $text);
        $text = preg_replace('/[òóọỏõôồốộổỗơờớợởỡ]/u', 'o', $text);
        $text = preg_replace('/[ùúụủũưừứựửữ]/u', 'u', $text);
        $text = preg_replace('/[ỳýỵỷỹ]/u', 'y', $text);
        $text = preg_replace('/đ/u', 'd', $text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '_', $text);
        return trim($text, '_');
    }
}
