<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class LeadFormController extends Controller
{
    private function checkPlugin(): bool
    {
        try {
            $installed = \App\Services\PluginManager::getInstalled($this->tenantId());
            foreach ($installed as $p) {
                if ($p['slug'] === 'lead-forms' && $p['tenant_active']) return true;
            }
        } catch (\Exception $e) {}
        $this->setFlash('error', 'Plugin Lead Forms chưa được cài đặt.');
        $this->redirect('plugins/marketplace');
        return false;
    }

    // ---- List Forms ----
    public function index()
    {
        if (!$this->checkPlugin()) return;
        $tid = Database::tenantId();
        $forms = Database::fetchAll("SELECT * FROM lead_forms WHERE tenant_id = ? ORDER BY created_at DESC", [$tid]);
        return $this->view('lead-forms.index', compact('forms'));
    }

    // ---- Create Form ----
    public function create()
    {
        if (!$this->checkPlugin()) return;
        return $this->view('lead-forms.create');
    }

    public function store()
    {
        if (!$this->isPost()) return $this->redirect('lead-forms');
        $tid = Database::tenantId();

        $name = trim($this->input('name') ?? '');
        if (empty($name)) { $this->setFlash('error', 'Nhập tên form.'); return $this->redirect('lead-forms/create'); }

        $slug = preg_replace('/[^a-z0-9-]/', '', strtolower(str_replace(' ', '-', $name))) ?: 'form-' . time();

        // Build fields from form
        $fieldNames = $this->input('field_name') ?: [];
        $fieldLabels = $this->input('field_label') ?: [];
        $fieldTypes = $this->input('field_type') ?: [];
        $fieldRequired = $this->input('field_required') ?: [];

        $fields = [];
        foreach ($fieldNames as $i => $fn) {
            if (empty($fn)) continue;
            $fields[] = [
                'name' => $fn,
                'label' => $fieldLabels[$i] ?? $fn,
                'type' => $fieldTypes[$i] ?? 'text',
                'required' => in_array($i, $fieldRequired),
            ];
        }

        if (empty($fields)) {
            $fields = [
                ['name' => 'name', 'label' => 'Họ tên', 'type' => 'text', 'required' => true],
                ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                ['name' => 'phone', 'label' => 'Số điện thoại', 'type' => 'tel', 'required' => false],
                ['name' => 'message', 'label' => 'Nội dung', 'type' => 'textarea', 'required' => false],
            ];
        }

        $settings = [
            'thank_you_message' => trim($this->input('thank_you_message') ?? '') ?: 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất.',
            'button_text' => trim($this->input('button_text') ?? '') ?: 'Gửi',
            'button_color' => trim($this->input('button_color') ?? '') ?: '#405189',
            'auto_assign' => (int)($this->input('auto_assign') ?? 0),
            'form_style' => $this->input('form_style') ?: 'classic',
        ];

        Database::insert('lead_forms', [
            'tenant_id' => $tid,
            'name' => $name,
            'slug' => $slug,
            'description' => trim($this->input('description') ?? '') ?: null,
            'fields' => json_encode($fields),
            'settings' => json_encode($settings),
            'is_active' => 1,
            'created_by' => $this->userId(),
        ]);

        $this->setFlash('success', 'Đã tạo form "' . $name . '".');
        return $this->redirect('lead-forms');
    }

    // ---- Edit ----
    public function edit($id)
    {
        if (!$this->checkPlugin()) return;
        $form = Database::fetch("SELECT * FROM lead_forms WHERE id = ? AND tenant_id = ?", [(int)$id, Database::tenantId()]);
        if (!$form) { $this->setFlash('error', 'Form không tồn tại.'); return $this->redirect('lead-forms'); }
        $form['fields'] = json_decode($form['fields'], true) ?: [];
        $form['settings'] = json_decode($form['settings'], true) ?: [];
        return $this->view('lead-forms.edit', compact('form'));
    }

    public function update($id)
    {
        if (!$this->isPost()) return $this->redirect('lead-forms');
        $tid = Database::tenantId();

        $name = trim($this->input('name') ?? '');
        $fieldNames = $this->input('field_name') ?: [];
        $fieldLabels = $this->input('field_label') ?: [];
        $fieldTypes = $this->input('field_type') ?: [];
        $fieldRequired = $this->input('field_required') ?: [];

        $fields = [];
        foreach ($fieldNames as $i => $fn) {
            if (empty($fn)) continue;
            $fields[] = [
                'name' => $fn,
                'label' => $fieldLabels[$i] ?? $fn,
                'type' => $fieldTypes[$i] ?? 'text',
                'required' => in_array((string)$i, $fieldRequired),
            ];
        }

        $settings = [
            'thank_you_message' => trim($this->input('thank_you_message') ?? '') ?: 'Cảm ơn bạn đã liên hệ!',
            'button_text' => trim($this->input('button_text') ?? '') ?: 'Gửi',
            'button_color' => trim($this->input('button_color') ?? '') ?: '#405189',
            'auto_assign' => (int)($this->input('auto_assign') ?? 0),
            'form_style' => $this->input('form_style') ?: 'classic',
        ];

        Database::update('lead_forms', [
            'name' => $name,
            'description' => trim($this->input('description') ?? '') ?: null,
            'fields' => json_encode($fields),
            'settings' => json_encode($settings),
            'is_active' => $this->input('is_active') ? 1 : 0,
        ], 'id = ? AND tenant_id = ?', [(int)$id, $tid]);

        $this->setFlash('success', 'Đã cập nhật form.');
        return $this->redirect('lead-forms');
    }

    // ---- Delete ----
    public function delete($id)
    {
        if (!$this->isPost()) return $this->redirect('lead-forms');
        Database::query("DELETE FROM lead_forms WHERE id = ? AND tenant_id = ?", [(int)$id, Database::tenantId()]);
        $this->setFlash('success', 'Đã xóa form.');
        return $this->redirect('lead-forms');
    }

    // ---- Embed Code ----
    public function embed($id)
    {
        if (!$this->checkPlugin()) return;
        $form = Database::fetch("SELECT * FROM lead_forms WHERE id = ? AND tenant_id = ?", [(int)$id, Database::tenantId()]);
        if (!$form) { $this->setFlash('error', 'Form không tồn tại.'); return $this->redirect('lead-forms'); }
        return $this->view('lead-forms.embed', compact('form'));
    }

    // ---- Submissions ----
    public function submissions($id)
    {
        if (!$this->checkPlugin()) return;
        $tid = Database::tenantId();
        $form = Database::fetch("SELECT * FROM lead_forms WHERE id = ? AND tenant_id = ?", [(int)$id, $tid]);
        if (!$form) { $this->setFlash('error', 'Form không tồn tại.'); return $this->redirect('lead-forms'); }

        $page = max(1, (int)($this->input('page') ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $total = Database::fetch("SELECT COUNT(*) as cnt FROM lead_form_submissions WHERE form_id = ? AND tenant_id = ?", [(int)$id, $tid])['cnt'];
        $submissions = Database::fetchAll(
            "SELECT lfs.*, c.first_name, c.last_name FROM lead_form_submissions lfs LEFT JOIN contacts c ON lfs.contact_id = c.id WHERE lfs.form_id = ? AND lfs.tenant_id = ? ORDER BY lfs.created_at DESC LIMIT $limit OFFSET $offset",
            [(int)$id, $tid]
        );
        $totalPages = ceil($total / $limit);
        $form['fields'] = json_decode($form['fields'], true) ?: [];

        return $this->view('lead-forms.submissions', compact('form', 'submissions', 'page', 'totalPages', 'total'));
    }

    // ---- PUBLIC: Render Form ----
    public function publicForm($slug)
    {
        $form = Database::fetch("SELECT * FROM lead_forms WHERE slug = ? AND is_active = 1", [$slug]);
        if (!$form) { http_response_code(404); echo 'Form not found'; exit; }

        $form['fields'] = json_decode($form['fields'], true) ?: [];
        $form['settings'] = json_decode($form['settings'], true) ?: [];

        // Render standalone HTML form (no layout)
        header('Content-Type: text/html; charset=UTF-8');
        header('Access-Control-Allow-Origin: *');
        include BASE_PATH . '/resources/views/lead-forms/public-form.php';
        exit;
    }

    // ---- PUBLIC: Submit Form (API) ----
    public function publicSubmit($slug)
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Headers: Content-Type');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

        // Simple rate limit: max 5 submissions per IP per minute
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        try {
            $recent = (int)(Database::fetch(
                "SELECT COUNT(*) as c FROM lead_form_submissions WHERE ip_address = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)",
                [$ip]
            )['c'] ?? 0);
            if ($recent >= 5) {
                http_response_code(429);
                echo json_encode(['success' => false, 'error' => 'Quá nhiều yêu cầu. Vui lòng thử lại sau.']);
                exit;
            }
        } catch (\Exception $e) {}

        $form = Database::fetch("SELECT * FROM lead_forms WHERE slug = ? AND is_active = 1", [$slug]);
        if (!$form) { echo json_encode(['success' => false, 'error' => 'Form not found']); exit; }

        $fields = json_decode($form['fields'], true) ?: [];
        $settings = json_decode($form['settings'], true) ?: [];
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        // Validate required + sanitize each field (XSS prevention for public endpoint)
        $data = [];
        foreach ($fields as $f) {
            $val = trim($input[$f['name']] ?? '');
            if ($f['required'] && empty($val)) {
                echo json_encode(['success' => false, 'error' => $f['label'] . ' là bắt buộc']); exit;
            }
            // Strip HTML tags to prevent stored XSS when displayed in admin dashboard
            $data[$f['name']] = strip_tags($val);
        }

        // Validate auto_assign owner belongs to this tenant (don't trust form settings blindly)
        $autoAssign = null;
        if (!empty($settings['auto_assign'])) {
            $ownerOk = Database::fetch("SELECT id FROM users WHERE id = ? AND tenant_id = ?", [$settings['auto_assign'], $form['tenant_id']]);
            if ($ownerOk) $autoAssign = (int)$settings['auto_assign'];
        }

        // Auto-create contact
        $contactId = null;
        $name = $data['name'] ?? $data['ho_ten'] ?? $data['full_name'] ?? '';
        $email = $data['email'] ?? '';
        $phone = $data['phone'] ?? $data['sdt'] ?? $data['dien_thoai'] ?? '';

        if ($name || $email) {
            $nameParts = explode(' ', $name, 2);
            $contactId = Database::insert('contacts', [
                'tenant_id' => $form['tenant_id'],
                'first_name' => $nameParts[0] ?? $name,
                'last_name' => $nameParts[1] ?? '',
                'email' => $email ?: null,
                'phone' => $phone ?: null,
                'status' => 'new',
                'description' => 'Từ form: ' . $form['name'],
                'owner_id' => $autoAssign,
                'created_by' => $autoAssign,
            ]);
        }

        // Save submission
        Database::insert('lead_form_submissions', [
            'tenant_id' => $form['tenant_id'],
            'form_id' => $form['id'],
            'data' => json_encode($data),
            'contact_id' => $contactId,
            'source_url' => $input['_source_url'] ?? $_SERVER['HTTP_REFERER'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);

        // Update submission count
        Database::query("UPDATE lead_forms SET submission_count = submission_count + 1 WHERE id = ?", [$form['id']]);

        echo json_encode([
            'success' => true,
            'message' => $settings['thank_you_message'] ?? 'Cảm ơn bạn!',
            'contact_id' => $contactId,
        ]);
        exit;
    }
}
