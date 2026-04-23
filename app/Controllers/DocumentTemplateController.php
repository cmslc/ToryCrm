<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class DocumentTemplateController extends Controller
{
    public function index()
    {
        $this->authorize('settings', 'manage');

        $type = $this->input('type') ?: '';
        $search = trim($this->input('search') ?? '');
        $tid = Database::tenantId();

        $where = ["t.tenant_id = ?"];
        $params = [$tid];

        if ($type) {
            $where[] = "t.type = ?";
            $params[] = $type;
        }
        if ($search) {
            $where[] = "(t.name LIKE ? OR t.description LIKE ?)";
            $s = "%{$search}%";
            $params = array_merge($params, [$s, $s]);
        }

        $whereClause = implode(' AND ', $where);

        $templates = Database::fetchAll(
            "SELECT t.id, t.name, t.type, t.description, t.is_default, t.is_active, t.created_by, t.created_at, u.name as creator_name
             FROM document_templates t
             LEFT JOIN users u ON t.created_by = u.id
             WHERE {$whereClause}
             ORDER BY t.type, t.is_default DESC, t.name",
            $params
        );

        return $this->view('document-templates.index', [
            'templates' => $templates,
            'filters' => ['type' => $type, 'search' => $search],
        ]);
    }

    public function create()
    {
        $this->authorize('settings', 'manage');

        $type = $this->input('type') ?: 'quotation';

        return $this->view('document-templates.create', [
            'type' => $type,
            'variables' => $this->getVariables($type),
        ]);
    }

    public function store()
    {
        if (!$this->isPost()) return $this->redirect('settings/document-templates');
        $this->authorize('settings', 'manage');

        $data = $this->allInput();
        $type = $data['type'] ?? 'quotation';

        if (empty(trim($data['name'] ?? ''))) {
            $this->setFlash('error', 'Vui lòng nhập tên mẫu.');
            return $this->back();
        }

        $tid = Database::tenantId();

        // If set as default, unset other defaults of same type
        if (!empty($data['is_default'])) {
            Database::query("UPDATE document_templates SET is_default = 0 WHERE tenant_id = ? AND type = ?", [$tid, $type]);
        }

        $id = Database::insert('document_templates', [
            'tenant_id' => $tid,
            'type' => $type,
            'name' => trim($data['name']),
            'description' => trim($data['description'] ?? '') ?: null,
            'content' => $data['content'] ?? '',
            'is_default' => !empty($data['is_default']) ? 1 : 0,
            'is_active' => 1,
            'created_by' => $this->userId(),
        ]);

        $this->setFlash('success', 'Đã tạo mẫu thành công.');
        return $this->redirect('settings/document-templates/' . $id . '/edit');
    }

    public function edit($id)
    {
        $this->authorize('settings', 'manage');

        $template = Database::fetch("SELECT * FROM document_templates WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$template) {
            $this->setFlash('error', 'Mẫu không tồn tại.');
            return $this->redirect('settings/document-templates');
        }

        return $this->view('document-templates.edit', [
            'template' => $template,
            'variables' => $this->getVariables($template['type']),
        ]);
    }

    public function update($id)
    {
        if (!$this->isPost()) return $this->redirect('settings/document-templates');
        $this->authorize('settings', 'manage');

        $tid = Database::tenantId();
        $template = Database::fetch("SELECT * FROM document_templates WHERE id = ? AND tenant_id = ?", [$id, $tid]);
        if (!$template) {
            $this->setFlash('error', 'Mẫu không tồn tại.');
            return $this->redirect('settings/document-templates');
        }

        $data = $this->allInput();

        if (!empty($data['is_default'])) {
            Database::query("UPDATE document_templates SET is_default = 0 WHERE tenant_id = ? AND type = ?", [$tid, $template['type']]);
        }

        Database::update('document_templates', [
            'name' => trim($data['name'] ?? $template['name']),
            'description' => trim($data['description'] ?? '') ?: null,
            'content' => $data['content'] ?? '',
            'is_default' => !empty($data['is_default']) ? 1 : 0,
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : $template['is_active'],
        ], 'id = ?', [$id]);

        $this->setFlash('success', 'Đã cập nhật mẫu.');
        return $this->redirect('settings/document-templates/' . $id . '/edit');
    }

    public function delete($id)
    {
        if (!$this->isPost()) return $this->redirect('settings/document-templates');
        $this->authorize('settings', 'manage');

        Database::delete('document_templates', 'id = ? AND tenant_id = ?', [$id, Database::tenantId()]);
        $this->setFlash('success', 'Đã xóa mẫu.');
        return $this->redirect('settings/document-templates');
    }

    public function toggle($id)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);
        $this->authorize('settings', 'manage');

        $template = Database::fetch("SELECT id, is_active FROM document_templates WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$template) return $this->json(['error' => 'Not found'], 404);

        $newStatus = $template['is_active'] ? 0 : 1;
        Database::update('document_templates', ['is_active' => $newStatus], 'id = ?', [$id]);
        return $this->json(['success' => true, 'is_active' => $newStatus]);
    }

    private function getVariables(string $type): array
    {
        $common = [
            '{{company_name}}' => 'Tên công ty (Bên A)',
            '{{company_address}}' => 'Địa chỉ công ty',
            '{{company_phone}}' => 'Điện thoại công ty',
            '{{company_tax_code}}' => 'Mã số thuế công ty',
            '{{company_representative}}' => 'Người đại diện',
            '{{company_position}}' => 'Chức vụ đại diện',
            '{{company_bank_account}}' => 'Tài khoản ngân hàng',
            '{{company_bank_name}}' => 'Ngân hàng',
            '{{customer_name}}' => 'Tên khách hàng (Bên B)',
            '{{customer_address}}' => 'Địa chỉ khách hàng',
            '{{customer_phone}}' => 'Điện thoại khách hàng',
            '{{customer_tax_code}}' => 'Mã số thuế KH',
            '{{customer_representative}}' => 'Người đại diện KH',
            '{{customer_position}}' => 'Chức vụ đại diện KH',
            '{{items_table}}' => 'Bảng sản phẩm',
            '{{subtotal}}' => 'Tổng tiền hàng',
            '{{discount}}' => 'Chiết khấu',
            '{{vat}}' => 'Thuế VAT',
            '{{total}}' => 'Tổng thanh toán',
            '{{today}}' => 'Ngày hiện tại',
            '{{owner_name}}' => 'Người phụ trách',
        ];

        if ($type === 'quotation') {
            return array_merge($common, [
                '{{quote_number}}' => 'Số báo giá',
                '{{valid_until}}' => 'Hiệu lực đến',
                '{{notes}}' => 'Ghi chú',
                '{{terms}}' => 'Điều khoản',
            ]);
        }

        if ($type === 'order') {
            return array_merge($common, [
                '{{order_number}}' => 'Số đơn hàng',
                '{{issued_date}}' => 'Ngày đặt hàng',
                '{{due_date}}' => 'Hạn thanh toán',
                '{{lading_code}}' => 'Mã vận đơn',
                '{{payment_method}}' => 'Phương thức thanh toán',
                '{{shipping_address}}' => 'Địa chỉ giao hàng',
                '{{shipping_contact}}' => 'Tên người nhận',
                '{{shipping_phone}}' => 'SĐT người nhận',
                '{{delivery_type}}' => 'Hình thức giao (Tự giao/Đối tác)',
                '{{delivery_date}}' => 'Ngày giao hàng',
                '{{delivery_partner}}' => 'Đối tác giao hàng',
                '{{delivery_notes}}' => 'Điều khoản giao hàng',
                '{{transport_amount}}' => 'Phí vận chuyển',
                '{{installation_amount}}' => 'Phí lắp đặt',
                '{{paid_amount}}' => 'Đã thanh toán',
                '{{notes}}' => 'Ghi chú',
                '{{terms}}' => 'Điều khoản',
            ]);
        }

        return array_merge($common, [
            '{{contract_number}}' => 'Số hợp đồng',
            '{{contract_title}}' => 'Tên hợp đồng',
            '{{contract_type}}' => 'Kiểu hợp đồng',
            '{{start_date}}' => 'Ngày có hiệu lực',
            '{{end_date}}' => 'Ngày hết hiệu lực',
            '{{payment_method}}' => 'Hình thức thanh toán',
            '{{installation_address}}' => 'Địa chỉ lắp đặt',
            '{{notes}}' => 'Ghi chú',
            '{{terms}}' => 'Điều khoản',
        ]);
    }
}
