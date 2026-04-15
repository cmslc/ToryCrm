<?php $noLayout = true; ?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo giá <?= e($quotation['quote_number']) ?></title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/icons.min.css" rel="stylesheet">
    <link href="/assets/css/app.min.css" rel="stylesheet">
    <style>
        body { background: #f3f3f9; min-height: 100vh; }
        .quote-container { max-width: 900px; margin: 30px auto; }
        .company-header { border-bottom: 3px solid #405189; padding-bottom: 20px; margin-bottom: 30px; }
        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .quote-container { margin: 0; max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="quote-container p-3">
        <div class="card shadow-lg border-0">
            <div class="card-body p-4 p-md-5">
                <!-- Company Header -->
                <div class="company-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <?php if (!empty($branding['logo_url'])): ?>
                                <img src="<?= e($branding['logo_url']) ?>" alt="Logo" height="50" class="mb-2">
                            <?php endif; ?>
                            <h3 class="mb-1"><?= e($branding['name'] ?? 'ToryCRM') ?></h3>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h2 class="text-primary mb-1">BÁO GIÁ</h2>
                            <h5 class="text-muted"><?= e($quotation['quote_number']) ?></h5>
                        </div>
                    </div>
                </div>

                <!-- Info Row -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted text-uppercase mb-2">Gửi đến</h6>
                        <?php if ($quotation['contact_first_name']): ?>
                            <p class="mb-1 fw-medium fs-5"><?= e(trim($quotation['contact_first_name'] . ' ' . ($quotation['contact_last_name'] ?? ''))) ?></p>
                        <?php endif; ?>
                        <?php if ($quotation['company_name']): ?>
                            <p class="mb-1"><?= e($quotation['company_name']) ?></p>
                        <?php endif; ?>
                        <?php if ($quotation['company_address']): ?>
                            <p class="mb-1 text-muted"><?= e($quotation['company_address']) ?></p>
                        <?php endif; ?>
                        <?php if ($quotation['contact_email']): ?>
                            <p class="mb-0 text-muted"><i class="ri-mail-line me-1"></i><?= e($quotation['contact_email']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="mb-2">
                            <span class="text-muted">Ngày tạo:</span>
                            <span class="fw-medium"><?= date('d/m/Y', strtotime($quotation['created_at'])) ?></span>
                        </div>
                        <?php if ($quotation['valid_until']): ?>
                            <?php $isExpired = $quotation['valid_until'] < date('Y-m-d'); ?>
                            <div class="mb-2">
                                <span class="text-muted">Hiệu lực đến:</span>
                                <span class="badge bg-<?= $isExpired ? 'danger' : 'success' ?> fs-6"><?= date('d/m/Y', strtotime($quotation['valid_until'])) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="35%">Sản phẩm / Dịch vụ</th>
                                <th class="text-end" width="10%">Số lượng</th>
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
                                <td>
                                    <span class="fw-medium"><?= e($item['product_name']) ?></span>
                                    <?php if ($item['description']): ?>
                                        <br><small class="text-muted"><?= e($item['description']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end"><?= $item['quantity'] ?></td>
                                <td><?= e($item['unit']) ?></td>
                                <td class="text-end"><?= number_format($item['unit_price'], 0, ',', '.') ?> ₫</td>
                                <td class="text-end"><?= $item['tax_rate'] ?>%</td>
                                <td class="text-end fw-medium"><?= number_format($item['total'], 0, ',', '.') ?> ₫</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" class="text-end">Tạm tính:</td>
                                <td class="text-end fw-medium"><?= number_format($quotation['subtotal'] ?? 0, 0, ',', '.') ?> ₫</td>
                            </tr>
                            <tr>
                                <td colspan="6" class="text-end">Thuế:</td>
                                <td class="text-end"><?= number_format($quotation['tax_amount'] ?? 0, 0, ',', '.') ?> ₫</td>
                            </tr>
                            <?php if (($quotation['discount_amount'] ?? 0) > 0): ?>
                            <tr>
                                <td colspan="6" class="text-end">Giảm giá:</td>
                                <td class="text-end text-danger">-<?= number_format($quotation['discount_amount'], 0, ',', '.') ?> ₫</td>
                            </tr>
                            <?php endif; ?>
                            <tr class="table-primary">
                                <td colspan="6" class="text-end fw-bold fs-5">TỔNG CỘNG:</td>
                                <td class="text-end fw-bold fs-5"><?= number_format($quotation['total'] ?? 0, 0, ',', '.') ?> ₫</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Notes & Terms -->
                <?php if ($quotation['notes']): ?>
                <div class="mb-3">
                    <h6 class="text-muted">Ghi chú</h6>
                    <p><?= nl2br(e($quotation['notes'])) ?></p>
                </div>
                <?php endif; ?>

                <?php if ($quotation['terms']): ?>
                <div class="mb-4">
                    <h6 class="text-muted">Điều khoản</h6>
                    <p><?= nl2br(e($quotation['terms'])) ?></p>
                </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <?php
                $isExpiredQuote = $quotation['valid_until'] && $quotation['valid_until'] < date('Y-m-d');
                $canRespond = in_array($quotation['status'], ['sent', 'draft']) && !$isExpiredQuote;
                ?>

                <?php if ($quotation['status'] === 'accepted'): ?>
                    <div class="alert alert-success text-center no-print">
                        <i class="ri-check-double-line fs-1 d-block mb-2"></i>
                        <h5>Báo giá đã được chấp nhận</h5>
                        <?php if ($quotation['accepted_at']): ?>
                            <p class="mb-0">Ngày chấp nhận: <?= date('d/m/Y H:i', strtotime($quotation['accepted_at'])) ?></p>
                        <?php endif; ?>
                    </div>
                <?php elseif ($quotation['status'] === 'rejected'): ?>
                    <div class="alert alert-danger text-center no-print">
                        <i class="ri-close-circle-line fs-1 d-block mb-2"></i>
                        <h5>Báo giá đã bị từ chối</h5>
                        <?php if ($quotation['reject_reason']): ?>
                            <p class="mb-0">Lý do: <?= e($quotation['reject_reason']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php elseif ($isExpiredQuote): ?>
                    <div class="alert alert-warning text-center no-print">
                        <i class="ri-time-line fs-1 d-block mb-2"></i>
                        <h5>Báo giá đã hết hiệu lực</h5>
                    </div>
                <?php elseif ($canRespond): ?>
                    <div class="d-flex gap-3 justify-content-center no-print mt-4">
                        <button type="button" class="btn btn-success btn-lg px-5" id="acceptBtn" onclick="acceptQuote()">
                            <i class="ri-check-line me-2"></i> Chấp nhận báo giá
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-lg px-5" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="ri-close-line me-2"></i> Từ chối
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-muted py-3 no-print">
            <small>Powered by <?= e($branding['name'] ?? 'ToryCRM') ?></small>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade no-print" id="rejectModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Từ chối báo giá</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Lý do từ chối (không bắt buộc)</label>
                        <textarea class="form-control" id="rejectReason" rows="3" placeholder="Vui lòng cho chúng tôi biết lý do..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-danger" onclick="rejectQuote()">Xác nhận từ chối</button>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
    function acceptQuote() {
        if (!confirm('Bạn xác nhận chấp nhận báo giá này?')) return;

        fetch('<?= url('quote/' . $quotation['portal_token'] . '/accept') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || 'Có lỗi xảy ra');
            }
        })
        .catch(() => alert('Có lỗi xảy ra'));
    }

    function rejectQuote() {
        const reason = document.getElementById('rejectReason').value;
        const form = new FormData();
        form.append('reason', reason);

        fetch('<?= url('quote/' . $quotation['portal_token'] . '/reject') ?>', {
            method: 'POST',
            body: form,
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || 'Có lỗi xảy ra');
            }
        })
        .catch(() => alert('Có lỗi xảy ra'));
    }
    </script>
</body>
</html>
