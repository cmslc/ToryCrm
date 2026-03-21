<?php

namespace App\Services;

use Core\Database;

class AutomationService
{
    /**
     * Trigger automation rules for a given event.
     */
    public static function trigger(string $event, array $data): void
    {
        try {
            $rules = Database::fetchAll('automation_rules', [
                'trigger_event' => $event,
                'is_active'     => 1,
            ]);

            if (!$rules) {
                return;
            }

            foreach ($rules as $rule) {
                if (!self::evaluateConditions($rule['conditions'] ?? null, $data)) {
                    continue;
                }

                $actions = json_decode($rule['actions'] ?? '[]', true);
                if (!is_array($actions)) {
                    continue;
                }

                foreach ($actions as $action) {
                    self::executeAction($action, $data, (int) $rule['id']);
                }

                Database::insert('automation_logs', [
                    'rule_id'    => $rule['id'],
                    'event'      => $event,
                    'data'       => json_encode($data),
                    'status'     => 'executed',
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (\Exception $e) {
            Database::insert('automation_logs', [
                'rule_id'    => 0,
                'event'      => $event,
                'data'       => json_encode($data),
                'status'     => 'failed',
                'error'      => $e->getMessage(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * Evaluate a JSON-encoded set of conditions against the given data.
     * Returns true if ALL conditions match (AND logic), or if no conditions are defined.
     */
    public static function evaluateConditions(?string $conditionsJson, array $data): bool
    {
        if (empty($conditionsJson)) {
            return true;
        }

        $conditions = json_decode($conditionsJson, true);

        if (!is_array($conditions) || empty($conditions)) {
            return true;
        }

        foreach ($conditions as $condition) {
            $field    = $condition['field'] ?? '';
            $operator = $condition['operator'] ?? 'eq';
            $value    = $condition['value'] ?? null;
            $actual   = $data[$field] ?? null;

            switch ($operator) {
                case 'eq':
                    if ($actual != $value) return false;
                    break;
                case 'neq':
                    if ($actual == $value) return false;
                    break;
                case 'contains':
                    if (stripos((string) $actual, (string) $value) === false) return false;
                    break;
                case 'gt':
                    if ($actual <= $value) return false;
                    break;
                case 'lt':
                    if ($actual >= $value) return false;
                    break;
                case 'in':
                    $list = is_array($value) ? $value : explode(',', (string) $value);
                    $list = array_map('trim', $list);
                    if (!in_array($actual, $list)) return false;
                    break;
                default:
                    return false;
            }
        }

        return true;
    }

    /**
     * Execute a single automation action.
     */
    public static function executeAction(array $action, array $data, int $ruleId): void
    {
        $type = $action['type'] ?? '';

        try {
            switch ($type) {
                case 'assign_to':
                    $entityType = $data['entity_type'] ?? '';
                    $entityId   = $data['entity_id'] ?? 0;
                    $assignTo   = $action['value'] ?? $action['user_id'] ?? null;

                    if ($entityType && $entityId && $assignTo) {
                        Database::update($entityType, [
                            'assigned_to' => $assignTo,
                        ], ['id' => $entityId]);
                    }
                    break;

                case 'send_email':
                    $to      = $data['email'] ?? ($action['to'] ?? '');
                    $subject = $action['subject'] ?? '';
                    $body    = $action['body'] ?? '';

                    // Replace placeholders in subject and body
                    foreach ($data as $key => $value) {
                        if (is_string($value)) {
                            $subject = str_replace('{{' . $key . '}}', $value, $subject);
                            $body    = str_replace('{{' . $key . '}}', $value, $body);
                        }
                    }

                    if (!empty($to)) {
                        MailService::send($to, $subject, $body);
                    }
                    break;

                case 'create_task':
                    Database::insert('tasks', [
                        'title'       => $action['title'] ?? 'Tác vụ tự động',
                        'description' => $action['description'] ?? '',
                        'assigned_to' => $action['assigned_to'] ?? ($data['assigned_to'] ?? null),
                        'due_date'    => $action['due_date'] ?? date('Y-m-d', strtotime('+1 day')),
                        'status'      => 'pending',
                        'entity_type' => $data['entity_type'] ?? null,
                        'entity_id'   => $data['entity_id'] ?? null,
                        'created_by'  => $data['user_id'] ?? 0,
                        'created_at'  => date('Y-m-d H:i:s'),
                    ]);
                    break;

                case 'create_notification':
                    Database::insert('notifications', [
                        'user_id'     => $action['user_id'] ?? ($data['assigned_to'] ?? ($data['user_id'] ?? 0)),
                        'title'       => $action['title'] ?? 'Thông báo tự động',
                        'message'     => $action['message'] ?? '',
                        'type'        => $action['notification_type'] ?? 'automation',
                        'entity_type' => $data['entity_type'] ?? null,
                        'entity_id'   => $data['entity_id'] ?? null,
                        'is_read'     => 0,
                        'created_at'  => date('Y-m-d H:i:s'),
                    ]);
                    break;

                case 'update_field':
                    $entityType = $data['entity_type'] ?? '';
                    $entityId   = $data['entity_id'] ?? 0;
                    $field      = $action['field'] ?? '';
                    $value      = $action['value'] ?? '';

                    if ($entityType && $entityId && $field) {
                        Database::update($entityType, [
                            $field => $value,
                        ], ['id' => $entityId]);
                    }
                    break;
            }
        } catch (\Exception $e) {
            Database::insert('automation_logs', [
                'rule_id'    => $ruleId,
                'event'      => $type,
                'data'       => json_encode($data),
                'status'     => 'action_failed',
                'error'      => $e->getMessage(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
