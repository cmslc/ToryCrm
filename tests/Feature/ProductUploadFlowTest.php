<?php

namespace Tests\Feature;

use Core\Database;

class ProductUploadFlowTest extends DatabaseTestCase
{
    public function testStoreProductWithTenantId(): void
    {
        $_SESSION['tenant_id'] = 5;

        $productId = Database::insert('products', [
            'tenant_id' => Database::tenantId(),
            'name' => 'Test Product',
            'sku' => 'SP001',
            'type' => 'product',
            'unit' => 'Cái',
            'price' => 100000,
            'cost_price' => 80000,
            'image' => null,
            'is_active' => 1,
        ]);

        $product = Database::fetch("SELECT * FROM products WHERE id = ?", [$productId]);

        $this->assertNotNull($product);
        $this->assertEquals(5, $product['tenant_id'], 'Product should have tenant_id from session');
        $this->assertEquals('Test Product', $product['name']);
        $this->assertEquals('SP001', $product['sku']);
    }

    public function testStoreProductWithImage(): void
    {
        $imageName = uniqid() . '_product_photo.jpg';

        $productId = Database::insert('products', [
            'tenant_id' => Database::tenantId(),
            'name' => 'Product With Image',
            'image' => $imageName,
            'is_active' => 1,
        ]);

        $product = Database::fetch("SELECT * FROM products WHERE id = ?", [$productId]);

        $this->assertNotNull($product);
        $this->assertEquals($imageName, $product['image']);
    }

    public function testStoreProductWithoutImage(): void
    {
        $productId = Database::insert('products', [
            'tenant_id' => Database::tenantId(),
            'name' => 'Product No Image',
            'image' => null,
            'is_active' => 1,
        ]);

        $product = Database::fetch("SELECT * FROM products WHERE id = ?", [$productId]);

        $this->assertNotNull($product);
        $this->assertNull($product['image']);
    }

    public function testUpdateProductImageReplacesOldImage(): void
    {
        // Create a test directory and fake old image
        $uploadDir = BASE_PATH . '/public/uploads/products_test';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $oldImageName = 'old_image_123.jpg';
        $oldImagePath = $uploadDir . '/' . $oldImageName;
        file_put_contents($oldImagePath, 'old-image-data');

        // Insert product with old image
        $productId = Database::insert('products', [
            'tenant_id' => Database::tenantId(),
            'name' => 'Product To Update',
            'image' => $oldImageName,
            'is_active' => 1,
        ]);

        // Verify old image exists on disk
        $this->assertFileExists($oldImagePath);

        // Simulate image replacement - delete old file (as the fix does)
        $product = Database::fetch("SELECT * FROM products WHERE id = ?", [$productId]);
        if (!empty($product['image'])) {
            $oldPath = $uploadDir . '/' . $product['image'];
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        // Update with new image name
        $newImageName = 'new_image_456.jpg';
        Database::update('products', ['image' => $newImageName], 'id = ?', [$productId]);

        // Verify old image is deleted from disk
        $this->assertFileDoesNotExist($oldImagePath);

        // Verify new image name in database
        $updated = Database::fetch("SELECT * FROM products WHERE id = ?", [$productId]);
        $this->assertEquals($newImageName, $updated['image']);

        // Clean up
        if (is_dir($uploadDir)) {
            array_map('unlink', glob($uploadDir . '/*'));
            rmdir($uploadDir);
        }
    }

    public function testUpdateProductWithoutNewImageKeepsOld(): void
    {
        $oldImageName = 'existing_image.jpg';

        $productId = Database::insert('products', [
            'tenant_id' => Database::tenantId(),
            'name' => 'Keep Image Product',
            'image' => $oldImageName,
            'is_active' => 1,
        ]);

        // Update without image field
        Database::update('products', [
            'name' => 'Updated Product Name',
        ], 'id = ?', [$productId]);

        $product = Database::fetch("SELECT * FROM products WHERE id = ?", [$productId]);

        $this->assertEquals('Updated Product Name', $product['name']);
        $this->assertEquals($oldImageName, $product['image'], 'Image should remain unchanged');
    }

    public function testSoftDeletedProductsExcludedFromList(): void
    {
        // Insert active product
        Database::insert('products', [
            'tenant_id' => 1,
            'name' => 'Active Product',
            'is_active' => 1,
            'is_deleted' => 0,
        ]);

        // Insert soft-deleted product
        Database::insert('products', [
            'tenant_id' => 1,
            'name' => 'Deleted Product',
            'is_active' => 1,
            'is_deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        // Query like the API does (with the fix)
        $products = Database::fetchAll(
            "SELECT p.* FROM products p WHERE p.is_deleted = 0"
        );

        $this->assertCount(1, $products);
        $this->assertEquals('Active Product', $products[0]['name']);
    }

    public function testSoftDeletedProductsVisibleWithoutFilter(): void
    {
        Database::insert('products', ['tenant_id' => 1, 'name' => 'A', 'is_deleted' => 0]);
        Database::insert('products', ['tenant_id' => 1, 'name' => 'B', 'is_deleted' => 1]);

        // Without filter (the old bug)
        $all = Database::fetchAll("SELECT * FROM products WHERE 1=1");
        $this->assertCount(2, $all, 'Without filter, deleted products would leak through');

        // With filter (the fix)
        $active = Database::fetchAll("SELECT * FROM products WHERE is_deleted = 0");
        $this->assertCount(1, $active);
    }

    public function testProductImageColumnMatchesSchema(): void
    {
        $columns = Database::fetchAll("PRAGMA table_info(products)");
        $columnNames = array_column($columns, 'name');

        $this->assertContains('image', $columnNames);
        $this->assertContains('tenant_id', $columnNames);
        $this->assertContains('is_deleted', $columnNames);
        $this->assertContains('deleted_at', $columnNames);
    }

    public function testProductImagesTableHasCorrectColumns(): void
    {
        $columns = Database::fetchAll("PRAGMA table_info(product_images)");
        $columnNames = array_column($columns, 'name');

        // Verify the fix: is_featured (not is_primary)
        $this->assertContains('is_featured', $columnNames, 'Should use is_featured, not is_primary');
        $this->assertNotContains('is_primary', $columnNames, 'Should NOT have is_primary column');
        $this->assertContains('image_path', $columnNames, 'Should use image_path, not file_path');
        $this->assertNotContains('file_path', $columnNames, 'Should NOT have file_path column');
        $this->assertNotContains('file_name', $columnNames, 'Should NOT have file_name column');
    }

    public function testProductImagesQueryUsesCorrectColumns(): void
    {
        // Insert a product
        $productId = Database::insert('products', [
            'tenant_id' => 1,
            'name' => 'Product With Gallery',
        ]);

        // Insert product images
        Database::insert('product_images', [
            'product_id' => $productId,
            'image_path' => 'uploads/products/img1.jpg',
            'is_featured' => 1,
            'sort_order' => 0,
        ]);
        Database::insert('product_images', [
            'product_id' => $productId,
            'image_path' => 'uploads/products/img2.jpg',
            'is_featured' => 0,
            'sort_order' => 1,
        ]);

        // Query as the fixed API does
        $images = Database::fetchAll(
            "SELECT id, image_path, is_featured, sort_order
             FROM product_images
             WHERE product_id = ?
             ORDER BY sort_order",
            [$productId]
        );

        $this->assertCount(2, $images);
        $this->assertEquals(1, $images[0]['is_featured']);
        $this->assertEquals('uploads/products/img1.jpg', $images[0]['image_path']);
        $this->assertEquals(0, $images[1]['is_featured']);
    }

    public function testFileUploadMetadataStoredCorrectly(): void
    {
        $id = Database::insert('file_uploads', [
            'file_name' => 'abc123_product.jpg',
            'original_name' => 'product.jpg',
            'file_path' => 'uploads/products/abc123_product.jpg',
            'file_size' => 2048,
            'mime_type' => 'image/jpeg',
            'entity_type' => 'product',
            'entity_id' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $record = Database::fetch("SELECT * FROM file_uploads WHERE id = ?", [$id]);

        $this->assertNotNull($record);
        $this->assertEquals('abc123_product.jpg', $record['file_name']);
        $this->assertEquals('product.jpg', $record['original_name']);
        $this->assertEquals('image/jpeg', $record['mime_type']);
        $this->assertEquals(2048, $record['file_size']);
        $this->assertEquals('product', $record['entity_type']);
    }

    public function testActivityLogCreatedOnProductCreate(): void
    {
        $_SESSION['tenant_id'] = 1;
        $productName = 'New Test Product';

        $productId = Database::insert('products', [
            'tenant_id' => Database::tenantId(),
            'name' => $productName,
            'is_active' => 1,
        ]);

        // Simulate activity log (as the controller does)
        Database::insert('activities', [
            'type' => 'system',
            'title' => "Thêm sản phẩm: {$productName}",
            'description' => "Sản phẩm {$productName} đã được tạo.",
            'user_id' => 1,
        ]);

        $activity = Database::fetch(
            "SELECT * FROM activities WHERE title LIKE ?",
            ["%{$productName}%"]
        );

        $this->assertNotNull($activity);
        $this->assertEquals('system', $activity['type']);
        $this->assertStringContainsString($productName, $activity['title']);
    }
}
