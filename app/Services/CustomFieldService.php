<?php

namespace App\Services;

use Core\Database;

class CustomFieldService
{
    /**
     * Get all field definitions for a module
     */
    public static function getFields(string $module, int $tenantId): array
    {
        return Database::fetchAll(
            "SELECT * FROM custom_field_definitions
             WHERE module = ? AND tenant_id = ?
             ORDER BY sort_order ASC, id ASC",
            [$module, $tenantId]
        );
    }

    /**
     * Get field values for an entity, JOINed with definitions
     */
    public static function getValues(string $entityType, int $entityId): array
    {
        $rows = Database::fetchAll(
            "SELECT v.*, d.field_key, d.field_label, d.field_type, d.options, d.placeholder, d.is_required, d.is_filterable, d.show_in_list
             FROM custom_field_values v
             JOIN custom_field_definitions d ON v.field_id = d.id
             WHERE v.entity_type = ? AND v.entity_id = ?",
            [$entityType, $entityId]
        );

        $values = [];
        foreach ($rows as $row) {
            $values[$row['field_key']] = $row['field_value'];
        }

        return $values;
    }

    /**
     * Save/update custom field values (REPLACE INTO)
     */
    public static function saveValues(string $entityType, int $entityId, array $values): void
    {
        $tenantId = Database::tenantId();

        foreach ($values as $key => $value) {
            // Look up field definition by key
            $field = Database::fetch(
                "SELECT id FROM custom_field_definitions WHERE field_key = ? AND tenant_id = ?",
                [$key, $tenantId]
            );

            if (!$field) continue;

            // Check if value already exists
            $existing = Database::fetch(
                "SELECT id FROM custom_field_values WHERE field_id = ? AND entity_type = ? AND entity_id = ?",
                [$field['id'], $entityType, $entityId]
            );

            if ($existing) {
                Database::update('custom_field_values', [
                    'field_value' => is_array($value) ? json_encode($value) : $value,
                ], 'id = ?', [$existing['id']]);
            } else {
                Database::query(
                    "INSERT INTO custom_field_values (field_id, entity_type, entity_id, field_value, tenant_id) VALUES (?, ?, ?, ?, ?)",
                    [$field['id'], $entityType, $entityId, is_array($value) ? json_encode($value) : $value, $tenantId]
                );
            }
        }
    }

    /**
     * Return HTML string of form fields based on definitions
     */
    public static function renderFormFields(string $module, int $tenantId, array $values = []): string
    {
        $fields = self::getFields($module, $tenantId);
        if (empty($fields)) return '';

        $html = '';
        foreach ($fields as $field) {
            $key = htmlspecialchars($field['field_key']);
            $label = htmlspecialchars($field['field_label']);
            $type = $field['field_type'];
            $required = $field['is_required'] ? ' required' : '';
            $requiredStar = $field['is_required'] ? ' <span class="text-danger">*</span>' : '';
            $placeholder = htmlspecialchars($field['placeholder'] ?? '');
            $value = htmlspecialchars($values[$field['field_key']] ?? ($field['default_value'] ?? ''));
            $name = "custom_fields[{$key}]";

            $html .= '<div class="mb-3">';
            $html .= "<label class=\"form-label\">{$label}{$requiredStar}</label>";

            switch ($type) {
                case 'text':
                case 'email':
                case 'phone':
                case 'url':
                case 'color':
                    $inputType = ($type === 'phone') ? 'tel' : $type;
                    $html .= "<input type=\"{$inputType}\" class=\"form-control\" name=\"{$name}\" value=\"{$value}\" placeholder=\"{$placeholder}\"{$required}>";
                    break;

                case 'number':
                case 'currency':
                    $html .= "<input type=\"number\" class=\"form-control\" name=\"{$name}\" value=\"{$value}\" placeholder=\"{$placeholder}\" step=\"any\"{$required}>";
                    break;

                case 'textarea':
                    $html .= "<textarea class=\"form-control\" name=\"{$name}\" rows=\"3\" placeholder=\"{$placeholder}\"{$required}>{$value}</textarea>";
                    break;

                case 'date':
                    $html .= "<input type=\"date\" class=\"form-control\" name=\"{$name}\" value=\"{$value}\"{$required}>";
                    break;

                case 'datetime':
                    $html .= "<input type=\"datetime-local\" class=\"form-control\" name=\"{$name}\" value=\"{$value}\"{$required}>";
                    break;

                case 'select':
                    $options = array_filter(array_map('trim', explode("\n", $field['options'] ?? '')));
                    $html .= "<select class=\"form-select\" name=\"{$name}\"{$required}>";
                    $html .= '<option value="">-- Chọn --</option>';
                    foreach ($options as $opt) {
                        $optVal = htmlspecialchars($opt);
                        $selected = ($opt === ($values[$field['field_key']] ?? '')) ? ' selected' : '';
                        $html .= "<option value=\"{$optVal}\"{$selected}>{$optVal}</option>";
                    }
                    $html .= '</select>';
                    break;

                case 'multi_select':
                    $options = array_filter(array_map('trim', explode("\n", $field['options'] ?? '')));
                    $selectedValues = json_decode($values[$field['field_key']] ?? '[]', true) ?: [];
                    $html .= "<select class=\"form-select\" name=\"{$name}[]\" multiple{$required}>";
                    foreach ($options as $opt) {
                        $optVal = htmlspecialchars($opt);
                        $selected = in_array($opt, $selectedValues) ? ' selected' : '';
                        $html .= "<option value=\"{$optVal}\"{$selected}>{$optVal}</option>";
                    }
                    $html .= '</select>';
                    break;

                case 'checkbox':
                    $checked = !empty($values[$field['field_key']]) ? ' checked' : '';
                    $html .= '<div class="form-check form-switch">';
                    $html .= "<input type=\"hidden\" name=\"{$name}\" value=\"0\">";
                    $html .= "<input type=\"checkbox\" class=\"form-check-input\" name=\"{$name}\" value=\"1\" id=\"cf_{$key}\"{$checked}>";
                    $html .= "<label class=\"form-check-label\" for=\"cf_{$key}\">{$label}</label>";
                    $html .= '</div>';
                    break;

                case 'radio':
                    $options = array_filter(array_map('trim', explode("\n", $field['options'] ?? '')));
                    foreach ($options as $i => $opt) {
                        $optVal = htmlspecialchars($opt);
                        $checked = ($opt === ($values[$field['field_key']] ?? '')) ? ' checked' : '';
                        $html .= '<div class="form-check">';
                        $html .= "<input type=\"radio\" class=\"form-check-input\" name=\"{$name}\" value=\"{$optVal}\" id=\"cf_{$key}_{$i}\"{$checked}{$required}>";
                        $html .= "<label class=\"form-check-label\" for=\"cf_{$key}_{$i}\">{$optVal}</label>";
                        $html .= '</div>';
                    }
                    break;

                case 'file':
                    $html .= "<input type=\"file\" class=\"form-control\" name=\"{$name}\"{$required}>";
                    if (!empty($value)) {
                        $html .= "<small class=\"text-muted\">File hiện tại: {$value}</small>";
                    }
                    break;

                default:
                    $html .= "<input type=\"text\" class=\"form-control\" name=\"{$name}\" value=\"{$value}\" placeholder=\"{$placeholder}\"{$required}>";
            }

            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Return array of visible-in-list field definitions
     */
    public static function renderListColumns(string $module, int $tenantId): array
    {
        return Database::fetchAll(
            "SELECT * FROM custom_field_definitions
             WHERE module = ? AND tenant_id = ? AND show_in_list = 1
             ORDER BY sort_order ASC, id ASC",
            [$module, $tenantId]
        );
    }
}
