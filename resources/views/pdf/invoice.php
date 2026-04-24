<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>HÓA ĐƠN <?= htmlspecialchars($order['order_number'] ?? '') ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px; color: #333; padding: 40px; max-width: 800px; margin: 0 auto; }
        .no-print { text-align: center; margin-bottom: 20px; }
        .print-btn { padding: 10px 30px; background: #405189; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; border-bottom: 3px solid #405189; padding-bottom: 20px; }
        .company-info { max-width: 50%; }
        .company-name { font-size: 24px; font-weight: 700; color: #405189; margin-bottom: 5px; }
        .company-details { font-size: 12px; color: #666; line-height: 1.6; }
        .doc-header { text-align: right; }
        .doc-title { font-size: 26px; font-weight: 700; color: #405189; letter-spacing: 2px; }
        .doc-number { font-size: 14px; color: #666; margin-top: 4px; }
        .invoice-meta { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .meta-box { width: 48%; }
        .meta-box h4 { font-size: 11px; text-transform: uppercase; color: #888; margin-bottom: 8px; letter-spacing: 1px; border-bottom: 1px solid #e9ebec; padding-bottom: 4px; }
        .meta-box p { margin-bottom: 3px; font-size: 13px; }
        .meta-box .name { font-weight: 700; font-size: 15px; color: #333; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th { background: #405189; color: #fff; padding: 10px 12px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
        table.items td { padding: 10px 12px; border-bottom: 1px solid #e9ebec; font-size: 13px; }
        table.items tr:nth-child(even) { background: #f8f9fa; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals-section { display: flex; justify-content: flex-end; margin-bottom: 30px; }
        table.totals { width: 320px; }
        table.totals td { padding: 6px 12px; border: none; font-size: 13px; }
        table.totals .grand-total td { font-size: 18px; font-weight: 700; color: #405189; border-top: 2px solid #405189; padding-top: 10px; }
        .payment-info { background: #f0f4ff; border-radius: 6px; padding: 15px 20px; margin-bottom: 30px; }
        .payment-info h4 { font-size: 12px; text-transform: uppercase; color: #405189; margin-bottom: 8px; }
        .payment-info p { margin-bottom: 3px; }
        .terms { margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 4px; border-left: 3px solid #405189; }
        .terms h4 { font-size: 12px; text-transform: uppercase; color: #666; margin-bottom: 6px; }
        .terms p { font-size: 12px; color: #666; line-height: 1.6; }
        .footer-sign { display: flex; justify-content: space-between; margin-top: 50px; }
        .sign-box { text-align: center; width: 200px; }
        .sign-label { font-weight: 600; margin-bottom: 70px; font-size: 13px; }
        .sign-line { border-top: 1px dotted #999; padding-top: 5px; font-size: 12px; color: #666; }
        @media print {
            body { padding: 20px; }
            .no-print { display: none !important; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; break-inside: avoid; }
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
            h1, h2, h3, h4 { page-break-after: avoid; break-after: avoid; }
            .footer-sign { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="print-btn"><i class="ri-printer-line"></i> In / Lưu PDF</button>
        <a href="<?= htmlspecialchars($_SERVER['HTTP_REFERER'] ?? '/orders') ?>" style="margin-left:10px;color:#405189">Quay lại</a>
    </div>

    <div class="header">
        <div class="company-info">
            <div class="company-name"><?= htmlspecialchars($tenant['name'] ?? 'ToryCRM') ?></div>
            <div class="company-details">
                <?php if (!empty($tenant['address'])): ?><?= htmlspecialchars($tenant['address']) ?><br><?php endif; ?>
                <?php if (!empty($tenant['phone'])): ?>ĐT: <?= htmlspecialchars($tenant['phone']) ?><br><?php endif; ?>
                <?php if (!empty($tenant['email'])): ?>Email: <?= htmlspecialchars($tenant['email']) ?><br><?php endif; ?>
                <?php if (!empty($tenant['tax_code'])): ?>MST: <?= htmlspecialchars($tenant['tax_code']) ?><?php endif; ?>
            </div>
        </div>
        <div class="doc-header">
            <div class="doc-title">HÓA ĐƠN</div>
            <div class="doc-number"><?= htmlspecialchars($order['order_number'] ?? '') ?></div>
            <div class="doc-number">Ngày: <?= !empty($order['issued_date']) ? date('d/m/Y', strtotime($order['issued_date'])) : date('d/m/Y') ?></div>
            <?php if (!empty($order['due_date'])): ?>
                <div class="doc-number">Hạn TT: <?= date('d/m/Y', strtotime($order['due_date'])) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="invoice-meta">
        <div class="meta-box">
            <h4>Khách hàng</h4>
            <p class="name"><?= htmlspecialchars(trim(($order['contact_first_name'] ?? '') . ' ' . ($order['contact_last_name'] ?? '')) ?: '-') ?></p>
            <?php if (!empty($order['company_name'])): ?><p><?= htmlspecialchars($order['company_name']) ?></p><?php endif; ?>
            <?php if (!empty($order['contact_email'])): ?><p>Email: <?= htmlspecialchars($order['contact_email']) ?></p><?php endif; ?>
            <?php if (!empty($order['contact_phone'])): ?><p>ĐT: <?= htmlspecialchars($order['contact_phone']) ?></p><?php endif; ?>
            <?php if (!empty($order['contact_address'])): ?><p>ĐC: <?= htmlspecialchars($order['contact_address']) ?></p><?php endif; ?>
        </div>
        <div class="meta-box">
            <h4>Thông tin thanh toán</h4>
            <p>Phụ trách: <?= htmlspecialchars($order['owner_name'] ?? '-') ?></p>
            <p>Phương thức: <?= htmlspecialchars($order['payment_method'] ?? '-') ?></p>
            <p>Trạng thái TT: <?php
                $ps = $order['payment_status'] ?? '';
                $psLabels = ['unpaid' => 'Chưa thanh toán', 'partial' => 'Thanh toán một phần', 'paid' => 'Đã thanh toán'];
                echo $psLabels[$ps] ?? $ps;
            ?></p>
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th style="width:30px">#</th>
                <th>Sản phẩm / Dịch vụ</th>
                <th class="text-center" style="width:60px">SL</th>
                <th style="width:50px">ĐVT</th>
                <th class="text-right" style="width:100px">Đơn giá</th>
                <th class="text-right" style="width:60px">Thuế</th>
                <th class="text-right" style="width:110px">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $i => $item): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($item['product_name'] ?? '') ?></td>
                <td class="text-center"><?= $item['quantity'] ?? 0 ?></td>
                <td><?= htmlspecialchars($item['unit'] ?? '') ?></td>
                <td class="text-right"><?= number_format((float)($item['unit_price'] ?? 0), 0, ',', '.') ?></td>
                <td class="text-right"><?= ($item['tax_rate'] ?? 0) ?>%</td>
                <td class="text-right"><?= number_format((float)($item['total'] ?? 0), 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totals-section">
        <table class="totals">
            <tr><td>Tạm tính:</td><td class="text-right"><?= number_format((float)($order['subtotal'] ?? 0), 0, ',', '.') ?> đ</td></tr>
            <tr><td>Thuế:</td><td class="text-right"><?= number_format((float)($order['tax_amount'] ?? 0), 0, ',', '.') ?> đ</td></tr>
            <?php if (($order['discount_amount'] ?? 0) > 0): ?>
            <tr><td>Giảm giá:</td><td class="text-right">-<?= number_format((float)$order['discount_amount'], 0, ',', '.') ?> đ</td></tr>
            <?php endif; ?>
            <tr class="grand-total"><td>TỔNG CỘNG:</td><td class="text-right"><?= number_format((float)($order['total'] ?? 0), 0, ',', '.') ?> đ</td></tr>
        </table>
    </div>

    <?php if (!empty($bankInfo)): ?>
    <div class="payment-info">
        <h4>Thông tin chuyển khoản</h4>
        <?php if (!empty($bankInfo['bank_name'])): ?><p>Ngân hàng: <strong><?= htmlspecialchars($bankInfo['bank_name']) ?></strong></p><?php endif; ?>
        <?php if (!empty($bankInfo['account_number'])): ?><p>Số TK: <strong><?= htmlspecialchars($bankInfo['account_number']) ?></strong></p><?php endif; ?>
        <?php if (!empty($bankInfo['account_name'])): ?><p>Chủ TK: <strong><?= htmlspecialchars($bankInfo['account_name']) ?></strong></p><?php endif; ?>
        <p>Nội dung CK: <strong><?= htmlspecialchars($order['order_number'] ?? '') ?></strong></p>
    </div>
    <?php endif; ?>

    <?php if (!empty($order['notes'])): ?>
    <div class="terms">
        <h4>Ghi chú</h4>
        <p><?= nl2br(htmlspecialchars($order['notes'])) ?></p>
    </div>
    <?php endif; ?>

    <div class="terms">
        <h4>Điều khoản thanh toán</h4>
        <p>Vui lòng thanh toán trước ngày hết hạn. Đơn hàng quá hạn có thể chịu phí phạt chậm thanh toán theo quy định.</p>
    </div>

    <div class="footer-sign">
        <div class="sign-box">
            <div class="sign-label">Khách hàng</div>
            <div class="sign-line">(Ký, ghi rõ họ tên)</div>
        </div>
        <div class="sign-box">
            <div class="sign-label">Người lập</div>
            <div class="sign-line">(Ký, ghi rõ họ tên)</div>
        </div>
    </div>
</body>
</html>
