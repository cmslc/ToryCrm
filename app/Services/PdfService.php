<?php

namespace App\Services;

use Core\Database;

/**
 * Simple HTML-to-PDF service using browser's print capability.
 * Generates a printable HTML page that can be saved as PDF via Ctrl+P.
 *
 * For server-side PDF generation, install dompdf:
 * composer require dompdf/dompdf
 */
class PdfService
{
    /**
     * Render a printable HTML page for an order/invoice
     */
    public static function orderHtml(array $order, array $items): string
    {
        $isQuote = ($order['type'] ?? 'order') === 'quote';
        $title = ($isQuote ? 'BÁO GIÁ' : 'ĐƠN HÀNG') . ' ' . ($order['order_number'] ?? '');

        $tenant = $_SESSION['tenant'] ?? ['name' => 'ToryCRM'];

        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="vi">
        <head>
            <meta charset="UTF-8">
            <title><?= htmlspecialchars($title) ?></title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px; color: #333; padding: 40px; }
                .header { display: flex; justify-content: space-between; margin-bottom: 30px; border-bottom: 3px solid #405189; padding-bottom: 20px; }
                .company-name { font-size: 24px; font-weight: 700; color: #405189; }
                .doc-title { font-size: 22px; font-weight: 700; color: #405189; text-align: right; }
                .doc-number { font-size: 14px; color: #666; text-align: right; }
                .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px; }
                .info-box h4 { font-size: 12px; text-transform: uppercase; color: #888; margin-bottom: 8px; letter-spacing: 0.5px; }
                .info-box p { margin-bottom: 4px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th { background: #405189; color: #fff; padding: 10px 12px; text-align: left; font-size: 12px; text-transform: uppercase; }
                td { padding: 10px 12px; border-bottom: 1px solid #e9ebec; }
                tr:nth-child(even) { background: #f8f9fa; }
                .text-right { text-align: right; }
                .totals { margin-left: auto; width: 300px; }
                .totals tr td { padding: 6px 12px; border: none; }
                .totals .grand-total td { font-size: 16px; font-weight: 700; color: #405189; border-top: 2px solid #405189; }
                .footer { margin-top: 40px; display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
                .sign-box { text-align: center; padding-top: 60px; }
                .sign-label { font-weight: 600; margin-bottom: 60px; }
                .notes { margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 4px; }
                @media print {
                    body { padding: 20px; }
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="no-print" style="text-align:center;margin-bottom:20px">
                <button onclick="window.print()" style="padding:10px 30px;background:#405189;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:14px">
                    In / Lưu PDF
                </button>
            </div>

            <div class="header">
                <div>
                    <div class="company-name"><?= htmlspecialchars($tenant['name'] ?? 'ToryCRM') ?></div>
                </div>
                <div>
                    <div class="doc-title"><?= $isQuote ? 'BÁO GIÁ' : 'ĐƠN HÀNG' ?></div>
                    <div class="doc-number"><?= htmlspecialchars($order['order_number'] ?? '') ?></div>
                    <div class="doc-number">Ngày: <?= !empty($order['issued_date']) ? date('d/m/Y', strtotime($order['issued_date'])) : date('d/m/Y') ?></div>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-box">
                    <h4>Khách hàng</h4>
                    <p><strong><?= htmlspecialchars(trim(($order['contact_first_name'] ?? '') . ' ' . ($order['contact_last_name'] ?? '')) ?: '-') ?></strong></p>
                    <?php if (!empty($order['company_name'])): ?><p><?= htmlspecialchars($order['company_name']) ?></p><?php endif; ?>
                    <?php if (!empty($order['contact_email'])): ?><p><?= htmlspecialchars($order['contact_email']) ?></p><?php endif; ?>
                    <?php if (!empty($order['contact_phone'])): ?><p><?= htmlspecialchars($order['contact_phone']) ?></p><?php endif; ?>
                </div>
                <div class="info-box">
                    <h4>Thông tin đơn</h4>
                    <p>Hạn TT: <?= !empty($order['due_date']) ? date('d/m/Y', strtotime($order['due_date'])) : '-' ?></p>
                    <p>Phụ trách: <?= htmlspecialchars($order['owner_name'] ?? '-') ?></p>
                    <p>Phương thức: <?= htmlspecialchars($order['payment_method'] ?? '-') ?></p>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Sản phẩm / Dịch vụ</th>
                        <th class="text-right">SL</th>
                        <th>ĐVT</th>
                        <th class="text-right">Đơn giá</th>
                        <th class="text-right">Thuế</th>
                        <th class="text-right">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $i => $item): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($item['product_name'] ?? '') ?></td>
                        <td class="text-right"><?= $item['quantity'] ?? 0 ?></td>
                        <td><?= htmlspecialchars($item['unit'] ?? '') ?></td>
                        <td class="text-right"><?= number_format((float)($item['unit_price'] ?? 0), 0, ',', '.') ?></td>
                        <td class="text-right"><?= ($item['tax_rate'] ?? 0) ?>%</td>
                        <td class="text-right"><?= number_format((float)($item['total'] ?? 0), 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <table class="totals">
                <tr><td>Tạm tính:</td><td class="text-right"><?= number_format((float)($order['subtotal'] ?? 0), 0, ',', '.') ?> đ</td></tr>
                <tr><td>Thuế:</td><td class="text-right"><?= number_format((float)($order['tax_amount'] ?? 0), 0, ',', '.') ?> đ</td></tr>
                <?php if (($order['discount_amount'] ?? 0) > 0): ?>
                <tr><td>Giảm giá:</td><td class="text-right">-<?= number_format((float)$order['discount_amount'], 0, ',', '.') ?> đ</td></tr>
                <?php endif; ?>
                <tr class="grand-total"><td>TỔNG CỘNG:</td><td class="text-right"><?= number_format((float)($order['total'] ?? 0), 0, ',', '.') ?> đ</td></tr>
            </table>

            <?php if (!empty($order['notes'])): ?>
            <div class="notes"><strong>Ghi chú:</strong> <?= nl2br(htmlspecialchars($order['notes'])) ?></div>
            <?php endif; ?>

            <div class="footer">
                <div class="sign-box">
                    <div class="sign-label">Khách hàng</div>
                    <p>(Ký, ghi rõ họ tên)</p>
                </div>
                <div class="sign-box">
                    <div class="sign-label">Người lập</div>
                    <p>(Ký, ghi rõ họ tên)</p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Render a printable HTML page for a fund transaction (phiếu thu/chi)
     */
    public static function fundTransactionHtml(array $transaction): string
    {
        $isReceipt = $transaction['type'] === 'receipt';
        $title = ($isReceipt ? 'PHIẾU THU' : 'PHIẾU CHI') . ' ' . $transaction['transaction_code'];
        $tenant = $_SESSION['tenant'] ?? ['name' => 'ToryCRM'];

        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="vi">
        <head>
            <meta charset="UTF-8">
            <title><?= htmlspecialchars($title) ?></title>
            <style>
                body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 14px; padding: 40px; max-width: 800px; margin: 0 auto; }
                .header { text-align: center; margin-bottom: 30px; }
                .company { font-size: 18px; font-weight: 700; color: #405189; }
                .doc-title { font-size: 24px; font-weight: 700; margin: 15px 0; }
                .doc-number { color: #666; }
                .info { margin: 20px 0; }
                .info table { width: 100%; }
                .info td { padding: 8px 0; border-bottom: 1px dotted #ddd; }
                .info td:first-child { font-weight: 600; width: 180px; }
                .amount-box { text-align: center; margin: 30px 0; padding: 20px; background: <?= $isReceipt ? '#e8f5e9' : '#ffebee' ?>; border-radius: 8px; }
                .amount { font-size: 32px; font-weight: 700; color: <?= $isReceipt ? '#2e7d32' : '#c62828' ?>; }
                .signatures { display: flex; justify-content: space-between; margin-top: 50px; text-align: center; }
                .sig-box { width: 200px; }
                .sig-label { font-weight: 600; margin-bottom: 80px; }
                .no-print { text-align: center; margin-bottom: 20px; }
                @media print { .no-print { display: none; } }
            </style>
        </head>
        <body>
            <div class="no-print">
                <button onclick="window.print()" style="padding:10px 30px;background:#405189;color:#fff;border:none;border-radius:4px;cursor:pointer">In / Lưu PDF</button>
            </div>

            <div class="header">
                <div class="company"><?= htmlspecialchars($tenant['name'] ?? 'ToryCRM') ?></div>
                <div class="doc-title"><?= $isReceipt ? 'PHIẾU THU' : 'PHIẾU CHI' ?></div>
                <div class="doc-number"><?= htmlspecialchars($transaction['transaction_code']) ?></div>
                <div class="doc-number">Ngày: <?= date('d/m/Y', strtotime($transaction['transaction_date'])) ?></div>
            </div>

            <div class="amount-box">
                <div>Số tiền</div>
                <div class="amount"><?= number_format((float)$transaction['amount'], 0, ',', '.') ?> đ</div>
            </div>

            <div class="info">
                <table>
                    <tr><td>Quỹ:</td><td><?= htmlspecialchars($transaction['fund_account_name'] ?? '-') ?></td></tr>
                    <tr><td>Danh mục:</td><td><?= htmlspecialchars($transaction['category'] ?? '-') ?></td></tr>
                    <tr><td>Nội dung:</td><td><?= htmlspecialchars($transaction['description'] ?? '-') ?></td></tr>
                    <?php if (!empty($transaction['contact_first_name'])): ?>
                    <tr><td>Khách hàng:</td><td><?= htmlspecialchars($transaction['contact_first_name'] . ' ' . ($transaction['contact_last_name'] ?? '')) ?></td></tr>
                    <?php endif; ?>
                    <?php if (!empty($transaction['company_name'])): ?>
                    <tr><td>Công ty:</td><td><?= htmlspecialchars($transaction['company_name']) ?></td></tr>
                    <?php endif; ?>
                    <tr><td>Người tạo:</td><td><?= htmlspecialchars($transaction['created_by_name'] ?? '-') ?></td></tr>
                    <tr><td>Trạng thái:</td><td><?= $transaction['status'] === 'confirmed' ? 'Đã xác nhận' : 'Nháp' ?></td></tr>
                </table>
            </div>

            <div class="signatures">
                <div class="sig-box"><div class="sig-label">Người nộp</div><p>(Ký, họ tên)</p></div>
                <div class="sig-box"><div class="sig-label">Người lập</div><p>(Ký, họ tên)</p></div>
                <div class="sig-box"><div class="sig-label">Kế toán</div><p>(Ký, họ tên)</p></div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}
