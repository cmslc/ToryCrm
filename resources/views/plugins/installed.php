<?php $pageTitle = 'Plugin đã cài đặt'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Plugin</h4>
            <a href="<?= url('plugins/marketplace') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Thêm plugin</a>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Plugin</th>
                                <th>Phiên bản</th>
                                <th>Danh mục</th>
                                <th>Ngày cài</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($plugins)): ?>
                                <?php foreach ($plugins as $plugin): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-start">
                                            <div class="avatar-xs me-3 mt-1">
                                                <div class="avatar-title bg-primary-subtle text-primary rounded fs-18">
                                                    <i class="<?= e($plugin['icon']) ?>"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?= e($plugin['name']) ?></h6>
                                                <small class="text-muted d-block"><i class="ri-user-line me-1"></i><?= e($plugin['author']) ?></small>
                                                <?php if (!empty($plugin['description'])): ?>
                                                    <div class="text-muted fs-12 mt-1" style="max-width:420px"><?= e($plugin['description']) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="text-muted">v<?= e($plugin['version']) ?></span></td>
                                    <td><span class="badge bg-info-subtle text-info"><?= e(ucfirst($plugin['category'])) ?></span></td>
                                    <td><?= $plugin['tenant_installed_at'] ? date('d/m/Y', strtotime($plugin['tenant_installed_at'])) : '-' ?></td>
                                    <td>
                                        <form method="POST" action="<?= url('plugins/' . $plugin['id'] . '/toggle') ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" <?= $plugin['tenant_active'] ? 'checked' : '' ?> onchange="this.form.submit()">
                                            </div>
                                        </form>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="<?= url('plugins/' . $plugin['id'] . '/configure') ?>" class="btn btn-soft-primary" title="Cấu hình">
                                                <i class="ri-settings-3-line"></i>
                                            </a>
                                            <form method="POST" action="<?= url('plugins/' . $plugin['id'] . '/uninstall') ?>" data-confirm="Gỡ cài đặt plugin này?">
                                                <?= csrf_field() ?>
                                                <button class="btn btn-soft-danger" title="Gỡ cài đặt">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="ri-plug-line fs-1 d-block mb-2"></i>
                                        Chưa cài đặt plugin nào.
                                        <a href="<?= url('plugins/marketplace') ?>">Thêm plugin</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
