<?php
$pageTitle = 'Hợp đồng ' . $contract['contract_number'];
$sc = ['draft' => 'secondary', 'sent' => 'info', 'signed' => 'primary', 'active' => 'success', 'expired' => 'danger', 'cancelled' => 'dark'];
$sl = ['draft' => 'Nháp', 'sent' => 'Đã gửi', 'signed' => 'Đã ký', 'active' => 'Hoạt động', 'expired' => 'Hết hạn', 'cancelled' => 'Đã hủy'];
$tc = ['service' => 'Dịch vụ', 'product' => 'Sản phẩm', 'rental' => 'Cho thuê', 'maintenance' => 'Bảo trì', 'other' => 'Khác'];
$rcl = ['monthly' => 'Hàng tháng', 'quarterly' => 'Hàng quý', 'yearly' => 'Hàng năm'];

// Days remaining
$daysRemaining = null;
if (!empty($contract['end_date'])) {
    $end = new DateTime($contract['end_date']);
    $now = new DateTime();
    $diff = $now->diff($end);
    $daysRemaining = $end > $now ? $diff->days : -$diff->days;
}

// Timeline steps
$steps = ['draft', 'sent', 'signed', 'active', 'expired'];
$stepLabels = ['draft' => 'Nháp', 'sent' => 'Đã gửi', 'signed' => 'Đã ký', 'active' => 'Hoạt động', 'expired' => 'Hết hạn'];
$currentStepIndex = array_search($contract['status'], $steps);
if ($currentStepIndex === false) $currentStepIndex = 0;
?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">
                <?= e($contract['contract_number']) ?> - <?= e($contract['title']) ?>
                <span class="badge bg-<?= $sc[$contract['status']] ?? 'secondary' ?> ms-2"><?= $sl[$contract['status']] ?? '' ?></span>
            </h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('contracts') ?>">Hợp đồng</a></li>
                <li class="breadcrumb-item active"><?= e($contract['contract_number']) ?></li>
            </ol>
        </div>

        <!-- Info Cards -->
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card card-animate">
                    <div class="card-body text-center">
                        <p class="text-muted mb-1">Giá trị</p>
                        <h5 class="mb-0 text-primary"><?= format_money($contract['value']) ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-animate">
                    <div class="card-body text-center">
                        <p class="text-muted mb-1">Thời hạn</p>
                        <h5 class="mb-0">
                            <?= !empty($contract['start_date']) ? format_date($contract['start_date']) : '?' ?>
                            - <?= !empty($contract['end_date']) ? format_date($contract['end_date']) : '?' ?>
                        </h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-animate">
                    <div class="card-body text-center">
                        <p class="text-muted mb-1">Định kỳ</p>
                        <h5 class="mb-0">
                            <?php if ($contract['recurring_value'] > 0): ?>
                                <?= format_money($contract['recurring_value']) ?> / <?= $rcl[$contract['recurring_cycle']] ?? '-' ?>
                            <?php else: ?>
                                <span class="text-muted">Không</span>
                            <?php endif; ?>
                        </h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-animate">
                    <div class="card-body text-center">
                        <p class="text-muted mb-1">Còn lại</p>
                        <h5 class="mb-0 <?= $daysRemaining !== null && $daysRemaining < 30 ? ($daysRemaining < 0 ? 'text-danger' : 'text-warning') : 'text-success' ?>">
                            <?php if ($daysRemaining !== null): ?>
                                <?= $daysRemaining >= 0 ? $daysRemaining . ' ngày' : abs($daysRemaining) . ' ngày trước' ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center position-relative" style="padding: 0 40px;">
                    <div class="position-absolute" style="top:50%;left:60px;right:60px;height:3px;background:#e9ecef;transform:translateY(-50%);z-index:0;"></div>
                    <?php foreach ($steps as $idx => $step): ?>
                        <?php
                        $isActive = $idx <= $currentStepIndex;
                        $isCurrent = $idx === $currentStepIndex;
                        ?>
                        <div class="text-center position-relative" style="z-index:1;">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-1"
                                 style="width:36px;height:36px;border:3px solid <?= $isActive ? '#0ab39c' : '#e9ecef' ?>;background:<?= $isCurrent ? '#0ab39c' : ($isActive ? '#d1f5ef' : '#fff') ?>;color:<?= $isCurrent ? '#fff' : ($isActive ? '#0ab39c' : '#adb5bd') ?>;">
                                <?php if ($isActive && !$isCurrent): ?>
                                    <i class="ri-check-line"></i>
                                <?php else: ?>
                                    <?= $idx + 1 ?>
                                <?php endif; ?>
                            </div>
                            <div class="<?= $isCurrent ? 'fw-bold text-success' : ($isActive ? 'text-success' : 'text-muted') ?>" style="font-size:12px;"><?= $stepLabels[$step] ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Contract Details -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Chi tiết hợp đồng</h5>
                        <div class="d-flex gap-1 flex-wrap">
                            <?php if (in_array($contract['status'], ['draft', 'sent', 'signed'])): ?>
                                <form method="POST" action="<?= url('contracts/' . $contract['id'] . '/sign') ?>" class="d-inline" data-confirm="Xác nhận ký hợp đồng?">
                                    <?= csrf_field() ?><button class="btn btn-soft-success"><i class="ri-quill-pen-line me-1"></i>Ký hợp đồng</button>
                                </form>
                            <?php endif; ?>
                            <?php if (in_array($contract['status'], ['active', 'expired'])): ?>
                                <form method="POST" action="<?= url('contracts/' . $contract['id'] . '/renew') ?>" class="d-inline" data-confirm="Gia hạn hợp đồng? Sẽ tạo hợp đồng mới.">
                                    <?= csrf_field() ?><button class="btn btn-soft-warning"><i class="ri-refresh-line me-1"></i>Gia hạn</button>
                                </form>
                            <?php endif; ?>
                            <a href="<?= url('contracts/' . $contract['id'] . '/edit') ?>" class="btn btn-soft-primary"><i class="ri-pencil-line me-1"></i>Sửa</a>
                            <form method="POST" action="<?= url('contracts/' . $contract['id'] . '/delete') ?>" class="d-inline" data-confirm="Xóa hợp đồng?">
                                <?= csrf_field() ?><button class="btn btn-soft-danger"><i class="ri-delete-bin-line me-1"></i>Xóa</button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Khách hàng</h6>
                                <?php $contactName = trim(($contract['contact_first_name'] ?? '') . ' ' . ($contract['contact_last_name'] ?? '')); ?>
                                <?php if ($contactName): ?>
                                    <p class="mb-1 fw-medium"><?= e($contactName) ?></p>
                                    <?php if (!empty($contract['contact_email'])): ?><p class="mb-1 text-muted"><i class="ri-mail-line me-1"></i><?= e($contract['contact_email']) ?></p><?php endif; ?>
                                    <?php if (!empty($contract['contact_phone'])): ?><p class="mb-0 text-muted"><i class="ri-phone-line me-1"></i><?= e($contract['contact_phone']) ?></p><?php endif; ?>
                                <?php else: ?>
                                    <p class="text-muted">-</p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Công ty</h6>
                                <p class="mb-0 fw-medium"><?= e($contract['company_name'] ?? '-') ?></p>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <h6 class="text-muted mb-1">Loại hợp đồng</h6>
                                <p class="mb-0"><?= $tc[$contract['type']] ?? $contract['type'] ?></p>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-muted mb-1">Người phụ trách</h6>
                                <p class="mb-0"><?= e($contract['owner_name'] ?? '-') ?></p>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-muted mb-1">Tự động gia hạn</h6>
                                <p class="mb-0"><?= $contract['auto_renew'] ? '<span class="badge bg-success">Có</span>' : '<span class="badge bg-secondary">Không</span>' ?></p>
                            </div>
                        </div>

                        <?php if (!empty($contract['signed_date'])): ?>
                            <div class="mb-3">
                                <h6 class="text-muted mb-1">Ngày ký</h6>
                                <p class="mb-0"><?= format_date($contract['signed_date']) ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($contract['notes'])): ?>
                            <div class="mb-3">
                                <h6 class="text-muted mb-1">Ghi chú</h6>
                                <p class="mb-0"><?= nl2br(e($contract['notes'])) ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($contract['terms'])): ?>
                            <div class="mb-3">
                                <h6 class="text-muted mb-1">Điều khoản</h6>
                                <div class="border rounded p-3 bg-light"><?= nl2br(e($contract['terms'])) ?></div>
                            </div>
                        <?php endif; ?>

                        <div class="text-muted mt-3">
                            <small>Người tạo: <?= e($contract['created_by_name'] ?? '-') ?> | Ngày tạo: <?= !empty($contract['created_at']) ? format_datetime($contract['created_at']) : '-' ?></small>
                        </div>
                    </div>
                </div>

                <!-- Related Orders -->
                <?php if (!empty($orders)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Đơn hàng liên quan</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Mã đơn</th>
                                            <th>Tổng tiền</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $osc = ['draft'=>'secondary','sent'=>'info','confirmed'=>'primary','processing'=>'warning','completed'=>'success','cancelled'=>'danger'];
                                        $osl = ['draft'=>'Nháp','sent'=>'Đã gửi','confirmed'=>'Xác nhận','processing'=>'Đang xử lý','completed'=>'Hoàn thành','cancelled'=>'Đã hủy'];
                                        foreach ($orders as $o):
                                        ?>
                                            <tr>
                                                <td><a href="<?= url('orders/' . $o['id']) ?>" class="fw-medium"><?= e($o['order_number']) ?></a></td>
                                                <td><?= format_money($o['total']) ?></td>
                                                <td><span class="badge bg-<?= $osc[$o['status']] ?? 'secondary' ?>"><?= $osl[$o['status']] ?? '' ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <!-- Related Deal -->
                <?php if (!empty($contract['deal_title'])): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Cơ hội liên quan</h5>
                        </div>
                        <div class="card-body">
                            <a href="<?= url('deals/' . $contract['deal_id']) ?>" class="fw-medium">
                                <i class="ri-hand-coin-line me-1"></i><?= e($contract['deal_title']) ?>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Quick Info -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Thông tin nhanh</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Số hợp đồng</span>
                                <span class="fw-medium"><?= e($contract['contract_number']) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Trạng thái</span>
                                <span class="badge bg-<?= $sc[$contract['status']] ?? 'secondary' ?>"><?= $sl[$contract['status']] ?? '' ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Loại</span>
                                <span><?= $tc[$contract['type']] ?? $contract['type'] ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Giá trị</span>
                                <span class="fw-medium"><?= format_money($contract['value']) ?></span>
                            </li>
                            <?php if (!empty($contract['start_date'])): ?>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Bắt đầu</span>
                                    <span><?= format_date($contract['start_date']) ?></span>
                                </li>
                            <?php endif; ?>
                            <?php if (!empty($contract['end_date'])): ?>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Kết thúc</span>
                                    <span class="<?= ($daysRemaining !== null && $daysRemaining < 30) ? 'text-danger fw-bold' : '' ?>"><?= format_date($contract['end_date']) ?></span>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
