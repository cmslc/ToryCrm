<?php
$isEdit = !empty($rule);
$pageTitle = $isEdit ? 'Sửa quy tắc hoa hồng' : 'Tạo quy tắc hoa hồng';
?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0"><?= $pageTitle ?></h4>
            <a href="<?= url('commissions/rules') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?= $isEdit ? url('commissions/rules/' . $rule['id'] . '/update') : url('commissions/rules/store') ?>">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tên quy tắc <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="<?= e($rule['name'] ?? '') ?>" required placeholder="VD: Hoa hồng Deal 5%">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Loại hoa hồng</label>
                            <select name="type" class="form-select">
                                <option value="percent" <?= ($rule['type'] ?? 'percent') === 'percent' ? 'selected' : '' ?>>Phần trăm (%)</option>
                                <option value="fixed" <?= ($rule['type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Cố định (VNĐ)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Giá trị <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="value" step="0.1" min="0" value="<?= e($rule['value'] ?? '') ?>" required placeholder="VD: 5">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Áp dụng cho</label>
                            <select name="apply_to" class="form-select">
                                <option value="deal" <?= ($rule['apply_to'] ?? 'deal') === 'deal' ? 'selected' : '' ?>>Deal (Cơ hội)</option>
                                <option value="order" <?= ($rule['apply_to'] ?? '') === 'order' ? 'selected' : '' ?>>Đơn hàng</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Giá trị tối thiểu</label>
                            <input type="number" class="form-control" name="min_value" min="0" value="<?= e($rule['min_value'] ?? 0) ?>" placeholder="0 = không giới hạn">
                            <div class="form-text">Chỉ áp dụng khi giá trị deal/đơn hàng >= giá trị này</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Trạng thái</label>
                            <div class="form-check form-switch mt-2">
                                <input type="checkbox" class="form-check-input" name="is_active" id="isActive" <?= ($rule['is_active'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="isActive">Đang hoạt động</label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> <?= $isEdit ? 'Cập nhật' : 'Tạo mới' ?></button>
                        <a href="<?= url('commissions/rules') ?>" class="btn btn-soft-secondary ms-1">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
