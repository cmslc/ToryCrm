<?php
$pageTitle = 'Nhãn & Trạng thái';
$colors = ['primary'=>'Xanh dương','success'=>'Xanh lá','info'=>'Xanh nhạt','warning'=>'Vàng','danger'=>'Đỏ','secondary'=>'Xám','dark'=>'Đen'];
$icons = [
    'ri-user-add-line'=>'Người mới','ri-phone-line'=>'Điện thoại','ri-star-line'=>'Ngôi sao',
    'ri-checkbox-circle-line'=>'Hoàn thành','ri-close-circle-line'=>'Đóng','ri-time-line'=>'Đồng hồ',
    'ri-heart-line'=>'Tim','ri-fire-line'=>'Lửa','ri-circle-line'=>'Tròn',
    'ri-shield-check-line'=>'Bảo vệ','ri-vip-crown-line'=>'VIP','ri-mail-line'=>'Email',
    'ri-hand-heart-line'=>'Chăm sóc','ri-eye-line'=>'Theo dõi','ri-refresh-line'=>'Quay lại',
];
$presetColors = ['#405189','#0ab39c','#f06548','#f7b84b','#299cdb','#6559cc','#e83e8c','#3577f1','#66d1d1','#f3b600'];
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Nhãn & Trạng thái</h4>
    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="<?= url('settings') ?>">Cài đặt</a></li>
        <li class="breadcrumb-item active">Nhãn & Trạng thái</li>
    </ol>
</div>

<ul class="nav nav-tabs nav-tabs-custom mb-3" role="tablist">
    <li class="nav-item"><a class="nav-link <?= ($_GET['tab'] ?? '') !== 'tags' ? 'active' : '' ?>" data-bs-toggle="tab" href="#tabStatuses"><i class="ri-shield-check-line me-1"></i> Trạng thái KH <span class="badge bg-primary-subtle text-primary ms-1"><?= count($statuses) ?></span></a></li>
    <li class="nav-item"><a class="nav-link <?= ($_GET['tab'] ?? '') === 'tags' ? 'active' : '' ?>" data-bs-toggle="tab" href="#tabTags"><i class="ri-price-tag-3-line me-1"></i> Nhãn <span class="badge bg-info-subtle text-info ms-1"><?= count($tags) ?></span></a></li>
</ul>

<div class="tab-content">
<!-- ===== TRẠNG THÁI TAB ===== -->
<div class="tab-pane <?= ($_GET['tab'] ?? '') !== 'tags' ? 'active' : '' ?>" id="tabStatuses">
<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Danh sách trạng thái</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th style="width:40px"></th><th>Trạng thái</th><th>Mã</th><th>Màu</th><th>Mặc định</th><th>Số KH</th><th>Thao tác</th></tr>
                        </thead>
                        <tbody id="sortableStatuses">
                            <?php foreach ($statuses as $s):
                                $contactCount = \Core\Database::fetch("SELECT COUNT(*) as cnt FROM contacts WHERE status = ? AND tenant_id = ?", [$s['slug'], $_SESSION['tenant_id'] ?? 1]);
                            ?>
                            <tr data-id="<?= $s['id'] ?>">
                                <td class="text-center" style="cursor:grab"><i class="ri-drag-move-line text-muted"></i></td>
                                <td>
                                    <span class="badge bg-<?= e($s['color']) ?>-subtle text-<?= e($s['color']) ?>">
                                        <i class="<?= e($s['icon']) ?> me-1"></i><?= e($s['name']) ?>
                                    </span>
                                </td>
                                <td><code class="fs-11"><?= e($s['slug']) ?></code></td>
                                <td><span class="d-inline-block rounded-circle" style="width:14px;height:14px;background:var(--vz-<?= e($s['color']) ?>)"></span></td>
                                <td>
                                    <?php if ($s['is_default']): ?>
                                        <span class="badge bg-success">Mặc định</span>
                                    <?php else: ?>
                                        <form method="POST" action="<?= url('settings/contact-statuses/' . $s['id'] . '/default') ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-soft-secondary py-0 px-2 fs-12">Đặt MĐ</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-secondary-subtle text-secondary"><?= $contactCount['cnt'] ?? 0 ?></span></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-soft-primary btn-icon edit-status-btn" data-id="<?= $s['id'] ?>" data-name="<?= e($s['name']) ?>" data-color="<?= e($s['color']) ?>" data-icon="<?= e($s['icon']) ?>" title="Sửa"><i class="ri-pencil-line"></i></button>
                                        <?php if (!$s['is_default']): ?>
                                        <form method="POST" action="<?= url('settings/contact-statuses/' . $s['id'] . '/delete') ?>" onsubmit="return confirm('Xóa trạng thái <?= e($s['name']) ?>?')">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-soft-danger btn-icon" title="Xóa"><i class="ri-delete-bin-line"></i></button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card" id="addCard">
            <div class="card-header"><h5 class="card-title mb-0">Thêm trạng thái</h5></div>
            <div class="card-body">
                <form method="POST" action="<?= url('settings/contact-statuses/store') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="VD: Đang chăm sóc" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Màu</label>
                        <select name="color" class="form-select">
                            <?php foreach ($colors as $val => $label): ?><option value="<?= $val ?>"><?= $label ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon</label>
                        <select name="icon" class="form-select">
                            <?php foreach ($icons as $val => $label): ?><option value="<?= $val ?>"><?= $label ?></option><?php endforeach; ?>
                        </select>
                        <div class="mt-2 d-flex gap-2 flex-wrap">
                            <?php foreach ($icons as $val => $label): ?>
                            <span class="border rounded p-1 px-2" style="cursor:pointer" onclick="this.closest('.mb-3').querySelector('select').value='<?= $val ?>'" title="<?= $label ?>"><i class="<?= $val ?> fs-16"></i></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="ri-add-line me-1"></i> Thêm</button>
                </form>
            </div>
        </div>
        <div class="card d-none" id="editCard">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Sửa trạng thái</h5>
                <button class="btn btn-soft-secondary py-0 px-2" onclick="document.getElementById('editCard').classList.add('d-none');document.getElementById('addCard').classList.remove('d-none');">Hủy</button>
            </div>
            <div class="card-body">
                <form method="POST" id="editForm">
                    <?= csrf_field() ?>
                    <div class="mb-3"><label class="form-label">Tên <span class="text-danger">*</span></label><input type="text" class="form-control" name="name" id="editName" required></div>
                    <div class="mb-3"><label class="form-label">Màu</label><select name="color" id="editColor" class="form-select"><?php foreach ($colors as $val => $label): ?><option value="<?= $val ?>"><?= $label ?></option><?php endforeach; ?></select></div>
                    <div class="mb-3"><label class="form-label">Icon</label><select name="icon" id="editIcon" class="form-select"><?php foreach ($icons as $val => $label): ?><option value="<?= $val ?>"><?= $label ?></option><?php endforeach; ?></select></div>
                    <button type="submit" class="btn btn-primary w-100"><i class="ri-check-line me-1"></i> Cập nhật</button>
                </form>
            </div>
        </div>
    </div>
</div>
</div>

<!-- ===== NHÃN TAB ===== -->
<div class="tab-pane <?= ($_GET['tab'] ?? '') === 'tags' ? 'active' : '' ?>" id="tabTags">
<div class="row">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Tạo nhãn mới</h5></div>
            <div class="card-body">
                <form id="createTagForm">
                    <div class="mb-3">
                        <label class="form-label">Tên nhãn</label>
                        <input type="text" class="form-control" name="name" id="tagName" placeholder="Nhập tên nhãn..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Màu sắc</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="color" class="form-control form-control-color" name="color" id="tagColor" value="#405189" style="width:50px;height:38px;">
                            <div class="d-flex gap-1 flex-wrap">
                                <?php foreach ($presetColors as $pc): ?>
                                <span class="tag-color-preset rounded-circle border" style="width:24px;height:24px;background:<?= $pc ?>;cursor:pointer;display:inline-block;" data-color="<?= $pc ?>"></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="ri-add-line me-1"></i> Thêm nhãn</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">Danh sách nhãn</h5>
                <span class="badge bg-info-subtle text-info" id="tagCount"><?= count($tags) ?> nhãn</span>
            </div>
            <div class="card-body">
                <div class="row g-3" id="tagGrid">
                    <?php if (!empty($tags)): ?>
                        <?php foreach ($tags as $tag): ?>
                        <div class="col-md-6 col-lg-4 tag-card" data-tag-id="<?= $tag['id'] ?>">
                            <div class="border rounded p-3 h-100">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge fs-13" style="background-color: <?= e($tag['color']) ?>; color: #fff;"><?= e($tag['name']) ?></span>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-soft-primary btn-icon edit-tag-btn" data-id="<?= $tag['id'] ?>" data-name="<?= e($tag['name']) ?>" data-color="<?= e($tag['color']) ?>" title="Sửa"><i class="ri-pencil-line"></i></button>
                                        <button class="btn btn-soft-danger btn-icon delete-tag-btn" data-id="<?= $tag['id'] ?>" title="Xóa"><i class="ri-delete-bin-line"></i></button>
                                    </div>
                                </div>
                                <small class="text-muted"><i class="ri-link me-1"></i><?= (int)$tag['use_count'] ?> liên kết</small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-4">
                            <i class="ri-price-tag-3-line fs-1 text-muted d-block mb-2"></i>
                            <p class="text-muted mb-0">Chưa có nhãn nào.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>

<!-- Edit Tag Modal -->
<div class="modal fade" id="editTagModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Sửa nhãn</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="editTagForm">
                <div class="modal-body">
                    <input type="hidden" id="editTagId">
                    <div class="mb-3"><label class="form-label">Tên nhãn</label><input type="text" class="form-control" id="editTagName" required></div>
                    <div class="mb-3"><label class="form-label">Màu sắc</label><input type="color" class="form-control form-control-color" id="editTagColor" style="width:50px;height:38px;"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-primary">Lưu</button></div>
            </form>
        </div>
    </div>
</div>

<script>
// === Status edit ===
document.querySelectorAll('.edit-status-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('editForm').action = '<?= url('settings/contact-statuses/') ?>' + this.dataset.id + '/update';
        document.getElementById('editName').value = this.dataset.name;
        document.getElementById('editColor').value = this.dataset.color;
        document.getElementById('editIcon').value = this.dataset.icon;
        document.getElementById('editCard').classList.remove('d-none');
        document.getElementById('addCard').classList.add('d-none');
        document.getElementById('editName').focus();
    });
});

// === Tag CRUD ===
document.querySelectorAll('.tag-color-preset').forEach(function(el) {
    el.addEventListener('click', function() { document.getElementById('tagColor').value = this.dataset.color; });
});

document.getElementById('createTagForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    var fd = new FormData();
    fd.append('name', document.getElementById('tagName').value.trim());
    fd.append('color', document.getElementById('tagColor').value);
    fd.append('_token', '<?= csrf_token() ?>');
    fetch('<?= url("tags/store") ?>', {method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:fd})
    .then(function(r){return r.json()}).then(function(d){ if(d.success) location.reload(); else alert(d.error||'Lỗi'); });
});

document.querySelectorAll('.edit-tag-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('editTagId').value = this.dataset.id;
        document.getElementById('editTagName').value = this.dataset.name;
        document.getElementById('editTagColor').value = this.dataset.color;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('editTagModal')).show();
    });
});

document.getElementById('editTagForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    var id = document.getElementById('editTagId').value;
    var fd = new FormData();
    fd.append('name', document.getElementById('editTagName').value.trim());
    fd.append('color', document.getElementById('editTagColor').value);
    fd.append('_token', '<?= csrf_token() ?>');
    fetch('<?= url("tags/") ?>' + id + '/update', {method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:fd})
    .then(function(r){return r.json()}).then(function(d){ if(d.success) location.reload(); else alert(d.error||'Lỗi'); });
});

document.querySelectorAll('.delete-tag-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        if (!confirm('Xóa nhãn này?')) return;
        var id = this.dataset.id;
        var fd = new FormData();
        fd.append('_token', '<?= csrf_token() ?>');
        fetch('<?= url("tags/") ?>' + id + '/delete', {method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:fd})
        .then(function(r){return r.json()}).then(function(d){ if(d.success){ var c=document.querySelector('.tag-card[data-tag-id="'+id+'"]'); if(c)c.remove(); } });
    });
});

// === Status drag reorder ===
(function() {
    var tbody = document.getElementById('sortableStatuses');
    if (!tbody) return;
    var dragEl = null;
    tbody.querySelectorAll('tr').forEach(function(row) {
        var handle = row.querySelector('.ri-drag-move-line');
        if (!handle) return;
        handle.closest('td').addEventListener('mousedown', function() { dragEl = row; row.style.opacity = '0.5'; });
    });
    tbody.addEventListener('dragover', function(e) { e.preventDefault(); });
    document.addEventListener('mouseup', function() {
        if (dragEl) {
            dragEl.style.opacity = '';
            var ids = [];
            tbody.querySelectorAll('tr').forEach(function(r) { ids.push(r.dataset.id); });
            fetch('<?= url('settings/contact-statuses/reorder') ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: '_token=<?= csrf_token() ?>&' + ids.map(function(id, i) { return 'ids[' + i + ']=' + id; }).join('&')
            });
            dragEl = null;
        }
    });
})();
</script>
