<?php $pageTitle = 'Email Templates'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Email Templates</h4>
            <a href="<?= url('email-templates/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Tạo template</a>
        </div>

        <!-- Filters -->
        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?= url('email-templates') ?>" class="row g-3 mb-0">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" placeholder="Tìm kiếm template..." value="<?= e($filters['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="category" class="form-select">
                            <option value="">Tất cả danh mục</option>
                            <?php
                            $categories = [
                                'general' => 'Chung',
                                'sales' => 'Bán hàng',
                                'marketing' => 'Marketing',
                                'support' => 'Hỗ trợ',
                                'follow_up' => 'Theo dõi',
                                'welcome' => 'Chào mừng',
                                'invoice' => 'Hóa đơn',
                            ];
                            foreach ($categories as $v => $l): ?>
                                <option value="<?= $v ?>" <?= ($filters['category'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary"><i class="ri-search-line"></i> Lọc</button>
                        <a href="<?= url('email-templates') ?>" class="btn btn-soft-secondary">Xóa lọc</a>
                    </div>
                </form>
            </div>
        </div>

        <?php
        $categoryLabels = [
            'general' => 'Chung', 'sales' => 'Bán hàng', 'marketing' => 'Marketing',
            'support' => 'Hỗ trợ', 'follow_up' => 'Theo dõi', 'welcome' => 'Chào mừng', 'invoice' => 'Hóa đơn',
        ];
        $categoryColors = [
            'general' => 'secondary', 'sales' => 'success', 'marketing' => 'info',
            'support' => 'warning', 'follow_up' => 'primary', 'welcome' => 'danger', 'invoice' => 'dark',
        ];
        ?>

        <!-- Template Cards Grid -->
        <div class="row">
            <?php if (!empty($templates['items'])): ?>
                <?php foreach ($templates['items'] as $tpl): ?>
                    <div class="col-xl-4 col-md-6">
                        <div class="card card-height-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="flex-grow-1">
                                        <h5 class="card-title mb-1"><?= e($tpl['name']) ?></h5>
                                        <span class="badge bg-<?= $categoryColors[$tpl['category']] ?? 'secondary' ?>-subtle text-<?= $categoryColors[$tpl['category']] ?? 'secondary' ?>">
                                            <?= $categoryLabels[$tpl['category']] ?? $tpl['category'] ?>
                                        </span>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <div class="dropdown">
                                            <button class="btn btn-soft-secondary" data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="<?= url('email-templates/' . $tpl['id'] . '/edit') ?>"><i class="ri-pencil-line me-2"></i>Sửa</a></li>
                                                <li><a class="dropdown-item preview-template" href="#" data-id="<?= $tpl['id'] ?>"><i class="ri-eye-line me-2"></i>Xem trước</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form method="POST" action="<?= url('email-templates/' . $tpl['id'] . '/delete') ?>" data-confirm="Xác nhận xóa template này?">
                                                        <?= csrf_field() ?>
                                                        <button class="dropdown-item text-danger"><i class="ri-delete-bin-line me-2"></i>Xóa</button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-muted mb-2 text-truncate"><i class="ri-mail-line me-1"></i> <?= e($tpl['subject'] ?: '(Chưa có tiêu đề)') ?></p>
                                <div class="d-flex align-items-center justify-content-between">
                                    <small class="text-muted"><i class="ri-send-plane-line me-1"></i> Đã dùng: <?= number_format($tpl['use_count'] ?? 0) ?> lần</small>
                                    <small class="text-muted"><?= time_ago($tpl['created_at']) ?></small>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-top">
                                <div class="d-flex gap-2">
                                    <a href="<?= url('email-templates/' . $tpl['id'] . '/edit') ?>" class="btn btn-primary flex-grow-1"><i class="ri-pencil-line me-1"></i> Chỉnh sửa</a>
                                    <button class="btn btn-soft-info preview-template" data-id="<?= $tpl['id'] ?>"><i class="ri-eye-line"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5 text-muted">
                            <i class="ri-mail-settings-line fs-1 d-block mb-2"></i>
                            Chưa có email template nào
                            <div class="mt-3">
                                <a href="<?= url('email-templates/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Tạo template đầu tiên</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if (($templates['total_pages'] ?? 0) > 1): ?>
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">Hiển thị <?= count($templates['items']) ?> / <?= $templates['total'] ?></div>
                <nav><ul class="pagination mb-0">
                    <?php for ($i = 1; $i <= $templates['total_pages']; $i++): ?>
                        <li class="page-item <?= $i === $templates['page'] ? 'active' : '' ?>">
                            <a class="page-link" href="<?= url('email-templates?page=' . $i . '&' . http_build_query(array_filter($filters ?? []))) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul></nav>
            </div>
        <?php endif; ?>

        <!-- Preview Modal -->
        <div class="modal fade" id="previewModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Xem trước template</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0">
                        <iframe id="preview-iframe" style="width:100%;height:500px;border:none;"></iframe>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.querySelectorAll('.preview-template').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.dataset.id;
                fetch('/email-templates/' + id + '/preview')
                    .then(r => r.json())
                    .then(data => {
                        if (data.html) {
                            const iframe = document.getElementById('preview-iframe');
                            const modal = new bootstrap.Modal(document.getElementById('previewModal'));
                            modal.show();
                            setTimeout(() => {
                                iframe.contentDocument.open();
                                iframe.contentDocument.write(data.html);
                                iframe.contentDocument.close();
                            }, 200);
                        }
                    });
            });
        });
        </script>
