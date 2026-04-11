<?php
$pageTitle = 'Đơn hàng Logistics';
$stLabels = ['pending'=>'Chờ','processing'=>'Đang xử lý','partial'=>'Nhận 1 phần','completed'=>'Hoàn thành','cancelled'=>'Đã hủy'];
$stColors = ['pending'=>'secondary','processing'=>'primary','partial'=>'warning','completed'=>'success','cancelled'=>'danger'];
$currentType = $filters['type'] ?? '';
$currentStatus = $filters['status'] ?? '';
$currentSearch = $filters['search'] ?? '';
$currentDateFrom = $filters['date_from'] ?? '';
$currentDateTo = $filters['date_to'] ?? '';

// Status counts
$statusCounts = [];
try {
    $statusCounts = \Core\Database::fetchAll("SELECT status, COUNT(*) as count FROM logistics_orders WHERE tenant_id = ? GROUP BY status", [$_SESSION['tenant_id'] ?? 1]);
} catch (\Exception $e) {}
$countMap = []; $totalAll = 0;
foreach ($statusCounts as $sc) { $countMap[$sc['status']] = $sc['count']; $totalAll += $sc['count']; }
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Đơn hàng</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('logistics') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Dashboard</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOrderModal"><i class="ri-add-line me-1"></i> Tạo đơn</button>
    </div>
</div>

<!-- Filter Row -->
<div class="card mb-2">
    <div class="card-header p-2">
        <form method="GET" action="<?= url('logistics/orders') ?>" class="d-flex align-items-center gap-2 flex-wrap">
            <div class="search-box" style="min-width:180px;max-width:280px">
                <input type="text" class="form-control" name="search" placeholder="Mã đơn, KH, SĐT, SP..." value="<?= e($currentSearch) ?>">
                <i class="ri-search-line search-icon"></i>
            </div>
            <select name="type" class="form-select" style="width:auto;min-width:120px" onchange="this.form.submit()">
                <option value="">Tất cả loại</option>
                <option value="retail" <?= $currentType === 'retail' ? 'selected' : '' ?>>Hàng lẻ</option>
                <option value="wholesale" <?= $currentType === 'wholesale' ? 'selected' : '' ?>>Hàng lô/sỉ</option>
            </select>
            <input type="date" name="date_from" class="form-control" style="width:auto" value="<?= e($currentDateFrom) ?>" title="Từ ngày">
            <input type="date" name="date_to" class="form-control" style="width:auto" value="<?= e($currentDateTo) ?>" title="Đến ngày">
            <input type="hidden" name="status" value="<?= e($currentStatus) ?>">
            <button type="submit" class="btn btn-primary"><i class="ri-search-line me-1"></i> Tìm</button>
            <?php if ($currentSearch || $currentType || $currentStatus || $currentDateFrom || $currentDateTo): ?>
                <a href="<?= url('logistics/orders') ?>" class="btn btn-soft-danger"><i class="ri-refresh-line me-1"></i> Xóa lọc</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Status Tabs -->
<div class="card mb-2">
    <div class="card-header p-2">
        <ul class="nav nav-custom nav-custom-light mb-0">
            <li class="nav-item"><a class="nav-link <?= !$currentStatus ? 'active' : '' ?>" href="<?= url('logistics/orders?' . http_build_query(array_filter(['type'=>$currentType,'search'=>$currentSearch]))) ?>">Tất cả <span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1"><?= $totalAll ?></span></a></li>
            <?php foreach ($stLabels as $k => $v):
                $c = $countMap[$k] ?? 0;
                if ($c == 0 && $currentStatus !== $k) continue;
            ?>
            <li class="nav-item"><a class="nav-link <?= $currentStatus === $k ? 'active' : '' ?>" href="<?= url('logistics/orders?' . http_build_query(array_filter(['status'=>$k,'type'=>$currentType,'search'=>$currentSearch]))) ?>"><?= $v ?> <span class="badge bg-<?= $stColors[$k] ?>-subtle text-<?= $stColors[$k] ?> rounded-pill ms-1"><?= $c ?></span></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<!-- Bulk Action Bar -->
<?php
$existingShipments = \Core\Database::fetchAll("SELECT id, shipment_code, origin, destination, total_packages, total_weight, total_cbm FROM logistics_shipments WHERE tenant_id = ? AND status = 'preparing' ORDER BY created_at DESC", [$_SESSION['tenant_id'] ?? 1]);
?>
<div class="card mb-2 d-none" id="bulkBar" style="position:sticky;top:70px;z-index:100">
    <div class="card-body py-2">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <span class="fw-medium"><span id="bulkCount">0</span> đơn đã chọn</span>
            <span class="text-muted">|</span>
            <span class="text-muted fs-12"><i class="ri-box-3-line me-1"></i><span id="bulkPkgs">0</span> kiện</span>
            <span class="text-muted fs-12"><i class="ri-scales-line me-1"></i><span id="bulkWeight">0</span> kg</span>
            <span class="text-muted fs-12"><i class="ri-ruler-line me-1"></i><span id="bulkCbm">0</span> m³</span>
            <span class="text-muted">|</span>
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#shipModal"><i class="ri-truck-line me-1"></i> Xếp xe vận chuyển</button>
        </div>
    </div>
</div>

<!-- Ship Modal (ToryCMS style with tabs) -->
<div class="modal fade" id="shipModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning-subtle">
                <h5 class="modal-title"><i class="ri-truck-line me-2"></i> Xếp xe vận chuyển</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Summary -->
                <div class="alert alert-info py-2 mb-3">
                    <i class="ri-information-line me-1"></i> Đã chọn <strong id="shipSummary"></strong>
                </div>

                <!-- Tabs: Chuyến có sẵn / Tạo chuyến mới -->
                <ul class="nav nav-tabs" role="tablist">
                    <?php if (!empty($existingShipments)): ?>
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabExisting">Chuyến có sẵn</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabNew">Tạo chuyến mới</a></li>
                    <?php else: ?>
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabNew">Tạo chuyến mới</a></li>
                    <?php endif; ?>
                </ul>

                <div class="tab-content pt-3">
                    <!-- Tab: Chuyến có sẵn -->
                    <?php if (!empty($existingShipments)): ?>
                    <div class="tab-pane active" id="tabExisting">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light"><tr><th></th><th>Mã chuyến</th><th>Tuyến</th><th>Kiện hiện tại</th><th>Cân</th><th>Khối</th></tr></thead>
                                <tbody>
                                <?php foreach ($existingShipments as $es): ?>
                                <tr>
                                    <td><input type="radio" class="form-check-input" name="existing_shipment" value="<?= $es['id'] ?>" data-code="<?= e($es['shipment_code']) ?>"></td>
                                    <td class="fw-medium"><?= e($es['shipment_code']) ?></td>
                                    <td><?= e($es['origin']) ?> → <?= e($es['destination']) ?></td>
                                    <td><?= $es['total_packages'] ?></td>
                                    <td><?= $es['total_weight'] > 0 ? rtrim(rtrim(number_format($es['total_weight'], 2), '0'), '.') . ' kg' : '-' ?></td>
                                    <td><?= $es['total_cbm'] > 0 ? rtrim(rtrim(number_format($es['total_cbm'], 4), '0'), '.') . ' m³' : '-' ?></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <form method="POST" action="" id="existingShipForm" class="mt-3 text-end">
                            <?= csrf_field() ?>
                            <div id="existingShipOrderIds"></div>
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Đóng</button>
                            <button type="submit" class="btn btn-warning"><i class="ri-truck-line me-1"></i> Xếp xe</button>
                        </form>
                    </div>
                    <?php endif; ?>

                    <!-- Tab: Tạo chuyến mới -->
                    <div class="tab-pane <?= empty($existingShipments) ? 'active' : '' ?>" id="tabNew">
                        <form method="POST" action="<?= url('logistics/shipments/create-from-orders') ?>" id="shipForm">
                            <?= csrf_field() ?>
                            <div id="shipOrderIds"></div>
                            <div class="row">
                                <div class="col-4 mb-3"><label class="form-label">Biển số xe <span class="text-danger">*</span></label><input type="text" class="form-control" name="vehicle_info" placeholder="VD: 29C-12345" required></div>
                                <div class="col-4 mb-3"><label class="form-label">Tên tài xế</label><input type="text" class="form-control" name="driver_name" placeholder="Tên tài xế"></div>
                                <div class="col-4 mb-3"><label class="form-label">SĐT tài xế</label><input type="text" class="form-control" name="driver_phone" placeholder="SĐT"></div>
                            </div>
                            <div class="row">
                                <div class="col-6 mb-3"><label class="form-label">Tuyến đường</label><input type="text" class="form-control" name="route_name" value="Kho Trung Quốc - Cửa khẩu"></div>
                                <div class="col-6 mb-3"><label class="form-label">Trọng tải tối đa (kg)</label><input type="number" class="form-control" name="max_weight" step="0.1"></div>
                            </div>
                            <div class="row">
                                <div class="col-6 mb-3"><label class="form-label">Chi phí vận chuyển</label><input type="number" class="form-control" name="shipping_cost" step="1000" min="0"></div>
                                <div class="col-6 mb-3"><label class="form-label">Ghi chú</label><input type="text" class="form-control" name="note"></div>
                            </div>
                            <input type="hidden" name="origin" value="CN">
                            <input type="hidden" name="destination" value="VN">
                            <div class="text-end">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Đóng</button>
                                <button type="submit" class="btn btn-warning"><i class="ri-truck-line me-1"></i> Xếp xe</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th style="width:30px"><input type="checkbox" class="form-check-input" id="checkAll"></th><th>Mã đơn</th><th>Ảnh</th><th>Loại</th><th>Khách hàng</th><th>Sản phẩm</th><th>Kiện</th><th>Số khối</th><th>Đã nhận</th><th>Tổng tiền</th><th>COD</th><th>Trạng thái</th><th>Ngày tạo</th><th style="width:60px"></th></tr></thead>
                <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td><?php if ($o['type'] === 'wholesale'): ?><input type="checkbox" class="form-check-input row-check" value="<?= $o['id'] ?>" data-pkgs="<?= $o['total_packages'] ?>" data-weight="<?= $o['total_weight'] ?>" data-cbm="<?= $o['total_cbm'] ?>"><?php endif; ?></td>
                    <td><a href="<?= url('logistics/orders/' . $o['id']) ?>" class="fw-medium"><?= e($o['order_code']) ?></a></td>
                    <td>
                        <?php
                        $oImgs = json_decode($o['images'] ?? '[]', true) ?: [];
                        if (!empty($oImgs)):
                            $firstImg = $oImgs[0];
                        ?>
                            <a href="javascript:void(0)" onclick="showImagePopup(<?= htmlspecialchars(json_encode(array_map(fn($img) => url('uploads/logistics/' . $img), $oImgs))) ?>)">
                                <img src="<?= url('uploads/logistics/' . $firstImg) ?>" class="rounded" style="width:40px;height:40px;object-fit:cover;cursor:pointer">
                                <?php if (count($oImgs) > 1): ?><span class="text-muted fs-11 ms-1">+<?= count($oImgs) - 1 ?></span><?php endif; ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge bg-<?= $o['type'] === 'wholesale' ? 'success' : 'info' ?>-subtle text-<?= $o['type'] === 'wholesale' ? 'success' : 'info' ?>"><?= $o['type'] === 'wholesale' ? 'Sỉ' : 'Lẻ' ?></span></td>
                    <td><?= e($o['customer_name'] ?? '-') ?><?= $o['customer_phone'] ? '<div class="text-muted fs-11">' . e($o['customer_phone']) . '</div>' : '' ?></td>
                    <td class="fs-12"><?= e(mb_substr($o['product_name'] ?? '-', 0, 30)) ?></td>
                    <td class="fw-medium"><?= $o['total_packages'] ?></td>
                    <td class="text-muted fs-12">
                        <?php if ($o['total_weight'] > 0): ?><?= rtrim(rtrim(number_format($o['total_weight'], 2), '0'), '.') ?> kg<?php endif; ?>
                        <?php if ($o['total_cbm'] > 0): ?><div><?= rtrim(rtrim(number_format($o['total_cbm'], 4), '0'), '.') ?> m³</div><?php endif; ?>
                        <?php if (!$o['total_weight'] && !$o['total_cbm']): ?>-<?php endif; ?>
                    </td>
                    <td>
                        <?php if ($o['type'] === 'wholesale' && $o['total_packages'] > 0): ?>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:6px;min-width:50px"><div class="progress-bar bg-success" style="width:<?= min(100, round(($o['received_packages'] / $o['total_packages']) * 100)) ?>%"></div></div>
                                <span class="fs-12"><?= $o['received_packages'] ?>/<?= $o['total_packages'] ?></span>
                            </div>
                        <?php else: ?>
                            <?= $o['received_packages'] ?>
                        <?php endif; ?>
                    </td>
                    <td><?= $o['total_amount'] > 0 ? format_money($o['total_amount']) : '-' ?></td>
                    <td><?= $o['cod_amount'] > 0 ? format_money($o['cod_amount']) : '-' ?></td>
                    <td><span class="badge bg-<?= $stColors[$o['status']] ?? 'secondary' ?>-subtle text-<?= $stColors[$o['status']] ?? 'secondary' ?>"><?= $stLabels[$o['status']] ?? $o['status'] ?></span></td>
                    <td class="text-muted fs-12"><?= created_ago($o['created_at']) ?></td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-soft-secondary btn-icon" data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= url('logistics/orders/' . $o['id']) ?>"><i class="ri-eye-line me-2"></i>Chi tiết</a></li>
                                <li><a class="dropdown-item" href="<?= url('logistics/orders/' . $o['id']) ?>#editOrderModal" onclick="setTimeout(function(){var m=document.getElementById('editOrderModal');if(m)new bootstrap.Modal(m).show()},500)"><i class="ri-pencil-line me-2"></i>Sửa</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><form method="POST" action="<?= url('logistics/orders/' . $o['id'] . '/delete') ?>" data-confirm="Xóa đơn <?= e($o['order_code']) ?>?"><?= csrf_field() ?><button class="dropdown-item text-danger"><i class="ri-delete-bin-line me-2"></i>Xóa</button></form></li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?><tr><td colspan="14" class="text-center text-muted py-4">Chưa có đơn hàng</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Order Modal -->
<div class="modal fade" id="addOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('logistics/orders/create') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header"><h5 class="modal-title">Tạo đơn hàng</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-6 mb-3"><label class="form-label">Mã đơn</label><input type="text" class="form-control" name="order_code" placeholder="Tự tạo"></div>
                        <div class="col-6 mb-3"><label class="form-label">Loại <span class="text-danger">*</span></label>
                            <select name="type" class="form-select"><option value="retail">Hàng lẻ</option><option value="wholesale">Hàng lô/sỉ</option></select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Khách hàng</label>
                            <div class="position-relative">
                                <input type="text" class="form-control" id="logCustSearch" placeholder="Gõ tên/SĐT để tìm..." autocomplete="off">
                                <input type="hidden" name="customer_id" id="logCustId">
                                <input type="hidden" name="customer_name" id="logCustName">
                                <input type="hidden" name="customer_phone" id="logCustPhone">
                                <div id="logCustDropdown" class="dropdown-menu w-100" style="display:none;max-height:200px;overflow-y:auto;position:absolute;z-index:1060"></div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">SĐT</label>
                            <input type="text" class="form-control" id="logCustPhoneInput" name="customer_phone_display" placeholder="Tự điền hoặc nhập tay">
                        </div>
                    </div>
                    <div class="mb-3"><label class="form-label">Sản phẩm</label><input type="text" class="form-control" name="product_name"></div>
                    <div class="row">
                        <div class="col-3 mb-3"><label class="form-label">Tổng kiện</label><input type="number" class="form-control" name="total_packages" min="0" value="1"></div>
                        <div class="col-3 mb-3"><label class="form-label">Cân nặng (kg)</label><input type="number" class="form-control" name="total_weight" min="0" step="0.01"></div>
                        <div class="col-3 mb-3"><label class="form-label">Số khối (m³)</label><input type="number" class="form-control" name="total_cbm" min="0" step="0.0001"></div>
                        <div class="col-4 mb-3"><label class="form-label">Tổng tiền</label><input type="number" class="form-control" name="total_amount" min="0" step="1000"></div>
                        <div class="col-4 mb-3"><label class="form-label">COD thu</label><input type="number" class="form-control" name="cod_amount" min="0" step="1000"></div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3"><label class="form-label">Thanh toán</label>
                            <select name="payment_method" class="form-select"><option value="">Chưa chọn</option><option value="cod">COD</option><option value="transfer">Chuyển khoản</option><option value="cash">Tiền mặt</option><option value="prepaid">Đã thanh toán</option></select>
                        </div>
                    </div>
                    <div class="mb-3"><label class="form-label">Ghi chú</label><textarea class="form-control" name="note" rows="2"></textarea></div>
                    <div class="mb-3"><label class="form-label">Ảnh đơn hàng</label><input type="file" name="images[]" class="form-control" accept="image/*" multiple></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-primary"><i class="ri-check-line me-1"></i> Tạo</button></div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    var input = document.getElementById('logCustSearch');
    var dd = document.getElementById('logCustDropdown');
    var custId = document.getElementById('logCustId');
    var custName = document.getElementById('logCustName');
    var custPhone = document.getElementById('logCustPhone');
    var phoneInput = document.getElementById('logCustPhoneInput');
    var timer = null;

    if (!input) return;

    input.addEventListener('input', function() {
        clearTimeout(timer);
        var q = this.value.trim();
        if (q.length < 2) { dd.style.display = 'none'; return; }

        timer = setTimeout(function() {
            fetch('<?= url("contacts") ?>?search=' + encodeURIComponent(q) + '&_ajax=1')
                .then(function(r) { return r.text(); })
                .catch(function() { return ''; })
                .then(function() {
                    // Use direct DB search via simple API
                    var xhr = new XMLHttpRequest();
                    xhr.open('GET', '<?= url("api-internal/users") ?>?search=' + encodeURIComponent(q));
                    xhr.onload = function() {
                        // Fallback: search contacts via PHP inline
                    };
                });

            // Simple approach: search contacts inline
            <?php
            $allContacts = \Core\Database::fetchAll(
                "SELECT id, first_name, last_name, phone, email FROM contacts WHERE tenant_id = ? AND is_deleted = 0 ORDER BY first_name LIMIT 200",
                [$_SESSION['tenant_id'] ?? 1]
            );
            ?>
            var contacts = <?= json_encode(array_map(fn($c) => [
                'id' => $c['id'],
                'name' => trim($c['first_name'] . ' ' . ($c['last_name'] ?? '')),
                'phone' => $c['phone'] ?? '',
                'email' => $c['email'] ?? '',
            ], $allContacts)) ?>;

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

            if (q.length >= 2) {
                html += '<a href="#" class="dropdown-item py-2 text-primary border-top" data-id="" data-name="' + q + '" data-phone="">'
                    + '<i class="ri-add-line me-1"></i> Tạo mới: <strong>' + q + '</strong>'
                    + '</a>';
            }

            dd.innerHTML = html;
            dd.style.display = html ? 'block' : 'none';

            dd.querySelectorAll('[data-name]').forEach(function(a) {
                a.onclick = function(e) {
                    e.preventDefault();
                    var name = this.dataset.name;
                    var phone = this.dataset.phone;
                    var id = this.dataset.id;

                    input.value = name;
                    custId.value = id;
                    custName.value = name;
                    custPhone.value = phone;
                    phoneInput.value = phone;
                    dd.style.display = 'none';
                };
            });
        }, 200);
    });

    input.addEventListener('blur', function() {
        setTimeout(function() { dd.style.display = 'none'; }, 200);
        // If typed but not selected, use as new name
        if (input.value && !custName.value) {
            custName.value = input.value;
        }
    });

    // Sync phone input back
    phoneInput?.addEventListener('input', function() {
        custPhone.value = this.value;
    });
})();
</script>

<!-- Image Popup Modal -->
<div class="modal fade" id="imagePopup" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-body p-0 text-center position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" style="z-index:10"></button>
                <button type="button" class="btn btn-light rounded-circle position-absolute start-0 top-50 translate-middle-y ms-2" id="imgPrev" style="z-index:10"><i class="ri-arrow-left-s-line"></i></button>
                <button type="button" class="btn btn-light rounded-circle position-absolute end-0 top-50 translate-middle-y me-2" id="imgNext" style="z-index:10"><i class="ri-arrow-right-s-line"></i></button>
                <img id="popupImage" src="" class="rounded" style="max-height:80vh;max-width:100%">
                <div class="text-white mt-2" id="popupCounter"></div>
            </div>
        </div>
    </div>
</div>

<script>
// Bulk selection + ship modal
document.addEventListener('DOMContentLoaded', function() {
    var checkAll = document.getElementById('checkAll');
    var bulkBar = document.getElementById('bulkBar');
    if (!bulkBar) return;
    var selectedIds = [];

    function updateBulk() {
        var checked = document.querySelectorAll('.row-check:checked');
        selectedIds = [];
        if (checked.length > 0) {
            bulkBar.classList.remove('d-none');
            document.getElementById('bulkCount').textContent = checked.length;

            var totalPkgs = 0, totalWeight = 0, totalCbm = 0;
            var idsHtml = '';
            checked.forEach(function(cb) {
                selectedIds.push(cb.value);
                totalPkgs += parseInt(cb.dataset.pkgs || 0);
                totalWeight += parseFloat(cb.dataset.weight || 0);
                totalCbm += parseFloat(cb.dataset.cbm || 0);
                idsHtml += '<input type="hidden" name="order_ids[]" value="' + cb.value + '">';
            });
            document.getElementById('bulkPkgs').textContent = totalPkgs;
            document.getElementById('bulkWeight').textContent = parseFloat(totalWeight.toFixed(2));
            document.getElementById('bulkCbm').textContent = parseFloat(totalCbm.toFixed(4));
            document.getElementById('shipOrderIds').innerHTML = idsHtml;
            document.getElementById('shipSummary').textContent = checked.length + ' đơn hàng — Tổng cân: ' + parseFloat(totalWeight.toFixed(2)) + ' kg — Tổng khối: ' + parseFloat(totalCbm.toFixed(4)) + ' m³';
        } else {
            bulkBar.classList.add('d-none');
        }
    }

    if (checkAll) {
        checkAll.addEventListener('change', function() {
            document.querySelectorAll('.row-check').forEach(function(cb) { cb.checked = checkAll.checked; });
            updateBulk();
        });
    }
    document.querySelectorAll('.row-check').forEach(function(cb) {
        cb.addEventListener('change', updateBulk);
        cb.addEventListener('click', updateBulk);
    });

    // Add to existing shipment
    // Update existing ship form when radio changes or submits
    document.querySelectorAll('input[name="existing_shipment"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            document.getElementById('existingShipForm').action = '<?= url("logistics/shipments") ?>/' + this.value + '/add-orders';
        });
    });

    document.getElementById('existingShipForm')?.addEventListener('submit', function(e) {
        var selected = document.querySelector('input[name="existing_shipment"]:checked');
        if (!selected) { e.preventDefault(); alert('Chọn một chuyến xe'); return; }
        if (selectedIds.length === 0) { e.preventDefault(); alert('Chưa chọn đơn hàng'); return; }
        this.action = '<?= url("logistics/shipments") ?>/' + selected.value + '/add-orders';
        // Add order IDs
        var idsDiv = document.getElementById('existingShipOrderIds');
        idsDiv.innerHTML = '';
        selectedIds.forEach(function(id) {
            var inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'order_ids[]'; inp.value = id;
            idsDiv.appendChild(inp);
        });
    });
    });
});

var popupImages = [], popupIndex = 0;
function showImagePopup(images, startIndex) {
    popupImages = images;
    popupIndex = startIndex || 0;
    updatePopupImage();
    new bootstrap.Modal(document.getElementById('imagePopup')).show();
}
function updatePopupImage() {
    document.getElementById('popupImage').src = popupImages[popupIndex];
    document.getElementById('popupCounter').textContent = (popupIndex + 1) + ' / ' + popupImages.length;
    document.getElementById('imgPrev').style.display = popupImages.length > 1 ? '' : 'none';
    document.getElementById('imgNext').style.display = popupImages.length > 1 ? '' : 'none';
}
document.getElementById('imgPrev')?.addEventListener('click', function() { popupIndex = (popupIndex - 1 + popupImages.length) % popupImages.length; updatePopupImage(); });
document.getElementById('imgNext')?.addEventListener('click', function() { popupIndex = (popupIndex + 1) % popupImages.length; updatePopupImage(); });
document.getElementById('imagePopup')?.addEventListener('keydown', function(e) { if (e.key === 'ArrowLeft') document.getElementById('imgPrev').click(); if (e.key === 'ArrowRight') document.getElementById('imgNext').click(); });
</script>
