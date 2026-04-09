<?php $noLayout = true; $pageTitle = 'Điều khoản sử dụng'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | ToryCRM</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <!-- Remix Icon -->
    <link href="/css/icons.min.css" rel="stylesheet">
    <!-- App CSS -->
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">

</head>
<body>

<div class="legal-container">
    <div class="legal-header">
        <h1><i class="ri-customer-service-2-line"></i> ToryCRM</h1>
        <h2 class="h4 text-muted mt-2"><?= e($pageTitle) ?></h2>
        <p class="text-muted">Cập nhật lần cuối: 21/03/2026</p>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">

            <div class="legal-section">
                <h3>1. Giới thiệu</h3>
                <p>Chào mừng bạn đến với ToryCRM - hệ thống quản lý khách hàng toàn diện dành cho doanh nghiệp Việt Nam. Khi sử dụng dịch vụ của chúng tôi, bạn đồng ý tuân thủ các điều khoản được nêu dưới đây. Vui lòng đọc kỹ trước khi sử dụng hệ thống.</p>
            </div>

            <div class="legal-section">
                <h3>2. Điều kiện sử dụng</h3>
                <p>Bạn phải từ đủ 18 tuổi và có năng lực pháp lý để sử dụng dịch vụ này. Bạn chịu trách nhiệm đảm bảo rằng tất cả thông tin đăng ký là chính xác và đầy đủ. Việc sử dụng dịch vụ cho mục đích bất hợp pháp hoặc vi phạm pháp luật Việt Nam là bị nghiêm cấm.</p>
            </div>

            <div class="legal-section">
                <h3>3. Tài khoản người dùng</h3>
                <p>Bạn chịu trách nhiệm bảo mật tài khoản và mật khẩu của mình. Mọi hoạt động xảy ra dưới tài khoản của bạn đều thuộc trách nhiệm của bạn. Nếu phát hiện truy cập trái phép, bạn cần thông báo cho chúng tôi ngay lập tức.</p>
            </div>

            <div class="legal-section">
                <h3>4. Quyền sở hữu trí tuệ</h3>
                <p>Tất cả nội dung, giao diện và mã nguồn của ToryCRM thuộc quyền sở hữu của chúng tôi. Bạn không được sao chép, phân phối hoặc chỉnh sửa bất kỳ phần nào của hệ thống khi chưa có sự đồng ý bằng văn bản. Dữ liệu bạn nhập vào hệ thống vẫn thuộc quyền sở hữu của bạn.</p>
            </div>

            <div class="legal-section">
                <h3>5. Bảo mật dữ liệu</h3>
                <p>Chúng tôi cam kết bảo vệ dữ liệu của bạn theo các tiêu chuẩn bảo mật cao nhất. Dữ liệu của mỗi doanh nghiệp được cách ly hoàn toàn với các doanh nghiệp khác trên hệ thống. Chúng tôi sử dụng mã hóa SSL/TLS cho mọi kết nối và mã hóa dữ liệu nhạy cảm khi lưu trữ.</p>
            </div>

            <div class="legal-section">
                <h3>6. Giới hạn trách nhiệm</h3>
                <p>ToryCRM được cung cấp theo hiện trạng "như là" (as-is). Chúng tôi không đảm bảo dịch vụ sẽ hoạt động liên tục không gián đoạn. Trong mọi trường hợp, trách nhiệm bồi thường của chúng tôi không vượt quá số tiền bạn đã thanh toán trong 12 tháng gần nhất.</p>
            </div>

            <div class="legal-section">
                <h3>7. Thay đổi điều khoản</h3>
                <p>Chúng tôi có quyền thay đổi các điều khoản này bất kỳ lúc nào. Mọi thay đổi sẽ được thông báo trên hệ thống ít nhất 7 ngày trước khi có hiệu lực. Việc bạn tiếp tục sử dụng dịch vụ sau khi thay đổi đồng nghĩa với việc bạn chấp nhận điều khoản mới.</p>
            </div>

            <div class="legal-section">
                <h3>8. Liên hệ</h3>
                <p>Nếu bạn có bất kỳ câu hỏi nào về các điều khoản sử dụng này, vui lòng liên hệ với chúng tôi qua email hoặc hệ thống hỗ trợ. Chúng tôi sẽ phản hồi trong vòng 24 giờ làm việc. Mọi phản hồi của bạn giúp chúng tôi cải thiện dịch vụ tốt hơn.</p>
            </div>

        </div>
    </div>

    <div class="text-center mt-4 mb-4">
        <a href="<?= url('login') ?>" class="btn btn-outline-primary">
            <i class="ri-arrow-left-line me-1"></i> Quay lại trang đăng nhập
        </a>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
