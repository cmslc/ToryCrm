<?php

namespace App\Services;

/**
 * Reusable CSV downloader with column picking.
 *
 * Usage:
 *   $rows = Database::fetchAll('SELECT * FROM quotations WHERE ...');
 *   $columns = [
 *       'quote_number' => ['label' => 'Số báo giá'],
 *       'total'        => ['label' => 'Tổng', 'format' => fn($r) => number_format($r['total'])],
 *       'status'       => ['label' => 'Trạng thái'],
 *   ];
 *   CsvExporter::download($rows, $columns, 'bao-gia.csv', $selectedKeys);
 *
 * - $selectedKeys (optional): subset of column keys to include, in order.
 *   If null, exports all columns.
 * - Emits UTF-8 BOM so Excel opens Vietnamese correctly.
 * - Streams line-by-line (no memory spike on large datasets).
 */
class CsvExporter
{
    public static function download(array $rows, array $columns, string $filename, ?array $selectedKeys = null): void
    {
        $keys = self::resolveKeys($columns, $selectedKeys);

        // Reset output buffer to guarantee clean stream
        while (ob_get_level() > 0) { ob_end_clean(); }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . self::safeName($filename) . '"');
        header('Cache-Control: no-store, no-cache');
        header('Pragma: no-cache');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM for Excel
        fwrite($out, "\xEF\xBB\xBF");

        // Header row
        $header = [];
        foreach ($keys as $k) $header[] = $columns[$k]['label'] ?? $k;
        fputcsv($out, $header);

        // Data rows
        foreach ($rows as $row) {
            $line = [];
            foreach ($keys as $k) {
                $col = $columns[$k];
                if (isset($col['format']) && is_callable($col['format'])) {
                    $line[] = (string) $col['format']($row);
                } else {
                    $line[] = (string) ($row[$k] ?? '');
                }
            }
            fputcsv($out, $line);
        }
        fclose($out);
        exit;
    }

    /** Parse the ?columns=a,b,c query param into an array of keys. */
    public static function parseColumnsParam(?string $raw, array $allColumns): ?array
    {
        if (!$raw) return null;
        $keys = array_filter(array_map('trim', explode(',', $raw)));
        $keys = array_values(array_intersect($keys, array_keys($allColumns)));
        return $keys ?: null;
    }

    private static function resolveKeys(array $columns, ?array $selectedKeys): array
    {
        if (!$selectedKeys) return array_keys($columns);
        // Preserve definition order, only include requested + valid keys
        $set = array_flip($selectedKeys);
        return array_values(array_filter(array_keys($columns), fn($k) => isset($set[$k])));
    }

    private static function safeName(string $name): string
    {
        $name = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $name) ?: 'export.csv';
        if (!str_ends_with($name, '.csv')) $name .= '.csv';
        return $name;
    }
}
