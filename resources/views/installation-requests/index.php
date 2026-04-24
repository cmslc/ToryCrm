<?php
$pageTitle = 'Yêu cầu thi công';
$ctrl = \App\Controllers\InstallationRequestController::class;
$currentStatus = $filters['status'] ?? '';
$countByStatus = [];
foreach ($statusCounts ?? [] as $sc) $countByStatus[$sc['status']] = $sc['count'];
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Yêu cầu thi công</h4>
    <a href="<?= url('installation-requests/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Tạo yêu cầu</a>
</div>

<div class="card mb-3">
    <div class="card-header p-2">
        <form method="GET" action="<?= url('installation-requests') ?>" class="d-flex align-items-center gap-2 flex-wrap">
            <div class="search-box" style="min-width:200px;max-width:260px">
                <input type="text" class="form-control" name="search" placeholder="Mã CF, khách hàng, địa chỉ..." value="<?= e($filters['search'] ?? '') ?>">
                <i class="ri-search-line search-icon"></i>
            </div>
            <select name="owner_id" class="form-select" style="width:auto;min-width:160px" onchange="this.form.submit()">
                <option value="">Tất cả người phụ trách</option>
                <?php foreach ($users ?? [] as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= ($filters['owner_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="status" value="<?= e($currentStatus) ?>">
            <button type="submit" class="btn btn-primary"><i class="ri-search-line me-1"></i> Tìm</button>
            <?php if (!empty(array_filter($filters ?? []))): ?>
                <a href="<?= url('installation-requests') ?>" class="btn btn-soft-danger btn-icon" title="Xóa lọc"><i class="ri-refresh-line"></i></a>
            <?php endif; ?>
        </form>
    </div>
    <div class="card-body py-2 px-3 d-flex align-items-center gap-1 border-top">
        <div class="flex-grow-1 d-flex" style="overflow-x:auto;scrollbar-width:none;min-width:0">
            <div class="d-flex gap-1 flex-nowrap">
                <a href="<?= url('installation-requests?' . http_build_query(array_merge($filters, ['status' => '']))) ?>"
                   class="btn <?= $currentStatus === '' ? 'btn-dark' : 'btn-soft-dark' ?> rounded-pill text-nowrap waves-effect">
                    Tất cả <span class="badge rounded-pill bg-danger ms-1"><?= number_format((int)($totalAll ?? 0)) ?></span>
                </a>
                <?php foreach (['pending','scheduled','completed','cancelled'] as $s):
                    $color = $ctrl::statusColor($s);
                    $isActive = $currentStatus === $s;
                ?>
                <a href="<?= url('installation-requests?' . http_build_query(array_merge($filters, ['status' => $s]))) ?>"
                   class="btn <?= $isActive ? "btn-{$color}" : "btn-soft-{$color}" ?> rounded-pill text-nowrap waves-effect">
                    <?= e($ctrl::statusLabel($s)) ?>
                    <span class="badge rounded-pill bg-danger ms-1"><?= number_format((int)($countByStatus[$s] ?? 0)) ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-sticky mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Mã CF</th>
                        <th>Khách hàng</th>
                        <th>Địa chỉ lắp đặt</th>
                        <th>Đơn hàng gốc</th>
                        <th>Ngày yêu cầu</th>
                        <th>Người phụ trách</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($requests['items'])): ?>
                        <?php foreach ($requests['items'] as $r):
                            $cName = $r['c_company_name'] ?: ($r['c_full_name'] ?: $r['customer_contact_name']);
                        ?>
                            <tr>
                                <td>
                                    <a href="<?= url('installation-requests/' . $r['id']) ?>" class="fw-medium"><?= e($r['code']) ?></a>
                                </td>
                                <td>
                                    <?php if ($cName): ?>
                                        <div class="fw-medium"><?= e($cName) ?></div>
                                        <?php if ($r['customer_contact_name']): ?>
                                            <small class="text-muted"><?= e($r['customer_contact_name']) ?><?= $r['customer_contact_phone'] ? ' · ' . e($r['customer_contact_phone']) : '' ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($r['installation_address']): ?>
                                        <small><?= e(mb_strimwidth($r['installation_address'], 0, 80, '...', 'UTF-8')) ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($r['order_id'] && $r['order_number']): ?>
                                        <a href="<?= url('orders/' . $r['order_id']) ?>" class="text-primary"><?= e($r['order_number']) ?></a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $r['requested_date'] ? format_date($r['requested_date']) : '-' ?></td>
                                <td>
                                    <?php if ($r['owner_name']): ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if (!empty($r['owner_avatar'])): ?>
                                                <img src="<?= asset($r['owner_avatar']) ?>" class="rounded-circle" width="24" height="24" style="object-fit:cover">
                                            <?php else: ?>
                                                <span class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:24px;height:24px;font-size:11px"><?= mb_substr($r['owner_name'], 0, 1) ?></span>
                                            <?php endif; ?>
                                            <span><?= e($r['owner_name']) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $ctrl::statusColor($r['status']) ?>-subtle text-<?= $ctrl::statusColor($r['status']) ?>"><?= e($ctrl::statusLabel($r['status'])) ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="<?= url('installation-requests/' . $r['id']) ?>" class="btn btn-soft-primary btn-icon" title="Xem"><i class="ri-eye-line"></i></a>
                                        <a href="<?= url('installation-requests/' . $r['id'] . '/edit') ?>" class="btn btn-soft-secondary btn-icon" title="Sửa"><i class="ri-pencil-line"></i></a>
                                        <a href="<?= url('installation-requests/' . $r['id'] . '/pdf') ?>" target="_blank" class="btn btn-soft-info btn-icon" title="In"><i class="ri-printer-line"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="ri-tools-line fs-1 d-block mb-2"></i>
                                Chưa có yêu cầu thi công nào.
                                <a href="<?= url('installation-requests/create') ?>">Tạo mới</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (($requests['total_pages'] ?? 1) > 1): ?>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">Tổng <?= (int)$requests['total'] ?> yêu cầu</div>
            <nav>
                <ul class="pagination mb-0">
                    <?php for ($p = 1; $p <= $requests['total_pages']; $p++): ?>
                        <li class="page-item <?= $p == $requests['page'] ? 'active' : '' ?>">
                            <a class="page-link" href="<?= url('installation-requests?' . http_build_query(array_merge($filters, ['page' => $p]))) ?>"><?= $p ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>
