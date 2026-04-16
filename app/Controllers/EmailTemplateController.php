<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $search = $this->input('search');
        $category = $this->input('category');
        $page = max(1, (int) $this->input('page') ?: 1);
        $perPage = in_array((int)$this->input('per_page'), [10,20,50,100]) ? (int)$this->input('per_page') : 20;
        $offset = ($page - 1) * $perPage;

        $where = ["et.tenant_id = ?"];
        $params = [Database::tenantId()];

        if ($search) {
            $where[] = "(name LIKE ? OR subject LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($category) {
            $where[] = "category = ?";
            $params[] = $category;
        }

        $whereClause = implode(' AND ', $where);

        $total = Database::fetch(
            "SELECT COUNT(*) as count FROM email_templates et WHERE {$whereClause}",
            $params
        )['count'];

        $templates = Database::fetchAll(
            "SELECT et.*, u.name as creator_name
             FROM email_templates et
             LEFT JOIN users u ON et.created_by = u.id
             WHERE {$whereClause}
             ORDER BY et.updated_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $totalPages = ceil($total / $perPage);

        return $this->view('email-templates.index', [
            'templates' => [
                'items' => $templates,
                'total' => $total,
                'page' => $page,
                'total_pages' => $totalPages,
            ],
            'filters' => [
                'search' => $search,
                'category' => $category,
            ],
        ]);
    }

    public function create()
    {
        return $this->view('email-templates.create');
    }

    public function store()
    {
        if (!$this->isPost()) {
            return $this->redirect('email-templates');
        }

        $data = $this->allInput();
        $name = trim($data['name'] ?? '');

        if (empty($name)) {
            $this->setFlash('error', 'Tên template không được để trống.');
            return $this->back();
        }

        $id = Database::insert('email_templates', [
            'name' => $name,
            'subject' => trim($data['subject'] ?? ''),
            'category' => $data['category'] ?? 'general',
            'body' => $data['body'] ?? '',
            'created_by' => $this->userId(),
        ]);

        $this->setFlash('success', 'Template đã được tạo thành công.');
        return $this->redirect('email-templates/' . $id . '/edit');
    }

    public function edit($id)
    {
        $template = Database::fetch(
            "SELECT * FROM email_templates WHERE id = ? AND tenant_id = ?",
            [$id, Database::tenantId()]
        );

        if (!$template) {
            $this->setFlash('error', 'Template không tồn tại.');
            return $this->redirect('email-templates');
        }

        return $this->view('email-templates.edit', [
            'template' => $template,
        ]);
    }

    public function update($id)
    {
        if (!$this->isPost()) {
            return $this->redirect('email-templates/' . $id . '/edit');
        }

        $template = Database::fetch(
            "SELECT * FROM email_templates WHERE id = ? AND tenant_id = ?",
            [$id, Database::tenantId()]
        );

        if (!$template) {
            $this->setFlash('error', 'Template không tồn tại.');
            return $this->redirect('email-templates');
        }

        $data = $this->allInput();
        $name = trim($data['name'] ?? '');

        if (empty($name)) {
            $this->setFlash('error', 'Tên template không được để trống.');
            return $this->back();
        }

        Database::update('email_templates', [
            'name' => $name,
            'subject' => trim($data['subject'] ?? ''),
            'category' => $data['category'] ?? 'general',
            'body' => $data['body'] ?? '',
        ], 'id = ?', [$id]);

        $this->setFlash('success', 'Template đã được cập nhật.');
        return $this->redirect('email-templates/' . $id . '/edit');
    }

    public function delete($id)
    {
        if (!$this->isPost()) {
            return $this->redirect('email-templates');
        }

        $template = Database::fetch(
            "SELECT * FROM email_templates WHERE id = ? AND tenant_id = ?",
            [$id, Database::tenantId()]
        );

        if (!$template) {
            $this->setFlash('error', 'Template không tồn tại.');
            return $this->redirect('email-templates');
        }

        Database::delete('email_templates', 'id = ?', [$id]);

        $this->setFlash('success', 'Template đã được xóa.');
        return $this->redirect('email-templates');
    }

    public function preview($id)
    {
        $template = Database::fetch(
            "SELECT * FROM email_templates WHERE id = ? AND tenant_id = ?",
            [$id, Database::tenantId()]
        );

        if (!$template) {
            return $this->json(['error' => 'Template không tồn tại'], 404);
        }

        // Fill with sample data
        $sampleData = [
            '{{ten_kh}}' => 'Nguyễn Văn A',
            '{{email_kh}}' => 'nguyenvana@email.com',
            '{{sdt_kh}}' => '0901234567',
            '{{ten_cty}}' => 'Công ty TNHH ABC',
            '{{nguoi_phu_trach}}' => 'Trần Thị B',
            '{{email_npt}}' => 'tranthib@company.com',
            '{{sdt_npt}}' => '0987654321',
            '{{ten_sp}}' => 'Sản phẩm mẫu',
            '{{don_gia}}' => '1,500,000 VNĐ',
            '{{ma_don}}' => 'ORD-2026-001',
            '{{ngay}}' => date('d/m/Y'),
        ];

        $subject = str_replace(array_keys($sampleData), array_values($sampleData), $template['subject']);
        $body = str_replace(array_keys($sampleData), array_values($sampleData), $template['body']);

        $html = $this->buildEmailHtml($subject, $body);

        return $this->json([
            'subject' => $subject,
            'body' => $body,
            'html' => $html,
        ]);
    }

    public function send($id)
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }

        $template = Database::fetch(
            "SELECT * FROM email_templates WHERE id = ? AND tenant_id = ?",
            [$id, Database::tenantId()]
        );

        if (!$template) {
            return $this->json(['error' => 'Template không tồn tại'], 404);
        }

        $toEmail = trim($this->input('to_email', ''));
        $contactId = $this->input('contact_id');

        if (empty($toEmail) && empty($contactId)) {
            return $this->json(['error' => 'Vui lòng nhập email hoặc chọn khách hàng'], 422);
        }

        // Get merge data from contact
        $mergeData = [
            '{{ten_kh}}' => '',
            '{{email_kh}}' => $toEmail,
            '{{sdt_kh}}' => '',
            '{{ten_cty}}' => '',
            '{{nguoi_phu_trach}}' => '',
            '{{email_npt}}' => '',
            '{{sdt_npt}}' => '',
            '{{ten_sp}}' => '',
            '{{don_gia}}' => '',
            '{{ma_don}}' => '',
            '{{ngay}}' => date('d/m/Y'),
        ];

        if ($contactId) {
            $contact = Database::fetch(
                "SELECT c.*, u.name as owner_name, u.email as owner_email, u.phone as owner_phone,
                        comp.name as company_name
                 FROM contacts c
                 LEFT JOIN users u ON c.owner_id = u.id
                 LEFT JOIN companies comp ON c.company_id = comp.id
                 WHERE c.id = ?",
                [$contactId]
            );

            if ($contact) {
                $mergeData['{{ten_kh}}'] = trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? ''));
                $mergeData['{{email_kh}}'] = $contact['email'] ?? $toEmail;
                $mergeData['{{sdt_kh}}'] = $contact['phone'] ?? '';
                $mergeData['{{ten_cty}}'] = $contact['company_name'] ?? '';
                $mergeData['{{nguoi_phu_trach}}'] = $contact['owner_name'] ?? '';
                $mergeData['{{email_npt}}'] = $contact['owner_email'] ?? '';
                $mergeData['{{sdt_npt}}'] = $contact['owner_phone'] ?? '';

                if (empty($toEmail)) {
                    $toEmail = $contact['email'] ?? '';
                }
            }
        }

        if (empty($toEmail)) {
            return $this->json(['error' => 'Không tìm thấy email người nhận'], 422);
        }

        $subject = str_replace(array_keys($mergeData), array_values($mergeData), $template['subject']);
        $body = str_replace(array_keys($mergeData), array_values($mergeData), $template['body']);
        $html = $this->buildEmailHtml($subject, $body);

        // Send email
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: ToryCRM <noreply@torycrm.com>\r\n";

        $sent = @mail($toEmail, $subject, $html, $headers);

        // Increment use_count
        Database::query("UPDATE email_templates SET use_count = use_count + 1 WHERE id = ?", [$id]);

        if ($sent) {
            return $this->json(['success' => true, 'message' => 'Email đã được gửi thành công đến ' . $toEmail]);
        } else {
            return $this->json(['success' => true, 'message' => 'Đã xử lý gửi email đến ' . $toEmail . '. Vui lòng kiểm tra cấu hình mail server.']);
        }
    }

    private function buildEmailHtml(string $subject, string $body): string
    {
        return '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>' . htmlspecialchars($subject) . '</title></head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f5;padding:20px 0;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
<tr><td style="background:#405189;padding:24px 30px;"><h1 style="margin:0;color:#fff;font-size:20px;">ToryCRM</h1></td></tr>
<tr><td style="padding:30px;">' . $body . '</td></tr>
<tr><td style="background:#f8f9fa;padding:16px 30px;text-align:center;font-size:12px;color:#878a99;">
Email được gửi từ ToryCRM
</td></tr>
</table>
</td></tr>
</table>
</body></html>';
    }
}
