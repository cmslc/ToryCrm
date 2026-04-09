<?php $noLayout = true; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thanh toán thất bại - ToryCRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        body { background: #f3f6f9; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .failed-card { max-width: 500px; width: 100%; }
        .error-circle { width: 80px; height: 80px; border-radius: 50%; background: #f06548; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; }
        .error-circle i { font-size: 40px; color: #fff; }
    </style>
</head>
<body>
    <div class="failed-card">
        <div class="card shadow-lg border-0">
            <div class="card-body text-center p-5">
                <div class="error-circle">
                    <i class="ri-close-line"></i>
                </div>
                <h3 class="text-danger mb-2">Thanh toán thất bại</h3>
                <p class="text-muted mb-4"><?= e($message ?? 'Giao dịch không thành công. Vui lòng thử lại.') ?></p>

                <div class="bg-light rounded p-3 mb-4">
                    <p class="text-muted mb-0">
                        <i class="ri-information-line me-1"></i>
                        Cổng thanh toán: <strong><?= e($gateway ?? 'N/A') ?></strong>
                    </p>
                </div>

                <div class="d-flex gap-2 justify-content-center">
                    <a href="javascript:history.back()" class="btn btn-primary">
                        <i class="ri-refresh-line me-1"></i> Thử lại
                    </a>
                    <a href="/billing/invoices" class="btn btn-secondary">
                        <i class="ri-arrow-left-line me-1"></i> Quay lại
                    </a>
                </div>
            </div>
        </div>
        <p class="text-center text-muted mt-3">
            <small>ToryCRM - Hệ thống quản lý khách hàng</small>
        </p>
    </div>
</body>
</html>
