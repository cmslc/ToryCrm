<?php

namespace App\Services;

/**
 * Minimal XLSX reader — unzips the .xlsx, reads sheet1.xml and sharedStrings.xml,
 * returns rows as arrays indexed by column letter.
 *
 * Good enough for simple tabular imports. Does not handle formulas, styles,
 * multiple sheets, or dates as numbers — for that, use PhpSpreadsheet.
 */
class XlsxReader
{
    /**
     * Read all rows from sheet1. Each row is [colLetter => string value].
     * @return array<int, array<string,string>>
     */
    public static function readAllRows(string $xlsxPath): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($xlsxPath) !== true) {
            throw new \RuntimeException('Không mở được file Excel.');
        }

        try {
            // Shared strings table (may be empty or absent)
            $strings = [];
            $ssXml = $zip->getFromName('xl/sharedStrings.xml');
            if ($ssXml !== false) {
                $prev = libxml_use_internal_errors(true);
                $doc = simplexml_load_string($ssXml);
                libxml_use_internal_errors($prev);
                if ($doc !== false) {
                    foreach ($doc->si as $si) {
                        $text = '';
                        foreach ($si->xpath('.//*[local-name()="t"]') ?: [] as $t) {
                            $text .= (string) $t;
                        }
                        $strings[] = $text;
                    }
                }
            }

            $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
            if ($sheetXml === false) {
                throw new \RuntimeException('Không có sheet1.xml trong file.');
            }
        } finally {
            $zip->close();
        }

        // Regex-based row extraction — much faster than simplexml on large sheets
        $rows = [];
        if (!preg_match_all('/<row[^>]*>([\s\S]*?)<\/row>/', $sheetXml, $rowMatches)) {
            return $rows;
        }

        foreach ($rowMatches[1] as $rowBody) {
            $row = [];
            if (preg_match_all('/<c\s+r="([A-Z]+)(\d+)"([^>]*)>([\s\S]*?)<\/c>/', $rowBody, $cellMatches, PREG_SET_ORDER)) {
                foreach ($cellMatches as $m) {
                    $col = $m[1];
                    $attrs = $m[3];
                    $body = $m[4];
                    $type = null;
                    if (preg_match('/t="([^"]+)"/', $attrs, $tm)) $type = $tm[1];

                    $val = '';
                    if ($type === 'inlineStr') {
                        if (preg_match('/<t[^>]*>([\s\S]*?)<\/t>/', $body, $im)) {
                            $val = self::decode($im[1]);
                        }
                    } elseif (preg_match('/<v>([\s\S]*?)<\/v>/', $body, $vm)) {
                        $raw = $vm[1];
                        if ($type === 's' && ctype_digit($raw)) {
                            $val = $strings[(int) $raw] ?? '';
                        } elseif ($type === 'b') {
                            $val = $raw === '1' ? 'TRUE' : 'FALSE';
                        } else {
                            $val = self::decode($raw);
                        }
                    }
                    $row[$col] = $val;
                }
            }
            $rows[] = $row;
        }

        return $rows;
    }

    private static function decode(string $s): string
    {
        return html_entity_decode($s, ENT_QUOTES | ENT_XML1 | ENT_HTML5, 'UTF-8');
    }
}
