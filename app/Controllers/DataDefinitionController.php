<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class DataDefinitionController extends Controller
{
    private array $modules = [
        'contacts' => ['label' => 'Khách hàng', 'icon' => 'ri-contacts-line', 'desc' => 'Định nghĩa dữ liệu khách hàng'],
        'companies' => ['label' => 'Doanh nghiệp', 'icon' => 'ri-building-line', 'desc' => 'Định nghĩa dữ liệu doanh nghiệp'],
        'deals' => ['label' => 'Cơ hội', 'icon' => 'ri-hand-coin-line', 'desc' => 'Định nghĩa dữ liệu cơ hội'],
        'tasks' => ['label' => 'Công việc', 'icon' => 'ri-task-line', 'desc' => 'Định nghĩa dữ liệu công việc'],
        'products' => ['label' => 'Sản phẩm', 'icon' => 'ri-shopping-bag-line', 'desc' => 'Định nghĩa dữ liệu sản phẩm'],
        'orders' => ['label' => 'Đơn hàng', 'icon' => 'ri-file-list-3-line', 'desc' => 'Định nghĩa dữ liệu đơn hàng'],
        'order_items' => ['label' => 'Chi tiết đơn hàng', 'icon' => 'ri-list-check', 'desc' => 'Định nghĩa dữ liệu chi tiết đơn hàng'],
        'contracts' => ['label' => 'Hợp đồng', 'icon' => 'ri-file-text-line', 'desc' => 'Định nghĩa dữ liệu hợp đồng'],
        'tickets' => ['label' => 'Hỗ trợ', 'icon' => 'ri-customer-service-line', 'desc' => 'Định nghĩa dữ liệu ticket'],
        'users' => ['label' => 'Người dùng', 'icon' => 'ri-user-line', 'desc' => 'Định nghĩa dữ liệu người dùng'],
    ];

    private array $fieldLabels = [
        // Contacts
        'first_name' => 'Họ', 'last_name' => 'Tên', 'full_name' => 'Họ tên',
        'email' => 'Email', 'phone' => 'Điện thoại', 'mobile' => 'Di động',
        'account_code' => 'Mã KH', 'position' => 'Chức vụ', 'gender' => 'Giới tính',
        'date_of_birth' => 'Ngày sinh', 'address' => 'Địa chỉ', 'city' => 'Thành phố',
        'province' => 'Tỉnh/TP', 'district' => 'Quận/Huyện', 'ward' => 'Phường/Xã',
        'country' => 'Quốc gia', 'description' => 'Mô tả', 'status' => 'Trạng thái',
        'customer_group' => 'Nhóm KH', 'referrer_code' => 'Người giới thiệu',
        'is_private' => 'Riêng tư', 'score' => 'Điểm', 'avatar' => 'Ảnh đại diện',
        'tax_code' => 'Mã số thuế', 'website' => 'Website', 'fax' => 'Fax',
        'latitude' => 'Kinh độ', 'longitude' => 'Vĩ độ',
        'company_id' => 'Công ty', 'source_id' => 'Nguồn KH', 'owner_id' => 'Người phụ trách',
        'created_by' => 'Người tạo', 'created_at' => 'Ngày tạo', 'updated_at' => 'Ngày cập nhật',
        'last_activity_at' => 'Liên hệ lần cuối', 'is_deleted' => 'Đã xóa', 'deleted_at' => 'Ngày xóa',

        // Companies
        'name' => 'Tên', 'website' => 'Website', 'tax_code' => 'Mã số thuế',
        'industry' => 'Ngành nghề', 'company_size' => 'Quy mô', 'logo' => 'Logo',

        // Deals
        'title' => 'Tiêu đề', 'value' => 'Giá trị', 'stage_id' => 'Giai đoạn',
        'expected_close_date' => 'Ngày dự kiến đóng', 'actual_close_date' => 'Ngày đóng thực tế',
        'priority' => 'Ưu tiên', 'loss_reason_category' => 'Lý do mất',
        'contact_id' => 'Khách hàng',

        // Tasks
        'assigned_to' => 'Người thực hiện', 'due_date' => 'Hạn', 'completed_at' => 'Ngày hoàn thành',
        'deal_id' => 'Cơ hội',

        // Products
        'sku' => 'Mã SP', 'category_id' => 'Danh mục', 'type' => 'Loại',
        'unit' => 'Đơn vị', 'price' => 'Giá bán', 'cost_price' => 'Giá vốn',
        'tax_rate' => 'Thuế (%)', 'stock_quantity' => 'Tồn kho', 'min_stock' => 'Tồn tối thiểu',
        'image' => 'Hình ảnh', 'is_active' => 'Kích hoạt',

        // Orders
        'order_number' => 'Mã đơn hàng', 'subtotal' => 'Tạm tính',
        'tax_amount' => 'Thuế', 'discount_amount' => 'Chiết khấu', 'discount_type' => 'Loại CK',
        'transport_amount' => 'Phí vận chuyển', 'installation_amount' => 'Phí lắp đặt',
        'total' => 'Tổng tiền', 'currency' => 'Tiền tệ', 'notes' => 'Ghi chú',
        'order_terms' => 'Điều khoản', 'payment_status' => 'Trạng thái TT',
        'payment_method' => 'Hình thức TT', 'lading_code' => 'Mã vận đơn',
        'paid_amount' => 'Đã thanh toán', 'issued_date' => 'Ngày phát hành',
        'approved_by' => 'Người duyệt', 'approved_at' => 'Ngày duyệt',
        'contract_id' => 'Hợp đồng', 'order_source_id' => 'Nguồn đơn hàng',
        'shipping_address' => 'Địa chỉ giao hàng', 'shipping_contact' => 'Người nhận',
        'shipping_phone' => 'SĐT người nhận', 'shipping_province' => 'Tỉnh/TP giao',
        'shipping_district' => 'Quận/huyện giao', 'lading_status' => 'TT vận đơn',
        'warehouse_id' => 'Kho xuất', 'payment_date' => 'Ngày thanh toán',
        'commission_amount' => 'Hoa hồng',

        // Order items
        'product_id' => 'Sản phẩm', 'order_id' => 'Đơn hàng',
        'product_name' => 'Tên SP', 'quantity' => 'Số lượng',
        'unit_price' => 'Đơn giá', 'discount' => 'Chiết khấu',
        'tax' => 'Thuế', 'line_total' => 'Thành tiền',

        // Contracts
        'start_date' => 'Ngày bắt đầu', 'end_date' => 'Ngày kết thúc',
        'recurring_value' => 'Giá trị định kỳ', 'recurring_cycle' => 'Chu kỳ',
        'auto_renew' => 'Tự gia hạn', 'terms' => 'Điều khoản',

        // Users
        'role' => 'Vai trò', 'department' => 'Phòng ban',
        'department_id' => 'Phòng ban', 'position_id' => 'Chức vụ',
        'password' => 'Mật khẩu', 'last_login' => 'Đăng nhập cuối',
        'tenant_id' => 'Tenant',

        // Common
        'id' => 'ID', 'sort_order' => 'Thứ tự',
        // Companies extra
        'deal_code' => 'Mã cơ hội', 'task_code' => 'Mã công việc',
        'ticket_code' => 'Mã ticket', 'contract_number' => 'Mã hợp đồng',
        'content' => 'Nội dung', 'contact_phone' => 'SĐT liên hệ',
        'contact_email' => 'Email liên hệ', 'campaign_id' => 'Chiến dịch',
        'start_date' => 'Ngày bắt đầu', 'end_date' => 'Ngày kết thúc',
    ];

    // Module-specific label overrides (when same column means different things)
    private array $moduleLabels = [
        'contacts' => ['title' => 'Danh xưng'],
        'deals' => ['title' => 'Tiêu đề'],
        'tasks' => ['title' => 'Tiêu đề'],
        'tickets' => ['title' => 'Tiêu đề'],
        'contracts' => ['title' => 'Tiêu đề'],
    ];

    public function index()
    {
        $this->authorize('settings', 'manage');
        return $this->view('settings.data-definition', ['modules' => $this->modules]);
    }

    public function show($module)
    {
        $this->authorize('settings', 'manage');

        if (!isset($this->modules[$module])) {
            $this->setFlash('error', 'Module không tồn tại.');
            return $this->redirect('settings/data-definition');
        }

        $moduleInfo = $this->modules[$module];

        // Get actual DB columns
        $columns = Database::fetchAll("SHOW FULL COLUMNS FROM `{$module}`");

        // Get custom fields for this module
        $customFields = [];
        try {
            $customFields = Database::fetchAll(
                "SELECT * FROM custom_fields WHERE module = ? AND tenant_id = ? ORDER BY sort_order",
                [$module, Database::tenantId()]
            );
        } catch (\Exception $e) {}

        // Load label overrides
        $overrides = [];
        try {
            $ovRows = Database::fetchAll(
                "SELECT field_name, label, is_required, check_duplicate FROM field_label_overrides WHERE tenant_id = ? AND table_name = ?",
                [Database::tenantId(), $module]
            );
            foreach ($ovRows as $ov) $overrides[$ov['field_name']] = $ov;
        } catch (\Exception $e) {}

        // Build field list
        $fields = [];
        $systemFields = [
            // Common
            'id', 'tenant_id', 'is_deleted', 'deleted_at', 'created_at', 'updated_at',
            'created_by', 'owner_id', 'last_activity_at', 'sort_order',
            'avatar', 'logo', 'image', 'featured_image', 'currency',
            // Contacts
            'portal_token', 'portal_password', 'portal_active',
            'total_revenue', 'bonus_points', 'relation_id', 'industry_id', 'referrer_type',
            // Orders
            'approved_by', 'approved_at', 'cancelled_at', 'cancelled_reason',
            'is_auto_approve', 'tracking_url',
            // Deals
            'opportunity_status_id', 'close_reason', 'competitor', 'probability',
            'receipt_date', 'lost_reason',
            // Tasks
            'cancelled_at', 'recurring_id', 'task_type_id', 'project_id', 'parent_id',
            'progress', 'estimated_hours', 'color', 'is_important',
            // Products
            'barcode', 'origin_id', 'manufacturer_id', 'featured_image',
            'price_wholesale', 'price_online', 'discount_percent', 'saleoff_price', 'weight',
            'short_description',
            // Contracts
            'parent_contract_id', 'signed_date',
            // Tickets
            'sla_policy_id', 'first_response_at', 'sla_first_response_due',
            'sla_resolution_due', 'sla_breached', 'status_id', 'expected_at',
            'resolved_at', 'closed_at',
            // Users
            'password', 'two_factor_secret', 'two_factor_enabled',
            'login_attempts', 'locked_until', 'password_changed_at', 'last_login', 'theme',
            'department', 'base_salary', 'leave_balance',
            'allowance_lunch', 'allowance_transport', 'allowance_phone', 'allowance_other',
            'dependents',
        ];

        foreach ($columns as $col) {
            $isSystem = in_array($col['Field'], $systemFields);
            $isNullable = $col['Null'] === 'YES';
            $ov = $overrides[$col['Field']] ?? null;
            $fields[] = [
                'name' => $col['Field'],
                'label' => $ov ? $ov['label'] : ($this->moduleLabels[$module][$col['Field']] ?? $this->fieldLabels[$col['Field']] ?? $col['Field']),
                'type' => $this->parseColumnType($col['Type']),
                'raw_type' => $col['Type'],
                'nullable' => $isNullable,
                'required' => $ov ? (bool)$ov['is_required'] : (!$isNullable && $col['Default'] === null && $col['Field'] !== 'id'),
                'check_duplicate' => $ov ? (bool)($ov['check_duplicate'] ?? 0) : false,
                'default' => $col['Default'],
                'is_system' => $isSystem,
                'is_custom' => false,
                'source' => 'database',
            ];
        }

        // Add custom fields
        foreach ($customFields as $cf) {
            $fields[] = [
                'name' => 'cf_' . $cf['field_name'],
                'label' => $cf['field_label'],
                'type' => $cf['field_type'],
                'raw_type' => $cf['field_type'],
                'nullable' => !$cf['is_required'],
                'required' => (bool)$cf['is_required'],
                'default' => $cf['default_value'],
                'is_system' => false,
                'is_custom' => true,
                'source' => 'custom_field',
                'custom_field_id' => $cf['id'],
            ];
        }

        return $this->view('settings.data-definition-show', [
            'module' => $module,
            'moduleInfo' => $moduleInfo,
            'fields' => $fields,
            'modules' => $this->modules,
        ]);
    }

    public function updateField($module)
    {
        if (!$this->isPost()) return $this->redirect('settings/data-definition/' . $module);
        $this->authorize('settings', 'manage');

        $fieldName = $this->input('field_name');
        $label = trim($this->input('label') ?? '');
        $required = $this->input('required') ? 1 : 0;
        $checkDuplicate = $this->input('check_duplicate') ? 1 : 0;
        $isCustom = $this->input('is_custom');
        $cfId = $this->input('custom_field_id');

        if ($isCustom && $cfId) {
            // Update custom field
            Database::update('custom_fields', [
                'field_label' => $label,
                'is_required' => $required,
            ], 'id = ? AND tenant_id = ?', [$cfId, Database::tenantId()]);
        }

        // Save label override + check_duplicate
        Database::query(
            "INSERT INTO field_label_overrides (tenant_id, table_name, field_name, label, is_required, check_duplicate)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE label = VALUES(label), is_required = VALUES(is_required), check_duplicate = VALUES(check_duplicate)",
            [Database::tenantId(), $module, $fieldName, $label, $required, $checkDuplicate]
        );

        $this->setFlash('success', 'Đã cập nhật thuộc tính.');
        return $this->redirect('settings/data-definition/' . $module);
    }

    public function deleteField($module)
    {
        if (!$this->isPost()) return $this->redirect('settings/data-definition/' . $module);
        $this->authorize('settings', 'manage');

        $fieldId = (int)$this->input('field_id');
        if ($fieldId) {
            Database::query("DELETE FROM custom_fields WHERE id = ? AND tenant_id = ?", [$fieldId, Database::tenantId()]);
        }

        $this->setFlash('success', 'Đã xóa trường.');
        return $this->redirect('settings/data-definition/' . $module);
    }

    private function parseColumnType(string $rawType): string
    {
        if (str_starts_with($rawType, 'int') || str_starts_with($rawType, 'bigint') || str_starts_with($rawType, 'tinyint')) return 'Số nguyên';
        if (str_starts_with($rawType, 'decimal') || str_starts_with($rawType, 'float') || str_starts_with($rawType, 'double')) return 'Số thập phân';
        if (str_starts_with($rawType, 'varchar')) return 'Chuỗi';
        if ($rawType === 'text' || $rawType === 'longtext' || $rawType === 'mediumtext') return 'Văn bản';
        if (str_starts_with($rawType, 'enum')) return 'Danh sách';
        if ($rawType === 'date') return 'Ngày';
        if ($rawType === 'datetime' || str_starts_with($rawType, 'timestamp')) return 'Ngày giờ';
        if ($rawType === 'json') return 'JSON';
        return $rawType;
    }
}
