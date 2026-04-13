<?php $pageTitle = 'Tạo chiến dịch'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Tạo chiến dịch</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('campaigns') ?>">Chiến dịch</a></li>
                <li class="breadcrumb-item active">Tạo mới</li>
            </ol>
        </div>

        <form method="POST" action="<?= url('campaigns/store') ?>">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Thông tin chiến dịch</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label">Tên chiến dịch <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Loại chiến dịch</label>
                                    <select name="type" class="form-select">
                                        <option value="email">Email</option>
                                        <option value="sms">SMS</option>
                                        <option value="call">Gọi điện</option>
                                        <option value="social">Mạng xã hội</option>
                                        <option value="other">Khác</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngân sách (VNĐ)</label>
                                    <input type="number" class="form-control" name="budget" value="0" min="0">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Mô tả</label>
                                    <textarea name="description" class="form-control" rows="4"></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày bắt đầu</label>
                                    <input type="date" class="form-control" name="start_date">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày kết thúc</label>
                                    <input type="date" class="form-control" name="end_date">
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
                                    <option value="draft">Nháp</option>
                                    <option value="running">Đang chạy</option>
                                    <option value="paused">Tạm dừng</option>
                                    <option value="completed">Hoàn thành</option>
                                    <option value="cancelled">Đã hủy</option>
                                </select>
                            </div>
                            <?php
                            $deptGrouped = [];
                            foreach ($users ?? [] as $u) { $deptGrouped[$u['dept_name'] ?? 'Chưa phân phòng'][] = $u; }
                            ?>
                            <div class="mb-3">
                                <label class="form-label">Người phụ trách</label>
                                <select name="owner_id" class="form-select searchable-select">
                                    <option value="">Chọn</option>
                                    <?php foreach ($deptGrouped as $dept => $dUsers): ?>
                                    <optgroup label="<?= e($dept) ?>">
                                        <?php foreach ($dUsers as $u): ?>
                                        <option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-save-line me-1"></i> Lưu</button>
                            <a href="<?= url('campaigns') ?>" class="btn btn-soft-secondary">Hủy</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
