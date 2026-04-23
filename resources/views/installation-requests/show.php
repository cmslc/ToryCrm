<?php
$pageTitle = 'Yêu cầu thi công ' . ($request['code'] ?? '');
$ctrl = \App\Controllers\InstallationRequestController::class;
$cName = $request['c_company_name'] ?: ($request['c_full_name'] ?: '');
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-0">
            <?= e($request['code']) ?>
            <span class="badge bg-<?= $ctrl::statusColor($request['status']) ?> ms-2"><?= e($ctrl::statusLabel($request['status'])) ?></span>
        </h4>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('installation-requests/' . $request['id'] . '/edit') ?>" class="btn btn-soft-primary"><i class="ri-pencil-line me-1"></i> Sửa</a>
        <a href="<?= url('installation-requests/' . $request['id'] . '/pdf') ?>" target="_blank" class="btn btn-soft-info"><i class="ri-printer-line me-1"></i> In</a>
        <form method="POST" action="<?= url('installation-requests/' . $request['id'] . '/delete') ?>" class="d-inline" data-confirm="Xóa yêu cầu thi công này?">
            <?= csrf_field() ?>
            <button class="btn btn-soft-danger"><i class="ri-delete-bin-line me-1"></i> Xóa</button>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-lg-9">
        <!-- Thông tin chung -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="ri-file-list-3-line me-1"></i> Thông tin yêu cầu</h5>
                <?php if ($request['order_id'] && $request['order_number']): ?>
                    <a href="<?= url('orders/' . $request['order_id']) ?>" class="text-primary">Đơn hàng <?= e($request['order_number']) ?> <i class="ri-external-link-line ms-1"></i></a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Khách hàng</h6>
                        <?php if ($cName): ?>
                            <p class="mb-1 fw-medium">
                                <a href="<?= url('contacts/' . $request['contact_id']) ?>"><?= e($cName) ?></a>
                            </p>
                            <?php if ($request['c_account_code'] ?? ''): ?><p class="mb-1 text-muted"><i class="ri-user-line me-1"></i>Mã KH: <?= e($request['c_account_code']) ?></p><?php endif; ?>
                        <?php else: ?>
                            <p class="text-muted">-</p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Người yêu cầu</h6>
                        <?php if ($request['requester_name']): ?>
                            <p class="mb-1 fw-medium"><?= e($request['requester_name']) ?></p>
                            <?php if ($request['requester_phone']): ?><p class="mb-1 text-muted"><i class="ri-phone-line me-1"></i><?= e($request['requester_phone']) ?></p><?php endif; ?>
                            <?php if ($request['department']): ?><p class="mb-0 text-muted">Bộ phận: <?= e($request['department']) ?></p><?php endif; ?>
                        <?php else: ?>
                            <p class="text-muted">-</p>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Đơn vị thi công</h6>
                        <p class="mb-0 fw-medium"><?= e($request['contractor'] ?: '-') ?></p>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-2">Ngày yêu cầu TC</h6>
                        <p class="mb-0 fw-medium"><?= $request['requested_date'] ? date('d/m/Y', strtotime($request['requested_date'])) : '-' ?></p>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-2">Ngày thi công</h6>
                        <p class="mb-0 fw-medium"><?= $request['execution_date'] ? date('d/m/Y H:i', strtotime($request['execution_date'])) : '-' ?></p>
                    </div>

                    <div class="col-12">
                        <h6 class="text-muted mb-2"><i class="ri-map-pin-line me-1"></i>Địa chỉ lắp đặt</h6>
                        <p class="mb-0"><?= nl2br(e($request['installation_address'] ?: '-')) ?></p>
                    </div>

                    <?php if ($request['customer_contact_name'] || $request['customer_contact_phone']): ?>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Người liên hệ tại công trình</h6>
                        <p class="mb-1 fw-medium"><?= e($request['customer_contact_name'] ?: '-') ?></p>
                        <?php if ($request['customer_contact_phone']): ?><p class="mb-0 text-muted"><i class="ri-phone-line me-1"></i><?= e($request['customer_contact_phone']) ?></p><?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Items -->
        <div class="card mt-3">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ri-list-check me-1"></i> Nội dung thi công</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px">STT</th>
                                <th>Mã SP</th>
                                <th>Tên hàng</th>
                                <th>Kích thước, màu sắc</th>
                                <th>ĐVT</th>
                                <th class="text-end">SL</th>
                                <th>Check hàng</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($items)): foreach ($items as $i => $it): ?>
                                <tr>
                                    <td class="text-center text-muted"><?= $i + 1 ?></td>
                                    <td><?= e($it['product_sku'] ?: ($it['p_sku'] ?? '')) ?></td>
                                    <td><?= e($it['product_name']) ?></td>
                                    <td><?= e($it['size_color'] ?: '-') ?></td>
                                    <td><?= e($it['unit']) ?></td>
                                    <td class="text-end"><?= rtrim(rtrim(number_format((float)$it['quantity'], 2), '0'), '.') ?></td>
                                    <td><?= $it['check_status'] ? '<span class="badge bg-' . ($it['check_status'] === 'Đã kiểm' ? 'success' : 'warning') . '-subtle text-' . ($it['check_status'] === 'Đã kiểm' ? 'success' : 'warning') . '">' . e($it['check_status']) . '</span>' : '-' ?></td>
                                    <td><?= e($it['notes'] ?: '-') ?></td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="8" class="text-center text-muted py-3">Chưa có dòng nào</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Report -->
        <?php if ($request['installer_name'] || $request['condition_report'] || $request['notes']): ?>
        <div class="card mt-3">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ri-tools-line me-1"></i> Điều phối & báo cáo</h5></div>
            <div class="card-body">
                <?php if ($request['installer_name']): ?>
                    <p class="mb-2"><strong>Người thi công:</strong> <?= e($request['installer_name']) ?></p>
                <?php endif; ?>
                <?php if ($request['condition_report']): ?>
                    <h6 class="text-muted mb-1">Báo cáo tình trạng hàng hóa</h6>
                    <p class="mb-3"><?= nl2br(e($request['condition_report'])) ?></p>
                <?php endif; ?>
                <?php if ($request['notes']): ?>
                    <h6 class="text-muted mb-1">Ghi chú nội bộ</h6>
                    <p class="mb-0"><?= nl2br(e($request['notes'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-3">
        <!-- Status quick change -->
        <div class="card">
            <div class="card-header"><h6 class="card-title mb-0">Trạng thái</h6></div>
            <div class="card-body">
                <form method="POST" action="<?= url('installation-requests/' . $request['id'] . '/status') ?>">
                    <?= csrf_field() ?>
                    <select name="status" class="form-select mb-2" onchange="this.form.submit()">
                        <?php foreach (['pending','scheduled','completed','cancelled'] as $s): ?>
                            <option value="<?= $s ?>" <?= $request['status'] === $s ? 'selected' : '' ?>><?= e($ctrl::statusLabel($s)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header"><h6 class="card-title mb-0">Thông tin</h6></div>
            <div class="card-body">
                <p class="mb-2"><i class="ri-user-line me-1 text-muted"></i>Phụ trách: <strong><?= e($request['owner_name'] ?: '-') ?></strong></p>
                <p class="mb-2"><i class="ri-user-add-line me-1 text-muted"></i>Tạo bởi: <?= e($request['created_by_name'] ?: '-') ?></p>
                <p class="mb-0"><i class="ri-time-line me-1 text-muted"></i>Tạo lúc: <?= $request['created_at'] ? date('d/m/Y H:i', strtotime($request['created_at'])) : '-' ?></p>
            </div>
        </div>
    </div>
</div>
