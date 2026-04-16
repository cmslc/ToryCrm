<?php $pageTitle = 'Tạo báo giá'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Tạo báo giá</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('quotations') ?>">Báo giá</a></li>
                <li class="breadcrumb-item active">Tạo mới</li>
            </ol>
        </div>

        <form method="POST" action="<?= url('quotations/store') ?>" id="quotationForm" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="action" id="formAction" value="draft">

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
                            <input type="text" class="form-control" value="<?= e($quoteNumber) ?>" readonly>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Khách hàng</label>
                            <select name="contact_id" class="form-select searchable-select" id="contactSelect" onchange="onContactChange(this)">
                                <option value="">Chọn khách hàng</option>
                                <?php foreach ($contacts ?? [] as $c): ?>
                                    <option value="<?= $c['id'] ?>" data-company="<?= $c['company_id'] ?? '' ?>"><?= e($c['first_name'] . ' ' . ($c['last_name'] ?? '')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Công ty</label>
                            <select name="company_id" class="form-select searchable-select">
                                <option value="">Chọn công ty</option>
                                <?php foreach ($companies ?? [] as $comp): ?>
                                    <option value="<?= $comp['id'] ?>"><?= e($comp['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Hiệu lực đến</label>
                            <input type="date" class="form-control" name="valid_until" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Người phụ trách</label>
                            <select name="owner_id" class="form-select searchable-select">
                                <option value="">Chọn</option>
                                <?php foreach ($deptGrouped as $dept => $dUsers): ?>
                                <optgroup label="<?= e($dept) ?>">
                                    <?php foreach ($dUsers as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option>
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
                        <div class="card-header"><h5 class="card-title mb-0"><i class="ri-money-dollar-circle-line me-1"></i> Chi phí & Thuế</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Phí vận chuyển (%)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="shipping_percent" value="0" min="0" step="0.01" onchange="calcPaymentRow(this,'shipping')">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Phí vận chuyển</label>
                                    <input type="number" class="form-control" name="shipping_fee" value="0" min="0" onchange="calculateTotal()">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Chiết khấu (%)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="discount_percent" value="0" min="0" step="0.01" onchange="calcPaymentRow(this,'discount')">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Chiết khấu</label>
                                    <input type="number" class="form-control" name="discount_amount" value="0" min="0" onchange="calculateTotal()">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Thuế VAT (%)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="tax_rate" value="0" min="0" step="0.01" onchange="calcPaymentRow(this,'tax')">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Tiền thuế</label>
                                    <input type="number" class="form-control" name="tax_amount" value="0" readonly style="background:#f3f6f9">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Phí lắp đặt (%)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="installation_percent" value="0" min="0" step="0.01" onchange="calcPaymentRow(this,'installation')">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Phí lắp đặt</label>
                                    <input type="number" class="form-control" name="installation_fee" value="0" min="0" onchange="calculateTotal()">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="shipping_after_tax" value="1" id="shippingAfterTax">
                                        <label class="form-check-label" for="shippingAfterTax">Phí vận chuyển sau thuế</label>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="discount_after_tax" value="1" id="discountAfterTax">
                                        <label class="form-check-label" for="discountAfterTax">Chiết khấu sau thuế</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3 d-flex align-items-center justify-content-end">
                                    <span class="fw-bold fs-5 me-3">Tổng cộng:</span>
                                    <span class="fw-bold fs-4 text-primary" id="grandTotalDisplay">0 ₫</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes & Terms -->
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
                    <!-- Attachments -->
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0"><i class="ri-attachment-2 me-1"></i> Tài liệu đính kèm</h5></div>
                        <div class="card-body">
                            <input type="file" name="attachments[]" class="form-control mb-2" multiple>
                            <small class="text-muted">Chọn nhiều file cùng lúc. Tối đa 10MB/file. PDF, Word, Excel, hình ảnh...</small>
                        </div>
                    </div>

            <div class="card">
                <div class="card-body d-flex gap-2">
                    <button type="submit" class="btn btn-soft-primary flex-grow-1" onclick="document.getElementById('formAction').value='draft'">
                        <i class="ri-save-line me-1"></i> Lưu nháp
                    </button>
                    <button type="submit" class="btn btn-primary flex-grow-1" onclick="document.getElementById('formAction').value='send'">
                        <i class="ri-send-plane-line me-1"></i> Lưu & Gửi
                    </button>
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
        function onContactChange(sel) {
            var opt = sel.options[sel.selectedIndex];
            var compId = opt ? opt.dataset.company : '';
            if (compId) {
                var compSel = document.querySelector('[name="company_id"]');
                if (compSel) { compSel.value = compId; compSel.dispatchEvent(new Event('change')); }
            }
        }

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
                        <input type="text" class="form-control" id="item-sku-${idx}" placeholder="Tìm mã SP..." value="${data?.sku || ''}" autocomplete="off" onfocus="searchProduct(this,${idx},'sku')" oninput="searchProduct(this,${idx},'sku')">
                        <div class="product-dropdown" id="item-skudrop-${idx}"></div>
                    </div>
                </td>
                <td>
                    <div class="product-search-wrap">
                        <input type="text" class="form-control" id="item-namesearch-${idx}" placeholder="Tìm tên SP..." value="${data?.product_name || ''}" autocomplete="off" onfocus="searchProduct(this,${idx},'name')" oninput="searchProduct(this,${idx},'name')">
                        <div class="product-dropdown" id="item-namedrop-${idx}"></div>
                    </div>
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
            document.getElementById('item-skudrop-' + idx).style.display = 'none';
            document.getElementById('item-namedrop-' + idx).style.display = 'none';
            calculateRow(idx);
        }

        // Close dropdowns on click outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.product-search-wrap')) {
                document.querySelectorAll('.product-dropdown').forEach(d => d.style.display = 'none');
            }
        });

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

        // Add first item
        addItem();
        </script>
