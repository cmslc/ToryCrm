<?php
$pageTitle = 'Sửa khách hàng';
$c = $contact;
$fl = \App\Services\ColumnService::getLabels('contacts');
$req = array_flip(\App\Services\ColumnService::getRequiredFields('contacts'));
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Sửa khách hàng</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('contacts/' . $c['id']) ?>" class="btn btn-soft-secondary">Quay lại</a>
        <button type="submit" form="contactForm" class="btn btn-primary"><i class="ri-save-line me-1"></i> Cập nhật</button>
    </div>
</div>

<form method="POST" action="<?= url('contacts/' . $c['id'] . '/update') ?>" enctype="multipart/form-data" id="contactForm">
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
                            <?php if (!empty($c['avatar'])): ?>
                            <img src="<?= asset($c['avatar']) ?>" class="rounded-circle" id="avatarPreview" style="width:64px;height:64px;object-fit:cover">
                            <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center d-none" style="width:64px;height:64px;font-size:24px" id="avatarInitial"><?= strtoupper(mb_substr($c['company_name'] ?? $c['first_name'] ?? '?', 0, 1)) ?></div>
                            <?php else: ?>
                            <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:64px;height:64px;font-size:24px" id="avatarInitial"><?= strtoupper(mb_substr($c['company_name'] ?? $c['first_name'] ?? '?', 0, 1)) ?></div>
                            <img src="" class="rounded-circle d-none" id="avatarPreview" style="width:64px;height:64px;object-fit:cover">
                            <?php endif; ?>
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

                    <!-- === DOANH NGHIỆP === -->
                    <div>
                        <div class="mb-3">
                            <label class="form-label"><?= $fl["tax_code"] ?? "Mã số thuế" ?><?= isset($req["tax_code"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="tax_code" id="taxCodeInput" value="<?= e($c['tax_code'] ?? '') ?>" placeholder="Nhập MST rồi bấm tra cứu">
                                <button type="button" class="btn btn-soft-info" id="btnLookupTax"><i class="ri-search-line"></i></button>
                            </div>
                            <div class="form-text text-success d-none" id="taxLookupStatus"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?= $fl["company_name"] ?? "Tên công ty" ?><?= isset($req["company_name"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                            <input type="text" class="form-control" name="company_name" id="companyNameInput" value="<?= e($c['company_name'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?= $fl["company_phone"] ?? "Điện thoại công ty" ?><?= isset($req["company_phone"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                            <input type="text" class="form-control" name="company_phone" value="<?= e($c['company_phone'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?= $fl["company_email"] ?? "Email công ty" ?><?= isset($req["company_email"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                            <input type="email" class="form-control" name="company_email" value="<?= e($c['company_email'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?= $fl["address"] ?? "Địa chỉ" ?><?= isset($req["address"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                            <input type="text" class="form-control" name="address" value="<?= e($c['address'] ?? '') ?>">
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label"><?= $fl["website"] ?? "Website" ?><?= isset($req["website"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                                <input type="text" class="form-control" name="website" value="<?= e($c['website'] ?? '') ?>" placeholder="https://">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label"><?= $fl["fax"] ?? "Fax" ?><?= isset($req["fax"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                                <input type="text" class="form-control" name="fax" value="<?= e($c['fax'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Chung -->
                    <div class="mb-3">
                        <label class="form-label"><?= $fl["account_code"] ?? "Mã KH" ?><?= isset($req["account_code"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                        <input type="text" class="form-control" name="account_code" value="<?= e($c['account_code'] ?? '') ?>">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label"><?= $fl["province"] ?? "Tỉnh/TP" ?><?= isset($req["province"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                            <input type="text" class="form-control" name="province" value="<?= e($c['province'] ?? '') ?>">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label"><?= $fl["district"] ?? "Quận/Huyện" ?><?= isset($req["district"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                            <input type="text" class="form-control" name="district" value="<?= e($c['district'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= $fl["description"] ?? "Mô tả" ?><?= isset($req["description"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                        <textarea name="description" class="form-control" rows="2"><?= e($c['description'] ?? '') ?></textarea>
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
                    <?php
                    $personList = !empty($contactPersons) ? $contactPersons : [null];
                    foreach ($personList as $idx => $cp):
                    ?>
                        <div class="contact-person-item border rounded p-3 mb-3" data-index="<?= $idx ?>">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="cp_primary[]" value="<?= $idx ?>" <?= ($cp === null && $idx === 0) || ($cp['is_primary'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-medium">Liên hệ chính</label>
                                </div>
                                <button type="button" class="btn btn-soft-danger btn-icon btn-remove-person <?= $idx === 0 ? 'd-none' : '' ?>"><i class="ri-delete-bin-line"></i></button>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="position-relative flex-shrink-0">
                                    <?php if (!empty($cp['avatar'])): ?>
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center cp-avatar-placeholder d-none" style="width:48px;height:48px"><i class="ri-user-line text-muted fs-20"></i></div>
                                    <img src="<?= asset($cp['avatar']) ?>" class="rounded-circle cp-avatar-preview" style="width:48px;height:48px;object-fit:cover">
                                    <?php else: ?>
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center cp-avatar-placeholder" style="width:48px;height:48px"><i class="ri-user-line text-muted fs-20"></i></div>
                                    <img src="" class="rounded-circle cp-avatar-preview d-none" style="width:48px;height:48px;object-fit:cover">
                                    <?php endif; ?>
                                    <label class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:20px;height:20px;cursor:pointer">
                                        <i class="ri-camera-line" style="font-size:10px"></i>
                                        <input type="file" name="cp_avatar[]" accept="image/*" class="d-none" onchange="var p=this.closest('.position-relative');var img=p.querySelector('.cp-avatar-preview');var ph=p.querySelector('.cp-avatar-placeholder');if(this.files[0]){var r=new FileReader();r.onload=function(e){img.src=e.target.result;img.classList.remove('d-none');ph.classList.add('d-none')};r.readAsDataURL(this.files[0])}">
                                    </label>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="row">
                                        <div class="col-5">
                                            <select name="cp_title[]" class="form-select">
                                                <option value="">Danh xưng</option>
                                                <option value="anh" <?= ($cp['title'] ?? '') === 'anh' ? 'selected' : '' ?>>Anh</option>
                                                <option value="chị" <?= ($cp['title'] ?? '') === 'chị' ? 'selected' : '' ?>>Chị</option>
                                                <option value="ông" <?= ($cp['title'] ?? '') === 'ông' ? 'selected' : '' ?>>Ông</option>
                                                <option value="bà" <?= ($cp['title'] ?? '') === 'bà' ? 'selected' : '' ?>>Bà</option>
                                            </select>
                                        </div>
                                        <div class="col-7">
                                            <input type="hidden" name="cp_person_id[]" class="cp-person-id" value="<?= (int)($cp['person_id'] ?? 0) ?>">
                                            <input type="text" class="form-control cp-name-input" name="cp_name[]" placeholder="Họ và tên *" value="<?= e($cp['full_name'] ?? '') ?>" required autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Vị trí</label>
                                <input type="text" class="form-control" name="cp_position[]" value="<?= e($cp['position'] ?? '') ?>">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Điện thoại</label>
                                <div class="input-group position-relative">
                                    <input type="text" class="form-control cp-phone-input" name="cp_phone[]" value="<?= e($cp['phone'] ?? '') ?>" autocomplete="off">
                                    <button type="button" class="btn btn-soft-info cp-phone-check-btn" onclick="checkCpPhone(this)"><i class="ri-search-line"></i></button>
                                    <div class="cp-person-dropdown dropdown-menu w-100" style="display:none;max-height:260px;overflow-y:auto"></div>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="cp_email[]" value="<?= e($cp['email'] ?? '') ?>">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Sinh nhật</label>
                                <input type="date" class="form-control" name="cp_dob[]" value="<?= e($cp['date_of_birth'] ?? '') ?>">
                            </div>
                            <div class="mb-0">
                                <label class="form-label">Ghi chú</label>
                                <textarea name="cp_note[]" class="form-control" rows="2"><?= e($cp['note'] ?? '') ?></textarea>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
                        <label class="form-label"><?= $fl["referrer_code"] ?? "Người giới thiệu" ?><?= isset($req["referrer_code"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                        <input type="text" class="form-control" name="referrer_code" value="<?= e($c['referrer_code'] ?? '') ?>" placeholder="Nhập tên khách hàng">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= $fl["status"] ?? "Mối quan hệ" ?><?= isset($req["status"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                        <select name="status" class="form-select" required>
                            <?php foreach ($contactStatuses ?? [] as $st): ?>
                            <option value="<?= e($st['slug']) ?>" <?= ($c['status'] ?? '') === $st['slug'] ? 'selected' : '' ?>><?= e($st['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nhãn</label>
                        <?php $selectedTags = \App\Services\TagService::getForEntity('contact', $c['id']); include BASE_PATH . '/resources/views/components/tag-input-form.php'; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= $fl["source_id"] ?? "Nguồn KH" ?><?= isset($req["source_id"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                        <div class="d-flex gap-2">
                            <select name="source_id" class="form-select flex-grow-1" required>
                                <option value="">Vui lòng chọn</option>
                                <?php foreach ($sources ?? [] as $source): ?>
                                    <option value="<?= $source['id'] ?>" <?= ($c['source_id'] ?? '') == $source['id'] ? 'selected' : '' ?>><?= e($source['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= $fl["owner_id"] ?? "Phụ trách" ?><?= isset($req["owner_id"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                        <?php
                        $deptGrouped = [];
                        foreach ($users ?? [] as $u) {
                            $deptName = $u['dept_name'] ?? 'Chưa phân phòng';
                            $deptGrouped[$deptName][] = $u;
                        }
                        ?>
                        <select name="owner_id" class="form-select searchable-select" required>
                            <option value="">Chọn</option>
                            <?php foreach ($deptGrouped as $dept => $deptUsers): ?>
                            <optgroup label="<?= e($dept) ?>">
                                <?php foreach ($deptUsers as $u): ?>
                                <option value="<?= $u['id'] ?>" data-avatar="<?= e($u['avatar'] ?? '') ?>" <?= ($c['owner_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= $fl["customer_group"] ?? "Nhóm KH" ?><?= isset($req["customer_group"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                        <?php $cg = $c['customer_group'] ?? ''; ?>
                        <select name="customer_group" class="form-select" required>
                            <option value="">Vui lòng chọn</option>
                            <option value="Khách Lẻ" <?= $cg === 'Khách Lẻ' ? 'selected' : '' ?>>Khách Lẻ</option>
                            <option value="Khách Dự Án" <?= $cg === 'Khách Dự Án' ? 'selected' : '' ?>>Khách Dự Án</option>
                            <option value="Khách Đại Lý" <?= $cg === 'Khách Đại Lý' ? 'selected' : '' ?>>Khách Đại Lý</option>
                            <option value="Doanh Nghiệp" <?= $cg === 'Doanh Nghiệp' ? 'selected' : '' ?>>Doanh Nghiệp</option>
                            <option value="VIP" <?= $cg === 'VIP' ? 'selected' : '' ?>>VIP</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= $fl["industry"] ?? "Ngành KD" ?><?= isset($req["industry"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                        <select name="industry" class="form-select">
                            <option value="">Vui lòng chọn</option>
                            <?php foreach ($industries ?? [] as $ind): ?>
                            <option value="<?= e($ind['industry']) ?>" <?= ($c['industry'] ?? '') === $ind['industry'] ? 'selected' : '' ?>><?= e($ind['industry']) ?></option>
                            <?php endforeach; ?>
                        </select>
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
    template.querySelectorAll('input:not([type=radio])').forEach(el => el.value = '');
    template.querySelectorAll('textarea').forEach(el => el.value = '');
    template.querySelectorAll('select').forEach(el => el.selectedIndex = 0);
    var radio = template.querySelector('input[type=radio]');
    radio.value = idx;
    radio.checked = false;
    // Show delete button
    var delBtn = template.querySelector('.btn-remove-person');
    if (delBtn) { delBtn.classList.remove('d-none'); } else {
        var header = template.querySelector('.d-flex.justify-content-between');
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-soft-danger btn-icon btn-remove-person';
        btn.innerHTML = '<i class="ri-delete-bin-line"></i>';
        header.appendChild(btn);
    }
    container.appendChild(template);
});

document.getElementById('contactPersonsContainer')?.addEventListener('click', function(e) {
    var btn = e.target.closest('.btn-remove-person');
    if (btn) btn.closest('.contact-person-item').remove();
});

// Tra cứu MST
document.getElementById('btnLookupTax')?.addEventListener('click', function() {
    var taxCode = document.getElementById('taxCodeInput').value.trim();
    if (!taxCode) { document.getElementById('taxCodeInput').focus(); return; }
    var btn = this, status = document.getElementById('taxLookupStatus');
    btn.disabled = true; btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i>'; status.classList.add('d-none');
    fetch('<?= url("api/tax-lookup") ?>?tax_code=' + encodeURIComponent(taxCode))
        .then(r => r.json())
        .then(data => {
            if (data.success && data.data) {
                var d = data.data;
                if (d.name) document.querySelector('[name="company_name"]').value = d.name;
                if (d.address) document.querySelector('[name="address"]').value = d.address;
                status.textContent = 'Đã tìm thấy: ' + (d.name || '');
                status.classList.remove('d-none', 'text-danger'); status.classList.add('text-success');
            } else {
                status.textContent = 'Không tìm thấy doanh nghiệp với MST này';
                status.classList.remove('d-none', 'text-success'); status.classList.add('text-danger');
            }
        })
        .catch(() => { status.textContent = 'Lỗi kết nối'; status.classList.remove('d-none', 'text-success'); status.classList.add('text-danger'); })
        .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="ri-search-line"></i>'; });
});
document.getElementById('taxCodeInput')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); document.getElementById('btnLookupTax').click(); }
});

// Check trùng (exclude current ID)
var dupTimer = null;
function checkDuplicate(field, value) {
    if (!value || value.length < 3) return;
    clearTimeout(dupTimer);
    dupTimer = setTimeout(function() {
        fetch('<?= url("contacts/check-duplicate") ?>?field=' + field + '&value=' + encodeURIComponent(value) + '&exclude_id=<?= $c['id'] ?>')
        .then(r => r.json())
        .then(function(data) {
            var alertId = 'dup-alert-' + field;
            var existing = document.getElementById(alertId);
            if (existing) existing.remove();
            if (data.found) {
                var input = document.querySelector('[name="' + field + '"]') || document.getElementById('taxCodeInput');
                var alert = document.createElement('div');
                alert.id = alertId;
                alert.className = 'alert alert-warning py-1 px-2 mt-1';
                alert.style.fontSize = '13px';
                if (data.can_see) {
                    alert.innerHTML = '<div class="d-flex align-items-center justify-content-between">'
                        + '<span><i class="ri-error-warning-line me-1"></i><strong>Trùng!</strong> ' + data.name + (data.account_code ? ' (' + data.account_code + ')' : '') + ' - PT: ' + (data.owner_name || '') + '</span>'
                        + '<a href="<?= url("contacts") ?>/' + data.id + '" target="_blank" class="btn btn-warning py-0 px-2" style="font-size:12px">Mở KH</a>'
                        + '</div>';
                } else {
                    alert.innerHTML = '<i class="ri-error-warning-line me-1"></i><strong>Trùng!</strong> KH với thông tin này đã tồn tại, phụ trách: <strong>' + (data.owner_name || 'N/A') + '</strong>. Liên hệ quản lý để xử lý.';
                }
                input.closest('.mb-3, .input-group')?.parentNode.insertBefore(alert, input.closest('.mb-3, .input-group').nextSibling);
            }
        });
    }, 500);
}
document.getElementById('taxCodeInput')?.addEventListener('blur', function() { checkDuplicate('tax_code', this.value.trim()); });
document.querySelectorAll('[name="phone"]').forEach(function(el) { el.addEventListener('blur', function() { checkDuplicate('phone', this.value.trim()); }); });
document.querySelectorAll('[name="email"]').forEach(function(el) { el.addEventListener('blur', function() { checkDuplicate('email', this.value.trim()); }); });

// Check SĐT người liên hệ
function checkCpPhone(btn) {
    var input = btn.closest('.input-group').querySelector('.cp-phone-input');
    var phone = input.value.trim();
    if (!phone) { input.focus(); return; }
    var old = input.closest('.mb-2')?.querySelector('.cp-phone-alert');
    if (old) old.remove();
    fetch('<?= url("contacts/check-duplicate") ?>?field=phone&value=' + encodeURIComponent(phone) + '&exclude_id=<?= $c['id'] ?>')
    .then(r => r.json())
    .then(function(data) {
        var alertDiv = document.createElement('div');
        alertDiv.style.fontSize = '13px';
        if (data.found) {
            alertDiv.className = 'cp-phone-alert mt-2 p-2 border rounded border-warning bg-warning-subtle';
            if (data.can_see) {
                alertDiv.innerHTML = '<div class="d-flex align-items-center justify-content-between"><span><i class="ri-error-warning-line text-warning me-1"></i>SĐT đã tồn tại: <strong>' + data.name + '</strong>' + (data.account_code ? ' (' + data.account_code + ')' : '') + '</span><a href="<?= url("contacts") ?>/' + data.id + '" target="_blank" class="btn btn-warning py-0 px-2" style="font-size:12px">Mở KH</a></div>';
            } else {
                alertDiv.innerHTML = '<div class="d-flex align-items-center gap-2"><i class="ri-error-warning-line text-warning fs-18"></i><span>SĐT này đã tồn tại trong hệ thống, phụ trách: <strong>' + (data.owner_name || 'N/A') + '</strong></span></div>';
            }
        } else {
            alertDiv.className = 'cp-phone-alert mt-2 p-2 border rounded bg-success-subtle';
            alertDiv.innerHTML = '<div class="d-flex align-items-center gap-2"><i class="ri-check-circle-line text-success fs-18"></i><span>SĐT chưa có trong hệ thống.</span></div>';
        }
        input.closest('.mb-2')?.appendChild(alertDiv);
    });
}

// ---- Phase 3: Link existing person when phone/name matches ----
(function() {
    function escapeHtml(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
    var searchTimer = null;

    function searchPersons(input) {
        clearTimeout(searchTimer);
        var wrap = input.closest('.input-group') || input.parentElement;
        var dd = wrap.querySelector('.cp-person-dropdown');
        if (!dd) return;
        var q = input.value.trim();
        if (q.length < 3) { dd.style.display = 'none'; return; }

        searchTimer = setTimeout(function() {
            fetch('<?= url('persons/search') ?>?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(list => {
                    if (!Array.isArray(list) || list.length === 0) { dd.style.display = 'none'; return; }
                    dd.innerHTML = '<div class="dropdown-header fs-12 text-muted">Đã có người này — click để dùng lại:</div>';
                    list.forEach(function(p) {
                        var empTxt = (p.employments || []).map(function(e) { return e.company_name + (e.position ? ' (' + e.position + ')' : ''); }).join(', ');
                        var item = document.createElement('a');
                        item.className = 'dropdown-item py-2';
                        item.href = '#';
                        item.innerHTML = '<div class="d-flex align-items-start gap-2">' +
                            '<div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px;height:32px;font-size:13px">' + escapeHtml((p.full_name||'?').charAt(0).toUpperCase()) + '</div>' +
                            '<div class="flex-grow-1" style="white-space:normal">' +
                                '<div class="fw-medium">' + escapeHtml(p.full_name || '') + '</div>' +
                                '<div class="text-muted fs-12">' + escapeHtml(p.phone || '') + (p.email ? ' · ' + escapeHtml(p.email) : '') + '</div>' +
                                (empTxt ? '<div class="text-muted fs-12"><i class="ri-building-line me-1"></i>' + escapeHtml(empTxt) + '</div>' : '') +
                            '</div>' +
                        '</div>';
                        item.addEventListener('click', function(e) {
                            e.preventDefault();
                            var root = wrap.closest('.row, .cp-row, .card, .mb-3') || wrap.parentElement.parentElement.parentElement;
                            var nameEl = root.querySelector('.cp-name-input');
                            var phoneEl = root.querySelector('.cp-phone-input');
                            var emailEl = root.querySelector('[name="cp_email[]"]');
                            var pidEl = root.querySelector('.cp-person-id');
                            if (nameEl && !nameEl.value.trim()) nameEl.value = p.full_name || '';
                            if (phoneEl) phoneEl.value = p.phone || phoneEl.value;
                            if (emailEl && !emailEl.value.trim()) emailEl.value = p.email || '';
                            if (pidEl) pidEl.value = p.id;
                            dd.style.display = 'none';
                        });
                        dd.appendChild(item);
                    });
                    dd.style.display = 'block';
                })
                .catch(() => { dd.style.display = 'none'; });
        }, 300);
    }

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('cp-phone-input') || e.target.classList.contains('cp-name-input')) {
            searchPersons(e.target);
        }
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.input-group')) {
            document.querySelectorAll('.cp-person-dropdown').forEach(function(dd) { dd.style.display = 'none'; });
        }
    });
})();
</script>
