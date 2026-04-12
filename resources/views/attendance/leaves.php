<?php
$pageTitle = 'Nghỉ phép';
$typeLabels = ['annual'=>'Phép năm','sick'=>'Ốm','personal'=>'Việc riêng','maternity'=>'Thai sản','unpaid'=>'Không lương','other'=>'Khác'];
$stLabels = ['pending'=>'Chờ duyệt','approved'=>'Đã duyệt','rejected'=>'Từ chối','cancelled'=>'Đã hủy'];
$stColors = ['pending'=>'warning','approved'=>'success','rejected'=>'danger','cancelled'=>'secondary'];
$_role = $user['role'] ?? 'staff';
$fStatus = $filters['status'] ?? '';
$fType = $filters['leaveType'] ?? '';
$hasFilter = $fStatus || $fType;
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0"><i class="ri-calendar-event-line me-2"></i> Nghỉ phép</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#leaveModal"><i class="ri-add-line me-1"></i> Xin nghỉ</button>
</div>

<!-- Leave balance cards -->
<?php if ($_role !== 'staff' && !empty($leaveStats)): ?>
<div class="card mb-3">
    <div class="card-header"><h5 class="card-title mb-0"><i class="ri-team-line me-2"></i> Số phép còn lại</h5></div>
    <div class="card-body py-2">
        <div class="d-flex gap-3 flex-wrap">
            <?php foreach ($leaveStats as $ls):
                $remain = (float)$ls['leave_balance'] - (float)$ls['used'];
            ?>
            <div class="border rounded px-3 py-2 text-center" style="min-width:100px">
                <div class="fw-medium fs-13"><?= e($ls['name']) ?></div>
                <div class="fs-12">
                    <span class="text-<?= $remain > 3 ? 'success' : ($remain > 0 ? 'warning' : 'danger') ?> fw-medium"><?= rtrim(rtrim(number_format($remain, 1), '0'), '.') ?></span>
                    <span class="text-muted">/ <?= rtrim(rtrim(number_format($ls['leave_balance'], 1), '0'), '.') ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="card mb-2">
    <div class="card-header p-2">
        <form method="GET" action="<?= url('attendance/leaves') ?>" class="d-flex align-items-center gap-2 flex-wrap">
            <select name="status" class="form-select" style="width:auto;min-width:130px" onchange="this.form.submit()">
                <option value="">Tất cả trạng thái</option>
                <?php foreach ($stLabels as $k => $v): ?>
                <option value="<?= $k ?>" <?= $fStatus === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
            <select name="leave_type" class="form-select" style="width:auto;min-width:130px" onchange="this.form.submit()">
                <option value="">Tất cả loại</option>
                <?php foreach ($typeLabels as $k => $v): ?>
                <option value="<?= $k ?>" <?= $fType === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($hasFilter): ?>
            <a href="<?= url('attendance/leaves') ?>" class="btn btn-soft-danger"><i class="ri-refresh-line me-1"></i> Xóa lọc</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Nhân viên</th><th>Loại</th><th>Từ ngày</th><th>Đến ngày</th><th>Số ngày</th><th>Phép còn</th><th>Lý do</th><th>Trạng thái</th><th>Người duyệt</th><?php if ($_role !== 'staff'): ?><th>Thao tác</th><?php endif; ?></tr></thead>
                <tbody>
                <?php foreach ($leaves as $l):
                    $remain = (float)($l['leave_balance'] ?? 12);
                ?>
                <tr>
                    <td class="fw-medium"><?= e($l['user_name']) ?></td>
                    <td><span class="badge bg-info-subtle text-info"><?= $typeLabels[$l['leave_type']] ?? $l['leave_type'] ?></span></td>
                    <td><?= date('d/m/Y', strtotime($l['date_from'])) ?></td>
                    <td><?= date('d/m/Y', strtotime($l['date_to'])) ?></td>
                    <td><?= rtrim(rtrim(number_format($l['days'], 1), '0'), '.') ?></td>
                    <td><span class="text-<?= $remain > 3 ? 'success' : ($remain > 0 ? 'warning' : 'danger') ?>"><?= rtrim(rtrim(number_format($remain, 1), '0'), '.') ?></span></td>
                    <td class="text-muted"><?= e($l['reason'] ?? '-') ?></td>
                    <td><span class="badge bg-<?= $stColors[$l['status']] ?>-subtle text-<?= $stColors[$l['status']] ?>"><?= $stLabels[$l['status']] ?></span></td>
                    <td><?= $l['approved_by_name'] ? user_avatar($l['approved_by_name']) : '-' ?></td>
                    <?php if ($_role !== 'staff'): ?>
                    <td>
                        <?php if ($l['status'] === 'pending'): ?>
                        <div class="d-flex gap-1">
                            <form method="POST" action="<?= url('attendance/leaves/' . $l['id'] . '/approve') ?>"><?= csrf_field() ?><input type="hidden" name="action" value="approve"><button class="btn btn-soft-success btn-icon" title="Duyệt"><i class="ri-check-line"></i></button></form>
                            <form method="POST" action="<?= url('attendance/leaves/' . $l['id'] . '/approve') ?>"><?= csrf_field() ?><input type="hidden" name="action" value="reject"><button class="btn btn-soft-danger btn-icon" title="Từ chối"><i class="ri-close-line"></i></button></form>
                        </div>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($leaves)): ?><tr><td colspan="<?= $_role !== 'staff' ? 10 : 9 ?>" class="text-center text-muted py-4">Chưa có đơn nghỉ phép</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($totalPages > 1): ?>
    <?php $qs = http_build_query(array_filter(['status' => $fStatus, 'leave_type' => $fType])); ?>
    <div class="card-footer">
        <div class="d-flex align-items-center justify-content-between">
            <span class="text-muted fs-12">Tổng <?= $total ?> đơn</span>
            <ul class="pagination pagination-separated mb-0">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= url('attendance/leaves?' . $qs . '&page=' . ($page - 1)) ?>"><i class="ri-arrow-left-s-line"></i></a></li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="<?= url('attendance/leaves?' . $qs . '&page=' . $i) ?>"><?= $i ?></a></li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>"><a class="page-link" href="<?= url('attendance/leaves?' . $qs . '&page=' . ($page + 1)) ?>"><i class="ri-arrow-right-s-line"></i></a></li>
            </ul>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Create Leave Modal -->
<div class="modal fade" id="leaveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('attendance/leaves/create') ?>">
                <?= csrf_field() ?>
                <div class="modal-header"><h5 class="modal-title">Xin nghỉ phép</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Loại nghỉ</label>
                        <select name="leave_type" class="form-select">
                            <?php foreach ($typeLabels as $k => $v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3"><label class="form-label">Từ ngày <span class="text-danger">*</span></label><input type="date" class="form-control" name="date_from" required></div>
                        <div class="col-6 mb-3"><label class="form-label">Đến ngày</label><input type="date" class="form-control" name="date_to"></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Lý do</label><textarea class="form-control" name="reason" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-primary"><i class="ri-send-plane-line me-1"></i> Gửi đơn</button></div>
            </form>
        </div>
    </div>
</div>
