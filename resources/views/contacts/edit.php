<?php $pageTitle = 'Sửa khách hàng'; ?>

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Sửa khách hàng</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?= url('contacts') ?>">Khách hàng</a></li>
                            <li class="breadcrumb-item active">Sửa</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="<?= url('contacts/' . $contact['id'] . '/update') ?>">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Thông tin cơ bản</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Họ <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="first_name" value="<?= e($contact['first_name']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tên</label>
                                    <input type="text" class="form-control" name="last_name" value="<?= e($contact['last_name'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" value="<?= e($contact['email'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Điện thoại</label>
                                    <input type="text" class="form-control" name="phone" value="<?= e($contact['phone'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Di động</label>
                                    <input type="text" class="form-control" name="mobile" value="<?= e($contact['mobile'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Chức vụ</label>
                                    <input type="text" class="form-control" name="position" value="<?= e($contact['position'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Giới tính</label>
                                    <select name="gender" class="form-select">
                                        <option value="">Chọn</option>
                                        <option value="male" <?= ($contact['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Nam</option>
                                        <option value="female" <?= ($contact['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Nữ</option>
                                        <option value="other" <?= ($contact['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Khác</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày sinh</label>
                                    <input type="date" class="form-control" name="date_of_birth" value="<?= $contact['date_of_birth'] ?? '' ?>">
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Địa chỉ</label>
                                    <input type="text" class="form-control" name="address" value="<?= e($contact['address'] ?? '') ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Thành phố</label>
                                    <input type="text" class="form-control" name="city" value="<?= e($contact['city'] ?? '') ?>">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Ghi chú</label>
                                    <textarea name="description" class="form-control" rows="3"><?= e($contact['description'] ?? '') ?></textarea>
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
                                <label class="form-label">Công ty</label>
                                <select name="company_id" class="form-select">
                                    <option value="">Chọn công ty</option>
                                    <?php foreach ($companies ?? [] as $company): ?>
                                        <option value="<?= $company['id'] ?>" <?= ($contact['company_id'] ?? '') == $company['id'] ? 'selected' : '' ?>><?= e($company['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nguồn</label>
                                <select name="source_id" class="form-select">
                                    <option value="">Chọn nguồn</option>
                                    <?php foreach ($sources ?? [] as $source): ?>
                                        <option value="<?= $source['id'] ?>" <?= ($contact['source_id'] ?? '') == $source['id'] ? 'selected' : '' ?>><?= e($source['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Trạng thái</label>
                                <select name="status" class="form-select">
                                    <?php $statuses = ['new' => 'Mới', 'contacted' => 'Đã liên hệ', 'qualified' => 'Tiềm năng', 'converted' => 'Chuyển đổi', 'lost' => 'Mất']; ?>
                                    <?php foreach ($statuses as $val => $label): ?>
                                        <option value="<?= $val ?>" <?= ($contact['status'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Người phụ trách</label>
                                <select name="owner_id" class="form-select">
                                    <option value="">Chọn</option>
                                    <?php foreach ($users ?? [] as $u): ?>
                                        <option value="<?= $u['id'] ?>" <?= ($contact['owner_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Điểm</label>
                                <input type="number" class="form-control" name="score" value="<?= $contact['score'] ?? 0 ?>" min="0" max="100">
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-save-line me-1"></i> Cập nhật</button>
                                <a href="<?= url('contacts/' . $contact['id']) ?>" class="btn btn-soft-secondary">Hủy</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
