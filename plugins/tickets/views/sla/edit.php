<?php $pageTitle = 'Sửa chính sách SLA'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Sửa chính sách SLA</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('sla') ?>">Chính sách SLA</a></li>
                <li class="breadcrumb-item active">Sửa</li>
            </ol>
        </div>

        <form method="POST" action="<?= url('sla/' . $policy['id'] . '/update') ?>">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Thông tin chính sách</h5></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Tên chính sách <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" value="<?= e($policy['name']) ?>" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mức ưu tiên <span class="text-danger">*</span></label>
                                    <select name="priority" class="form-select" required>
                                        <option value="low" <?= $policy['priority'] === 'low' ? 'selected' : '' ?>>Thấp</option>
                                        <option value="medium" <?= $policy['priority'] === 'medium' ? 'selected' : '' ?>>Trung bình</option>
                                        <option value="high" <?= $policy['priority'] === 'high' ? 'selected' : '' ?>>Cao</option>
                                        <option value="urgent" <?= $policy['priority'] === 'urgent' ? 'selected' : '' ?>>Khẩn cấp</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Chuyển tiếp đến</label>
                                    <?php $deptGrouped = []; foreach ($users ?? [] as $u) { $deptGrouped[$u['dept_name'] ?? 'Chưa phân phòng'][] = $u; } ?>
                                    <select name="escalate_to" class="form-select">
                                        <option value="">Chọn người nhận</option>
                                        <?php foreach ($deptGrouped as $dept => $dUsers): ?>
                                        <optgroup label="<?= e($dept) ?>">
                                            <?php foreach ($dUsers as $u): ?>
                                            <option value="<?= $u['id'] ?>" <?= ($policy['escalate_to'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Người sẽ được gán khi SLA bị vi phạm</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Thời gian phản hồi đầu tiên (giờ) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="first_response_hours" value="<?= e($policy['first_response_hours']) ?>" min="0.5" step="0.5" required>
                                    <small class="text-muted">Thời gian tối đa để phản hồi lần đầu</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Thời gian xử lý (giờ) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="resolution_hours" value="<?= e($policy['resolution_hours']) ?>" min="1" step="0.5" required>
                                    <small class="text-muted">Thời gian tối đa để xử lý xong ticket</small>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" name="is_active" id="is_active" value="1" <?= $policy['is_active'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_active">Kích hoạt chính sách</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-save-line me-1"></i> Cập nhật</button>
                            <a href="<?= url('sla') ?>" class="btn btn-soft-secondary">Hủy</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
