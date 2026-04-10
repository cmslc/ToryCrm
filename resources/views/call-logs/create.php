<?php $pageTitle = 'Ghi nhận cuộc gọi'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Ghi nhận cuộc gọi</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('call-logs') ?>">Tổng đài</a></li>
                <li class="breadcrumb-item active">Ghi nhận</li>
            </ol>
        </div>

        <form method="POST" action="<?= url('call-logs/store') ?>">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Thông tin cuộc gọi</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Loại cuộc gọi</label>
                                    <select name="call_type" class="form-select">
                                        <option value="outbound">Gọi đi</option>
                                        <option value="inbound">Gọi đến</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Trạng thái</label>
                                    <select name="status" class="form-select">
                                        <option value="answered">Đã nghe</option>
                                        <option value="missed">Nhỡ</option>
                                        <option value="busy">Bận</option>
                                        <option value="failed">Lỗi</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số gọi <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="caller_number" required placeholder="VD: 0901234567">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số nhận</label>
                                    <input type="text" class="form-control" name="callee_number" placeholder="VD: 02812345678">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Thời gian bắt đầu</label>
                                    <input type="datetime-local" class="form-control" name="started_at" value="<?= date('Y-m-d\TH:i') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Thời lượng (giây)</label>
                                    <input type="number" class="form-control" name="duration" value="0" min="0">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Ghi chú</label>
                                    <textarea name="notes" class="form-control" rows="3" placeholder="Nội dung cuộc gọi..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Liên kết</h5></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Khách hàng</label>
                                <select name="contact_id" class="form-select searchable-select">
                                    <option value="">Chọn</option>
                                    <?php foreach ($contacts ?? [] as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= e($c['first_name'] . ' ' . ($c['last_name'] ?? '')) ?> <?= $c['phone'] ? '(' . $c['phone'] . ')' : '' ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nhân viên</label>
                                <select name="user_id" class="form-select searchable-select">
                                    <option value="">Tôi</option>
                                    <?php foreach ($users ?? [] as $u): ?>
                                        <option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-save-line me-1"></i> Lưu</button>
                            <a href="<?= url('call-logs') ?>" class="btn btn-soft-secondary">Hủy</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
