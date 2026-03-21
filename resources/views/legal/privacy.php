<?php $noLayout = true; $pageTitle = 'Chính sách bảo mật'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | ToryCRM</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Remix Icon -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <!-- App CSS -->
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }
        .legal-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        .legal-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .legal-header h1 {
            color: #405189;
            font-weight: 700;
        }
        .legal-section {
            margin-bottom: 2rem;
        }
        .legal-section h3 {
            color: #405189;
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }
        .legal-section p {
            color: #495057;
            line-height: 1.8;
        }
    </style>
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
                <h3>1. Thu thập thông tin</h3>
                <p>Chúng tôi thu thập thông tin cá nhân khi bạn đăng ký tài khoản, bao gồm họ tên, email, số điện thoại và tên công ty. Ngoài ra, hệ thống tự động ghi nhận thông tin kỹ thuật như địa chỉ IP, loại trình duyệt và thời gian truy cập. Các thông tin này giúp chúng tôi cung cấp và cải thiện dịch vụ.</p>
            </div>

            <div class="legal-section">
                <h3>2. Sử dụng thông tin</h3>
                <p>Thông tin của bạn được sử dụng để cung cấp dịch vụ quản lý khách hàng, gửi thông báo hệ thống và hỗ trợ kỹ thuật. Chúng tôi cũng có thể sử dụng dữ liệu tổng hợp (không định danh cá nhân) để phân tích và cải thiện sản phẩm. Chúng tôi không bao giờ bán thông tin cá nhân của bạn cho bên thứ ba vì mục đích thương mại.</p>
            </div>

            <div class="legal-section">
                <h3>3. Bảo vệ dữ liệu</h3>
                <p>Dữ liệu của bạn được lưu trữ trên máy chủ bảo mật với mã hóa AES-256. Chúng tôi thực hiện sao lưu dữ liệu hàng ngày và lưu trữ tại nhiều vị trí địa lý khác nhau. Hệ thống được giám sát 24/7 để phát hiện và ngăn chặn các truy cập trái phép.</p>
            </div>

            <div class="legal-section">
                <h3>4. Chia sẻ với bên thứ ba</h3>
                <p>Chúng tôi chỉ chia sẻ thông tin của bạn với bên thứ ba khi có sự đồng ý của bạn hoặc theo yêu cầu của pháp luật. Các đối tác cung cấp dịch vụ (như hosting, email) chỉ được truy cập thông tin cần thiết để thực hiện nhiệm vụ. Tất cả đối tác đều phải ký thỏa thuận bảo mật dữ liệu trước khi hợp tác.</p>
            </div>

            <div class="legal-section">
                <h3>5. Cookie</h3>
                <p>ToryCRM sử dụng cookie để duy trì phiên đăng nhập và lưu trữ tùy chọn của người dùng. Cookie kỹ thuật là bắt buộc để hệ thống hoạt động bình thường. Bạn có thể quản lý cài đặt cookie trong trình duyệt, tuy nhiên việc vô hiệu hóa cookie có thể ảnh hưởng đến trải nghiệm sử dụng.</p>
            </div>

            <div class="legal-section">
                <h3>6. Quyền của người dùng</h3>
                <p>Bạn có quyền truy cập, chỉnh sửa hoặc xóa dữ liệu cá nhân của mình bất kỳ lúc nào thông qua phần cài đặt tài khoản. Bạn có quyền yêu cầu xuất toàn bộ dữ liệu của mình ở định dạng máy đọc được. Nếu muốn xóa tài khoản vĩnh viễn, vui lòng liên hệ bộ phận hỗ trợ và chúng tôi sẽ xử lý trong vòng 30 ngày.</p>
            </div>

            <div class="legal-section">
                <h3>7. Liên hệ</h3>
                <p>Mọi thắc mắc về chính sách bảo mật, vui lòng liên hệ với chúng tôi qua email hoặc hệ thống hỗ trợ khách hàng. Chúng tôi cam kết phản hồi mọi yêu cầu liên quan đến bảo mật dữ liệu trong vòng 24 giờ làm việc. Chính sách này có thể được cập nhật định kỳ và chúng tôi sẽ thông báo cho bạn về mọi thay đổi quan trọng.</p>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
