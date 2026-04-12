<?php
$pageTitle = 'Tạm ứng lương';
$stLabels = ['pending'=>'Chờ duyệt','approved'=>'Đã duyệt','rejected'=>'Từ chối'];
$stColors = ['pending'=>'warning','approved'=>'success','rejected'=>'danger'];
$_role = $user['role'] ?? 'staff';
$monthNames = ['','Tháng 1','Tháng 2','Tháng 3','Tháng 4','Tháng 5','Tháng 6','Tháng 7','Tháng 8','Tháng 9','Tháng 10','Tháng 11','Tháng 12'];
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0"><i class="ri-hand-coin-line me-2"></i> Tạm ứng lương</h4>
    <div class="d-flex gap-2">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#advanceModal"><i class="ri-add-line me-1"></i> Yêu cầu tạm ứng</button>
        <a href="<?= url('attendance/payroll') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Bảng lương</a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Nhân viên</th><th>Số tiền</th><th>Tháng</th><th>Lý do</th><th>Trạng thái</th><th>Người duyệt</th><th>Ngày tạo</th><?php if ($_role !== 'staff'): ?><th>Thao tác</th><?php endif; ?></tr></thead>
                <tbody>
                <?php foreach ($advances as $a): ?>
                <tr>
                    <td class="fw-medium"><?= e($a['user_name']) ?></td>
                    <td class="text-warning fw-medium"><?= number_format($a['amount']) ?>đ</td>
                    <td><?= $monthNames[$a['month']] ?>/<?= $a['year'] ?></td>
                    <td class="text-muted"><?= e($a['reason'] ?? '-') ?></td>
                    <td><span class="badge bg-<?= $stColors[$a['status']] ?>-subtle text-<?= $stColors[$a['status']] ?>"><?= $stLabels[$a['status']] ?></span></td>
                    <td><?= $a['approved_by_name'] ? user_avatar($a['approved_by_name']) : '-' ?></td>
                    <td class="text-muted fs-12"><?= created_ago($a['created_at']) ?></td>
                    <?php if ($_role !== 'staff'): ?>
                    <td>
                        <?php if ($a['status'] === 'pending'): ?>
                        <div class="d-flex gap-1">
                            <form method="POST" action="<?= url('attendance/advances/' . $a['id'] . '/approve') ?>"><?= csrf_field() ?><input type="hidden" name="action" value="approve"><button class="btn btn-soft-success btn-icon" title="Duyệt"><i class="ri-check-line"></i></button></form>
                            <form method="POST" action="<?= url('attendance/advances/' . $a['id'] . '/approve') ?>"><?= csrf_field() ?><input type="hidden" name="action" value="reject"><button class="btn btn-soft-danger btn-icon" title="Từ chối"><i class="ri-close-line"></i></button></form>
                        </div>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($advances)): ?><tr><td colspan="<?= $_role !== 'staff' ? 8 : 7 ?>" class="text-center text-muted py-4">Chưa có yêu cầu tạm ứng</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Advance Modal -->
<div class="modal fade" id="advanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('attendance/advances/create') ?>">
                <?= csrf_field() ?>
                <div class="modal-header"><h5 class="modal-title">Yêu cầu tạm ứng</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Số tiền <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="amount" min="100000" step="100000" required placeholder="VD: 5000000">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Tháng</label>
                            <select name="month" class="form-select">
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>" <?= $m === (int)date('m') ? 'selected' : '' ?>><?= $monthNames[$m] ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Năm</label>
                            <select name="year" class="form-select">
                                <?php for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++): ?>
                                <option value="<?= $y ?>" <?= $y === (int)date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3"><label class="form-label">Lý do</label><textarea class="form-control" name="reason" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-primary"><i class="ri-send-plane-line me-1"></i> Gửi yêu cầu</button></div>
            </form>
        </div>
    </div>
</div>
