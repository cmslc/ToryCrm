<?php $pageTitle = 'Sửa ' . $person['full_name']; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Sửa thông tin người liên hệ</h4>
    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="<?= url('contacts') ?>">Khách hàng</a></li>
        <li class="breadcrumb-item"><a href="<?= url('persons/' . $person['id']) ?>"><?= e($person['full_name']) ?></a></li>
        <li class="breadcrumb-item active">Sửa</li>
    </ol>
</div>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <form method="POST" action="<?= url('persons/' . $person['id'] . '/update') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Thông tin cá nhân</h5></div>
                <div class="card-body">
                    <div class="row mb-3 align-items-center">
                        <div class="col-auto">
                            <?php if (!empty($person['avatar'])): ?>
                            <img src="<?= asset($person['avatar']) ?>" class="rounded-circle" width="80" height="80" style="object-fit:cover">
                            <?php else: ?>
                            <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:80px;height:80px;font-size:28px"><?= mb_strtoupper(mb_substr($person['full_name'], 0, 1)) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col">
                            <label class="form-label">Ảnh đại diện</label>
                            <input type="file" class="form-control" name="avatar" accept="image/*">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="full_name" value="<?= e($person['full_name']) ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Điện thoại</label>
                            <input type="text" class="form-control" name="phone" value="<?= e($person['phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?= e($person['email'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Giới tính</label>
                            <select class="form-select" name="gender">
                                <option value="">-</option>
                                <option value="male" <?= ($person['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Nam</option>
                                <option value="female" <?= ($person['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Nữ</option>
                                <option value="other" <?= ($person['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Khác</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sinh nhật</label>
                            <input type="date" class="form-control" name="date_of_birth" value="<?= e($person['date_of_birth'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi chú</label>
                        <textarea class="form-control" name="note" rows="3"><?= e($person['note'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="<?= url('persons/' . $person['id']) ?>" class="btn btn-soft-secondary">Huỷ</a>
                    <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i>Lưu</button>
                </div>
            </div>
        </form>
    </div>
</div>
