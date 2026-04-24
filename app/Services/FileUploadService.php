<?php

namespace App\Services;

use Core\Database;

class FileUploadService
{
    private static array $allowedTypes = [
        'jpg', 'jpeg', 'png', 'gif', 'ico',
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'zip',
    ];

    private static array $imageTypes = [
        'jpg', 'jpeg', 'png', 'gif', 'ico',
    ];

    private static int $maxSize = 10 * 1024 * 1024; // 10MB

    /**
     * Upload a file, store metadata in the database, and return file info.
     */
    public static function upload(array $file, string $directory = 'files', ?string $entityType = null, ?int $entityId = null): ?array
    {
        try {
            if (!isset($file['tmp_name']) || empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
                return null;
            }

            if ($file['size'] > self::$maxSize) {
                return null;
            }

            $originalName = $file['name'];
            $extension    = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            // Block double-extension tricks: name.php.jpg, .phtml, .phar
            $danger = ['php', 'php3', 'php4', 'php5', 'php7', 'phtml', 'phar', 'pht', 'htaccess', 'htm', 'html', 'shtml', 'svg', 'js'];
            foreach (explode('.', strtolower($originalName)) as $part) {
                if (in_array($part, $danger)) return null;
            }

            if (!in_array($extension, self::$allowedTypes)) {
                return null;
            }

            // Real MIME-type check against magic bytes (not the untrusted $file['type'])
            $realMime = null;
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                if ($finfo) {
                    $realMime = finfo_file($finfo, $file['tmp_name']);
                    finfo_close($finfo);
                }
            }
            $extToMime = [
                'jpg' => ['image/jpeg', 'image/pjpeg'],
                'jpeg' => ['image/jpeg', 'image/pjpeg'],
                'png' => ['image/png'],
                'gif' => ['image/gif'],
                'ico' => ['image/x-icon', 'image/vnd.microsoft.icon', 'image/ico', 'image/icon', 'application/octet-stream'],
                'pdf' => ['application/pdf'],
                'doc' => ['application/msword', 'application/vnd.ms-office'],
                'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
                'xls' => ['application/vnd.ms-excel', 'application/vnd.ms-office'],
                'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
                'csv' => ['text/csv', 'text/plain', 'application/csv'],
                'zip' => ['application/zip', 'application/x-zip-compressed'],
            ];
            if ($realMime && isset($extToMime[$extension]) && !in_array($realMime, $extToMime[$extension])) {
                return null; // extension lies about content
            }

            // Randomise filename — no trace of original (user can use original_name column for display)
            $uniqueName = bin2hex(random_bytes(16)) . '.' . $extension;

            $uploadDir = BASE_PATH . '/public/uploads/' . $directory;

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $destination = $uploadDir . '/' . $uniqueName;

            if (is_uploaded_file($file['tmp_name'])) {
                if (!move_uploaded_file($file['tmp_name'], $destination)) {
                    return null;
                }
            } else {
                if (!copy($file['tmp_name'], $destination)) {
                    return null;
                }
            }

            $relativePath = 'uploads/' . $directory . '/' . $uniqueName;

            $id = Database::insert('file_uploads', [
                'file_name'     => $uniqueName,
                'original_name' => $originalName,
                'file_path'     => $relativePath,
                'file_size'     => $file['size'],
                'mime_type'     => $file['type'] ?? null,
                'entity_type'   => $entityType,
                'entity_id'     => $entityId,
                'uploaded_by'   => $_SESSION['user']['id'] ?? null,
            ]);

            return [
                'id'            => $id,
                'file_name'     => $uniqueName,
                'file_path'     => $relativePath,
                'original_name' => $originalName,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Upload an image file (restricted to image types only).
     */
    public static function uploadImage(array $file, string $directory = 'images'): ?array
    {
        try {
            if (!isset($file['tmp_name']) || empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
                return null;
            }

            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($extension, self::$imageTypes)) {
                return null;
            }

            return self::upload($file, $directory);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Delete a file from disk and database by its ID.
     */
    public static function delete(int $id): bool
    {
        try {
            $file = Database::fetch("SELECT * FROM file_uploads WHERE id = ?", [$id]);

            if (!$file) {
                return false;
            }

            $fullPath = BASE_PATH . '/public/' . $file['file_path'];

            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            Database::delete('file_uploads', 'id = ?', [$id]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
