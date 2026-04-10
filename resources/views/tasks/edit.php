<?php $pageTitle = 'Sửa công việc'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Sửa công việc</h4>
            <ol class="breadcrumb m-0"><li class="breadcrumb-item"><a href="<?= url('tasks') ?>">Công việc</a></li><li class="breadcrumb-item active">Sửa</li></ol>
        </div>

        <form method="POST" action="<?= url('tasks/' . $task['id'] . '/update') ?>">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card"><div class="card-body">
                        <div class="mb-3"><label class="form-label">Tiêu đề <span class="text-danger">*</span></label><input type="text" class="form-control" name="title" value="<?= e($task['title']) ?>" required></div>
                        <div class="mb-3"><label class="form-label">Mô tả</label><textarea name="description" class="form-control" rows="4"><?= e($task['description'] ?? '') ?></textarea></div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">Hạn</label><input type="datetime-local" class="form-control" name="due_date" value="<?= $task['due_date'] ? date('Y-m-d\TH:i', strtotime($task['due_date'])) : '' ?>"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Khách hàng</label><select name="contact_id" class="form-select searchable-select"><option value="">Chọn</option><?php foreach ($contacts ?? [] as $c): ?><option value="<?= $c['id'] ?>" <?= ($task['contact_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['first_name'] . ' ' . ($c['last_name'] ?? '')) ?></option><?php endforeach; ?></select></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Cơ hội</label><select name="deal_id" class="form-select"><option value="">Chọn</option><?php foreach ($deals ?? [] as $d): ?><option value="<?= $d['id'] ?>" <?= ($task['deal_id'] ?? '') == $d['id'] ? 'selected' : '' ?>><?= e($d['title']) ?></option><?php endforeach; ?></select></div>
                        </div>
                    </div></div>
                </div>
                <div class="col-lg-4">
                    <div class="card"><div class="card-body">
                        <div class="mb-3"><label class="form-label">Trạng thái</label><select name="status" class="form-select"><?php foreach (['todo'=>'Cần làm','in_progress'=>'Đang làm','review'=>'Review','done'=>'Xong'] as $v=>$l): ?><option value="<?= $v ?>" <?= ($task['status'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></div>
                        <div class="mb-3"><label class="form-label">Ưu tiên</label><select name="priority" class="form-select"><?php foreach (['low'=>'Thấp','medium'=>'TB','high'=>'Cao','urgent'=>'Khẩn'] as $v=>$l): ?><option value="<?= $v ?>" <?= ($task['priority'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></div>
                        <div class="mb-3"><label class="form-label">Giao cho</label><select name="assigned_to" class="form-select searchable-select"><option value="">Chọn</option><?php foreach ($users ?? [] as $u): ?><option value="<?= $u['id'] ?>" <?= ($task['assigned_to'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option><?php endforeach; ?></select></div>
                    </div></div>
                    <div class="card"><div class="card-body d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-save-line me-1"></i> Cập nhật</button>
                        <a href="<?= url('tasks/' . $task['id']) ?>" class="btn btn-soft-secondary">Hủy</a>
                    </div></div>
                </div>
            </div>
        </form>
