<?php

namespace App\Services;

use Core\Database;

class Validator
{
    private array $data;
    private array $rules = [];
    private array $errorMessages = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Add validation rules for a field.
     * $rules is pipe-separated: 'required|email|min:6|max:255|unique:users,email'
     */
    public function rule(string $field, string $rules): self
    {
        $this->rules[$field] = $rules;
        return $this;
    }

    /**
     * Run all validation rules. Returns true if all pass.
     */
    public function validate(): bool
    {
        $this->errorMessages = [];

        foreach ($this->rules as $field => $rulesString) {
            $rules = explode('|', $rulesString);

            foreach ($rules as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramString] = explode(':', $rule, 2);
                    $params = explode(',', $paramString);
                }

                $value = $this->data[$field] ?? null;

                $this->applyRule($field, $rule, $params, $value);
            }
        }

        return empty($this->errorMessages);
    }

    /**
     * Get all error messages grouped by field.
     */
    public function errors(): array
    {
        return $this->errorMessages;
    }

    /**
     * Get the first error message, or null if none.
     */
    public function firstError(): ?string
    {
        foreach ($this->errorMessages as $fieldErrors) {
            if (!empty($fieldErrors)) {
                return $fieldErrors[0];
            }
        }
        return null;
    }

    /**
     * Apply a single rule to a field value.
     */
    private function applyRule(string $field, string $rule, array $params, mixed $value): void
    {
        switch ($rule) {
            case 'required':
                if ($value === null || $value === '' || $value === []) {
                    $this->addError($field, "Trường {$field} là bắt buộc");
                }
                break;

            case 'email':
                if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "Trường {$field} phải là email hợp lệ");
                }
                break;

            case 'min':
                $min = (int) ($params[0] ?? 0);
                if ($value !== null && $value !== '' && mb_strlen((string) $value) < $min) {
                    $this->addError($field, "Trường {$field} phải có ít nhất {$min} ký tự");
                }
                break;

            case 'max':
                $max = (int) ($params[0] ?? 0);
                if ($value !== null && $value !== '' && mb_strlen((string) $value) > $max) {
                    $this->addError($field, "Trường {$field} không được vượt quá {$max} ký tự");
                }
                break;

            case 'numeric':
                if ($value !== null && $value !== '' && !is_numeric($value)) {
                    $this->addError($field, "Trường {$field} phải là số");
                }
                break;

            case 'unique':
                $table  = $params[0] ?? '';
                $column = $params[1] ?? $field;
                $ignoreId = $params[2] ?? null;

                // Whitelist table/column names to prevent SQL injection
                $allowedTables = ['users','contacts','companies','products','orders','deals','tasks','tickets','campaigns'];
                if ($value !== null && $value !== '' && $table && in_array($table, $allowedTables)) {
                    $safeColumn = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
                    $sql = "SELECT COUNT(*) as cnt FROM `{$table}` WHERE `{$safeColumn}` = ?";
                    $bindings = [$value];

                    if ($ignoreId) {
                        $sql .= " AND id != ?";
                        $bindings[] = (int) $ignoreId;
                    }

                    $result = Database::fetch($sql, $bindings);
                    if (($result['cnt'] ?? 0) > 0) {
                        $this->addError($field, "Trường {$field} đã tồn tại trong hệ thống");
                    }
                }
                break;

            case 'in':
                if ($value !== null && $value !== '' && !in_array($value, $params, true)) {
                    $allowed = implode(', ', $params);
                    $this->addError($field, "Trường {$field} phải là một trong các giá trị: {$allowed}");
                }
                break;

            case 'date':
                if ($value !== null && $value !== '' && strtotime($value) === false) {
                    $this->addError($field, "Trường {$field} phải là ngày hợp lệ");
                }
                break;

            case 'confirmed':
                $confirmValue = $this->data["{$field}_confirmation"] ?? null;
                if ($value !== $confirmValue) {
                    $this->addError($field, "Trường {$field} xác nhận không khớp");
                }
                break;
        }
    }

    /**
     * Add an error message for a field.
     */
    private function addError(string $field, string $message): void
    {
        $this->errorMessages[$field][] = $message;
    }
}
