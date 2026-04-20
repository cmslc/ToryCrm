<?php $pageTitle = 'Chờ phê duyệt'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Chờ phê duyệt</h4>
    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="<?= url('approvals') ?>">Phê duyệt</a></li>
        <li class="breadcrumb-item active">Chờ xử lý</li>
    </ol>
</div>

<?php
$moduleLabels = [
    'orders' => 'Đơn hàng',
    'deals' => 'Cơ hội',
    'purchase_orders' => 'Đơn hàng mua',
    'fund_transactions' => 'Giao dịch quỹ',
];
$moduleIcons = [
    'orders' => 'ri-file-list-3-line',
    'deals' => 'ri-hand-coin-line',
    'purchase_orders' => 'ri-shopping-cart-line',
    'fund_transactions' => 'ri-wallet-3-line',
];
$hasAny = !empty($requests) || !empty($pending);
?>

<!-- Yêu cầu thêm người liên hệ -->
<?php if (!empty($requests)): ?>
<div class="card">
    <div class="card-header d-flex align-items-center">
        <h5 class="card-title mb-0 flex-grow-1"><i class="ri-user-add-line me-1"></i> Yêu cầu thêm người liên hệ <span class="badge bg-danger align-middle ms-1"><?= count($requests) ?></span></h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Người yêu cầu</th>
                    <th>Khách hàng</th>
                    <th>Người liên hệ mới</th>
                    <th>SĐT</th>
                    <th>Thời gian</th>
                    <th style="width:320px">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $r):
                    $contactName = $r['company_name'] ?: trim($r['first_name'] . ' ' . ($r['last_name'] ?? ''));
                ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <?php if (!empty($r['requester_avatar'])): ?>
                            <img src="<?= asset($r['requester_avatar']) ?>" class="rounded-circle" width="32" height="32" style="object-fit:cover">
                            <?php else: ?>
                            <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:13px"><?= mb_substr($r['requester_name'], 0, 1) ?></div>
                            <?php endif; ?>
                            <span><?= e($r['requester_name']) ?></span>
                        </div>
                    </td>
                    <td>
                        <a href="<?= url('contacts/' . $r['existing_contact_id']) ?>" class="fw-medium"><?= e($contactName) ?></a>
                        <?php if ($r['account_code']): ?><br><small class="text-muted"><?= e($r['account_code']) ?></small><?php endif; ?>
                    </td>
                    <td>
                        <?php if ($r['cp_title']): ?><span class="badge bg-soft-info text-info me-1"><?= e(ucfirst($r['cp_title'])) ?></span><?php endif; ?>
                        <span class="fw-medium"><?= e($r['cp_name']) ?></span>
                        <?php if ($r['cp_position']): ?><br><small class="text-muted"><?= e($r['cp_position']) ?></small><?php endif; ?>
                    </td>
                    <td>
                        <?php if ($r['cp_phone_masked']): ?><i class="ri-phone-line text-muted me-1"></i><?= e($r['cp_phone_masked']) ?><?php endif; ?>
                        <?php if ($r['cp_email']): ?><br><i class="ri-mail-line text-muted me-1"></i><?= e($r['cp_email']) ?><?php endif; ?>
                    </td>
                    <td><small class="text-muted"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></small></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <form method="POST" action="<?= url('merge-requests/' . $r['id'] . '/approve') ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-success"><i class="ri-check-line me-1"></i>Duyệt</button>
                            </form>
                            <button class="btn btn-danger" onclick="rejectMerge(<?= $r['id'] ?>)"><i class="ri-close-line me-1"></i>Từ chối</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Phê duyệt quy trình -->
<?php if (!empty($pending)): ?>
<div class="card">
    <div class="card-header d-flex align-items-center">
        <h5 class="card-title mb-0 flex-grow-1"><i class="ri-checkbox-circle-line me-1"></i> Phê duyệt quy trình <span class="badge bg-danger align-middle ms-1"><?= count($pending) ?></span></h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Loại</th>
                    <th>Nội dung</th>
                    <th>Quy trình</th>
                    <th>Người yêu cầu</th>
                    <th>Thời gian</th>
                    <th style="width:400px">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending as $item): ?>
                <tr>
                    <td>
                        <span class="badge bg-soft-warning text-warning">
                            <i class="<?= $moduleIcons[$item['module']] ?? 'ri-file-line' ?> me-1"></i><?= $moduleLabels[$item['module']] ?? $item['module'] ?>
                        </span>
                        <br><small class="text-muted">Bước <?= $item['step_order'] ?></small>
                    </td>
                    <td><span class="fw-medium"><?= e($item['entity_title'] ?? ($item['entity_type'] . ' #' . $item['entity_id'])) ?></span></td>
                    <td><?= e($item['flow_name']) ?></td>
                    <td><?= e($item['requested_by_name']) ?></td>
                    <td><small class="text-muted"><?= date('d/m/Y H:i', strtotime($item['created_at'])) ?></small></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <form method="POST" action="<?= url('approvals/' . $item['id'] . '/approve') ?>">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-success"><i class="ri-check-line me-1"></i>Duyệt</button>
                            </form>
                            <button class="btn btn-danger" onclick="rejectApproval(<?= $item['id'] ?>)"><i class="ri-close-line me-1"></i>Từ chối</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Yêu cầu tôi đã gửi -->
<?php if (!empty($myRequests)): ?>
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-send-plane-line me-1"></i> Yêu cầu tôi đã gửi</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Trạng thái</th>
                    <th>Người liên hệ</th>
                    <th>Khách hàng</th>
                    <th>Thời gian</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($myRequests as $r):
                    $contactName = $r['company_name'] ?: trim($r['first_name'] . ' ' . ($r['last_name'] ?? ''));
                    $statusBadge = ['pending' => 'bg-warning', 'approved' => 'bg-success', 'rejected' => 'bg-danger'];
                    $statusLabel = ['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối'];
                ?>
                <tr>
                    <td><span class="badge <?= $statusBadge[$r['status']] ?? 'bg-secondary' ?>"><?= $statusLabel[$r['status']] ?? '' ?></span></td>
                    <td>
                        <span class="fw-medium"><?= e($r['cp_name']) ?></span>
                        <?php if ($r['cp_phone_masked']): ?><br><small class="text-muted"><i class="ri-phone-line me-1"></i><?= e($r['cp_phone_masked']) ?></small><?php endif; ?>
                    </td>
                    <td><?= e($contactName) ?></td>
                    <td><small class="text-muted"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></small></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Không có gì -->
<?php if (!$hasAny): ?>
<div class="card">
    <div class="card-body text-center py-5 text-muted">
        <i class="ri-checkbox-circle-line" style="font-size:48px"></i>
        <p class="mt-3 mb-0">Không có yêu cầu phê duyệt nào đang chờ</p>
    </div>
</div>
<?php endif; ?>

<!-- Modal từ chối -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Từ chối yêu cầu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Lý do từ chối <span class="text-danger">*</span></label>
                        <textarea name="reason" id="rejectReason" class="form-control" rows="3" placeholder="Nhập lý do từ chối..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-soft-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger"><i class="ri-close-line me-1"></i>Xác nhận từ chối</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function rejectMerge(id) {
    document.getElementById('rejectForm').action = '<?= url("merge-requests") ?>/' + id + '/reject';
    document.getElementById('rejectReason').value = '';
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function rejectApproval(id) {
    document.getElementById('rejectForm').action = '<?= url("approvals") ?>/' + id + '/reject';
    document.getElementById('rejectReason').value = '';
    // Change field name to comment for approvals
    document.getElementById('rejectReason').name = 'comment';
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}
</script>
