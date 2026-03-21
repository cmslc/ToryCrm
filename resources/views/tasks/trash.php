<?php $pageTitle = 'Công việc đã hủy'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Công việc đã hủy / xóa</h4>
    <a href="<?= url('tasks') ?>" class="btn btn-soft-primary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Công việc</th><th>Phụ trách</th><th>Ưu tiên</th><th>Ngày hủy</th><th>Thao tác</th></tr>
                </thead>
                <tbody>
                    <?php if (!empty($tasks)): ?>
                        <?php foreach ($tasks as $t): ?>
                        <tr>
                            <td class="fw-medium"><?= e($t['title']) ?></td>
                            <td><?= e($t['assigned_name'] ?? '-') ?></td>
                            <td>
                                <?php $pc = ['low'=>'info','medium'=>'warning','high'=>'danger','urgent'=>'danger']; ?>
                                <span class="badge bg-<?= $pc[$t['priority']] ?? 'secondary' ?>-subtle text-<?= $pc[$t['priority']] ?? 'secondary' ?>"><?= ucfirst($t['priority'] ?? '') ?></span>
                            </td>
                            <td class="text-muted"><?= $t['deleted_at'] ? format_datetime($t['deleted_at']) : '-' ?></td>
                            <td>
                                <form method="POST" action="<?= url('tasks/' . $t['id'] . '/restore') ?>" class="d-inline" data-confirm="Khôi phục công việc này?">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-soft-success"><i class="ri-refresh-line me-1"></i>Khôi phục</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted"><i class="ri-delete-bin-line fs-1 d-block mb-2"></i>Không có công việc đã hủy</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
