<?php

namespace App\Services;

use Core\Database;

/**
 * Auto activity logger - can be called from controllers or hooked into events
 */
class ActivityLogger
{
    private static array $moduleLabels = [
        'contacts' => 'Khách hàng',
        'companies' => 'Doanh nghiệp',
        'deals' => 'Cơ hội',
        'tasks' => 'Công việc',
        'orders' => 'Đơn hàng',
        'products' => 'Sản phẩm',
        'tickets' => 'Ticket',
        'campaigns' => 'Chiến dịch',
        'fund_transactions' => 'Phiếu thu/chi',
        'purchase_orders' => 'Đơn mua',
        'calendar_events' => 'Lịch hẹn',
        'users' => 'Người dùng',
    ];

    /**
     * Log a create action
     */
    public static function created(string $module, int $entityId, string $entityName, ?int $contactId = null, ?int $dealId = null, ?int $companyId = null): void
    {
        $label = self::$moduleLabels[$module] ?? $module;
        self::log('system', "Tạo mới {$label}: {$entityName}", null, $contactId, $dealId, $companyId);
        AuditService::log('create', $module, $entityId);
    }

    /**
     * Log an update action with field diff
     */
    public static function updated(string $module, int $entityId, string $entityName, array $oldData, array $newData, array $trackFields = [], ?int $contactId = null, ?int $dealId = null): void
    {
        $label = self::$moduleLabels[$module] ?? $module;

        if (empty($trackFields)) {
            $trackFields = array_keys($newData);
        }

        $changes = AuditService::diff($oldData, $newData, $trackFields);

        if (empty($changes)) return;

        $changedFields = array_keys($changes);
        $description = "Cập nhật " . implode(', ', array_slice($changedFields, 0, 3));
        if (count($changedFields) > 3) {
            $description .= ' và ' . (count($changedFields) - 3) . ' trường khác';
        }

        self::log('system', "Cập nhật {$label}: {$entityName}", $description, $contactId, $dealId);
        AuditService::log('update', $module, $entityId, $oldData, $newData);
    }

    /**
     * Log a delete action
     */
    public static function deleted(string $module, int $entityId, string $entityName, ?int $contactId = null): void
    {
        $label = self::$moduleLabels[$module] ?? $module;
        self::log('system', "Xóa {$label}: {$entityName}", null, $contactId);
        AuditService::log('delete', $module, $entityId);
    }

    /**
     * Log a status change
     */
    public static function statusChanged(string $module, int $entityId, string $entityName, string $oldStatus, string $newStatus, ?int $contactId = null, ?int $dealId = null): void
    {
        $label = self::$moduleLabels[$module] ?? $module;
        self::log(
            'system',
            "{$label} {$entityName}: {$oldStatus} → {$newStatus}",
            null,
            $contactId,
            $dealId
        );
        AuditService::log('update', $module, $entityId,
            ['status' => $oldStatus],
            ['status' => $newStatus]
        );
    }

    /**
     * Internal log method
     */
    private static function log(string $type, string $title, ?string $description = null, ?int $contactId = null, ?int $dealId = null, ?int $companyId = null): void
    {
        try {
            Database::insert('activities', [
                'type' => $type,
                'title' => $title,
                'description' => $description,
                'contact_id' => $contactId,
                'deal_id' => $dealId,
                'company_id' => $companyId,
                'user_id' => $_SESSION['user']['id'] ?? null,
            ]);
        } catch (\Exception $e) {
            // Silently fail - don't break the main operation
        }
    }
}
