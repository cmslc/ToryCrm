<?php
$pageTitle = 'Sửa báo giá ' . $quotation['quote_number'];
$q = $quotation;
$ec = $editContact ?? null;
$ecName = '';
if ($ec) {
    $ecName = $ec['company_name'] ?: ($ec['full_name'] ?: trim(($ec['first_name'] ?? '') . ' ' . ($ec['last_name'] ?? '')));
}
$deptGrouped = [];
foreach ($users ?? [] as $u) { $deptGrouped[$u['dept_name'] ?? 'Chưa phân phòng'][] = $u; }
$fl = \App\Services\ColumnService::getLabels('quotations');
$req = array_flip(\App\Services\ColumnService::getRequiredFields('quotations'));
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-0">Sửa báo giá</h4>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('quotations/' . $q['id']) ?>" class="btn btn-soft-secondary">Quay lại</a>
        <?php if (($q['status'] ?? 'draft') === 'draft'): ?>
            <button type="submit" form="quotationForm" name="action" value="draft" class="btn btn-soft-secondary"><i class="ri-draft-line me-1"></i> Lưu nháp</button>
            <button type="submit" form="quotationForm" name="action" value="submit" class="btn btn-primary"><i class="ri-send-plane-line me-1"></i> Lưu &amp; Gửi duyệt</button>
        <?php else: ?>
            <button type="submit" form="quotationForm" class="btn btn-primary"><i class="ri-save-line me-1"></i> Cập nhật</button>
        <?php endif; ?>
    </div>
</div>

<form method="POST" action="<?= url('quotations/' . $q['id'] . '/update') ?>" id="quotationForm" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <!-- ROW 1: 2 cột thông tin -->
    <div class="card">
        <div class="card-body">
            <div class="row">
                <!-- CỘT TRÁI: Thông tin khách hàng -->
                <div class="col-lg-6">
                    <h5 class="card-title mb-3"><i class="ri-menu-line me-1"></i> Thông tin khách hàng</h5>

            <div class="mb-3">
                <label class="form-label"><?= $fl["contact_id"] ?? "Tìm khách hàng" ?><?= isset($req["contact_id"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                <div class="d-flex gap-2">
                    <div class="flex-grow-1 position-relative">
                        <input type="hidden" name="contact_id" id="contactIdInput" value="<?= $q['contact_id'] ?? '' ?>">
                        <input type="text" class="form-control" id="contactSearchInput" placeholder="Nhập tên, mã KH, SĐT, MST..." value="<?= e($ecName) ?>" autocomplete="off">
                        <div class="border rounded bg-white shadow" id="contactDropdown" style="position:absolute;z-index:1060;width:100%;display:none;top:100%;left:0;margin-top:2px;max-height:250px;overflow-y:auto"></div>
                    </div>
                    <a href="<?= url('contacts/create') ?>" class="btn btn-soft-primary" title="Tạo KH mới"><i class="ri-add-line"></i></a>
                </div>
            </div>

            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label">Mã khách hàng</label>
                    <input type="text" class="form-control" id="qAccountCode" value="<?= e($ec['account_code'] ?? '') ?>" readonly>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">Mã số thuế</label>
                    <input type="text" class="form-control" id="qTaxCode" value="<?= e($ec['tax_code'] ?? '') ?>" readonly>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label"><?= $fl["address"] ?? "Địa chỉ" ?><?= isset($req["address"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                <input type="text" class="form-control" name="address" id="qAddress" value="<?= e($q['address'] ?? $ec['address'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label"><?= $fl["contact_person_id"] ?? "Người liên hệ" ?><?= isset($req["contact_person_id"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                <select class="form-select" name="contact_person_id" id="contactPersonSelect">
                    <option value="">Chọn người liên hệ</option>
                    <?php if ($q['contact_id']):
                        $cpList = \Core\Database::fetchAll("SELECT id, title, full_name, phone, email, position, is_primary FROM contact_persons WHERE contact_id = ? ORDER BY is_primary DESC, sort_order, id", [$q['contact_id']]);
                        $savedCpId = $q['contact_person_id'] ?? null;
                        if (!$savedCpId && !empty($cpList)) $savedCpId = $cpList[0]['id'];
                        foreach ($cpList as $cp):
                    ?>
                    <option value="<?= $cp['id'] ?>"
                        data-phone="<?= e($cp['phone'] ?? '') ?>"
                        data-email="<?= e($cp['email'] ?? '') ?>"
                        <?= $savedCpId == $cp['id'] ? 'selected' : '' ?>
                    ><?= $cp['title'] ? e(ucfirst($cp['title'])) . ' ' : '' ?><?= e($cp['full_name']) ?><?= $cp['position'] ? ' - ' . e($cp['position']) : '' ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>

            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label"><?= $fl["contact_phone"] ?? "Điện thoại" ?><?= isset($req["contact_phone"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                    <input type="text" class="form-control" name="contact_phone" id="qPhone" value="<?= e($q['contact_phone'] ?? $ec['company_phone'] ?? $ec['phone'] ?? '') ?>">
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label"><?= $fl["contact_email"] ?? "Email" ?><?= isset($req["contact_email"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                    <input type="text" class="form-control" name="contact_email" id="qEmail" value="<?= e($q['contact_email'] ?? $ec['company_email'] ?? $ec['email'] ?? '') ?>">
                </div>
            </div>
        </div>

        <!-- CỘT PHẢI: Thông tin báo giá -->
        <div class="col-lg-6">
            <h5 class="card-title mb-3"><i class="ri-menu-line me-1"></i> Thông tin báo giá</h5>

            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label"><?= $fl["quote_number"] ?? "Mã báo giá" ?></label>
                    <input type="text" class="form-control" value="<?= e($q['quote_number']) ?>" readonly>
                </div>
                <div class="col-3 mb-3">
                    <label class="form-label"><?= $fl["created_at"] ?? "Ngày tạo" ?></label>
                    <input type="date" class="form-control" name="created_date" value="<?= e(substr($q['created_at'] ?? '', 0, 10)) ?>">
                </div>
                <div class="col-3 mb-3">
                    <label class="form-label"><?= $fl["valid_until"] ?? "Hiệu lực đến" ?></label>
                    <input type="date" class="form-control" name="valid_until" value="<?= e($q['valid_until'] ?? '') ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label"><?= $fl["owner_id"] ?? "Người thực hiện" ?><?= isset($req["owner_id"]) ? ' <span class="text-danger">*</span>' : '' ?></label>
                    <select name="owner_id" class="form-select searchable-select" required>
                        <option value="">Chọn</option>
                        <?php foreach ($deptGrouped as $dept => $dUsers): ?>
                        <optgroup label="<?= e($dept) ?>">
                            <?php foreach ($dUsers as $u): ?>
                            <option value="<?= $u['id'] ?>" data-avatar="<?= e($u['avatar'] ?? '') ?>" <?= ($q['owner_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label"><?= $fl["revision"] ?? "Lần báo giá" ?></label>
                    <input type="number" class="form-control" name="revision" value="<?= (int)($q['revision'] ?? 1) ?>" min="1">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label"><?= $fl["description"] ?? "Mô tả" ?></label>
                <input type="text" class="form-control" name="description" value="<?= e($q['description'] ?? '') ?>">
            </div>

            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label"><?= $fl["project"] ?? "Dự án" ?></label>
                    <input type="text" class="form-control" name="project" value="<?= e($q['project'] ?? '') ?>">
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label"><?= $fl["location"] ?? "Địa điểm" ?></label>
                    <input type="text" class="form-control" name="location" value="<?= e($q['location'] ?? '') ?>">
                </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Nội dung -->
            <div class="mb-3">
                <label class="form-label"><?= $fl["content"] ?? "Nội dung" ?></label>
                <textarea name="content" id="quoteContent" class="form-control" rows="6"><?= e($q['content'] ?? '') ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tài liệu đính kèm</label>
                    <?php if (!empty($attachments)): ?>
                    <div class="list-group list-group-flush mb-2">
                        <?php foreach ($attachments as $att):
                            $icon = 'ri-file-line';
                            $mime = $att['mime_type'] ?? '';
                            if (str_contains($mime, 'pdf')) $icon = 'ri-file-pdf-line text-danger';
                            elseif (str_contains($mime, 'word') || str_contains($mime, 'document')) $icon = 'ri-file-word-line text-primary';
                            elseif (str_contains($mime, 'sheet') || str_contains($mime, 'excel')) $icon = 'ri-file-excel-line text-success';
                            elseif (str_contains($mime, 'image')) $icon = 'ri-image-line text-info';
                            $size = $att['file_size'] < 1048576 ? round($att['file_size'] / 1024) . ' KB' : round($att['file_size'] / 1048576, 1) . ' MB';
                        ?>
                        <div class="list-group-item d-flex align-items-center px-0">
                            <i class="<?= $icon ?> fs-4 me-3"></i>
                            <div class="flex-grow-1">
                                <a href="<?= url('uploads/quotations/' . $att['filename']) ?>" target="_blank" class="fw-medium"><?= e($att['original_name']) ?></a>
                                <div class="text-muted fs-12"><?= $size ?> &middot; <?= date('d/m/Y H:i', strtotime($att['created_at'])) ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <input type="file" name="attachments[]" class="form-control" multiple>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label"><?= $fl["campaign_id"] ?? "Chiến dịch" ?></label>
                    <select name="campaign_id" class="form-select">
                        <option value="">Mới chọn</option>
                        <?php foreach ($campaigns ?? [] as $camp): ?>
                        <option value="<?= $camp['id'] ?>" <?= ($q['campaign_id'] ?? '') == $camp['id'] ? 'selected' : '' ?>><?= e($camp['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- SẢN PHẨM -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="ri-shopping-bag-line me-1"></i> Sản phẩm</h5>
        </div>
        <div class="card-body p-2">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" id="itemsTable" style="min-width:900px">
                    <thead class="table-light">
                        <tr>
                            <th style="width:35px">#</th>
                            <th style="width:12%">Mã SP</th>
                            <th style="width:22%">Tên sản phẩm</th>
                            <th style="width:6%">ĐVT</th>
                            <th style="width:8%">SL</th>
                            <th style="width:11%">Đơn giá</th>
                            <th style="width:7%">CK(%)</th>
                            <th style="width:9%">CK</th>
                            <th style="width:7%">VAT(%)</th>
                            <th style="width:11%">Thành tiền</th>
                            <th style="width:70px"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody"></tbody>
                </table>
            </div>
            <div class="mt-2 mb-2 ms-2">
                <a href="javascript:void(0)" class="text-primary" onclick="addItem()"><i class="ri-add-line me-1"></i>Thêm mới sản phẩm</a>
            </div>
        </div>
    </div>

    <!-- THANH TOÁN (bên phải) -->
    <div class="row">
        <div class="col-lg-6"></div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body" style="background:#e8f0fe">
                    <h6 class="fw-bold mb-3"><i class="ri-money-dollar-circle-line me-1"></i> Thông tin thanh toán</h6>
                    <div class="mb-2 d-flex align-items-center justify-content-between">
                        <span>Phí vận chuyển sau thuế</span>
                        <input class="form-check-input" type="checkbox" name="shipping_after_tax" value="1" <?= ($q['shipping_after_tax'] ?? 0) ? 'checked' : '' ?>>
                    </div>
                    <div class="mb-2 d-flex align-items-center gap-2">
                        <span class="flex-shrink-0" style="width:140px">Phí vận chuyển</span>
                        <div class="input-group" style="width:120px"><input type="number" class="form-control" name="shipping_percent" value="<?= (float)($q['shipping_percent'] ?? 0) ?>" min="0" step="0.01" onchange="calcPaymentRow(this,'shipping')"><span class="input-group-text">%</span></div>
                        <input type="number" class="form-control" name="shipping_fee" value="<?= (float)($q['shipping_fee'] ?? 0) ?>" min="0" onchange="calculateTotal()" style="width:120px">
                    </div>
                    <div class="mb-2 d-flex align-items-center gap-2">
                        <span class="flex-shrink-0" style="width:140px">Chiết khấu</span>
                        <div class="input-group" style="width:120px"><input type="number" class="form-control" name="discount_percent" value="<?= (float)($q['discount_percent'] ?? 0) ?>" min="0" step="0.01" onchange="calcPaymentRow(this,'discount')"><span class="input-group-text">%</span></div>
                        <input type="number" class="form-control" name="discount_amount" value="<?= (float)($q['discount_amount'] ?? 0) ?>" min="0" onchange="calculateTotal()" style="width:120px">
                    </div>
                    <div class="mb-2 d-flex align-items-center justify-content-between">
                        <span>Chiết khấu sau thuế</span>
                        <input class="form-check-input" type="checkbox" name="discount_after_tax" value="1" <?= ($q['discount_after_tax'] ?? 0) ? 'checked' : '' ?>>
                    </div>
                    <div class="mb-2 d-flex align-items-center gap-2">
                        <span class="flex-shrink-0" style="width:140px">Thuế VAT</span>
                        <div class="input-group" style="width:120px"><input type="number" class="form-control" name="tax_rate" value="<?= (float)($q['tax_rate'] ?? 0) ?>" min="0" step="0.01" onchange="calcPaymentRow(this,'tax')"><span class="input-group-text">%</span></div>
                        <input type="number" class="form-control" name="tax_amount" value="<?= (float)($q['tax_amount'] ?? 0) ?>" readonly style="width:120px;background:#dce6f5">
                    </div>
                    <div class="mb-2 d-flex align-items-center gap-2">
                        <span class="flex-shrink-0" style="width:140px">Phí lắp đặt</span>
                        <div class="input-group" style="width:120px"><input type="number" class="form-control" name="installation_percent" value="<?= (float)($q['installation_percent'] ?? 0) ?>" min="0" step="0.01" onchange="calcPaymentRow(this,'installation')"><span class="input-group-text">%</span></div>
                        <input type="number" class="form-control" name="installation_fee" value="<?= (float)($q['installation_fee'] ?? 0) ?>" min="0" onchange="calculateTotal()" style="width:120px">
                    </div>
                    <hr class="my-2">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="fw-bold fs-5">Tổng cộng</span>
                        <span class="fw-bold fs-5 text-primary" id="grandTotalDisplay">0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ghi chú & Điều khoản -->
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label"><?= $fl["notes"] ?? "Ghi chú" ?></label>
                    <textarea name="notes" class="form-control" rows="3"><?= e($q['notes'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label"><?= $fl["terms"] ?? "Điều khoản" ?></label>
                    <textarea name="terms" class="form-control" rows="3"><?= e($q['terms'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
.product-search-wrap { position: relative; }
.product-search-wrap input { width: 100%; }
.product-dropdown { position: fixed; background: #fff; border: 1px solid #ddd; border-radius: 6px; max-height: 220px; overflow-y: auto; z-index: 1060; display: none; box-shadow: 0 4px 12px rgba(0,0,0,.15); min-width: 250px; }
#itemsTable td { overflow: visible; }
.table-responsive { overflow: visible; }
.product-dropdown .pd-item { padding: 8px 12px; cursor: pointer; font-size: 13px; border-bottom: 1px solid #f3f3f3; }
.product-dropdown .pd-item:hover { background: #f0f4ff; }
.product-dropdown .pd-item .pd-sku { color: #888; font-size: 12px; }
.btn-note-toggle { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; padding: 2px 4px; color: #5e7394; cursor: pointer; z-index: 2; }
.btn-note-toggle:hover { color: #405189; }
.btn-note-toggle .ri-sticky-note-fill { color: #0ab39c; }
</style>

<script src="<?= asset('libs/ckeditor/ckeditor.js') ?>"></script>
<script>
const existingItems = <?= json_encode($items ?? []) ?>;

// AJAX search khách hàng
var csTimer = null;
var csInput = document.getElementById('contactSearchInput');
var csDrop = document.getElementById('contactDropdown');

csInput?.addEventListener('input', function() {
    var q = this.value.trim();
    if (q.length < 1) { csDrop.style.display = 'none'; return; }
    clearTimeout(csTimer);
    csTimer = setTimeout(function() {
        fetch('<?= url("contacts/search-ajax") ?>?q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(function(results) {
                if (!results.length) {
                    csDrop.innerHTML = '<div class="px-3 py-2 text-muted">Không tìm thấy</div>';
                    csDrop.style.display = 'block';
                    return;
                }
                csDrop.innerHTML = results.map(function(c) {
                    var name = c.company_name || c.full_name || ((c.first_name || '') + ' ' + (c.last_name || '')).trim();
                    var sub = [c.account_code, c.phone || c.company_phone].filter(Boolean).join(' - ');
                    return '<div class="px-3 py-2 border-bottom" style="cursor:pointer" onmousedown="pickContact(' + JSON.stringify(c).replace(/"/g, '&quot;') + ')">'
                        + '<div class="fw-medium">' + name + '</div>'
                        + (sub ? '<small class="text-muted">' + sub + '</small>' : '')
                        + '</div>';
                }).join('');
                csDrop.style.display = 'block';
            });
    }, 300);
});

csInput?.addEventListener('blur', function() { setTimeout(function() { csDrop.style.display = 'none'; }, 200); });

function pickContact(c) {
    var name = c.company_name || c.full_name || ((c.first_name || '') + ' ' + (c.last_name || '')).trim();
    document.getElementById('contactIdInput').value = c.id;
    document.getElementById('contactSearchInput').value = name;
    var acc = document.getElementById('qAccountCode'); if (acc) acc.value = c.account_code || '';
    var tax = document.getElementById('qTaxCode');     if (tax) tax.value = c.tax_code || '';
    document.getElementById('qAddress').value = c.address || '';
    document.getElementById('qPhone').value = c.company_phone || c.phone || '';
    document.getElementById('qEmail').value = c.company_email || c.email || '';
    csDrop.style.display = 'none';
    loadPersons(c.id);
}

function loadPersons(contactId, selectId) {
    fetch('<?= url("contacts") ?>/' + contactId + '/persons', { credentials: 'same-origin' })
        .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
        .then(function(persons) {
            var cpSel = document.getElementById('contactPersonSelect');
            cpSel.innerHTML = '<option value="">Chọn người liên hệ</option>';
            (persons || []).forEach(function(p) {
                var o = document.createElement('option');
                o.value = p.id;
                o.textContent = (p.title ? p.title + ' ' : '') + p.full_name + (p.position ? ' - ' + p.position : '');
                o.dataset.phone = p.phone || '';
                o.dataset.email = p.email || '';
                if (selectId && String(p.id) === String(selectId)) o.selected = true;
                cpSel.appendChild(o);
            });
        }).catch(function(err){ console.error('loadPersons error:', err); });
}

document.getElementById('contactPersonSelect')?.addEventListener('change', function() {
    var opt = this.options[this.selectedIndex];
    if (opt && opt.value) {
        if (opt.dataset.phone) document.getElementById('qPhone').value = opt.dataset.phone;
        if (opt.dataset.email) document.getElementById('qEmail').value = opt.dataset.email;
    }
});

// Persons already rendered by PHP, only use loadPersons when changing contact

// === Sản phẩm ===
let itemIndex = 0;
let searchTimer = null;

function addItem(data) {
    const tbody = document.getElementById('itemsBody');
    const idx = itemIndex++;
    const tr = document.createElement('tr');
    tr.id = 'item-row-' + idx;
    tr.innerHTML = `
        <td class="text-center text-muted">${idx + 1}</td>
        <td>
            <div class="product-search-wrap">
                <input type="text" class="form-control" id="item-sku-${idx}" placeholder="Mã SP" value="${data?.sku || data?.product_sku || ''}" autocomplete="off" onfocus="searchProduct(this,${idx},'sku')" oninput="searchProduct(this,${idx},'sku')">
                <div class="product-dropdown" id="item-skudrop-${idx}"></div>
            </div>
        </td>
        <td>
            <div class="product-search-wrap position-relative">
                <input type="text" class="form-control pe-5" id="item-namesearch-${idx}" placeholder="Tên SP" value="${data?.product_name || ''}" autocomplete="off" onfocus="searchProduct(this,${idx},'name')" oninput="searchProduct(this,${idx},'name')">
                <button type="button" class="btn-note-toggle" onclick="toggleNote(${idx})" title="Ghi chú">
                    <i class="${data?.description ? 'ri-sticky-note-fill' : 'ri-sticky-note-line'}" id="note-icon-${idx}"></i>
                </button>
                <div class="product-dropdown" id="item-namedrop-${idx}"></div>
            </div>
            <textarea class="form-control mt-2 ${data?.description ? '' : 'd-none'}" id="item-note-${idx}" name="items[${idx}][description]" rows="2" placeholder="Ghi chú..." oninput="updateNoteIcon(${idx})">${data?.description || ''}</textarea>
            <input type="hidden" name="items[${idx}][product_id]" id="item-product-${idx}" value="${data?.product_id || ''}">
            <input type="hidden" name="items[${idx}][product_name]" id="item-name-${idx}" value="${data?.product_name || ''}">
        </td>
        <td><input type="text" class="form-control" name="items[${idx}][unit]" id="item-unit-${idx}" value="${data?.unit || 'Cái'}"></td>
        <td><input type="number" class="form-control" name="items[${idx}][quantity]" value="${data?.quantity || 0}" min="0" step="0.01" onchange="calculateRow(${idx})"></td>
        <td><input type="number" class="form-control" name="items[${idx}][unit_price]" id="item-price-${idx}" value="${data?.unit_price || 0}" min="0" onchange="calculateRow(${idx})"></td>
        <td><input type="number" class="form-control" name="items[${idx}][discount_percent]" id="item-ckpct-${idx}" value="${data?.discount_percent || 0}" min="0" max="100" step="0.01" onchange="calcDiscountFromPct(${idx})"></td>
        <td><input type="number" class="form-control" name="items[${idx}][discount]" id="item-discount-${idx}" value="${data?.discount || 0}" min="0" onchange="calculateRow(${idx})"></td>
        <td><input type="number" class="form-control" name="items[${idx}][tax_rate]" id="item-tax-${idx}" value="${data?.tax_rate || 0}" min="0" max="100" step="0.01" onchange="calculateRow(${idx})"></td>
        <td class="fw-medium text-end" id="item-total-${idx}">0.00</td>
        <td><div class="d-flex gap-1"><button type="button" class="btn btn-soft-danger btn-icon" onclick="removeItem(${idx})"><i class="ri-delete-bin-line"></i></button><button type="button" class="btn btn-soft-primary btn-icon" onclick="addItem()"><i class="ri-add-line"></i></button></div></td>
    `;
    tbody.appendChild(tr);
    if (data) calculateRow(idx);
}

function positionDropdown(input, drop) {
    var rect = input.getBoundingClientRect();
    drop.style.top = (rect.bottom + 2) + 'px';
    drop.style.left = rect.left + 'px';
    drop.style.width = Math.max(rect.width, 250) + 'px';
}

function searchProduct(input, idx, type) {
    const q = input.value.trim();
    const dropId = type === 'sku' ? 'item-skudrop-' + idx : 'item-namedrop-' + idx;
    const drop = document.getElementById(dropId);
    if (q.length < 1) { drop.style.display = 'none'; return; }
    clearTimeout(searchTimer);
    searchTimer = setTimeout(function() {
        fetch('<?= url("products/search-ajax") ?>?q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(results => {
                if (!results.length) { drop.innerHTML = '<div class="pd-item text-muted">Không tìm thấy</div>'; positionDropdown(input, drop); drop.style.display = 'block'; return; }
                drop.innerHTML = results.map(p =>
                    `<div class="pd-item" onclick="pickProduct(${idx}, ${JSON.stringify(p).replace(/"/g, '&quot;')})">
                        <strong>${p.name}</strong> <span class="pd-sku">${p.sku || ''}</span>
                        <br><small class="text-muted">${Number(p.price).toLocaleString('vi-VN')} ₫ / ${p.unit || 'Cái'}</small>
                    </div>`
                ).join('');
                positionDropdown(input, drop);
                drop.style.display = 'block';
            });
    }, 250);
}

function pickProduct(idx, p) {
    document.getElementById('item-product-' + idx).value = p.id;
    document.getElementById('item-name-' + idx).value = p.name;
    document.getElementById('item-sku-' + idx).value = p.sku || '';
    document.getElementById('item-namesearch-' + idx).value = p.name;
    document.getElementById('item-price-' + idx).value = p.price || 0;
    document.getElementById('item-unit-' + idx).value = p.unit || 'Cái';
    document.getElementById('item-tax-' + idx).value = p.tax_rate || 0;
    document.querySelectorAll('.product-dropdown').forEach(d => d.style.display = 'none');
    calculateRow(idx);
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.product-search-wrap')) {
        document.querySelectorAll('.product-dropdown').forEach(d => d.style.display = 'none');
    }
});

function calcDiscountFromPct(idx) {
    const qty = parseFloat(document.querySelector(`[name="items[${idx}][quantity]"]`)?.value || 0);
    const price = parseFloat(document.getElementById('item-price-' + idx)?.value || 0);
    const pct = parseFloat(document.getElementById('item-ckpct-' + idx)?.value || 0);
    document.getElementById('item-discount-' + idx).value = Math.round(qty * price * pct / 100);
    calculateRow(idx);
}

function removeItem(idx) {
    document.getElementById('item-row-' + idx)?.remove();
    calculateTotal();
}

function toggleNote(idx) {
    const ta = document.getElementById('item-note-' + idx);
    if (!ta) return;
    ta.classList.toggle('d-none');
    if (!ta.classList.contains('d-none')) ta.focus();
}

function updateNoteIcon(idx) {
    const ta = document.getElementById('item-note-' + idx);
    const icon = document.getElementById('note-icon-' + idx);
    if (!ta || !icon) return;
    const has = ta.value.trim().length > 0;
    icon.classList.toggle('ri-sticky-note-line', !has);
    icon.classList.toggle('ri-sticky-note-fill', has);
}

function calculateRow(idx) {
    const qty = parseFloat(document.querySelector(`[name="items[${idx}][quantity]"]`)?.value || 0);
    const price = parseFloat(document.getElementById('item-price-' + idx)?.value || 0);
    const tax = parseFloat(document.getElementById('item-tax-' + idx)?.value || 0);
    const discount = parseFloat(document.getElementById('item-discount-' + idx)?.value || 0);
    const total = qty * price * (1 + tax / 100) - discount;
    const el = document.getElementById('item-total-' + idx);
    if (el) el.textContent = formatMoney(Math.max(0, total));
    calculateTotal();
}

function calculateTotal() {
    let subtotal = 0;
    document.querySelectorAll('#itemsBody tr').forEach(tr => {
        const qty = parseFloat(tr.querySelector('[name*="[quantity]"]')?.value || 0);
        const price = parseFloat(tr.querySelector('[name*="[unit_price]"]')?.value || 0);
        subtotal += qty * price;
    });

    const taxRate = parseFloat(document.querySelector('[name="tax_rate"]')?.value || 0);
    const taxAmount = subtotal * taxRate / 100;
    document.querySelector('[name="tax_amount"]').value = Math.round(taxAmount);

    const discountAmount = parseFloat(document.querySelector('[name="discount_amount"]')?.value || 0);
    const shippingFee = parseFloat(document.querySelector('[name="shipping_fee"]')?.value || 0);
    const installFee = parseFloat(document.querySelector('[name="installation_fee"]')?.value || 0);
    const total = Math.max(0, subtotal + taxAmount - discountAmount + shippingFee + installFee);

    document.getElementById('grandTotalDisplay').textContent = formatMoney(total);
}

function calcPaymentRow(el, type) {
    let subtotal = 0;
    document.querySelectorAll('#itemsBody tr').forEach(tr => {
        const qty = parseFloat(tr.querySelector('[name*="[quantity]"]')?.value || 0);
        const price = parseFloat(tr.querySelector('[name*="[unit_price]"]')?.value || 0);
        subtotal += qty * price;
    });
    const pct = parseFloat(el.value || 0);
    const amount = Math.round(subtotal * pct / 100);
    if (type === 'shipping') document.querySelector('[name="shipping_fee"]').value = amount;
    if (type === 'discount') document.querySelector('[name="discount_amount"]').value = amount;
    if (type === 'tax') document.querySelector('[name="tax_amount"]').value = amount;
    if (type === 'installation') document.querySelector('[name="installation_fee"]').value = amount;
    calculateTotal();
}

function formatMoney(amount) {
    return new Intl.NumberFormat('vi-VN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Math.round(amount * 100) / 100);
}

// Load existing items or add empty row
if (existingItems.length > 0) {
    existingItems.forEach(item => addItem(item));
} else {
    addItem();
}

// CKEditor for content
if (typeof CKEDITOR !== 'undefined') {
    CKEDITOR.replace('quoteContent', { language: 'vi', height: 250, allowedContent: true });
}

</script>
