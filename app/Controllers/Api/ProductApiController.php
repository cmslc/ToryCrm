<?php

namespace App\Controllers\Api;

use Core\Controller;
use Core\Database;

class ProductApiController extends Controller
{
    public function list()
    {
        $limit = max(1, min(100, (int) ($_GET['limit'] ?? 20)));
        $offset = max(0, (int) ($_GET['offset'] ?? 0));
        $sort = $_GET['sort'] ?? 'created_at';
        $order = strtoupper($_GET['order'] ?? 'DESC');

        $allowedSorts = ['id', 'name', 'sku', 'price', 'type', 'created_at', 'updated_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        $where = ['p.is_deleted = 0'];
        $params = [];

        if (!empty($_GET['category_id'])) {
            $where[] = "p.category_id = ?";
            $params[] = (int) $_GET['category_id'];
        }

        if (!empty($_GET['type'])) {
            $where[] = "p.type = ?";
            $params[] = $_GET['type'];
        }

        if (!empty($_GET['search'])) {
            $where[] = "(p.name LIKE ? OR p.sku LIKE ?)";
            $s = "%" . $_GET['search'] . "%";
            $params[] = $s;
            $params[] = $s;
        }

        $whereClause = implode(' AND ', $where);

        $total = Database::fetch(
            "SELECT COUNT(*) as count FROM products p WHERE {$whereClause}",
            $params
        )['count'];

        $products = Database::fetchAll(
            "SELECT p.*, pc.name as category_name
             FROM products p
             LEFT JOIN product_categories pc ON p.category_id = pc.id
             WHERE {$whereClause}
             ORDER BY p.{$sort} {$order}
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        return $this->json([
            'data' => $products,
            'total' => (int) $total,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function detail()
    {
        $id = (int) ($_GET['id'] ?? 0);

        if (!$id) {
            return $this->json(['error' => 'Missing id parameter'], 400);
        }

        $product = Database::fetch(
            "SELECT p.*, pc.name as category_name, u.name as created_by_name
             FROM products p
             LEFT JOIN product_categories pc ON p.category_id = pc.id
             LEFT JOIN users u ON p.created_by = u.id
             WHERE p.id = ?",
            [$id]
        );

        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        // Get images
        $product['images'] = Database::fetchAll(
            "SELECT id, image_path, is_featured, sort_order
             FROM product_images
             WHERE product_id = ?
             ORDER BY sort_order",
            [$id]
        );

        return $this->json(['data' => $product]);
    }

    public function create()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $name = trim($input['name'] ?? '');

        if (empty($name)) {
            return $this->json(['error' => 'name is required'], 422);
        }

        $productId = Database::insert('products', [
            'tenant_id' => $_SESSION['tenant_id'] ?? 1,
            'name' => $name,
            'sku' => trim($input['sku'] ?? '') ?: null,
            'category_id' => !empty($input['category_id']) ? (int) $input['category_id'] : null,
            'type' => $input['type'] ?? 'product',
            'unit' => trim($input['unit'] ?? 'Cái'),
            'price' => (float) ($input['price'] ?? 0),
            'cost_price' => (float) ($input['cost_price'] ?? 0),
            'tax_rate' => (float) ($input['tax_rate'] ?? 0),
            'stock_quantity' => (int) ($input['stock_quantity'] ?? 0),
            'min_stock' => (int) ($input['min_stock'] ?? 0),
            'description' => trim($input['description'] ?? ''),
            'is_active' => (int) ($input['is_active'] ?? 1),
            'created_by' => $_SESSION['api_user']['user_id'] ?? null,
        ]);

        return $this->json([
            'message' => 'Thêm mới thành công',
            'id' => $productId,
        ], 201);
    }

    public function update()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $id = (int) ($input['id'] ?? $_GET['id'] ?? 0);

        if (!$id) {
            return $this->json(['error' => 'Missing id parameter'], 400);
        }

        $product = Database::fetch("SELECT * FROM products WHERE id = ?", [$id]);

        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        $updateData = [];
        $allowedFields = [
            'name', 'sku', 'category_id', 'type', 'unit', 'price',
            'cost_price', 'tax_rate', 'stock_quantity', 'min_stock',
            'description', 'is_active',
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $input)) {
                $updateData[$field] = is_string($input[$field]) ? trim($input[$field]) : $input[$field];
            }
        }

        if (empty($updateData)) {
            return $this->json(['error' => 'No fields to update'], 422);
        }

        $updateData['updated_at'] = date('Y-m-d H:i:s');

        Database::update('products', $updateData, 'id = ?', [$id]);

        return $this->json([
            'message' => 'Cập nhật thành công',
            'id' => $id,
        ]);
    }
}
