<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Core\Database;

abstract class DatabaseTestCase extends TestCase
{
    protected static bool $dbInitialized = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('BASE_PATH')) {
            define('BASE_PATH', dirname(__DIR__, 2));
        }

        $_SESSION['tenant_id'] = 1;

        $this->initSqliteDatabase();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up any test upload files
        $testUploadDir = BASE_PATH . '/public/uploads/products_test';
        if (is_dir($testUploadDir)) {
            array_map('unlink', glob($testUploadDir . '/*'));
            rmdir($testUploadDir);
        }

        $_SESSION = [];
    }

    private function initSqliteDatabase(): void
    {
        // Use reflection to reset the Database connection for each test
        $ref = new \ReflectionClass(Database::class);
        $prop = $ref->getProperty('connection');
        $prop->setAccessible(true);
        $prop->setValue(null, null);

        // Initialize with SQLite in-memory
        $pdo = new \PDO('sqlite::memory:', null, null, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        $prop->setValue(null, $pdo);

        $this->createTables($pdo);
    }

    private function createTables(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS file_uploads (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                original_name VARCHAR(255) NOT NULL,
                file_name VARCHAR(255) NOT NULL,
                file_path VARCHAR(500) NOT NULL,
                file_size INTEGER DEFAULT 0,
                mime_type VARCHAR(100) DEFAULT NULL,
                entity_type VARCHAR(50) DEFAULT NULL,
                entity_id INTEGER DEFAULT NULL,
                uploaded_by INTEGER DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id INTEGER DEFAULT 1,
                name VARCHAR(255) NOT NULL,
                sku VARCHAR(100) DEFAULT NULL,
                category_id INTEGER DEFAULT NULL,
                type VARCHAR(20) DEFAULT 'product',
                unit VARCHAR(50) DEFAULT 'Cái',
                price DECIMAL(15,2) DEFAULT 0,
                cost_price DECIMAL(15,2) DEFAULT 0,
                tax_rate DECIMAL(5,2) DEFAULT 0,
                stock_quantity INTEGER DEFAULT 0,
                min_stock INTEGER DEFAULT 0,
                description TEXT DEFAULT NULL,
                image VARCHAR(255) DEFAULT NULL,
                is_active TINYINT DEFAULT 1,
                is_deleted TINYINT DEFAULT 0,
                deleted_at DATETIME DEFAULT NULL,
                created_by INTEGER DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT NULL
            )
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS product_images (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                product_id INTEGER NOT NULL,
                image_path VARCHAR(500) NOT NULL,
                is_featured TINYINT DEFAULT 0,
                sort_order INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS product_categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                sort_order INTEGER DEFAULT 0
            )
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS activities (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id INTEGER DEFAULT 1,
                type VARCHAR(50) DEFAULT NULL,
                title VARCHAR(255) DEFAULT NULL,
                description TEXT DEFAULT NULL,
                user_id INTEGER DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    /**
     * Create a fake uploaded file for testing (bypasses move_uploaded_file).
     */
    protected function createTempFile(string $content = 'test', string $extension = 'jpg'): string
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_upload_');
        file_put_contents($tmpFile, $content);
        return $tmpFile;
    }

    protected function makeFakeFileArray(string $name = 'photo.jpg', int $size = 1024, string $type = 'image/jpeg', ?string $tmpName = null, int $error = UPLOAD_ERR_OK): array
    {
        return [
            'name' => $name,
            'type' => $type,
            'size' => $size,
            'tmp_name' => $tmpName ?? $this->createTempFile('fake-image-data', pathinfo($name, PATHINFO_EXTENSION)),
            'error' => $error,
        ];
    }
}
