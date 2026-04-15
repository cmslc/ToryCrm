<?php
$pageTitle = 'Đơn hàng & Báo giá';
$currentStatus = $filters['status'] ?? '';
$sc = ['pending'=>'warning','approved'=>'primary','cancelled'=>'danger','unpaid'=>'info','paid'=>'success','completed'=>'dark','collected'=>'secondary'];
$sl = ['pending'=>'Chờ duyệt','approved'=>'Đã duyệt','cancelled'=>'Đã hủy','unpaid'=>'Chưa thanh toán','paid'=>'Đã thanh toán','completed'=>'Đã hoàn thành','collected'=>'Đã thu trong kỳ'];
$orderColumns = [
    'col-code' => 'Mã',
    'col-type' => 'Loại',
    'col-customer' => 'Khách hàng',
    'col-company' => 'Công ty',
    'col-total' => 'Tổng tiền',
    'col-status' => 'Trạng thái',
    'col-payment' => 'Thanh toán',
    'col-owner' => 'Phụ trách',
    'col-shipping' => 'Giao hàng',
    'col-lading' => 'Vận đơn',
    'col-commission' => 'Hoa hồng',
    'col-created' => 'Ngày tạo',
];
?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Đơn hàng & Báo giá</h4>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-soft-secondary" id="toggleColumnPanel">Hiển thị cột <i class="ri-arrow-down-s-line ms-1"></i></button>
                <a href="<?= url('orders/create?type=quote') ?>" class="btn btn-soft-info"><i class="ri-file-text-line me-1"></i> Tạo báo giá</a>
                <a href="<?= url('orders/create?type=order') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Tạo đơn hàng</a>
            </div>
        </div>

        <!-- Column Options Panel -->
        <div class="card mb-2 d-none" id="columnPanel">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-2">Cột</h6>
                        <div class="d-flex flex-wrap gap-3">
                            <?php foreach ($orderColumns as $colId => $colLabel): ?>
                            <div class="form-check">
                                <input class="form-check-input column-toggle" type="checkbox" id="<?= $colId ?>" data-column="<?= $colId ?>" checked>
                                <label class="form-check-label" for="<?= $colId ?>"><?= $colLabel ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button type="button" class="btn btn-soft-secondary py-1 px-2" id="resetColumns"><i class="ri-refresh-line me-1"></i>Đặt lại</button>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header p-2">
                <form method="GET" action="<?= url('orders') ?>" class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="search-box" style="min-width:200px;max-width:300px">
                        <input type="text" class="form-control" name="search" placeholder="Tìm mã đơn, khách hàng..." value="<?= e($filters['search'] ?? '') ?>">
                        <i class="ri-search-line search-icon"></i>
                    </div>
                    <select name="type" class="form-select" style="width:auto;min-width:130px" onchange="this.form.submit()">
                        <option value="">Tất cả loại</option>
                        <option value="order" <?= ($filters['type'] ?? '') === 'order' ? 'selected' : '' ?>>Đơn hàng</option>
                        <option value="quote" <?= ($filters['type'] ?? '') === 'quote' ? 'selected' : '' ?>>Báo giá</option>
                    </select>
                    <select name="payment_status" class="form-select" style="width:auto;min-width:130px" onchange="this.form.submit()">
                        <option value="">Thanh toán</option>
                        <option value="unpaid" <?= ($filters['payment_status'] ?? '') === 'unpaid' ? 'selected' : '' ?>>Chưa TT</option>
                        <option value="partial" <?= ($filters['payment_status'] ?? '') === 'partial' ? 'selected' : '' ?>>Một phần</option>
                        <option value="paid" <?= ($filters['payment_status'] ?? '') === 'paid' ? 'selected' : '' ?>>Đã TT</option>
                    </select>
                    <input type="hidden" name="status" value="<?= e($currentStatus) ?>">
                    <button type="submit" class="btn btn-primary"><i class="ri-search-line me-1"></i> Tìm</button>
                    <?php if (!empty(array_filter($filters ?? []))): ?>
                        <a href="<?= url('orders') ?>" class="btn btn-soft-danger"><i class="ri-refresh-line me-1"></i> Xóa lọc</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="card-body py-2 px-3 d-flex align-items-center gap-1 border-top">
                <div class="flex-grow-1 d-flex" style="overflow-x:auto;scrollbar-width:none;-webkit-overflow-scrolling:touch">
                    <div class="d-flex gap-1 flex-nowrap">
                        <a href="<?= url('orders?' . http_build_query(array_diff_key($filters ?? [], ['status'=>'','page'=>'']))) ?>" class="btn <?= !$currentStatus ? 'btn-dark' : 'btn-soft-dark' ?> btn-label right rounded-pill text-nowrap waves-effect">
                            Tất cả <span class="label-icon align-middle rounded-pill fs-12 ms-2"><?= number_format($totalAll) ?></span>
                        </a>
                        <?php foreach ($sl as $key => $label):
                            $count = 0;
                            foreach ($statusCounts ?? [] as $stc) { if ($stc['status'] === $key) $count = $stc['count']; }
                            $color = $sc[$key] ?? 'secondary';
                            $isActive = $currentStatus === $key;
                        ?>
                        <a href="<?= url('orders?status=' . $key . '&' . http_build_query(array_diff_key($filters ?? [], ['status'=>'','page'=>'']))) ?>"
                           class="btn <?= $isActive ? "btn-{$color}" : "btn-soft-{$color}" ?> btn-label right rounded-pill text-nowrap waves-effect">
                            <?= $label ?> <span class="label-icon align-middle rounded-pill fs-12 ms-2"><?= number_format($count) ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="dropdown flex-shrink-0 ms-auto">
                    <button class="btn btn-soft-secondary py-1 px-2" data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= url('orders/trash') ?>"><i class="ri-delete-bin-line me-2"></i>Đã xóa</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="col-code">Mã</th>
                                <th class="col-type">Loại</th>
                                <th class="col-customer">Khách hàng</th>
                                <th class="col-company">Công ty</th>
                                <th class="col-total">Tổng tiền</th>
                                <th class="col-status">Trạng thái</th>
                                <th class="col-payment">Thanh toán</th>
                                <th class="col-owner">Phụ trách</th>
                                <th class="col-shipping">Giao hàng</th>
                                <th class="col-lading">Vận đơn</th>
                                <th class="col-commission">Hoa hồng</th>
                                <th class="col-created">Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($orders['items'])): ?>
                                <?php
                                $pc = ['unpaid'=>'danger','partial'=>'warning','paid'=>'success'];
                                $pl = ['unpaid'=>'Chưa TT','partial'=>'Một phần','paid'=>'Đã TT'];
                                ?>
                                <?php foreach ($orders['items'] as $order): ?>
                                    <tr>
                                        <td class="col-code"><a href="<?= url('orders/' . $order['id']) ?>" class="fw-medium"><?= e($order['order_number']) ?></a></td>
                                        <td class="col-type">
                                            <?= $order['type'] === 'quote'
                                                ? '<span class="badge bg-info-subtle text-info">Báo giá</span>'
                                                : '<span class="badge bg-primary-subtle text-primary">Đơn hàng</span>' ?>
                                        </td>
                                        <td class="col-customer"><?= e(trim(($order['contact_first_name'] ?? '') . ' ' . ($order['contact_last_name'] ?? ''))) ?: '-' ?></td>
                                        <td class="col-company"><?= e($order['company_name'] ?? '-') ?></td>
                                        <td class="col-total fw-medium"><?= format_money($order['total']) ?></td>
                                        <td class="col-status"><span class="badge bg-<?= $sc[$order['status']] ?? 'secondary' ?>"><?= $sl[$order['status']] ?? '' ?></span></td>
                                        <td class="col-payment"><span class="badge bg-<?= $pc[$order['payment_status']] ?? 'secondary' ?>-subtle text-<?= $pc[$order['payment_status']] ?? 'secondary' ?>"><?= $pl[$order['payment_status']] ?? '' ?></span></td>
                                        <td class="col-owner"><?= e($order['owner_name'] ?? '-') ?></td>
                                        <td class="col-shipping fs-12 text-muted"><?= e($order['shipping_contact'] ?? '') ? e($order['shipping_contact']) . ' - ' . e($order['shipping_phone'] ?? '') : '-' ?></td>
                                        <td class="col-lading fs-12"><?= e($order['lading_code'] ?? '') ?: '-' ?></td>
                                        <td class="col-commission"><?= ($order['commission_amount'] ?? 0) > 0 ? format_money($order['commission_amount']) : '-' ?></td>
                                        <td class="col-created"><?= format_date($order['created_at']) ?></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn btn-soft-secondary" data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="<?= url('orders/' . $order['id']) ?>"><i class="ri-eye-line me-2"></i>Xem</a></li>
                                                    <li><a class="dropdown-item" href="<?= url('orders/' . $order['id'] . '/edit') ?>"><i class="ri-pencil-line me-2"></i>Sửa</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form method="POST" action="<?= url('orders/' . $order['id'] . '/delete') ?>" data-confirm="Xác nhận xóa?">
                                                            <?= csrf_field() ?><button class="dropdown-item text-danger"><i class="ri-delete-bin-line me-2"></i>Xóa</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="9" class="text-center py-4 text-muted"><i class="ri-file-list-3-line fs-1 d-block mb-2"></i>Chưa có đơn hàng</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (($orders['total_pages'] ?? 0) > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">Hiển thị <?= count($orders['items']) ?> / <?= $orders['total'] ?></div>
                        <nav><ul class="pagination mb-0">
                            <?php for ($i = 1; $i <= $orders['total_pages']; $i++): ?>
                                <li class="page-item <?= $i === $orders['page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= url('orders?page=' . $i . '&' . http_build_query(array_filter($filters ?? []))) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul></nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>

<script>
// Toggle column panel
document.getElementById('toggleColumnPanel')?.addEventListener('click', function() {
    var panel = document.getElementById('columnPanel');
    panel.classList.toggle('d-none');
    var isOpen = !panel.classList.contains('d-none');
    this.innerHTML = 'Hiển thị cột <i class="ri-arrow-' + (isOpen ? 'up' : 'down') + '-s-line ms-1"></i>';
});

// Column toggle
(function() {
    var storageKey = 'order_columns';
    var saved = JSON.parse(localStorage.getItem(storageKey) || '{}');
    function applyColumns() {
        document.querySelectorAll('.column-toggle').forEach(function(cb) {
            var col = cb.dataset.column;
            var visible = saved[col] !== false;
            cb.checked = visible;
            document.querySelectorAll('.' + col).forEach(function(el) { el.style.display = visible ? '' : 'none'; });
        });
    }
    document.querySelectorAll('.column-toggle').forEach(function(cb) {
        cb.addEventListener('change', function() {
            saved[this.dataset.column] = this.checked;
            localStorage.setItem(storageKey, JSON.stringify(saved));
            applyColumns();
        });
    });
    document.getElementById('resetColumns')?.addEventListener('click', function() {
        saved = {};
        localStorage.removeItem(storageKey);
        applyColumns();
    });
    applyColumns();
})();
</script>
