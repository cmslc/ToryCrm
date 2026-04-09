<?php $pageTitle = 'Marketplace'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Marketplace</h4>
            <a href="<?= url('plugins') ?>" class="btn btn-soft-primary"><i class="ri-list-check me-1"></i> Plugin đã cài</a>
        </div>

        <!-- Search & Filter -->
        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?= url('plugins/marketplace') ?>" class="row g-3">
                    <div class="col-md-6">
                        <div class="search-box">
                            <input type="text" class="form-control" name="search" placeholder="Tìm kiếm plugin..." value="<?= e($filters['search'] ?? '') ?>">
                            <i class="ri-search-line search-icon"></i>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="category" class="form-select" onchange="this.form.submit()">
                            <option value="">Tất cả danh mục</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= e($cat) ?>" <?= ($filters['category'] ?? '') === $cat ? 'selected' : '' ?>><?= e(ucfirst($cat)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="ri-search-line me-1"></i> Lọc</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Plugin Grid -->
        <div class="row">
            <?php if (!empty($plugins)): ?>
                <?php foreach ($plugins as $plugin): ?>
                    <?php $isInstalled = in_array($plugin['id'], $installedIds); ?>
                    <div class="col-xl-4 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-start mb-3">
                                    <div class="flex-shrink-0">
                                        <div class="avatar-md">
                                            <div class="avatar-title bg-primary-subtle text-primary rounded fs-24">
                                                <i class="<?= e($plugin['icon']) ?>"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="mb-1"><?= e($plugin['name']) ?></h5>
                                        <div class="d-flex gap-2 align-items-center">
                                            <span class="badge bg-info-subtle text-info"><?= e(ucfirst($plugin['category'])) ?></span>
                                            <span class="text-muted">v<?= e($plugin['version']) ?></span>
                                        </div>
                                    </div>
                                    <?php if ($isInstalled): ?>
                                        <span class="badge bg-success-subtle text-success">Đã cài</span>
                                    <?php endif; ?>
                                </div>

                                <p class="text-muted mb-3"><?= e($plugin['description']) ?></p>

                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="text-muted"><i class="ri-user-line me-1"></i><?= e($plugin['author']) ?></span>

                                    <?php if ($isInstalled): ?>
                                        <a href="<?= url('plugins/' . $plugin['id'] . '/configure') ?>" class="btn btn-soft-primary">
                                            <i class="ri-settings-3-line me-1"></i> Cấu hình
                                        </a>
                                    <?php else: ?>
                                        <form method="POST" action="<?= url('plugins/' . $plugin['id'] . '/install') ?>">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="ri-download-line me-1"></i> Cài đặt
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="ri-store-2-line fs-1 text-muted d-block mb-3"></i>
                            <h5 class="text-muted">Không tìm thấy plugin nào</h5>
                            <p class="text-muted mb-0">Thử thay đổi bộ lọc hoặc từ khóa tìm kiếm.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
