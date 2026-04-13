<?php $pageTitle = 'Sửa form - ' . e($form['name']); ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Sửa form: <?= e($form['name']) ?></h4>
    <a href="<?= url('lead-forms') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<form method="POST" action="<?= url('lead-forms/' . $form['id'] . '/update') ?>">
    <?= csrf_field() ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Thông tin form</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tên form <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="<?= e($form['name']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mô tả</label>
                            <input type="text" class="form-control" name="description" value="<?= e($form['description'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Các trường</h5>
                    <button type="button" class="btn btn-soft-primary" onclick="addField()"><i class="ri-add-line me-1"></i> Thêm trường</button>
                </div>
                <div class="card-body" id="fieldsContainer">
                    <?php foreach ($form['fields'] as $i => $f): ?>
                    <div class="field-row row mb-3 align-items-end">
                        <div class="col-md-3"><?php if ($i === 0): ?><label class="form-label">Tên field</label><?php endif; ?><input type="text" class="form-control" name="field_name[]" value="<?= e($f['name']) ?>" required></div>
                        <div class="col-md-3"><?php if ($i === 0): ?><label class="form-label">Nhãn</label><?php endif; ?><input type="text" class="form-control" name="field_label[]" value="<?= e($f['label']) ?>"></div>
                        <div class="col-md-2"><?php if ($i === 0): ?><label class="form-label">Loại</label><?php endif; ?>
                            <select name="field_type[]" class="form-select">
                                <option value="text" <?= ($f['type'] ?? '') === 'text' ? 'selected' : '' ?>>Text</option>
                                <option value="email" <?= ($f['type'] ?? '') === 'email' ? 'selected' : '' ?>>Email</option>
                                <option value="tel" <?= ($f['type'] ?? '') === 'tel' ? 'selected' : '' ?>>Phone</option>
                                <option value="textarea" <?= ($f['type'] ?? '') === 'textarea' ? 'selected' : '' ?>>Textarea</option>
                                <option value="select" <?= ($f['type'] ?? '') === 'select' ? 'selected' : '' ?>>Select</option>
                                <option value="number" <?= ($f['type'] ?? '') === 'number' ? 'selected' : '' ?>>Number</option>
                            </select>
                        </div>
                        <div class="col-md-2"><?php if ($i === 0): ?><label class="form-label">Bắt buộc</label><?php endif; ?><div><input type="checkbox" class="form-check-input" name="field_required[]" value="<?= $i ?>" <?= $f['required'] ? 'checked' : '' ?>> Có</div></div>
                        <div class="col-md-2"><button type="button" class="btn btn-soft-danger" onclick="this.closest('.field-row').remove()"><i class="ri-delete-bin-line"></i></button></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Cài đặt</h5></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Lời cảm ơn</label>
                        <textarea class="form-control" name="thank_you_message" rows="2"><?= e($form['settings']['thank_you_message'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nút gửi</label>
                        <input type="text" class="form-control" name="button_text" value="<?= e($form['settings']['button_text'] ?? 'Gửi') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Giao diện</label>
                        <div class="row g-2">
                            <?php
                            $styles = [
                                'classic' => ['name'=>'Classic','bg'=>'#fff','border'=>'1px solid #ddd','radius'=>'8px'],
                                'modern' => ['name'=>'Modern','bg'=>'#f8f9fa','border'=>'none','radius'=>'16px'],
                                'dark' => ['name'=>'Dark','bg'=>'#1a1d21','border'=>'none','radius'=>'12px'],
                                'gradient' => ['name'=>'Gradient','bg'=>'linear-gradient(135deg,#667eea,#764ba2)','border'=>'none','radius'=>'16px'],
                                'minimal' => ['name'=>'Minimal','bg'=>'#fff','border'=>'2px solid #000','radius'=>'0'],
                            ];
                            $currentStyle = $form['settings']['form_style'] ?? 'classic';
                            foreach ($styles as $key => $st): ?>
                            <div class="col-4">
                                <label class="d-block">
                                    <input type="radio" name="form_style" value="<?= $key ?>" class="d-none" <?= $key === $currentStyle ? 'checked' : '' ?>>
                                    <div class="border rounded p-2 text-center style-option" style="cursor:pointer">
                                        <div class="rounded mb-1" style="height:40px;background:<?= $st['bg'] ?>;border:<?= $st['border'] ?>;border-radius:<?= $st['radius'] ?>"></div>
                                        <small class="fw-medium"><?= $st['name'] ?></small>
                                    </div>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <style>.style-option { transition:.2s } input[name=form_style]:checked + .style-option { border-color:#405189!important; box-shadow:0 0 0 2px #40518944 }</style>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Màu nút</label>
                        <input type="color" class="form-control form-control-color" name="button_color" value="<?= e($form['settings']['button_color'] ?? '#405189') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tự gán cho</label>
                        <select name="auto_assign" class="form-select">
                            <option value="">Không gán</option>
                            <?php $users = \Core\Database::fetchAll("SELECT id, name FROM users WHERE is_active = 1 ORDER BY name"); ?>
                            <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= ($form['settings']['auto_assign'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" <?= $form['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="isActive">Kích hoạt form</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2"><i class="ri-save-line me-1"></i> Cập nhật</button>
                    <a href="<?= url('lead-forms/' . $form['id'] . '/embed') ?>" class="btn btn-soft-success w-100"><i class="ri-code-line me-1"></i> Mã nhúng</a>
                </div>
            </div>

            <!-- Live Preview -->
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1"><i class="ri-eye-line me-2"></i> Xem trước</h5>
                    <button type="button" class="btn btn-soft-info btn-icon" onclick="refreshPreview()" title="Làm mới"><i class="ri-refresh-line"></i></button>
                </div>
                <div class="card-body p-0">
                    <iframe id="formPreview" src="<?= url('form/' . $form['slug']) ?>" width="100%" height="450" frameborder="0" style="border:none;border-radius:0 0 6px 6px"></iframe>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
function refreshPreview() {
    document.getElementById('formPreview').src = '<?= url('form/' . $form['slug']) ?>?t=' + Date.now();
}
</script>

<script>
var fieldIdx = <?= count($form['fields']) ?>;
function addField() {
    var html = '<div class="field-row row mb-3 align-items-end">'
        + '<div class="col-md-3"><input type="text" class="form-control" name="field_name[]" placeholder="field_name" required></div>'
        + '<div class="col-md-3"><input type="text" class="form-control" name="field_label[]" placeholder="Nhãn hiển thị"></div>'
        + '<div class="col-md-2"><select name="field_type[]" class="form-select"><option value="text">Text</option><option value="email">Email</option><option value="tel">Phone</option><option value="textarea">Textarea</option><option value="select">Select</option><option value="number">Number</option></select></div>'
        + '<div class="col-md-2"><div><input type="checkbox" class="form-check-input" name="field_required[]" value="' + fieldIdx + '"> Có</div></div>'
        + '<div class="col-md-2"><button type="button" class="btn btn-soft-danger" onclick="this.closest(\'.field-row\').remove()"><i class="ri-delete-bin-line"></i></button></div>'
        + '</div>';
    document.getElementById('fieldsContainer').insertAdjacentHTML('beforeend', html);
    fieldIdx++;
}
</script>
