<?php $pageTitle = 'Thêm khách hàng'; ?>

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Thêm khách hàng</h4>
                    <div class="d-flex gap-2">
                        <a href="<?= url('contacts') ?>" class="btn btn-soft-secondary">Quay lại</a>
                        <button type="submit" form="contactForm" class="btn btn-primary"><i class="ri-save-line me-1"></i> Lưu</button>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="<?= url('contacts/store') ?>" enctype="multipart/form-data" id="contactForm">
            <?= csrf_field() ?>
            <div class="row">
                <!-- CỘT TRÁI: Thông tin khách hàng -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="ri-user-3-line me-1"></i> Thông tin khách hàng</h5>
                        </div>
                        <div class="card-body">
                            <!-- Avatar -->
                            <div class="mb-3 d-flex align-items-center gap-3">
                                <div class="position-relative">
                                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:64px;height:64px;font-size:24px" id="avatarInitial">?</div>
                                    <img src="" class="rounded-circle d-none" id="avatarPreview" style="width:64px;height:64px;object-fit:cover">
                                    <label for="avatarInput" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:24px;height:24px;cursor:pointer">
                                        <i class="ri-camera-line fs-12"></i>
                                    </label>
                                    <input type="file" name="avatar" id="avatarInput" accept="image/*" class="d-none">
                                </div>
                                <div class="text-muted fs-13">Ảnh đại diện <br><small>JPG, PNG tối đa 5MB</small></div>
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
                            <div class="mb-3">
                                <label class="form-label">Mã KH</label>
                                <input type="text" class="form-control" name="account_code" value="<?= old('account_code') ?>" placeholder="Tự tạo nếu để trống">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="full_name" value="<?= old('full_name') ?>" placeholder="VD: Nguyễn Văn A hoặc Công ty TNHH ABC" required>
                                <small class="text-muted">Nhập tên cá nhân hoặc tên công ty</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tên công ty</label>
                                <input type="text" class="form-control" name="company_name" value="<?= old('company_name') ?>" placeholder="Bỏ trống nếu là KH cá nhân">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Điện thoại</label>
                                <input type="text" class="form-control" name="phone" value="<?= old('phone') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="<?= old('email') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Địa chỉ</label>
                                <input type="text" class="form-control" name="address" value="<?= old('address') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Số CF</label>
                                <input type="text" class="form-control" name="referrer_code" value="<?= old('referrer_code') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mô tả</label>
                                <textarea name="description" class="form-control" rows="3"><?= old('description') ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mã số thuế (KH cá nhân nhập SĐT)</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="tax_code" id="taxCodeInput" value="<?= old('tax_code') ?>" placeholder="Nhập MST rồi bấm tra cứu">
                                    <button type="button" class="btn btn-soft-info" id="btnLookupTax"><i class="ri-search-line"></i></button>
                                </div>
                                <div class="form-text text-success d-none" id="taxLookupStatus"></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tỉnh/thành phố</label>
                                    <input type="text" class="form-control" name="province" value="<?= old('province') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Quận/huyện</label>
                                    <input type="text" class="form-control" name="district" value="<?= old('district') ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Website</label>
                                <input type="text" class="form-control" name="website" value="<?= old('website') ?>" placeholder="https://">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fax</label>
                                <input type="text" class="form-control" name="fax" value="<?= old('fax') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Sinh nhật</label>
                                <input type="date" class="form-control" name="date_of_birth" value="<?= old('date_of_birth') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CỘT GIỮA: Thông tin người liên hệ -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0"><i class="ri-contacts-book-line me-1"></i> Thông tin người liên hệ</h5>
                            <button type="button" class="btn btn-soft-primary" id="btnAddPerson"><i class="ri-add-line me-1"></i> Thêm người liên hệ</button>
                        </div>
                        <div class="card-body" id="contactPersonsContainer">
                            <!-- Person template -->
                            <div class="contact-person-item border rounded p-3 mb-3" data-index="0">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="cp_primary[]" value="0" checked>
                                        <label class="form-check-label fw-medium">Liên hệ chính</label>
                                    </div>
                                    <button type="button" class="btn btn-soft-danger btn-icon btn-remove-person d-none"><i class="ri-delete-bin-line"></i></button>
                                </div>
                                <div class="row">
                                    <div class="col-4 mb-2">
                                        <label class="form-label">Danh xưng</label>
                                        <select name="cp_title[]" class="form-select">
                                            <option value="">Chọn</option>
                                            <option value="anh">Anh</option>
                                            <option value="chị">Chị</option>
                                            <option value="ông">Ông</option>
                                            <option value="bà">Bà</option>
                                        </select>
                                    </div>
                                    <div class="col-8 mb-2">
                                        <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="cp_name[]" required>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Vị trí</label>
                                    <input type="text" class="form-control" name="cp_position[]">
                                </div>
                                <div class="row">
                                    <div class="col-6 mb-2">
                                        <label class="form-label">Điện thoại</label>
                                        <input type="text" class="form-control" name="cp_phone[]">
                                    </div>
                                    <div class="col-6 mb-2">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="cp_email[]">
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Sinh nhật</label>
                                    <input type="date" class="form-control" name="cp_dob[]">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">Ghi chú</label>
                                    <textarea name="cp_note[]" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CỘT PHẢI: Thông tin bổ sung -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="ri-settings-3-line me-1"></i> Thông tin bổ sung</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Người giới thiệu</label>
                                <input type="text" class="form-control" name="referrer_code" value="<?= old('referrer_code') ?>" placeholder="Nhập tên khách hàng">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mối quan hệ <span class="text-danger">*</span></label>
                                <select name="status" class="form-select">
                                    <?php foreach ($contactStatuses ?? [] as $st): ?>
                                    <option value="<?= e($st['slug']) ?>"><?= e($st['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nguồn khách hàng <span class="text-danger">*</span></label>
                                <div class="d-flex gap-2">
                                    <select name="source_id" class="form-select flex-grow-1">
                                        <option value="">Vui lòng chọn</option>
                                        <?php foreach ($sources ?? [] as $source): ?>
                                            <option value="<?= $source['id'] ?>"><?= e($source['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Người phụ trách <span class="text-danger">*</span></label>
                                <?php
                                $deptGrouped = [];
                                foreach ($users ?? [] as $u) {
                                    $deptName = $u['dept_name'] ?? 'Chưa phân phòng';
                                    $deptGrouped[$deptName][] = $u;
                                }
                                ?>
                                <select name="owner_id" class="form-select searchable-select">
                                    <option value="">Chọn</option>
                                    <?php foreach ($deptGrouped as $dept => $deptUsers): ?>
                                    <optgroup label="<?= e($dept) ?>">
                                        <?php foreach ($deptUsers as $u): ?>
                                        <option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nhóm khách hàng <span class="text-danger">*</span></label>
                                <select name="customer_group" class="form-select">
                                    <option value="">Vui lòng chọn</option>
                                    <option value="Khách Lẻ">Khách Lẻ</option>
                                    <option value="Khách Dự Án">Khách Dự Án</option>
                                    <option value="Khách Đại Lý">Khách Đại Lý</option>
                                    <option value="Doanh Nghiệp">Doanh Nghiệp</option>
                                    <option value="VIP">VIP</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ngành kinh doanh</label>
                                <select name="industry" class="form-select">
                                    <option value="">Vui lòng chọn</option>
                                    <?php foreach ($industries ?? [] as $ind): ?>
                                    <option value="<?= e($ind['industry']) ?>"><?= e($ind['industry']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_private" value="1" id="isPrivate">
                                    <label class="form-check-label" for="isPrivate">KH riêng tư (chỉ người phụ trách xem)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

<script>
// Thêm người liên hệ
document.getElementById('btnAddPerson')?.addEventListener('click', function() {
    var container = document.getElementById('contactPersonsContainer');
    var items = container.querySelectorAll('.contact-person-item');
    var idx = items.length;
    var template = items[0].cloneNode(true);
    template.setAttribute('data-index', idx);
    // Clear values
    template.querySelectorAll('input:not([type=radio])').forEach(el => el.value = '');
    template.querySelectorAll('textarea').forEach(el => el.value = '');
    template.querySelectorAll('select').forEach(el => el.selectedIndex = 0);
    // Update radio
    var radio = template.querySelector('input[type=radio]');
    radio.value = idx;
    radio.checked = false;
    // Show delete button
    template.querySelector('.btn-remove-person').classList.remove('d-none');
    container.appendChild(template);
});

// Remove person
document.getElementById('contactPersonsContainer')?.addEventListener('click', function(e) {
    var btn = e.target.closest('.btn-remove-person');
    if (btn) {
        btn.closest('.contact-person-item').remove();
    }
});

// Tra cứu MST
document.getElementById('btnLookupTax')?.addEventListener('click', function() {
    var taxCode = document.getElementById('taxCodeInput').value.trim();
    if (!taxCode) { document.getElementById('taxCodeInput').focus(); return; }
    var btn = this;
    var status = document.getElementById('taxLookupStatus');
    btn.disabled = true;
    btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i>';
    status.classList.add('d-none');
    fetch('<?= url("api/tax-lookup") ?>?tax_code=' + encodeURIComponent(taxCode))
        .then(r => r.json())
        .then(data => {
            if (data.success && data.data) {
                var d = data.data;
                var nameEl = document.querySelector('[name="company_name"]');
                var addrEl = document.querySelector('[name="address"]');
                if (nameEl && d.name) nameEl.value = d.name;
                if (addrEl && d.address) addrEl.value = d.address;
                status.textContent = '✓ Đã tìm thấy: ' + (d.name || '');
                status.classList.remove('d-none', 'text-danger');
                status.classList.add('text-success');
            } else {
                status.textContent = 'Không tìm thấy doanh nghiệp với MST này';
                status.classList.remove('d-none', 'text-success');
                status.classList.add('text-danger');
            }
        })
        .catch(() => { status.textContent = 'Lỗi kết nối'; status.classList.remove('d-none', 'text-success'); status.classList.add('text-danger'); })
        .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="ri-search-line"></i>'; });
});
document.getElementById('taxCodeInput')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); document.getElementById('btnLookupTax').click(); }
});
</script>
