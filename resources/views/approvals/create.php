<?php $pageTitle = 'Tạo quy trình phê duyệt'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Tạo quy trình phê duyệt</h4>
    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="<?= url('approvals') ?>">Phê duyệt</a></li>
        <li class="breadcrumb-item active">Tạo mới</li>
    </ol>
</div>

<form method="POST" action="<?= url('approvals/store') ?>">
    <?= csrf_field() ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Thông tin quy trình</h5></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Tên quy trình <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required placeholder="VD: Duyệt đơn hàng trên 10 triệu">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Module áp dụng <span class="text-danger">*</span></label>
                            <select name="module" class="form-select" required>
                                <?php foreach ($modules as $key => $label): ?>
                                    <option value="<?= $key ?>"><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch mt-4">
                                <input type="checkbox" class="form-check-input" name="is_active" id="isActive" value="1" checked>
                                <label class="form-check-label" for="isActive">Kích hoạt</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Conditions -->
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Điều kiện áp dụng</h5></div>
                <div class="card-body">
                    <p class="text-muted mb-3">Quy trình sẽ tự động áp dụng khi thỏa điều kiện. Để trống nếu áp dụng cho tất cả.</p>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Trường</label>
                            <input type="text" class="form-control" name="condition_field" placeholder="VD: total, status">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Toán tử</label>
                            <select name="condition_operator" class="form-select">
                                <option value="=">=</option>
                                <option value=">">&gt;</option>
                                <option value=">=">&gt;=</option>
                                <option value="<">&lt;</option>
                                <option value="in">trong danh sách</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Giá trị</label>
                            <input type="text" class="form-control" name="condition_value" placeholder="VD: 10000000">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Steps -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Các bước phê duyệt</h5>
                    <button type="button" class="btn btn-soft-primary" id="addStepBtn">
                        <i class="ri-add-line me-1"></i> Thêm bước
                    </button>
                </div>
                <div class="card-body">
                    <div id="stepsContainer">
                        <div class="step-row row mb-3 align-items-end" data-step="1">
                            <div class="col-md-1">
                                <label class="form-label">Bước</label>
                                <div class="form-control bg-light text-center fw-bold step-number">1</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tên bước</label>
                                <input type="text" class="form-control" name="step_label[]" value="Bước 1" placeholder="VD: Trưởng phòng duyệt">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Người phê duyệt <span class="text-danger">*</span></label>
                                <select name="approver_id[]" class="form-select" required>
                                    <option value="">Chọn người duyệt</option>
                                    <?php foreach ($users as $u): ?>
                                        <option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-soft-danger remove-step-btn w-100" disabled>
                                    <i class="ri-delete-bin-line"></i>
                                </button>
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
                    <a href="<?= url('approvals') ?>" class="btn btn-soft-secondary">Hủy</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="card-title mb-0">Hướng dẫn</h6></div>
                <div class="card-body">
                    <ul class="list-unstyled text-muted mb-0">
                        <li class="mb-2"><i class="ri-information-line text-primary me-1"></i> Mỗi bước phải có một người phê duyệt</li>
                        <li class="mb-2"><i class="ri-information-line text-primary me-1"></i> Yêu cầu sẽ đi qua từng bước theo thứ tự</li>
                        <li class="mb-2"><i class="ri-information-line text-primary me-1"></i> Nếu bị từ chối ở bất kỳ bước nào, yêu cầu sẽ bị từ chối</li>
                        <li><i class="ri-information-line text-primary me-1"></i> Người phê duyệt sẽ nhận thông báo tự động</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var container = document.getElementById('stepsContainer');
    var addBtn = document.getElementById('addStepBtn');
    var stepCount = 1;

    addBtn.addEventListener('click', function() {
        stepCount++;
        var html = '<div class="step-row row mb-3 align-items-end" data-step="' + stepCount + '">' +
            '<div class="col-md-1"><div class="form-control bg-light text-center fw-bold step-number">' + stepCount + '</div></div>' +
            '<div class="col-md-4"><input type="text" class="form-control" name="step_label[]" value="Bước ' + stepCount + '" placeholder="Tên bước"></div>' +
            '<div class="col-md-5"><select name="approver_id[]" class="form-select" required><option value="">Chọn người duyệt</option>' +
            <?php
                $userOptions = '';
                foreach ($users as $u) {
                    $userOptions .= '<option value="' . $u['id'] . '">' . htmlspecialchars($u['name'], ENT_QUOTES) . '</option>';
                }
                echo json_encode($userOptions);
            ?> +
            '</select></div>' +
            '<div class="col-md-2"><button type="button" class="btn btn-soft-danger remove-step-btn w-100"><i class="ri-delete-bin-line"></i></button></div>' +
            '</div>';
        container.insertAdjacentHTML('beforeend', html);
        updateRemoveButtons();
    });

    container.addEventListener('click', function(e) {
        var removeBtn = e.target.closest('.remove-step-btn');
        if (removeBtn) {
            removeBtn.closest('.step-row').remove();
            reorderSteps();
            updateRemoveButtons();
        }
    });

    function reorderSteps() {
        var rows = container.querySelectorAll('.step-row');
        rows.forEach(function(row, idx) {
            var num = row.querySelector('.step-number');
            if (num) num.textContent = idx + 1;
            var label = row.querySelector('input[name="step_label[]"]');
            if (label && label.value.match(/^Bước \d+$/)) {
                label.value = 'Bước ' + (idx + 1);
            }
        });
        stepCount = rows.length;
    }

    function updateRemoveButtons() {
        var rows = container.querySelectorAll('.step-row');
        rows.forEach(function(row) {
            var btn = row.querySelector('.remove-step-btn');
            if (btn) btn.disabled = rows.length <= 1;
        });
    }
});
</script>
