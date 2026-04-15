<?php $noLayout = true;
$isExpiredQuote = $quotation['valid_until'] && $quotation['valid_until'] < date('Y-m-d');
$canRespond = in_array($quotation['status'], ['sent', 'draft']) && !$isExpiredQuote;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo giá <?= e($quotation['quote_number']) ?> - <?= e($branding['name'] ?? 'ToryCRM') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; font-family: 'Segoe UI', system-ui, sans-serif; }
        .quote-wrap { max-width: 900px; margin: 0 auto; padding: 30px 15px; }
        .quote-card { background: #fff; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,.15); overflow: hidden; }
        .quote-header { background: linear-gradient(135deg, #405189, #2d3a6e); color: #fff; padding: 30px 40px; }
        .quote-header .logo { max-height: 45px; margin-bottom: 10px; }
        .quote-header .company { font-size: 20px; font-weight: 700; }
        .quote-header .company-detail { font-size: 12px; opacity: .8; line-height: 1.8; }
        .quote-header .doc-title { font-size: 32px; font-weight: 800; letter-spacing: 3px; text-align: right; }
        .quote-header .doc-number { text-align: right; font-size: 16px; opacity: .9; }
        .meta-strip { background: #f8f9fa; padding: 12px 40px; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px; border-bottom: 1px solid #eee; font-size: 13px; }
        .info-section { padding: 30px 40px; }
        .info-label { font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; color: #999; font-weight: 700; margin-bottom: 8px; }
        .info-name { font-size: 18px; font-weight: 700; color: #2d3436; }
        .info-detail { color: #636e72; font-size: 13px; line-height: 1.8; }
        .items-section { padding: 0 40px 20px; }
        .items-table th { background: #405189; color: #fff; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; padding: 12px; border: none; }
        .items-table td { padding: 12px; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
        .items-table tbody tr:hover { background: #f8f9ff; }
        .totals-section { padding: 0 40px 30px; display: flex; justify-content: flex-end; }
        .totals-box { width: 320px; }
        .totals-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; border-bottom: 1px solid #f5f5f5; }
        .totals-row.grand { background: linear-gradient(135deg, #405189, #2d3a6e); color: #fff; padding: 14px 20px; margin-top: 10px; border-radius: 8px; font-size: 18px; font-weight: 700; border: none; }
        .note-block { margin: 0 40px 15px; background: #f8f9fa; border-left: 3px solid #405189; padding: 15px 20px; border-radius: 0 8px 8px 0; }
        .note-block .note-title { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #999; font-weight: 700; margin-bottom: 5px; }
        .action-section { padding: 20px 40px 30px; }
        .status-banner { text-align: center; padding: 25px; }
        .footer { text-align: center; padding: 20px; color: rgba(255,255,255,.7); font-size: 12px; }
        @media print { body { background: #fff; } .no-print { display: none !important; } .quote-wrap { padding: 0; } .quote-card { box-shadow: none; border-radius: 0; } }
        @media (max-width: 768px) { .quote-header, .info-section, .items-section, .totals-section, .action-section { padding-left: 20px; padding-right: 20px; } .note-block { margin-left: 20px; margin-right: 20px; } }
    </style>
</head>
<body>
<div class="quote-wrap">
    <div class="quote-card">
        <!-- Header -->
        <div class="quote-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <?php if (!empty($branding['logo_url'])): ?>
                    <img src="<?= url($branding['logo_url']) ?>" alt="" class="logo">
                    <?php endif; ?>
                    <div class="company"><?= e($branding['name'] ?? 'ToryCRM') ?></div>
                    <div class="company-detail">
                        <?php if (!empty($branding['address'])): ?><?= e($branding['address']) ?><br><?php endif; ?>
                        <?php if (!empty($branding['phone'])): ?>Hotline: <?= e($branding['phone']) ?><?php endif; ?>
                        <?php if (!empty($branding['email'])): ?> · <?= e($branding['email']) ?><?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="doc-title">BÁO GIÁ</div>
                    <div class="doc-number"><?= e($quotation['quote_number']) ?></div>
                </div>
            </div>
        </div>

        <!-- Meta -->
        <div class="meta-strip">
            <span>Ngày: <strong><?= date('d/m/Y', strtotime($quotation['created_at'])) ?></strong></span>
            <?php if ($quotation['valid_until']): ?>
            <span>Hiệu lực đến: <strong class="<?= $isExpiredQuote ? 'text-danger' : 'text-success' ?>"><?= date('d/m/Y', strtotime($quotation['valid_until'])) ?></strong></span>
            <?php endif; ?>
            <span>Trạng thái:
                <?php if ($quotation['status'] === 'accepted'): ?>
                    <span class="badge bg-success">Đã chấp nhận</span>
                <?php elseif ($quotation['status'] === 'rejected'): ?>
                    <span class="badge bg-danger">Đã từ chối</span>
                <?php elseif ($isExpiredQuote): ?>
                    <span class="badge bg-warning text-dark">Hết hiệu lực</span>
                <?php else: ?>
                    <span class="badge bg-info">Chờ phản hồi</span>
                <?php endif; ?>
            </span>
        </div>

        <!-- Info -->
        <div class="info-section">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="info-label">Kính gửi</div>
                    <?php if ($quotation['contact_first_name']): ?>
                    <div class="info-name"><?= e(trim($quotation['contact_first_name'] . ' ' . ($quotation['contact_last_name'] ?? ''))) ?></div>
                    <?php endif; ?>
                    <div class="info-detail">
                        <?php if ($quotation['company_name']): ?><i class="ri-building-line me-1"></i><?= e($quotation['company_name']) ?><br><?php endif; ?>
                        <?php if ($quotation['company_address']): ?><i class="ri-map-pin-line me-1"></i><?= e($quotation['company_address']) ?><br><?php endif; ?>
                        <?php if ($quotation['contact_email']): ?><i class="ri-mail-line me-1"></i><?= e($quotation['contact_email']) ?><br><?php endif; ?>
                        <?php if ($quotation['contact_phone']): ?><i class="ri-phone-line me-1"></i><?= e($quotation['contact_phone']) ?><?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6 text-md-end mb-3">
                    <div class="info-label">Người phụ trách</div>
                    <div class="info-name"><?= e($quotation['owner_name'] ?? '-') ?></div>
                    <div class="info-detail">
                        <?php if (!empty($quotation['owner_email'])): ?><i class="ri-mail-line me-1"></i><?= e($quotation['owner_email']) ?><br><?php endif; ?>
                        <?php if (!empty($quotation['owner_phone'])): ?><i class="ri-phone-line me-1"></i><?= e($quotation['owner_phone']) ?><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items -->
        <div class="items-section">
            <table class="table items-table mb-0">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="35%">Sản phẩm / Dịch vụ</th>
                        <th class="text-center" width="8%">SL</th>
                        <th width="8%">ĐVT</th>
                        <th class="text-end" width="15%">Đơn giá</th>
                        <th class="text-center" width="8%">VAT</th>
                        <th class="text-end" width="17%">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $i => $item): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td>
                            <strong><?= e($item['product_name']) ?></strong>
                            <?php if ($item['description']): ?><br><span class="text-muted" style="font-size:12px"><?= e($item['description']) ?></span><?php endif; ?>
                        </td>
                        <td class="text-center"><?= $item['quantity'] ?></td>
                        <td><?= e($item['unit'] ?? 'Cái') ?></td>
                        <td class="text-end"><?= number_format($item['unit_price'], 0, ',', '.') ?> ₫</td>
                        <td class="text-center"><?= $item['tax_rate'] ?>%</td>
                        <td class="text-end"><strong><?= number_format($item['total'], 0, ',', '.') ?> ₫</strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="totals-section">
            <div class="totals-box">
                <div class="totals-row"><span>Tạm tính</span><strong><?= number_format($quotation['subtotal'] ?? 0, 0, ',', '.') ?> ₫</strong></div>
                <div class="totals-row"><span>Thuế VAT</span><span><?= number_format($quotation['tax_amount'] ?? 0, 0, ',', '.') ?> ₫</span></div>
                <?php if (($quotation['discount_amount'] ?? 0) > 0): ?>
                <div class="totals-row" style="color:#e17055"><span>Chiết khấu</span><span>-<?= number_format($quotation['discount_amount'], 0, ',', '.') ?> ₫</span></div>
                <?php endif; ?>
                <div class="totals-row grand"><span>TỔNG CỘNG</span><span><?= number_format($quotation['total'] ?? 0, 0, ',', '.') ?> ₫</span></div>
            </div>
        </div>

        <!-- Notes -->
        <?php if ($quotation['notes']): ?>
        <div class="note-block"><div class="note-title">Ghi chú</div><p class="mb-0" style="font-size:13px"><?= nl2br(e($quotation['notes'])) ?></p></div>
        <?php endif; ?>
        <?php if ($quotation['terms']): ?>
        <div class="note-block"><div class="note-title">Điều khoản & Điều kiện</div><p class="mb-0" style="font-size:13px"><?= nl2br(e($quotation['terms'])) ?></p></div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="action-section no-print">
            <?php if ($quotation['status'] === 'accepted'): ?>
            <div class="status-banner">
                <div class="rounded-circle bg-success-subtle d-inline-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px">
                    <i class="ri-check-double-line text-success" style="font-size:32px"></i>
                </div>
                <h4 class="text-success">Báo giá đã được chấp nhận</h4>
                <?php if ($quotation['accepted_at']): ?><p class="text-muted">Ngày <?= date('d/m/Y H:i', strtotime($quotation['accepted_at'])) ?></p><?php endif; ?>
            </div>
            <?php elseif ($quotation['status'] === 'rejected'): ?>
            <div class="status-banner">
                <div class="rounded-circle bg-danger-subtle d-inline-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px">
                    <i class="ri-close-circle-line text-danger" style="font-size:32px"></i>
                </div>
                <h4 class="text-danger">Báo giá đã bị từ chối</h4>
                <?php if ($quotation['rejection_reason'] ?? null): ?><p class="text-muted">Lý do: <?= e($quotation['rejection_reason']) ?></p><?php endif; ?>
            </div>
            <?php elseif ($isExpiredQuote): ?>
            <div class="status-banner">
                <div class="rounded-circle bg-warning-subtle d-inline-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px">
                    <i class="ri-time-line text-warning" style="font-size:32px"></i>
                </div>
                <h4 class="text-warning">Báo giá đã hết hiệu lực</h4>
            </div>
            <?php elseif ($canRespond): ?>
            <div class="d-flex gap-3 justify-content-center">
                <button type="button" class="btn btn-success btn-lg px-5 rounded-pill" onclick="acceptQuote()">
                    <i class="ri-check-line me-2"></i> Chấp nhận báo giá
                </button>
                <button type="button" class="btn btn-outline-danger btn-lg px-5 rounded-pill" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="ri-close-line me-2"></i> Từ chối
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="footer no-print"><?= e($branding['name'] ?? 'ToryCRM') ?> · <?= e($quotation['quote_number']) ?></div>
</div>

<!-- Reject Modal -->
<div class="modal fade no-print" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0"><h5 class="modal-title">Từ chối báo giá</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <textarea class="form-control" id="rejectReason" rows="3" placeholder="Vui lòng cho chúng tôi biết lý do..."></textarea>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger rounded-pill px-4" onclick="rejectQuote()">Xác nhận từ chối</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function acceptQuote() {
    if (!confirm('Bạn xác nhận chấp nhận báo giá này?')) return;
    fetch('<?= url('quote/' . $quotation['portal_token'] . '/accept') ?>', { method: 'POST', headers: {'Content-Type':'application/json'} })
    .then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.error || 'Lỗi'); }).catch(() => alert('Lỗi'));
}
function rejectQuote() {
    var fd = new FormData(); fd.append('reason', document.getElementById('rejectReason').value);
    fetch('<?= url('quote/' . $quotation['portal_token'] . '/reject') ?>', { method: 'POST', body: fd })
    .then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.error || 'Lỗi'); }).catch(() => alert('Lỗi'));
}
</script>
</body>
</html>
