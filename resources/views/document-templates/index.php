<?php $pageTitle = 'Mẫu tài liệu'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Mẫu tài liệu</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('settings/document-templates/create?type=quotation') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Tạo mẫu báo giá</a>
        <a href="<?= url('settings/document-templates/create?type=contract') ?>" class="btn btn-success"><i class="ri-add-line me-1"></i> Tạo mẫu hợp đồng</a>
    </div>
</div>

<div class="card">
    <div class="card-header p-2">
        <form method="GET" action="<?= url('settings/document-templates') ?>" class="d-flex align-items-center gap-2">
            <div class="search-box" style="min-width:200px">
                <input type="text" class="form-control" name="search" placeholder="Tìm kiếm..." value="<?= e($filters['search'] ?? '') ?>">
                <i class="ri-search-line search-icon"></i>
            </div>
            <select name="type" class="form-select" style="width:auto" onchange="this.form.submit()">
                <option value="">Tất cả loại</option>
                <option value="quotation" <?= ($filters['type'] ?? '') === 'quotation' ? 'selected' : '' ?>>Báo giá</option>
                <option value="contract" <?= ($filters['type'] ?? '') === 'contract' ? 'selected' : '' ?>>Hợp đồng</option>
            </select>
            <button type="submit" class="btn btn-primary"><i class="ri-search-line"></i></button>
            <?php if (!empty(array_filter($filters ?? []))): ?>
            <a href="<?= url('settings/document-templates') ?>" class="btn btn-soft-danger"><i class="ri-refresh-line"></i></a>
            <?php endif; ?>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Tên mẫu</th>
                    <th style="width:120px">Loại</th>
                    <th style="width:100px" class="text-center">Mặc định</th>
                    <th style="width:100px" class="text-center">Trạng thái</th>
                    <th style="width:150px">Người tạo</th>
                    <th style="width:150px">Ngày tạo</th>
                    <th style="width:120px" class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($templates)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">Chưa có mẫu nào. Tạo mẫu mới để bắt đầu.</td></tr>
                <?php else: ?>
                <?php foreach ($templates as $t): ?>
                <tr>
                    <td>
                        <a href="<?= url('settings/document-templates/' . $t['id'] . '/edit') ?>" class="fw-medium"><?= e($t['name']) ?></a>
                        <?php if ($t['description']): ?>
                        <br><small class="text-muted"><?= e($t['description']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($t['type'] === 'quotation'): ?>
                        <span class="badge bg-primary-subtle text-primary">Báo giá</span>
                        <?php else: ?>
                        <span class="badge bg-success-subtle text-success">Hợp đồng</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($t['is_default']): ?>
                        <span class="badge bg-warning">Mặc định</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($t['is_active']): ?>
                        <span class="badge bg-success">Hoạt động</span>
                        <?php else: ?>
                        <span class="badge bg-secondary">Tắt</span>
                        <?php endif; ?>
                    </td>
                    <td><?= e($t['creator_name'] ?? '-') ?></td>
                    <td><?= !empty($t['created_at']) ? date('d/m/Y H:i', strtotime($t['created_at'])) : '-' ?></td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            <a href="<?= url('settings/document-templates/' . $t['id'] . '/edit') ?>" class="btn btn-soft-primary btn-icon"><i class="ri-pencil-line"></i></a>
                            <form method="POST" action="<?= url('settings/document-templates/' . $t['id'] . '/delete') ?>" class="d-inline" data-confirm="Xóa mẫu này?">
                                <?= csrf_field() ?>
                                <button class="btn btn-soft-danger btn-icon"><i class="ri-delete-bin-line"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
