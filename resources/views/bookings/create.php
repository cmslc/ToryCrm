<?php
$pageTitle = isset($link) ? 'Sửa liên kết đặt lịch' : 'Tạo liên kết đặt lịch';
$isEdit = isset($link);
$availableDays = $isEdit ? explode(',', $link['available_days'] ?? '1,2,3,4,5') : [1,2,3,4,5];
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0"><?= $pageTitle ?></h4>
    <a href="<?= url('bookings') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?= $isEdit ? url('bookings/' . $link['id'] . '/update') : url('bookings/store') ?>">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <div class="mb-3">
                        <label class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="<?= e($link['title'] ?? '') ?>" required placeholder="VD: Tư vấn sản phẩm 30 phút">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Mô tả ngắn về cuộc hẹn..."><?= e($link['description'] ?? '') ?></textarea>
                    </div>

                    <?php if (!$isEdit): ?>
                    <div class="mb-3">
                        <label class="form-label">Đường dẫn (slug)</label>
                        <div class="input-group">
                            <span class="input-group-text">/book/</span>
                            <input type="text" name="slug" class="form-control" value="<?= e($link['slug'] ?? '') ?>" placeholder="tu-van-san-pham">
                        </div>
                        <small class="text-muted">Để trống sẽ tự động tạo từ tiêu đề</small>
                    </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Thời lượng cuộc hẹn</label>
                            <select name="duration" class="form-select">
                                <?php foreach ([15, 30, 45, 60] as $d): ?>
                                    <option value="<?= $d ?>" <?= (int)($link['duration'] ?? 30) === $d ? 'selected' : '' ?>><?= $d ?> phút</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Thời gian nghỉ giữa cuộc hẹn</label>
                            <select name="buffer_minutes" class="form-select">
                                <?php foreach ([0, 15, 30] as $b): ?>
                                    <option value="<?= $b ?>" <?= (int)($link['buffer_minutes'] ?? 15) === $b ? 'selected' : '' ?>><?= $b ?> phút</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ngày khả dụng</label>
                        <div class="d-flex flex-wrap gap-3">
                            <?php
                            $dayLabels = [1 => 'Thứ 2', 2 => 'Thứ 3', 3 => 'Thứ 4', 4 => 'Thứ 5', 5 => 'Thứ 6', 6 => 'Thứ 7', 7 => 'Chủ nhật'];
                            foreach ($dayLabels as $val => $label):
                            ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="available_days[]" value="<?= $val ?>" id="day-<?= $val ?>"
                                    <?= in_array($val, $availableDays) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="day-<?= $val ?>"><?= $label ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Giờ bắt đầu</label>
                            <input type="time" name="start_time" class="form-control" value="<?= e(substr($link['start_time'] ?? '08:00', 0, 5)) ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Giờ kết thúc</label>
                            <input type="time" name="end_time" class="form-control" value="<?= e(substr($link['end_time'] ?? '17:00', 0, 5)) ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Đặt trước tối đa (ngày)</label>
                            <input type="number" name="max_advance_days" class="form-control" value="<?= (int)($link['max_advance_days'] ?? 30) ?>" min="1" max="365">
                        </div>
                    </div>

                    <?php if ($isEdit): ?>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" <?= ($link['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="isActive">Kích hoạt liên kết</label>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> <?= $isEdit ? 'Cập nhật' : 'Tạo liên kết' ?></button>
                        <a href="<?= url('bookings') ?>" class="btn btn-light">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card bg-primary-subtle border-0">
            <div class="card-body">
                <h6 class="text-primary"><i class="ri-lightbulb-line me-1"></i> Hướng dẫn</h6>
                <ul class="text-muted mb-0" style="padding-left: 18px">
                    <li class="mb-2">Tạo liên kết đặt lịch và chia sẻ với khách hàng</li>
                    <li class="mb-2">Khách hàng chọn ngày, giờ phù hợp và điền thông tin</li>
                    <li class="mb-2">Lịch hẹn tự động được tạo trong Calendar</li>
                    <li>Đường dẫn công khai: <code>/book/slug-cua-ban</code></li>
                </ul>
            </div>
        </div>
    </div>
</div>
