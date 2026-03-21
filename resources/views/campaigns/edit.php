<?php $pageTitle = 'Sửa chiến dịch'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Sửa chiến dịch</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('campaigns') ?>">Chiến dịch</a></li>
                <li class="breadcrumb-item active">Sửa</li>
            </ol>
        </div>

        <form method="POST" action="<?= url('campaigns/' . $campaign['id'] . '/update') ?>">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Thông tin chiến dịch</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label">Tên chiến dịch <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" value="<?= e($campaign['name']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Loại chiến dịch</label>
                                    <select name="type" class="form-select">
                                        <?php foreach (['email' => 'Email', 'sms' => 'SMS', 'call' => 'Gọi điện', 'social' => 'Mạng xã hội', 'other' => 'Khác'] as $v => $l): ?>
                                            <option value="<?= $v ?>" <?= ($campaign['type'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngân sách (VNĐ)</label>
                                    <input type="number" class="form-control" name="budget" value="<?= $campaign['budget'] ?? 0 ?>" min="0">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Chi phí thực tế (VNĐ)</label>
                                    <input type="number" class="form-control" name="actual_cost" value="<?= $campaign['actual_cost'] ?? 0 ?>" min="0">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <!-- spacer -->
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Mô tả</label>
                                    <textarea name="description" class="form-control" rows="4"><?= e($campaign['description'] ?? '') ?></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày bắt đầu</label>
                                    <input type="date" class="form-control" name="start_date" value="<?= $campaign['start_date'] ?? '' ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày kết thúc</label>
                                    <input type="date" class="form-control" name="end_date" value="<?= $campaign['end_date'] ?? '' ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Phân loại</h5></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Trạng thái</label>
                                <select name="status" class="form-select">
                                    <?php foreach (['draft' => 'Nháp', 'running' => 'Đang chạy', 'paused' => 'Tạm dừng', 'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy'] as $v => $l): ?>
                                        <option value="<?= $v ?>" <?= ($campaign['status'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Người phụ trách</label>
                                <select name="owner_id" class="form-select">
                                    <option value="">Chọn</option>
                                    <?php foreach ($users ?? [] as $u): ?>
                                        <option value="<?= $u['id'] ?>" <?= ($campaign['owner_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="hidden" name="is_locked" value="0">
                                    <input class="form-check-input" type="checkbox" name="is_locked" value="1" id="isLocked" <?= !empty($campaign['is_locked']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="isLocked">Khóa chiến dịch</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-save-line me-1"></i> Cập nhật</button>
                            <a href="<?= url('campaigns/' . $campaign['id']) ?>" class="btn btn-soft-secondary">Hủy</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
