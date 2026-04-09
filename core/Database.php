<?php

namespace Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    // Tables that have soft delete (is_deleted column) - MUST match actual DB schema
    private static array $softDeleteTables = [
        'contacts', 'companies', 'tasks', 'products', 'orders',
    ];

    // Tables that are tenant-scoped
    private static array $tenantTables = [
        'contacts', 'companies', 'deals', 'tasks', 'products', 'orders',
        'tickets', 'campaigns', 'fund_transactions', 'calendar_events',
        'activities', 'purchase_orders', 'call_logs', 'notifications', 'users',
        'conversations', 'canned_responses', 'internal_chats', 'email_templates',
    ];

    public static function init(array $config): void
    {
        if (self::$connection !== null) return;

        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['name'],
                $config['charset'] ?? 'utf8mb4'
            );

            self::$connection = new PDO($dsn, $config['user'], $config['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            if ($_ENV['APP_DEBUG'] ?? false) {
                die('Database connection failed: ' . $e->getMessage());
            }
            die('Database connection failed.');
        }
    }

    public static function getConnection(): PDO
    {
        return self::$connection;
    }

    public static function getInstance(): self
    {
        return new self();
    }

    public function execute(string $sql, array $params = []): \PDOStatement
    {
        return self::query($sql, $params);
    }

    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::$connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetch(string $sql, array $params = []): ?array
    {
        $stmt = self::query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        $stmt = self::query($sql, $params);
        return $stmt->fetchAll();
    }

    public static function insert(string $table, array $data): int
    {
        // Auto-inject tenant_id
        if (in_array($table, self::$tenantTables) && !isset($data['tenant_id'])) {
            $data['tenant_id'] = $_SESSION['tenant_id'] ?? 1;
        }

        $columns = implode(', ', array_map(fn($col) => "`{$col}`", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders})";
        self::query($sql, array_values($data));

        return (int) self::$connection->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = implode(', ', array_map(fn($col) => "`{$col}` = ?", array_keys($data)));
        $sql = "UPDATE `{$table}` SET {$set} WHERE {$where}";

        $stmt = self::query($sql, array_merge(array_values($data), $whereParams));
        return $stmt->rowCount();
    }

    public static function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Soft delete - set is_deleted=1 instead of real delete
     */
    public static function softDelete(string $table, string $where, array $params = []): int
    {
        return self::update($table, [
            'is_deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s'),
        ], $where, $params);
    }

    /**
     * Restore soft-deleted record
     */
    public static function restore(string $table, string $where, array $params = []): int
    {
        return self::update($table, [
            'is_deleted' => 0,
            'deleted_at' => null,
        ], $where, $params);
    }

    /**
     * Check if a table supports soft delete
     */
    public static function hasSoftDelete(string $table): bool
    {
        return in_array($table, self::$softDeleteTables);
    }

    /**
     * Check if a table is tenant-scoped
     */
    public static function isTenantScoped(string $table): bool
    {
        return in_array($table, self::$tenantTables);
    }

    /**
     * Get tenant_id for current session
     */
    public static function tenantId(): int
    {
        return (int) ($_SESSION['tenant_id'] ?? 1);
    }

    /**
     * Build WHERE clause with automatic tenant + soft delete scope
     * Usage: [$where, $params] = Database::scoped('contacts', 'c', 'c.status = ?', ['new']);
     */
    public static function scoped(string $table, string $alias = '', string $extraWhere = '1=1', array $extraParams = []): array
    {
        $prefix = $alias ? "{$alias}." : '';
        $conditions = [$extraWhere];
        $params = $extraParams;

        // Auto add soft delete filter
        if (in_array($table, self::$softDeleteTables)) {
            $conditions[] = "{$prefix}is_deleted = 0";
        }

        // Auto add tenant scope
        if (in_array($table, self::$tenantTables)) {
            $conditions[] = "{$prefix}tenant_id = ?";
            $params[] = self::tenantId();
        }

        return [implode(' AND ', $conditions), $params];
    }

    public static function lastInsertId(): int
    {
        return (int) self::$connection->lastInsertId();
    }

    public static function beginTransaction(): void
    {
        self::$connection->beginTransaction();
    }

    public static function commit(): void
    {
        self::$connection->commit();
    }

    public static function rollback(): void
    {
        self::$connection->rollBack();
    }
}
