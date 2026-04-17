<?php $pageTitle = 'Tạo hợp đồng'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Tạo hợp đồng</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('contracts') ?>" class="btn btn-soft-secondary">Quay lại</a>
        <button type="submit" form="contractForm" class="btn btn-primary"><i class="ri-save-line me-1"></i> Tạo hợp đồng</button>
    </div>
</div>

<form method="POST" action="<?= url('contracts/store') ?>" id="contractForm">
    <?= csrf_field() ?>
    <div class="row">
        <!-- CỘT TRÁI: Thông tin hợp đồng -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-file-shield-line me-1"></i> Thông tin hợp đồng</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Tên hợp đồng <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" required placeholder="VD: 9988/2026/HĐMB/VNT">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số hợp đồng</label>
                        <input type="text" class="form-control" name="contract_number" value="<?= e($contractNumber ?? '') ?>" placeholder="Tự tạo nếu để trống">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kiểu hợp đồng</label>
                        <select name="type" class="form-select">
                            <option value="Mới">Mới</option>
                            <option value="Gia hạn">Gia hạn</option>
                            <option value="Bổ sung">Bổ sung</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày có hiệu lực</label>
                            <input type="date" class="form-control" name="start_date" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày hết hiệu lực</label>
                            <input type="date" class="form-control" name="end_date" value="<?= date('Y-m-d', strtotime('+1 year')) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Địa chỉ lắp đặt</label>
                        <textarea name="installation_address" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi chú</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="auto_renew" id="autoRenew" value="1">
                            <label class="form-check-label" for="autoRenew">Tự động gia hạn</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CỘT GIỮA: Sản phẩm -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0"><i class="ri-shopping-bag-line me-1"></i> Sản phẩm</h5>
                    <button type="button" class="btn btn-soft-primary" id="btnAddItem"><i class="ri-add-line me-1"></i> Thêm SP</button>
                </div>
                <div class="card-body" id="itemsContainer">
                    <div class="contract-item border rounded p-3 mb-3">
                        <div class="mb-2">
                            <label class="form-label">Sản phẩm</label>
                            <select name="item_product_id[]" class="form-select searchable-select">
                                <option value="">Chọn sản phẩm</option>
                                <?php foreach ($products ?? [] as $p): ?>
                                <option value="<?= $p['id'] ?>" data-price="<?= $p['price'] ?>" data-unit="<?= e($p['unit'] ?? '') ?>"><?= e($p['sku'] . ' - ' . $p['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <label class="form-label">Số lượng</label>
                                <input type="number" class="form-control" name="item_qty[]" value="1" min="1">
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label">Đơn giá</label>
                                <input type="number" class="form-control" name="item_price[]" value="0" min="0">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4 mb-2">
                                <label class="form-label">CK(%)</label>
                                <input type="number" class="form-control" name="item_discount_pct[]" value="0" min="0" max="100">
                            </div>
                            <div class="col-4 mb-2">
                                <label class="form-label">VAT(%)</label>
                                <input type="number" class="form-control" name="item_tax[]" value="0" min="0">
                            </div>
                            <div class="col-4 mb-2">
                                <label class="form-label">Đơn vị</label>
                                <input type="text" class="form-control" name="item_unit[]" placeholder="Cái">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tổng hợp tài chính -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-money-dollar-circle-line me-1"></i> Chi phí & Thuế</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Chiết khấu (đ)</label>
                            <input type="number" class="form-control" name="discount_amount" value="0" min="0">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Phí vận chuyển</label>
                            <input type="number" class="form-control" name="shipping_fee" value="0" min="0">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Phí lắp đặt</label>
                            <input type="number" class="form-control" name="installation_fee" value="0" min="0">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Giá trị hợp đồng</label>
                            <input type="number" class="form-control" name="value" value="0" min="0">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CỘT PHẢI: Đối tượng -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-user-3-line me-1"></i> Đối tượng</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Khách hàng <span class="text-danger">*</span></label>
                        <select name="contact_id" class="form-select searchable-select" required>
                            <option value="">Chọn khách hàng</option>
                            <?php foreach ($contacts ?? [] as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= e(trim(($c['company_name'] ?? '') ?: ($c['first_name'] . ' ' . ($c['last_name'] ?? '')))) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Người liên hệ</label>
                        <input type="text" class="form-control" name="contact_name" placeholder="Tên người liên hệ">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Người phụ trách <span class="text-danger">*</span></label>
                        <?php
                        $deptGrouped = [];
                        foreach ($users ?? [] as $u) { $deptGrouped[$u['dept_name'] ?? 'Chưa phân phòng'][] = $u; }
                        ?>
                        <select name="owner_id" class="form-select searchable-select">
                            <option value="">Chọn</option>
                            <?php foreach ($deptGrouped as $dept => $dUsers): ?>
                            <optgroup label="<?= e($dept) ?>">
                                <?php foreach ($dUsers as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= ($u['id'] ?? 0) == ($_SESSION['user']['id'] ?? 0) ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="pending">Chờ duyệt</option>
                            <option value="approved">Đã duyệt</option>
                            <option value="in_progress">Đang thực hiện</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Điều khoản</label>
                        <textarea name="terms" class="form-control" rows="4"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.getElementById('btnAddItem')?.addEventListener('click', function() {
    var container = document.getElementById('itemsContainer');
    var items = container.querySelectorAll('.contract-item');
    var template = items[0].cloneNode(true);
    template.querySelectorAll('input').forEach(el => { if (el.type === 'number') el.value = el.name.includes('qty') ? '1' : '0'; else el.value = ''; });
    template.querySelectorAll('select').forEach(el => el.selectedIndex = 0);
    // Add remove button
    var rmBtn = document.createElement('button');
    rmBtn.type = 'button';
    rmBtn.className = 'btn btn-soft-danger btn-icon float-end mb-2';
    rmBtn.innerHTML = '<i class="ri-delete-bin-line"></i>';
    rmBtn.onclick = function() { this.closest('.contract-item').remove(); };
    template.insertBefore(rmBtn, template.firstChild);
    container.appendChild(template);
});
</script>
