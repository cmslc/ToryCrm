<?php $pageTitle = e($campaign['name']); ?>

<?php
    $typeLabels = ['email' => 'Email', 'sms' => 'SMS', 'call' => 'Gọi điện', 'social' => 'Mạng xã hội', 'other' => 'Khác'];
    $statusLabels = ['draft' => 'Nháp', 'running' => 'Đang chạy', 'paused' => 'Tạm dừng', 'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy'];
    $statusColors = ['draft' => 'secondary', 'running' => 'success', 'paused' => 'warning', 'completed' => 'primary', 'cancelled' => 'danger'];
?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1"><?= e($campaign['name']) ?></h4>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted"><?= e($campaign['campaign_code'] ?? '') ?></span>
                    <span class="badge bg-<?= $statusColors[$campaign['status']] ?? 'secondary' ?>">
                        <?= $statusLabels[$campaign['status']] ?? $campaign['status'] ?>
                    </span>
                    <span class="badge bg-info-subtle text-info">
                        <?= $typeLabels[$campaign['type']] ?? $campaign['type'] ?>
                    </span>
                </div>
            </div>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('campaigns') ?>">Chiến dịch</a></li>
                <li class="breadcrumb-item active"><?= e($campaign['name']) ?></li>
            </ol>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <?php if ($campaign['description']): ?>
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Mô tả</h5></div>
                        <div class="card-body"><p><?= nl2br(e($campaign['description'])) ?></p></div>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">Liên hệ trong chiến dịch</h5>
                        <span class="badge bg-primary-subtle text-primary"><?= $contactStats['total'] ?? 0 ?> liên hệ</span>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('campaigns/' . $campaign['id'] . '/add-contact') ?>" class="row g-2 mb-4">
                            <?= csrf_field() ?>
                            <div class="col-md-8">
                                <select name="contact_id" class="form-select" required>
                                    <option value="">Chọn liên hệ...</option>
                                    <?php foreach ($contacts ?? [] as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= e($c['first_name'] . ' ' . ($c['last_name'] ?? '')) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100"><i class="ri-add-line me-1"></i> Thêm liên hệ</button>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tên liên hệ</th>
                                        <th>Email</th>
                                        <th>Điện thoại</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày thêm</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($campaignContacts)): ?>
                                        <?php
                                            $contactStatusLabels = ['pending' => 'Chờ xử lý', 'sent' => 'Đã gửi', 'reached' => 'Đã tiếp cận', 'converted' => 'Chuyển đổi', 'failed' => 'Thất bại'];
                                            $contactStatusColors = ['pending' => 'secondary', 'sent' => 'info', 'reached' => 'primary', 'converted' => 'success', 'failed' => 'danger'];
                                        ?>
                                        <?php foreach ($campaignContacts as $cc): ?>
                                            <tr>
                                                <td>
                                                    <a href="<?= url('contacts/' . $cc['contact_id']) ?>" class="fw-medium text-dark">
                                                        <?= e(($cc['first_name'] ?? '') . ' ' . ($cc['last_name'] ?? '')) ?>
                                                    </a>
                                                </td>
                                                <td><?= e($cc['email'] ?? '-') ?></td>
                                                <td><?= e($cc['phone'] ?? '-') ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $contactStatusColors[$cc['status']] ?? 'secondary' ?>-subtle text-<?= $contactStatusColors[$cc['status']] ?? 'secondary' ?>">
                                                        <?= $contactStatusLabels[$cc['status']] ?? $cc['status'] ?>
                                                    </span>
                                                </td>
                                                <td><?= time_ago($cc['created_at'] ?? '') ?></td>
                                                <td>
                                                    <form method="POST" action="<?= url('campaigns/' . $campaign['id'] . '/remove-contact') ?>" data-confirm="Xác nhận xóa liên hệ khỏi chiến dịch?">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="contact_id" value="<?= $cc['contact_id'] ?>">
                                                        <button class="btn btn btn-soft-danger"><i class="ri-close-line"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center py-4 text-muted"><i class="ri-user-line fs-1 d-block mb-2"></i>Chưa có liên hệ trong chiến dịch</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex gap-2 mb-4">
                            <a href="<?= url('campaigns/' . $campaign['id'] . '/edit') ?>" class="btn btn-primary btn"><i class="ri-pencil-line me-1"></i> Sửa</a>
                            <form method="POST" action="<?= url('campaigns/' . $campaign['id'] . '/delete') ?>" data-confirm="Xác nhận xóa chiến dịch?">
                                <?= csrf_field() ?>
                                <button class="btn btn-danger btn"><i class="ri-delete-bin-line me-1"></i> Xóa</button>
                            </form>
                        </div>
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th class="text-muted" width="40%">Loại</th>
                                <td><span class="badge bg-info-subtle text-info"><?= $typeLabels[$campaign['type']] ?? $campaign['type'] ?></span></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Trạng thái</th>
                                <td><span class="badge bg-<?= $statusColors[$campaign['status']] ?? 'secondary' ?>"><?= $statusLabels[$campaign['status']] ?? $campaign['status'] ?></span></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Ngân sách</th>
                                <td class="fw-medium"><?= format_money($campaign['budget'] ?? 0) ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Chi phí thực</th>
                                <td class="fw-medium"><?= format_money($campaign['actual_cost'] ?? 0) ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Ngày bắt đầu</th>
                                <td><?= $campaign['start_date'] ? format_date($campaign['start_date']) : '-' ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Ngày kết thúc</th>
                                <td><?= $campaign['end_date'] ? format_date($campaign['end_date']) : '-' ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Phụ trách</th>
                                <td><?= e($campaign['owner_name'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Ngày tạo</th>
                                <td><?= time_ago($campaign['created_at']) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Thống kê</h5></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Mục tiêu</span>
                            <span class="fw-medium"><?= number_format($contactStats['target'] ?? $campaign['target'] ?? 0) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Đã tiếp cận</span>
                            <span class="fw-medium text-primary"><?= number_format($contactStats['reached'] ?? $campaign['reached'] ?? 0) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Chuyển đổi</span>
                            <span class="fw-medium text-success"><?= number_format($contactStats['converted'] ?? $campaign['converted'] ?? 0) ?></span>
                        </div>
                        <?php
                            $target = $contactStats['target'] ?? $campaign['target'] ?? 0;
                            $reached = $contactStats['reached'] ?? $campaign['reached'] ?? 0;
                            $reachedPct = $target > 0 ? round($reached / $target * 100) : 0;
                        ?>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Tiến độ tiếp cận</small>
                                <small class="fw-medium"><?= $reachedPct ?>%</small>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-primary" style="width: <?= $reachedPct ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
