<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Không tìm thấy</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="d-flex align-items-center justify-content-center min-vh-100">
        <div class="text-center">
            <h1 class="display-1 fw-bold text-primary">404</h1>
            <p class="fs-3 text-muted">Trang không tìm thấy</p>
            <a href="<?= $_ENV['APP_URL'] ?? '/' ?>/dashboard" class="btn btn-primary">Về trang chủ</a>
        </div>
    </div>
</body>
</html>
