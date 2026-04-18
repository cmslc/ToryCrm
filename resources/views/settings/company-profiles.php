<?php $pageTitle = 'Quản lý công ty'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Quản lý công ty</h4>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#companyModal" id="btnAdd"><i class="ri-add-line me-1"></i> Thêm công ty</button>
</div>

<div class="row">
    <?php foreach ($profiles as $p): ?>
    <div class="col-lg-6">
        <div class="card <?= $p['is_default'] ? 'border-primary' : '' ?>">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <?php if (!empty($p['logo'])): ?>
                    <img src="<?= asset($p['logo']) ?>" alt="" style="height:32px;width:auto;object-fit:contain">
                    <?php else: ?>
                    <div class="bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:14px;font-weight:bold"><?= mb_substr($p['name'], 0, 1) ?></div>
                    <?php endif; ?>
                    <h6 class="card-title mb-0">
                        <?= e($p['name']) ?>
                        <?php if ($p['is_default']): ?><span class="badge bg-primary ms-1">Mặc định</span><?php endif; ?>
                    </h6>
                </div>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-soft-primary btn-icon edit-btn"
                        data-id="<?= $p['id'] ?>"
                        data-name="<?= e($p['name']) ?>"
                        data-short_name="<?= e($p['short_name'] ?? '') ?>"
                        data-tax_code="<?= e($p['tax_code'] ?? '') ?>"
                        data-address="<?= e($p['address'] ?? '') ?>"
                        data-phone="<?= e($p['phone'] ?? '') ?>"
                        data-fax="<?= e($p['fax'] ?? '') ?>"
                        data-email="<?= e($p['email'] ?? '') ?>"
                        data-website="<?= e($p['website'] ?? '') ?>"
                        data-representative="<?= e($p['representative'] ?? '') ?>"
                        data-representative_title="<?= e($p['representative_title'] ?? '') ?>"
                        data-bank_account="<?= e($p['bank_account'] ?? '') ?>"
                        data-bank_name="<?= e($p['bank_name'] ?? '') ?>"
                        data-is_default="<?= $p['is_default'] ?>"
                        data-logo="<?= e($p['logo'] ?? '') ?>"
                    ><i class="ri-pencil-line"></i></button>
                    <?php if (!$p['is_default']): ?>
                    <form method="POST" action="<?= url('settings/company-profiles/' . $p['id'] . '/delete') ?>" class="d-inline" data-confirm="Xóa công ty này?">
                        <?= csrf_field() ?>
                        <button class="btn btn-soft-danger btn-icon"><i class="ri-delete-bin-line"></i></button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <div style="font-size:13px;display:grid;grid-template-columns:1fr 1fr;gap:0">
                    <?php
                    $fields = [
                        'Tên viết tắt' => $p['short_name'] ?? '',
                        'MST' => $p['tax_code'] ?? '',
                        'ĐT' => $p['phone'] ?? '',
                        'Fax' => $p['fax'] ?? '',
                        'Đại diện' => ($p['representative'] ?? '') ? ($p['representative'] . ' - ' . ($p['representative_title'] ?? '')) : '',
                        'TK NH' => ($p['bank_account'] ?? '') ? ($p['bank_account'] . ' - ' . ($p['bank_name'] ?? '')) : '',
                    ];
                    ?>
                    <?php foreach ($fields as $label => $val): ?>
                    <div class="py-1"><span class="text-muted"><?= $label ?>:</span> <?= e($val) ?: '-' ?></div>
                    <?php endforeach; ?>
                    <div class="py-1 <?= ($p['address'] ?? '') ? '' : 'text-muted' ?>" style="grid-column:1/-1"><span class="text-muted">Địa chỉ:</span> <?= e($p['address'] ?? '') ?: '-' ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($profiles)): ?>
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5 text-muted">
                <i class="ri-building-line fs-48 d-block mb-3"></i>
                <h5>Chưa có công ty nào</h5>
                <p>Thêm thông tin các công ty/pháp nhân để sử dụng khi tạo hợp đồng.</p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal -->
<div class="modal fade" id="companyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="<?= url('settings/company-profiles/store') ?>" id="companyForm" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Thêm công ty</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div id="logoPreview" class="bg-light border rounded d-flex align-items-center justify-content-center" style="width:64px;height:64px;min-width:64px;overflow:hidden">
                            <i class="ri-building-line fs-24 text-muted" id="logoPlaceholder"></i>
                            <img id="logoImg" src="" style="max-width:100%;max-height:100%;display:none">
                        </div>
                        <div class="flex-grow-1">
                            <label class="form-label mb-1">Logo công ty</label>
                            <input type="file" name="logo" class="form-control" id="fLogo" accept="image/*" onchange="previewLogo(this)">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Tên công ty <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="fName" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tên viết tắt</label>
                            <input type="text" class="form-control" name="short_name" id="fShortName">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Mã số thuế</label>
                            <input type="text" class="form-control" name="tax_code" id="fTaxCode">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Điện thoại</label>
                            <input type="text" class="form-control" name="phone" id="fPhone">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Fax</label>
                            <input type="text" class="form-control" name="fax" id="fFax">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Địa chỉ</label>
                        <input type="text" class="form-control" name="address" id="fAddress">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="fEmail">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Website</label>
                            <input type="text" class="form-control" name="website" id="fWebsite">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Người đại diện</label>
                            <input type="text" class="form-control" name="representative" id="fRep">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Chức vụ</label>
                            <input type="text" class="form-control" name="representative_title" id="fRepTitle">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số tài khoản</label>
                            <input type="text" class="form-control" name="bank_account" id="fBankAcc">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngân hàng</label>
                            <input type="text" class="form-control" name="bank_name" id="fBankName">
                        </div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="is_default" value="1" id="fDefault">
                        <label class="form-check-label" for="fDefault">Đặt làm công ty mặc định</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-soft-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i>Lưu</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
var fields = ['name','short_name','tax_code','address','phone','fax','email','website','representative','representative_title','bank_account','bank_name'];
var fieldMap = {name:'fName',short_name:'fShortName',tax_code:'fTaxCode',address:'fAddress',phone:'fPhone',fax:'fFax',email:'fEmail',website:'fWebsite',representative:'fRep',representative_title:'fRepTitle',bank_account:'fBankAcc',bank_name:'fBankName'};

function previewLogo(input) {
    var img = document.getElementById('logoImg');
    var ph = document.getElementById('logoPlaceholder');
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) { img.src = e.target.result; img.style.display = 'block'; ph.style.display = 'none'; };
        reader.readAsDataURL(input.files[0]);
    }
}

function resetLogoPreview(logoUrl) {
    var img = document.getElementById('logoImg');
    var ph = document.getElementById('logoPlaceholder');
    document.getElementById('fLogo').value = '';
    if (logoUrl) { img.src = logoUrl; img.style.display = 'block'; ph.style.display = 'none'; }
    else { img.style.display = 'none'; ph.style.display = ''; }
}

document.getElementById('btnAdd')?.addEventListener('click', function() {
    document.getElementById('companyForm').action = '<?= url('settings/company-profiles/store') ?>';
    document.getElementById('modalTitle').textContent = 'Thêm công ty';
    fields.forEach(function(f) { document.getElementById(fieldMap[f]).value = ''; });
    document.getElementById('fDefault').checked = false;
    resetLogoPreview('');
});

document.querySelectorAll('.edit-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.dataset.id;
        document.getElementById('companyForm').action = '<?= url('settings/company-profiles') ?>/' + id + '/update';
        document.getElementById('modalTitle').textContent = 'Sửa công ty';
        fields.forEach(function(f) { document.getElementById(fieldMap[f]).value = btn.dataset[f] || ''; });
        document.getElementById('fDefault').checked = btn.dataset.is_default === '1';
        resetLogoPreview(btn.dataset.logo ? '<?= url('') ?>/' + btn.dataset.logo : '');
        new bootstrap.Modal(document.getElementById('companyModal')).show();
    });
});
</script>
