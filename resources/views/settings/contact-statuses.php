<?php $pageTitle = 'Trạng thái khách hàng'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Trạng thái khách hàng</h4>
    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="<?= url('settings') ?>">Cài đặt</a></li>
        <li class="breadcrumb-item active">Trạng thái KH</li>
    </ol>
</div>

<div class="row">
    <!-- Left: Status List -->
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Danh sách trạng thái</h5>
                <span class="badge bg-primary"><?= count($statuses) ?> trạng thái</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="statusTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px"></th>
                                <th>Trạng thái</th>
                                <th>Mã (slug)</th>
                                <th>Màu</th>
                                <th>Icon</th>
                                <th>Mặc định</th>
                                <th>Số KH</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="sortableStatuses">
                            <?php foreach ($statuses as $s):
                                $contactCount = \Core\Database::fetch(
                                    "SELECT COUNT(*) as cnt FROM contacts WHERE status = ? AND tenant_id = ?",
                                    [$s['slug'], $_SESSION['tenant_id'] ?? 1]
                                );
                            ?>
                            <tr data-id="<?= $s['id'] ?>">
                                <td class="text-center" style="cursor:grab"><i class="ri-drag-move-line text-muted"></i></td>
                                <td>
                                    <span class="badge bg-<?= e($s['color']) ?>-subtle text-<?= e($s['color']) ?>">
                                        <i class="<?= e($s['icon']) ?> me-1"></i><?= e($s['name']) ?>
                                    </span>
                                </td>
                                <td><code><?= e($s['slug']) ?></code></td>
                                <td>
                                    <span class="d-inline-block rounded-circle" style="width:16px;height:16px;background:var(--vz-<?= e($s['color']) ?>)"></span>
                                    <?= e($s['color']) ?>
                                </td>
                                <td><i class="<?= e($s['icon']) ?> fs-16"></i></td>
                                <td>
                                    <?php if ($s['is_default']): ?>
                                        <span class="badge bg-success">Mặc định</span>
                                    <?php else: ?>
                                        <form method="POST" action="<?= url('settings/contact-statuses/' . $s['id'] . '/default') ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-soft-secondary py-0 px-2">Đặt mặc định</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-secondary-subtle text-secondary"><?= $contactCount['cnt'] ?? 0 ?></span></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-soft-primary edit-status-btn"
                                            data-id="<?= $s['id'] ?>"
                                            data-name="<?= e($s['name']) ?>"
                                            data-color="<?= e($s['color']) ?>"
                                            data-icon="<?= e($s['icon']) ?>">
                                            <i class="ri-pencil-line me-1"></i> Sửa
                                        </button>
                                        <?php if (!$s['is_default']): ?>
                                            <form method="POST" action="<?= url('settings/contact-statuses/' . $s['id'] . '/delete') ?>" data-confirm="Xóa trạng thái <?= e($s['name']) ?>?">
                                                <?= csrf_field() ?>
                                                <button class="btn btn-soft-danger"><i class="ri-delete-bin-line me-1"></i> Xóa</button>
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

    <!-- Right: Add/Edit Form -->
    <div class="col-xl-4">
        <!-- Add New -->
        <div class="card" id="addCard">
            <div class="card-header">
                <h5 class="card-title mb-0">Thêm trạng thái mới</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('settings/contact-statuses/store') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Tên trạng thái <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="VD: Đang chăm sóc" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Màu sắc</label>
                        <select name="color" class="form-select">
                            <?php
                            $colors = ['primary'=>'Xanh dương','success'=>'Xanh lá','info'=>'Xanh nhạt','warning'=>'Vàng','danger'=>'Đỏ','secondary'=>'Xám','dark'=>'Đen'];
                            foreach ($colors as $val => $label): ?>
                                <option value="<?= $val ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="mt-2 d-flex gap-2">
                            <?php foreach ($colors as $val => $label): ?>
                                <span class="badge bg-<?= $val ?>" style="cursor:pointer" onclick="this.closest('.mb-3').querySelector('select').value='<?= $val ?>'"><?= $label ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon</label>
                        <select name="icon" class="form-select">
                            <?php
                            $icons = [
                                'ri-user-add-line'=>'Người mới','ri-phone-line'=>'Điện thoại','ri-star-line'=>'Ngôi sao',
                                'ri-checkbox-circle-line'=>'Hoàn thành','ri-close-circle-line'=>'Đóng','ri-time-line'=>'Đồng hồ',
                                'ri-heart-line'=>'Tim','ri-fire-line'=>'Lửa','ri-circle-line'=>'Tròn',
                                'ri-shield-check-line'=>'Bảo vệ','ri-vip-crown-line'=>'VIP','ri-mail-line'=>'Email',
                                'ri-hand-heart-line'=>'Chăm sóc','ri-eye-line'=>'Theo dõi','ri-refresh-line'=>'Quay lại',
                            ];
                            foreach ($icons as $val => $label): ?>
                                <option value="<?= $val ?>"><i class="<?= $val ?>"></i> <?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="mt-2 d-flex gap-2 flex-wrap">
                            <?php foreach ($icons as $val => $label): ?>
                                <span class="border rounded p-1 px-2" style="cursor:pointer" onclick="this.closest('.mb-3').querySelector('select').value='<?= $val ?>'" title="<?= $label ?>">
                                    <i class="<?= $val ?> fs-16"></i>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="ri-add-line me-1"></i> Thêm trạng thái</button>
                </form>
            </div>
        </div>

        <!-- Edit (hidden by default) -->
        <div class="card d-none" id="editCard">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Sửa trạng thái</h5>
                <button class="btn btn-soft-secondary py-0 px-2" onclick="document.getElementById('editCard').classList.add('d-none');document.getElementById('addCard').classList.remove('d-none');">Hủy</button>
            </div>
            <div class="card-body">
                <form method="POST" id="editForm">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Tên trạng thái <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="editName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Màu sắc</label>
                        <select name="color" id="editColor" class="form-select">
                            <?php foreach ($colors as $val => $label): ?>
                                <option value="<?= $val ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon</label>
                        <select name="icon" id="editIcon" class="form-select">
                            <?php foreach ($icons as $val => $label): ?>
                                <option value="<?= $val ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="ri-check-line me-1"></i> Cập nhật</button>
                </form>
            </div>
        </div>

        <!-- Info -->
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-2"><i class="ri-information-line me-1"></i> Hướng dẫn</h6>
                <ul class="text-muted mb-0" style="padding-left:16px">
                    <li>Kéo thả để sắp xếp thứ tự hiển thị</li>
                    <li>Trạng thái mặc định sẽ được gán tự động cho KH mới</li>
                    <li>Không thể xóa trạng thái đang có KH sử dụng</li>
                    <li>Mã (slug) được tạo tự động từ tên</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Edit button handler
document.querySelectorAll('.edit-status-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.dataset.id;
        document.getElementById('editForm').action = '<?= url('settings/contact-statuses/') ?>' + id + '/update';
        document.getElementById('editName').value = this.dataset.name;
        document.getElementById('editColor').value = this.dataset.color;
        document.getElementById('editIcon').value = this.dataset.icon;
        document.getElementById('editCard').classList.remove('d-none');
        document.getElementById('addCard').classList.add('d-none');
        document.getElementById('editName').focus();
    });
});

// Drag-drop reorder
(function() {
    var tbody = document.getElementById('sortableStatuses');
    if (!tbody) return;

    var dragEl = null;

    tbody.querySelectorAll('tr').forEach(function(row) {
        var handle = row.querySelector('.ri-drag-move-line');
        if (!handle) return;

        handle.closest('td').addEventListener('mousedown', function() {
            dragEl = row;
            row.style.opacity = '0.5';
        });
    });

    tbody.addEventListener('dragover', function(e) { e.preventDefault(); });

    document.addEventListener('mouseup', function() {
        if (dragEl) {
            dragEl.style.opacity = '';
            // Save order
            var ids = [];
            tbody.querySelectorAll('tr').forEach(function(r) { ids.push(r.dataset.id); });
            fetch('<?= url('settings/contact-statuses/reorder') ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: '_token=<?= $_SESSION['csrf_token'] ?? '' ?>&' + ids.map(function(id, i) { return 'ids[' + i + ']=' + id; }).join('&')
            });
            dragEl = null;
        }
    });
})();
</script>
