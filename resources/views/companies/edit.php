<?php $pageTitle = 'Sửa doanh nghiệp'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Sửa doanh nghiệp</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('companies') ?>">Doanh nghiệp</a></li>
                <li class="breadcrumb-item active">Sửa</li>
            </ol>
        </div>

        <form method="POST" action="<?= url('companies/' . $company['id'] . '/update') ?>">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Thông tin doanh nghiệp</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" value="<?= e($company['name']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">MST</label>
                                    <input type="text" class="form-control" name="tax_code" value="<?= e($company['tax_code'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" value="<?= e($company['email'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Điện thoại</label>
                                    <input type="text" class="form-control" name="phone" value="<?= e($company['phone'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Website</label>
                                    <input type="text" class="form-control" name="website" value="<?= e($company['website'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngành nghề</label>
                                    <select name="industry" class="form-select">
                                        <option value="">Chọn</option>
                                        <?php foreach (['Công nghệ','Tài chính','Giáo dục','Y tế','Bất động sản','Thương mại','Sản xuất','Dịch vụ','Khác'] as $ind): ?>
                                            <option value="<?= $ind ?>" <?= ($company['industry'] ?? '') === $ind ? 'selected' : '' ?>><?= $ind ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Quy mô</label>
                                    <select name="company_size" class="form-select">
                                        <option value="">Chọn</option>
                                        <?php foreach (['1-10'=>'1-10 người','11-50'=>'11-50 người','51-200'=>'51-200 người','201-500'=>'201-500 người','500+'=>'500+ người'] as $v=>$l): ?>
                                            <option value="<?= $v ?>" <?= ($company['company_size'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Thành phố</label>
                                    <input type="text" class="form-control" name="city" value="<?= e($company['city'] ?? '') ?>">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Địa chỉ</label>
                                    <input type="text" class="form-control" name="address" value="<?= e($company['address'] ?? '') ?>">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Mô tả</label>
                                    <textarea name="description" class="form-control" rows="3"><?= e($company['description'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Người phụ trách</label>
                                <select name="owner_id" class="form-select">
                                    <option value="">Chọn</option>
                                    <?php foreach ($users ?? [] as $u): ?>
                                        <option value="<?= $u['id'] ?>" <?= ($company['owner_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-save-line me-1"></i> Cập nhật</button>
                            <a href="<?= url('companies/' . $company['id']) ?>" class="btn btn-soft-secondary">Hủy</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
