<?php $pageTitle = 'Tìm kiếm: ' . e($query); ?>

<!-- Page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Tìm kiếm: <?= e($query) ?></h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">ToryCRM</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('help') ?>">Trợ giúp</a></li>
                    <li class="breadcrumb-item active">Tìm kiếm</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Search form -->
<div class="row justify-content-center mb-4">
    <div class="col-lg-8">
        <form action="<?= url('help/search') ?>" method="GET">
            <div class="input-group input-group-lg">
                <span class="input-group-text bg-white">
                    <i class="ri-search-line text-muted"></i>
                </span>
                <input type="text" class="form-control" name="q" placeholder="Tìm kiếm bài viết..." value="<?= e($query) ?>">
                <button class="btn btn-primary" type="submit">Tìm kiếm</button>
            </div>
        </form>
    </div>
</div>

<!-- Results -->
<div class="row justify-content-center">
    <div class="col-lg-8">
        <?php if (!empty($query)): ?>
            <p class="text-muted mb-3">Tìm thấy <strong><?= count($results) ?></strong> kết quả cho "<strong><?= e($query) ?></strong>"</p>
        <?php endif; ?>

        <?php if (!empty($results)): ?>
            <?php foreach ($results as $article): ?>
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 me-3">
                                <i class="ri-article-line fs-3 text-primary"></i>
                            </div>
                            <div class="flex-grow-1">
                                <a href="<?= url('help/article/' . $article['slug']) ?>" class="text-dark">
                                    <h6 class="mb-1"><?= e($article['title']) ?></h6>
                                </a>
                                <span class="badge bg-info-subtle text-info mb-2">
                                    <?= e($article['category_name']) ?>
                                </span>
                                <p class="text-muted mb-0 small">
                                    <?= e(mb_substr(strip_tags($article['content'] ?? ''), 0, 200)) ?>...
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php elseif (!empty($query)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="ri-search-line fs-1 text-muted d-block mb-3"></i>
                    <h5>Không tìm thấy kết quả</h5>
                    <p class="text-muted">Thử tìm kiếm với từ khóa khác hoặc <a href="<?= url('help') ?>">duyệt theo danh mục</a>.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center py-5 text-muted">
                    <i class="ri-search-line fs-1 d-block mb-3"></i>
                    <p>Nhập từ khóa để tìm kiếm bài viết trợ giúp.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
