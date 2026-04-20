<?php
$pageTitle = 'Sửa mẫu: ' . $template['name'];
$typeLabel = $template['type'] === 'quotation' ? 'báo giá' : 'hợp đồng';
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Sửa mẫu <?= $typeLabel ?>: <?= e($template['name']) ?></h4>
    <div class="d-flex gap-2">
        <a href="<?= url('settings/document-templates') ?>" class="btn btn-soft-secondary">Quay lại</a>
        <button type="submit" form="templateForm" class="btn btn-primary"><i class="ri-save-line me-1"></i> Lưu</button>
    </div>
</div>

<form method="POST" action="<?= url('settings/document-templates/' . $template['id'] . '/update') ?>" id="templateForm">
    <?= csrf_field() ?>

    <div class="row">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Thông tin mẫu</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tên mẫu <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="<?= e($template['name']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mô tả</label>
                            <input type="text" class="form-control" name="description" value="<?= e($template['description'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="d-flex gap-4">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_default" value="1" id="isDefault" <?= $template['is_default'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="isDefault">Mẫu mặc định</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_active" value="1" id="isActive" <?= $template['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="isActive">Hoạt động</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Nội dung mẫu</h5></div>
                <div class="card-body">
                    <textarea name="content" id="templateContent" rows="20"><?= e($template['content'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Biến có thể dùng</h5></div>
                <div class="card-body p-2">
                    <div class="list-group list-group-flush" style="max-height:500px;overflow-y:auto">
                        <?php foreach ($variables as $var => $label): ?>
                        <div class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center" style="cursor:pointer" onclick="insertVariable('<?= $var ?>')">
                            <div>
                                <code class="text-primary" style="font-size:12px"><?= $var ?></code><br>
                                <small class="text-muted"><?= e($label) ?></small>
                            </div>
                            <i class="ri-add-circle-line text-muted"></i>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <small class="text-muted">
                        Loại: <span class="badge bg-<?= $template['type'] === 'quotation' ? 'primary' : 'success' ?>"><?= $typeLabel ?></span><br>
                        Tạo: <?= date('d/m/Y H:i', strtotime($template['created_at'])) ?><br>
                        Cập nhật: <?= date('d/m/Y H:i', strtotime($template['updated_at'])) ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
</form>

<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
<script>
CKEDITOR.replace('templateContent', {
    language: 'vi',
    height: 400,
    allowedContent: true,
    extraPlugins: 'font,colorbutton,justify,pagebreak,find',
});

function insertVariable(varName) {
    var editor = CKEDITOR.instances.templateContent;
    if (editor) {
        editor.insertText(varName);
        editor.focus();
    }
}
</script>
