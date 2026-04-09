<?php
$noLayout = true;
$pageTitle = 'Tạo ticket';
ob_start();
?>

        <div class="page-title-box d-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-0">Tạo ticket hỗ trợ</h4>
            <a href="<?= url('portal/tickets') ?>" class="btn btn-light"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?= url('portal/tickets/store') ?>">
                    <?= csrf_field() ?>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" placeholder="Mô tả ngắn vấn đề của bạn" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Danh mục</label>
                            <select name="category_id" class="form-select">
                                <option value="">-- Chọn danh mục --</option>
                                <?php foreach ($categories ?? [] as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Độ ưu tiên</label>
                            <select name="priority" class="form-select">
                                <option value="low">Thấp</option>
                                <option value="medium" selected>Trung bình</option>
                                <option value="high">Cao</option>
                                <option value="urgent">Khẩn cấp</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nội dung chi tiết</label>
                        <textarea class="form-control" name="content" rows="6" placeholder="Mô tả chi tiết vấn đề bạn đang gặp phải..."></textarea>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary"><i class="ri-send-plane-line me-1"></i> Gửi ticket</button>
                    </div>
                </form>
            </div>
        </div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
