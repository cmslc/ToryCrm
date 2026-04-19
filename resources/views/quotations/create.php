<?php
$pageTitle = 'Tạo báo giá';
$preContactId = $_GET['contact_id'] ?? '';
$deptGrouped = [];
foreach ($users ?? [] as $u) { $deptGrouped[$u['dept_name'] ?? 'Chưa phân phòng'][] = $u; }
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-0"><i class="ri-file-list-3-line me-2"></i>Quản lý báo giá \ Tạo báo giá</h4>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('quotations') ?>" class="btn btn-soft-secondary">Quay lại</a>
        <button type="submit" form="quotationForm" class="btn btn-primary"><i class="ri-save-line me-1"></i> Cập nhật</button>
    </div>
</div>

<form method="POST" action="<?= url('quotations/store') ?>" id="quotationForm" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="action" id="formAction" value="draft">

    <!-- ROW 1: 2 cột thông tin -->
    <div class="card">
        <div class="card-body">
            <div class="row">
                <!-- CỘT TRÁI: Thông tin khách hàng -->
                <div class="col-lg-6">
                    <h6 class="fw-bold mb-3"><i class="ri-menu-line me-1"></i> Thông tin khách hàng</h6>

            <div class="mb-3">
                <label class="form-label">Tìm khách hàng</label>
                <div class="d-flex gap-2">
                    <select name="contact_id" class="form-select searchable-select" style="width:100%" id="contactSelect" onchange="onContactChange(this)">
                        <option value="">Vui lòng nhập và ấn enter</option>
                        <?php foreach ($contacts ?? [] as $c):
                            $cName = $c['company_name'] ?: trim(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? ''));
                        ?>
                            <option value="<?= $c['id'] ?>"
                                data-address="<?= e($c['address'] ?? '') ?>"
                                data-phone="<?= e($c['company_phone'] ?? $c['phone'] ?? '') ?>"
                                data-email="<?= e($c['company_email'] ?? $c['email'] ?? '') ?>"
                                <?= $preContactId == $c['id'] ? 'selected' : '' ?>
                            ><?= e($cName) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <a href="<?= url('contacts/create') ?>" class="btn btn-soft-primary" title="Tạo KH mới"><i class="ri-add-line"></i></a>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Địa chỉ</label>
                <input type="text" class="form-control" name="address" id="qAddress">
            </div>

            <div class="mb-3">
                <label class="form-label">Người liên hệ</label>
                <select class="form-select" name="contact_person_id" id="contactPersonSelect">
                    <option value="">Chọn người liên hệ</option>
                </select>
            </div>

            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label">Điện thoại</label>
                    <input type="text" class="form-control" name="contact_phone" id="qPhone">
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="text" class="form-control" name="contact_email" id="qEmail">
                </div>
            </div>
        </div>

        <!-- CỘT PHẢI: Thông tin báo giá -->
        <div class="col-lg-6">
            <h6 class="fw-bold mb-3"><i class="ri-menu-line me-1"></i> Thông tin báo giá</h6>

            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label">Mã báo giá</label>
                    <input type="text" class="form-control" name="quote_number" value="<?= e($quoteNumber) ?>">
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">Ngày tạo</label>
                    <input type="date" class="form-control" name="created_date" value="<?= date('Y-m-d') ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label">Người thực hiện <span class="text-danger">*</span></label>
                    <select name="owner_id" class="form-select searchable-select" required>
                        <option value="">Chọn</option>
                        <?php foreach ($deptGrouped as $dept => $dUsers): ?>
                        <optgroup label="<?= e($dept) ?>">
                            <?php foreach ($dUsers as $u): ?>
                            <option value="<?= $u['id'] ?>" data-avatar="<?= e($u['avatar'] ?? '') ?>" <?= $u['id'] == ($_SESSION['user']['id'] ?? 0) ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">Lần báo giá</label>
                    <input type="number" class="form-control" name="revision" value="1" min="1">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Mô tả</label>
                <input type="text" class="form-control" name="description">
            </div>

            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label">Dự án</label>
                    <input type="text" class="form-control" name="project">
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">Địa điểm</label>
                    <input type="text" class="form-control" name="location">
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
                <label class="form-label">Nội dung</label>
                <textarea name="content" id="quoteContent" class="form-control" rows="6"></textarea>
            </div>

            <!-- Tài liệu đính kèm -->
            <div class="mb-3">
                <label class="form-label">Tài liệu đính kèm</label>
                <input type="file" name="attachments[]" class="form-control" multiple>
            </div>

            <!-- Chiến dịch -->
            <div class="mb-3" style="max-width:400px">
                <label class="form-label">Chiến dịch</label>
                <select name="campaign_id" class="form-select">
                    <option value="">Mới chọn</option>
                    <?php foreach ($campaigns ?? [] as $camp): ?>
                    <option value="<?= $camp['id'] ?>"><?= e($camp['name']) ?></option>
                    <?php endforeach; ?>
                </select>
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
        <table class="table table-bordered align-middle mb-0" id="itemsTable">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Mã sản phẩm</th>
                    <th>Tên sản phẩm</th>
                    <th>Đơn vị</th>
                    <th>Số lượng</th>
                    <th>Đơn giá</th>
                    <th>CK (%)</th>
                    <th>CK</th>
                    <th>VAT (%)</th>
                    <th>Thành tiền</th>
                    <th></th>
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
                <table class="table table-borderless mb-0">
                    <tr>
                        <td>Phí vận chuyển sau thuế</td>
                        <td colspan="2"><input class="form-check-input" type="checkbox" name="shipping_after_tax" value="1" id="shippingAfterTax"></td>
                    </tr>
                    <tr>
                        <td>Phí vận chuyển</td>
                        <td><div class="input-group"><input type="number" class="form-control" name="shipping_percent" value="0" min="0" step="0.01" onchange="calcPaymentRow(this,'shipping')"><span class="input-group-text">%</span></div></td>
                        <td><input type="number" class="form-control" name="shipping_fee" value="0" min="0" onchange="calculateTotal()"></td>
                    </tr>
                    <tr>
                        <td>Chiết khấu</td>
                        <td><div class="input-group"><input type="number" class="form-control" name="discount_percent" value="0" min="0" step="0.01" onchange="calcPaymentRow(this,'discount')"><span class="input-group-text">%</span></div></td>
                        <td><input type="number" class="form-control" name="discount_amount" value="0" min="0" onchange="calculateTotal()"></td>
                    </tr>
                    <tr>
                        <td>Chiết khấu sau thuế</td>
                        <td colspan="2"><input class="form-check-input" type="checkbox" name="discount_after_tax" value="1" id="discountAfterTax"></td>
                    </tr>
                    <tr>
                        <td>Thuế VAT</td>
                        <td><div class="input-group"><input type="number" class="form-control" name="tax_rate" value="0" min="0" step="0.01" onchange="calcPaymentRow(this,'tax')"><span class="input-group-text">%</span></div></td>
                        <td><input type="number" class="form-control" name="tax_amount" value="0" readonly style="background:#dce6f5"></td>
                    </tr>
                    <tr>
                        <td>Phí lắp đặt</td>
                        <td><div class="input-group"><input type="number" class="form-control" name="installation_percent" value="0" min="0" step="0.01" onchange="calcPaymentRow(this,'installation')"><span class="input-group-text">%</span></div></td>
                        <td><input type="number" class="form-control" name="installation_fee" value="0" min="0" onchange="calculateTotal()"></td>
                    </tr>
                    <tr class="fw-bold">
                        <td>Tổng cộng</td>
                        <td colspan="2"><div class="form-control-plaintext text-end fs-5 text-primary" id="grandTotalDisplay">0.00</div></td>
                    </tr>
                </table>
            </div>
            </div>
        </div>
    </div>

    <!-- Ghi chú & Điều khoản -->
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Ghi chú nội bộ hoặc cho khách hàng..."></textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Điều khoản</label>
                    <textarea name="terms" class="form-control" rows="3" placeholder="Điều khoản thanh toán, bảo hành..."></textarea>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
.product-search-wrap { position: relative; }
.product-search-wrap input { width: 100%; }
.product-dropdown { position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid #ddd; border-radius: 6px; max-height: 220px; overflow-y: auto; z-index: 1050; display: none; box-shadow: 0 4px 12px rgba(0,0,0,.1); }
.product-dropdown .pd-item { padding: 8px 12px; cursor: pointer; font-size: 13px; border-bottom: 1px solid #f3f3f3; }
.product-dropdown .pd-item:hover { background: #f0f4ff; }
.product-dropdown .pd-item .pd-sku { color: #888; font-size: 12px; }
</style>

<script>
// Auto-fill khi chọn khách hàng
function onContactChange(sel) {
    var opt = sel.options[sel.selectedIndex];
    if (opt && opt.value) {
        document.getElementById('qAddress').value = opt.dataset.address || '';
        document.getElementById('qPhone').value = opt.dataset.phone || '';
        document.getElementById('qEmail').value = opt.dataset.email || '';
        // Load contact persons
        fetch('<?= url("contacts") ?>/' + opt.value + '/persons')
            .then(r => r.json())
            .then(function(persons) {
                var cpSel = document.getElementById('contactPersonSelect');
                cpSel.innerHTML = '<option value="">Chọn người liên hệ</option>';
                (persons || []).forEach(function(p) {
                    var o = document.createElement('option');
                    o.value = p.id;
                    o.textContent = (p.title ? p.title + ' ' : '') + p.full_name + (p.position ? ' - ' + p.position : '');
                    o.dataset.phone = p.phone || '';
                    o.dataset.email = p.email || '';
                    cpSel.appendChild(o);
                });
            }).catch(function(){});
    }
}

// Auto-fill khi chọn người liên hệ
document.getElementById('contactPersonSelect')?.addEventListener('change', function() {
    var opt = this.options[this.selectedIndex];
    if (opt && opt.value) {
        if (opt.dataset.phone) document.getElementById('qPhone').value = opt.dataset.phone;
        if (opt.dataset.email) document.getElementById('qEmail').value = opt.dataset.email;
    }
});

// Pre-fill nếu có contact_id
<?php if ($preContactId): ?>
setTimeout(function() {
    var sel = document.querySelector('[name="contact_id"]');
    if (sel && sel.value) onContactChange(sel);
}, 300);
<?php endif; ?>

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
                <input type="text" class="form-control" id="item-sku-${idx}" placeholder="Mã SP" value="${data?.sku || ''}" autocomplete="off" onfocus="searchProduct(this,${idx},'sku')" oninput="searchProduct(this,${idx},'sku')">
                <div class="product-dropdown" id="item-skudrop-${idx}"></div>
            </div>
        </td>
        <td>
            <div class="product-search-wrap">
                <input type="text" class="form-control" id="item-namesearch-${idx}" placeholder="Tên SP" value="${data?.product_name || ''}" autocomplete="off" onfocus="searchProduct(this,${idx},'name')" oninput="searchProduct(this,${idx},'name')">
                <div class="product-dropdown" id="item-namedrop-${idx}"></div>
            </div>
            <input type="hidden" name="items[${idx}][product_id]" id="item-product-${idx}" value="${data?.product_id || ''}">
            <input type="hidden" name="items[${idx}][product_name]" id="item-name-${idx}" value="${data?.product_name || ''}">
        </td>
        <td><input type="text" class="form-control" name="items[${idx}][unit]" id="item-unit-${idx}" value="${data?.unit || 'Cái'}"></td>
        <td><input type="number" class="form-control" name="items[${idx}][quantity]" value="${data?.quantity || 0}" min="0" step="0.01" onchange="calculateRow(${idx})"></td>
        <td><input type="number" class="form-control" name="items[${idx}][unit_price]" id="item-price-${idx}" value="${data?.unit_price || 0}" min="0" onchange="calculateRow(${idx})"></td>
        <td><input type="number" class="form-control" name="items[${idx}][discount_percent]" id="item-ckpct-${idx}" value="0" min="0" max="100" step="0.01" onchange="calcDiscountFromPct(${idx})"></td>
        <td><input type="number" class="form-control" name="items[${idx}][discount]" id="item-discount-${idx}" value="${data?.discount || 0}" min="0" onchange="calculateRow(${idx})"></td>
        <td><input type="number" class="form-control" name="items[${idx}][tax_rate]" id="item-tax-${idx}" value="${data?.tax_rate || 0}" min="0" max="100" step="0.01" onchange="calculateRow(${idx})"></td>
        <td class="fw-medium text-end" id="item-total-${idx}">0.00</td>
        <td class="text-center">
            <button type="button" class="btn btn-soft-danger btn-icon" onclick="removeItem(${idx})"><i class="ri-delete-bin-line"></i></button>
            <button type="button" class="btn btn-soft-primary btn-icon" onclick="addItem()"><i class="ri-add-line"></i></button>
        </td>
    `;
    tbody.appendChild(tr);
    if (data) calculateRow(idx);
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
                if (!results.length) { drop.innerHTML = '<div class="pd-item text-muted">Không tìm thấy</div>'; drop.style.display = 'block'; return; }
                drop.innerHTML = results.map(p =>
                    `<div class="pd-item" onclick="pickProduct(${idx}, ${JSON.stringify(p).replace(/"/g, '&quot;')})">
                        <strong>${p.name}</strong> <span class="pd-sku">${p.sku || ''}</span>
                        <br><small class="text-muted">${Number(p.price).toLocaleString('vi-VN')} ₫ / ${p.unit || 'Cái'}</small>
                    </div>`
                ).join('');
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

// Init first item row
addItem();

// CKEditor for content
if (typeof CKEDITOR !== 'undefined') {
    CKEDITOR.replace('quoteContent', { height: 200 });
}
</script>
