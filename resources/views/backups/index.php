<?php $pageTitle = 'Quản lý Backup'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Quản lý Backup Database</h4>
    <form method="POST" action="<?= url('backups/run') ?>" class="d-inline"
          data-confirm="Chạy backup thủ công? Mất vài giây để dump DB.">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-primary"><i class="ri-play-circle-line me-1"></i> Chạy backup ngay</button>
    </form>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="ri-database-2-line me-1"></i> Danh sách file</h5>
                <small class="text-muted">
                    Tổng: <?= count($files) ?> file / <?= format_bytes($totalSize) ?>
                </small>
            </div>
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tên file</th>
                                <th class="text-end">Kích thước</th>
                                <th>Thời điểm</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($files)): ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">
                                    <i class="ri-inbox-line fs-1 d-block mb-2"></i>
                                    Chưa có backup nào. Chạy backup ngay để tạo bản đầu tiên.
                                </td></tr>
                            <?php else: ?>
                                <?php foreach ($files as $f): ?>
                                <tr>
                                    <td class="font-monospace small"><?= e($f['name']) ?></td>
                                    <td class="text-end"><?= format_bytes($f['size']) ?></td>
                                    <td><?= time_ago(date('Y-m-d H:i:s', $f['mtime'])) ?><br>
                                        <small class="text-muted"><?= date('d/m/Y H:i:s', $f['mtime']) ?></small>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= url('backups/download?file=' . urlencode($f['name'])) ?>" class="btn btn-soft-primary btn-icon" title="Tải về">
                                            <i class="ri-download-2-line"></i>
                                        </a>
                                        <form method="POST" action="<?= url('backups/delete') ?>" class="d-inline"
                                              data-confirm="Xóa backup <?= e($f['name']) ?>? Không thể hoàn tác.">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="file" value="<?= e($f['name']) ?>">
                                            <button type="submit" class="btn btn-soft-danger btn-icon" title="Xóa">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-information-line me-1"></i> Thông tin</h5>
            </div>
            <div class="card-body">
                <dl class="mb-0 small">
                    <dt>Thư mục</dt>
                    <dd><code>/var/backups/torycrm-mysql/</code></dd>
                    <dt>Cron tự động</dt>
                    <dd>Hàng ngày 2:30 sáng</dd>
                    <dt>Giữ bản cũ</dt>
                    <dd>7 ngày gần nhất</dd>
                    <dt>Script</dt>
                    <dd><code>/usr/local/bin/torycrm-backup</code></dd>
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-file-list-3-line me-1"></i> Log gần đây</h5>
            </div>
            <div class="card-body p-2">
                <?php if (empty($logTail)): ?>
                    <div class="text-muted small">Chưa có log.</div>
                <?php else: ?>
                    <pre class="mb-0" style="font-size:11px;max-height:300px;overflow:auto"><?= e(implode("\n", $logTail)) ?></pre>
                <?php endif; ?>
            </div>
        </div>

        <div class="alert alert-warning mt-3 small mb-0">
            <i class="ri-alert-line me-1"></i>
            <strong>Lưu ý:</strong> Backup hiện chỉ lưu local trên VPS. Nếu ổ đĩa hỏng sẽ mất cả DB lẫn backup.
            Khuyến nghị cấu hình off-site backup (Backblaze B2, S3) — xem hướng dẫn trong code.
        </div>
    </div>
</div>
