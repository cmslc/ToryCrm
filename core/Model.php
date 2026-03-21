<?php

namespace Core;

class Model
{
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function find(int $id): ?array
    {
        [$where, $params] = Database::scoped($this->table, '', "{$this->primaryKey} = ?", [$id]);
        return Database::fetch(
            "SELECT * FROM {$this->table} WHERE {$where}",
            $params
        );
    }

    public function all(string $orderBy = 'id DESC', int $limit = 0): array
    {
        [$where, $params] = Database::scoped($this->table);
        $sql = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY {$orderBy}";
        if ($limit > 0) $sql .= " LIMIT {$limit}";
        return Database::fetchAll($sql, $params);
    }

    public function where(string $column, $value, string $operator = '='): array
    {
        [$where, $params] = Database::scoped($this->table, '', "{$column} {$operator} ?", [$value]);
        return Database::fetchAll(
            "SELECT * FROM {$this->table} WHERE {$where}",
            $params
        );
    }

    public function whereFirst(string $column, $value, string $operator = '='): ?array
    {
        [$where, $params] = Database::scoped($this->table, '', "{$column} {$operator} ?", [$value]);
        return Database::fetch(
            "SELECT * FROM {$this->table} WHERE {$where} LIMIT 1",
            $params
        );
    }

    public function create(array $data): int
    {
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        return Database::insert($this->table, $data);
    }

    public function update(int $id, array $data): int
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return Database::update($this->table, $data, "{$this->primaryKey} = ?", [$id]);
    }

    public function deleteById(int $id): int
    {
        // Use soft delete if supported
        if (Database::hasSoftDelete($this->table)) {
            return Database::softDelete($this->table, "{$this->primaryKey} = ?", [$id]);
        }
        return Database::delete($this->table, "{$this->primaryKey} = ?", [$id]);
    }

    public function restoreById(int $id): int
    {
        return Database::restore($this->table, "{$this->primaryKey} = ?", [$id]);
    }

    public function count(string $where = '1=1', array $params = []): int
    {
        // Merge with scope
        [$scopedWhere, $scopedParams] = Database::scoped($this->table, '', $where, $params);
        $result = Database::fetch(
            "SELECT COUNT(*) as total FROM {$this->table} WHERE {$scopedWhere}",
            $scopedParams
        );
        return (int) ($result['total'] ?? 0);
    }

    public function paginate(int $page = 1, int $perPage = 10, string $where = '1=1', array $params = [], string $orderBy = 'id DESC'): array
    {
        [$scopedWhere, $scopedParams] = Database::scoped($this->table, '', $where, $params);

        $total = Database::fetch(
            "SELECT COUNT(*) as total FROM {$this->table} WHERE {$scopedWhere}",
            $scopedParams
        )['total'] ?? 0;

        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $items = Database::fetchAll(
            "SELECT * FROM {$this->table} WHERE {$scopedWhere} ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}",
            $scopedParams
        );

        return [
            'items' => $items,
            'total' => (int) $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) $totalPages,
        ];
    }
}
