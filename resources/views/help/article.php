<?php $pageTitle = e($article['title']); ?>

<!-- Page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0"><?= e($article['title']) ?></h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">ToryCRM</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('help') ?>">Trợ giúp</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('help/category/' . $article['category_slug']) ?>"><?= e($article['category_name']) ?></a></li>
                    <li class="breadcrumb-item active"><?= e($article['title']) ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Article content -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-4">
                    <span class="badge bg-primary-subtle text-primary me-2">
                        <i class="ri-folder-line me-1"></i><?= e($article['category_name']) ?>
                    </span>
                    <span class="text-muted me-3">
                        <i class="ri-time-line me-1"></i><?= date('d/m/Y', strtotime($article['created_at'])) ?>
                    </span>
                    <span class="badge bg-secondary-subtle text-secondary">
                        <i class="ri-eye-line me-1"></i><?= number_format($article['view_count'] ?? 0) ?> lượt xem
                    </span>
                </div>

                <div class="article-content">
                    <?= $article['content'] ?>
                </div>
            </div>
        </div>

        <!-- Helpful? -->
        <div class="card">
            <div class="card-body text-center">
                <h6 class="mb-3">Bài viết này có hữu ích?</h6>
                <form method="POST" action="<?= url('help/' . $article['id'] . '/helpful') ?>" class="d-inline">
                    <input type="hidden" name="vote" value="yes">
                    <button type="submit" class="btn btn-outline-success me-2">
                        <i class="ri-thumb-up-line me-1"></i> Có
                        <?php if (($article['helpful_yes'] ?? 0) > 0): ?>
                            <span class="badge bg-success ms-1"><?= $article['helpful_yes'] ?></span>
                        <?php endif; ?>
                    </button>
                </form>
                <form method="POST" action="<?= url('help/' . $article['id'] . '/helpful') ?>" class="d-inline">
                    <input type="hidden" name="vote" value="no">
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="ri-thumb-down-line me-1"></i> Không
                        <?php if (($article['helpful_no'] ?? 0) > 0): ?>
                            <span class="badge bg-danger ms-1"><?= $article['helpful_no'] ?></span>
                        <?php endif; ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Related articles -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="ri-links-line me-1"></i> Bài viết liên quan
                </h6>
            </div>
            <div class="card-body p-2">
                <?php if (!empty($relatedArticles)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($relatedArticles as $related): ?>
                            <a href="<?= url('help/article/' . $related['slug']) ?>" class="list-group-item list-group-item-action">
                                <i class="ri-article-line me-2 text-primary"></i>
                                <?= e($related['title']) ?>
                                <br>
                                <small class="text-muted"><?= date('d/m/Y', strtotime($related['created_at'])) ?></small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-3 text-muted">
                        Không có bài viết liên quan
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <a href="<?= url('help/category/' . $article['category_slug']) ?>" class="btn btn-soft-primary w-100">
            <i class="ri-arrow-left-line me-1"></i> Quay lại danh mục
        </a>
    </div>
</div>
