<?php $pageTitle = 'Quản lý nhãn'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Quản lý nhãn</h4>
        </div>

        <div class="row">
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Tạo nhãn mới</h5>
                    </div>
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
                                        <?php
                                        $presetColors = ['#405189','#0ab39c','#f06548','#f7b84b','#299cdb','#6559cc','#e83e8c','#3577f1','#66d1d1','#f3b600'];
                                        foreach ($presetColors as $pc): ?>
                                            <span class="tag-color-preset rounded-circle border" style="width:24px;height:24px;background:<?= $pc ?>;cursor:pointer;display:inline-block;" data-color="<?= $pc ?>"></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ri-add-line me-1"></i> Thêm nhãn
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">Danh sách nhãn</h5>
                        <span class="badge bg-primary-subtle text-primary" id="tagCount"><?= count($tags) ?> nhãn</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3" id="tagGrid">
                            <?php if (!empty($tags)): ?>
                                <?php foreach ($tags as $tag): ?>
                                    <div class="col-md-6 col-lg-4 tag-card" data-tag-id="<?= $tag['id'] ?>">
                                        <div class="border rounded p-3 h-100">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="badge fs-13" style="background-color: <?= e($tag['color']) ?>; color: #fff;">
                                                    <?= e($tag['name']) ?>
                                                </span>
                                                <div class="dropdown">
                                                    <button class="btn btn-ghost-secondary btn-icon" data-bs-toggle="dropdown">
                                                        <i class="ri-more-2-fill"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item edit-tag-btn" href="#" data-id="<?= $tag['id'] ?>" data-name="<?= e($tag['name']) ?>" data-color="<?= e($tag['color']) ?>"><i class="ri-pencil-line me-2"></i>Sửa</a></li>
                                                        <li><a class="dropdown-item text-danger delete-tag-btn" href="#" data-id="<?= $tag['id'] ?>"><i class="ri-delete-bin-line me-2"></i>Xóa</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <small class="text-muted"><i class="ri-link me-1"></i><?= (int) $tag['use_count'] ?> liên kết</small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12 text-center py-4">
                                    <i class="ri-price-tag-3-line fs-1 text-muted d-block mb-2"></i>
                                    <p class="text-muted mb-0">Chưa có nhãn nào. Tạo nhãn đầu tiên ở bên trái.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Tag Modal -->
        <div class="modal fade" id="editTagModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Sửa nhãn</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="editTagForm">
                        <div class="modal-body">
                            <input type="hidden" id="editTagId">
                            <div class="mb-3">
                                <label class="form-label">Tên nhãn</label>
                                <input type="text" class="form-control" id="editTagName" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Màu sắc</label>
                                <input type="color" class="form-control form-control-color" id="editTagColor" style="width:50px;height:38px;">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Color presets
    document.querySelectorAll('.tag-color-preset').forEach(function(el) {
        el.addEventListener('click', function() {
            document.getElementById('tagColor').value = this.dataset.color;
        });
    });

    // Create tag
    document.getElementById('createTagForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var name = document.getElementById('tagName').value.trim();
        var color = document.getElementById('tagColor').value;
        if (!name) return;

        var formData = new FormData();
        formData.append('name', name);
        formData.append('color', color);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

        fetch(BASE_URL + '/tags/store', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || 'Có lỗi xảy ra');
            }
        });
    });

    // Edit tag
    document.querySelectorAll('.edit-tag-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('editTagId').value = this.dataset.id;
            document.getElementById('editTagName').value = this.dataset.name;
            document.getElementById('editTagColor').value = this.dataset.color;
            new bootstrap.Modal(document.getElementById('editTagModal')).show();
        });
    });

    document.getElementById('editTagForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var id = document.getElementById('editTagId').value;
        var formData = new FormData();
        formData.append('name', document.getElementById('editTagName').value.trim());
        formData.append('color', document.getElementById('editTagColor').value);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

        fetch(BASE_URL + '/tags/' + id + '/update', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || 'Có lỗi xảy ra');
            }
        });
    });

    // Delete tag
    document.querySelectorAll('.delete-tag-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (!confirm('Xác nhận xóa nhãn này?')) return;
            var id = this.dataset.id;

            var formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

            fetch(BASE_URL + '/tags/' + id + '/delete', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    var card = document.querySelector('.tag-card[data-tag-id="' + id + '"]');
                    if (card) card.remove();
                }
            });
        });
    });
});
</script>
