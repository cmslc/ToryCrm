<?php $noLayout = true; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo giá <?= e($quotation['quote_number']) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 13px; color: #2d3436; line-height: 1.6; }

        .page { padding: 0; }

        /* Header */
        .header { background: linear-gradient(135deg, #405189 0%, #2d3a6e 100%); color: #fff; padding: 30px 40px; display: flex; justify-content: space-between; align-items: center; }
        .header-left h2 { font-size: 22px; margin-bottom: 4px; }
        .header-left p { opacity: 0.8; font-size: 12px; }
        .header-right { text-align: right; }
        .header-right .doc-type { font-size: 28px; font-weight: 700; letter-spacing: 2px; }
        .header-right .doc-number { font-size: 14px; opacity: 0.9; margin-top: 4px; }

        /* Meta info */
        .meta-bar { background: #f8f9fa; padding: 12px 40px; display: flex; justify-content: space-between; border-bottom: 1px solid #e9ecef; font-size: 12px; }
        .meta-bar span { color: #666; }
        .meta-bar strong { color: #333; }

        /* Info blocks */
        .info-section { padding: 25px 40px; display: flex; justify-content: space-between; gap: 40px; }
        .info-block { flex: 1; }
        .info-block .label { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #999; font-weight: 600; margin-bottom: 8px; }
        .info-block .name { font-size: 16px; font-weight: 700; color: #2d3436; margin-bottom: 4px; }
        .info-block .detail { font-size: 12px; color: #636e72; line-height: 1.8; }

        /* Title */
        .title-bar { padding: 0 40px 15px; }
        .title-bar h3 { font-size: 15px; color: #405189; border-left: 3px solid #405189; padding-left: 10px; }

        /* Table */
        .items-table { padding: 0 40px; }
        table { width: 100%; border-collapse: collapse; }
        thead tr { background: #405189; color: #fff; }
        th { padding: 10px 12px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
        td { padding: 10px 12px; border-bottom: 1px solid #f0f0f0; font-size: 12px; }
        tbody tr:nth-child(even) { background: #fafbfc; }
        tbody tr:hover { background: #f0f4ff; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }

        /* Totals */
        .totals { padding: 15px 40px; display: flex; justify-content: flex-end; }
        .totals-box { width: 300px; }
        .totals-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 13px; }
        .totals-row.discount { color: #e17055; }
        .totals-row.grand { background: #405189; color: #fff; padding: 12px 16px; margin-top: 8px; font-size: 16px; font-weight: 700; border-radius: 6px; }

        /* Notes */
        .notes-section { padding: 20px 40px; }
        .note-box { background: #f8f9fa; border-left: 3px solid #405189; padding: 12px 16px; margin-bottom: 12px; border-radius: 0 6px 6px 0; }
        .note-box .note-title { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #999; font-weight: 600; margin-bottom: 4px; }
        .note-box p { font-size: 12px; color: #555; }

        /* Signatures */
        .signatures { padding: 40px 40px 20px; display: flex; justify-content: space-between; }
        .sig-block { text-align: center; width: 200px; }
        .sig-block .sig-label { font-size: 11px; text-transform: uppercase; color: #999; font-weight: 600; }
        .sig-block .sig-line { border-bottom: 1px solid #ccc; height: 60px; margin: 10px 0; }
        .sig-block .sig-name { font-size: 12px; font-weight: 600; color: #333; }

        /* Footer */
        .footer { text-align: center; padding: 15px 40px; border-top: 1px solid #eee; color: #aaa; font-size: 10px; }

        @page { margin: 10mm 15mm; }
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; padding: 0; }
            .page { page-break-after: avoid; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; break-inside: avoid; }
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
            h1, h2, h3, h4 { page-break-after: avoid; break-after: avoid; }
            .sig-block, .footer { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
<div class="page">

    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <?php if (!empty($branding['logo_url'])): ?>
            <img src="<?= url($branding['logo_url']) ?>" alt="" style="max-height:50px;margin-bottom:8px">
            <?php endif; ?>
            <h2><?= e($branding['name'] ?? 'ToryCRM') ?></h2>
            <?php if (!empty($branding['address'])): ?><p>Trụ sở: <?= e($branding['address']) ?></p><?php endif; ?>
            <?php if (!empty($branding['branch_address'])): ?><p>Chi nhánh: <?= e($branding['branch_address']) ?></p><?php endif; ?>
            <p>
                <?php if (!empty($branding['phone'])): ?>Hotline: <?= e($branding['phone']) ?><?php endif; ?>
                <?php if (!empty($branding['fax'])): ?> · Fax: <?= e($branding['fax']) ?><?php endif; ?>
            </p>
            <p>
                <?php if (!empty($branding['email'])): ?>Email: <?= e($branding['email']) ?><?php endif; ?>
                <?php if (!empty($branding['website'])): ?> · <?= e($branding['website']) ?><?php endif; ?>
            </p>
            <?php if (!empty($branding['tax_code'])): ?><p>MST: <?= e($branding['tax_code']) ?></p><?php endif; ?>
        </div>
        <div class="header-right">
            <div class="doc-type">BÁO GIÁ</div>
            <div class="doc-number"><?= e($quotation['quote_number']) ?></div>
        </div>
    </div>

    <!-- Meta bar -->
    <div class="meta-bar">
        <span>Ngày: <strong><?= date('d/m/Y', strtotime($quotation['created_at'])) ?></strong></span>
        <?php if ($quotation['valid_until']): ?>
        <span>Hiệu lực đến: <strong><?= date('d/m/Y', strtotime($quotation['valid_until'])) ?></strong></span>
        <?php endif; ?>
        <span>Người phụ trách: <strong><?= e($quotation['owner_name'] ?? '-') ?></strong></span>
    </div>

    <!-- Info -->
    <div class="info-section">
        <div class="info-block">
            <div class="label">Khách hàng</div>
            <?php
            $pdfName = $quotation['c_company_name'] ?? ($quotation['c_full_name'] ?? trim(($quotation['contact_first_name'] ?? '') . ' ' . ($quotation['contact_last_name'] ?? '')));
            $pdfPhone = $quotation['contact_phone'] ?? ($quotation['c_company_phone'] ?? $quotation['c_phone'] ?? '');
            $pdfEmail = $quotation['contact_email'] ?? ($quotation['c_company_email'] ?? $quotation['c_email'] ?? '');
            $pdfAddress = $quotation['address'] ?? ($quotation['c_address'] ?? '');
            $pdfTax = $quotation['c_tax_code'] ?? '';
            ?>
            <div class="name"><?= e($pdfName) ?></div>
            <div class="detail">
                <?php if ($pdfTax): ?>MST: <?= e($pdfTax) ?><br><?php endif; ?>
                <?php if ($pdfAddress): ?><?= e($pdfAddress) ?><br><?php endif; ?>
                <?php if ($pdfPhone): ?>ĐT: <?= e($pdfPhone) ?><br><?php endif; ?>
                <?php if ($pdfEmail): ?>Email: <?= e($pdfEmail) ?><?php endif; ?>
            </div>
        </div>
        <div class="info-block" style="text-align:right">
            <div class="label">Người tạo báo giá</div>
            <div class="name"><?= e($quotation['owner_name'] ?? '-') ?></div>
            <div class="detail">
                <?php if (!empty($quotation['owner_email'])): ?>Email: <?= e($quotation['owner_email']) ?><br><?php endif; ?>
                <?php if (!empty($quotation['owner_phone'])): ?>ĐT: <?= e($quotation['owner_phone']) ?><br><?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Items -->
    <div class="items-table">
        <table>
            <thead>
                <tr>
                    <th width="5%" class="text-center">#</th>
                    <th width="35%">Sản phẩm / Dịch vụ</th>
                    <th width="8%" class="text-center">SL</th>
                    <th width="8%">ĐVT</th>
                    <th width="15%" class="text-end">Đơn giá</th>
                    <th width="8%" class="text-center">VAT</th>
                    <th width="17%" class="text-end">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $i => $item): ?>
                <tr>
                    <td class="text-center"><?= $i + 1 ?></td>
                    <td>
                        <strong><?= e($item['product_name']) ?></strong>
                        <?php if (!empty($item['description'])): ?>
                        <br><span style="color:#888;font-size:11px"><?= e($item['description']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?= $item['quantity'] ?></td>
                    <td><?= e($item['unit'] ?? 'Cái') ?></td>
                    <td class="text-end"><?= number_format($item['unit_price'], 0, ',', '.') ?></td>
                    <td class="text-center"><?= $item['tax_rate'] ?>%</td>
                    <td class="text-end"><strong><?= number_format($item['total'], 0, ',', '.') ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Totals -->
    <div class="totals">
        <div class="totals-box">
            <div class="totals-row">
                <span>Tạm tính:</span>
                <strong><?= number_format($quotation['subtotal'] ?? 0, 0, ',', '.') ?> ₫</strong>
            </div>
            <div class="totals-row">
                <span>Thuế VAT:</span>
                <span><?= number_format($quotation['tax_amount'] ?? 0, 0, ',', '.') ?> ₫</span>
            </div>
            <?php if (($quotation['discount_amount'] ?? 0) > 0): ?>
            <div class="totals-row discount">
                <span>Chiết khấu:</span>
                <span>-<?= number_format($quotation['discount_amount'], 0, ',', '.') ?> ₫</span>
            </div>
            <?php endif; ?>
            <?php if (($quotation['shipping_fee'] ?? 0) > 0): ?>
            <div class="totals-row">
                <span>Phí vận chuyển<?= !empty($quotation['shipping_note']) ? ' (' . e($quotation['shipping_note']) . ')' : '' ?>:</span>
                <span><?= number_format($quotation['shipping_fee'], 0, ',', '.') ?> ₫</span>
            </div>
            <?php endif; ?>
            <?php if (($quotation['installation_fee'] ?? 0) > 0): ?>
            <div class="totals-row">
                <span>Phí lắp đặt:</span>
                <span><?= number_format($quotation['installation_fee'], 0, ',', '.') ?> ₫</span>
            </div>
            <?php endif; ?>
            <div class="totals-row grand">
                <span>TỔNG CỘNG</span>
                <span><?= number_format($quotation['total'] ?? 0, 0, ',', '.') ?> ₫</span>
            </div>
        </div>
    </div>

    <!-- Nội dung điều khoản -->
    <?php if ($quotation['content'] ?? null): ?>
    <div class="notes-section">
        <div class="note-box">
            <div class="note-title">Nội dung điều khoản</div>
            <div style="font-size:12px;color:#555"><?= $quotation['content'] ?></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Notes & Terms -->
    <?php if (($quotation['notes'] ?? null) || ($quotation['terms'] ?? null)): ?>
    <div class="notes-section">
        <?php if ($quotation['notes']): ?>
        <div class="note-box">
            <div class="note-title">Ghi chú</div>
            <p><?= nl2br(e($quotation['notes'])) ?></p>
        </div>
        <?php endif; ?>
        <?php if ($quotation['terms']): ?>
        <div class="note-box">
            <div class="note-title">Điều khoản & Điều kiện</div>
            <p><?= nl2br(e($quotation['terms'])) ?></p>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Bank info -->
    <?php if (!empty($branding['bank_account'])): ?>
    <div class="notes-section">
        <div class="note-box">
            <div class="note-title">Thông tin thanh toán</div>
            <p>
                Số TK: <strong><?= e($branding['bank_account']) ?></strong><br>
                Ngân hàng: <strong><?= e($branding['bank_name'] ?? '') ?></strong><br>
                Chủ TK: <strong><?= e($branding['name'] ?? '') ?></strong>
            </p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="footer">
        Báo giá <?= e($quotation['quote_number']) ?> · Ngày <?= date('d/m/Y', strtotime($quotation['created_at'])) ?>
    </div>

</div>

<script>window.onload = function() { window.print(); }</script>
</body>
</html>
