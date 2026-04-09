<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>BÁO GIÁ <?= htmlspecialchars($order['order_number'] ?? '') ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px; color: #333; padding: 40px; max-width: 800px; margin: 0 auto; }
        .no-print { text-align: center; margin-bottom: 20px; }
        .print-btn { padding: 10px 30px; background: #0ab39c; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; border-bottom: 3px solid #0ab39c; padding-bottom: 20px; }
        .company-info { max-width: 50%; }
        .company-name { font-size: 24px; font-weight: 700; color: #0ab39c; margin-bottom: 5px; }
        .company-details { font-size: 12px; color: #666; line-height: 1.6; }
        .doc-header { text-align: right; }
        .doc-title { font-size: 26px; font-weight: 700; color: #0ab39c; letter-spacing: 2px; }
        .doc-number { font-size: 14px; color: #666; margin-top: 4px; }
        .validity { display: inline-block; background: #e8faf7; color: #0ab39c; padding: 4px 12px; border-radius: 4px; font-weight: 600; margin-top: 6px; font-size: 12px; }
        .info-grid { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .info-box { width: 48%; }
        .info-box h4 { font-size: 11px; text-transform: uppercase; color: #888; margin-bottom: 8px; letter-spacing: 1px; border-bottom: 1px solid #e9ebec; padding-bottom: 4px; }
        .info-box p { margin-bottom: 3px; font-size: 13px; }
        .info-box .name { font-weight: 700; font-size: 15px; color: #333; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th { background: #0ab39c; color: #fff; padding: 10px 12px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
        table.items td { padding: 10px 12px; border-bottom: 1px solid #e9ebec; font-size: 13px; }
        table.items tr:nth-child(even) { background: #f8f9fa; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals-section { display: flex; justify-content: flex-end; margin-bottom: 30px; }
        table.totals { width: 320px; }
        table.totals td { padding: 6px 12px; border: none; font-size: 13px; }
        table.totals .grand-total td { font-size: 18px; font-weight: 700; color: #0ab39c; border-top: 2px solid #0ab39c; padding-top: 10px; }
        .terms { margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 4px; border-left: 3px solid #0ab39c; }
        .terms h4 { font-size: 12px; text-transform: uppercase; color: #666; margin-bottom: 6px; }
        .terms p, .terms li { font-size: 12px; color: #666; line-height: 1.8; }
        .terms ul { padding-left: 20px; }
        .notes { margin-bottom: 20px; padding: 15px; background: #fff8e1; border-radius: 4px; border-left: 3px solid #f7b84b; }
        .notes h4 { font-size: 12px; text-transform: uppercase; color: #f7b84b; margin-bottom: 6px; }
        .footer-sign { display: flex; justify-content: space-between; margin-top: 50px; }
        .sign-box { text-align: center; width: 200px; }
        .sign-label { font-weight: 600; margin-bottom: 70px; font-size: 13px; }
        .sign-line { border-top: 1px dotted #999; padding-top: 5px; font-size: 12px; color: #666; }
        @media print {
            body { padding: 20px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="print-btn">In / Lưu PDF</button>
        <a href="<?= htmlspecialchars($_SERVER['HTTP_REFERER'] ?? '/orders') ?>" style="margin-left:10px;color:#0ab39c">Quay lại</a>
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
            <div class="doc-title">BÁO GIÁ</div>
            <div class="doc-number"><?= htmlspecialchars($order['order_number'] ?? '') ?></div>
            <div class="doc-number">Ngày: <?= !empty($order['issued_date']) ? date('d/m/Y', strtotime($order['issued_date'])) : date('d/m/Y') ?></div>
            <?php
                $validDays = $validityDays ?? 30;
                $validUntil = !empty($order['issued_date'])
                    ? date('d/m/Y', strtotime($order['issued_date'] . " + {$validDays} days"))
                    : date('d/m/Y', strtotime("+{$validDays} days"));
            ?>
            <div class="validity">Hiệu lực đến: <?= $validUntil ?></div>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-box">
            <h4>Kính gửi</h4>
            <p class="name"><?= htmlspecialchars(trim(($order['contact_first_name'] ?? '') . ' ' . ($order['contact_last_name'] ?? '')) ?: '-') ?></p>
            <?php if (!empty($order['company_name'])): ?><p>Công ty: <?= htmlspecialchars($order['company_name']) ?></p><?php endif; ?>
            <?php if (!empty($order['contact_email'])): ?><p>Email: <?= htmlspecialchars($order['contact_email']) ?></p><?php endif; ?>
            <?php if (!empty($order['contact_phone'])): ?><p>ĐT: <?= htmlspecialchars($order['contact_phone']) ?></p><?php endif; ?>
        </div>
        <div class="info-box">
            <h4>Người phụ trách</h4>
            <p class="name"><?= htmlspecialchars($order['owner_name'] ?? '-') ?></p>
            <?php if (!empty($order['owner_email'])): ?><p>Email: <?= htmlspecialchars($order['owner_email']) ?></p><?php endif; ?>
            <?php if (!empty($order['owner_phone'])): ?><p>ĐT: <?= htmlspecialchars($order['owner_phone']) ?></p><?php endif; ?>
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

    <?php if (!empty($order['notes'])): ?>
    <div class="notes">
        <h4>Ghi chú</h4>
        <p><?= nl2br(htmlspecialchars($order['notes'])) ?></p>
    </div>
    <?php endif; ?>

    <div class="terms">
        <h4>Điều khoản và điều kiện</h4>
        <ul>
            <li>Báo giá có hiệu lực <?= $validDays ?> ngày kể từ ngày phát hành</li>
            <li>Giá trên chưa bao gồm chi phí vận chuyển (nếu có)</li>
            <li>Thanh toán theo điều khoản thỏa thuận giữa hai bên</li>
            <li>Hàng hóa/dịch vụ được cung cấp theo đúng mô tả và thông số kỹ thuật</li>
            <li>Mọi thay đổi về số lượng hoặc quy cách sẽ được báo giá lại</li>
        </ul>
    </div>

    <div class="footer-sign">
        <div class="sign-box">
            <div class="sign-label">Khách hàng</div>
            <div class="sign-line">(Ký, ghi rõ họ tên)</div>
        </div>
        <div class="sign-box">
            <div class="sign-label">Đại diện công ty</div>
            <div class="sign-line">(Ký, ghi rõ họ tên)</div>
        </div>
    </div>
</body>
</html>
