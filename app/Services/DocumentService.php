<?php

namespace App\Services;

use Core\Database;

class DocumentService
{
    /**
     * Render a document template with data replacements.
     * @param string $type 'contract' or 'quotation'
     * @param int|null $templateId Specific template, or null for default
     * @param array $data Key-value replacements
     * @return string|null Rendered HTML or null if no template
     */
    public static function render(string $type, ?int $templateId, array $data): ?string
    {
        $tid = Database::tenantId();

        if ($templateId) {
            $template = Database::fetch("SELECT content FROM document_templates WHERE id = ? AND tenant_id = ?", [$templateId, $tid]);
        } else {
            $template = Database::fetch("SELECT content FROM document_templates WHERE type = ? AND is_default = 1 AND tenant_id = ? AND is_active = 1", [$type, $tid]);
            if (!$template) {
                $template = Database::fetch("SELECT content FROM document_templates WHERE type = ? AND tenant_id = ? AND is_active = 1 ORDER BY id LIMIT 1", [$type, $tid]);
            }
        }

        if (!$template || empty($template['content'])) return null;

        return str_replace(array_keys($data), array_values($data), $template['content']);
    }

    /**
     * Get available templates for a type.
     */
    public static function getTemplates(string $type): array
    {
        return Database::fetchAll(
            "SELECT id, name, is_default FROM document_templates WHERE type = ? AND tenant_id = ? AND is_active = 1 ORDER BY is_default DESC, name",
            [$type, Database::tenantId()]
        );
    }

    /**
     * Build items table HTML from items array.
     */
    public static function buildItemsTable(array $items, array $summary = []): string
    {
        $html = '<table border="1" cellpadding="6" cellspacing="0" style="width:100%;border-collapse:collapse">';
        $html .= '<thead><tr style="background:#f5f5f5"><th>STT</th><th>Tên sản phẩm</th><th>Đơn vị</th><th style="text-align:right">Số lượng</th><th style="text-align:right">Đơn giá (VNĐ)</th><th style="text-align:right">Chiết khấu</th><th style="text-align:right">CK thành tiền</th><th style="text-align:right">VAT (%)</th><th style="text-align:right">Thành tiền</th></tr></thead><tbody>';

        foreach ($items as $i => $item) {
            $html .= '<tr>';
            $html .= '<td style="text-align:center">' . ($i + 1) . '</td>';
            $html .= '<td>' . htmlspecialchars($item['product_name'] ?? '') . '</td>';
            $html .= '<td style="text-align:center">' . htmlspecialchars($item['unit'] ?? '') . '</td>';
            $html .= '<td style="text-align:right">' . number_format((float)($item['quantity'] ?? 0), 2) . '</td>';
            $html .= '<td style="text-align:right">' . number_format((float)($item['unit_price'] ?? 0)) . '</td>';
            $html .= '<td style="text-align:right">' . number_format((float)($item['discount_percent'] ?? 0), 2) . '%</td>';
            $html .= '<td style="text-align:right">' . number_format((float)($item['discount'] ?? 0)) . '</td>';
            $html .= '<td style="text-align:right">' . number_format((float)($item['tax_rate'] ?? 0), 2) . '%</td>';
            $html .= '<td style="text-align:right">' . number_format((float)($item['total'] ?? 0)) . '</td>';
            $html .= '</tr>';
        }

        // Summary rows
        foreach ($summary as $row) {
            $html .= '<tr><td colspan="8" style="text-align:right">' . $row['label'] . '</td>';
            $html .= '<td style="text-align:right"><strong>' . $row['value'] . '</strong></td></tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }

    /**
     * Build common replacements for contracts.
     */
    public static function contractReplacements(array $contract, array $items): array
    {
        $pmethods = ['bank_transfer'=>'Chuyển khoản','cash'=>'Tiền mặt','credit_card'=>'Thẻ tín dụng','other'=>'Khác'];

        $summary = [
            ['label' => '<strong>Tổng tiền hàng</strong>', 'value' => number_format((float)($contract['subtotal'] ?? 0))],
        ];
        if (($contract['shipping_fee'] ?? 0) > 0) {
            $summary[] = ['label' => 'Phí vận chuyển ' . number_format((float)($contract['shipping_fee_percent'] ?? 0), 2) . '%', 'value' => number_format((float)$contract['shipping_fee'])];
        }
        if (($contract['discount_amount'] ?? 0) > 0) {
            $summary[] = ['label' => 'Chiết khấu ' . number_format((float)($contract['discount_percent'] ?? 0), 2) . '%', 'value' => number_format((float)$contract['discount_amount'])];
        }
        if (($contract['vat_amount'] ?? 0) > 0) {
            $summary[] = ['label' => 'Thuế VAT ' . number_format((float)($contract['vat_percent'] ?? 0), 2) . '%', 'value' => number_format((float)$contract['vat_amount'])];
        }
        if (($contract['installation_fee'] ?? 0) > 0) {
            $summary[] = ['label' => 'Phí lắp đặt ' . number_format((float)($contract['installation_fee_percent'] ?? 0), 2) . '%', 'value' => number_format((float)$contract['installation_fee'])];
        }
        $summary[] = ['label' => '<strong>Tổng tiền thanh toán</strong>', 'value' => '<strong>' . number_format((float)($contract['value'] ?? 0)) . '</strong>'];
        $summary[] = ['label' => 'Bằng chữ', 'value' => '<em>' . self::numberToWords((float)($contract['value'] ?? 0)) . ' đồng</em>'];

        return [
            '{{contract_number}}' => $contract['contract_number'] ?? '',
            '{{contract_title}}' => $contract['title'] ?? '',
            '{{contract_type}}' => $contract['type'] ?? '',
            '{{start_date}}' => !empty($contract['start_date']) ? date('d/m/Y', strtotime($contract['start_date'])) : '',
            '{{end_date}}' => !empty($contract['end_date']) ? date('d/m/Y', strtotime($contract['end_date'])) : '',
            '{{payment_method}}' => $pmethods[$contract['payment_method'] ?? ''] ?? ($contract['payment_method'] ?? ''),
            '{{installation_address}}' => $contract['installation_address'] ?? '',
            '{{notes}}' => $contract['notes'] ?? '',
            '{{terms}}' => $contract['terms'] ?? '',
            '{{company_name}}' => $contract['party_a_name'] ?? '',
            '{{company_address}}' => $contract['party_a_address'] ?? '',
            '{{company_phone}}' => $contract['party_a_phone'] ?? '',
            '{{company_tax_code}}' => $contract['party_a_tax_code'] ?? '',
            '{{company_representative}}' => $contract['party_a_representative'] ?? '',
            '{{company_position}}' => $contract['party_a_position'] ?? '',
            '{{company_bank_account}}' => $contract['party_a_bank_account'] ?? '',
            '{{company_bank_name}}' => $contract['party_a_bank_name'] ?? '',
            '{{customer_name}}' => $contract['party_b_name'] ?? '',
            '{{customer_address}}' => $contract['party_b_address'] ?? '',
            '{{customer_phone}}' => $contract['party_b_phone'] ?? '',
            '{{customer_tax_code}}' => $contract['party_b_tax_code'] ?? '',
            '{{customer_representative}}' => $contract['party_b_representative'] ?? '',
            '{{customer_position}}' => $contract['party_b_position'] ?? '',
            '{{customer_bank_account}}' => $contract['party_b_bank_account'] ?? '',
            '{{customer_bank_name}}' => $contract['party_b_bank_name'] ?? '',
            '{{items_table}}' => self::buildItemsTable($items, $summary),
            '{{subtotal}}' => number_format((float)($contract['subtotal'] ?? 0)),
            '{{discount}}' => number_format((float)($contract['discount_amount'] ?? 0)),
            '{{discount_amount}}' => number_format((float)($contract['discount_amount'] ?? 0)),
            '{{vat}}' => number_format((float)($contract['vat_amount'] ?? 0)),
            '{{vat_amount}}' => number_format((float)($contract['vat_amount'] ?? 0)),
            '{{total}}' => number_format((float)($contract['value'] ?? 0)),
            '{{revenue}}' => number_format((float)($contract['value'] ?? 0)),
            '{{w_revenue}}' => self::numberToWords((float)($contract['value'] ?? 0)) . ' đồng',
            '{{today}}' => date('d/m/Y'),
            '{{today_text}}' => 'ngày ' . date('d') . ' tháng ' . date('m') . ' năm ' . date('Y'),
            '{{owner_name}}' => $contract['owner_name'] ?? '',
            '{{sales}}' => number_format((float)($contract['subtotal'] ?? 0)),
            '{{transport}}' => number_format((float)($contract['shipping_fee_percent'] ?? 0), 2),
            '{{transport_amount}}' => number_format((float)($contract['shipping_fee'] ?? 0)),
            '{{discount}}' => number_format((float)($contract['discount_percent'] ?? 0), 2),
            '{{installation}}' => number_format((float)($contract['installation_fee_percent'] ?? 0), 2),
            '{{installation_amount}}' => number_format((float)($contract['installation_fee'] ?? 0)),
            '{{contract_vat}}' => number_format((float)($contract['vat_percent'] ?? 0), 2),
        ];
    }

    /**
     * Build common replacements for orders.
     */
    public static function orderReplacements(array $order, array $items): array
    {
        $pmethods = ['bank_transfer'=>'Chuyển khoản','cash'=>'Tiền mặt','credit_card'=>'Thẻ tín dụng','other'=>'Khác'];
        $dtypes = ['self'=>'Tự giao','partner'=>'Đối tác giao'];

        $cp = Database::fetch(
            "SELECT * FROM company_profiles WHERE tenant_id = ? AND is_default = 1 AND is_active = 1 LIMIT 1",
            [Database::tenantId()]
        ) ?: [];

        $subtotal = (float)($order['subtotal'] ?? 0);
        $total = (float)($order['total'] ?? 0);
        $paid = (float)($order['paid_amount'] ?? 0);
        $remaining = max(0, $total - $paid);

        return [
            '{{order_number}}' => $order['order_number'] ?? '',
            '{{issued_date}}' => !empty($order['issued_date']) ? date('d/m/Y', strtotime($order['issued_date'])) : '',
            '{{due_date}}' => !empty($order['due_date']) ? date('d/m/Y', strtotime($order['due_date'])) : '',
            '{{lading_code}}' => $order['lading_code'] ?? '',
            '{{payment_method}}' => $pmethods[$order['payment_method'] ?? ''] ?? ($order['payment_method'] ?? ''),
            '{{shipping_address}}' => $order['shipping_address'] ?? '',
            '{{shipping_contact}}' => $order['shipping_contact'] ?? '',
            '{{shipping_phone}}' => $order['shipping_phone'] ?? '',
            '{{delivery_type}}' => $dtypes[$order['delivery_type'] ?? 'self'] ?? 'Tự giao',
            '{{delivery_date}}' => !empty($order['delivery_date']) ? date('d/m/Y', strtotime($order['delivery_date'])) : '',
            '{{delivery_partner}}' => $order['delivery_partner'] ?? '',
            '{{delivery_notes}}' => nl2br(htmlspecialchars($order['delivery_notes'] ?? '')),
            '{{notes}}' => nl2br(htmlspecialchars($order['notes'] ?? '')),
            '{{terms}}' => nl2br(htmlspecialchars($order['order_terms'] ?? '')),
            '{{company_name}}' => $cp['name'] ?? ($_SESSION['tenant']['name'] ?? ''),
            '{{company_address}}' => $cp['address'] ?? '',
            '{{company_phone}}' => $cp['phone'] ?? '',
            '{{company_email}}' => $cp['email'] ?? '',
            '{{company_website}}' => $cp['website'] ?? '',
            '{{company_fax}}' => $cp['fax'] ?? '',
            '{{company_tax_code}}' => $cp['tax_code'] ?? '',
            '{{company_representative}}' => $cp['representative'] ?? '',
            '{{company_position}}' => $cp['representative_title'] ?? '',
            '{{company_bank_account}}' => $cp['bank_account'] ?? '',
            '{{company_bank_name}}' => $cp['bank_name'] ?? '',
            '{{company_logo}}' => !empty($cp['logo']) ? '<img src="' . htmlspecialchars($cp['logo']) . '" style="max-height:70px">' : '',
            '{{customer_name}}' => $order['c_company_name'] ?: ($order['c_full_name'] ?? ''),
            '{{customer_address}}' => $order['c_address'] ?? '',
            '{{customer_phone}}' => $order['c_company_phone'] ?: ($order['contact_phone'] ?? ''),
            '{{customer_email}}' => $order['c_company_email'] ?: ($order['contact_email'] ?? ''),
            '{{customer_tax_code}}' => $order['c_tax_code'] ?? '',
            '{{customer_representative}}' => $order['cp_full_name'] ?? '',
            '{{customer_position}}' => $order['cp_position'] ?? '',
            '{{items_table}}' => self::buildOrderItemsTable($items, $order),
            '{{subtotal}}' => number_format($subtotal),
            '{{discount}}' => number_format((float)($order['discount_amount'] ?? 0)),
            '{{discount_amount}}' => number_format((float)($order['discount_amount'] ?? 0)),
            '{{discount_percent}}' => number_format((float)($order['discount_percent'] ?? 0), 2),
            '{{vat}}' => number_format((float)($order['tax_amount'] ?? 0)),
            '{{vat_amount}}' => number_format((float)($order['tax_amount'] ?? 0)),
            '{{vat_percent}}' => number_format((float)($order['tax_rate'] ?? 0), 2),
            '{{total}}' => number_format($total),
            '{{paid_amount}}' => number_format($paid),
            '{{remaining_amount}}' => number_format($remaining),
            '{{transport_amount}}' => number_format((float)($order['transport_amount'] ?? 0)),
            '{{transport_percent}}' => number_format((float)($order['transport_percent'] ?? 0), 2),
            '{{installation_amount}}' => number_format((float)($order['installation_amount'] ?? 0)),
            '{{installation_percent}}' => number_format((float)($order['installation_percent'] ?? 0), 2),
            '{{today}}' => date('d/m/Y'),
            '{{today_text}}' => 'ngày ' . date('d') . ' tháng ' . date('m') . ' năm ' . date('Y'),
            '{{owner_name}}' => $order['owner_name'] ?? '',
            '{{w_total}}' => self::numberToWords($total) . ' đồng',
        ];
    }

    /**
     * Build items table HTML specifically for orders (with SKU + full summary).
     */
    public static function buildOrderItemsTable(array $items, array $order): string
    {
        $total = (float)($order['total'] ?? 0);
        $paid = (float)($order['paid_amount'] ?? 0);

        $html  = '<table style="width:100%;border-collapse:collapse" border="1" cellpadding="6">';
        $html .= '<thead><tr style="background:#f0f0f0;font-weight:bold;text-align:center">'
              . '<th>STT</th><th>Mã SP</th><th>Tên sản phẩm</th><th>ĐVT</th>'
              . '<th>SL</th><th>Giá</th><th>CK(%)</th><th>CK(VND)</th><th>VAT(%)</th><th>Thành tiền</th>'
              . '</tr></thead><tbody>';

        foreach ($items as $i => $it) {
            $html .= '<tr>'
                . '<td style="text-align:center">' . ($i + 1) . '</td>'
                . '<td>' . htmlspecialchars($it['sku'] ?? '') . '</td>'
                . '<td>' . htmlspecialchars($it['product_name'] ?? '') . '</td>'
                . '<td style="text-align:center">' . htmlspecialchars($it['unit'] ?? '') . '</td>'
                . '<td style="text-align:right">' . number_format((float)($it['quantity'] ?? 0), 2) . '</td>'
                . '<td style="text-align:right">' . number_format((float)($it['unit_price'] ?? 0)) . '</td>'
                . '<td style="text-align:right">' . number_format((float)($it['discount_percent'] ?? 0), 2) . '</td>'
                . '<td style="text-align:right">' . number_format((float)($it['discount'] ?? 0)) . '</td>'
                . '<td style="text-align:right">' . number_format((float)($it['tax_rate'] ?? 0), 2) . '</td>'
                . '<td style="text-align:right">' . number_format((float)($it['total'] ?? 0)) . '</td>'
                . '</tr>';
        }

        $row = function($label, $value, $bold = false) {
            $b = $bold ? 'font-weight:bold;' : '';
            return '<tr><td colspan="9" style="text-align:left;' . $b . '">' . $label . '</td>'
                 . '<td style="text-align:right;' . $b . '">' . $value . '</td></tr>';
        };

        $html .= $row('Cộng', number_format((float)($order['subtotal'] ?? 0)), true);
        $html .= $row('Phí vận chuyển (%) sau thuế ' . number_format((float)($order['transport_percent'] ?? 0), 2) . '%', number_format((float)($order['transport_amount'] ?? 0)));
        $html .= $row('Chiết khấu trước thuế ' . number_format((float)($order['discount_percent'] ?? 0), 2) . '%', number_format((float)($order['discount_amount'] ?? 0)));
        $html .= $row('Thuế VAT ' . number_format((float)($order['tax_rate'] ?? 0), 2) . '%', number_format((float)($order['tax_amount'] ?? 0)));
        $html .= $row('Phí lắp đặt ' . number_format((float)($order['installation_percent'] ?? 0), 2) . '%', number_format((float)($order['installation_amount'] ?? 0)));
        $html .= $row('Tổng cộng', number_format($total), true);
        if ($paid > 0) {
            $html .= $row('Đã thanh toán', number_format($paid), true);
            $html .= $row('Còn lại', number_format(max(0, $total - $paid)), true);
        }

        $html .= '</tbody></table>';
        return $html;
    }

    /**
     * Generate PDF from HTML content.
     * @return string PDF binary content
     */
    public static function generatePdf(string $html, string $title = ''): string
    {
        $dompdf = new \Dompdf\Dompdf([
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Serif',
        ]);

        $fullHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8">
            <style>
                body { font-family: "DejaVu Serif", serif; font-size: 13pt; line-height: 1.6; }
                table { border-collapse: collapse; width: 100%; }
                table td, table th { padding: 5px 8px; vertical-align: top; font-size: 11pt; }
                h2 { font-size: 16pt; margin: 10px 0; }
                h3 { font-size: 13pt; margin: 16px 0 8px; }
                p { margin: 4px 0; }
            </style>
            </head><body>' . $html . '</body></html>';

        $dompdf->loadHtml($fullHtml);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Convert number to Vietnamese words.
     */
    public static function numberToWords(float $number): string
    {
        if ($number == 0) return 'không';

        $number = round($number);
        $units = ['', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
        $groups = ['', 'nghìn', 'triệu', 'tỷ', 'nghìn tỷ', 'triệu tỷ'];

        if ($number < 0) return 'âm ' . self::numberToWords(-$number);

        $result = '';
        $groupIndex = 0;

        while ($number > 0) {
            $chunk = $number % 1000;
            if ($chunk > 0) {
                $chunkStr = self::chunkToWords($chunk);
                $result = $chunkStr . ' ' . $groups[$groupIndex] . ' ' . $result;
            }
            $number = intdiv((int)$number, 1000);
            $groupIndex++;
        }

        $text = trim(preg_replace('/\s+/', ' ', $result));
        return mb_strtoupper(mb_substr($text, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($text, 1, null, 'UTF-8');
    }

    private static function chunkToWords(int $n): string
    {
        $units = ['', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];

        $hundreds = intdiv($n, 100);
        $tens = intdiv($n % 100, 10);
        $ones = $n % 10;

        $result = '';
        if ($hundreds > 0) {
            $result .= $units[$hundreds] . ' trăm ';
        }

        if ($tens > 1) {
            $result .= $units[$tens] . ' mươi ';
            if ($ones == 1) $result .= 'mốt';
            elseif ($ones == 5) $result .= 'lăm';
            elseif ($ones > 0) $result .= $units[$ones];
        } elseif ($tens == 1) {
            $result .= 'mười ';
            if ($ones == 5) $result .= 'lăm';
            elseif ($ones > 0) $result .= $units[$ones];
        } elseif ($ones > 0) {
            if ($hundreds > 0) $result .= 'lẻ ';
            $result .= $units[$ones];
        }

        return trim($result);
    }
}
