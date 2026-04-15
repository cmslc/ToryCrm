<?php $pageTitle = 'Đơn hàng mua'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Đơn hàng mua</h4>
            <div class="d-flex gap-2">
                <a href="<?= url('purchase-orders/export?format=csv') ?>" class="btn btn-soft-info"><i class="ri-download-line me-1"></i> Export</a>
                <a href="<?= url('purchase-orders/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Tạo đơn mua</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?= url('purchase-orders') ?>" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" placeholder="Tìm mã đơn, nhà cung cấp..." value="<?= e($filters['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">Trạng thái</option>
                            <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Nháp</option>
                            <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                            <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Đã duyệt</option>
                            <option value="receiving" <?= ($filters['status'] ?? '') === 'receiving' ? 'selected' : '' ?>>Đang nhận</option>
                            <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Hoàn thành</option>
                            <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="payment_status" class="form-select">
                            <option value="">Thanh toán</option>
                            <option value="unpaid" <?= ($filters['payment_status'] ?? '') === 'unpaid' ? 'selected' : '' ?>>Chưa TT</option>
                            <option value="partial" <?= ($filters['payment_status'] ?? '') === 'partial' ? 'selected' : '' ?>>Một phần</option>
                            <option value="paid" <?= ($filters['payment_status'] ?? '') === 'paid' ? 'selected' : '' ?>>Đã TT</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary"><i class="ri-search-line"></i> Lọc</button>
                        <a href="<?= url('purchase-orders') ?>" class="btn btn-soft-secondary">Xóa lọc</a>
                    </div>
                </form>

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
