<?php

namespace App\Services;

use Core\Database;
use App\Models\Notification;

class ApprovalService
{
    /**
     * Find matching approval flow based on module + conditions
     */
    public static function findApplicableFlow(string $module, array $entityData, int $tenantId): ?array
    {
        $flows = Database::fetchAll(
            "SELECT * FROM approval_flows
             WHERE module = ? AND tenant_id = ? AND is_active = 1
             ORDER BY id ASC",
            [$module, $tenantId]
        );

        foreach ($flows as $flow) {
            $conditions = json_decode($flow['conditions'] ?? '{}', true);

            if (empty($conditions)) {
                return $flow; // No conditions = always match
            }

            $match = true;
            foreach ($conditions as $field => $rule) {
                $entityValue = $entityData[$field] ?? null;

                if (is_array($rule)) {
                    $operator = $rule['operator'] ?? '=';
                    $compareValue = $rule['value'] ?? null;

                    switch ($operator) {
                        case '>':
                            if (!((float) $entityValue > (float) $compareValue)) $match = false;
                            break;
                        case '>=':
                            if (!((float) $entityValue >= (float) $compareValue)) $match = false;
                            break;
                        case '<':
                            if (!((float) $entityValue < (float) $compareValue)) $match = false;
                            break;
                        case 'in':
                            if (!in_array($entityValue, (array) $compareValue)) $match = false;
                            break;
                        default:
                            if ($entityValue != $compareValue) $match = false;
                    }
                } else {
                    if ($entityValue != $rule) $match = false;
                }

                if (!$match) break;
            }

            if ($match) return $flow;
        }

        return null;
    }

    /**
     * Create an approval request
     */
    public static function createRequest(int $flowId, string $entityType, int $entityId, int $requestedBy): int
    {
        $requestId = Database::insert('approval_requests', [
            'flow_id' => $flowId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'requested_by' => $requestedBy,
            'current_step' => 1,
            'status' => 'pending',
        ]);

        // Notify first step approver
        $firstStep = Database::fetch(
            "SELECT * FROM approval_flow_steps WHERE flow_id = ? AND step_order = 1",
            [$flowId]
        );

        if ($firstStep) {
            $requester = Database::fetch("SELECT name FROM users WHERE id = ?", [$requestedBy]);
            Notification::send(
                $firstStep['approver_id'],
                'approval_pending',
                'Yêu cầu phê duyệt mới',
                ($requester['name'] ?? 'Ai đó') . " đã gửi yêu cầu phê duyệt {$entityType} #{$entityId}.",
                'approvals/pending',
                'ri-checkbox-circle-line'
            );
        }

        return $requestId;
    }

    /**
     * Approve current step
     */
    public static function approve(int $requestId, int $userId, string $comment = null): bool
    {
        $request = Database::fetch("SELECT * FROM approval_requests WHERE id = ?", [$requestId]);
        if (!$request || $request['status'] !== 'pending') return false;

        $currentStep = Database::fetch(
            "SELECT * FROM approval_flow_steps WHERE flow_id = ? AND step_order = ?",
            [$request['flow_id'], $request['current_step']]
        );

        if (!$currentStep || $currentStep['approver_id'] != $userId) return false;

        // Log the action
        Database::insert('approval_actions', [
            'request_id' => $requestId,
            'step_order' => $request['current_step'],
            'user_id' => $userId,
            'action' => 'approved',
            'comment' => $comment,
        ]);

        // Check if there are more steps
        $nextStep = Database::fetch(
            "SELECT * FROM approval_flow_steps WHERE flow_id = ? AND step_order = ?",
            [$request['flow_id'], $request['current_step'] + 1]
        );

        if ($nextStep) {
            // Advance to next step
            Database::update('approval_requests', [
                'current_step' => $request['current_step'] + 1,
            ], 'id = ?', [$requestId]);

            // Notify next approver
            $approverName = Database::fetch("SELECT name FROM users WHERE id = ?", [$userId]);
            Notification::send(
                $nextStep['approver_id'],
                'approval_pending',
                'Yêu cầu phê duyệt - Bước tiếp theo',
                ($approverName['name'] ?? '') . " đã phê duyệt. Đến lượt bạn phê duyệt {$request['entity_type']} #{$request['entity_id']}.",
                'approvals/pending',
                'ri-checkbox-circle-line'
            );
        } else {
            // Last step → mark as approved
            Database::update('approval_requests', [
                'status' => 'approved',
                'completed_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$requestId]);

            // Notify requester
            Notification::send(
                $request['requested_by'],
                'approval_approved',
                'Yêu cầu đã được phê duyệt',
                "Yêu cầu phê duyệt {$request['entity_type']} #{$request['entity_id']} đã được chấp thuận.",
                'approvals/pending',
                'ri-check-double-line'
            );
        }

        return true;
    }

    /**
     * Reject request
     */
    public static function reject(int $requestId, int $userId, string $comment = null): bool
    {
        $request = Database::fetch("SELECT * FROM approval_requests WHERE id = ?", [$requestId]);
        if (!$request || $request['status'] !== 'pending') return false;

        $currentStep = Database::fetch(
            "SELECT * FROM approval_flow_steps WHERE flow_id = ? AND step_order = ?",
            [$request['flow_id'], $request['current_step']]
        );

        if (!$currentStep || $currentStep['approver_id'] != $userId) return false;

        // Log the action
        Database::insert('approval_actions', [
            'request_id' => $requestId,
            'step_order' => $request['current_step'],
            'user_id' => $userId,
            'action' => 'rejected',
            'comment' => $comment,
        ]);

        // Mark as rejected
        Database::update('approval_requests', [
            'status' => 'rejected',
            'completed_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$requestId]);

        // Notify requester
        $rejectorName = Database::fetch("SELECT name FROM users WHERE id = ?", [$userId]);
        Notification::send(
            $request['requested_by'],
            'approval_rejected',
            'Yêu cầu bị từ chối',
            ($rejectorName['name'] ?? '') . " đã từ chối yêu cầu phê duyệt {$request['entity_type']} #{$request['entity_id']}." . ($comment ? " Lý do: {$comment}" : ''),
            'approvals/pending',
            'ri-close-circle-line'
        );

        return true;
    }

    /**
     * Get current approval status with steps and actions history
     */
    public static function getStatus(string $entityType, int $entityId): ?array
    {
        $request = Database::fetch(
            "SELECT ar.*, af.name as flow_name
             FROM approval_requests ar
             JOIN approval_flows af ON ar.flow_id = af.id
             WHERE ar.entity_type = ? AND ar.entity_id = ?
             ORDER BY ar.id DESC LIMIT 1",
            [$entityType, $entityId]
        );

        if (!$request) return null;

        $steps = Database::fetchAll(
            "SELECT fs.*, u.name as approver_name
             FROM approval_flow_steps fs
             JOIN users u ON fs.approver_id = u.id
             WHERE fs.flow_id = ?
             ORDER BY fs.step_order ASC",
            [$request['flow_id']]
        );

        $actions = Database::fetchAll(
            "SELECT aa.*, u.name as user_name
             FROM approval_actions aa
             JOIN users u ON aa.user_id = u.id
             WHERE aa.request_id = ?
             ORDER BY aa.created_at ASC",
            [$request['id']]
        );

        // Map actions to steps
        $actionMap = [];
        foreach ($actions as $action) {
            $actionMap[$action['step_order']] = $action;
        }

        foreach ($steps as &$step) {
            $step['action'] = $actionMap[$step['step_order']] ?? null;
            if ($step['step_order'] < $request['current_step']) {
                $step['status'] = 'approved';
            } elseif ($step['step_order'] == $request['current_step'] && $request['status'] === 'rejected') {
                $step['status'] = 'rejected';
            } elseif ($step['step_order'] == $request['current_step']) {
                $step['status'] = 'current';
            } else {
                $step['status'] = 'pending';
            }
        }

        $request['steps'] = $steps;
        $request['actions'] = $actions;

        return $request;
    }

    /**
     * Get all pending approvals for a user
     */
    public static function getPendingForUser(int $userId): array
    {
        return Database::fetchAll(
            "SELECT ar.*, af.name as flow_name, af.module,
                    fs.step_order, u.name as requested_by_name
             FROM approval_requests ar
             JOIN approval_flows af ON ar.flow_id = af.id
             JOIN approval_flow_steps fs ON fs.flow_id = ar.flow_id AND fs.step_order = ar.current_step
             JOIN users u ON ar.requested_by = u.id
             WHERE ar.status = 'pending' AND fs.approver_id = ?
             ORDER BY ar.created_at DESC",
            [$userId]
        );
    }
}
