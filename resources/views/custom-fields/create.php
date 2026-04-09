<?php
    $isEdit = !empty($field);
    $pageTitle = $isEdit ? 'Sửa trường tùy chỉnh' : 'Tạo trường tùy chỉnh';
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0"><?= $pageTitle ?></h4>
    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="<?= url('custom-fields') ?>">Trường tùy chỉnh</a></li>
        <li class="breadcrumb-item active"><?= $isEdit ? 'Sửa' : 'Tạo mới' ?></li>
    </ol>
</div>

<form method="POST" action="<?= url($isEdit ? 'custom-fields/' . $field['id'] . '/update' : 'custom-fields/store') ?>">
    <?= csrf_field() ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Thông tin trường</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Module <span class="text-danger">*</span></label>
                            <select name="module" class="form-select" <?= $isEdit ? 'disabled' : 'required' ?>>
                                <?php foreach ($modules as $key => $label): ?>
                                    <option value="<?= $key ?>" <?= ($isEdit ? $field['module'] : ($module ?? 'contacts')) === $key ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($isEdit): ?>
                                <input type="hidden" name="module" value="<?= e($field['module']) ?>">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Loại trường <span class="text-danger">*</span></label>
                            <select name="field_type" class="form-select" id="fieldType" required>
                                <?php
                                    $types = [
                                        'text' => 'Văn bản', 'number' => 'Số', 'email' => 'Email', 'phone' => 'Điện thoại',
                                        'url' => 'URL', 'textarea' => 'Văn bản dài', 'select' => 'Danh sách thả xuống',
                                        'multi_select' => 'Chọn nhiều', 'checkbox' => 'Checkbox', 'radio' => 'Radio',
                                        'date' => 'Ngày', 'datetime' => 'Ngày giờ', 'file' => 'Tệp tin',
                                        'color' => 'Màu sắc', 'currency' => 'Tiền tệ',
                                    ];
                                    $currentType = $isEdit ? $field['field_type'] : 'text';
                                ?>
                                <?php foreach ($types as $tKey => $tLabel): ?>
                                    <option value="<?= $tKey ?>" <?= $currentType === $tKey ? 'selected' : '' ?>><?= $tLabel ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tên trường <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="field_label" id="fieldLabel"
                               value="<?= e($isEdit ? $field['field_label'] : '') ?>"
                               required placeholder="VD: Mã số thuế, Số hợp đồng...">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Khóa trường <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="field_key" id="fieldKey"
                               value="<?= e($isEdit ? $field['field_key'] : '') ?>"
                               <?= $isEdit ? 'readonly' : 'required' ?>
                               placeholder="VD: ma_so_thue">
                        <small class="text-muted">Tự động tạo từ tên trường. Chỉ cho phép chữ thường, số, gạch dưới.</small>
                    </div>

                    <div class="mb-3" id="optionsGroup" style="display:none">
                        <label class="form-label">Các tùy chọn</label>
                        <textarea class="form-control" name="options" rows="4" placeholder="Mỗi tùy chọn một dòng"><?= e($isEdit ? ($field['options'] ?? '') : '') ?></textarea>
                        <small class="text-muted">Áp dụng cho loại: Danh sách, Chọn nhiều, Radio. Mỗi giá trị một dòng.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Giá trị mặc định</label>
                            <input type="text" class="form-control" name="default_value"
                                   value="<?= e($isEdit ? ($field['default_value'] ?? '') : '') ?>"
                                   placeholder="Để trống nếu không có">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Placeholder</label>
                            <input type="text" class="form-control" name="placeholder"
                                   value="<?= e($isEdit ? ($field['placeholder'] ?? '') : '') ?>"
                                   placeholder="Gợi ý hiển thị trong ô nhập">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="is_required" id="isRequired" value="1"
                                       <?= ($isEdit && $field['is_required']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="isRequired">Bắt buộc</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="is_filterable" id="isFilterable" value="1"
                                       <?= ($isEdit && $field['is_filterable']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="isFilterable">Có thể lọc</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="show_in_list" id="showInList" value="1"
                                       <?= ($isEdit && $field['show_in_list']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="showInList">Hiển thị trong danh sách</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-save-line me-1"></i> Lưu</button>
                    <a href="<?= url('custom-fields') ?>" class="btn btn-soft-secondary">Hủy</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="card-title mb-0">Hướng dẫn</h6></div>
                <div class="card-body">
                    <ul class="list-unstyled text-muted mb-0">
                        <li class="mb-2"><i class="ri-information-line text-primary me-1"></i> Khóa trường là duy nhất trong mỗi module</li>
                        <li class="mb-2"><i class="ri-information-line text-primary me-1"></i> Không thể đổi khóa sau khi tạo</li>
                        <li class="mb-2"><i class="ri-information-line text-primary me-1"></i> Trường bắt buộc sẽ yêu cầu nhập khi tạo/sửa bản ghi</li>
                        <li><i class="ri-information-line text-primary me-1"></i> Bật "Hiển thị trong danh sách" để hiện cột trong bảng</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var labelInput = document.getElementById('fieldLabel');
    var keyInput = document.getElementById('fieldKey');
    var typeSelect = document.getElementById('fieldType');
    var optionsGroup = document.getElementById('optionsGroup');

    // Auto-generate key from label
    <?php if (!$isEdit): ?>
    labelInput.addEventListener('input', function() {
        var val = this.value.toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/đ/g, 'd').replace(/Đ/g, 'd')
            .replace(/[^a-z0-9\s]/g, '')
            .replace(/\s+/g, '_')
            .replace(/_+/g, '_')
            .replace(/^_|_$/g, '');
        keyInput.value = val;
    });
    <?php endif; ?>

    // Show/hide options textarea
    function toggleOptions() {
        var optionTypes = ['select', 'multi_select', 'radio'];
        optionsGroup.style.display = optionTypes.includes(typeSelect.value) ? 'block' : 'none';
    }

    typeSelect.addEventListener('change', toggleOptions);
    toggleOptions();
});
</script>
