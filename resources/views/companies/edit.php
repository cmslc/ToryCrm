<?php $pageTitle = 'Sửa doanh nghiệp'; $c = $company; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Sửa doanh nghiệp</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('companies') ?>">Doanh nghiệp</a></li>
                <li class="breadcrumb-item active">Sửa</li>
            </ol>
        </div>

        <form method="POST" action="<?= url('companies/' . $c['id'] . '/update') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Thông tin doanh nghiệp</h5></div>
                        <div class="card-body">
                            <!-- Logo -->
                            <div class="mb-3 d-flex align-items-center gap-3">
                                <div class="position-relative">
                                    <?php if (!empty($c['logo']) && file_exists(BASE_PATH . '/public/uploads/logos/' . $c['logo'])): ?>
                                    <img src="<?= url('uploads/logos/' . $c['logo']) ?>" class="rounded" id="logoPreview" style="width:64px;height:64px;object-fit:cover">
                                    <div class="rounded bg-primary-subtle text-primary d-none d-flex align-items-center justify-content-center" style="width:64px;height:64px;font-size:24px" id="logoInitial"><i class="ri-building-line"></i></div>
                                    <?php else: ?>
                                    <div class="rounded bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:64px;height:64px;font-size:24px" id="logoInitial"><i class="ri-building-line"></i></div>
                                    <img src="" class="rounded d-none" id="logoPreview" style="width:64px;height:64px;object-fit:cover">
                                    <?php endif; ?>
                                    <label for="logoInput" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:24px;height:24px;cursor:pointer">
                                        <i class="ri-camera-line fs-12"></i>
                                    </label>
                                    <input type="file" name="logo" id="logoInput" accept="image/*" class="d-none">
                                </div>
                                <div class="text-muted fs-13">Logo doanh nghiệp<br><small>JPG, PNG tối đa 5MB</small></div>
                            </div>
                            <script>
                            document.getElementById('logoInput')?.addEventListener('change', function() {
                                if (this.files && this.files[0]) {
                                    var reader = new FileReader();
                                    reader.onload = function(e) {
                                        document.getElementById('logoPreview').src = e.target.result;
                                        document.getElementById('logoPreview').classList.remove('d-none');
                                        document.getElementById('logoInitial').classList.add('d-none');
                                    };
                                    reader.readAsDataURL(this.files[0]);
                                }
                            });
                            </script>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tên doanh nghiệp <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" value="<?= e($c['name']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mã số thuế</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="tax_code" id="taxCodeInput" value="<?= e($c['tax_code'] ?? '') ?>" placeholder="Nhập MST rồi bấm tra cứu">
                                        <button type="button" class="btn btn-soft-info" id="btnLookupTax">
                                            <i class="ri-search-line me-1"></i> Tra cứu
                                        </button>
                                    </div>
                                    <div class="form-text text-success d-none" id="taxLookupStatus"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" value="<?= e($c['email'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Điện thoại</label>
                                    <input type="text" class="form-control" name="phone" value="<?= e($c['phone'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Website</label>
                                    <input type="text" class="form-control" name="website" value="<?= e($c['website'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngành nghề</label>
                                    <select name="industry" class="form-select">
                                        <option value="">Chọn</option>
                                        <?php foreach (['Công nghệ','Tài chính','Giáo dục','Y tế','Bất động sản','Thương mại','Sản xuất','Dịch vụ','Khác'] as $ind): ?>
                                            <option value="<?= $ind ?>" <?= ($c['industry'] ?? '') === $ind ? 'selected' : '' ?>><?= $ind ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Quy mô</label>
                                    <select name="company_size" class="form-select">
                                        <option value="">Chọn</option>
                                        <?php foreach (['1-10'=>'1-10 người','11-50'=>'11-50 người','51-200'=>'51-200 người','201-500'=>'201-500 người','500+'=>'500+ người'] as $v=>$l): ?>
                                            <option value="<?= $v ?>" <?= ($c['company_size'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Thành phố</label>
                                    <input type="text" class="form-control" name="city" value="<?= e($c['city'] ?? '') ?>">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Địa chỉ</label>
                                    <input type="text" class="form-control" name="address" value="<?= e($c['address'] ?? '') ?>">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Mô tả</label>
                                    <textarea name="description" class="form-control" rows="3"><?= e($c['description'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Quản lý</h5></div>
                        <div class="card-body">
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
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-save-line me-1"></i> Cập nhật</button>
                            <a href="<?= url('companies/' . $c['id']) ?>" class="btn btn-soft-secondary">Hủy</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

<script>
document.getElementById('btnLookupTax')?.addEventListener('click', function() {
    var taxCode = document.getElementById('taxCodeInput').value.trim();
    if (!taxCode) { document.getElementById('taxCodeInput').focus(); return; }

    var btn = this;
    var status = document.getElementById('taxLookupStatus');
    btn.disabled = true;
    btn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i> Đang tra...';
    status.classList.add('d-none');

    fetch('<?= url("api/tax-lookup") ?>?tax_code=' + encodeURIComponent(taxCode))
        .then(r => r.json())
        .then(data => {
            if (data.success && data.data) {
                var d = data.data;
                var nameInput = document.querySelector('input[name="name"]');
                var addressInput = document.querySelector('input[name="address"]');
                if (nameInput) nameInput.value = d.name || '';
                if (addressInput) addressInput.value = d.address || '';
                status.textContent = '✓ Đã tìm thấy: ' + (d.name || '');
                status.classList.remove('d-none', 'text-danger');
                status.classList.add('text-success');
            } else {
                status.textContent = 'Không tìm thấy doanh nghiệp với MST này';
                status.classList.remove('d-none', 'text-success');
                status.classList.add('text-danger');
            }
        })
        .catch(() => {
            status.textContent = 'Lỗi kết nối, vui lòng thử lại';
            status.classList.remove('d-none', 'text-success');
            status.classList.add('text-danger');
        })
        .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="ri-search-line me-1"></i> Tra cứu'; });
});

document.getElementById('taxCodeInput')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); document.getElementById('btnLookupTax').click(); }
});
</script>
