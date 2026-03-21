<?php
$pageTitle = ($transaction['type'] === 'receipt' ? 'Phiếu thu' : 'Phiếu chi') . ' ' . $transaction['transaction_code'];
$statusColors = ['draft'=>'secondary','confirmed'=>'success','cancelled'=>'danger'];
$statusLabels = ['draft'=>'Nháp','confirmed'=>'Đã xác nhận','cancelled'=>'Đã hủy'];
?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0"><?= $pageTitle ?></h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('fund') ?>">Quỹ</a></li>
                <li class="breadcrumb-item active"><?= e($transaction['transaction_code']) ?></li>
            </ol>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1"><?= e($transaction['transaction_code']) ?></h5>
                            <div class="d-flex gap-2">
                                <?= $transaction['type'] === 'receipt'
                                    ? '<span class="badge bg-success">Phiếu thu</span>'
                                    : '<span class="badge bg-danger">Phiếu chi</span>' ?>
                                <span class="badge bg-<?= $statusColors[$transaction['status']] ?? 'secondary' ?>"><?= $statusLabels[$transaction['status']] ?? '' ?></span>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <?php if ($transaction['status'] === 'draft'): ?>
                                <form method="POST" action="<?= url('fund/' . $transaction['id'] . '/confirm') ?>" class="d-inline" data-confirm="Xác nhận phiếu này?">
                                    <?= csrf_field() ?>
                                    <button class="btn btn btn-success"><i class="ri-check-line me-1"></i>Xác nhận</button>
                                </form>
                                <form method="POST" action="<?= url('fund/' . $transaction['id'] . '/cancel') ?>" class="d-inline" data-confirm="Xác nhận hủy phiếu?">
                                    <?= csrf_field() ?>
                                    <button class="btn btn btn-soft-danger"><i class="ri-close-line me-1"></i>Hủy</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4 py-3 bg-light rounded">
                            <p class="text-muted mb-1">Số tiền</p>
                            <h2 class="mb-0 <?= $transaction['type'] === 'receipt' ? 'text-success' : 'text-danger' ?>"><?= format_money($transaction['amount']) ?></h2>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <td class="text-muted" style="width: 200px;">Mã phiếu</td>
                                        <td class="fw-medium"><?= e($transaction['transaction_code']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Loại</td>
                                        <td><?= $transaction['type'] === 'receipt' ? 'Phiếu thu' : 'Phiếu chi' ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Tài khoản quỹ</td>
                                        <td><?= e($transaction['account_name'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Danh mục</td>
                                        <td><?= e($transaction['category'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Ngày giao dịch</td>
                                        <td><?= format_date($transaction['transaction_date']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Liên hệ</td>
                                        <td><?= e($transaction['contact_name'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Công ty</td>
                                        <td><?= e($transaction['company_name'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Mô tả</td>
                                        <td><?= !empty($transaction['description']) ? nl2br(e($transaction['description'])) : '-' ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Trạng thái</td>
                                        <td><span class="badge bg-<?= $statusColors[$transaction['status']] ?? 'secondary' ?>"><?= $statusLabels[$transaction['status']] ?? '' ?></span></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Ngày tạo</td>
                                        <td><?= format_datetime($transaction['created_at']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Cập nhật</td>
                                        <td><?= format_datetime($transaction['updated_at']) ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body text-center">
                        <a href="<?= url('fund') ?>" class="btn btn-soft-secondary w-100"><i class="ri-arrow-left-line me-1"></i> Quay lại danh sách</a>
                    </div>
                </div>
            </div>
        </div>
