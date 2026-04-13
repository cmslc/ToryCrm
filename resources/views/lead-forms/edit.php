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

            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Cài đặt</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nút gửi</label>
                            <input type="text" class="form-control" name="button_text" value="<?= e($form['settings']['button_text'] ?? 'Gửi') ?>">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Màu nút</label>
                            <input type="color" class="form-control form-control-color" name="button_color" value="<?= e($form['settings']['button_color'] ?? '#405189') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tự gán cho</label>
                            <select name="auto_assign" class="form-select">
                                <option value="">Không gán</option>
                                <?php $users = \Core\Database::fetchAll("SELECT id, name FROM users WHERE is_active = 1 ORDER BY name"); ?>
                                <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= ($form['settings']['auto_assign'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" <?= $form['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="isActive">Kích hoạt</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lời cảm ơn</label>
                        <input type="text" class="form-control" name="thank_you_message" value="<?= e($form['settings']['thank_you_message'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Giao diện</label>
                        <div class="d-flex gap-2">
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
                            <label class="d-block" style="flex:1">
                                <input type="radio" name="form_style" value="<?= $key ?>" class="d-none" <?= $key === $currentStyle ? 'checked' : '' ?>>
                                <div class="border rounded p-2 text-center style-option" style="cursor:pointer">
                                    <div class="rounded mb-1" style="height:30px;background:<?= $st['bg'] ?>;border:<?= $st['border'] ?>;border-radius:<?= $st['radius'] ?>"></div>
                                    <small class="fw-medium fs-11"><?= $st['name'] ?></small>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <style>.style-option { transition:.2s } input[name=form_style]:checked + .style-option { border-color:#405189!important; box-shadow:0 0 0 2px #40518944 }</style>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-save-line me-1"></i> Cập nhật</button>
                        <a href="<?= url('lead-forms/' . $form['id'] . '/embed') ?>" class="btn btn-soft-success"><i class="ri-code-line me-1"></i> Mã nhúng</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Live Preview -->
            <div class="card" style="position:sticky;top:80px">
                <div class="card-header"><h5 class="card-title mb-0"><i class="ri-eye-line me-2"></i> Xem trước</h5></div>
                <div class="card-body p-3" id="livePreview">
                    <div style="max-width:100%;padding:24px" id="previewBox">
                        <div style="font-size:18px;font-weight:600;margin-bottom:16px" id="previewTitle"><?= e($form['name']) ?></div>
                        <div id="previewFields"></div>
                        <button style="width:100%;padding:10px;border:none;color:#fff;font-weight:600;cursor:default" id="previewBtn"><?= e($form['settings']['button_text'] ?? 'Gửi') ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
var styleThemes = {
    classic: {bg:'#fff',bodyBg:'#f8f9fa',text:'#333',inputBg:'#fff',inputBorder:'#ddd',radius:'8px',shadow:'0 2px 12px rgba(0,0,0,0.08)'},
    modern: {bg:'#f8f9fa',bodyBg:'#fff',text:'#333',inputBg:'#fff',inputBorder:'#e9ecef',radius:'16px',shadow:'0 8px 32px rgba(0,0,0,0.06)'},
    dark: {bg:'#1a1d21',bodyBg:'#111315',text:'#e0e0e0',inputBg:'#2a2d32',inputBorder:'#3a3d42',radius:'12px',shadow:'0 4px 24px rgba(0,0,0,0.3)'},
    gradient: {bg:'transparent',bodyBg:'linear-gradient(135deg,#667eea,#764ba2)',text:'#fff',inputBg:'rgba(255,255,255,0.15)',inputBorder:'rgba(255,255,255,0.3)',radius:'16px',shadow:'none'},
    minimal: {bg:'#fff',bodyBg:'#fff',text:'#000',inputBg:'#fff',inputBorder:'#000',radius:'0',shadow:'none'}
};
function updatePreview() {
    var title = document.querySelector('[name=name]').value || 'Form';
    var btnText = document.querySelector('[name=button_text]').value || 'Gửi';
    var btnColor = document.querySelector('[name=button_color]').value || '#405189';
    var style = document.querySelector('[name=form_style]:checked')?.value || 'classic';
    var t = styleThemes[style];

    document.getElementById('previewTitle').textContent = title;
    document.getElementById('previewTitle').style.color = t.text;
    document.getElementById('previewBtn').textContent = btnText;
    document.getElementById('previewBtn').style.background = btnColor;
    document.getElementById('previewBtn').style.borderRadius = t.radius;

    var box = document.getElementById('previewBox');
    box.style.background = t.bg;
    box.style.color = t.text;
    box.style.borderRadius = t.radius;
    box.style.boxShadow = t.shadow;
    document.getElementById('livePreview').style.background = t.bodyBg;
    document.getElementById('livePreview').style.borderRadius = '0 0 6px 6px';

    var rows = document.querySelectorAll('.field-row');
    var html = '';
    rows.forEach(function(r) {
        var label = r.querySelector('[name="field_label[]"]')?.value || 'Field';
        var type = r.querySelector('[name="field_type[]"]')?.value || 'text';
        html += '<div style="margin-bottom:12px"><label style="display:block;font-size:13px;color:' + t.text + ';opacity:0.7;margin-bottom:4px">' + label + '</label>';
        if (type === 'textarea') {
            html += '<textarea style="width:100%;padding:8px 12px;border:1px solid ' + t.inputBorder + ';border-radius:' + t.radius + ';background:' + t.inputBg + ';color:' + t.text + ';resize:none;height:60px;outline:none" disabled></textarea>';
        } else {
            html += '<input type="text" style="width:100%;padding:8px 12px;border:1px solid ' + t.inputBorder + ';border-radius:' + t.radius + ';background:' + t.inputBg + ';color:' + t.text + ';outline:none" disabled placeholder="' + label + '">';
        }
        html += '</div>';
    });
    document.getElementById('previewFields').innerHTML = html;
}

document.querySelectorAll('[name=name],[name=button_text],[name=button_color],[name=form_style]').forEach(function(el) {
    el.addEventListener('input', updatePreview);
    el.addEventListener('change', updatePreview);
});

// Watch field changes
var observer = new MutationObserver(updatePreview);
observer.observe(document.getElementById('fieldsContainer'), {childList: true, subtree: true, characterData: true});
document.getElementById('fieldsContainer').addEventListener('input', updatePreview);

setTimeout(updatePreview, 100);

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
