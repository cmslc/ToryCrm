<?php $pageTitle = 'Điểm thưởng - ' . e($contact['first_name']); ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Điểm thưởng: <?= e($contact['first_name'] . ' ' . ($contact['last_name'] ?? '')) ?></h4>
    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="<?= url('contacts') ?>">Khách hàng</a></li>
        <li class="breadcrumb-item"><a href="<?= url('contacts/' . $contact['id']) ?>"><?= e($contact['first_name']) ?></a></li>
        <li class="breadcrumb-item active">Điểm thưởng</li>
    </ol>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="avatar-lg mx-auto mb-3">
                    <span class="avatar-title bg-warning-subtle rounded-circle fs-1">
                        <i class="ri-star-fill text-warning"></i>
                    </span>
                </div>
                <h3 class="text-warning"><?= number_format($contact['bonus_points'] ?? 0) ?></h3>
                <p class="text-muted mb-0">Điểm thưởng hiện tại</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Cộng / Trừ điểm</h5></div>
            <div class="card-body">
                <form method="POST" action="<?= url('contacts/' . $contact['id'] . '/add-bonus-points') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Số điểm <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="points" required placeholder="VD: 100 hoặc -50">
                        <small class="text-muted">Nhập số dương để cộng, số âm để trừ</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lý do</label>
                        <textarea name="reason" class="form-control" rows="2" placeholder="VD: Mua hàng đơn DH001"></textarea>
                    </div>
                    <button type="submit" class="btn btn-warning w-100"><i class="ri-star-line me-1"></i> Cập nhật điểm</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Lịch sử điểm thưởng</h5></div>
            <div class="card-body">
                <?php
                $pointLogs = \Core\Database::fetchAll(
                    "SELECT a.*, u.name as user_name FROM activities a
                     LEFT JOIN users u ON a.user_id = u.id
                     WHERE a.contact_id = ? AND a.title LIKE '%điểm thưởng%'
                     ORDER BY a.created_at DESC LIMIT 20",
                    [$contact['id']]
                );
                ?>
                <?php if (!empty($pointLogs)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th>Hành động</th><th>Lý do</th><th>Người thực hiện</th><th>Thời gian</th></tr></thead>
                            <tbody>
                                <?php foreach ($pointLogs as $log): ?>
                                <tr>
                                    <td class="fw-medium"><?= e($log['title']) ?></td>
                                    <td class="text-muted"><?= e($log['description'] ?? '-') ?></td>
                                    <td><?= e($log['user_name'] ?? '-') ?></td>
                                    <td class="text-muted"><?= time_ago($log['created_at']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center mb-0">Chưa có lịch sử điểm thưởng</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
