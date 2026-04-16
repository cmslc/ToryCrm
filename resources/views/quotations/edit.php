<?php $pageTitle = 'Sửa báo giá ' . $quotation['quote_number']; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0"><?= $pageTitle ?></h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('quotations') ?>">Báo giá</a></li>
                <li class="breadcrumb-item active">Sửa</li>
            </ol>
        </div>

        <form method="POST" action="<?= url('quotations/' . $quotation['id'] . '/update') ?>" id="quotationForm">
            <?= csrf_field() ?>

            <?php
            $deptGrouped = [];
            foreach ($users ?? [] as $u) { $deptGrouped[$u['dept_name'] ?? 'Chưa phân phòng'][] = $u; }
            ?>
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Thông tin báo giá</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Mã báo giá</label>
                            <input type="text" class="form-control" value="<?= e($quotation['quote_number']) ?>" readonly>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Khách hàng</label>
                            <select name="contact_id" class="form-select searchable-select">
                                <option value="">Chọn khách hàng</option>
                                <?php foreach ($contacts ?? [] as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= ($quotation['contact_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['first_name'] . ' ' . ($c['last_name'] ?? '')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Công ty</label>
                            <select name="company_id" class="form-select searchable-select">
                                <option value="">Chọn công ty</option>
                                <?php foreach ($companies ?? [] as $comp): ?>
                                    <option value="<?= $comp['id'] ?>" <?= ($quotation['company_id'] ?? '') == $comp['id'] ? 'selected' : '' ?>><?= e($comp['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Cơ hội liên quan</label>
                            <select name="deal_id" class="form-select">
                                <option value="">Không</option>
                                <?php foreach ($deals ?? [] as $d): ?>
                                    <option value="<?= $d['id'] ?>" <?= ($quotation['deal_id'] ?? '') == $d['id'] ? 'selected' : '' ?>><?= e($d['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Hiệu lực đến</label>
                            <input type="date" class="form-control" name="valid_until" value="<?= e($quotation['valid_until'] ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Người phụ trách</label>
                            <select name="owner_id" class="form-select searchable-select">
                                <option value="">Chọn</option>
                                <?php foreach ($deptGrouped as $dept => $dUsers): ?>
                                <optgroup label="<?= e($dept) ?>">
                                    <?php foreach ($dUsers as $u): ?>
                                    <option value="<?= $u['id'] ?>" <?= ($quotation['owner_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

                    <!-- Items Table -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0"><i class="ri-shopping-bag-line me-1"></i> Sản phẩm</h5>
                            <button type="button" class="btn btn-soft-primary" onclick="addItem()">
                                <i class="ri-add-line me-1"></i> Thêm dòng
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0" id="itemsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:40px">#</th>
                                            <th style="width:140px">Mã sản phẩm</th>
                                            <th style="min-width:200px">Tên sản phẩm</th>
                                            <th style="width:70px">Đơn vị</th>
                                            <th style="width:90px">Số lượng</th>
                                            <th style="width:130px">Đơn giá</th>
                                            <th style="width:80px">CK (%)</th>
                                            <th style="width:110px">CK</th>
                                            <th style="width:80px">VAT (%)</th>
                                            <th style="width:140px">Thành tiền</th>
                                            <th style="width:50px"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsBody">
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="9" class="text-end fw-medium">Tạm tính:</td>
                                            <td id="subtotalDisplay" class="fw-medium">0 ₫</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="9" class="text-end fw-bold fs-5">Tổng cộng:</td>
                                            <td id="totalDisplay" class="fw-bold fs-5 text-primary">0 ₫</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Info -->
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0"><i class="ri-money-dollar-circle-line me-1"></i> Thông tin thanh toán</h5></div>
                        <div class="card-body">
                            <div class="row align-items-end mb-3">
                                <div class="col-md-2"><label class="form-label mb-0 fw-medium">Phí vận chuyển</label></div>
                                <div class="col-md-2">
                                    <div class="input-group input-group">
                                        <input type="number" class="form-control" name="shipping_percent" value="<?= (float)($quotation['shipping_percent'] ?? 0) ?>" min="0" step="0.01" onchange="calcPaymentRow(this,'shipping')">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control form-control" name="shipping_fee" value="<?= (float)($quotation['shipping_fee'] ?? 0) ?>" min="0" onchange="calculateTotal()">
                                </div>
                                <div class="col-md-3"></div>
                                <div class="col-md-2">
                                    <div class="form-check form-check-sm">
                                        <input class="form-check-input" type="checkbox" name="shipping_after_tax" value="1" id="shippingAfterTax" <?= ($quotation['shipping_after_tax'] ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label small" for="shippingAfterTax">Sau thuế</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row align-items-end mb-3">
                                <div class="col-md-2"><label class="form-label mb-0 fw-medium">Chiết khấu</label></div>
                                <div class="col-md-2">
                                    <div class="input-group input-group">
                                        <input type="number" class="form-control" name="discount_percent" value="<?= (float)($quotation['discount_percent'] ?? 0) ?>" min="0" step="0.01" onchange="calcPaymentRow(this,'discount')">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control form-control" name="discount_amount" value="<?= (float)($quotation['discount_amount'] ?? 0) ?>" min="0" onchange="calculateTotal()">
                                </div>
                                <div class="col-md-3"></div>
                                <div class="col-md-2">
                                    <div class="form-check form-check-sm">
                                        <input class="form-check-input" type="checkbox" name="discount_after_tax" value="1" id="discountAfterTax" <?= ($quotation['discount_after_tax'] ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label small" for="discountAfterTax">Sau thuế</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row align-items-end mb-3">
                                <div class="col-md-2"><label class="form-label mb-0 fw-medium">Thuế VAT</label></div>
                                <div class="col-md-2">
                                    <div class="input-group input-group">
                                        <input type="number" class="form-control" name="tax_rate" value="<?= (float)($quotation['tax_rate'] ?? 0) ?>" min="0" step="0.01" onchange="calcPaymentRow(this,'tax')">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control form-control" name="tax_amount" value="<?= (float)($quotation['tax_amount'] ?? 0) ?>" min="0" readonly style="background:#f3f6f9">
                                </div>
                            </div>
                            <div class="row align-items-end mb-3">
                                <div class="col-md-2"><label class="form-label mb-0 fw-medium">Phí lắp đặt</label></div>
                                <div class="col-md-2">
                                    <div class="input-group input-group">
                                        <input type="number" class="form-control" name="installation_percent" value="<?= (float)($quotation['installation_percent'] ?? 0) ?>" min="0" step="0.01" onchange="calcPaymentRow(this,'installation')">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control form-control" name="installation_fee" value="<?= (float)($quotation['installation_fee'] ?? 0) ?>" min="0" onchange="calculateTotal()">
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-2"><span class="fw-bold fs-5">Tổng cộng</span></div>
                                <div class="col-md-5"></div>
                                <div class="col-md-3"><span class="fw-bold fs-5 text-primary" id="grandTotalDisplay">0 ₫</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ghi chú</label>
                                    <textarea name="notes" class="form-control" rows="3"><?= e($quotation['notes'] ?? '') ?></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Điều khoản</label>
                                    <textarea name="terms" class="form-control" rows="3"><?= e($quotation['terms'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
            <div class="card">
                <div class="card-body d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-save-line me-1"></i> Lưu</button>
                    <a href="<?= url('quotations/' . $quotation['id']) ?>" class="btn btn-soft-secondary">Hủy</a>
                </div>
            </div>
        </form>

        <script>
        const products = <?= json_encode($products ?? []) ?>;
        const existingItems = <?= json_encode($items ?? []) ?>;
        let itemIndex = 0;

        function addItem(data) {
            const tbody = document.getElementById('itemsBody');
            const idx = itemIndex++;
            const tr = document.createElement('tr');
            tr.id = 'item-row-' + idx;

            let skuOptions = '<option value="">Chọn</option>';
            let nameOptions = '<option value="">Chọn sản phẩm</option>';
            products.forEach(p => {
                const selected = data && data.product_id == p.id ? 'selected' : '';
                skuOptions += `<option value="${p.id}" data-price="${p.price}" data-unit="${p.unit || 'Cái'}" data-tax="${p.tax_rate || 0}" data-name="${p.name}" ${selected}>${p.sku || p.name}</option>`;
                nameOptions += `<option value="${p.id}" data-price="${p.price}" data-unit="${p.unit || 'Cái'}" data-tax="${p.tax_rate || 0}" data-sku="${p.sku || ''}" ${selected}>${p.name}</option>`;
            });

            tr.innerHTML = `
                <td class="text-center text-muted">${idx + 1}</td>
                <td>
                    <select class="form-select sku-select searchable-select" onchange="selectBySku(this, ${idx})">
                        ${skuOptions}
                    </select>
                </td>
                <td>
                    <select class="form-select product-select searchable-select" onchange="selectProduct(this, ${idx})">
                        ${nameOptions}
                    </select>
                    <input type="hidden" name="items[${idx}][product_id]" id="item-product-${idx}" value="${data?.product_id || ''}">
                    <input type="hidden" name="items[${idx}][product_name]" id="item-name-${idx}" value="${data?.product_name || ''}">
                </td>
                <td><input type="text" class="form-control" name="items[${idx}][unit]" id="item-unit-${idx}" value="${data?.unit || 'Cái'}"></td>
                <td><input type="number" class="form-control" name="items[${idx}][quantity]" value="${data?.quantity || 1}" min="0.01" step="0.01" onchange="calculateRow(${idx})"></td>
                <td><input type="number" class="form-control" name="items[${idx}][unit_price]" id="item-price-${idx}" value="${data?.unit_price || 0}" min="0" onchange="calculateRow(${idx})"></td>
                <td><input type="number" class="form-control" name="items[${idx}][discount_percent]" id="item-ckpct-${idx}" value="0" min="0" max="100" step="0.01" onchange="calcDiscountFromPct(${idx})"></td>
                <td><input type="number" class="form-control" name="items[${idx}][discount]" id="item-discount-${idx}" value="${data?.discount || 0}" min="0" onchange="calculateRow(${idx})"></td>
                <td><input type="number" class="form-control" name="items[${idx}][tax_rate]" id="item-tax-${idx}" value="${data?.tax_rate || 0}" min="0" max="100" step="0.01" onchange="calculateRow(${idx})"></td>
                <td class="fw-medium text-end" id="item-total-${idx}">0 ₫</td>
                <td>
                    <button type="button" class="btn btn-soft-danger btn-icon" onclick="removeItem(${idx})"><i class="ri-delete-bin-line"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
            if (typeof window._initSearchableSelect === 'function') window._initSearchableSelect();
            if (data) calculateRow(idx);
        }

        function selectProduct(select, idx) {
            const option = select.options[select.selectedIndex];
            if (option.value) {
                document.getElementById('item-product-' + idx).value = option.value;
                document.getElementById('item-name-' + idx).value = option.text;
                document.getElementById('item-price-' + idx).value = option.dataset.price || 0;
                document.getElementById('item-unit-' + idx).value = option.dataset.unit || 'Cái';
                document.getElementById('item-tax-' + idx).value = option.dataset.tax || 0;
                const row = document.getElementById('item-row-' + idx);
                const skuSel = row?.querySelector('.sku-select');
                if (skuSel) skuSel.value = option.value;
            } else {
                document.getElementById('item-product-' + idx).value = '';
                document.getElementById('item-name-' + idx).value = '';
            }
            calculateRow(idx);
        }

        function selectBySku(select, idx) {
            const option = select.options[select.selectedIndex];
            if (option.value) {
                document.getElementById('item-product-' + idx).value = option.value;
                document.getElementById('item-name-' + idx).value = option.dataset.name || '';
                document.getElementById('item-price-' + idx).value = option.dataset.price || 0;
                document.getElementById('item-unit-' + idx).value = option.dataset.unit || 'Cái';
                document.getElementById('item-tax-' + idx).value = option.dataset.tax || 0;
                const row = document.getElementById('item-row-' + idx);
                const nameSel = row?.querySelector('.product-select');
                if (nameSel) nameSel.value = option.value;
            }
            calculateRow(idx);
        }

        function calcDiscountFromPct(idx) {
            const qty = parseFloat(document.querySelector(`[name="items[${idx}][quantity]"]`)?.value || 0);
            const price = parseFloat(document.getElementById('item-price-' + idx)?.value || 0);
            const pct = parseFloat(document.getElementById('item-ckpct-' + idx)?.value || 0);
            const discount = Math.round(qty * price * pct / 100);
            document.getElementById('item-discount-' + idx).value = discount;
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
            document.getElementById('subtotalDisplay').textContent = formatMoney(subtotal);

            const taxRate = parseFloat(document.querySelector('[name="tax_rate"]')?.value || 0);
            const taxAmount = subtotal * taxRate / 100;
            document.querySelector('[name="tax_amount"]').value = Math.round(taxAmount);

            const discountAmount = parseFloat(document.querySelector('[name="discount_amount"]')?.value || 0);
            const shippingFee = parseFloat(document.querySelector('[name="shipping_fee"]')?.value || 0);
            const installFee = parseFloat(document.querySelector('[name="installation_fee"]')?.value || 0);
            const total = Math.max(0, subtotal + taxAmount - discountAmount + shippingFee + installFee);

            document.getElementById('totalDisplay').textContent = formatMoney(total);
            var gt = document.getElementById('grandTotalDisplay');
            if (gt) gt.textContent = formatMoney(total);
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
            return new Intl.NumberFormat('vi-VN').format(Math.round(amount)) + ' ₫';
        }

        // Load existing items
        if (existingItems.length > 0) {
            existingItems.forEach(item => addItem(item));
        } else {
            addItem();
        }
        </script>
