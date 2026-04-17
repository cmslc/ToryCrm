<?php
$pageTitle = 'Đơn hàng & Báo giá';
$currentStatus = $filters['status'] ?? '';
$sc = ['pending'=>'warning','approved'=>'primary','cancelled'=>'danger','unpaid'=>'info','paid'=>'success','completed'=>'dark','collected'=>'secondary'];
$sl = ['pending'=>'Chờ duyệt','approved'=>'Đã duyệt','cancelled'=>'Đã hủy','unpaid'=>'Chưa thanh toán','paid'=>'Đã thanh toán','completed'=>'Đã hoàn thành','collected'=>'Đã thu trong kỳ'];
$colKeys = array_column($displayColumns ?? [], 'key');
?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Đơn hàng & Báo giá</h4>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-soft-secondary" id="toggleColumnPanel">Hiển thị cột <i class="ri-arrow-down-s-line ms-1"></i></button>
                <a href="<?= url('orders/export?format=csv') ?>" class="btn btn-soft-info"><i class="ri-download-line me-1"></i> Export</a>
                <a href="<?= url('orders/create?type=order') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Tạo đơn hàng</a>
            </div>
        </div>

        <!-- Column Options Panel -->
        <div class="card mb-2 d-none" id="columnPanel">
            <div class="card-body py-3">
                <h6 class="mb-2">Cột hiển thị</h6>
                <div class="d-flex flex-wrap gap-3 mb-3">
                    <?php foreach ($displayColumns as $dc): ?>
                    <div class="form-check">
                        <input class="form-check-input column-toggle" type="checkbox" id="<?= $dc['key'] ?>" data-column="<?= $dc['key'] ?>" checked>
                        <label class="form-check-label" for="<?= $dc['key'] ?>"><?= e($dc['label']) ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn btn-soft-secondary py-1 px-2" id="resetColumns"><i class="ri-refresh-line me-1"></i>Đặt lại</button>
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
                    <?php $dp = $filters['date_period'] ?? ''; ?>
                    <select name="date_period" class="form-select" style="width:auto;min-width:140px" onchange="if(this.value==='custom'){document.getElementById('customDateRange').classList.remove('d-none')}else{this.form.submit()}">
                        <option value="">Thời gian</option>
                        <option value="today" <?= $dp === 'today' ? 'selected' : '' ?>>Hôm nay</option>
                        <option value="yesterday" <?= $dp === 'yesterday' ? 'selected' : '' ?>>Hôm qua</option>
                        <option value="this_week" <?= $dp === 'this_week' ? 'selected' : '' ?>>Tuần này</option>
                        <option value="this_month" <?= $dp === 'this_month' ? 'selected' : '' ?>>Tháng này</option>
                        <option value="last_month" <?= $dp === 'last_month' ? 'selected' : '' ?>>Tháng trước</option>
                        <option value="this_year" <?= $dp === 'this_year' ? 'selected' : '' ?>>Năm nay</option>
                        <option value="custom" <?= $dp === 'custom' ? 'selected' : '' ?>>Thời gian khác</option>
                    </select>
                    <div id="customDateRange" class="d-flex gap-1 <?= $dp === 'custom' ? '' : 'd-none' ?>">
                        <input type="date" name="date_from" class="form-control" style="width:auto" value="<?= e($filters['date_from'] ?? '') ?>" title="Từ ngày">
                        <input type="date" name="date_to" class="form-control" style="width:auto" value="<?= e($filters['date_to'] ?? '') ?>" title="Đến ngày">
                    </div>
                    <input type="hidden" name="status" value="<?= e($currentStatus) ?>">
                    <button type="submit" class="btn btn-primary"><i class="ri-search-line me-1"></i> Tìm</button>
                    <?php if (!empty(array_filter($filters ?? []))): ?>
                        <a href="<?= url('orders') ?>" class="btn btn-soft-danger"><i class="ri-refresh-line me-1"></i> Xóa lọc</a>
                    <?php endif; ?>
                    <select name="per_page" class="form-select ms-auto" style="width:auto;min-width:90px" onchange="this.form.submit()">
                        <?php foreach ([10,20,50,100] as $pp): ?>
                        <option value="<?= $pp ?>" <?= ($filters['per_page'] ?? 20) == $pp ? 'selected' : '' ?>><?= $pp ?> dòng</option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            <div class="card-body py-2 px-3 d-flex align-items-center gap-1 border-top">
                <button type="button" class="btn btn-link text-muted p-0 px-1 flex-shrink-0 d-none" id="tabScrollLeft"><i class="ri-arrow-left-s-line fs-18"></i></button>
                <div class="flex-grow-1 d-flex" id="tabScrollContainer" style="overflow-x:auto;scroll-behavior:smooth;-webkit-overflow-scrolling:touch;scrollbar-width:none;min-width:0">
                <style>#tabScrollContainer::-webkit-scrollbar{display:none}</style>
                    <div class="d-flex gap-1 flex-nowrap" id="tabScrollInner">
                        <a href="<?= url('orders?' . http_build_query(array_diff_key($filters ?? [], ['status'=>'','page'=>'']))) ?>" class="btn <?= !$currentStatus ? 'btn-dark' : 'btn-soft-dark' ?> rounded-pill text-nowrap waves-effect">
                            Tất cả <span class="badge rounded-pill bg-danger ms-1"><?= number_format($totalAll) ?></span>
                        </a>
                        <?php foreach ($sl as $key => $label):
                            $count = 0;
                            foreach ($statusCounts ?? [] as $stc) { if ($stc['status'] === $key) $count = $stc['count']; }
                            $color = $sc[$key] ?? 'secondary';
                            $isActive = $currentStatus === $key;
                        ?>
                        <a href="<?= url('orders?status=' . $key . '&' . http_build_query(array_diff_key($filters ?? [], ['status'=>'','page'=>'']))) ?>"
                           class="btn <?= $isActive ? "btn-{$color}" : "btn-soft-{$color}" ?> rounded-pill text-nowrap waves-effect">
                            <?= $label ?> <span class="badge rounded-pill bg-danger ms-1"><?= number_format($count) ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button type="button" class="btn btn-link text-muted p-0 px-1 flex-shrink-0 d-none" id="tabScrollRight"><i class="ri-arrow-right-s-line fs-18"></i></button>
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
                        <thead class="text-muted table-light">
                            <tr>
                                <?php foreach ($displayColumns as $dc): ?>
                                <th class="<?= $dc['key'] ?>"><?= e($dc['label']) ?></th>
                                <?php endforeach; ?>
                                <th style="width:50px"></th>
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
                                        <?php foreach ($displayColumns as $dc):
                                            $field = $dc['field'];
                                            $key = $dc['key'];
                                            $val = $order[$field] ?? '';
                                        ?>
                                        <td class="<?= $key ?>">
                                        <?php
                                        switch ($field):
                                            case 'order_number':
                                                echo '<a href="' . url('orders/' . $order['id']) . '" class="fw-medium">' . e($val) . '</a>';
                                                break;
                                            case 'type':
                                                echo $val === 'quote'
                                                    ? '<span class="badge bg-info-subtle text-info">Báo giá</span>'
                                                    : '<span class="badge bg-primary-subtle text-primary">Đơn hàng</span>';
                                                break;
                                            case 'contact_id':
                                                $contactName = trim(($order['contact_first_name'] ?? '') . ' ' . ($order['contact_last_name'] ?? ''));
                                                echo $val ? '<a href="' . url('contacts/' . $val) . '">' . e($contactName ?: '-') . '</a>' : '-';
                                                break;
                                            case 'company_id':
                                                echo $val ? '<a href="' . url('companies/' . $val) . '">' . e($order['company_name'] ?? '-') . '</a>' : '-';
                                                break;
                                            case 'deal_id':
                                                echo $val ? '<a href="' . url('deals/' . $val) . '">' . e($order['deal_title'] ?? $val) . '</a>' : '-';
                                                break;
                                            case 'total': case 'subtotal': case 'tax_amount': case 'discount_amount':
                                            case 'transport_amount': case 'installation_amount': case 'paid_amount':
                                            case 'commission_amount':
                                                echo ($val + 0) > 0 ? format_money($val) : '-';
                                                break;
                                            case 'status':
                                                echo '<span class="badge bg-' . ($sc[$val] ?? 'secondary') . '">' . ($sl[$val] ?? $val) . '</span>';
                                                break;
                                            case 'payment_status':
                                                echo '<span class="badge bg-' . ($pc[$val] ?? 'secondary') . '-subtle text-' . ($pc[$val] ?? 'secondary') . '">' . ($pl[$val] ?? $val) . '</span>';
                                                break;
                                            case 'owner_id':
                                                echo user_avatar($order['owner_name'] ?? null, 'primary', $order['owner_avatar'] ?? null);
                                                break;
                                            case 'shipping_contact':
                                                echo $val ? e($val) . ($order['shipping_phone'] ? ' - ' . e($order['shipping_phone']) : '') : '-';
                                                break;
                                            case 'lading_code':
                                                echo $val ? '<code>' . e($val) . '</code>' : '-';
                                                break;
                                            case 'lading_status':
                                                echo $val ? e($val) : '-';
                                                break;
                                            case 'created_at': case 'updated_at': case 'issued_date': case 'due_date':
                                            case 'payment_date': case 'approved_at': case 'cancelled_at':
                                                echo $val ? '<span class="text-muted">' . time_ago($val) . '</span>' : '-';
                                                break;
                                            default:
                                                echo e($val ?: '-');
                                        endswitch;
                                        ?>
                                        </td>
                                        <?php endforeach; ?>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-soft-secondary" data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
                                                <ul class="dropdown-menu dropdown-menu-end">
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
                                <tr><td colspan="<?= count($displayColumns) + 1 ?>" class="text-center py-4 text-muted"><i class="ri-file-list-3-line fs-1 d-block mb-2"></i>Chưa có đơn hàng</td></tr>
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
    var STORAGE_KEY = 'torycrm_orders_columns';
    var allColumns = <?= json_encode(array_column($displayColumns, 'key')) ?>;
    var defaultVisible = ['col-ordernumber','col-type','col-contactid','col-companyid','col-total','col-status','col-paymentstatus','col-ownerid','col-createdat'];

    function getVisible() {
        try { return JSON.parse(localStorage.getItem(STORAGE_KEY)) || defaultVisible; }
        catch(e) { return defaultVisible; }
    }

    function applyColumns(visible) {
        allColumns.forEach(function(col) {
            var show = visible.includes(col);
            document.querySelectorAll('.' + col).forEach(function(el) { el.style.display = show ? '' : 'none'; });
            var cb = document.getElementById(col);
            if (cb) cb.checked = show;
        });
    }

    applyColumns(getVisible());

    document.querySelectorAll('.column-toggle').forEach(function(cb) {
        cb.addEventListener('change', function() {
            var visible = [];
            document.querySelectorAll('.column-toggle:checked').forEach(function(c) { visible.push(c.dataset.column); });
            if (visible.length === 0) { this.checked = true; return; }
            localStorage.setItem(STORAGE_KEY, JSON.stringify(visible));
            applyColumns(visible);
        });
    });

    document.getElementById('resetColumns')?.addEventListener('click', function() {
        localStorage.removeItem(STORAGE_KEY);
        applyColumns(defaultVisible);
    });
})();
</script>
