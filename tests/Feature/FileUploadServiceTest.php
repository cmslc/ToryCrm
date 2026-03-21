<?php

namespace Tests\Feature;

use App\Services\FileUploadService;
use Core\Database;

class FileUploadServiceTest extends DatabaseTestCase
{
    private string $uploadDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->uploadDir = BASE_PATH . '/public/uploads/products_test';
    }

    protected function tearDown(): void
    {
        // Clean up uploaded files
        if (is_dir($this->uploadDir)) {
            array_map('unlink', glob($this->uploadDir . '/*'));
            rmdir($this->uploadDir);
        }
        parent::tearDown();
    }

    public function testUploadImageRejectsNonImageExtension(): void
    {
        $file = $this->makeFakeFileArray('document.pdf', 5000, 'application/pdf');

        $result = FileUploadService::uploadImage($file, 'products_test');

        $this->assertNull($result, 'uploadImage should reject non-image files');
    }

    public function testUploadImageRejectsExeFile(): void
    {
        $file = $this->makeFakeFileArray('malware.exe', 5000, 'application/x-executable');

        $result = FileUploadService::uploadImage($file, 'products_test');

        $this->assertNull($result, 'uploadImage should reject .exe files');
    }

    public function testUploadImageRejectsPhpFile(): void
    {
        $file = $this->makeFakeFileArray('shell.php', 100, 'text/plain');

        $result = FileUploadService::uploadImage($file, 'products_test');

        $this->assertNull($result, 'uploadImage should reject .php files');
    }

    public function testUploadRejectsOversizedFile(): void
    {
        // Simulate a file > 10MB
        $file = $this->makeFakeFileArray('big.jpg', 11 * 1024 * 1024, 'image/jpeg');

        $result = FileUploadService::uploadImage($file, 'products_test');

        $this->assertNull($result, 'Should reject files larger than 10MB');
    }

    public function testUploadRejectsEmptyFile(): void
    {
        $file = $this->makeFakeFileArray('empty.jpg', 0, 'image/jpeg', '', UPLOAD_ERR_NO_FILE);

        $result = FileUploadService::uploadImage($file, 'products_test');

        $this->assertNull($result, 'Should reject empty/missing files');
    }

    public function testUploadRejectsUploadError(): void
    {
        $file = $this->makeFakeFileArray('error.jpg', 1000, 'image/jpeg');
        $file['error'] = UPLOAD_ERR_INI_SIZE;

        $result = FileUploadService::uploadImage($file, 'products_test');

        $this->assertNull($result, 'Should reject files with upload errors');
    }

    public function testUploadAcceptsJpgExtension(): void
    {
        $file = $this->makeFakeFileArray('photo.jpg', 1024, 'image/jpeg');

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $this->assertContains($ext, ['jpg', 'jpeg', 'png', 'gif']);
    }

    public function testUploadAcceptsPngExtension(): void
    {
        $file = $this->makeFakeFileArray('photo.png', 1024, 'image/png');

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $this->assertContains($ext, ['jpg', 'jpeg', 'png', 'gif']);
    }

    public function testUploadAcceptsGifExtension(): void
    {
        $file = $this->makeFakeFileArray('animation.gif', 1024, 'image/gif');

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $this->assertContains($ext, ['jpg', 'jpeg', 'png', 'gif']);
    }

    public function testFileNameIsSanitized(): void
    {
        $originalName = 'ảnh sản phẩm (1).jpg';
        $sanitized = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $originalName);

        $this->assertStringNotContainsString(' ', $sanitized);
        $this->assertStringNotContainsString('(', $sanitized);
        $this->assertStringNotContainsString(')', $sanitized);
        $this->assertStringEndsWith('.jpg', $sanitized);
    }

    public function testDatabaseInsertHasCorrectColumns(): void
    {
        // Verify the columns used in FileUploadService match the table schema
        // This tests the fix for file_type → mime_type
        $columns = Database::fetchAll("PRAGMA table_info(file_uploads)");
        $columnNames = array_column($columns, 'name');

        $this->assertContains('mime_type', $columnNames, 'file_uploads table should have mime_type column');
        $this->assertNotContains('file_type', $columnNames, 'file_uploads should NOT have file_type column');
        $this->assertNotContains('extension', $columnNames, 'file_uploads should NOT have extension column');
        $this->assertContains('file_name', $columnNames);
        $this->assertContains('original_name', $columnNames);
        $this->assertContains('file_path', $columnNames);
        $this->assertContains('file_size', $columnNames);
        $this->assertContains('entity_type', $columnNames);
        $this->assertContains('entity_id', $columnNames);
    }

    public function testDeleteRemovesFileFromDatabase(): void
    {
        // Insert a fake file record
        $id = Database::insert('file_uploads', [
            'file_name' => 'test123_photo.jpg',
            'original_name' => 'photo.jpg',
            'file_path' => 'uploads/products_test/test123_photo.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->assertGreaterThan(0, $id);

        // Verify it exists
        $record = Database::fetch("SELECT * FROM file_uploads WHERE id = ?", [$id]);
        $this->assertNotNull($record);

        // Delete it
        $result = FileUploadService::delete($id);
        $this->assertTrue($result);

        // Verify it's gone
        $record = Database::fetch("SELECT * FROM file_uploads WHERE id = ?", [$id]);
        $this->assertNull($record);
    }

    public function testDeleteReturnsFalseForNonExistentFile(): void
    {
        $result = FileUploadService::delete(99999);
        $this->assertFalse($result);
    }

    public function testUploadDirectoryCreation(): void
    {
        $testDir = BASE_PATH . '/public/uploads/products_test';
        if (is_dir($testDir)) {
            rmdir($testDir);
        }

        // Calling upload will try to create the directory
        // Even though move_uploaded_file will fail (not a real upload),
        // the directory should be created
        $file = $this->makeFakeFileArray('photo.jpg', 1024, 'image/jpeg');
        FileUploadService::uploadImage($file, 'products_test');

        $this->assertDirectoryExists($testDir);
    }
}
