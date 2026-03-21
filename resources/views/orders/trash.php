<?php $pageTitle = 'Đơn hàng đã xóa'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Đơn hàng đã xóa</h4>
    <a href="<?= url('orders') ?>" class="btn btn-soft-primary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Mã đơn</th><th>Khách hàng</th><th>Tổng tiền</th><th>Ngày xóa</th><th>Thao tác</th></tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $o): ?>
                        <tr>
                            <td class="fw-medium"><?= e($o['order_number']) ?></td>
                            <td><?= e(trim(($o['contact_first_name'] ?? '') . ' ' . ($o['contact_last_name'] ?? ''))) ?: '-' ?></td>
                            <td><?= format_money($o['total']) ?></td>
                            <td class="text-muted"><?= $o['deleted_at'] ? format_datetime($o['deleted_at']) : '-' ?></td>
                            <td>
                                <form method="POST" action="<?= url('orders/' . $o['id'] . '/restore') ?>" class="d-inline" data-confirm="Khôi phục đơn hàng này?">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-soft-success"><i class="ri-refresh-line me-1"></i>Khôi phục</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted"><i class="ri-delete-bin-line fs-1 d-block mb-2"></i>Không có đơn hàng đã xóa</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
