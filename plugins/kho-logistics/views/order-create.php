<?php
$pageTitle = 'Tạo đơn hàng';
$allContacts = \Core\Database::fetchAll(
    "SELECT id, first_name, last_name, phone, email FROM contacts WHERE tenant_id = ? AND is_deleted = 0 ORDER BY first_name LIMIT 200",
    [$_SESSION['tenant_id'] ?? 1]
);
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Tạo đơn hàng</h4>
    <a href="<?= url('logistics/orders') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<form method="POST" action="<?= url('logistics/orders/create') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Thông tin đơn hàng</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mã đơn</label>
                            <input type="text" class="form-control" name="order_code" placeholder="Tự tạo nếu để trống">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Loại <span class="text-danger">*</span></label>
                            <select name="type" class="form-select">
                                <option value="retail">Hàng lẻ</option>
                                <option value="wholesale">Hàng lô/sỉ</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Khách hàng</label>
                            <div class="position-relative">
                                <input type="text" class="form-control" id="custSearch" placeholder="Gõ tên/SĐT để tìm..." autocomplete="off">
                                <input type="hidden" name="customer_id" id="custId">
                                <input type="hidden" name="customer_name" id="custName">
                                <input type="hidden" name="customer_phone" id="custPhoneHidden">
                                <div id="custDropdown" class="dropdown-menu w-100" style="display:none;max-height:200px;overflow-y:auto;position:absolute;z-index:1060"></div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">SĐT</label>
                            <input type="text" class="form-control" id="custPhoneInput" name="customer_phone_display" placeholder="SĐT khách hàng">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sản phẩm</label>
                        <input type="text" class="form-control" name="product_name" placeholder="Tên sản phẩm / mô tả hàng hóa">
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Tổng kiện</label>
                            <input type="number" class="form-control" name="total_packages" value="1" min="1">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Cân nặng (kg)</label>
                            <input type="number" class="form-control" name="total_weight" step="0.01" min="0">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Số khối (m³)</label>
                            <input type="number" class="form-control" name="total_cbm" step="0.0001" min="0">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Tổng tiền</label>
                            <input type="number" class="form-control" name="total_amount" step="1000" min="0">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">COD thu hộ</label>
                            <input type="number" class="form-control" name="cod_amount" step="1000" min="0" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Phương thức thanh toán</label>
                            <select name="payment_method" class="form-select">
                                <option value="">Chưa chọn</option>
                                <option value="cod">COD</option>
                                <option value="transfer">Chuyển khoản</option>
                                <option value="cash">Tiền mặt</option>
                                <option value="prepaid">Đã thanh toán</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ghi chú</label>
                        <textarea class="form-control" name="note" rows="3" placeholder="Ghi chú đơn hàng..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Ảnh đơn hàng</h5></div>
                <div class="card-body">
                    <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                    <small class="text-muted mt-1 d-block">Chọn nhiều ảnh cùng lúc. Tối đa 10MB/ảnh.</small>
                </div>
            </div>

            <div class="card">
                <div class="card-body d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-save-line me-1"></i> Tạo đơn hàng</button>
                    <a href="<?= url('logistics/orders') ?>" class="btn btn-soft-secondary">Hủy</a>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
(function() {
    var input = document.getElementById('custSearch');
    var dd = document.getElementById('custDropdown');
    var custId = document.getElementById('custId');
    var custName = document.getElementById('custName');
    var custPhone = document.getElementById('custPhoneHidden');
    var phoneInput = document.getElementById('custPhoneInput');
    if (!input) return;

    var contacts = <?= json_encode(array_map(fn($c) => [
        'id' => $c['id'],
        'name' => trim($c['first_name'] . ' ' . ($c['last_name'] ?? '')),
        'phone' => $c['phone'] ?? '',
        'email' => $c['email'] ?? '',
    ], $allContacts)) ?>;

    input.addEventListener('input', function() {
        var q = this.value.trim();
        if (q.length < 2) { dd.style.display = 'none'; return; }

        var results = contacts.filter(function(c) {
            return c.name.toLowerCase().indexOf(q.toLowerCase()) >= 0
                || c.phone.indexOf(q) >= 0
                || c.email.toLowerCase().indexOf(q.toLowerCase()) >= 0;
        }).slice(0, 8);

        var html = '';
        results.forEach(function(c) {
            html += '<a href="#" class="dropdown-item py-2" data-id="' + c.id + '" data-name="' + c.name + '" data-phone="' + c.phone + '">'
                + '<div class="fw-medium">' + c.name + '</div>'
                + (c.phone ? '<small class="text-muted">' + c.phone + '</small>' : '')
                + (c.email ? ' <small class="text-muted">' + c.email + '</small>' : '')
                + '</a>';
        });
        html += '<a href="#" class="dropdown-item py-2 text-primary border-top" data-id="" data-name="' + q + '" data-phone="">'
            + '<i class="ri-add-line me-1"></i> Tạo mới: <strong>' + q + '</strong></a>';

        dd.innerHTML = html;
        dd.style.display = 'block';

        dd.querySelectorAll('[data-name]').forEach(function(a) {
            a.onclick = function(e) {
                e.preventDefault();
                input.value = this.dataset.name;
                custId.value = this.dataset.id;
                custName.value = this.dataset.name;
                custPhone.value = this.dataset.phone;
                phoneInput.value = this.dataset.phone;
                dd.style.display = 'none';
            };
        });
    });

    input.addEventListener('blur', function() {
        setTimeout(function() { dd.style.display = 'none'; }, 200);
        if (input.value && !custName.value) custName.value = input.value;
    });

    phoneInput?.addEventListener('input', function() { custPhone.value = this.value; });
})();
</script>
