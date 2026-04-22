<?php
$pageTitle = 'Tạo mẫu ' . ($type === 'quotation' ? 'báo giá' : 'hợp đồng');
$typeLabel = $type === 'quotation' ? 'báo giá' : 'hợp đồng';
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Tạo mẫu <?= $typeLabel ?></h4>
    <div class="d-flex gap-2">
        <a href="<?= url('settings/document-templates') ?>" class="btn btn-soft-secondary">Quay lại</a>
        <button type="submit" form="templateForm" class="btn btn-primary"><i class="ri-save-line me-1"></i> Lưu</button>
    </div>
</div>

<form method="POST" action="<?= url('settings/document-templates/store') ?>" id="templateForm">
    <?= csrf_field() ?>
    <input type="hidden" name="type" value="<?= $type ?>">

    <div class="row">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Thông tin mẫu</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tên mẫu <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required placeholder="VD: Mẫu báo giá chuẩn">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mô tả</label>
                            <input type="text" class="form-control" name="description" placeholder="Mô tả ngắn về mẫu">
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_default" value="1" id="isDefault">
                            <label class="form-check-label" for="isDefault">Đặt làm mẫu mặc định</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Nội dung mẫu</h5></div>
                <div class="card-body">
                    <textarea name="content" id="templateContent" rows="20"><?= $type === 'contract' ? e('<h2 style="text-align:center">HỢP ĐỒNG KINH TẾ</h2><p><strong>Số:</strong> {{contract_number}}</p><p><strong>Bên A:</strong> {{company_name}}</p><p>Địa chỉ: {{company_address}} | ĐT: {{company_phone}} | MST: {{company_tax_code}}</p><p>Đại diện: {{company_representative}} - {{company_position}}</p><p><strong>Bên B:</strong> {{customer_name}}</p><p>Địa chỉ: {{customer_address}} | ĐT: {{customer_phone}} | MST: {{customer_tax_code}}</p><p>Hai bên thỏa thuận ký hợp đồng với nội dung sau:</p><p>{{items_table}}</p><p><strong>Tổng giá trị:</strong> {{total}}</p><p><strong>Hình thức thanh toán:</strong> {{payment_method}}</p><p>{{terms}}</p>') : e('<h2 style="text-align:center">BÁO GIÁ</h2><p><strong>Số:</strong> {{quote_number}} | Hiệu lực đến: {{valid_until}}</p><p>Kính gửi: {{customer_name}}</p><p>Chúng tôi xin gửi báo giá các sản phẩm/dịch vụ như sau:</p><p>{{items_table}}</p><p><strong>Tổng cộng:</strong> {{total}}</p><p>{{terms}}</p><p>{{notes}}</p>') ?></textarea>
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
        </div>
    </div>
</form>

<script src="https://cdnjs.cloudflare.com/ajax/libs/ckeditor/4.22.1/ckeditor.js"></script>
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
