<?php
$pageTitle = 'Tài liệu';
$fCategory = $filters['category'] ?? '';
$fSearch = $filters['search'] ?? '';
$iconMap = ['pdf'=>'ri-file-pdf-line text-danger','doc'=>'ri-file-word-line text-primary','docx'=>'ri-file-word-line text-primary','xls'=>'ri-file-excel-line text-success','xlsx'=>'ri-file-excel-line text-success','ppt'=>'ri-file-ppt-line text-warning','pptx'=>'ri-file-ppt-line text-warning','jpg'=>'ri-image-line text-info','jpeg'=>'ri-image-line text-info','png'=>'ri-image-line text-info','zip'=>'ri-file-zip-line text-secondary','rar'=>'ri-file-zip-line text-secondary'];
function fileIcon($type) { global $iconMap; return $iconMap[strtolower($type)] ?? 'ri-file-line text-muted'; }
function fileSize($bytes) { if ($bytes < 1024) return $bytes . ' B'; if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB'; return round($bytes / 1048576, 1) . ' MB'; }
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0"><i class="ri-folder-line me-2"></i> Tài liệu</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal"><i class="ri-upload-2-line me-1"></i> Tải lên</button>
</div>

<!-- Filter -->
<div class="card mb-2">
    <div class="card-header p-2">
        <form method="GET" action="<?= url('documents') ?>" class="d-flex align-items-center gap-2 flex-wrap">
            <div class="search-box" style="min-width:180px;max-width:280px">
                <input type="text" class="form-control" name="search" placeholder="Tìm tài liệu..." value="<?= e($fSearch) ?>">
                <i class="ri-search-line search-icon"></i>
            </div>
            <select name="category" class="form-select" style="width:auto;min-width:140px" onchange="this.form.submit()">
                <option value="">Tất cả danh mục</option>
                <?php foreach ($categories as $c): ?>
                <option value="<?= e($c['category']) ?>" <?= $fCategory === $c['category'] ? 'selected' : '' ?>><?= e($c['category']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary"><i class="ri-search-line me-1"></i> Tìm</button>
            <?php if ($fCategory || $fSearch): ?>
            <a href="<?= url('documents') ?>" class="btn btn-soft-danger"><i class="ri-refresh-line me-1"></i> Xóa lọc</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Documents Grid -->
<div class="row">
    <?php if (!empty($docs)): ?>
        <?php foreach ($docs as $d): ?>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="card card-height-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-sm flex-shrink-0">
                            <div class="avatar-title bg-light rounded fs-24">
                                <i class="<?= fileIcon($d['file_type']) ?>"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3 overflow-hidden">
                            <h6 class="mb-1 text-truncate" title="<?= e($d['title']) ?>"><?= e($d['title']) ?></h6>
                            <small class="text-muted"><?= fileSize($d['file_size']) ?> &bull; <?= strtoupper($d['file_type']) ?></small>
                        </div>
                    </div>
                    <?php if ($d['category']): ?><span class="badge bg-info-subtle text-info mb-2"><?= e($d['category']) ?></span><?php endif; ?>
                    <?php if ($d['note']): ?><p class="text-muted fs-12 mb-2"><?= e($d['note']) ?></p><?php endif; ?>
                    <div class="d-flex align-items-center justify-content-between mt-auto">
                        <small class="text-muted"><?= user_avatar($d['uploaded_by_name'] ?? null) ?> <?= created_ago($d['created_at']) ?></small>
                        <div class="d-flex gap-1">
                            <a href="<?= url('documents/' . $d['id'] . '/download') ?>" class="btn btn-soft-primary btn-icon" title="Tải xuống"><i class="ri-download-line"></i></a>
                            <form method="POST" action="<?= url('documents/' . $d['id'] . '/delete') ?>" onsubmit="return confirm('Xóa tài liệu này?')">
                                <?= csrf_field() ?>
                                <button class="btn btn-soft-danger btn-icon" title="Xóa"><i class="ri-delete-bin-line"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="ri-folder-open-line fs-1 text-muted d-block mb-2"></i>
                    <h5 class="text-muted">Chưa có tài liệu</h5>
                    <p class="text-muted mb-0">Bấm "Tải lên" để thêm tài liệu đầu tiên.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if ($totalPages > 1): ?>
<?php $qs = http_build_query(array_filter(['category' => $fCategory, 'search' => $fSearch])); ?>
<div class="d-flex align-items-center justify-content-between">
    <span class="text-muted fs-12">Tổng <?= $total ?> tài liệu</span>
    <ul class="pagination pagination-separated mb-0">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= url('documents?' . $qs . '&page=' . ($page - 1)) ?>"><i class="ri-arrow-left-s-line"></i></a></li>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="<?= url('documents?' . $qs . '&page=' . $i) ?>"><?= $i ?></a></li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>"><a class="page-link" href="<?= url('documents?' . $qs . '&page=' . ($page + 1)) ?>"><i class="ri-arrow-right-s-line"></i></a></li>
    </ul>
</div>
<?php endif; ?>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('documents/upload') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header"><h5 class="modal-title"><i class="ri-upload-2-line me-2"></i> Tải lên tài liệu</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="file" required>
                        <small class="text-muted">Tối đa 20MB. PDF, Word, Excel, ảnh, ZIP...</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tiêu đề</label>
                        <input type="text" class="form-control" name="title" placeholder="Tự lấy từ tên file nếu để trống">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Danh mục</label>
                            <input type="text" class="form-control" name="category" placeholder="VD: Hợp đồng, Hóa đơn..." list="catList">
                            <datalist id="catList">
                                <?php foreach ($categories as $c): ?><option value="<?= e($c['category']) ?>"><?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Liên kết</label>
                            <select name="entity_type" class="form-select">
                                <option value="">Không liên kết</option>
                                <option value="contact">Khách hàng</option>
                                <option value="deal">Cơ hội</option>
                                <option value="order">Đơn hàng</option>
                                <option value="contract">Hợp đồng</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi chú</label>
                        <textarea class="form-control" name="note" rows="2"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_shared" value="1" id="isShared">
                        <label class="form-check-label" for="isShared">Chia sẻ cho toàn bộ nhân viên</label>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-primary"><i class="ri-upload-2-line me-1"></i> Tải lên</button></div>
            </form>
        </div>
    </div>
</div>
