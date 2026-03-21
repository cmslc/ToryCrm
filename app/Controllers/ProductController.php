<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class ProductController extends Controller
{
    public function index()
    {
        $search = $this->input('search');
        $categoryId = $this->input('category_id');
        $type = $this->input('type');
        $page = max(1, (int) $this->input('page') ?: 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $where = ["p.is_deleted = 0", "p.tenant_id = ?"];
        $params = [Database::tenantId()];

        if ($search) {
            $where[] = "(p.name LIKE ? OR p.sku LIKE ?)";
            $s = "%{$search}%";
            $params = array_merge($params, [$s, $s]);
        }

        if ($categoryId) {
            $where[] = "p.category_id = ?";
            $params[] = $categoryId;
        }

        if ($type) {
            $where[] = "p.type = ?";
            $params[] = $type;
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
             ORDER BY p.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $categories = Database::fetchAll("SELECT * FROM product_categories ORDER BY sort_order, name");
        $totalPages = ceil($total / $perPage);

        return $this->view('products.index', [
            'products' => [
                'items' => $products,
                'total' => $total,
                'page' => $page,
                'total_pages' => $totalPages,
            ],
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'category_id' => $categoryId,
                'type' => $type,
            ],
        ]);
    }

    public function create()
    {
        $categories = Database::fetchAll("SELECT * FROM product_categories ORDER BY sort_order, name");

        return $this->view('products.create', [
            'categories' => $categories,
        ]);
    }

    public function store()
    {
        if (!$this->isPost()) {
            return $this->redirect('products');
        }

        $data = $this->allInput();
        $name = trim($data['name'] ?? '');

        if (empty($name)) {
            $this->setFlash('error', 'Tên sản phẩm không được để trống.');
            return $this->back();
        }

        // Handle image upload
        $imageName = null;
        if (!empty($_FILES['image']['name'])) {
            $uploaded = \App\Services\FileUploadService::uploadImage($_FILES['image'], 'products');
            if ($uploaded) $imageName = $uploaded['file_name'];
        }

        $productId = Database::insert('products', [
            'name' => $name,
            'sku' => trim($data['sku'] ?? '') ?: null,
            'category_id' => !empty($data['category_id']) ? $data['category_id'] : null,
            'type' => $data['type'] ?? 'product',
            'unit' => trim($data['unit'] ?? 'Cái'),
            'price' => (float)($data['price'] ?? 0),
            'cost_price' => (float)($data['cost_price'] ?? 0),
            'tax_rate' => (float)($data['tax_rate'] ?? 0),
            'stock_quantity' => (int)($data['stock_quantity'] ?? 0),
            'min_stock' => (int)($data['min_stock'] ?? 0),
            'description' => trim($data['description'] ?? ''),
            'image' => $imageName,
            'is_active' => 1,
            'created_by' => $this->userId(),
        ]);

        Database::insert('activities', [
            'type' => 'system',
            'title' => "Thêm sản phẩm: {$name}",
            'description' => "Sản phẩm {$name} đã được tạo.",
            'user_id' => $this->userId(),
        ]);

        $this->setFlash('success', 'Tạo sản phẩm thành công.');
        return $this->redirect('products/' . $productId);
    }

    public function show($id)
    {
        $product = Database::fetch(
            "SELECT p.*, pc.name as category_name, u.name as created_by_name
             FROM products p
             LEFT JOIN product_categories pc ON p.category_id = pc.id
             LEFT JOIN users u ON p.created_by = u.id
             WHERE p.id = ?",
            [$id]
        );

        if (!$product) {
            $this->setFlash('error', 'Sản phẩm không tồn tại.');
            return $this->redirect('products');
        }

        // Get orders containing this product
        $orderItems = Database::fetchAll(
            "SELECT oi.*, o.order_number, o.status as order_status, o.type as order_type, o.created_at as order_date
             FROM order_items oi
             JOIN orders o ON oi.order_id = o.id
             WHERE oi.product_id = ?
             ORDER BY o.created_at DESC
             LIMIT 20",
            [$id]
        );

        return $this->view('products.show', [
            'product' => $product,
            'orderItems' => $orderItems,
        ]);
    }

    public function edit($id)
    {
        $product = Database::fetch("SELECT * FROM products WHERE id = ?", [$id]);

        if (!$product) {
            $this->setFlash('error', 'Sản phẩm không tồn tại.');
            return $this->redirect('products');
        }

        $categories = Database::fetchAll("SELECT * FROM product_categories ORDER BY sort_order, name");

        return $this->view('products.edit', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    public function update($id)
    {
        if (!$this->isPost()) {
            return $this->redirect('products/' . $id);
        }

        $product = Database::fetch("SELECT * FROM products WHERE id = ?", [$id]);

        if (!$product) {
            $this->setFlash('error', 'Sản phẩm không tồn tại.');
            return $this->redirect('products');
        }

        $data = $this->allInput();
        $name = trim($data['name'] ?? '');

        if (empty($name)) {
            $this->setFlash('error', 'Tên sản phẩm không được để trống.');
            return $this->back();
        }

        $updateData = [
            'name' => $name,
            'sku' => trim($data['sku'] ?? '') ?: null,
            'category_id' => !empty($data['category_id']) ? $data['category_id'] : null,
            'type' => $data['type'] ?? 'product',
            'unit' => trim($data['unit'] ?? 'Cái'),
            'price' => (float)($data['price'] ?? 0),
            'cost_price' => (float)($data['cost_price'] ?? 0),
            'tax_rate' => (float)($data['tax_rate'] ?? 0),
            'stock_quantity' => (int)($data['stock_quantity'] ?? 0),
            'min_stock' => (int)($data['min_stock'] ?? 0),
            'description' => trim($data['description'] ?? ''),
            'is_active' => isset($data['is_active']) ? 1 : 0,
        ];

        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $uploaded = \App\Services\FileUploadService::uploadImage($_FILES['image'], 'products');
            if ($uploaded) $updateData['image'] = $uploaded['file_name'];
        }

        Database::update('products', $updateData, 'id = ?', [$id]);

        $this->setFlash('success', 'Cập nhật sản phẩm thành công.');
        return $this->redirect('products/' . $id);
    }

    public function delete($id)
    {
        $product = Database::fetch("SELECT * FROM products WHERE id = ?", [$id]);

        if (!$product) {
            $this->setFlash('error', 'Sản phẩm không tồn tại.');
            return $this->redirect('products');
        }

        Database::delete('products', 'id = ?', [$id]);

        Database::insert('activities', [
            'type' => 'system',
            'title' => "Xóa sản phẩm: {$product['name']}",
            'user_id' => $this->userId(),
        ]);

        $this->setFlash('success', 'Xóa sản phẩm thành công.');
        return $this->redirect('products');
    }
}
