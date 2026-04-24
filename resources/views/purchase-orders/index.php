<?php $pageTitle = 'Đơn hàng mua'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Đơn hàng mua</h4>
            <div class="d-flex gap-2">
                <a href="<?= url('purchase-orders/export?format=csv') ?>" class="btn btn-soft-info"><i class="ri-download-line me-1"></i> Export</a>
                <a href="<?= url('purchase-orders/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Tạo đơn mua</a>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header p-2">
                <form method="GET" action="<?= url('purchase-orders') ?>" class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="search-box" style="min-width:160px;max-width:200px">
                        <input type="text" class="form-control" name="search" placeholder="Tìm mã đơn, NCC..." value="<?= e($filters['search'] ?? '') ?>">
                        <i class="ri-search-line search-icon"></i>
                    </div>
                    <select name="status" class="form-select" style="width:auto" onchange="this.form.submit()">
                        <option value="">Trạng thái</option>
                        <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Nháp</option>
                        <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                        <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Đã duyệt</option>
                        <option value="receiving" <?= ($filters['status'] ?? '') === 'receiving' ? 'selected' : '' ?>>Đang nhận</option>
                        <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Hoàn thành</option>
                        <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                    </select>
                    <select name="payment_status" class="form-select" style="width:auto" onchange="this.form.submit()">
                        <option value="">Thanh toán</option>
                        <option value="unpaid" <?= ($filters['payment_status'] ?? '') === 'unpaid' ? 'selected' : '' ?>>Chưa TT</option>
                        <option value="partial" <?= ($filters['payment_status'] ?? '') === 'partial' ? 'selected' : '' ?>>Một phần</option>
                        <option value="paid" <?= ($filters['payment_status'] ?? '') === 'paid' ? 'selected' : '' ?>>Đã TT</option>
                    </select>
                    <input type="hidden" name="owner_id" id="poOwnerIdInput" value="<?= e($filters['owner_id'] ?? '') ?>">
                    <div class="position-relative" id="poOwnerDropdown">
                        <div class="form-select d-flex align-items-center gap-2" style="cursor:pointer;width:auto;white-space:nowrap" id="poOwnerBtn">
                            <?php
                            $selectedOwner = null;
                            foreach ($users ?? [] as $u) { if (($filters['owner_id'] ?? '') == $u['id']) $selectedOwner = $u; }
                            ?>
                            <?php if ($selectedOwner): ?>
                                <?php if (!empty($selectedOwner['avatar'])): ?>
                                <img src="<?= asset($selectedOwner['avatar']) ?>" class="rounded-circle" width="20" height="20" style="object-fit:cover">
                                <?php else: ?>
                                <span class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:20px;height:20px;font-size:10px"><?= mb_substr($selectedOwner['name'], 0, 1) ?></span>
                                <?php endif; ?>
                                <span><?= e($selectedOwner['name']) ?></span>
                            <?php else: ?>
                                <span class="text-muted">Người phụ trách</span>
                            <?php endif; ?>
                        </div>
                        <div class="border rounded bg-white shadow" id="poOwnerList" style="position:absolute;z-index:1060;min-width:220px;display:none;top:100%;left:0;margin-top:2px;max-height:280px;overflow-y:auto">
                            <div class="po-owner-opt px-3 py-2 text-primary fw-medium" style="cursor:pointer" data-id="">Tất cả</div>
                            <?php foreach ($users ?? [] as $u): ?>
                            <div class="po-owner-opt d-flex align-items-center gap-2 px-3 py-2 <?= ($filters['owner_id'] ?? '') == $u['id'] ? 'bg-primary bg-opacity-10' : '' ?>" style="cursor:pointer" data-id="<?= $u['id'] ?>">
                                <?php if (!empty($u['avatar'])): ?>
                                <img src="<?= asset($u['avatar']) ?>" class="rounded-circle" width="24" height="24" style="object-fit:cover">
                                <?php else: ?>
                                <span class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:24px;height:24px;font-size:11px"><?= mb_substr($u['name'], 0, 1) ?></span>
                                <?php endif; ?>
                                <span style="font-size:13px"><?= e($u['name']) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="ri-search-line me-1"></i> Tìm</button>
                    <?php if (!empty(array_filter($filters ?? []))): ?>
                    <a href="<?= url('purchase-orders') ?>" class="btn btn-soft-danger btn-icon" title="Xóa lọc"><i class="ri-refresh-line"></i></a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-2">

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mã đơn</th>
                                <th>Nhà cung cấp</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Thanh toán</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($orders['items'])): ?>
                                <?php
                                $sc = ['draft'=>'secondary','pending'=>'warning','approved'=>'primary','receiving'=>'info','completed'=>'success','cancelled'=>'danger'];
                                $sl = ['draft'=>'Nháp','pending'=>'Chờ duyệt','approved'=>'Đã duyệt','receiving'=>'Đang nhận','completed'=>'Hoàn thành','cancelled'=>'Đã hủy'];
                                $pc = ['unpaid'=>'danger','partial'=>'warning','paid'=>'success'];
                                $pl = ['unpaid'=>'Chưa TT','partial'=>'Một phần','paid'=>'Đã TT'];
                                ?>
                                <?php foreach ($orders['items'] as $order): ?>
                                    <tr>
                                        <td><a href="<?= url('purchase-orders/' . $order['id']) ?>" class="fw-medium"><?= e($order['order_code']) ?></a></td>
                                        <td><?= e($order['supplier_name'] ?? '-') ?></td>
                                        <td class="fw-medium"><?= format_money($order['total']) ?></td>
                                        <td><span class="badge bg-<?= $sc[$order['status']] ?? 'secondary' ?>"><?= $sl[$order['status']] ?? '' ?></span></td>
                                        <td><span class="badge bg-<?= $pc[$order['payment_status']] ?? 'secondary' ?>-subtle text-<?= $pc[$order['payment_status']] ?? 'secondary' ?>"><?= $pl[$order['payment_status']] ?? '' ?></span></td>
                                        <td><?= format_date($order['created_at']) ?></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn btn-soft-secondary" data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="<?= url('purchase-orders/' . $order['id']) ?>"><i class="ri-eye-line me-2"></i>Xem</a></li>
                                                    <li><a class="dropdown-item" href="<?= url('purchase-orders/' . $order['id'] . '/edit') ?>"><i class="ri-pencil-line me-2"></i>Sửa</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form method="POST" action="<?= url('purchase-orders/' . $order['id'] . '/delete') ?>" data-confirm="Xác nhận xóa?">
                                                            <?= csrf_field() ?><button class="dropdown-item text-danger"><i class="ri-delete-bin-line me-2"></i>Xóa</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center py-4 text-muted"><i class="ri-file-list-3-line fs-1 d-block mb-2"></i>Chưa có đơn hàng mua</td></tr>
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
                                    <a class="page-link" href="<?= url('purchase-orders?page=' . $i . '&' . http_build_query(array_filter($filters ?? []))) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul></nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>

<script>
(function(){
    var btn = document.getElementById('poOwnerBtn');
    var list = document.getElementById('poOwnerList');
    if (!btn || !list) return;
    btn.addEventListener('click', function(e) { e.stopPropagation(); list.style.display = list.style.display === 'none' ? 'block' : 'none'; });
    document.addEventListener('click', function(e) { if (!document.getElementById('poOwnerDropdown').contains(e.target)) list.style.display = 'none'; });
    list.querySelectorAll('.po-owner-opt').forEach(function(opt) {
        opt.addEventListener('mouseenter', function() { this.style.backgroundColor = '#f3f6f9'; });
        opt.addEventListener('mouseleave', function() { this.style.backgroundColor = ''; });
        opt.addEventListener('click', function() {
            document.getElementById('poOwnerIdInput').value = this.dataset.id;
            this.closest('form').submit();
        });
    });
})();
</script>
