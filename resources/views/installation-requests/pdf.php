<?php
// Vars expected: $request, $items, $cp (company profile)
$ctrl = \App\Controllers\InstallationRequestController::class;
$cName = $request['c_company_name'] ?: ($request['c_full_name'] ?: '');
$reqDate = $request['requested_date'] ? date('d/m/y', strtotime($request['requested_date'])) : '';
$today = date('d') . ' tháng ' . date('m') . ' năm ' . date('Y');
$cityFromProfile = $cp['city'] ?? 'Hà Nội';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>YCTC <?= htmlspecialchars($request['code']) ?></title>
<style>
@page { size: A4; margin: 20mm; }
body { font-family: "DejaVu Sans","Segoe UI",Arial,sans-serif; font-size: 12px; color:#222; max-width: 800px; margin: 0 auto; padding: 20px; }
.header { display: flex; justify-content: space-between; border-bottom: 1px dashed #666; padding-bottom: 10px; margin-bottom: 14px; }
.company-info { font-size: 11px; line-height: 1.4; }
.company-info strong { font-size: 13px; }
.logo { max-height: 60px; }
h1.title { text-align: center; font-size: 20px; margin: 16px 0 10px; letter-spacing: 1px; }
.meta-right { float: right; border: 1px solid #333; padding: 6px 10px; font-size: 12px; }
.meta-right div { margin: 2px 0; }
.meta-left { font-size: 13px; line-height: 1.6; clear: none; }
.meta-left .label { display: inline-block; width: 160px; font-weight: 600; }
.section-title { text-align: center; font-size: 14px; font-weight: bold; margin: 14px 0 8px; }
table.content { width: 100%; border-collapse: collapse; margin-top: 6px; }
table.content th, table.content td { border: 1px solid #333; padding: 6px 8px; vertical-align: middle; font-size: 12px; }
table.content th { background: #f2f2f2; text-align: center; }
table.content td.center { text-align: center; }
table.content td.right { text-align: right; }
.report-block { border: 1px solid #333; padding: 8px 12px; margin-top: 8px; min-height: 90px; }
.report-block .row { margin: 4px 0; }
.signatures { display: flex; justify-content: space-between; margin-top: 20px; }
.sig { width: 32%; text-align: center; border: 1px solid #333; padding: 10px 6px; min-height: 110px; }
.sig u { font-weight: bold; display: block; margin-bottom: 6px; }
.sig .name-bottom { margin-top: 60px; font-weight: bold; text-decoration: underline; }
@media print { .no-print { display: none; } body { padding: 0; } }
</style>
</head>
<body>

<div class="no-print" style="text-align:center;margin-bottom:14px">
    <button onclick="window.print()" style="padding:8px 24px;background:#405189;color:#fff;border:none;border-radius:4px;cursor:pointer">In / Lưu PDF</button>
</div>

<div class="header">
    <div class="company-info">
        <?php if (!empty($cp['logo'])): ?>
            <img src="<?= htmlspecialchars($cp['logo']) ?>" class="logo">
        <?php endif; ?>
        <div>
            <strong><?= htmlspecialchars(strtoupper($cp['name'] ?? ($_SESSION['tenant']['name'] ?? 'COMPANY'))) ?></strong><br>
            <?php if (!empty($cp['address'])): ?><?= htmlspecialchars($cp['address']) ?><br><?php endif; ?>
            <?php if (!empty($cp['phone'])): ?>T: <?= htmlspecialchars($cp['phone']) ?><?php endif; ?>
            <?php if (!empty($cp['fax'])): ?> - F: <?= htmlspecialchars($cp['fax']) ?><?php endif; ?><br>
            <?php if (!empty($cp['website'])): ?>Website: <?= htmlspecialchars($cp['website']) ?><?php endif; ?>
            <?php if (!empty($cp['email'])): ?> - Email: <?= htmlspecialchars($cp['email']) ?><?php endif; ?>
        </div>
    </div>
</div>

<div style="text-align:right;font-style:italic;margin-bottom:8px"><?= htmlspecialchars($cityFromProfile) ?>, Ngày <?= $today ?></div>

<h1 class="title">GIẤY YÊU CẦU THI CÔNG</h1>

<table style="width:100%;margin-bottom:10px"><tr>
    <td class="meta-left" style="vertical-align:top">
        <div><span class="label">Người yêu cầu:</span> <strong><?= htmlspecialchars($request['requester_name'] ?: '') ?><?= $request['requester_phone'] ? ' - ' . htmlspecialchars($request['requester_phone']) : '' ?></strong></div>
        <div><span class="label">Đơn vị thi công:</span> <strong><?= htmlspecialchars($request['contractor'] ?: '') ?></strong></div>
        <div><span class="label">Địa chỉ lắp đặt:</span> <?= htmlspecialchars($request['installation_address'] ?: '') ?></div>
        <div><span class="label">Tên khách hàng:</span> <?= htmlspecialchars($request['customer_contact_name'] ?: $cName) ?><?= $request['customer_contact_phone'] ? ': ' . htmlspecialchars($request['customer_contact_phone']) : '' ?></div>
        <div><span class="label">Thời gian yêu cầu thi công:</span> <strong><?= htmlspecialchars($reqDate) ?></strong></div>
    </td>
    <td style="vertical-align:top;width:200px">
        <div class="meta-right">
            <div><strong>Số CF:</strong> <?= htmlspecialchars($request['code']) ?></div>
            <div><strong>Mã KH:</strong> <?= htmlspecialchars($request['c_account_code'] ?? '') ?></div>
            <div><strong>Bộ phận:</strong> <?= htmlspecialchars($request['department'] ?? '') ?></div>
        </div>
    </td>
</tr></table>

<div class="section-title">NỘI DUNG YÊU CẦU THI CÔNG</div>

<table class="content">
    <thead>
        <tr>
            <th style="width:30px">STT</th>
            <th>TÊN HÀNG (MÃ SẢN PHẨM)</th>
            <th style="width:100px">CHECK HÀNG</th>
            <th style="width:150px">KÍCH THƯỚC, MÀU SẮC</th>
            <th style="width:60px">ĐVT</th>
            <th style="width:60px">SL</th>
            <th style="width:150px">GHI CHÚ</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($items)): foreach ($items as $i => $it): ?>
            <tr>
                <td class="center"><?= $i + 1 ?></td>
                <td>
                    <strong><?= htmlspecialchars($it['product_sku'] ?: ($it['p_sku'] ?? '')) ?></strong>
                    <?php if ($it['product_name']): ?><br><?= htmlspecialchars($it['product_name']) ?><?php endif; ?>
                </td>
                <td class="center"><?= htmlspecialchars($it['check_status'] ?? '') ?></td>
                <td class="center"><?= htmlspecialchars($it['size_color'] ?? '') ?></td>
                <td class="center"><?= htmlspecialchars($it['unit']) ?></td>
                <td class="right"><?= rtrim(rtrim(number_format((float)$it['quantity'], 2), '0'), '.') ?></td>
                <td><?= htmlspecialchars($it['notes'] ?? '') ?></td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="7" class="center" style="color:#999;padding:20px">-- Không có sản phẩm --</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<div class="report-block">
    <div><strong>Người nhận yêu cầu:</strong></div>
    <div class="row">- Thời gian tiến hành thi công: <?= $request['execution_date'] ? date('d/m/y H:i', strtotime($request['execution_date'])) : '' ?></div>
    <div class="row">- Người thi công: <?= htmlspecialchars($request['installer_name'] ?? '') ?></div>
    <div class="row">- Báo cáo tình trạng hàng hóa của thợ: <?= nl2br(htmlspecialchars($request['condition_report'] ?? '')) ?></div>
</div>

<div class="signatures">
    <div class="sig">
        <u>Người yêu cầu</u>
        <div class="name-bottom"><?= htmlspecialchars($request['requester_name'] ?? '') ?></div>
    </div>
    <div class="sig">
        <u>Điều phối</u>
        <div class="name-bottom">&nbsp;</div>
    </div>
    <div class="sig">
        <u>Cán bộ thi công</u>
        <div class="name-bottom"><?= htmlspecialchars($request['installer_name'] ?? '') ?></div>
    </div>
</div>

<script>setTimeout(function(){window.print();},400);</script>
</body>
</html>
