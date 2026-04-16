<?php
$isQuote = ($order['type'] ?? 'order') === 'quote';
$pageTitle = 'Sửa ' . ($isQuote ? 'báo giá' : 'đơn hàng') . ' ' . $order['order_number'];
?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0"><?= $pageTitle ?></h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('orders') ?>">Đơn hàng</a></li>
                <li class="breadcrumb-item active">Sửa</li>
            </ol>
        </div>

        <form method="POST" action="<?= url('orders/' . $order['id'] . '/update') ?>">
            <?= csrf_field() ?>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Thông tin</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Mã</label>
                                    <input type="text" class="form-control" value="<?= e($order['order_number']) ?>" readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Ngày lập</label>
                                    <input type="date" class="form-control" name="issued_date" value="<?= $order['issued_date'] ?? '' ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Hạn thanh toán</label>
                                    <input type="date" class="form-control" name="due_date" value="<?= $order['due_date'] ?? '' ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Khách hàng</label>
                                    <select name="contact_id" class="form-select searchable-select">
                                        <option value="">Chọn</option>
                                        <?php foreach ($contacts ?? [] as $c): ?>
                                            <option value="<?= $c['id'] ?>" <?= ($order['contact_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['first_name'] . ' ' . ($c['last_name'] ?? '')) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Công ty</label>
                                    <select name="company_id" class="form-select searchable-select">
                                        <option value="">Chọn</option>
                                        <?php foreach ($companies ?? [] as $comp): ?>
                                            <option value="<?= $comp['id'] ?>" <?= ($order['company_id'] ?? '') == $comp['id'] ? 'selected' : '' ?>><?= e($comp['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Cơ hội liên quan</label>
                                    <select name="deal_id" class="form-select">
                                        <option value="">Không</option>
                                        <?php foreach ($deals ?? [] as $d): ?>
                                            <option value="<?= $d['id'] ?>" <?= ($order['deal_id'] ?? '') == $d['id'] ? 'selected' : '' ?>><?= e($d['title']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phương thức TT</label>
                                    <select name="payment_method" class="form-select">
                                        <option value="">Chọn</option>
                                        <option value="cash" <?= ($order['payment_method'] ?? '') === 'cash' ? 'selected' : '' ?>>Tiền mặt</option>
                                        <option value="bank_transfer" <?= ($order['payment_method'] ?? '') === 'bank_transfer' ? 'selected' : '' ?>>Chuyển khoản</option>
                                        <option value="credit_card" <?= ($order['payment_method'] ?? '') === 'credit_card' ? 'selected' : '' ?>>Thẻ tín dụng</option>
                                        <option value="other" <?= ($order['payment_method'] ?? '') === 'other' ? 'selected' : '' ?>>Khác</option>
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Ghi chú</label>
                                    <textarea name="notes" class="form-control" rows="2"><?= e($order['notes'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Giao hàng -->
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Thông tin giao hàng</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Người nhận</label>
                                    <input type="text" class="form-control" name="shipping_contact" value="<?= e($order['shipping_contact'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">SĐT người nhận</label>
                                    <input type="text" class="form-control" name="shipping_phone" value="<?= e($order['shipping_phone'] ?? '') ?>">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Địa chỉ giao hàng</label>
                                    <input type="text" class="form-control" name="shipping_address" value="<?= e($order['shipping_address'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tỉnh/TP</label>
                                    <input type="text" class="form-control" name="shipping_province" value="<?= e($order['shipping_province'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Quận/Huyện</label>
                                    <input type="text" class="form-control" name="shipping_district" value="<?= e($order['shipping_district'] ?? '') ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Mã vận đơn</label>
                                    <input type="text" class="form-control" name="lading_code" value="<?= e($order['lading_code'] ?? '') ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Trạng thái vận đơn</label>
                                    <select name="lading_status" class="form-select">
                                        <option value="">Chọn</option>
                                        <?php foreach (['pending'=>'Chờ giao','shipping'=>'Đang giao','delivered'=>'Đã giao','returned'=>'Hoàn trả'] as $lk=>$lv): ?>
                                        <option value="<?= $lk ?>" <?= ($order['lading_status'] ?? '') === $lk ? 'selected' : '' ?>><?= $lv ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Hoa hồng</label>
                                    <input type="number" class="form-control" name="commission_amount" value="<?= $order['commission_amount'] ?? 0 ?>" min="0" step="1000">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Sản phẩm / Dịch vụ</h5>
                            <button type="button" class="btn btn btn-soft-primary" onclick="addOrderItem()">
                                <i class="ri-add-line me-1"></i> Thêm dòng
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:40px">#</th>
                                            <th style="width:140px">Mã SP</th>
                                            <th style="min-width:200px">Tên sản phẩm</th>
                                            <th style="width:70px">ĐVT</th>
                                            <th style="width:80px">SL</th>
                                            <th style="width:120px">Giá vốn</th>
                                            <th style="width:120px">Giá bán</th>
                                            <th style="width:70px">CK(%)</th>
                                            <th style="width:100px">CK</th>
                                            <th style="width:70px">VAT(%)</th>
                                            <th style="width:130px">Thành tiền</th>
                                            <th style="width:40px"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="orderItems"></tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="10" class="text-end fw-medium">Tạm tính:</td>
                                            <td id="subtotalDisplay" class="fw-medium">0 ₫</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="10" class="text-end fw-bold fs-5">Tổng cộng:</td>
                                            <td id="totalDisplay" class="fw-bold fs-5 text-primary">0 ₫</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Phân loại</h5></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Trạng thái</label>
                                <select name="status" class="form-select">
                                    <?php $statuses = ['pending'=>'Chờ duyệt','approved'=>'Đã duyệt','cancelled'=>'Đã hủy','unpaid'=>'Chưa thanh toán','paid'=>'Đã thanh toán','completed'=>'Đã hoàn thành','collected'=>'Đã thu trong kỳ']; ?>
                                    <?php foreach ($statuses as $k => $v): ?>
                                        <option value="<?= $k ?>" <?= ($order['status'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Thanh toán</label>
                                <select name="payment_status" class="form-select">
                                    <option value="unpaid" <?= ($order['payment_status'] ?? '') === 'unpaid' ? 'selected' : '' ?>>Chưa TT</option>
                                    <option value="partial" <?= ($order['payment_status'] ?? '') === 'partial' ? 'selected' : '' ?>>Một phần</option>
                                    <option value="paid" <?= ($order['payment_status'] ?? '') === 'paid' ? 'selected' : '' ?>>Đã TT</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Đã thanh toán (VNĐ)</label>
                                <input type="number" class="form-control" name="paid_amount" value="<?= $order['paid_amount'] ?? 0 ?>" min="0">
                            </div>
                            <?php
                            $deptGrouped = [];
                            foreach ($users ?? [] as $u) { $deptGrouped[$u['dept_name'] ?? 'Chưa phân phòng'][] = $u; }
                            ?>
                            <div class="mb-3">
                                <label class="form-label">Người phụ trách</label>
                                <select name="owner_id" class="form-select searchable-select">
                                    <option value="">Chọn</option>
                                    <?php foreach ($deptGrouped as $dept => $dUsers): ?>
                                    <optgroup label="<?= e($dept) ?>">
                                        <?php foreach ($dUsers as $u): ?>
                                        <option value="<?= $u['id'] ?>" <?= ($order['owner_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
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
                            <a href="<?= url('orders/' . $order['id']) ?>" class="btn btn-soft-secondary">Hủy</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <script>
        const products = <?= json_encode($products ?? []) ?>;
        const existingItems = <?= json_encode($items ?? []) ?>;
        let itemIndex = 0;

        let searchTimer = null;

        function addOrderItem(data = null) {
            const tbody = document.getElementById('orderItems');
            const idx = itemIndex++;
            const tr = document.createElement('tr');
            tr.id = 'item-row-' + idx;

            tr.innerHTML = `
                <td class="text-center text-muted">${idx + 1}</td>
                <td>
                    <div class="product-search-wrap">
                        <input type="text" class="form-control" id="item-sku-${idx}" placeholder="Mã SP..." value="${data?.product_sku || data?.sku || ''}" autocomplete="off" onfocus="searchProduct(this,${idx},'sku')" oninput="searchProduct(this,${idx},'sku')">
                        <div class="product-dropdown" id="item-skudrop-${idx}"></div>
                    </div>
                </td>
                <td>
                    <div class="product-search-wrap">
                        <input type="text" class="form-control" id="item-namesearch-${idx}" placeholder="Tên SP..." value="${data?.product_name || ''}" autocomplete="off" onfocus="searchProduct(this,${idx},'name')" oninput="searchProduct(this,${idx},'name')">
                        <div class="product-dropdown" id="item-namedrop-${idx}"></div>
                    </div>
                    <input type="hidden" name="items[${idx}][product_id]" id="item-product-${idx}" value="${data?.product_id || ''}">
                    <input type="hidden" name="items[${idx}][product_name]" id="item-name-${idx}" value="${data?.product_name || ''}">
                </td>
                <td><input type="text" class="form-control" name="items[${idx}][unit]" id="item-unit-${idx}" value="${data?.unit || 'Cái'}"></td>
                <td><input type="number" class="form-control" name="items[${idx}][quantity]" value="${data?.quantity || 1}" min="0.01" step="0.01" onchange="calculateRow(${idx})"></td>
                <td><input type="number" class="form-control" name="items[${idx}][cost_price]" id="item-cost-${idx}" value="${data?.cost_price || 0}" min="0"></td>
                <td><input type="number" class="form-control" name="items[${idx}][unit_price]" id="item-price-${idx}" value="${data?.unit_price || 0}" min="0" onchange="calculateRow(${idx})"></td>
                <td><input type="number" class="form-control" name="items[${idx}][discount_percent]" id="item-ckpct-${idx}" value="${data?.discount_percent || 0}" min="0" max="100" step="0.01" onchange="calcDiscountFromPct(${idx})"></td>
                <td><input type="number" class="form-control" name="items[${idx}][discount]" id="item-discount-${idx}" value="${data?.discount || 0}" min="0" onchange="calculateRow(${idx})"></td>
                <td><input type="number" class="form-control" name="items[${idx}][tax_rate]" id="item-tax-${idx}" value="${data?.tax_rate || 0}" min="0" max="100" step="0.01" onchange="calculateRow(${idx})"></td>
                <td class="fw-medium text-end" id="item-total-${idx}">0 ₫</td>
                <td><button type="button" class="btn btn-soft-danger btn-icon" onclick="removeItem(${idx})"><i class="ri-delete-bin-line"></i></button></td>
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
                            '<div class="pd-item" onclick=\'pickProduct(' + idx + ',' + JSON.stringify(p).replace(/'/g, "\\'") + ')\'>' +
                            '<strong>' + p.name + '</strong> <span class="pd-sku">' + (p.sku || '') + '</span>' +
                            '<br><small class="text-muted">' + Number(p.price).toLocaleString('vi-VN') + ' ₫ / ' + (p.unit || 'Cái') + '</small></div>'
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
            document.getElementById('item-skudrop-' + idx).style.display = 'none';
            document.getElementById('item-namedrop-' + idx).style.display = 'none';
            calculateRow(idx);
        }

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.product-search-wrap')) {
                document.querySelectorAll('.product-dropdown').forEach(d => d.style.display = 'none');
            }
        });

        function calcDiscountFromPct(idx) {
            const qty = parseFloat(document.querySelector('[name="items[' + idx + '][quantity]"]')?.value || 0);
            const price = parseFloat(document.getElementById('item-price-' + idx)?.value || 0);
            const pct = parseFloat(document.getElementById('item-ckpct-' + idx)?.value || 0);
            document.getElementById('item-discount-' + idx).value = Math.round(qty * price * pct / 100);
            calculateRow(idx);
        }

        function removeItem(idx) { document.getElementById('item-row-' + idx)?.remove(); calculateTotal(); }

        function calculateRow(idx) {
            const qty = parseFloat(document.querySelector('[name="items[' + idx + '][quantity]"]')?.value || 0);
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
            document.querySelectorAll('#orderItems tr').forEach(tr => {
                const qty = parseFloat(tr.querySelector('[name*="[quantity]"]')?.value || 0);
                const price = parseFloat(tr.querySelector('[name*="[unit_price]"]')?.value || 0);
                subtotal += qty * price;
            });
            document.getElementById('subtotalDisplay').textContent = formatMoney(subtotal);
            document.getElementById('totalDisplay').textContent = formatMoney(subtotal);
        }

        function formatMoney(amount) {
            return new Intl.NumberFormat('vi-VN').format(Math.round(amount)) + ' ₫';
        }

        // Load existing items
        existingItems.forEach(item => addOrderItem(item));
        if (existingItems.length === 0) addOrderItem();
        </script>
        <style>
        .product-search-wrap { position: relative; }
        .product-dropdown { position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid #ddd; border-radius: 6px; max-height: 220px; overflow-y: auto; z-index: 1050; display: none; box-shadow: 0 4px 12px rgba(0,0,0,.1); }
        .product-dropdown .pd-item { padding: 8px 12px; cursor: pointer; font-size: 13px; border-bottom: 1px solid #f3f3f3; }
        .product-dropdown .pd-item:hover { background: #f0f4ff; }
        .product-dropdown .pd-item .pd-sku { color: #888; font-size: 12px; }
        </style>
