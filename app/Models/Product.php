<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class Product extends Model
{
    protected string $table = 'products';

    public function getWithRelations(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $where = '1=1';
        $params = [];

        if (!empty($filters['search'])) {
            $where .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['category_id'])) {
            $where .= " AND p.category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['type'])) {
            $where .= " AND p.type = ?";
            $params[] = $filters['type'];
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $where .= " AND p.is_active = ?";
            $params[] = $filters['is_active'];
        }

        $total = Database::fetch(
            "SELECT COUNT(*) as total FROM products p WHERE {$where}",
            $params
        )['total'];

        $offset = ($page - 1) * $perPage;

        $items = Database::fetchAll(
            "SELECT p.*, pc.name as category_name, u.name as created_by_name
             FROM products p
             LEFT JOIN product_categories pc ON p.category_id = pc.id
             LEFT JOIN users u ON p.created_by = u.id
             WHERE {$where}
             ORDER BY p.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return [
            'items' => $items,
            'total' => (int)$total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage),
        ];
    }

    public function getCategories(): array
    {
        return Database::fetchAll("SELECT * FROM product_categories ORDER BY sort_order, name");
    }

    public function getActiveProducts(): array
    {
        return Database::fetchAll(
            "SELECT id, name, sku, price, unit, tax_rate FROM products WHERE is_active = 1 ORDER BY name"
        );
    }

    public function findBySku(string $sku): ?array
    {
        return Database::fetch("SELECT * FROM products WHERE sku = ?", [$sku]);
    }

    public function getLowStock(): array
    {
        return Database::fetchAll(
            "SELECT * FROM products WHERE type = 'product' AND stock_quantity <= min_stock AND is_active = 1 ORDER BY stock_quantity ASC"
        );
    }

    public function updateStock(int $id, int $quantity): void
    {
        Database::query(
            "UPDATE products SET stock_quantity = stock_quantity + ?, updated_at = NOW() WHERE id = ?",
            [$quantity, $id]
        );
    }
}
