<?php $pageTitle = 'Khách hàng đã xóa'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Khách hàng đã xóa</h4>
    <a href="<?= url('contacts') ?>" class="btn btn-soft-primary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-sticky mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Khách hàng</th>
                        <th>Email</th>
                        <th>Điện thoại</th>
                        <th>Công ty</th>
                        <th>Ngày xóa</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($contacts)): ?>
                        <?php foreach ($contacts as $c): ?>
                        <tr>
                            <td class="fw-medium"><?= e($c['first_name'] . ' ' . ($c['last_name'] ?? '')) ?></td>
                            <td><?= e($c['email'] ?? '-') ?></td>
                            <td><?= e($c['phone'] ?? '-') ?></td>
                            <td><?= e($c['company_name'] ?? '-') ?></td>
                            <td class="text-muted"><?= $c['deleted_at'] ? format_datetime($c['deleted_at']) : '-' ?></td>
                            <td>
                                <form method="POST" action="<?= url('contacts/' . $c['id'] . '/restore') ?>" class="d-inline" data-confirm="Khôi phục khách hàng này?">
                                    <?= csrf_field() ?>
                                    <button class="btn btn btn-soft-success"><i class="ri-refresh-line me-1"></i>Khôi phục</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">
                            <i class="ri-delete-bin-line fs-1 d-block mb-2"></i>Không có khách hàng đã xóa
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
