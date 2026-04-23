<?php
$pageTitle = 'Tạo đơn hàng';
$preContactId = $preContactId ?? 0;
$pc = $preContact ?? null;
$pcName = '';
if ($pc) {
    $pcName = $pc['company_name'] ?: ($pc['full_name'] ?: trim(($pc['first_name'] ?? '') . ' ' . ($pc['last_name'] ?? '')));
    if (!empty($pc['account_code'])) $pcName .= ' (' . $pc['account_code'] . ')';
}
$deptGrouped = [];
foreach ($users ?? [] as $u) { $deptGrouped[$u['dept_name'] ?? 'Chưa phân phòng'][] = $u; }
$fl = \App\Services\ColumnService::getLabels('orders');
$req = array_flip(\App\Services\ColumnService::getRequiredFields('orders'));
$dv = \App\Services\ColumnService::getDefaultValues('orders');
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Tạo đơn hàng</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('orders') ?>" class="btn btn-soft-secondary">Quay lại</a>
        <button type="submit" form="orderForm" class="btn btn-primary"><i class="ri-save-line me-1"></i> Cập nhật</button>
    </div>
</div>

<form method="POST" action="<?= url('orders/store') ?>" id="orderForm" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="type" value="<?= $type ?? 'order' ?>">

    <!-- 2 cột thông tin -->
    <div class="card">
        <div class="card-body">
            <div class="row">
                <!-- CỘT TRÁI: KH -->
                <div class="col-lg-6">
                    <h5 class="card-title mb-3"><i class="ri-menu-line me-1"></i> Thông tin khách hàng</h5>
                    <div class="mb-3">
                        <label class="form-label"><?= $fl["contact_id"] ?? "Tìm khách hàng" ?></label>
                        <div class="d-flex gap-2">
                            <div class="flex-grow-1 position-relative">
                                <input type="hidden" name="contact_id" id="contactIdInput" value="<?= $preContactId ?>">
                                <input type="text" class="form-control" id="contactSearchInput" placeholder="Nhập tên, mã KH, SĐT, MST..." value="<?= e($pcName) ?>" autocomplete="off">
                                <div class="border rounded bg-white shadow" id="contactDropdown" style="position:absolute;z-index:1060;width:100%;display:none;top:100%;left:0;margin-top:2px;max-height:250px;overflow-y:auto"></div>
                            </div>
                            <a href="<?= url('contacts/create') ?>" class="btn btn-soft-primary" title="Tạo KH mới"><i class="ri-add-line"></i></a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Tên khách hàng</label>
                            <div id="qCustomerName" class="form-control-plaintext fw-medium"><?= e($pc['company_name'] ?? ($pc['full_name'] ?? '-')) ?></div>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Điện thoại</label>
                            <div id="qPhoneDisplay" class="form-control-plaintext"><?= e($pc['company_phone'] ?? $pc['phone'] ?? '-') ?></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Địa chỉ</label>
                            <div id="qAddressDisplay" class="form-control-plaintext"><?= e($pc['address'] ?? '-') ?></div>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Email</label>
                            <div id="qEmailDisplay" class="form-control-plaintext"><?= e($pc['company_email'] ?? $pc['email'] ?? '-') ?></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Người liên hệ</label>
                        <select class="form-select" name="contact_person_id" id="contactPersonSelect">
                            <option value="">Chọn người liên hệ</option>
                            <?php if ($preContactId):
                                $cpList = \Core\Database::fetchAll("SELECT id, title, full_name, phone, email, position, is_primary FROM contact_persons WHERE contact_id = ? ORDER BY is_primary DESC, sort_order, id", [$preContactId]);
                                $firstCpId = !empty($cpList) ? $cpList[0]['id'] : null;
                                foreach ($cpList as $cp):
                            ?>
                            <option value="<?= $cp['id'] ?>" data-phone="<?= e($cp['phone'] ?? '') ?>" data-email="<?= e($cp['email'] ?? '') ?>" <?= $firstCpId == $cp['id'] ? 'selected' : '' ?>><?= $cp['title'] ? e(ucfirst($cp['title'])) . ' ' : '' ?><?= e($cp['full_name']) ?><?= $cp['position'] ? ' - ' . e($cp['position']) : '' ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                </div>

                <!-- CỘT PHẢI: ĐH -->
                <div class="col-lg-6">
                    <h5 class="card-title mb-3"><i class="ri-menu-line me-1"></i> Thông tin đơn hàng</h5>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label"><?= $fl["order_number"] ?? "Mã ĐH" ?></label>
                            <input type="text" class="form-control" name="order_number" value="<?= e($orderNumber) ?>">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Ngày đặt hàng</label>
                            <input type="date" class="form-control" name="issued_date" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label"><?= $fl["owner_id"] ?? "Người thực hiện" ?> <span class="text-danger">*</span></label>
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
                            <label class="form-label">Nguồn đơn hàng</label>
                            <select name="order_source_id" class="form-select">
                                <option value="">Mới chọn</option>
                                <?php foreach ($sources ?? [] as $src): ?>
                                <option value="<?= $src['id'] ?>"><?= e($src['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mã vận đơn</label>
                        <input type="text" class="form-control" name="lading_code">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Chiến dịch</label>
                            <select name="campaign_id" class="form-select">
                                <option value="">Mới chọn</option>
                                <?php foreach ($campaigns ?? [] as $camp): ?>
                                <option value="<?= $camp['id'] ?>"><?= e($camp['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Hoa hồng trả khách</label>
                            <input type="number" class="form-control" name="commission_amount" value="0" min="0">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Thông tin giao hàng -->
    <div class="card">
        <div class="card-header"><h5 class="card-title mb-0"><i class="ri-truck-line me-1"></i> Thông tin giao hàng</h5></div>
        <div class="card-body">
            <div class="mb-3">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="delivery_type" id="deliveryTypeSelf" value="self" checked>
                    <label class="form-check-label" for="deliveryTypeSelf">Tự giao</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="delivery_type" id="deliveryTypePartner" value="partner">
                    <label class="form-check-label" for="deliveryTypePartner">Chọn đối tác giao</label>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Thời gian giao hàng</label>
                    <input type="date" class="form-control" name="delivery_date">
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label">Địa chỉ giao hàng</label>
                    <input type="text" class="form-control" name="shipping_address" id="qShippingAddress" value="<?= e($pc['address'] ?? '') ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tên người nhận <small class="text-muted">(không nhập sẽ lấy người liên hệ)</small></label>
                    <input type="text" class="form-control" name="shipping_contact" placeholder="Để trống để dùng người liên hệ">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">SĐT người nhận <small class="text-muted">(không nhập sẽ lấy người liên hệ)</small></label>
                    <input type="text" class="form-control" name="shipping_phone" placeholder="Để trống để dùng SĐT người liên hệ">
                </div>
            </div>
            <div class="mb-3 d-none" id="deliveryPartnerRow">
                <label class="form-label">Đối tác giao</label>
                <input type="text" class="form-control" name="delivery_partner" placeholder="Tên đối tác giao hàng">
            </div>
            <div class="mb-2">
                <label class="form-label">Điều khoản bổ sung</label>
                <textarea name="delivery_notes" class="form-control" rows="3" placeholder="Ghi chú / điều khoản giao hàng..."></textarea>
            </div>
        </div>
    </div>

    <!-- Ghi chú -->
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label"><?= $fl["notes"] ?? "Ghi chú" ?></label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Ghi chú đơn hàng..."><?= e($dv['notes'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label"><?= $fl["order_terms"] ?? "Điều khoản" ?></label>
                    <textarea name="order_terms" class="form-control" rows="3" placeholder="Điều khoản thanh toán..."><?= e($dv['order_terms'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Sản phẩm -->
    <div class="card">
        <div class="card-header"><h5 class="card-title mb-0"><i class="ri-shopping-bag-line me-1"></i> Sản phẩm</h5></div>
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

    <!-- Thanh toán -->
    <div class="row">
        <div class="col-lg-6"></div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body" style="background:#e8f0fe">
                    <h6 class="fw-bold mb-3"><i class="ri-money-dollar-circle-line me-1"></i> Thông tin thanh toán</h6>
                    <div class="mb-2 d-flex align-items-center gap-2">
                        <span class="flex-shrink-0" style="width:140px">Phương thức TT</span>
                        <select name="payment_method" class="form-select" style="width:200px">
                            <option value="bank_transfer">Chuyển khoản</option>
                            <option value="cash">Tiền mặt</option>
                            <option value="credit_card">Thẻ tín dụng</option>
                            <option value="other">Khác</option>
                        </select>
                    </div>
                    <div class="mb-2 d-flex align-items-center justify-content-between">
                        <span>Phí vận chuyển sau thuế</span>
                        <input class="form-check-input" type="checkbox" name="shipping_after_tax" value="1">
                    </div>
                    <div class="mb-2 d-flex align-items-center gap-2">
                        <span class="flex-shrink-0" style="width:140px">Phí vận chuyển</span>
                        <div class="input-group" style="width:120px"><input type="number" class="form-control" name="transport_percent" value="0" min="0" step="0.01" onchange="calcPaymentRow(this,'transport')"><span class="input-group-text">%</span></div>
                        <input type="number" class="form-control" name="transport_amount" value="0" min="0" onchange="calculateTotal()" style="width:120px">
                    </div>
                    <div class="mb-2 d-flex align-items-center gap-2">
                        <span class="flex-shrink-0" style="width:140px">Chiết khấu</span>
                        <div class="input-group" style="width:120px"><input type="number" class="form-control" name="discount_percent" value="0" min="0" step="0.01" onchange="calcPaymentRow(this,'discount')"><span class="input-group-text">%</span></div>
                        <input type="number" class="form-control" name="discount_amount" value="0" min="0" onchange="calculateTotal()" style="width:120px">
                    </div>
                    <div class="mb-2 d-flex align-items-center justify-content-between">
                        <span>Chiết khấu sau thuế</span>
                        <input class="form-check-input" type="checkbox" name="discount_after_tax" value="1">
                    </div>
                    <div class="mb-2 d-flex align-items-center gap-2">
                        <span class="flex-shrink-0" style="width:140px">Thuế VAT</span>
                        <div class="input-group" style="width:120px"><input type="number" class="form-control" name="tax_rate" value="0" min="0" step="0.01" onchange="calcPaymentRow(this,'tax')"><span class="input-group-text">%</span></div>
                        <input type="number" class="form-control" name="tax_amount" value="0" readonly style="width:120px;background:#dce6f5">
                    </div>
                    <div class="mb-2 d-flex align-items-center gap-2">
                        <span class="flex-shrink-0" style="width:140px">Phí lắp đặt</span>
                        <div class="input-group" style="width:120px"><input type="number" class="form-control" name="installation_percent" value="0" min="0" step="0.01" onchange="calcPaymentRow(this,'installation')"><span class="input-group-text">%</span></div>
                        <input type="number" class="form-control" name="installation_amount" value="0" min="0" onchange="calculateTotal()" style="width:120px">
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
</form>

<style>
.product-search-wrap { position: relative; }
.product-search-wrap input { width: 100%; }
.product-dropdown { position: fixed; background: #fff; border: 1px solid #ddd; border-radius: 6px; max-height: 220px; overflow-y: auto; z-index: 1060; display: none; box-shadow: 0 4px 12px rgba(0,0,0,.15); min-width: 250px; }
.product-dropdown .pd-item { padding: 8px 12px; cursor: pointer; font-size: 13px; border-bottom: 1px solid #f3f3f3; }
.product-dropdown .pd-item:hover { background: #f0f4ff; }
.product-dropdown .pd-item .pd-sku { color: #888; font-size: 12px; }
</style>

<script>
// AJAX search KH
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
                if (!results.length) { csDrop.innerHTML = '<div class="px-3 py-2 text-muted">Không tìm thấy</div>'; csDrop.style.display = 'block'; return; }
                csDrop.innerHTML = results.map(function(c) {
                    var name = c.company_name || c.full_name || ((c.first_name || '') + ' ' + (c.last_name || '')).trim();
                    var sub = [c.account_code, c.phone || c.company_phone].filter(Boolean).join(' - ');
                    return '<div class="px-3 py-2 border-bottom" style="cursor:pointer" onmousedown="pickContact(' + JSON.stringify(c).replace(/"/g, '&quot;') + ')">'
                        + '<div class="fw-medium">' + name + '</div>' + (sub ? '<small class="text-muted">' + sub + '</small>' : '') + '</div>';
                }).join('');
                csDrop.style.display = 'block';
            });
    }, 300);
});
csInput?.addEventListener('blur', function() { setTimeout(function() { csDrop.style.display = 'none'; }, 200); });

function pickContact(c) {
    var name = c.company_name || c.full_name || ((c.first_name || '') + ' ' + (c.last_name || '')).trim();
    if (c.account_code) name += ' (' + c.account_code + ')';
    document.getElementById('contactIdInput').value = c.id;
    document.getElementById('contactSearchInput').value = name;
    document.getElementById('qCustomerName').textContent = c.company_name || c.full_name || name;
    document.getElementById('qPhoneDisplay').textContent = c.company_phone || c.phone || '-';
    document.getElementById('qEmailDisplay').textContent = c.company_email || c.email || '-';
    document.getElementById('qAddressDisplay').textContent = c.address || '-';
    document.getElementById('qShippingAddress').value = c.address || '';
    csDrop.style.display = 'none';
    // Load persons
    fetch('<?= url("contacts") ?>/' + c.id + '/persons', { credentials: 'same-origin' })
        .then(r => r.json())
        .then(function(persons) {
            var cpSel = document.getElementById('contactPersonSelect');
            cpSel.innerHTML = '<option value="">Chọn người liên hệ</option>';
            (persons || []).forEach(function(p, i) {
                var o = document.createElement('option');
                o.value = p.id;
                o.textContent = (p.title ? p.title + ' ' : '') + p.full_name + (p.position ? ' - ' + p.position : '');
                if (i === 0) o.selected = true;
                cpSel.appendChild(o);
            });
        }).catch(function(){});
}

// Pre-fill
<?php if ($preContactId && $pc): ?>
// Already filled by PHP
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
        <td><div class="product-search-wrap"><input type="text" class="form-control" id="item-sku-${idx}" placeholder="Mã SP" value="${data?.sku || ''}" autocomplete="off" onfocus="searchProduct(this,${idx},'sku')" oninput="searchProduct(this,${idx},'sku')"><div class="product-dropdown" id="item-skudrop-${idx}"></div></div></td>
        <td><div class="product-search-wrap"><input type="text" class="form-control" id="item-namesearch-${idx}" placeholder="Tên SP" value="${data?.product_name || ''}" autocomplete="off" onfocus="searchProduct(this,${idx},'name')" oninput="searchProduct(this,${idx},'name')"><div class="product-dropdown" id="item-namedrop-${idx}"></div></div><input type="hidden" name="items[${idx}][product_id]" id="item-product-${idx}" value="${data?.product_id || ''}"><input type="hidden" name="items[${idx}][product_name]" id="item-name-${idx}" value="${data?.product_name || ''}"></td>
        <td><input type="text" class="form-control" name="items[${idx}][unit]" id="item-unit-${idx}" value="${data?.unit || 'Cái'}"></td>
        <td><input type="number" class="form-control" name="items[${idx}][quantity]" value="${data?.quantity || 0}" min="0" step="0.01" onchange="calculateRow(${idx})"></td>
        <td><input type="number" class="form-control" name="items[${idx}][unit_price]" id="item-price-${idx}" value="${data?.unit_price || 0}" min="0" onchange="calculateRow(${idx})"></td>
        <td><input type="number" class="form-control" name="items[${idx}][discount_percent]" id="item-ckpct-${idx}" value="0" min="0" max="100" step="0.01" onchange="calcDiscountFromPct(${idx})"></td>
        <td><input type="number" class="form-control" name="items[${idx}][discount]" id="item-discount-${idx}" value="${data?.discount || 0}" min="0" onchange="calculateRow(${idx})"></td>
        <td><input type="number" class="form-control" name="items[${idx}][tax_rate]" id="item-tax-${idx}" value="${data?.tax_rate || 0}" min="0" max="100" step="0.01" onchange="calculateRow(${idx})"></td>
        <td class="fw-medium text-end" id="item-total-${idx}">0.00</td>
        <td><div class="d-flex gap-1"><button type="button" class="btn btn-soft-danger btn-icon" onclick="removeItem(${idx})"><i class="ri-delete-bin-line"></i></button><button type="button" class="btn btn-soft-primary btn-icon" onclick="addItem()"><i class="ri-add-line"></i></button></div></td>
    `;
    tbody.appendChild(tr);
    if (data) calculateRow(idx);
}

function positionDropdown(input, drop) { var rect = input.getBoundingClientRect(); drop.style.top = (rect.bottom + 2) + 'px'; drop.style.left = rect.left + 'px'; drop.style.width = Math.max(rect.width, 250) + 'px'; }

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
                    `<div class="pd-item" onclick="pickProduct(${idx}, ${JSON.stringify(p).replace(/"/g, '&quot;')})"><strong>${p.name}</strong> <span class="pd-sku">${p.sku || ''}</span><br><small class="text-muted">${Number(p.price).toLocaleString('vi-VN')} ₫ / ${p.unit || 'Cái'}</small></div>`
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

document.addEventListener('click', function(e) { if (!e.target.closest('.product-search-wrap')) document.querySelectorAll('.product-dropdown').forEach(d => d.style.display = 'none'); });

function calcDiscountFromPct(idx) {
    const qty = parseFloat(document.querySelector(`[name="items[${idx}][quantity]"]`)?.value || 0);
    const price = parseFloat(document.getElementById('item-price-' + idx)?.value || 0);
    const pct = parseFloat(document.getElementById('item-ckpct-' + idx)?.value || 0);
    document.getElementById('item-discount-' + idx).value = Math.round(qty * price * pct / 100);
    calculateRow(idx);
}

function removeItem(idx) { document.getElementById('item-row-' + idx)?.remove(); calculateTotal(); }

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
    const transportAmount = parseFloat(document.querySelector('[name="transport_amount"]')?.value || 0);
    const installAmount = parseFloat(document.querySelector('[name="installation_amount"]')?.value || 0);
    const total = Math.max(0, subtotal + taxAmount - discountAmount + transportAmount + installAmount);
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
    if (type === 'transport') document.querySelector('[name="transport_amount"]').value = amount;
    if (type === 'discount') document.querySelector('[name="discount_amount"]').value = amount;
    if (type === 'tax') document.querySelector('[name="tax_amount"]').value = amount;
    if (type === 'installation') document.querySelector('[name="installation_amount"]').value = amount;
    calculateTotal();
}

function formatMoney(amount) { return new Intl.NumberFormat('vi-VN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Math.round(amount * 100) / 100); }

addItem();

// Toggle delivery partner row
document.querySelectorAll('input[name="delivery_type"]').forEach(r => r.addEventListener('change', function() {
    document.getElementById('deliveryPartnerRow').classList.toggle('d-none', this.value !== 'partner');
}));
</script>
