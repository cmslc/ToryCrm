<?php $pageTitle = 'Trung tâm trợ giúp'; ?>

<!-- Page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Trung tâm trợ giúp</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">ToryCRM</a></li>
                    <li class="breadcrumb-item active">Trợ giúp</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Search bar -->
<div class="row justify-content-center mb-4">
    <div class="col-lg-8">
        <div class="card bg-primary">
            <div class="card-body py-4">
                <h4 class="text-white text-center mb-3">Bạn cần hỗ trợ gì?</h4>
                <form action="<?= url('help/search') ?>" method="GET">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white border-0">
                            <i class="ri-search-line text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-0" name="q" placeholder="Tìm kiếm bài viết, hướng dẫn..." value="">
                        <button class="btn btn-warning" type="submit">Tìm kiếm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Category cards -->
<div class="row">
    <?php if (!empty($categories)): ?>
        <?php
        $categoryIcons = [
            'ri-book-open-line', 'ri-settings-3-line', 'ri-user-settings-line',
            'ri-contacts-line', 'ri-hand-coin-line', 'ri-mail-line',
            'ri-bar-chart-line', 'ri-plug-line', 'ri-question-line'
        ];
        $categoryColors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary', 'dark', 'primary', 'info'];
        ?>
        <?php foreach ($categories as $i => $category): ?>
            <div class="col-md-4 col-sm-6">
                <a href="<?= url('help/category/' . $category['slug']) ?>" class="text-decoration-none">
                    <div class="card card-animate">
                        <div class="card-body text-center p-4">
                            <div class="avatar-md mx-auto mb-3">
                                <span class="avatar-title bg-<?= $categoryColors[$i % count($categoryColors)] ?>-subtle rounded-circle fs-1">
                                    <i class="<?= $category['icon'] ?? $categoryIcons[$i % count($categoryIcons)] ?> text-<?= $categoryColors[$i % count($categoryColors)] ?>"></i>
                                </span>
                            </div>
                            <h5 class="text-dark mb-2"><?= e($category['name']) ?></h5>
                            <p class="text-muted mb-2"><?= e($category['description'] ?? '') ?></p>
                            <span class="badge bg-primary-subtle text-primary">
                                <?= (int) $category['article_count'] ?> bài viết
                            </span>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5 text-muted">
                    <i class="ri-folder-open-line fs-1 d-block mb-2"></i>
                    Chưa có danh mục nào
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
