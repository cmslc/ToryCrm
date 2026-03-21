<?php $pageTitle = 'Tạo đơn hàng mua'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Tạo đơn hàng mua</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('purchase-orders') ?>">Đơn mua</a></li>
                <li class="breadcrumb-item active">Tạo mới</li>
            </ol>
        </div>

        <form method="POST" action="<?= url('purchase-orders/store') ?>">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Thông tin đơn mua</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Mã đơn mua</label>
                                    <input type="text" class="form-control" value="<?= e($orderCode) ?>" readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nhà cung cấp</label>
                                    <select name="supplier_id" class="form-select">
                                        <option value="">Chọn NCC</option>
                                        <?php foreach ($suppliers ?? [] as $s): ?>
                                            <option value="<?= $s['id'] ?>"><?= e($s['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Ngày dự kiến nhận</label>
                                    <input type="date" class="form-control" name="expected_date">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Ghi chú</label>
                                    <textarea name="notes" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Sản phẩm</h5>
                            <button type="button" class="btn btn-sm btn-soft-primary" onclick="addItem()"><i class="ri-add-line me-1"></i> Thêm dòng</button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
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
                                    <tbody id="orderItems"></tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3"></td>
                                            <td colspan="2" class="text-end fw-medium">Giảm giá:</td>
                                            <td><input type="number" class="form-control form-control-sm" name="discount_amount" value="0" min="0" onchange="calcTotal()"></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="5" class="text-end fw-bold fs-5">Tổng:</td>
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
                                    <option value="pending">Chờ duyệt</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Người phụ trách</label>
                                <select name="owner_id" class="form-select">
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
                            <a href="<?= url('purchase-orders') ?>" class="btn btn-soft-secondary">Hủy</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <script>
        const products = <?= json_encode($products ?? []) ?>;
        let idx = 0;

        function addItem() {
            const tbody = document.getElementById('orderItems');
            const i = idx++;
            let opts = '<option value="">Chọn SP</option>';
            products.forEach(p => {
                opts += `<option value="${p.id}" data-price="${p.price}" data-unit="${p.unit}" data-tax="${p.tax_rate}">${p.name}${p.sku ? ' ('+p.sku+')' : ''}</option>`;
            });
            const tr = document.createElement('tr');
            tr.id = 'row-' + i;
            tr.innerHTML = `
                <td><select class="form-select form-select-sm" onchange="selProd(this,${i})">${opts}</select>
                    <input type="hidden" name="items[${i}][product_id]" id="pid-${i}">
                    <input type="hidden" name="items[${i}][product_name]" id="pn-${i}"></td>
                <td><input type="number" class="form-control form-control-sm" name="items[${i}][quantity]" value="1" min="0.01" step="0.01" onchange="calcRow(${i})"></td>
                <td><input type="text" class="form-control form-control-sm" name="items[${i}][unit]" id="pu-${i}" value="Cái"></td>
                <td><input type="number" class="form-control form-control-sm" name="items[${i}][unit_price]" id="pp-${i}" value="0" min="0" onchange="calcRow(${i})"></td>
                <td><input type="number" class="form-control form-control-sm" name="items[${i}][tax_rate]" id="pt-${i}" value="0" min="0" max="100" step="0.01" onchange="calcRow(${i})"></td>
                <td class="fw-medium" id="rt-${i}">0 ₫</td>
                <td><button type="button" class="btn btn-sm btn-soft-danger" onclick="document.getElementById('row-'+${i})?.remove();calcTotal()"><i class="ri-close-line"></i></button></td>`;
            tbody.appendChild(tr);
        }

        function selProd(sel, i) {
            const o = sel.options[sel.selectedIndex];
            if (o.value) {
                document.getElementById('pid-'+i).value = o.value;
                document.getElementById('pn-'+i).value = o.text.split(' (')[0];
                document.getElementById('pp-'+i).value = o.dataset.price || 0;
                document.getElementById('pu-'+i).value = o.dataset.unit || 'Cái';
                document.getElementById('pt-'+i).value = o.dataset.tax || 0;
            }
            calcRow(i);
        }

        function calcRow(i) {
            const q = parseFloat(document.querySelector(`[name="items[${i}][quantity]"]`)?.value || 0);
            const p = parseFloat(document.getElementById('pp-'+i)?.value || 0);
            const t = parseFloat(document.getElementById('pt-'+i)?.value || 0);
            document.getElementById('rt-'+i).textContent = fmt(q * p * (1 + t / 100));
            calcTotal();
        }

        function calcTotal() {
            let sub = 0;
            document.querySelectorAll('#orderItems tr').forEach(tr => {
                const q = parseFloat(tr.querySelector('[name*="[quantity]"]')?.value || 0);
                const p = parseFloat(tr.querySelector('[name*="[unit_price]"]')?.value || 0);
                sub += q * p;
            });
            const disc = parseFloat(document.querySelector('[name="discount_amount"]')?.value || 0);
            document.getElementById('totalDisplay').textContent = fmt(Math.max(0, sub - disc));
        }

        function fmt(n) { return new Intl.NumberFormat('vi-VN').format(Math.round(n)) + ' ₫'; }
        addItem();
        </script>
