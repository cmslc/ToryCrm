<?php $noLayout = true; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo giá <?= e($quotation['quote_number']) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 13px; color: #333; padding: 30px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #405189; padding-bottom: 15px; margin-bottom: 20px; }
        .header-left h2 { color: #405189; margin-bottom: 5px; }
        .header-right { text-align: right; }
        .header-right h1 { color: #405189; font-size: 28px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .info-block h4 { color: #666; font-size: 11px; text-transform: uppercase; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f3f3f9; padding: 10px 8px; text-align: left; font-size: 12px; border-bottom: 2px solid #ddd; }
        td { padding: 8px; border-bottom: 1px solid #eee; }
        .text-end { text-align: right; }
        .text-bold { font-weight: bold; }
        .total-row { font-size: 16px; color: #405189; }
        .notes { background: #f9f9f9; padding: 12px; border-radius: 4px; margin-bottom: 15px; }
        .notes h4 { font-size: 12px; color: #666; margin-bottom: 5px; }
        .footer { text-align: center; color: #999; font-size: 11px; margin-top: 30px; padding-top: 15px; border-top: 1px solid #eee; }
        @media print {
            body { padding: 15px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <h2><?= e($branding['name'] ?? 'ToryCRM') ?></h2>
        </div>
        <div class="header-right">
            <h1>BÁO GIÁ</h1>
            <p style="color:#666"><?= e($quotation['quote_number']) ?></p>
            <p>Ngày: <?= date('d/m/Y', strtotime($quotation['created_at'])) ?></p>
            <?php if ($quotation['valid_until']): ?>
                <p>Hiệu lực đến: <?= date('d/m/Y', strtotime($quotation['valid_until'])) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="info-row">
        <div class="info-block">
            <h4>Gửi đến</h4>
            <?php if ($quotation['contact_first_name']): ?>
                <p class="text-bold"><?= e(trim($quotation['contact_first_name'] . ' ' . ($quotation['contact_last_name'] ?? ''))) ?></p>
            <?php endif; ?>
            <?php if ($quotation['company_name']): ?>
                <p><?= e($quotation['company_name']) ?></p>
            <?php endif; ?>
            <?php if ($quotation['company_address']): ?>
                <p><?= e($quotation['company_address']) ?></p>
            <?php endif; ?>
            <?php if ($quotation['contact_email']): ?>
                <p><?= e($quotation['contact_email']) ?></p>
            <?php endif; ?>
            <?php if ($quotation['contact_phone']): ?>
                <p><?= e($quotation['contact_phone']) ?></p>
            <?php endif; ?>
        </div>
        <div class="info-block" style="text-align:right">
            <h4>Người phụ trách</h4>
            <p><?= e($quotation['owner_name'] ?? '-') ?></p>
        </div>
    </div>

    <?php if ($quotation['title']): ?>
        <p style="margin-bottom:15px"><strong>Tiêu đề:</strong> <?= e($quotation['title']) ?></p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="35%">Sản phẩm / Dịch vụ</th>
                <th class="text-end" width="10%">SL</th>
                <th width="8%">ĐVT</th>
                <th class="text-end" width="15%">Đơn giá</th>
                <th class="text-end" width="8%">Thuế</th>
                <th class="text-end" width="17%">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $i => $item): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= e($item['product_name']) ?></td>
                <td class="text-end"><?= $item['quantity'] ?></td>
                <td><?= e($item['unit']) ?></td>
                <td class="text-end"><?= number_format($item['unit_price'], 0, ',', '.') ?> ₫</td>
                <td class="text-end"><?= $item['tax_rate'] ?>%</td>
                <td class="text-end"><?= number_format($item['total'], 0, ',', '.') ?> ₫</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-end">Tạm tính:</td>
                <td class="text-end text-bold"><?= number_format($quotation['subtotal'] ?? 0, 0, ',', '.') ?> ₫</td>
            </tr>
            <tr>
                <td colspan="6" class="text-end">Thuế:</td>
                <td class="text-end"><?= number_format($quotation['tax_amount'] ?? 0, 0, ',', '.') ?> ₫</td>
            </tr>
            <?php if (($quotation['discount_amount'] ?? 0) > 0): ?>
            <tr>
                <td colspan="6" class="text-end">Giảm giá:</td>
                <td class="text-end" style="color:red">-<?= number_format($quotation['discount_amount'], 0, ',', '.') ?> ₫</td>
            </tr>
            <?php endif; ?>
            <tr class="total-row">
                <td colspan="6" class="text-end text-bold" style="font-size:16px">TỔNG CỘNG:</td>
                <td class="text-end text-bold" style="font-size:16px"><?= number_format($quotation['total'] ?? 0, 0, ',', '.') ?> ₫</td>
            </tr>
        </tfoot>
    </table>

    <?php if ($quotation['notes']): ?>
    <div class="notes">
        <h4>Ghi chú</h4>
        <p><?= nl2br(e($quotation['notes'])) ?></p>
    </div>
    <?php endif; ?>

    <?php if ($quotation['terms']): ?>
    <div class="notes">
        <h4>Điều khoản</h4>
        <p><?= nl2br(e($quotation['terms'])) ?></p>
    </div>
    <?php endif; ?>

    <div class="footer">
        <p>Tạo bởi <?= e($branding['name'] ?? 'ToryCRM') ?></p>
    </div>

    <script>window.onload = function() { window.print(); }</script>
</body>
</html>
