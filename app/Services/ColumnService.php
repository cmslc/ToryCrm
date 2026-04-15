<?php

namespace App\Services;

use Core\Database;

class ColumnService
{
    private static array $defaultLabels = [
        'contacts' => [
            'account_code' => 'Mã KH',
            'first_name' => 'Họ',
            'last_name' => 'Tên',
            'title' => 'Danh xưng',
            'email' => 'Email',
            'phone' => 'Điện thoại',
            'mobile' => 'Di động',
            'fax' => 'Fax',
            'position' => 'Chức vụ',
            'gender' => 'Giới tính',
            'date_of_birth' => 'Ngày sinh',
            'tax_code' => 'Mã số thuế',
            'website' => 'Website',
            'address' => 'Địa chỉ',
            'province' => 'Tỉnh/TP',
            'district' => 'Quận/Huyện',
            'ward' => 'Phường/Xã',
            'city' => 'Thành phố',
            'country' => 'Quốc gia',
            'description' => 'Mô tả',
            'status' => 'Trạng thái',
            'customer_group' => 'Nhóm KH',
            'referrer_code' => 'Người giới thiệu',
            'is_private' => 'Riêng tư',
            'company_id' => 'Công ty',
            'source_id' => 'Nguồn KH',
            'owner_id' => 'Phụ trách',
            'latitude' => 'Kinh độ',
            'longitude' => 'Vĩ độ',
        ],
        'orders' => [
            'order_number' => 'Mã ĐH',
            'type' => 'Loại',
            'status' => 'Trạng thái',
            'contact_id' => 'Khách hàng',
            'company_id' => 'Công ty',
            'deal_id' => 'Cơ hội',
            'contract_id' => 'Hợp đồng',
            'subtotal' => 'Tạm tính',
            'tax_amount' => 'Thuế',
            'discount_amount' => 'Chiết khấu',
            'transport_amount' => 'Phí vận chuyển',
            'installation_amount' => 'Phí lắp đặt',
            'total' => 'Tổng tiền',
            'notes' => 'Ghi chú',
            'order_terms' => 'Điều khoản',
            'payment_status' => 'Thanh toán',
            'payment_method' => 'Hình thức TT',
            'paid_amount' => 'Đã thanh toán',
            'payment_date' => 'Ngày thanh toán',
            'lading_code' => 'Mã vận đơn',
            'lading_status' => 'TT vận đơn',
            'shipping_address' => 'ĐC giao hàng',
            'shipping_contact' => 'Người nhận',
            'shipping_phone' => 'SĐT nhận',
            'shipping_province' => 'Tỉnh/TP giao',
            'shipping_district' => 'Q/H giao',
            'commission_amount' => 'Hoa hồng',
            'owner_id' => 'Phụ trách',
            'due_date' => 'Hạn thanh toán',
            'issued_date' => 'Ngày phát hành',
        ],
        'companies' => [
            'name' => 'Tên DN',
            'email' => 'Email',
            'phone' => 'Điện thoại',
            'fax' => 'Fax',
            'website' => 'Website',
            'tax_code' => 'MST',
            'industry' => 'Ngành nghề',
            'company_size' => 'Quy mô',
            'address' => 'Địa chỉ',
            'city' => 'Thành phố',
            'province' => 'Tỉnh/TP',
            'district' => 'Quận/Huyện',
            'country' => 'Quốc gia',
            'description' => 'Mô tả',
            'owner_id' => 'Phụ trách',
        ],
    ];

    private static array $systemFields = [
        'id', 'tenant_id', 'is_deleted', 'deleted_at', 'created_at', 'updated_at',
        'created_by', 'avatar', 'logo', 'image', 'currency', 'discount_type',
        'portal_token', 'portal_password', 'portal_active',
        'total_revenue', 'bonus_points', 'score', 'relation_id', 'industry_id', 'referrer_type',
        'approved_by', 'approved_at', 'cancelled_at', 'cancelled_reason', 'is_auto_approve',
        'tracking_url', 'sort_order', 'campaign_id', 'order_source_id', 'warehouse_id',
        'last_activity_at', 'password',
    ];

    /**
     * Get displayable columns for a module.
     * Returns: [['key' => 'col-xxx', 'field' => 'xxx', 'label' => 'Xxx'], ...]
     */
    public static function getColumns(string $module): array
    {
        $defaults = self::$defaultLabels[$module] ?? [];

        // Load overrides from DB
        try {
            $overrides = Database::fetchAll(
                "SELECT field_name, label FROM field_label_overrides WHERE tenant_id = ? AND table_name = ?",
                [Database::tenantId(), $module]
            );
            foreach ($overrides as $ov) {
                if (!empty($ov['label'])) {
                    $defaults[$ov['field_name']] = $ov['label'];
                }
            }
        } catch (\Exception $e) {}

        // Load custom fields
        try {
            $customFields = Database::fetchAll(
                "SELECT field_name, field_label FROM custom_fields WHERE module = ? AND tenant_id = ?",
                [$module, Database::tenantId()]
            );
            foreach ($customFields as $cf) {
                $defaults['cf_' . $cf['field_name']] = $cf['field_label'];
            }
        } catch (\Exception $e) {}

        // Get ALL columns from DB table (not just defaults)
        try {
            $dbColumns = Database::fetchAll("SHOW COLUMNS FROM `{$module}`");
            foreach ($dbColumns as $col) {
                $field = $col['Field'];
                if (!isset($defaults[$field]) && !in_array($field, ['id', 'tenant_id'])) {
                    $defaults[$field] = $field; // raw name as label if not defined
                }
            }
        } catch (\Exception $e) {}

        $columns = [];
        foreach ($defaults as $field => $label) {
            $columns[] = [
                'key' => 'col-' . str_replace('_', '', $field),
                'field' => $field,
                'label' => $label,
                'is_system' => in_array($field, self::$systemFields),
            ];
        }

        return $columns;
    }
}
