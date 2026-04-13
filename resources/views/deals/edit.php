<?php $pageTitle = 'Sửa cơ hội'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Sửa cơ hội</h4>
            <ol class="breadcrumb m-0"><li class="breadcrumb-item"><a href="<?= url('deals') ?>">Cơ hội</a></li><li class="breadcrumb-item active">Sửa</li></ol>
        </div>

        <form method="POST" action="<?= url('deals/' . $deal['id'] . '/update') ?>">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Thông tin</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mb-3"><label class="form-label">Tên <span class="text-danger">*</span></label><input type="text" class="form-control" name="title" value="<?= e($deal['title']) ?>" required></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Giá trị (VNĐ)</label><input type="number" class="form-control" name="value" value="<?= $deal['value'] ?>" min="0"></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Ngày dự kiến</label><input type="date" class="form-control" name="expected_close_date" value="<?= $deal['expected_close_date'] ?? '' ?>"></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Khách hàng</label><select name="contact_id" class="form-select searchable-select"><option value="">Chọn</option><?php foreach ($contacts ?? [] as $c): ?><option value="<?= $c['id'] ?>" <?= ($deal['contact_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['first_name'] . ' ' . ($c['last_name'] ?? '')) ?></option><?php endforeach; ?></select></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Công ty</label><select name="company_id" class="form-select searchable-select"><option value="">Chọn</option><?php foreach ($companies ?? [] as $comp): ?><option value="<?= $comp['id'] ?>" <?= ($deal['company_id'] ?? '') == $comp['id'] ? 'selected' : '' ?>><?= e($comp['name']) ?></option><?php endforeach; ?></select></div>
                                <div class="col-12 mb-3"><label class="form-label">Mô tả</label><textarea name="description" class="form-control" rows="3"><?= e($deal['description'] ?? '') ?></textarea></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card"><div class="card-body">
                        <div class="mb-3"><label class="form-label">Giai đoạn</label><select name="stage_id" class="form-select"><?php foreach ($stages ?? [] as $s): ?><option value="<?= $s['id'] ?>" <?= ($deal['stage_id'] ?? '') == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option><?php endforeach; ?></select></div>
                        <div class="mb-3"><label class="form-label">Trạng thái</label><select name="status" class="form-select"><option value="open" <?= $deal['status']==='open'?'selected':'' ?>>Đang mở</option><option value="won" <?= $deal['status']==='won'?'selected':'' ?>>Thắng</option><option value="lost" <?= $deal['status']==='lost'?'selected':'' ?>>Thua</option></select></div>
                        <div class="mb-3"><label class="form-label">Ưu tiên</label><select name="priority" class="form-select"><?php foreach (['low'=>'Thấp','medium'=>'TB','high'=>'Cao','urgent'=>'Khẩn'] as $v=>$l): ?><option value="<?= $v ?>" <?= ($deal['priority'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></div>
                        <div class="mb-3"><label class="form-label">Phụ trách</label><?php
                        $deptGrouped = [];
                        foreach ($users ?? [] as $u) { $deptGrouped[$u['dept_name'] ?? 'Chưa phân phòng'][] = $u; }
                        ?><select name="owner_id" class="form-select searchable-select"><option value="">Chọn</option><?php foreach ($deptGrouped as $dept => $dUsers): ?><optgroup label="<?= e($dept) ?>"><?php foreach ($dUsers as $u): ?><option value="<?= $u['id'] ?>" <?= ($deal['owner_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option><?php endforeach; ?></optgroup><?php endforeach; ?></select></div>
                    </div></div>
                    <div class="card"><div class="card-body d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-save-line me-1"></i> Cập nhật</button>
                        <a href="<?= url('deals/' . $deal['id']) ?>" class="btn btn-soft-secondary">Hủy</a>
                    </div></div>
                </div>
            </div>
        </form>
