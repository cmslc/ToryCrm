<?php $pageTitle = 'Tạo chính sách SLA'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Tạo chính sách SLA</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('sla') ?>">Chính sách SLA</a></li>
                <li class="breadcrumb-item active">Tạo mới</li>
            </ol>
        </div>

        <form method="POST" action="<?= url('sla/store') ?>">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Thông tin chính sách</h5></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Tên chính sách <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" required placeholder="VD: SLA Khẩn cấp - 1 giờ phản hồi">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mức ưu tiên <span class="text-danger">*</span></label>
                                    <select name="priority" class="form-select" required>
                                        <option value="low">Thấp</option>
                                        <option value="medium" selected>Trung bình</option>
                                        <option value="high">Cao</option>
                                        <option value="urgent">Khẩn cấp</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Chuyển tiếp đến</label>
                                    <select name="escalate_to" class="form-select">
                                        <option value="">Chọn người nhận</option>
                                        <?php foreach ($users ?? [] as $u): ?>
                                            <option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Người sẽ được gán khi SLA bị vi phạm</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Thời gian phản hồi đầu tiên (giờ) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="first_response_hours" value="4" min="0.5" step="0.5" required>
                                    <small class="text-muted">Thời gian tối đa để phản hồi lần đầu</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Thời gian xử lý (giờ) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="resolution_hours" value="24" min="1" step="0.5" required>
                                    <small class="text-muted">Thời gian tối đa để xử lý xong ticket</small>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" name="is_active" id="is_active" value="1" checked>
                                    <label class="form-check-label" for="is_active">Kích hoạt chính sách</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-save-line me-1"></i> Lưu</button>
                            <a href="<?= url('sla') ?>" class="btn btn-soft-secondary">Hủy</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
