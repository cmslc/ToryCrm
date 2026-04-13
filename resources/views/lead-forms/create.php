<?php $pageTitle = 'Tạo Lead Form'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Tạo Lead Form</h4>
    <a href="<?= url('lead-forms') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<form method="POST" action="<?= url('lead-forms/store') ?>">
    <?= csrf_field() ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Thông tin form</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tên form <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required placeholder="VD: Form liên hệ">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mô tả</label>
                            <input type="text" class="form-control" name="description" placeholder="Mô tả ngắn về form">
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
                    <div class="field-row row mb-3 align-items-end">
                        <div class="col-md-3"><label class="form-label">Tên field</label><input type="text" class="form-control" name="field_name[]" value="name" required></div>
                        <div class="col-md-3"><label class="form-label">Nhãn</label><input type="text" class="form-control" name="field_label[]" value="Họ tên"></div>
                        <div class="col-md-2"><label class="form-label">Loại</label><select name="field_type[]" class="form-select"><option value="text" selected>Text</option><option value="email">Email</option><option value="tel">Phone</option><option value="textarea">Textarea</option><option value="select">Select</option><option value="number">Number</option></select></div>
                        <div class="col-md-2"><label class="form-label">Bắt buộc</label><div><input type="checkbox" class="form-check-input" name="field_required[]" value="0" checked> Có</div></div>
                        <div class="col-md-2"><button type="button" class="btn btn-soft-danger" onclick="this.closest('.field-row').remove()"><i class="ri-delete-bin-line"></i></button></div>
                    </div>
                    <div class="field-row row mb-3 align-items-end">
                        <div class="col-md-3"><input type="text" class="form-control" name="field_name[]" value="email"></div>
                        <div class="col-md-3"><input type="text" class="form-control" name="field_label[]" value="Email"></div>
                        <div class="col-md-2"><select name="field_type[]" class="form-select"><option value="text">Text</option><option value="email" selected>Email</option><option value="tel">Phone</option><option value="textarea">Textarea</option><option value="select">Select</option><option value="number">Number</option></select></div>
                        <div class="col-md-2"><div><input type="checkbox" class="form-check-input" name="field_required[]" value="1" checked> Có</div></div>
                        <div class="col-md-2"><button type="button" class="btn btn-soft-danger" onclick="this.closest('.field-row').remove()"><i class="ri-delete-bin-line"></i></button></div>
                    </div>
                    <div class="field-row row mb-3 align-items-end">
                        <div class="col-md-3"><input type="text" class="form-control" name="field_name[]" value="phone"></div>
                        <div class="col-md-3"><input type="text" class="form-control" name="field_label[]" value="Số điện thoại"></div>
                        <div class="col-md-2"><select name="field_type[]" class="form-select"><option value="text">Text</option><option value="email">Email</option><option value="tel" selected>Phone</option><option value="textarea">Textarea</option><option value="select">Select</option><option value="number">Number</option></select></div>
                        <div class="col-md-2"><div><input type="checkbox" class="form-check-input" name="field_required[]" value="2"> Có</div></div>
                        <div class="col-md-2"><button type="button" class="btn btn-soft-danger" onclick="this.closest('.field-row').remove()"><i class="ri-delete-bin-line"></i></button></div>
                    </div>
                    <div class="field-row row mb-3 align-items-end">
                        <div class="col-md-3"><input type="text" class="form-control" name="field_name[]" value="message"></div>
                        <div class="col-md-3"><input type="text" class="form-control" name="field_label[]" value="Nội dung"></div>
                        <div class="col-md-2"><select name="field_type[]" class="form-select"><option value="text">Text</option><option value="email">Email</option><option value="tel">Phone</option><option value="textarea" selected>Textarea</option><option value="select">Select</option><option value="number">Number</option></select></div>
                        <div class="col-md-2"><div><input type="checkbox" class="form-check-input" name="field_required[]" value="3"> Có</div></div>
                        <div class="col-md-2"><button type="button" class="btn btn-soft-danger" onclick="this.closest('.field-row').remove()"><i class="ri-delete-bin-line"></i></button></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Cài đặt</h5></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Lời cảm ơn</label>
                        <textarea class="form-control" name="thank_you_message" rows="2">Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất.</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nút gửi</label>
                        <input type="text" class="form-control" name="button_text" value="Gửi">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Màu nút</label>
                        <input type="color" class="form-control form-control-color" name="button_color" value="#405189">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tự gán cho</label>
                        <select name="auto_assign" class="form-select">
                            <option value="">Không gán</option>
                            <?php $users = \Core\Database::fetchAll("SELECT id, name FROM users WHERE is_active = 1 ORDER BY name"); ?>
                            <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100"><i class="ri-save-line me-1"></i> Tạo form</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
var fieldIdx = 4;
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
