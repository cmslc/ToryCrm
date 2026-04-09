<?php $noLayout = true; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thanh toán thành công - ToryCRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        body { background: #f3f6f9; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .success-card { max-width: 500px; width: 100%; }
        .check-circle { width: 80px; height: 80px; border-radius: 50%; background: #0ab39c; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; }
        .check-circle i { font-size: 40px; color: #fff; }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="card shadow-lg border-0">
            <div class="card-body text-center p-5">
                <div class="check-circle">
                    <i class="ri-check-line"></i>
                </div>
                <h3 class="text-success mb-2">Thanh toán thành công!</h3>
                <p class="text-muted mb-4"><?= e($message ?? 'Giao dịch của bạn đã được xử lý thành công.') ?></p>

                <?php if (!empty($transaction)): ?>
                <div class="bg-light rounded p-3 mb-4 text-start">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Cổng thanh toán:</span>
                        <span class="fw-medium"><?= e($gateway ?? '') ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Mã giao dịch:</span>
                        <span class="fw-medium"><?= e($transaction['transaction_id'] ?? '') ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Số tiền:</span>
                        <span class="fw-medium text-success"><?= number_format($transaction['amount'] ?? 0, 0, ',', '.') ?> VND</span>
                    </div>
                </div>
                <?php endif; ?>

                <a href="/billing/invoices" class="btn btn-primary">
                    <i class="ri-arrow-left-line me-1"></i> Quay lại danh sách hóa đơn
                </a>
            </div>
        </div>
        <p class="text-center text-muted mt-3">
            <small>ToryCRM - Hệ thống quản lý khách hàng</small>
        </p>
    </div>
</body>
</html>
