<?php $pageTitle = 'Thêm công việc'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Thêm công việc</h4>
            <ol class="breadcrumb m-0"><li class="breadcrumb-item"><a href="<?= url('tasks') ?>">Công việc</a></li><li class="breadcrumb-item active">Thêm mới</li></ol>
        </div>

        <form method="POST" action="<?= url('tasks/store') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Thông tin</h5></div>
                        <div class="card-body">
                            <div class="mb-3"><label class="form-label">Tiêu đề <span class="text-danger">*</span></label><input type="text" class="form-control" name="title" required></div>
                            <div class="mb-3"><label class="form-label">Mô tả</label><textarea name="description" class="form-control" rows="4"></textarea></div>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label">Hạn hoàn thành</label><input type="datetime-local" class="form-control" name="due_date"></div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Liên kết khách hàng</label>
                                    <select name="contact_id" class="form-select searchable-select"><option value="">Chọn</option>
                                    <?php foreach ($contacts ?? [] as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['first_name'] . ' ' . ($c['last_name'] ?? '')) ?></option><?php endforeach; ?></select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Liên kết cơ hội</label>
                                    <select name="deal_id" class="form-select"><option value="">Chọn</option>
                                    <?php foreach ($deals ?? [] as $d): ?><option value="<?= $d['id'] ?>"><?= e($d['title']) ?></option><?php endforeach; ?></select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header"><h6 class="card-title mb-0"><i class="ri-attachment-2 me-1"></i> Đính kèm</h6></div>
                        <div class="card-body">
                            <input type="file" name="attachments[]" id="fileInput" class="d-none" multiple>
                            <div id="fileList" class="d-flex flex-wrap gap-2 mb-3"></div>
                            <button type="button" class="btn btn-soft-primary" onclick="document.getElementById('fileInput').click()"><i class="ri-upload-2-line me-1"></i> Chọn file</button>
                            <small class="text-muted ms-2">Tối đa 10MB / file, chọn nhiều file cùng lúc</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card"><div class="card-body">
                        <div class="mb-3"><label class="form-label">Trạng thái</label><select name="status" class="form-select"><option value="todo">Cần làm</option><option value="in_progress">Đang làm</option></select></div>
                        <div class="mb-3"><label class="form-label">Ưu tiên</label><select name="priority" class="form-select"><option value="low">Thấp</option><option value="medium" selected>Trung bình</option><option value="high">Cao</option><option value="urgent">Khẩn cấp</option></select></div>
                        <div class="mb-3"><label class="form-label">Giao cho</label><?php
                        $deptGrouped = [];
                        foreach ($users ?? [] as $u) { $deptGrouped[$u['dept_name'] ?? 'Chưa phân phòng'][] = $u; }
                        ?><select name="assigned_to" class="form-select searchable-select"><option value="">Chọn</option><?php foreach ($deptGrouped as $dept => $dUsers): ?><optgroup label="<?= e($dept) ?>"><?php foreach ($dUsers as $u): ?><option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option><?php endforeach; ?></optgroup><?php endforeach; ?></select></div>
                    </div></div>
                    <div class="card"><div class="card-body d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-save-line me-1"></i> Lưu</button>
                        <a href="<?= url('tasks') ?>" class="btn btn-soft-secondary">Hủy</a>
                    </div></div>
                </div>
            </div>
        </form>

<script>
document.getElementById('fileInput').addEventListener('change', function() {
    var list = document.getElementById('fileList');
    Array.from(this.files).forEach(function(file) {
        var size = (file.size / 1024).toFixed(1);
        var div = document.createElement('div');
        div.className = 'border rounded p-2 d-flex align-items-center gap-2';
        div.innerHTML = '<i class="ri-file-line text-primary fs-18"></i><div><div class="fw-medium fs-13">' + file.name + '</div><small class="text-muted">' + size + ' KB</small></div>';
        list.appendChild(div);
    });
});
</script>
