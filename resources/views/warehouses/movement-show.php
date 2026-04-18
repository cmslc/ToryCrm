<?php
$pageTitle = 'Phiếu ' . e($movement['code']);
$typeLabels = ['import'=>'Nhập kho','export'=>'Xuất kho','transfer'=>'Chuyển kho','adjustment'=>'Điều chỉnh'];
$typeColors = ['import'=>'success','export'=>'danger','transfer'=>'info','adjustment'=>'warning'];
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0"><?= e($movement['code']) ?> <span class="badge bg-<?= $typeColors[$movement['type']] ?? 'secondary' ?>"><?= $typeLabels[$movement['type']] ?? '' ?></span></h4>
    <a href="<?= url('warehouses/movements') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Chi tiết phiếu</h5></div>
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Sản phẩm</th><th>SKU</th><th>ĐVT</th><th class="text-end">Số lượng</th><th class="text-end">Đơn giá</th><th class="text-end">Thành tiền</th></tr></thead>
                        <tbody>
                        <?php $total = 0; foreach ($items as $item):
                            $subtotal = ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
                            $total += $subtotal;
                        ?>
                        <tr>
                            <td class="fw-medium"><?= e($item['product_name']) ?></td>
                            <td class="text-muted"><?= e($item['sku'] ?? '-') ?></td>
                            <td class="text-muted"><?= e($item['unit'] ?? '') ?></td>
                            <td class="text-end fw-semibold"><?= number_format($item['quantity']) ?></td>
                            <td class="text-end"><?= format_money($item['unit_price'] ?? 0) ?></td>
                            <td class="text-end"><?= format_money($subtotal) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="table-light"><td colspan="5" class="text-end fw-semibold">Tổng cộng</td><td class="text-end fw-semibold"><?= format_money($total) ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Thông tin</h5></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th class="text-muted">Mã phiếu</th><td><?= e($movement['code']) ?></td></tr>
                    <tr><th class="text-muted">Loại</th><td><span class="badge bg-<?= $typeColors[$movement['type']] ?? 'secondary' ?>"><?= $typeLabels[$movement['type']] ?? '' ?></span></td></tr>
                    <tr><th class="text-muted">Kho</th><td><?= e($movement['warehouse_name']) ?></td></tr>
                    <?php if ($movement['to_warehouse_name']): ?><tr><th class="text-muted">Kho đích</th><td><?= e($movement['to_warehouse_name']) ?></td></tr><?php endif; ?>
                    <tr><th class="text-muted">Trạng thái</th><td><span class="badge bg-<?= $movement['status'] === 'confirmed' ? 'success' : 'warning' ?>"><?= $movement['status'] === 'confirmed' ? 'Đã xác nhận' : 'Nháp' ?></span></td></tr>
                    <tr><th class="text-muted">Người tạo</th><td><?= user_avatar($movement['created_by_name'] ?? null) ?></td></tr>
                    <tr><th class="text-muted">Ngày tạo</th><td><?= date('d/m/Y H:i', strtotime($movement['created_at'])) ?></td></tr>
                    <?php if ($movement['note']): ?><tr><th class="text-muted">Ghi chú</th><td><?= e($movement['note']) ?></td></tr><?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>
