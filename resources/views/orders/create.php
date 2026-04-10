<?php
$isQuote = ($type ?? 'order') === 'quote';
$pageTitle = $isQuote ? 'Tạo báo giá' : 'Tạo đơn hàng';
?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0"><?= $pageTitle ?></h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('orders') ?>">Đơn hàng</a></li>
                <li class="breadcrumb-item active">Tạo mới</li>
            </ol>
        </div>

        <form method="POST" action="<?= url('orders/store') ?>" id="orderForm">
            <?= csrf_field() ?>
            <input type="hidden" name="type" value="<?= $type ?>">

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Thông tin <?= $isQuote ? 'báo giá' : 'đơn hàng' ?></h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Mã <?= $isQuote ? 'báo giá' : 'đơn hàng' ?></label>
                                    <input type="text" class="form-control" value="<?= e($orderNumber) ?>" readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Ngày lập</label>
                                    <input type="date" class="form-control" name="issued_date" value="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Hạn thanh toán</label>
                                    <input type="date" class="form-control" name="due_date">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Khách hàng</label>
                                    <select name="contact_id" class="form-select searchable-select">
                                        <option value="">Chọn khách hàng</option>
                                        <?php foreach ($contacts ?? [] as $c): ?>
                                            <option value="<?= $c['id'] ?>" <?= ($selectedContactId ?? 0) == $c['id'] ? 'selected' : '' ?>><?= e($c['first_name'] . ' ' . ($c['last_name'] ?? '')) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Công ty</label>
                                    <select name="company_id" class="form-select searchable-select">
                                        <option value="">Chọn công ty</option>
                                        <?php foreach ($companies ?? [] as $comp): ?>
                                            <option value="<?= $comp['id'] ?>" <?= ($selectedCompanyId ?? 0) == $comp['id'] ? 'selected' : '' ?>><?= e($comp['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Cơ hội liên quan</label>
                                    <select name="deal_id" class="form-select">
                                        <option value="">Không</option>
                                        <?php foreach ($deals ?? [] as $d): ?>
                                            <option value="<?= $d['id'] ?>" <?= ($selectedDealId ?? 0) == $d['id'] ? 'selected' : '' ?>><?= e($d['title']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phương thức thanh toán</label>
                                    <select name="payment_method" class="form-select">
                                        <option value="">Chọn</option>
                                        <option value="cash">Tiền mặt</option>
                                        <option value="bank_transfer">Chuyển khoản</option>
                                        <option value="credit_card">Thẻ tín dụng</option>
                                        <option value="other">Khác</option>
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Ghi chú</label>
                                    <textarea name="notes" class="form-control" rows="2"></textarea>
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
                                <table class="table align-middle mb-0" id="orderItemsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="30%">Sản phẩm</th>
                                            <th width="10%">SL</th>
                                            <th width="10%">ĐVT</th>
                                            <th width="15%">Đơn giá</th>
                                            <th width="10%">Thuế %</th>
                                            <th width="15%">Thành tiền</th>
                                            <th width="5%"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="orderItems">
                                        <!-- Items will be added by JS -->
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5" class="text-end fw-medium">Tạm tính:</td>
                                            <td id="subtotalDisplay" class="fw-medium">0 ₫</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3"></td>
                                            <td colspan="2" class="text-end">
                                                <div class="d-flex align-items-center justify-content-end gap-2">
                                                    <span>Giảm giá:</span>
                                                    <select name="discount_type" class="form-select form-select" style="width:90px">
                                                        <option value="fixed">VNĐ</option>
                                                        <option value="percent">%</option>
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control" name="discount_amount" value="0" min="0" onchange="calculateTotal()">
                                            </td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="5" class="text-end fw-bold fs-5">Tổng cộng:</td>
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
                                    <option value="draft">Nháp</option>
                                    <option value="sent">Đã gửi</option>
                                    <option value="confirmed">Đã xác nhận</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Người phụ trách</label>
                                <select name="owner_id" class="form-select searchable-select">
                                    <option value="">Chọn</option>
                                    <?php foreach ($users ?? [] as $u): ?>
                                        <option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-save-line me-1"></i> Lưu</button>
                            <a href="<?= url('orders') ?>" class="btn btn-soft-secondary">Hủy</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <script>
        const products = <?= json_encode($products ?? []) ?>;
        let itemIndex = 0;

        function addOrderItem(productId = '') {
            const tbody = document.getElementById('orderItems');
            const idx = itemIndex++;
            const tr = document.createElement('tr');
            tr.id = 'item-row-' + idx;

            let productOptions = '<option value="">Nhập tên SP</option>';
            products.forEach(p => {
                productOptions += `<option value="${p.id}" data-price="${p.price}" data-unit="${p.unit}" data-tax="${p.tax_rate}" ${p.id == productId ? 'selected' : ''}>${p.name}${p.sku ? ' ('+p.sku+')' : ''}</option>`;
            });

            tr.innerHTML = `
                <td>
                    <select class="form-select form-select product-select" onchange="selectProduct(this, ${idx})">
                        ${productOptions}
                    </select>
                    <input type="hidden" name="items[${idx}][product_id]" id="item-product-${idx}">
                    <input type="hidden" name="items[${idx}][product_name]" id="item-name-${idx}">
                </td>
                <td><input type="number" class="form-control form-control" name="items[${idx}][quantity]" value="1" min="0.01" step="0.01" onchange="calculateRow(${idx})"></td>
                <td><input type="text" class="form-control form-control" name="items[${idx}][unit]" id="item-unit-${idx}" value="Cái"></td>
                <td><input type="number" class="form-control form-control" name="items[${idx}][unit_price]" id="item-price-${idx}" value="0" min="0" onchange="calculateRow(${idx})"></td>
                <td><input type="number" class="form-control form-control" name="items[${idx}][tax_rate]" id="item-tax-${idx}" value="0" min="0" max="100" step="0.01" onchange="calculateRow(${idx})"></td>
                <td class="fw-medium" id="item-total-${idx}">0 ₫</td>
                <td><button type="button" class="btn btn btn-soft-danger" onclick="removeItem(${idx})"><i class="ri-close-line"></i></button></td>
            `;
            tbody.appendChild(tr);
        }

        function selectProduct(select, idx) {
            const option = select.options[select.selectedIndex];
            if (option.value) {
                document.getElementById('item-product-' + idx).value = option.value;
                document.getElementById('item-name-' + idx).value = option.text.split(' (')[0];
                document.getElementById('item-price-' + idx).value = option.dataset.price || 0;
                document.getElementById('item-unit-' + idx).value = option.dataset.unit || 'Cái';
                document.getElementById('item-tax-' + idx).value = option.dataset.tax || 0;
            } else {
                document.getElementById('item-product-' + idx).value = '';
                document.getElementById('item-name-' + idx).value = '';
            }
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
            const total = qty * price * (1 + tax / 100);
            const el = document.getElementById('item-total-' + idx);
            if (el) el.textContent = formatMoney(total);
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

            const discountAmount = parseFloat(document.querySelector('[name="discount_amount"]')?.value || 0);
            const discountType = document.querySelector('[name="discount_type"]')?.value || 'fixed';
            const discount = discountType === 'percent' ? subtotal * discountAmount / 100 : discountAmount;
            const total = Math.max(0, subtotal - discount);
            document.getElementById('totalDisplay').textContent = formatMoney(total);
        }

        function formatMoney(amount) {
            return new Intl.NumberFormat('vi-VN').format(Math.round(amount)) + ' ₫';
        }

        // Add first item
        addOrderItem();
        </script>
