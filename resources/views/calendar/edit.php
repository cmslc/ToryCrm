<?php $pageTitle = 'Sửa lịch hẹn'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Sửa lịch hẹn</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('calendar') ?>">Lịch</a></li>
                <li class="breadcrumb-item active">Sửa</li>
            </ol>
        </div>

        <form method="POST" action="<?= url('calendar/' . $event['id'] . '/update') ?>">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Thông tin lịch hẹn</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="title" value="<?= e($event['title']) ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Loại</label>
                                    <select name="type" class="form-select">
                                        <?php $types = ['meeting'=>'Cuộc họp','call'=>'Cuộc gọi','visit'=>'Thăm khách','reminder'=>'Nhắc nhở','other'=>'Khác']; ?>
                                        <?php foreach ($types as $k => $v): ?>
                                            <option value="<?= $k ?>" <?= $event['type'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Bắt đầu <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" name="start_at" value="<?= date('Y-m-d\TH:i', strtotime($event['start_at'])) ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Kết thúc</label>
                                    <input type="datetime-local" class="form-control" name="end_at" value="<?= $event['end_at'] ? date('Y-m-d\TH:i', strtotime($event['end_at'])) : '' ?>">
                                </div>
                                <div class="col-12 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="all_day" id="allDay" <?= $event['all_day'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="allDay">Cả ngày</label>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Địa điểm</label>
                                    <input type="text" class="form-control" name="location" value="<?= e($event['location'] ?? '') ?>">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Mô tả</label>
                                    <textarea name="description" class="form-control" rows="3"><?= e($event['description'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Liên kết</h5></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Khách hàng</label>
                                <select name="contact_id" class="form-select searchable-select">
                                    <option value="">Chọn</option>
                                    <?php foreach ($contacts ?? [] as $c): ?>
                                        <option value="<?= $c['id'] ?>" <?= ($event['contact_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['first_name'] . ' ' . ($c['last_name'] ?? '')) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Công ty</label>
                                <select name="company_id" class="form-select searchable-select">
                                    <option value="">Chọn</option>
                                    <?php foreach ($companies ?? [] as $comp): ?>
                                        <option value="<?= $comp['id'] ?>" <?= ($event['company_id'] ?? '') == $comp['id'] ? 'selected' : '' ?>><?= e($comp['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Cơ hội</label>
                                <select name="deal_id" class="form-select">
                                    <option value="">Chọn</option>
                                    <?php foreach ($deals ?? [] as $d): ?>
                                        <option value="<?= $d['id'] ?>" <?= ($event['deal_id'] ?? '') == $d['id'] ? 'selected' : '' ?>><?= e($d['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Người thực hiện</label>
                                <?php $deptGrouped = []; foreach ($users ?? [] as $u) { $deptGrouped[$u['dept_name'] ?? 'Chưa phân phòng'][] = $u; } ?>
                                <select name="user_id" class="form-select searchable-select">
                                    <option value="">Tôi</option>
                                    <?php foreach ($deptGrouped as $dept => $dUsers): ?>
                                    <optgroup label="<?= e($dept) ?>">
                                        <?php foreach ($dUsers as $u): ?>
                                        <option value="<?= $u['id'] ?>" <?= ($event['user_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-save-line me-1"></i> Cập nhật</button>
                            <a href="<?= url('calendar/' . $event['id']) ?>" class="btn btn-soft-secondary">Hủy</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
