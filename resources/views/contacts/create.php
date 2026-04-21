<?php
$pageTitle = 'Thêm khách hàng';
$fl = \App\Services\ColumnService::getLabels('contacts');
$req = array_flip(\App\Services\ColumnService::getRequiredFields('contacts'));
?>

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

                            <!-- === Thông tin doanh nghiệp === -->
                            <div>
                                <div class="mb-3">
                                    <label class="form-label"><?= $fl["tax_code"] ?? "Mã số thuế" ?><?= isset($req["tax_code"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="tax_code" id="taxCodeInput" value="<?= old('tax_code') ?>" placeholder="Nhập MST rồi bấm tra cứu">
                                        <button type="button" class="btn btn-soft-info" id="btnLookupTax"><i class="ri-search-line"></i></button>
                                    </div>
                                    <div class="form-text text-success d-none" id="taxLookupStatus"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?= $fl["company_name"] ?? "Tên công ty" ?><?= isset($req["company_name"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                                    <input type="text" class="form-control" name="company_name" id="companyNameInput" value="<?= old('company_name') ?>" placeholder="Tự động điền khi tra cứu MST">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?= $fl["company_phone"] ?? "Điện thoại công ty" ?><?= isset($req["company_phone"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                                    <input type="text" class="form-control" name="company_phone" value="<?= old('company_phone') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?= $fl["company_email"] ?? "Email công ty" ?><?= isset($req["company_email"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                                    <input type="email" class="form-control" name="company_email" value="<?= old('company_email') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?= $fl["address"] ?? "Địa chỉ" ?><?= isset($req["address"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                                    <input type="text" class="form-control" name="address" value="<?= old('address') ?>">
                                </div>
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label class="form-label"><?= $fl["website"] ?? "Website" ?><?= isset($req["website"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                                        <input type="text" class="form-control" name="website" value="<?= old('website') ?>" placeholder="https://">
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label class="form-label"><?= $fl["fax"] ?? "Fax" ?><?= isset($req["fax"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                                        <input type="text" class="form-control" name="fax" value="<?= old('fax') ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Chung -->
                            <div class="mb-3">
                                <label class="form-label"><?= $fl["account_code"] ?? "Mã KH" ?><?= isset($req["account_code"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                                <input type="text" class="form-control" name="account_code" value="<?= old('account_code') ?>" placeholder="Tự tạo nếu để trống">
                            </div>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label"><?= $fl["province"] ?? "Tỉnh/TP" ?><?= isset($req["province"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                                    <input type="text" class="form-control" name="province" value="<?= old('province') ?>">
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label"><?= $fl["district"] ?? "Quận/Huyện" ?><?= isset($req["district"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                                    <input type="text" class="form-control" name="district" value="<?= old('district') ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?= $fl["description"] ?? "Mô tả" ?><?= isset($req["description"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                                <textarea name="description" class="form-control" rows="2"><?= old('description') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CỘT GIỮA: Thông tin người liên hệ -->
                <div class="col-lg-4" id="colContactPersons">
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
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <div class="position-relative flex-shrink-0">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center cp-avatar-placeholder" style="width:48px;height:48px"><i class="ri-user-line text-muted fs-20"></i></div>
                                        <img src="" class="rounded-circle cp-avatar-preview d-none" style="width:48px;height:48px;object-fit:cover">
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
                                                    <option value="anh">Anh</option>
                                                    <option value="chị">Chị</option>
                                                    <option value="ông">Ông</option>
                                                    <option value="bà">Bà</option>
                                                </select>
                                            </div>
                                            <div class="col-7">
                                                <input type="text" class="form-control" name="cp_name[]" placeholder="Họ và tên *" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Vị trí</label>
                                    <input type="text" class="form-control" name="cp_position[]">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Điện thoại</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control cp-phone-input" name="cp_phone[]">
                                        <button type="button" class="btn btn-soft-info cp-phone-check-btn" onclick="checkCpPhone(this)"><i class="ri-search-line"></i></button>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="cp_email[]">
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
                                <label class="form-label"><?= $fl["referrer_code"] ?? "Người giới thiệu" ?><?= isset($req["referrer_code"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                                <input type="text" class="form-control" name="referrer_code" value="<?= old('referrer_code') ?>" placeholder="Nhập tên khách hàng">
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?= $fl["status"] ?? "Mối quan hệ" ?><?= isset($req["status"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                                <select name="status" class="form-select" required>
                                    <?php foreach ($contactStatuses ?? [] as $st): ?>
                                    <option value="<?= e($st['slug']) ?>"><?= e($st['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nhãn</label>
                                <?php $selectedTags = []; include BASE_PATH . '/resources/views/components/tag-input-form.php'; ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?= $fl["source_id"] ?? "Nguồn KH" ?><?= isset($req["source_id"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                                <div class="d-flex gap-2">
                                    <select name="source_id" class="form-select flex-grow-1" required>
                                        <option value="">Vui lòng chọn</option>
                                        <?php foreach ($sources ?? [] as $source): ?>
                                            <option value="<?= $source['id'] ?>"><?= e($source['name']) ?></option>
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
                                        <option value="<?= $u['id'] ?>" data-avatar="<?= e($u['avatar'] ?? '') ?>" <?= $u['id'] == ($_SESSION['user']['id'] ?? 0) ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?= $fl["customer_group"] ?? "Nhóm KH" ?><?= isset($req["customer_group"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                                <select name="customer_group" class="form-select" required>
                                    <option value="">Vui lòng chọn</option>
                                    <option value="Khách Lẻ" selected>Khách Lẻ</option>
                                    <option value="Khách Dự Án">Khách Dự Án</option>
                                    <option value="Khách Đại Lý">Khách Đại Lý</option>
                                    <option value="Doanh Nghiệp">Doanh Nghiệp</option>
                                    <option value="VIP">VIP</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?= $fl["industry"] ?? "Ngành KD" ?><?= isset($req["industry"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                                <select name="industry" class="form-select">
                                    <option value="">Vui lòng chọn</option>
                                    <?php foreach ($industries ?? [] as $ind): ?>
                                    <option value="<?= e($ind['industry']) ?>"><?= e($ind['industry']) ?></option>
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
    // Clear values
    template.querySelectorAll('input:not([type=radio]):not([type=file])').forEach(el => el.value = '');
    template.querySelectorAll('input[type=file]').forEach(el => el.value = '');
    template.querySelectorAll('textarea').forEach(el => el.value = '');
    template.querySelectorAll('select').forEach(el => el.selectedIndex = 0);
    // Reset avatar
    var avImg = template.querySelector('.cp-avatar-preview');
    var avPh = template.querySelector('.cp-avatar-placeholder');
    if (avImg) { avImg.classList.add('d-none'); avImg.src = ''; }
    if (avPh) { avPh.classList.remove('d-none'); }
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
    // Gọi song song: tra cứu MST + check trùng
    Promise.all([
        fetch('<?= url("api/tax-lookup") ?>?tax_code=' + encodeURIComponent(taxCode)).then(r => r.json()).catch(() => null),
        fetch('<?= url("contacts/check-duplicate") ?>?field=tax_code&value=' + encodeURIComponent(taxCode)).then(r => r.json()).catch(() => null)
    ]).then(function(results) {
        var taxData = results[0];
        var dupData = results[1];

        // Fill thông tin từ tra cứu MST
        if (taxData && taxData.success && taxData.data) {
            var d = taxData.data;
            var nameEl = document.getElementById('companyNameInput') || document.querySelector('[name="company_name"]');
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

        // Hiện cảnh báo trùng
        var alertId = 'dup-alert-tax_code';
        var existing = document.getElementById(alertId);
        if (existing) existing.remove();
        if (dupData && dupData.found) {
            duplicateContactId = dupData.existing_id || dupData.id || null;
            var input = document.getElementById('taxCodeInput');
            var alert = document.createElement('div');
            alert.id = alertId;
            alert.className = 'alert alert-warning py-1 px-2 mt-1';
            alert.style.fontSize = '13px';
            if (dupData.can_see) {
                alert.innerHTML = '<div class="d-flex align-items-center justify-content-between">'
                    + '<span><i class="ri-error-warning-line me-1"></i><strong>Trùng!</strong> ' + dupData.name + (dupData.account_code ? ' (' + dupData.account_code + ')' : '') + ' - PT: ' + (dupData.owner_name || '') + '</span>'
                    + '<a href="<?= url("contacts") ?>/' + dupData.id + '" target="_blank" class="btn btn-warning py-0 px-2" style="font-size:12px">Mở KH</a>'
                    + '</div>';
            } else {
                alert.innerHTML = '<i class="ri-error-warning-line me-1"></i><strong>Trùng!</strong> KH với MST này đã tồn tại, phụ trách: <strong>' + (dupData.owner_name || 'N/A') + '</strong>. Liên hệ quản lý để xử lý.';
            }
            input.closest('.mb-3')?.parentNode.insertBefore(alert, input.closest('.mb-3').nextSibling);
        }
    }).finally(() => { btn.disabled = false; btn.innerHTML = '<i class="ri-search-line"></i>'; });
});
document.getElementById('taxCodeInput')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); document.getElementById('btnLookupTax').click(); }
});
document.getElementById('taxCodeInput')?.addEventListener('paste', function() {
    setTimeout(function() { document.getElementById('btnLookupTax').click(); }, 100);
});

// === Check trùng real-time ===
var dupTimer = null;
function checkDuplicate(field, value) {
    if (!value || value.length < 3) return;
    clearTimeout(dupTimer);
    dupTimer = setTimeout(function() {
        fetch('<?= url("contacts/check-duplicate") ?>?field=' + field + '&value=' + encodeURIComponent(value))
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
                    alert.innerHTML = '<div><i class="ri-error-warning-line me-1"></i><strong>Trùng!</strong> KH với thông tin này đã tồn tại, phụ trách: <strong>' + (data.owner_name || 'N/A') + '</strong></div>'
                        + '<div class="mt-2 p-2 bg-white rounded border">'
                        + '<p class="mb-2" style="font-size:12px">Nhập người liên hệ để gửi yêu cầu thêm vào doanh nghiệp này:</p>'
                        + '<div class="row g-2">'
                        + '<div class="col-4"><select class="form-select" id="mrTitle" style="font-size:12px"><option value="">Danh xưng</option><option value="anh">Anh</option><option value="chị">Chị</option><option value="ông">Ông</option><option value="bà">Bà</option></select></div>'
                        + '<div class="col-8"><input type="text" class="form-control" id="mrName" placeholder="Họ và tên *" style="font-size:12px"></div>'
                        + '<div class="col-6"><input type="text" class="form-control" id="mrPhone" placeholder="SĐT *" style="font-size:12px"></div>'
                        + '<div class="col-6"><input type="text" class="form-control" id="mrEmail" placeholder="Email" style="font-size:12px"></div>'
                        + '<div class="col-8"><input type="text" class="form-control" id="mrPosition" placeholder="Chức vụ" style="font-size:12px"></div>'
                        + '<div class="col-4"><button type="button" class="btn btn-primary w-100" style="font-size:12px" onclick="submitMergeRequest(' + (data.existing_id || 0) + ')"><i class="ri-send-plane-line me-1"></i>Gửi</button></div>'
                        + '</div></div>';
                }
                input.closest('.mb-3, .input-group')?.parentNode.insertBefore(alert, input.closest('.mb-3, .input-group').nextSibling);
            }
        });
    }, 500);
}

// Track duplicate contact ID when MST matches
var duplicateContactId = null;

function checkCpPhone(btn) {
    var input = btn.closest('.input-group').querySelector('.cp-phone-input');
    var phone = input.value.trim();
    if (!phone) { input.focus(); return; }
    var old = input.closest('.mb-2')?.querySelector('.cp-phone-alert');
    if (old) old.remove();

    if (duplicateContactId) {
        // Check against specific contact's persons
        fetch('<?= url("contacts/check-person-phone") ?>?contact_id=' + duplicateContactId + '&phone=' + encodeURIComponent(phone))
        .then(r => r.json())
        .then(function(data) {
            var alertDiv = document.createElement('div');
            if (data.exists) {
                alertDiv.className = 'cp-phone-alert mt-2 p-2 border rounded border-warning bg-warning-subtle';
                alertDiv.style.fontSize = '13px';
                alertDiv.innerHTML = '<div class="d-flex align-items-center gap-2"><i class="ri-error-warning-line text-warning fs-18"></i><span>SĐT này <strong>đã có</strong> trong doanh nghiệp. Người liên hệ đã tồn tại, không cần thêm.</span></div>';
            } else {
                alertDiv.className = 'cp-phone-alert mt-2 p-2 border rounded bg-light';
                alertDiv.style.fontSize = '13px';
                alertDiv.innerHTML = '<div class="d-flex align-items-center gap-2 mb-2"><i class="ri-check-circle-line text-success fs-18"></i><span>SĐT này <strong>chưa có</strong> trong doanh nghiệp. Bạn có thể gửi yêu cầu để được thêm người liên hệ này.</span></div>'
                    + '<button type="button" class="btn btn-success w-100" onclick="sendMergeFromPerson(this)"><i class="ri-send-plane-line me-1"></i>Gửi yêu cầu thêm người liên hệ</button>';
            }
            input.closest('.mb-2')?.appendChild(alertDiv);
        });
    } else {
        // Check general duplicate in all contacts
        fetch('<?= url("contacts/check-duplicate") ?>?field=phone&value=' + encodeURIComponent(phone))
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
                alertDiv.innerHTML = '<div class="d-flex align-items-center gap-2"><i class="ri-check-circle-line text-success fs-18"></i><span>SĐT chưa có trong hệ thống. Có thể tạo mới.</span></div>';
            }
            input.closest('.mb-2')?.appendChild(alertDiv);
        });
    }
}

// Check person phone: blur + Enter + paste
document.addEventListener('keydown', function(e) {
    if (!e.target.classList.contains('cp-phone-input')) return;
    if (e.key === 'Enter') { e.preventDefault(); checkCpPhone(e.target.closest('.input-group').querySelector('.cp-phone-check-btn')); }
}, true);
document.addEventListener('paste', function(e) {
    if (!e.target.classList.contains('cp-phone-input')) return;
    setTimeout(function() { checkCpPhone(e.target.closest('.input-group').querySelector('.cp-phone-check-btn')); }, 100);
}, true);
document.addEventListener('blur', function(e) {
    if (!e.target.classList.contains('cp-phone-input')) return;
    if (!duplicateContactId) return;
    var phone = e.target.value.trim();
    if (!phone || phone.length < 8) return;

    // Don't remove existing alert on blur - keep it visible
    var old = e.target.closest('.mb-2')?.querySelector('.cp-phone-alert');
    if (old) return;

    fetch('<?= url("contacts/check-person-phone") ?>?contact_id=' + duplicateContactId + '&phone=' + encodeURIComponent(phone))
    .then(r => r.json())
    .then(function(data) {
        var alertDiv = document.createElement('div');
        alertDiv.className = 'cp-phone-alert mt-1';
        alertDiv.style.fontSize = '12px';
        if (data.exists) {
            alertDiv.className += ' text-warning';
            alertDiv.className = 'cp-phone-alert mt-2 p-2 border rounded border-warning bg-warning-subtle';
            alertDiv.style.fontSize = '13px';
            alertDiv.innerHTML = '<div class="d-flex align-items-center gap-2"><i class="ri-error-warning-line text-warning fs-18"></i><span>SĐT này <strong>đã có</strong> trong doanh nghiệp. Người liên hệ đã tồn tại, không cần thêm.</span></div>';
        } else {
            alertDiv.className += ' text-success';
            alertDiv.className = 'cp-phone-alert mt-2 p-2 border rounded bg-light';
            alertDiv.style.fontSize = '13px';
            alertDiv.innerHTML = '<div class="d-flex align-items-center gap-2 mb-2"><i class="ri-check-circle-line text-success fs-18"></i><span>SĐT này <strong>chưa có</strong> trong doanh nghiệp. Bạn có thể gửi yêu cầu để được thêm người liên hệ này.</span></div>'
                + '<button type="button" class="btn btn-success w-100" onclick="sendMergeFromPerson(this)"><i class="ri-send-plane-line me-1"></i>Gửi yêu cầu thêm người liên hệ</button>';
        }
        e.target.closest('.col-6')?.appendChild(alertDiv);
    });
}, true);

// Send merge from person form (inline button)
function sendMergeFromPerson(btn) {
    if (!duplicateContactId) { alert('Không tìm thấy KH trùng'); return; }
    var personItem = btn.closest('.contact-person-item');
    if (!personItem) { alert('Không tìm thấy thông tin người LH'); return; }
    var title = personItem.querySelector('[name="cp_title[]"]')?.value || '';
    var name = personItem.querySelector('[name="cp_name[]"]')?.value?.trim() || '';
    var phone = personItem.querySelector('[name="cp_phone[]"]')?.value?.trim() || '';
    var email = personItem.querySelector('[name="cp_email[]"]')?.value?.trim() || '';
    var position = personItem.querySelector('[name="cp_position[]"]')?.value?.trim() || '';
    if (!name) { alert('Vui lòng nhập họ tên người liên hệ'); return; }
    btn.disabled = true;
    btn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i>Đang gửi...';
    fetch('<?= url("merge-requests/store") ?>', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'<?= csrf_token() ?>'},
        body: JSON.stringify({ existing_contact_id: duplicateContactId, cp_title: title, cp_name: name, cp_phone: phone, cp_email: email, cp_position: position })
    }).then(r => r.json()).then(function(data) {
        var alertEl = btn.closest('.cp-phone-alert');
        if (data.success) {
            alertEl.className = 'cp-phone-alert mt-1 text-success';
            alertEl.style.fontSize = '12px';
            alertEl.innerHTML = '<i class="ri-check-double-line me-1"></i>Đã gửi yêu cầu! Chờ người phụ trách duyệt.';
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="ri-send-plane-line me-1"></i>Gửi yêu cầu thêm người LH';
            alert(data.error || 'Lỗi');
        }
    }).catch(function() { btn.disabled = false; btn.innerHTML = '<i class="ri-send-plane-line me-1"></i>Gửi lại'; });
}

// Submit merge request (from MST duplicate alert - legacy)
function submitMergeRequest(contactId) {
    var name = document.getElementById('mrName')?.value.trim();
    var phone = document.getElementById('mrPhone')?.value.trim();
    if (!name) { alert('Vui lòng nhập họ tên'); return; }
    if (!phone) { alert('Vui lòng nhập số điện thoại'); return; }
    fetch('<?= url("merge-requests/store") ?>', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'<?= csrf_token() ?>'},
        body: JSON.stringify({
            existing_contact_id: contactId,
            cp_title: document.getElementById('mrTitle')?.value || '',
            cp_name: name,
            cp_phone: phone,
            cp_email: document.getElementById('mrEmail')?.value.trim() || '',
            cp_position: document.getElementById('mrPosition')?.value.trim() || '',
        })
    }).then(r => r.json()).then(function(data) {
        if (data.success) {
            var alertEl = document.getElementById('dup-alert-tax_code');
            if (alertEl) alertEl.innerHTML = '<i class="ri-check-line me-1 text-success"></i><strong>Đã gửi yêu cầu!</strong> ' + data.message;
            alertEl.className = 'alert alert-success py-2 px-3 mt-1';
        } else if (data.phone_exists) {
            alert(data.error);
        } else {
            alert(data.error || 'Lỗi');
        }
    });
}

// MST: no separate blur check - already checked in lookup button
// Phone check: blur + Enter + paste
document.getElementById('phoneInput')?.addEventListener('blur', function() { checkDuplicate('phone', this.value.trim()); });
document.getElementById('phoneInput')?.addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); checkDuplicate('phone', this.value.trim()); } });
document.getElementById('phoneInput')?.addEventListener('paste', function() { var el = this; setTimeout(function() { checkDuplicate('phone', el.value.trim()); }, 100); });
// Email blur check
document.querySelectorAll('[name="email"]').forEach(function(el) {
    el.addEventListener('blur', function() { checkDuplicate('email', this.value.trim()); });
});
// Company name blur check
// company_name: no separate check - already checked via MST lookup

<?php if (!empty($_SESSION['force_create_contact'])): ?>
// Force create: add hidden input
var fc = document.createElement('input');
fc.type = 'hidden'; fc.name = 'force_create'; fc.value = '1';
document.getElementById('contactForm').appendChild(fc);
<?php unset($_SESSION['force_create_contact']); endif; ?>
</script>
