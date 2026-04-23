<?php
// Expected vars:
// $isEdit (bool), $request (array|null), $items (array), $order (array|null), $contact (array|null), $orderItems (array), $me (array|null), $users (array)
$isEdit = $isEdit ?? false;
$r = $request ?? [];
$orderRef = $order ?? null;
$contactRef = $contact ?? null;
$prefillItems = $isEdit ? ($items ?? []) : ($orderItems ?? []);
$defaultRequester = $me['name'] ?? '';
$defaultDept = $me['dept_name'] ?? '';
$requesterName = $r['requester_name'] ?? $defaultRequester;
$requesterPhone = $r['requester_phone'] ?? ($me['phone'] ?? '');
$department = $r['department'] ?? $defaultDept;
$contractor = $r['contractor'] ?? '';
$installAddr = $r['installation_address'] ?? ($orderRef['shipping_address'] ?? ($contactRef['address'] ?? ''));
$custName = $r['customer_contact_name'] ?? ($orderRef['shipping_contact'] ?? '');
$custPhone = $r['customer_contact_phone'] ?? ($orderRef['shipping_phone'] ?? '');
$requestedDate = $r['requested_date'] ?? date('Y-m-d');
$executionDate = $r['execution_date'] ?? '';
$installerName = $r['installer_name'] ?? '';
$conditionReport = $r['condition_report'] ?? '';
$status = $r['status'] ?? 'pending';
$notes = $r['notes'] ?? '';
$ownerId = $r['owner_id'] ?? ($_SESSION['user']['id'] ?? 0);
$contactId = $r['contact_id'] ?? ($contactRef['id'] ?? ($orderRef['contact_id'] ?? ''));
$orderId = $r['order_id'] ?? ($orderRef['id'] ?? '');
$accountCode = $contactRef['account_code'] ?? ($orderRef['c_account_code'] ?? '');
$custDisplayName = $contactRef['company_name'] ?? ($contactRef['full_name'] ?? ($orderRef['c_company_name'] ?? ($orderRef['c_full_name'] ?? '')));
?>

<input type="hidden" name="order_id" value="<?= e($orderId) ?>">
<input type="hidden" name="contact_id" id="f_contact_id" value="<?= e($contactId) ?>">

<div class="row g-3">
    <div class="col-lg-8">
        <!-- Thông tin chung -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Thông tin yêu cầu</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Người yêu cầu</label>
                        <input type="text" name="requester_name" class="form-control" value="<?= e($requesterName) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Điện thoại</label>
                        <input type="text" name="requester_phone" class="form-control" value="<?= e($requesterPhone) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Bộ phận</label>
                        <input type="text" name="department" class="form-control" value="<?= e($department) ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Đơn vị thi công <span class="text-muted">(nhà thầu)</span></label>
                        <input type="text" name="contractor" class="form-control" value="<?= e($contractor) ?>" placeholder="Ví dụ: CÔNG TY TNHH SEI OPTIFRONTIER VIỆT NAM">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Ngày yêu cầu TC <span class="text-danger">*</span></label>
                        <input type="date" name="requested_date" class="form-control" value="<?= e($requestedDate) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Ngày thi công</label>
                        <input type="datetime-local" name="execution_date" class="form-control" value="<?= $executionDate ? e(date('Y-m-d\TH:i', strtotime($executionDate))) : '' ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Địa chỉ lắp đặt <span class="text-danger">*</span></label>
                        <textarea name="installation_address" class="form-control" rows="2" required><?= e($installAddr) ?></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Người liên hệ tại công trình</label>
                        <input type="text" name="customer_contact_name" class="form-control" value="<?= e($custName) ?>" placeholder="Ms Hà">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SĐT liên hệ</label>
                        <input type="text" name="customer_contact_phone" class="form-control" value="<?= e($custPhone) ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Items -->
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Nội dung yêu cầu thi công</h5>
                <button type="button" class="btn btn-soft-primary" onclick="addItem()"><i class="ri-add-line me-1"></i> Thêm dòng</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px">STT</th>
                                <th style="width:130px">Mã SP</th>
                                <th>Tên hàng</th>
                                <th style="width:180px">Kích thước, màu sắc</th>
                                <th style="width:90px">ĐVT</th>
                                <th style="width:90px">SL</th>
                                <th style="width:130px">Check hàng</th>
                                <th style="width:180px">Ghi chú</th>
                                <th style="width:50px"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Thợ / báo cáo (chỉ hiện khi đang sửa hoặc đã có dữ liệu) -->
        <div class="card mt-3">
            <div class="card-header"><h5 class="card-title mb-0">Điều phối & báo cáo</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Người thi công</label>
                        <input type="text" name="installer_name" class="form-control" value="<?= e($installerName) ?>" placeholder="Tên thợ / nhóm thợ">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-select">
                            <?php foreach (['pending','scheduled','completed','cancelled'] as $s): ?>
                                <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(\App\Controllers\InstallationRequestController::statusLabel($s)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Báo cáo tình trạng hàng hóa của thợ</label>
                        <textarea name="condition_report" class="form-control" rows="3"><?= e($conditionReport) ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Ghi chú nội bộ</label>
                        <textarea name="notes" class="form-control" rows="2"><?= e($notes) ?></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Thông tin chung</h5></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Số CF</label>
                    <input type="text" class="form-control" value="<?= e($isEdit ? ($r['code'] ?? '') : $code) ?>" disabled>
                    <small class="text-muted">Tự sinh</small>
                </div>
                <?php if ($orderRef): ?>
                    <div class="mb-3">
                        <label class="form-label">Đơn hàng gốc</label>
                        <div><a href="<?= url('orders/' . $orderRef['id']) ?>" class="fw-medium"><?= e($orderRef['order_number']) ?></a></div>
                    </div>
                <?php endif; ?>
                <div class="mb-3">
                    <label class="form-label">Khách hàng</label>
                    <?php if ($custDisplayName): ?>
                        <div class="fw-medium"><?= e($custDisplayName) ?></div>
                        <?php if ($accountCode): ?><small class="text-muted">Mã KH: <?= e($accountCode) ?></small><?php endif; ?>
                    <?php else: ?>
                        <div class="text-muted">Chưa chọn khách hàng</div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">Người phụ trách</label>
                    <select name="owner_id" class="form-select">
                        <?php foreach ($users ?? [] as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= $ownerId == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-save-line me-1"></i> Lưu</button>
                    <a href="<?= url('installation-requests') ?>" class="btn btn-soft-secondary">Hủy</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var initItems = <?= json_encode(array_map(function($it) {
    return [
        'product_id' => $it['product_id'] ?? '',
        'product_name' => $it['product_name'] ?? '',
        'product_sku' => $it['product_sku'] ?? ($it['sku'] ?? ($it['p_sku'] ?? '')),
        'size_color' => $it['size_color'] ?? trim(($it['p_dimensions'] ?? '') . (($it['p_dimensions'] ?? '') && ($it['p_color'] ?? '') ? ', ' : '') . ($it['p_color'] ?? '')),
        'unit' => $it['unit'] ?? 'Chiếc',
        'quantity' => $it['quantity'] ?? 1,
        'check_status' => $it['check_status'] ?? '',
        'notes' => $it['notes'] ?? '',
    ];
}, $prefillItems ?? []), JSON_UNESCAPED_UNICODE) ?>;

let itemIndex = 0;

function addItem(data) {
    data = data || {};
    const tbody = document.getElementById('itemsBody');
    const idx = itemIndex++;
    const tr = document.createElement('tr');
    tr.id = 'ir-row-' + idx;
    const checkStatus = data.check_status || '';
    tr.innerHTML = `
        <td class="text-center text-muted ir-stt">${idx + 1}</td>
        <td><input type="text" class="form-control" name="items[${idx}][product_sku]" value="${escAttr(data.product_sku)}"></td>
        <td>
            <input type="text" class="form-control" name="items[${idx}][product_name]" value="${escAttr(data.product_name)}" required>
            <input type="hidden" name="items[${idx}][product_id]" value="${escAttr(data.product_id)}">
        </td>
        <td><input type="text" class="form-control" name="items[${idx}][size_color]" value="${escAttr(data.size_color)}"></td>
        <td><input type="text" class="form-control" name="items[${idx}][unit]" value="${escAttr(data.unit || 'Chiếc')}"></td>
        <td><input type="number" class="form-control text-end" name="items[${idx}][quantity]" value="${data.quantity || 0}" min="0" step="0.01"></td>
        <td>
            <select class="form-select" name="items[${idx}][check_status]">
                <option value="" ${checkStatus === '' ? 'selected' : ''}>-</option>
                <option value="Đã kiểm" ${checkStatus === 'Đã kiểm' ? 'selected' : ''}>Đã kiểm</option>
                <option value="Chưa kiểm" ${checkStatus === 'Chưa kiểm' ? 'selected' : ''}>Chưa kiểm</option>
            </select>
        </td>
        <td><input type="text" class="form-control" name="items[${idx}][notes]" value="${escAttr(data.notes)}"></td>
        <td><button type="button" class="btn btn-soft-danger btn-icon" onclick="removeItem(${idx})"><i class="ri-delete-bin-line"></i></button></td>
    `;
    tbody.appendChild(tr);
}

function removeItem(idx) {
    const row = document.getElementById('ir-row-' + idx);
    if (row) row.remove();
    renumber();
}

function renumber() {
    document.querySelectorAll('#itemsBody .ir-stt').forEach((cell, i) => cell.textContent = i + 1);
}

function escAttr(v) {
    return String(v == null ? '' : v).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// Init
(function() {
    if (initItems && initItems.length) {
        initItems.forEach(function(it) { addItem(it); });
    } else {
        addItem();
    }
})();
</script>
