<?php $pageTitle = 'Doanh nghiệp đã xóa'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Doanh nghiệp đã xóa</h4>
    <a href="<?= url('companies') ?>" class="btn btn-soft-primary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Doanh nghiệp</th>
                        <th>Ngành nghề</th>
                        <th>Email</th>
                        <th>Điện thoại</th>
                        <th>Phụ trách</th>
                        <th>Ngày xóa</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($companies)): ?>
                        <?php foreach ($companies as $c): ?>
                        <tr>
                            <td class="fw-medium"><?= e($c['name']) ?></td>
                            <td><?= e($c['industry'] ?? '-') ?></td>
                            <td><?= e($c['email'] ?? '-') ?></td>
                            <td><?= e($c['phone'] ?? '-') ?></td>
                            <td><?= e($c['owner_name'] ?? '-') ?></td>
                            <td class="text-muted"><?= $c['deleted_at'] ? format_datetime($c['deleted_at']) : '-' ?></td>
                            <td>
                                <form method="POST" action="<?= url('companies/' . $c['id'] . '/restore') ?>" class="d-inline" data-confirm="Khôi phục doanh nghiệp này?">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-soft-success"><i class="ri-refresh-line me-1"></i>Khôi phục</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">
                            <i class="ri-delete-bin-line fs-1 d-block mb-2"></i>Không có doanh nghiệp đã xóa
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
