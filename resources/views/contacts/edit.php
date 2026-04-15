<?php $pageTitle = 'Sửa khách hàng'; $c = $contact; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Sửa khách hàng</h4>
    <a href="<?= url('contacts/' . $c['id']) ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<form method="POST" action="<?= url('contacts/' . $c['id'] . '/update') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Thông tin cơ bản</h5></div>
                <div class="card-body">
                    <!-- Avatar -->
                    <div class="mb-3 d-flex align-items-center gap-3">
                        <div class="position-relative">
                            <?php if (!empty($c['avatar']) && file_exists(BASE_PATH . '/public/uploads/avatars/' . $c['avatar'])): ?>
                            <img src="<?= url('uploads/avatars/' . $c['avatar']) ?>" class="rounded-circle" id="avatarPreview" style="width:64px;height:64px;object-fit:cover">
                            <div class="rounded-circle bg-primary-subtle text-primary d-none d-flex align-items-center justify-content-center" style="width:64px;height:64px;font-size:24px" id="avatarInitial"><?= strtoupper(mb_substr($c['first_name'], 0, 1)) ?></div>
                            <?php else: ?>
                            <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:64px;height:64px;font-size:24px" id="avatarInitial"><?= strtoupper(mb_substr($c['first_name'], 0, 1)) ?></div>
                            <img src="" class="rounded-circle d-none" id="avatarPreview" style="width:64px;height:64px;object-fit:cover">
                            <?php endif; ?>
                            <label for="avatarInput" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:24px;height:24px;cursor:pointer">
                                <i class="ri-camera-line fs-12"></i>
                            </label>
                            <input type="file" name="avatar" id="avatarInput" accept="image/*" class="d-none">
                        </div>
                        <div class="text-muted fs-13">Ảnh đại diện<br><small>JPG, PNG tối đa 5MB</small></div>
                    </div>
                    <script>
                    document.getElementById('avatarInput')?.addEventListener('change', function() {
                        if (this.files && this.files[0]) {
                            var reader = new FileReader();
                            reader.onload = function(e) {
                                document.getElementById('avatarPreview').src = e.target.result;
                                document.getElementById('avatarPreview').classList.remove('d-none');
                                document.getElementById('avatarInitial').classList.add('d-none');
                            };
                            reader.readAsDataURL(this.files[0]);
                        }
                    });
                    </script>
                    <div class="row">
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Danh xưng</label>
                            <select name="title" class="form-select">
                                <option value="">Chọn</option>
                                <?php foreach (['anh'=>'Anh','chị'=>'Chị','ông'=>'Ông','bà'=>'Bà'] as $tv=>$tl): ?>
                                <option value="<?= $tv ?>" <?= ($c['title'] ?? '') === $tv ? 'selected' : '' ?>><?= $tl ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="full_name" value="<?= e(trim($c['first_name'] . ' ' . ($c['last_name'] ?? ''))) ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Mã khách hàng</label>
                            <input type="text" class="form-control" name="account_code" value="<?= e($c['account_code'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?= e($c['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Điện thoại</label>
                            <input type="text" class="form-control" name="phone" value="<?= e($c['phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Di động</label>
                            <input type="text" class="form-control" name="mobile" value="<?= e($c['mobile'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fax</label>
                            <input type="text" class="form-control" name="fax" value="<?= e($c['fax'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Chức vụ</label>
                            <input type="text" class="form-control" name="position" value="<?= e($c['position'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Giới tính</label>
                            <select name="gender" class="form-select">
                                <option value="">Chọn</option>
                                <option value="male" <?= ($c['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Nam</option>
                                <option value="female" <?= ($c['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Nữ</option>
                                <option value="other" <?= ($c['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Khác</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày sinh</label>
                            <input type="date" class="form-control" name="date_of_birth" value="<?= $c['date_of_birth'] ?? '' ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mã số thuế</label>
                            <input type="text" class="form-control" name="tax_code" value="<?= e($c['tax_code'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Website</label>
                            <input type="text" class="form-control" name="website" value="<?= e($c['website'] ?? '') ?>" placeholder="https://">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Địa chỉ</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Địa chỉ</label>
                            <input type="text" class="form-control" name="address" value="<?= e($c['address'] ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Tỉnh/Thành phố</label>
                            <input type="text" class="form-control" name="province" value="<?= e($c['province'] ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Quận/Huyện</label>
                            <input type="text" class="form-control" name="district" value="<?= e($c['district'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phường/Xã</label>
                            <input type="text" class="form-control" name="ward" value="<?= e($c['ward'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Quốc gia</label>
                            <input type="text" class="form-control" name="country" value="<?= e($c['country'] ?? 'Việt Nam') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Ghi chú</h5></div>
                <div class="card-body">
                    <textarea name="description" class="form-control" rows="3"><?= e($c['description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Phân loại</h5></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Công ty</label>
                        <div class="d-flex gap-2">
                            <select name="company_id" id="companySelect" class="form-select searchable-select flex-grow-1">
                                <option value="">Chọn công ty</option>
                                <?php foreach ($companies ?? [] as $company): ?>
                                <option value="<?= $company['id'] ?>" <?= ($c['company_id'] ?? '') == $company['id'] ? 'selected' : '' ?>><?= e($company['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="btn btn-soft-primary flex-shrink-0" data-bs-toggle="modal" data-bs-target="#quickCompanyModal" title="Tạo nhanh doanh nghiệp">
                                <i class="ri-add-line"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nguồn</label>
                        <select name="source_id" class="form-select">
                            <option value="">Chọn nguồn</option>
                            <?php foreach ($sources ?? [] as $source): ?>
                            <option value="<?= $source['id'] ?>" <?= ($c['source_id'] ?? '') == $source['id'] ? 'selected' : '' ?>><?= e($source['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-select">
                            <?php foreach ($contactStatuses ?? [] as $st): ?>
                            <option value="<?= e($st['slug']) ?>" <?= ($c['status'] ?? '') === $st['slug'] ? 'selected' : '' ?>><?= e($st['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nhóm khách hàng</label>
                        <select name="customer_group" class="form-select">
                            <option value="">Chưa phân loại</option>
                            <?php $groups = ['du_an'=>'Khách dự án','le'=>'Khách lẻ','dai_ly'=>'Khách đại lý','doanh_nghiep'=>'Doanh nghiệp','vip'=>'VIP']; ?>
                            <?php foreach ($groups as $val => $label): ?>
                            <option value="<?= $val ?>" <?= ($c['customer_group'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Người phụ trách</label>
                        <?php
                        $deptGrouped = [];
                        foreach ($users ?? [] as $u) { $deptGrouped[$u['dept_name'] ?? 'Chưa phân phòng'][] = $u; }
                        ?>
                        <select name="owner_id" class="form-select searchable-select">
                            <option value="">Chọn</option>
                            <?php foreach ($deptGrouped as $dept => $dUsers): ?>
                            <optgroup label="<?= e($dept) ?>">
                                <?php foreach ($dUsers as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= ($c['owner_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Người giới thiệu</label>
                        <input type="text" class="form-control" name="referrer_code" value="<?= e($c['referrer_code'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_private" value="1" id="isPrivate" <?= ($c['is_private'] ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="isPrivate">KH riêng tư</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-save-line me-1"></i> Cập nhật</button>
                        <a href="<?= url('contacts/' . $c['id']) ?>" class="btn btn-soft-secondary">Hủy</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Modal tạo nhanh doanh nghiệp -->
<div class="modal fade" id="quickCompanyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tạo nhanh doanh nghiệp</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tên doanh nghiệp <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="qcName" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Điện thoại</label>
                        <input type="text" class="form-control" id="qcPhone">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="qcEmail">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mã số thuế</label>
                    <input type="text" class="form-control" id="qcTaxCode">
                </div>
                <div class="mb-3">
                    <label class="form-label">Địa chỉ</label>
                    <input type="text" class="form-control" id="qcAddress">
                </div>
                <div class="mb-3">
                    <label class="form-label">Thành phố</label>
                    <input type="text" class="form-control" id="qcCity">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-soft-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="btnQuickCompany"><i class="ri-save-line me-1"></i> Tạo</button>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('btnQuickCompany')?.addEventListener('click', function() {
    var btn = this;
    var name = document.getElementById('qcName').value.trim();
    if (!name) { document.getElementById('qcName').focus(); return; }

    btn.disabled = true;
    btn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i> Đang tạo...';

    fetch('<?= url("companies/quick-store") ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?= csrf_token() ?>'},
        body: JSON.stringify({
            name: name,
            phone: document.getElementById('qcPhone').value.trim(),
            email: document.getElementById('qcEmail').value.trim(),
            tax_code: document.getElementById('qcTaxCode').value.trim(),
            address: document.getElementById('qcAddress').value.trim(),
            city: document.getElementById('qcCity').value.trim()
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            var sel = document.getElementById('companySelect');
            var opt = new Option(data.company.name, data.company.id, true, true);
            sel.appendChild(opt);
            if (sel._searchable) sel._searchable.refresh();
            bootstrap.Modal.getInstance(document.getElementById('quickCompanyModal')).hide();
            document.getElementById('qcName').value = '';
            document.getElementById('qcPhone').value = '';
            document.getElementById('qcEmail').value = '';
            document.getElementById('qcTaxCode').value = '';
            document.getElementById('qcAddress').value = '';
            document.getElementById('qcCity').value = '';
        } else {
            alert(data.error || 'Có lỗi xảy ra');
        }
    })
    .catch(() => alert('Có lỗi xảy ra'))
    .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="ri-save-line me-1"></i> Tạo'; });
});
</script>
