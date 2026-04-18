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
        'quotations' => ['label' => 'Báo giá', 'icon' => 'ri-file-list-2-line', 'desc' => 'Định nghĩa dữ liệu báo giá'],
        'contracts' => ['label' => 'Hợp đồng', 'icon' => 'ri-file-text-line', 'desc' => 'Định nghĩa dữ liệu hợp đồng'],
        'tickets' => ['label' => 'Hỗ trợ', 'icon' => 'ri-customer-service-line', 'desc' => 'Định nghĩa dữ liệu ticket'],
        'users' => ['label' => 'Người dùng', 'icon' => 'ri-user-line', 'desc' => 'Định nghĩa dữ liệu người dùng'],
    ];

    private array $fieldLabels = [
        // Contacts
        'first_name' => 'Họ (hệ thống)', 'last_name' => 'Tên (hệ thống)', 'full_name' => 'Họ tên',
        'email' => 'Email', 'phone' => 'Điện thoại', 'mobile' => 'Di động',
        'account_code' => 'Mã KH', 'position' => 'Chức vụ', 'gender' => 'Giới tính',
        'date_of_birth' => 'Ngày sinh', 'address' => 'Địa chỉ', 'city' => 'Thành phố',
        'province' => 'Tỉnh/TP', 'district' => 'Quận/Huyện', 'ward' => 'Phường/Xã',
        'country' => 'Quốc gia', 'description' => 'Mô tả', 'status' => 'Mối quan hệ',
        'customer_group' => 'Nhóm KH', 'referrer_code' => 'Người giới thiệu',
        'is_private' => 'Riêng tư', 'avatar' => 'Ảnh đại diện',
        'tax_code' => 'Mã số thuế', 'website' => 'Website', 'fax' => 'Fax',
        'latitude' => 'Kinh độ', 'longitude' => 'Vĩ độ',
        'company_name' => 'Tên công ty', 'company_phone' => 'ĐT công ty', 'company_email' => 'Email công ty',
        'industry' => 'Ngành KD', 'company_size' => 'Quy mô',
        'company_id' => 'Công ty', 'source_id' => 'Nguồn KH', 'owner_id' => 'Người phụ trách',
        'created_by' => 'Người tạo', 'created_at' => 'Ngày tạo', 'updated_at' => 'Ngày cập nhật',
        'last_activity_at' => 'Liên hệ lần cuối', 'is_deleted' => 'Đã xóa', 'deleted_at' => 'Ngày xóa',
        'relation_id' => 'Mối quan hệ (ID)', 'industry_id' => 'Ngành KD (ID)',
        'referrer_type' => 'Loại người giới thiệu', 'total_revenue' => 'Tổng doanh thu',
        'portal_token' => 'Token cổng KH', 'portal_password' => 'Mật khẩu cổng KH',
        'portal_active' => 'Kích hoạt cổng KH', 'tenant_id' => 'Mã doanh nghiệp',
        'id' => 'ID', 'title' => 'Danh xưng',

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

        // Quotations
        'quote_number' => 'Mã BG', 'valid_until' => 'Hiệu lực đến',
        'view_count' => 'Lượt xem', 'last_viewed_at' => 'Xem lần cuối',
        'client_note' => 'Ghi chú KH', 'accepted_at' => 'Ngày chấp nhận',
        'rejected_at' => 'Ngày từ chối', 'rejection_reason' => 'Lý do từ chối',
        'portal_token' => 'Token portal',
        'tax_rate' => 'Thuế VAT (%)', 'tax_amount' => 'Tiền thuế',
        'discount_percent' => 'Chiết khấu (%)', 'discount_after_tax' => 'CK sau thuế',
        'shipping_fee' => 'Phí vận chuyển', 'shipping_percent' => 'Phí VC (%)',
        'shipping_after_tax' => 'VC sau thuế', 'shipping_note' => 'Ghi chú VC',
        'installation_fee' => 'Phí lắp đặt', 'installation_percent' => 'Phí lắp đặt (%)',

        // Orders
        'order_number' => 'Mã ĐH', 'order_source_id' => 'Nguồn đơn hàng',
        'discount_type' => 'Loại CK', 'transport_amount' => 'Phí vận chuyển',
        'installation_amount' => 'Phí lắp đặt', 'order_terms' => 'Điều khoản ĐH',
        'payment_status' => 'TT thanh toán', 'payment_method' => 'Hình thức TT',
        'paid_amount' => 'Đã thanh toán', 'payment_date' => 'Ngày thanh toán',
        'lading_code' => 'Mã vận đơn', 'lading_status' => 'TT vận đơn',
        'shipping_address' => 'ĐC giao hàng', 'shipping_contact' => 'Người nhận',
        'shipping_phone' => 'SĐT nhận', 'shipping_province' => 'Tỉnh/TP giao',
        'shipping_district' => 'Q/H giao', 'warehouse_id' => 'Kho',
        'tracking_url' => 'Link tracking', 'commission_amount' => 'Hoa hồng',
        'issued_date' => 'Ngày phát hành', 'due_date' => 'Hạn thanh toán',
        'cost_price' => 'Giá vốn',

        // Contracts
        'contract_number' => 'Số HĐ', 'contract_code' => 'Mã HĐ', 'signed_date' => 'Ngày ký',
        'actual_value' => 'Giá trị thực', 'executed_amount' => 'Đã thực hiện',
        'paid_amount' => 'Đã thanh toán', 'installation_address' => 'ĐC lắp đặt',
        'contact_name' => 'Người liên hệ', 'auto_renew' => 'Tự động gia hạn',
        'recurring_value' => 'Giá trị định kỳ', 'recurring_cycle' => 'Chu kỳ',
        'parent_contract_id' => 'HĐ gốc', 'related_contract_id' => 'HĐ liên quan',
        'payment_method' => 'Hình thức TT', 'usage_type' => 'HĐ sử dụng',
        'created_date' => 'Ngày tạo HĐ', 'actual_start_date' => 'TG thực tế bắt đầu',
        'actual_end_date' => 'TG thực tế kết thúc', 'location' => 'Địa điểm',
        'project' => 'Dự án', 'quote_id' => 'Báo giá',
        'party_a_company_id' => 'Công ty bên bán', 'party_a_name' => 'Tên bên bán',
        'party_a_address' => 'ĐC bên bán', 'party_a_phone' => 'ĐT bên bán',
        'party_a_fax' => 'Fax bên bán', 'party_a_representative' => 'Đại diện bên bán',
        'party_a_position' => 'CV bên bán', 'party_a_bank_account' => 'TK bên bán',
        'party_a_bank_name' => 'NH bên bán', 'party_a_tax_code' => 'MST bên bán',
        'party_b_name' => 'Tên bên mua', 'party_b_address' => 'ĐC bên mua',
        'party_b_phone' => 'ĐT bên mua', 'party_b_fax' => 'Fax bên mua',
        'party_b_representative' => 'Đại diện bên mua', 'party_b_position' => 'CV bên mua',
        'party_b_bank_account' => 'TK bên mua', 'party_b_bank_name' => 'NH bên mua',
        'party_b_tax_code' => 'MST bên mua',
        'discount_percent' => 'CK (%)', 'discount_after_tax' => 'CK sau thuế',
        'shipping_fee_percent' => 'Phí VC (%)', 'shipping_after_tax' => 'VC sau thuế',
        'apply_vat' => 'Áp dụng VAT', 'vat_percent' => 'VAT (%)', 'vat_amount' => 'Tiền VAT',
        'installation_fee_percent' => 'Phí lắp đặt (%)',
        'auto_create_order' => 'Tự tạo ĐH', 'auto_notify_expiry' => 'Tự báo hết hạn',
        'auto_send_sms' => 'Tự gửi SMS', 'auto_send_email' => 'Tự gửi email',

        // Deals extra
        'opportunity_status_id' => 'Trạng thái cơ hội', 'receipt_date' => 'Ngày nhận',
        'lost_reason' => 'Lý do mất', 'probability' => 'Xác suất (%)',
        'close_reason' => 'Lý do đóng', 'competitor' => 'Đối thủ',

        // Tasks extra
        'progress' => 'Tiến độ (%)', 'estimated_hours' => 'Giờ dự kiến',
        'color' => 'Màu', 'is_important' => 'Quan trọng',
        'cancelled_at' => 'Ngày hủy', 'project_id' => 'Dự án',
        'parent_id' => 'Công việc cha', 'recurring_id' => 'Lặp lại',
        'task_type_id' => 'Loại công việc',

        // Products extra
        'barcode' => 'Mã vạch', 'origin_id' => 'Xuất xứ',
        'manufacturer_id' => 'Nhà sản xuất', 'price_wholesale' => 'Giá sỉ',
        'price_online' => 'Giá online', 'saleoff_price' => 'Giá khuyến mãi',
        'weight' => 'Trọng lượng', 'short_description' => 'Mô tả ngắn',
        'featured_image' => 'Ảnh nổi bật',

        // Orders extra
        'cancelled_reason' => 'Lý do hủy', 'is_auto_approve' => 'Tự duyệt',

        // Order items extra
        'note' => 'Ghi chú',

        // Tickets extra
        'status_id' => 'Trạng thái', 'expected_at' => 'Dự kiến xử lý',
        'resolved_at' => 'Ngày giải quyết', 'closed_at' => 'Ngày đóng',
        'sla_policy_id' => 'Chính sách SLA', 'first_response_at' => 'Phản hồi đầu',
        'sla_first_response_due' => 'Hạn phản hồi SLA', 'sla_resolution_due' => 'Hạn xử lý SLA',
        'sla_breached' => 'Vi phạm SLA',

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

        // Load show_in_list overrides
        $showInList = [];
        try {
            $slRows = Database::fetchAll(
                "SELECT field_name, show_in_list FROM field_label_overrides WHERE tenant_id = ? AND table_name = ? AND show_in_list IS NOT NULL",
                [Database::tenantId(), $module]
            );
            foreach ($slRows as $sl) $showInList[$sl['field_name']] = (bool)$sl['show_in_list'];
        } catch (\Exception $e) {}

        // Build field list
        $fields = [];
        $systemFields = [
            // Common
            'id', 'tenant_id', 'is_deleted', 'deleted_at', 'created_at', 'updated_at',
            'created_by', 'owner_id', 'last_activity_at', 'sort_order',
            'avatar', 'logo', 'image', 'featured_image', 'currency',
            // Contacts (first_name/last_name merged into full_name)
            'first_name', 'last_name',
            'portal_token', 'portal_password', 'portal_active',
            'total_revenue', 'bonus_points', 'score', 'relation_id', 'industry_id', 'referrer_type',
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
                'show_in_list' => $showInList[$col['Field']] ?? ($isSystem ? false : true),
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

    public function toggleShowInList($module)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Invalid'], 400);
        $this->authorize('settings', 'manage');

        $fieldName = $this->input('field_name');
        $show = $this->input('show') ? 1 : 0;

        Database::query(
            "INSERT INTO field_label_overrides (tenant_id, table_name, field_name, label, show_in_list)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE show_in_list = VALUES(show_in_list)",
            [Database::tenantId(), $module, $fieldName, $fieldName, $show]
        );

        return $this->json(['success' => true]);
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
