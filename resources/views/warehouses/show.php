<?php $pageTitle = e($warehouse['name']); ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <div><h4 class="mb-0"><?= e($warehouse['name']) ?> <?php if ($warehouse['code']): ?><span class="badge bg-secondary"><?= e($warehouse['code']) ?></span><?php endif; ?></h4></div>
    <a href="<?= url('warehouses') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<!-- Low Stock Alerts -->
<?php if (!empty($lowStock)): ?>
<div class="alert alert-danger d-flex align-items-center">
    <i class="ri-alarm-warning-line fs-20 me-2"></i>
    <div><strong><?= count($lowStock) ?> sản phẩm</strong> dưới mức tồn kho tối thiểu:
        <?php foreach ($lowStock as $ls): ?>
            <span class="badge bg-danger ms-1"><?= e($ls['product_name']) ?> (<?= $ls['quantity'] ?>/<?= $ls['min_quantity'] ?>)</span>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Tabs -->
<div class="card">
    <div class="card-header p-0">
        <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabStock">Tồn kho <span class="badge bg-primary-subtle text-primary ms-1"><?= count($stocks) ?></span></a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabHistory">Lịch sử <span class="badge bg-info-subtle text-info ms-1"><?= count($movements) ?></span></a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabInfo">Thông tin</a></li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            <!-- Tồn kho -->
            <div class="tab-pane active" id="tabStock">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Sản phẩm</th><th>SKU</th><th class="text-end">Tồn kho</th><th>ĐVT</th><th class="text-end">Đơn giá</th><th class="text-end">Giá trị</th><th>Tối thiểu</th></tr></thead>
                        <tbody>
                        <?php foreach ($stocks as $s):
                            $isLow = $s['min_quantity'] > 0 && $s['quantity'] <= $s['min_quantity'];
                        ?>
                        <tr class="<?= $isLow ? 'table-danger' : '' ?>">
                            <td class="fw-medium"><?= e($s['product_name']) ?></td>
                            <td class="text-muted"><?= e($s['sku'] ?? '-') ?></td>
                            <td class="text-end fw-semibold <?= $isLow ? 'text-danger' : '' ?>"><?= number_format($s['quantity']) ?></td>
                            <td class="text-muted"><?= e($s['unit'] ?? '') ?></td>
                            <td class="text-end"><?= format_money($s['price'] ?? 0) ?></td>
                            <td class="text-end fw-medium"><?= format_money(($s['quantity'] ?? 0) * ($s['price'] ?? 0)) ?></td>
                            <td><?= $s['min_quantity'] > 0 ? number_format($s['min_quantity']) : '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($stocks)): ?><tr><td colspan="7" class="text-center text-muted py-3">Chưa có hàng tồn</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Lịch sử -->
            <div class="tab-pane" id="tabHistory">
                <?php
                $typeLabels = ['import'=>'Nhập kho','export'=>'Xuất kho','transfer'=>'Chuyển kho','adjustment'=>'Điều chỉnh'];
                $typeColors = ['import'=>'success','export'=>'danger','transfer'=>'info','adjustment'=>'warning'];
                $statusLabels = ['draft'=>'Nháp','confirmed'=>'Đã xác nhận','cancelled'=>'Đã hủy'];
                ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Mã phiếu</th><th>Loại</th><th>Số SP</th><th>Tổng SL</th><th>Người tạo</th><th>Ngày</th></tr></thead>
                        <tbody>
                        <?php foreach ($movements as $m): ?>
                        <tr>
                            <td><a href="<?= url('warehouses/movements/' . $m['id']) ?>" class="fw-medium"><?= e($m['code']) ?></a></td>
                            <td><span class="badge bg-<?= $typeColors[$m['type']] ?? 'secondary' ?>"><?= $typeLabels[$m['type']] ?? $m['type'] ?></span></td>
                            <td><?= $m['item_count'] ?></td>
                            <td class="fw-medium"><?= number_format($m['total_qty'] ?? 0) ?></td>
                            <td><?= user_avatar($m['created_by_name'] ?? null) ?></td>
                            <td class="text-muted"><?= created_ago($m['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($movements)): ?><tr><td colspan="6" class="text-center text-muted py-3">Chưa có lịch sử</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Thông tin -->
            <div class="tab-pane" id="tabInfo">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr><th class="text-muted" width="140">Tên kho</th><td><?= e($warehouse['name']) ?></td></tr>
                            <tr><th class="text-muted">Mã kho</th><td><?= e($warehouse['code'] ?? '-') ?></td></tr>
                            <tr><th class="text-muted">Quản lý</th><td><?= user_avatar($warehouse['manager_name'] ?? null) ?></td></tr>
                            <tr><th class="text-muted">Địa chỉ</th><td><?= e($warehouse['address'] ?? '-') ?></td></tr>
                            <tr><th class="text-muted">Điện thoại</th><td><?= e($warehouse['phone'] ?? '-') ?></td></tr>
                            <?php if ($warehouse['description']): ?><tr><th class="text-muted">Mô tả</th><td><?= e($warehouse['description']) ?></td></tr><?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
