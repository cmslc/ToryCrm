<?php $pageTitle = 'Sản phẩm đã xóa'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Sản phẩm đã xóa</h4>
    <a href="<?= url('products') ?>" class="btn btn-soft-primary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-sticky mb-0">
                <thead class="table-light">
                    <tr><th>Sản phẩm</th><th>SKU</th><th>Loại</th><th>Danh mục</th><th>Đơn giá</th><th>Ngày xóa</th><th>Thao tác</th></tr>
                </thead>
                <tbody>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $p): ?>
                        <tr>
                            <td class="fw-medium"><?= e($p['name']) ?></td>
                            <td><code><?= e($p['sku'] ?? '-') ?></code></td>
                            <td><?= $p['type'] === 'service' ? '<span class="badge bg-info-subtle text-info">Dịch vụ</span>' : '<span class="badge bg-primary-subtle text-primary">Sản phẩm</span>' ?></td>
                            <td><?= e($p['category_name'] ?? '-') ?></td>
                            <td><?= format_money($p['price']) ?></td>
                            <td class="text-muted"><?= $p['deleted_at'] ? format_datetime($p['deleted_at']) : '-' ?></td>
                            <td>
                                <form method="POST" action="<?= url('products/' . $p['id'] . '/restore') ?>" class="d-inline" data-confirm="Khôi phục sản phẩm này?">
                                    <?= csrf_field() ?>
                                    <button class="btn btn btn-soft-success"><i class="ri-refresh-line me-1"></i>Khôi phục</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted"><i class="ri-delete-bin-line fs-1 d-block mb-2"></i>Không có sản phẩm đã xóa</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
