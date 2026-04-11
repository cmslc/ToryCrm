<?php $pageTitle = 'Báo cáo kho'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Báo cáo tồn kho</h4>
    <a href="<?= url('warehouses') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<!-- Stats -->
<div class="row">
    <div class="col-md-4"><div class="card card-animate"><div class="card-body"><p class="text-muted mb-0 text-uppercase fs-11">Sản phẩm có hàng</p><h4 class="mb-0"><?= number_format($totalProducts) ?></h4></div></div></div>
    <div class="col-md-4"><div class="card card-animate"><div class="card-body"><p class="text-muted mb-0 text-uppercase fs-11">Giá trị tồn kho</p><h4 class="mb-0 text-success"><?= format_money($totalValue) ?></h4></div></div></div>
    <div class="col-md-4"><div class="card card-animate"><div class="card-body"><p class="text-muted mb-0 text-uppercase fs-11">Cảnh báo tồn thấp</p><h4 class="mb-0 <?= $lowStockCount > 0 ? 'text-danger' : '' ?>"><?= $lowStockCount ?></h4></div></div></div>
</div>

<!-- Filter -->
<div class="card mb-2">
    <div class="card-header p-2">
        <form method="GET" action="<?= url('warehouses/report') ?>" class="d-flex align-items-center gap-2">
            <select name="warehouse_id" class="form-select" style="width:auto;min-width:180px" onchange="this.form.submit()">
                <option value="">Tất cả kho</option>
                <?php foreach ($warehouses as $wh): ?><option value="<?= $wh['id'] ?>" <?= ($filters['warehouse_id'] ?? '') == $wh['id'] ? 'selected' : '' ?>><?= e($wh['name']) ?></option><?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Sản phẩm</th><th>SKU</th><th>ĐVT</th><th class="text-end">Tồn kho</th><th class="text-end">Đơn giá</th><th class="text-end">Giá trị</th><th>Tối thiểu</th><th>Trạng thái</th></tr></thead>
                <tbody>
                <?php foreach ($stockReport as $r):
                    $isLow = $r['min_qty'] > 0 && $r['total_qty'] <= $r['min_qty'];
                    $isEmpty = $r['total_qty'] <= 0;
                ?>
                <tr class="<?= $isLow ? 'table-danger' : ($isEmpty ? 'text-muted' : '') ?>">
                    <td class="fw-medium"><?= e($r['name']) ?></td>
                    <td class="text-muted"><?= e($r['sku'] ?? '-') ?></td>
                    <td class="text-muted"><?= e($r['unit'] ?? '') ?></td>
                    <td class="text-end fw-semibold"><?= number_format($r['total_qty']) ?></td>
                    <td class="text-end"><?= format_money($r['price'] ?? 0) ?></td>
                    <td class="text-end fw-medium"><?= format_money($r['total_value']) ?></td>
                    <td><?= $r['min_qty'] > 0 ? number_format($r['min_qty']) : '-' ?></td>
                    <td>
                        <?php if ($isLow): ?><span class="badge bg-danger">Thấp</span>
                        <?php elseif ($isEmpty): ?><span class="badge bg-secondary">Hết hàng</span>
                        <?php else: ?><span class="badge bg-success">Đủ</span><?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
