<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class ProductController extends Controller
{
    public function searchAjax()
    {
        $q = trim($this->input('q', ''));
        $tid = Database::tenantId();
        if (strlen($q) < 1) return $this->json([]);

        $results = Database::fetchAll(
            "SELECT id, name, sku, price, unit, tax_rate FROM products
             WHERE tenant_id = ? AND is_active = 1 AND is_deleted = 0
             AND (name LIKE ? OR sku LIKE ?)
             ORDER BY name LIMIT 20",
            [$tid, "%{$q}%", "%{$q}%"]
        );
        return $this->json($results);
    }

    public function index()
    {
        $this->authorize('products', 'view');
        $search = $this->input('search');
        $categoryId = $this->input('category_id');
        $type = $this->input('type');
        $page = max(1, (int) $this->input('page') ?: 1);
        $perPage = in_array((int)$this->input('per_page'), [10,20,50,100]) ? (int)$this->input('per_page') : 20;
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
            "SELECT p.*, pc.name as category_name, po.name as origin_name, pm.name as manufacturer_name
             FROM products p
             LEFT JOIN product_categories pc ON p.category_id = pc.id
             LEFT JOIN product_origins po ON p.origin_id = po.id
             LEFT JOIN product_manufacturers pm ON p.manufacturer_id = pm.id
             WHERE {$whereClause}
             ORDER BY p.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $categories = Database::fetchAll("SELECT * FROM product_categories ORDER BY sort_order, name");
        $totalPages = ceil($total / $perPage);

        $displayColumns = \App\Services\ColumnService::getColumns('products');

        return $this->view('products.index', [
            'products' => [
                'items' => $products,
                'total' => $total,
                'page' => $page,
                'total_pages' => $totalPages,
            ],
            'categories' => $categories,
            'displayColumns' => $displayColumns,
            'filters' => [
                'search' => $search,
                'category_id' => $categoryId,
                'type' => $type,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('products', 'create');
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
        $this->authorize('products', 'create');

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
        $this->authorize('products', 'view');
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
        $this->authorize('products', 'edit');
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
        $this->authorize('products', 'edit');

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
        if (!$this->isPost()) return $this->redirect('products');
        $this->authorize('products', 'delete');

        $product = $this->findSecure('products', (int)$id);
        if (!$product) {
            $this->setFlash('error', 'Sản phẩm không tồn tại.');
            return $this->redirect('products');
        }

        Database::softDelete('products', 'id = ?', [$id]);

        Database::insert('activities', [
            'type' => 'system',
            'title' => "Xóa sản phẩm: {$product['name']}",
            'user_id' => $this->userId(),
        ]);

        $this->setFlash('success', 'Đã xóa sản phẩm.');
        return $this->redirect('products');
    }

    public function trash()
    {
        $this->authorize('products', 'delete');
        $tid = Database::tenantId();
        $products = Database::fetchAll(
            "SELECT p.*, pc.name as category_name
             FROM products p
             LEFT JOIN product_categories pc ON p.category_id = pc.id
             WHERE p.is_deleted = 1 AND p.tenant_id = ?
             ORDER BY p.deleted_at DESC",
            [$tid]
        );

        return $this->view('products.trash', ['products' => $products]);
    }

    public function restore($id)
    {
        if (!$this->isPost()) return $this->redirect('products/trash');
        $this->authorize('products', 'delete');

        Database::restore('products', 'id = ?', [$id]);

        $this->setFlash('success', 'Đã khôi phục sản phẩm.');
        return $this->redirect('products/trash');
    }

    public function settings()
    {
        $this->authorize('products', 'view');
        $categories = Database::fetchAll(
            "SELECT c.*, COUNT(p.id) as product_count FROM product_categories c
             LEFT JOIN products p ON p.category_id = c.id AND p.is_deleted = 0
             GROUP BY c.id ORDER BY c.sort_order, c.name"
        );
        $manufacturers = Database::fetchAll(
            "SELECT m.*, COUNT(p.id) as product_count FROM product_manufacturers m
             LEFT JOIN products p ON p.manufacturer_id = m.id AND p.is_deleted = 0
             GROUP BY m.id ORDER BY m.name"
        );
        $origins = Database::fetchAll(
            "SELECT o.*, COUNT(p.id) as product_count FROM product_origins o
             LEFT JOIN products p ON p.origin_id = o.id AND p.is_deleted = 0
             GROUP BY o.id ORDER BY o.name"
        );

        return $this->view('products.settings', [
            'categories' => $categories,
            'manufacturers' => $manufacturers,
            'origins' => $origins,
        ]);
    }

    public function saveCategory()
    {
        if (!$this->isPost()) return $this->redirect('products/settings');
        $this->authorize('products', 'create');

        $id = (int)$this->input('id');
        $name = trim($this->input('name'));
        $sortOrder = (int)$this->input('sort_order');

        if (!$name) {
            $this->setFlash('error', 'Tên danh mục không được để trống.');
            return $this->redirect('products/settings');
        }

        if ($id) {
            Database::execute("UPDATE product_categories SET name = ?, sort_order = ? WHERE id = ?", [$name, $sortOrder, $id]);
            $this->setFlash('success', 'Đã cập nhật danh mục.');
        } else {
            Database::execute("INSERT INTO product_categories (name, sort_order) VALUES (?, ?)", [$name, $sortOrder]);
            $this->setFlash('success', 'Đã thêm danh mục.');
        }
        return $this->redirect('products/settings');
    }

    public function deleteCategory($id)
    {
        if (!$this->isPost()) return $this->redirect('products/settings');
        $this->authorize('products', 'delete');

        $count = (int)(Database::fetch("SELECT COUNT(*) as cnt FROM products WHERE category_id = ? AND is_deleted = 0", [$id])['cnt'] ?? 0);
        if ($count > 0) {
            $this->setFlash('error', "Không thể xóa: danh mục đang có $count sản phẩm.");
        } else {
            Database::execute("DELETE FROM product_categories WHERE id = ?", [$id]);
            $this->setFlash('success', 'Đã xóa danh mục.');
        }
        return $this->redirect('products/settings');
    }

    public function saveManufacturer()
    {
        if (!$this->isPost()) return $this->redirect('products/settings');
        $this->authorize('products', 'create');

        $id = (int)$this->input('id');
        $name = trim($this->input('name'));

        if (!$name) {
            $this->setFlash('error', 'Tên nhà sản xuất không được để trống.');
            return $this->redirect('products/settings');
        }

        if ($id) {
            Database::execute("UPDATE product_manufacturers SET name = ? WHERE id = ?", [$name, $id]);
            $this->setFlash('success', 'Đã cập nhật nhà sản xuất.');
        } else {
            Database::execute("INSERT INTO product_manufacturers (name) VALUES (?)", [$name]);
            $this->setFlash('success', 'Đã thêm nhà sản xuất.');
        }
        return $this->redirect('products/settings?tab=manufacturers');
    }

    public function deleteManufacturer($id)
    {
        if (!$this->isPost()) return $this->redirect('products/settings');
        $this->authorize('products', 'delete');

        $count = (int)(Database::fetch("SELECT COUNT(*) as cnt FROM products WHERE manufacturer_id = ? AND is_deleted = 0", [$id])['cnt'] ?? 0);
        if ($count > 0) {
            $this->setFlash('error', "Không thể xóa: nhà sản xuất đang có $count sản phẩm.");
        } else {
            Database::execute("DELETE FROM product_manufacturers WHERE id = ?", [$id]);
            $this->setFlash('success', 'Đã xóa nhà sản xuất.');
        }
        return $this->redirect('products/settings?tab=manufacturers');
    }

    public function saveOrigin()
    {
        if (!$this->isPost()) return $this->redirect('products/settings');
        $this->authorize('products', 'create');

        $id = (int)$this->input('id');
        $name = trim($this->input('name'));

        if (!$name) {
            $this->setFlash('error', 'Tên xuất xứ không được để trống.');
            return $this->redirect('products/settings');
        }

        if ($id) {
            Database::execute("UPDATE product_origins SET name = ? WHERE id = ?", [$name, $id]);
            $this->setFlash('success', 'Đã cập nhật xuất xứ.');
        } else {
            Database::execute("INSERT INTO product_origins (name) VALUES (?)", [$name]);
            $this->setFlash('success', 'Đã thêm xuất xứ.');
        }
        return $this->redirect('products/settings?tab=origins');
    }

    public function deleteOrigin($id)
    {
        if (!$this->isPost()) return $this->redirect('products/settings');
        $this->authorize('products', 'delete');

        $count = (int)(Database::fetch("SELECT COUNT(*) as cnt FROM products WHERE origin_id = ? AND is_deleted = 0", [$id])['cnt'] ?? 0);
        if ($count > 0) {
            $this->setFlash('error', "Không thể xóa: xuất xứ đang có $count sản phẩm.");
        } else {
            Database::execute("DELETE FROM product_origins WHERE id = ?", [$id]);
            $this->setFlash('success', 'Đã xóa xuất xứ.');
        }
        return $this->redirect('products/settings?tab=origins');
    }
}
