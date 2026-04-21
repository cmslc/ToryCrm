<?php
$pageTitle = 'Tạm ứng lương';
$stLabels = ['pending'=>'Chờ duyệt','approved'=>'Đã duyệt','rejected'=>'Từ chối'];
$stColors = ['pending'=>'warning','approved'=>'success','rejected'=>'danger'];
$_role = $user['role'] ?? 'staff';
$monthNames = ['','Tháng 1','Tháng 2','Tháng 3','Tháng 4','Tháng 5','Tháng 6','Tháng 7','Tháng 8','Tháng 9','Tháng 10','Tháng 11','Tháng 12'];
$fStatus = $filters['status'] ?? '';
$fMonth = $filters['fMonth'] ?? '';
$fYear = $filters['fYear'] ?? '';
$hasFilter = $fStatus || $fMonth || $fYear;
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0"><i class="ri-hand-coin-line me-2"></i> Tạm ứng lương</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#advanceModal"><i class="ri-add-line me-1"></i> Yêu cầu tạm ứng</button>
</div>

<!-- Filters -->
<div class="card mb-2">
    <div class="card-header p-2">
        <form method="GET" action="<?= url('attendance/advances') ?>" class="d-flex align-items-center gap-2 flex-wrap">
            <select name="status" class="form-select" style="width:auto;min-width:130px" onchange="this.form.submit()">
                <option value="">Tất cả trạng thái</option>
                <?php foreach ($stLabels as $k => $v): ?>
                <option value="<?= $k ?>" <?= $fStatus === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
            <select name="month" class="form-select" style="width:auto" onchange="this.form.submit()">
                <option value="">Tất cả tháng</option>
                <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>" <?= $fMonth == $m ? 'selected' : '' ?>><?= $monthNames[$m] ?></option>
                <?php endfor; ?>
            </select>
            <select name="year" class="form-select" style="width:auto" onchange="this.form.submit()">
                <option value="">Năm</option>
                <?php for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++): ?>
                <option value="<?= $y ?>" <?= $fYear == $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
            <?php if ($hasFilter): ?>
            <a href="<?= url('attendance/advances') ?>" class="btn btn-soft-danger"><i class="ri-refresh-line me-1"></i> Xóa lọc</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-2">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Nhân viên</th><th class="text-end">Số tiền</th><th>% Lương</th><th>Tháng</th><th>Lý do</th><th>Trạng thái</th><th>Người duyệt</th><th>Ngày tạo</th><?php if ($_role !== 'staff'): ?><th>Thao tác</th><?php endif; ?></tr></thead>
                <tbody>
                <?php foreach ($advances as $a):
                    $pctSalary = ($a['base_salary'] ?? 0) > 0 ? round($a['amount'] / $a['base_salary'] * 100) : 0;
                ?>
                <tr>
                    <td class="fw-medium"><?= e($a['user_name']) ?></td>
                    <td class="text-end text-warning fw-medium"><?= number_format($a['amount']) ?>đ</td>
                    <td><span class="badge bg-<?= $pctSalary > 50 ? 'danger' : ($pctSalary > 30 ? 'warning' : 'success') ?>-subtle text-<?= $pctSalary > 50 ? 'danger' : ($pctSalary > 30 ? 'warning' : 'success') ?>"><?= $pctSalary ?>%</span></td>
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
                <?php if (empty($advances)): ?><tr><td colspan="<?= $_role !== 'staff' ? 9 : 8 ?>" class="text-center text-muted py-4">Chưa có yêu cầu tạm ứng</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($totalPages > 1): ?>
    <?php $qs = http_build_query(array_filter(['status' => $fStatus, 'month' => $fMonth, 'year' => $fYear])); ?>
    <div class="card-footer">
        <div class="d-flex align-items-center justify-content-between">
            <span class="text-muted fs-12">Tổng <?= $total ?></span>
            <ul class="pagination pagination-separated mb-0">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= url('attendance/advances?' . $qs . '&page=' . ($page - 1)) ?>"><i class="ri-arrow-left-s-line"></i></a></li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="<?= url('attendance/advances?' . $qs . '&page=' . $i) ?>"><?= $i ?></a></li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>"><a class="page-link" href="<?= url('attendance/advances?' . $qs . '&page=' . ($page + 1)) ?>"><i class="ri-arrow-right-s-line"></i></a></li>
            </ul>
        </div>
    </div>
    <?php endif; ?>
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
