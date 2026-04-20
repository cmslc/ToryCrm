<?php
$pageTitle = 'Mẫu tài liệu';
$activeTab = ($filters['type'] ?? '') ?: 'quotation';
$quotationTemplates = array_filter($templates ?? [], fn($t) => $t['type'] === 'quotation');
$contractTemplates = array_filter($templates ?? [], fn($t) => $t['type'] === 'contract');
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Mẫu tài liệu</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('settings/document-templates/create?type=quotation') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Tạo mẫu báo giá</a>
        <a href="<?= url('settings/document-templates/create?type=contract') ?>" class="btn btn-success"><i class="ri-add-line me-1"></i> Tạo mẫu hợp đồng</a>
    </div>
</div>

<div class="card">
    <div class="card-header p-0">
        <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'quotation' ? 'active' : '' ?>" data-bs-toggle="tab" href="#tab-quotation" role="tab">
                    <i class="ri-file-list-2-line me-1"></i> Mẫu báo giá <span class="badge bg-primary-subtle text-primary ms-1"><?= count($quotationTemplates) ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'contract' ? 'active' : '' ?>" data-bs-toggle="tab" href="#tab-contract" role="tab">
                    <i class="ri-file-text-line me-1"></i> Mẫu hợp đồng <span class="badge bg-success-subtle text-success ms-1"><?= count($contractTemplates) ?></span>
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
        <div class="tab-content">
            <!-- Tab Báo giá -->
            <div class="tab-pane <?= $activeTab === 'quotation' ? 'active show' : '' ?>" id="tab-quotation" role="tabpanel">
                <?php if (empty($quotationTemplates)): ?>
                <div class="text-center text-muted py-5">
                    <i class="ri-file-list-2-line" style="font-size:48px"></i>
                    <p class="mt-3 mb-2">Chưa có mẫu báo giá nào</p>
                    <a href="<?= url('settings/document-templates/create?type=quotation') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Tạo mẫu báo giá</a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:30%">Tên mẫu</th>
                                <th style="width:100px" class="text-center">Mặc định</th>
                                <th style="width:100px" class="text-center">Trạng thái</th>
                                <th style="width:150px">Người tạo</th>
                                <th style="width:150px">Ngày tạo</th>
                                <th style="width:120px" class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quotationTemplates as $t): ?>
                            <tr>
                                <td>
                                    <a href="<?= url('settings/document-templates/' . $t['id'] . '/edit') ?>" class="fw-medium"><?= e($t['name']) ?></a>
                                    <?php if ($t['description'] ?? null): ?><br><small class="text-muted"><?= e($t['description']) ?></small><?php endif; ?>
                                </td>
                                <td class="text-center"><?php if ($t['is_default'] ?? 0): ?><span class="badge bg-warning">Mặc định</span><?php endif; ?></td>
                                <td class="text-center">
                                    <?php if ($t['is_active'] ?? 0): ?><span class="badge bg-success">Hoạt động</span>
                                    <?php else: ?><span class="badge bg-secondary">Tắt</span><?php endif; ?>
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
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            <!-- Tab Hợp đồng -->
            <div class="tab-pane <?= $activeTab === 'contract' ? 'active show' : '' ?>" id="tab-contract" role="tabpanel">
                <?php if (empty($contractTemplates)): ?>
                <div class="text-center text-muted py-5">
                    <i class="ri-file-text-line" style="font-size:48px"></i>
                    <p class="mt-3 mb-2">Chưa có mẫu hợp đồng nào</p>
                    <a href="<?= url('settings/document-templates/create?type=contract') ?>" class="btn btn-success"><i class="ri-add-line me-1"></i> Tạo mẫu hợp đồng</a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:30%">Tên mẫu</th>
                                <th style="width:100px" class="text-center">Mặc định</th>
                                <th style="width:100px" class="text-center">Trạng thái</th>
                                <th style="width:150px">Người tạo</th>
                                <th style="width:150px">Ngày tạo</th>
                                <th style="width:120px" class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contractTemplates as $t): ?>
                            <tr>
                                <td>
                                    <a href="<?= url('settings/document-templates/' . $t['id'] . '/edit') ?>" class="fw-medium"><?= e($t['name']) ?></a>
                                    <?php if ($t['description'] ?? null): ?><br><small class="text-muted"><?= e($t['description']) ?></small><?php endif; ?>
                                </td>
                                <td class="text-center"><?php if ($t['is_default'] ?? 0): ?><span class="badge bg-warning">Mặc định</span><?php endif; ?></td>
                                <td class="text-center">
                                    <?php if ($t['is_active'] ?? 0): ?><span class="badge bg-success">Hoạt động</span>
                                    <?php else: ?><span class="badge bg-secondary">Tắt</span><?php endif; ?>
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
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
