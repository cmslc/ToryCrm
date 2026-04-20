<?php $pageTitle = 'Định nghĩa dữ liệu - ' . $moduleInfo['label']; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-2">
        <a href="<?= url('settings/data-definition') ?>" class="text-primary"><i class="ri-arrow-left-line fs-18"></i></a>
        <div>
            <nav><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="<?= url('settings/data-definition') ?>">Định nghĩa dữ liệu</a></li><li class="breadcrumb-item active"><?= e($moduleInfo['label']) ?></li></ol></nav>
            <h4 class="mb-0"><?= e($moduleInfo['label']) ?></h4>
        </div>
    </div>
    <a href="<?= url('custom-fields?module=' . $module) ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i>Thêm trường</a>
</div>

<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabActive">Đang sử dụng <span class="badge bg-primary-subtle text-primary ms-1" id="activeCount">0</span></a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabSystem">Hệ thống <span class="badge bg-secondary-subtle text-secondary ms-1" id="systemCount">0</span></a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabCustom">Tùy chỉnh <span class="badge bg-info-subtle text-info ms-1" id="customCount">0</span></a></li>
        </ul>
    </div>
    <div class="card-body p-2">
        <div class="tab-content">
            <!-- Active fields -->
            <div class="tab-pane active" id="tabActive">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:50px">#</th>
                                <th>Tên thuộc tính</th>
                                <th>Mã thuộc tính</th>
                                <th>Kiểu dữ liệu</th>
                                <th class="text-center">Bắt buộc</th>
                                <th class="text-center">Kiểm tra trùng</th>
                                <th class="text-center">Hiển thị</th>
                                <th class="text-center" style="width:100px">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $activeCount = 0; $systemCount = 0; $customCount = 0;
                            $idx = 0;
                            foreach ($fields as $f):
                                if ($f['is_system']) { $systemCount++; continue; }
                                if ($f['is_custom']) { $customCount++; }
                                $activeCount++;
                                $idx++;
                            ?>
                            <tr>
                                <td class="text-muted"><?= $idx ?></td>
                                <td>
                                    <span class="fw-medium"><?= e($f['label']) ?></span>
                                    <?php if ($f['is_custom']): ?><span class="badge bg-info-subtle text-info ms-1">Tùy chỉnh</span><?php endif; ?>
                                </td>
                                <td><code class="fs-12"><?= e($f['name']) ?></code></td>
                                <td>
                                    <span class="badge bg-light text-dark"><?= e($f['type']) ?></span>
                                    <span class="text-muted fs-11 ms-1"><?= e($f['raw_type']) ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($f['required']): ?>
                                    <span class="badge bg-danger">Có</span>
                                    <?php else: ?>
                                    <span class="text-muted">Không</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($f['check_duplicate'])): ?>
                                    <span class="badge bg-warning">Có</span>
                                    <?php else: ?>
                                    <span class="text-muted">Không</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center mb-0">
                                        <input class="form-check-input toggle-show-in-list" type="checkbox" data-field="<?= e($f['name']) ?>" <?= ($f['show_in_list'] ?? true) ? 'checked' : '' ?>>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-ghost-primary btn-icon btn-sm edit-field-btn"
                                        data-name="<?= e($f['name']) ?>"
                                        data-label="<?= e($f['label']) ?>"
                                        data-required="<?= $f['required'] ? '1' : '0' ?>"
                                        data-duplicate="<?= !empty($f['check_duplicate']) ? '1' : '0' ?>"
                                        data-default="<?= e($f['default_value'] ?? '') ?>"
                                        data-custom="<?= $f['is_custom'] ? '1' : '0' ?>"
                                        data-cfid="<?= $f['custom_field_id'] ?? '' ?>"
                                        title="Sửa"><i class="ri-pencil-line"></i></button>
                                    <?php if ($f['is_custom']): ?>
                                    <form method="POST" action="<?= url('settings/data-definition/' . $module . '/delete-field') ?>" class="d-inline" data-confirm="Xóa trường <?= e($f['label']) ?>?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="field_id" value="<?= $f['custom_field_id'] ?? '' ?>">
                                        <button type="submit" class="btn btn-ghost-danger btn-icon btn-sm" title="Xóa"><i class="ri-delete-bin-line"></i></button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- System fields -->
            <div class="tab-pane" id="tabSystem">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:50px">#</th>
                                <th>Tên thuộc tính</th>
                                <th>Mã thuộc tính</th>
                                <th>Kiểu dữ liệu</th>
                                <th>Giá trị mặc định</th>
                                <th class="text-center">Hiển thị</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $sidx = 0; foreach ($fields as $f):
                                if (!$f['is_system']) continue;
                                $sidx++;
                            ?>
                            <tr>
                                <td class="text-muted"><?= $sidx ?></td>
                                <td><span class="fw-medium"><?= e($f['label']) ?></span></td>
                                <td><code class="fs-12"><?= e($f['name']) ?></code></td>
                                <td><span class="badge bg-light text-dark"><?= e($f['type']) ?></span></td>
                                <td class="text-muted fs-13"><?= $f['default'] !== null ? e($f['default']) : '-' ?></td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center mb-0">
                                        <input class="form-check-input toggle-show-in-list" type="checkbox" data-field="<?= e($f['name']) ?>" <?= ($f['show_in_list'] ?? false) ? 'checked' : '' ?>>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Custom fields -->
            <div class="tab-pane" id="tabCustom">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:50px">#</th>
                                <th>Tên thuộc tính</th>
                                <th>Mã thuộc tính</th>
                                <th>Kiểu dữ liệu</th>
                                <th class="text-center">Bắt buộc</th>
                                <th>Giá trị mặc định</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $cidx = 0; foreach ($fields as $f):
                                if (!$f['is_custom']) continue;
                                $cidx++;
                            ?>
                            <tr>
                                <td class="text-muted"><?= $cidx ?></td>
                                <td><span class="fw-medium"><?= e($f['label']) ?></span></td>
                                <td><code class="fs-12"><?= e($f['name']) ?></code></td>
                                <td><span class="badge bg-info-subtle text-info"><?= e($f['type']) ?></span></td>
                                <td class="text-center">
                                    <?= $f['required'] ? '<span class="badge bg-danger">Có</span>' : '<span class="text-muted">Không</span>' ?>
                                </td>
                                <td class="text-muted fs-13"><?= $f['default'] !== null ? e($f['default']) : '-' ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if ($cidx === 0): ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">Chưa có trường tùy chỉnh. <a href="<?= url('custom-fields?module=' . $module) ?>">Thêm mới</a></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="card-title mb-3"><i class="ri-information-line me-1 text-info"></i>Ghi chú</h5>
        <div class="row">
            <div class="col-md-6">
                <ul class="list-unstyled mb-0 vstack gap-2">
                    <li><i class="ri-checkbox-circle-line text-success me-1"></i><strong>Đang sử dụng</strong> - Các trường người dùng nhìn thấy và nhập liệu trên form. Admin có thể sửa tên hiển thị, đặt bắt buộc, bật kiểm tra trùng.</li>
                    <li><i class="ri-settings-3-line text-secondary me-1"></i><strong>Hệ thống</strong> - Các trường được hệ thống tự động quản lý (ID, mật khẩu, token, ngày tạo...). Không nên chỉnh sửa.</li>
                    <li><i class="ri-paint-brush-line text-info me-1"></i><strong>Tùy chỉnh</strong> - Các trường do admin tạo thêm qua "Trường tùy chỉnh". Có thể sửa và xóa.</li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="list-unstyled mb-0 vstack gap-2">
                    <li><i class="ri-error-warning-line text-danger me-1"></i><strong>Bắt buộc</strong> - Trường bắt buộc nhập khi tạo/sửa dữ liệu. Nếu bỏ trống sẽ báo lỗi.</li>
                    <li><i class="ri-file-copy-line text-warning me-1"></i><strong>Kiểm tra trùng</strong> - Khi bật, hệ thống sẽ cảnh báo nếu giá trị đã tồn tại (VD: email, SĐT trùng).</li>
                    <li><i class="ri-add-circle-line text-primary me-1"></i>Thêm trường mới bằng nút <strong>"Thêm trường"</strong> ở góc trên phải.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Modal sửa trường -->
<div class="modal fade" id="editFieldModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('settings/data-definition/' . $module . '/update-field') ?>" id="editFieldForm">
            <?= csrf_field() ?>
            <input type="hidden" name="field_name" id="efName">
            <input type="hidden" name="is_custom" id="efIsCustom">
            <input type="hidden" name="custom_field_id" id="efCfId">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa thuộc tính</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Mã thuộc tính</label>
                        <input type="text" class="form-control" id="efCode" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tên hiển thị <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="label" id="efLabel" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="required" value="1" id="efRequired">
                            <label class="form-check-label" for="efRequired">Bắt buộc</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="check_duplicate" value="1" id="efDuplicate">
                            <label class="form-check-label" for="efDuplicate">Kiểm tra trùng dữ liệu</label>
                        </div>
                        <small class="text-muted">Cảnh báo khi giá trị đã tồn tại trong hệ thống</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Giá trị mặc định</label>
                        <textarea class="form-control" name="default_value" id="efDefault" rows="2" placeholder="Giá trị tự động điền khi tạo mới"></textarea>
                        <small class="text-muted">Để trống nếu không cần giá trị mặc định</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-soft-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i>Lưu</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Edit field modal
document.querySelectorAll('.edit-field-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('efName').value = this.dataset.name;
        document.getElementById('efCode').value = this.dataset.name;
        document.getElementById('efLabel').value = this.dataset.label;
        document.getElementById('efRequired').checked = this.dataset.required === '1';
        document.getElementById('efDuplicate').checked = this.dataset.duplicate === '1';
        document.getElementById('efDefault').value = this.dataset.default || '';
        document.getElementById('efIsCustom').value = this.dataset.custom;
        document.getElementById('efCfId').value = this.dataset.cfid;
        new bootstrap.Modal(document.getElementById('editFieldModal')).show();
    });
});

document.getElementById('activeCount').textContent = <?= $activeCount ?>;
document.getElementById('systemCount').textContent = <?= $systemCount ?>;
document.getElementById('customCount').textContent = <?= $customCount ?>;

// Toggle show in list
document.querySelectorAll('.toggle-show-in-list').forEach(function(cb) {
    cb.addEventListener('change', function() {
        var field = this.dataset.field;
        var isOn = this.checked;
        var label = this.closest('tr').querySelector('.fw-medium')?.textContent.trim() || field;
        fetch('<?= url('settings/data-definition/' . $module . '/toggle-show') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: '_token=<?= csrf_token() ?>&field_name=' + field + '&show=' + (isOn ? 1 : 0)
        }).then(function(r) { return r.json(); }).then(function(d) {
            if (d.success) {
                var toast = document.createElement('div');
                toast.className = 'position-fixed top-0 end-0 m-3 alert alert-' + (isOn ? 'success' : 'warning') + ' shadow fade show';
                toast.style.zIndex = 9999;
                toast.innerHTML = '<i class="ri-' + (isOn ? 'eye' : 'eye-off') + '-line me-1"></i>' + (isOn ? 'Đã hiện' : 'Đã ẩn') + ' cột <b>' + label + '</b>';
                document.body.appendChild(toast);
                setTimeout(function() { toast.remove(); }, 2000);
            }
        });
    });
});
</script>
