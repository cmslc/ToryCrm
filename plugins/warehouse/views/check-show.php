<?php $pageTitle = 'Kiểm kho ' . e($check['code']); ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Kiểm kho <?= e($check['code']) ?> <span class="badge bg-<?= $check['status'] === 'completed' ? 'success' : 'warning' ?>"><?= $check['status'] === 'completed' ? 'Hoàn thành' : 'Đang kiểm' ?></span></h4>
    <div class="d-flex gap-2">
        <?php if ($check['status'] === 'draft'): ?>
            <form method="POST" action="<?= url('warehouses/checks/' . $check['id'] . '/complete') ?>" data-confirm="Hoàn thành kiểm kho? Chênh lệch sẽ được điều chỉnh tự động.">
                <?= csrf_field() ?><button class="btn btn-success"><i class="ri-check-double-line me-1"></i> Hoàn thành</button>
            </form>
        <?php endif; ?>
        <a href="<?= url('warehouses/checks') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
    </div>
</div>

<div class="card">
    <div class="card-body p-2">
        <form method="POST" action="<?= url('warehouses/checks/' . $check['id'] . '/update') ?>">
            <?= csrf_field() ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>Sản phẩm</th><th>SKU</th><th class="text-end">Tồn hệ thống</th><th class="text-end" style="width:140px">Tồn thực tế</th><th class="text-end">Chênh lệch</th><th>Ghi chú</th></tr></thead>
                    <tbody>
                    <?php foreach ($items as $item):
                        $diff = $item['actual_qty'] - $item['system_qty'];
                    ?>
                    <tr class="<?= $diff != 0 ? ($diff > 0 ? 'table-success' : 'table-danger') : '' ?>">
                        <input type="hidden" name="item_id[]" value="<?= $item['id'] ?>">
                        <td class="fw-medium"><?= e($item['product_name']) ?></td>
                        <td class="text-muted"><?= e($item['sku'] ?? '') ?></td>
                        <td class="text-end"><?= number_format($item['system_qty']) ?></td>
                        <td>
                            <?php if ($check['status'] === 'draft'): ?>
                                <input type="number" name="actual_qty[]" class="form-control text-end" value="<?= $item['actual_qty'] ?>" step="0.01">
                            <?php else: ?>
                                <span class="text-end d-block"><?= number_format($item['actual_qty']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end fw-semibold <?= $diff > 0 ? 'text-success' : ($diff < 0 ? 'text-danger' : '') ?>">
                            <?= $diff > 0 ? '+' : '' ?><?= number_format($diff, 2) ?>
                        </td>
                        <td>
                            <?php if ($check['status'] === 'draft'): ?>
                                <input type="text" name="item_note[]" class="form-control" value="<?= e($item['note'] ?? '') ?>" placeholder="Ghi chú...">
                            <?php else: ?>
                                <?= e($item['note'] ?? '-') ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($check['status'] === 'draft'): ?>
                <div class="p-3 border-top"><button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Lưu</button></div>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-borderless mb-0">
            <tr><th class="text-muted" width="140">Kho</th><td><?= e($check['warehouse_name']) ?></td></tr>
            <tr><th class="text-muted">Người kiểm</th><td><?= user_avatar($check['checked_by_name'] ?? null) ?></td></tr>
            <tr><th class="text-muted">Ngày tạo</th><td><?= date('d/m/Y H:i', strtotime($check['created_at'])) ?></td></tr>
            <?php if ($check['completed_at']): ?><tr><th class="text-muted">Hoàn thành</th><td class="text-success"><?= date('d/m/Y H:i', strtotime($check['completed_at'])) ?></td></tr><?php endif; ?>
        </table>
    </div>
</div>
