<?php $pageTitle = 'Mẫu công việc'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Mẫu công việc</h4>
    <a href="<?= url('tasks') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<div class="row">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h6 class="card-title mb-0">Tạo mẫu mới</h6></div>
            <div class="card-body">
                <form method="POST" action="<?= url('tasks/templates/store') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3"><label class="form-label">Tên mẫu <span class="text-danger">*</span></label><input type="text" class="form-control" name="name" required></div>
                    <div class="mb-3"><label class="form-label">Mô tả</label><textarea class="form-control" name="description" rows="2"></textarea></div>
                    <div class="mb-3"><label class="form-label">Công việc con (mỗi dòng 1 task)</label><textarea class="form-control" name="checklist" rows="4" placeholder="Task con 1&#10;Task con 2&#10;Task con 3"></textarea></div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Ưu tiên</label>
                            <select class="form-select" name="default_priority">
                                <option value="low">Thấp</option><option value="medium" selected>TB</option><option value="high">Cao</option><option value="urgent">Khẩn</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3"><label class="form-label">Hạn (ngày)</label><input type="number" class="form-control" name="due_days" placeholder="VD: 7"></div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Lưu mẫu</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h6 class="card-title mb-0">Danh sách mẫu</h6></div>
            <div class="card-body p-2">
                <?php if (!empty($templates)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-sticky mb-0">
                        <thead class="table-light"><tr><th>Tên mẫu</th><th>Ưu tiên</th><th>Hạn</th><th>Subtasks</th><th>Thao tác</th></tr></thead>
                        <tbody>
                        <?php foreach ($templates as $tpl):
                            $items = json_decode($tpl['checklist'] ?? '[]', true);
                            $pl = ['low'=>'Thấp','medium'=>'TB','high'=>'Cao','urgent'=>'Khẩn'];
                        ?>
                        <tr>
                            <td><strong><?= e($tpl['name']) ?></strong><?php if ($tpl['description']): ?><div class="text-muted fs-12"><?= e(mb_substr($tpl['description'], 0, 50)) ?></div><?php endif; ?></td>
                            <td><span class="badge bg-secondary-subtle text-secondary"><?= $pl[$tpl['default_priority']] ?? '' ?></span></td>
                            <td><?= $tpl['due_days'] ? $tpl['due_days'] . ' ngày' : '-' ?></td>
                            <td><?= count($items) ?></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="<?= url('tasks/templates/' . $tpl['id'] . '/create') ?>" class="btn btn-soft-success"><i class="ri-add-line me-1"></i> Tạo task</a>
                                    <form method="POST" action="<?= url('tasks/templates/' . $tpl['id'] . '/delete') ?>" data-confirm="Xóa mẫu?"><?= csrf_field() ?><button class="btn btn-soft-danger"><i class="ri-delete-bin-line"></i></button></form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4 text-muted"><i class="ri-file-copy-line fs-1 d-block mb-2"></i>Chưa có mẫu nào</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
