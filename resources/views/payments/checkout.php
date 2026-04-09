<?php $pageTitle = 'Thanh toán hóa đơn'; ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Thanh toán hóa đơn</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">ToryCRM</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('billing/invoices') ?>">Hóa đơn</a></li>
                    <li class="breadcrumb-item active">Thanh toán</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<?php $flashMsg = flash(); if ($flashMsg): ?>
    <div class="alert alert-<?= $flashMsg['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
        <?= e($flashMsg['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row justify-content-center">
    <!-- Invoice Summary -->
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-file-list-3-line me-1"></i> Thông tin hóa đơn
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <p class="text-muted mb-1">Số hóa đơn</p>
                        <h6><?= e($invoice['invoice_number'] ?? '#' . $invoice['id']) ?></h6>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted mb-1">Số tiền</p>
                        <h5 class="text-primary"><?= number_format($invoice['total_amount'] ?? 0, 0, ',', '.') ?> VND</h5>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted mb-1">Hạn thanh toán</p>
                        <h6>
                            <?php if (!empty($invoice['due_date'])): ?>
                                <?= date('d/m/Y', strtotime($invoice['due_date'])) ?>
                            <?php else: ?>
                                <span class="text-muted">Không giới hạn</span>
                            <?php endif; ?>
                        </h6>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-bank-card-line me-1"></i> Chọn phương thức thanh toán
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php if ($hasVNPay): ?>
                    <!-- VNPay -->
                    <div class="col-md-4">
                        <div class="card border shadow-none mb-0 h-100">
                            <div class="card-body text-center p-4">
                                <div class="avatar-lg mx-auto mb-3">
                                    <div class="avatar-title rounded-circle bg-primary-subtle text-primary fs-24">
                                        <i class="ri-bank-card-line"></i>
                                    </div>
                                </div>
                                <h5 class="mb-1">VNPay</h5>
                                <p class="text-muted mb-3">Thanh toán qua thẻ ATM, Visa, MasterCard, QR Pay</p>
                                <form method="POST" action="<?= url('payments/' . $invoice['id'] . '/vnpay') ?>">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ri-bank-card-line me-1"></i> Thanh toán VNPay
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($hasMoMo): ?>
                    <!-- MoMo -->
                    <div class="col-md-4">
                        <div class="card border shadow-none mb-0 h-100">
                            <div class="card-body text-center p-4">
                                <div class="avatar-lg mx-auto mb-3">
                                    <div class="avatar-title rounded-circle bg-danger-subtle text-danger fs-24">
                                        <i class="ri-wallet-3-line"></i>
                                    </div>
                                </div>
                                <h5 class="mb-1">MoMo</h5>
                                <p class="text-muted mb-3">Thanh toán qua ví MoMo, quét mã QR</p>
                                <form method="POST" action="<?= url('payments/' . $invoice['id'] . '/momo') ?>">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="ri-wallet-3-line me-1"></i> Thanh toán MoMo
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Bank Transfer -->
                    <div class="col-md-4">
                        <div class="card border shadow-none mb-0 h-100">
                            <div class="card-body text-center p-4">
                                <div class="avatar-lg mx-auto mb-3">
                                    <div class="avatar-title rounded-circle bg-success-subtle text-success fs-24">
                                        <i class="ri-building-2-line"></i>
                                    </div>
                                </div>
                                <h5 class="mb-1">Chuyển khoản</h5>
                                <p class="text-muted mb-3">Chuyển khoản ngân hàng trực tiếp</p>
                                <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#bankTransferModal">
                                    <i class="ri-building-2-line me-1"></i> Xem thông tin
                                </button>
                            </div>
                        </div>
                    </div>

                    <?php if (!$hasVNPay && !$hasMoMo): ?>
                    <div class="col-12">
                        <div class="alert alert-warning mb-0">
                            <i class="ri-alert-line me-1"></i>
                            Chưa cấu hình cổng thanh toán online. Vui lòng liên hệ quản trị viên hoặc thanh toán bằng chuyển khoản ngân hàng.
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bank Transfer Modal -->
<div class="modal fade" id="bankTransferModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thông tin chuyển khoản</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-borderless mb-3">
                        <tr>
                            <td class="text-muted" style="width: 40%">Ngân hàng:</td>
                            <td class="fw-medium">Vietcombank</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Số tài khoản:</td>
                            <td class="fw-medium">0123456789</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Chủ tài khoản:</td>
                            <td class="fw-medium">CONG TY TNHH TORYCRM</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Nội dung CK:</td>
                            <td class="fw-medium text-primary"><?= e($invoice['invoice_number'] ?? 'INV' . $invoice['id']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Số tiền:</td>
                            <td class="fw-medium text-danger"><?= number_format($invoice['total_amount'] ?? 0, 0, ',', '.') ?> VND</td>
                        </tr>
                    </table>
                </div>

                <div class="text-center p-3 bg-light rounded">
                    <p class="text-muted mb-2">Quét mã QR để chuyển khoản</p>
                    <div class="border rounded p-4 bg-white d-inline-block">
                        <i class="ri-qr-code-line" style="font-size: 80px; color: #ccc;"></i>
                        <p class="text-muted mb-0 mt-2">QR Code placeholder</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
