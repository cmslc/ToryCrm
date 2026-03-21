<?php $pageTitle = e($category['name']); ?>

<!-- Page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0"><?= e($category['name']) ?></h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">ToryCRM</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('help') ?>">Trợ giúp</a></li>
                    <li class="breadcrumb-item active"><?= e($category['name']) ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <?php if (!empty($category['description'])): ?>
            <p class="text-muted mb-4"><?= e($category['description']) ?></p>
        <?php endif; ?>

        <div class="card">
            <div class="card-body p-0">
                <?php if (!empty($articles)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($articles as $article): ?>
                            <a href="<?= url('help/article/' . $article['slug']) ?>" class="list-group-item list-group-item-action d-flex align-items-center py-3">
                                <div class="flex-shrink-0 me-3">
                                    <i class="ri-article-line fs-4 text-primary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?= e($article['title']) ?></h6>
                                    <small class="text-muted">
                                        <i class="ri-time-line me-1"></i>
                                        <?= date('d/m/Y', strtotime($article['created_at'])) ?>
                                    </small>
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="ri-arrow-right-s-line text-muted"></i>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="ri-file-text-line fs-1 d-block mb-2"></i>
                        Chưa có bài viết nào trong danh mục này
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-3">
            <a href="<?= url('help') ?>" class="btn btn-soft-primary">
                <i class="ri-arrow-left-line me-1"></i> Quay lại
            </a>
        </div>
    </div>
</div>
