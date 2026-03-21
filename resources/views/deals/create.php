<?php $pageTitle = 'Thêm cơ hội'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Thêm cơ hội kinh doanh</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('deals') ?>">Cơ hội</a></li>
                <li class="breadcrumb-item active">Thêm mới</li>
            </ol>
        </div>

        <form method="POST" action="<?= url('deals/store') ?>">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Thông tin cơ hội</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label">Tên cơ hội <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="title" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Giá trị (VNĐ)</label>
                                    <input type="number" class="form-control" name="value" value="0" min="0">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày dự kiến đóng</label>
                                    <input type="date" class="form-control" name="expected_close_date">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Khách hàng</label>
                                    <select name="contact_id" class="form-select">
                                        <option value="">Chọn</option>
                                        <?php foreach ($contacts ?? [] as $c): ?>
                                            <option value="<?= $c['id'] ?>" <?= ($_GET['contact_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                                <?= e($c['first_name'] . ' ' . ($c['last_name'] ?? '')) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Công ty</label>
                                    <select name="company_id" class="form-select">
                                        <option value="">Chọn</option>
                                        <?php foreach ($companies ?? [] as $comp): ?>
                                            <option value="<?= $comp['id'] ?>" <?= ($_GET['company_id'] ?? '') == $comp['id'] ? 'selected' : '' ?>>
                                                <?= e($comp['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Mô tả</label>
                                    <textarea name="description" class="form-control" rows="3"></textarea>
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
                                <label class="form-label">Giai đoạn</label>
                                <select name="stage_id" class="form-select">
                                    <?php foreach ($stages ?? [] as $s): ?>
                                        <option value="<?= $s['id'] ?>"><?= e($s['name']) ?> (<?= $s['probability'] ?>%)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ưu tiên</label>
                                <select name="priority" class="form-select">
                                    <option value="low">Thấp</option>
                                    <option value="medium" selected>Trung bình</option>
                                    <option value="high">Cao</option>
                                    <option value="urgent">Khẩn cấp</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Người phụ trách</label>
                                <select name="owner_id" class="form-select">
                                    <option value="">Chọn</option>
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
                            <a href="<?= url('deals') ?>" class="btn btn-soft-secondary">Hủy</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
