<?php $pageTitle = e($contact['first_name'] . ' ' . ($contact['last_name'] ?? '')); ?>

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Chi tiết khách hàng</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?= url('contacts') ?>">Khách hàng</a></li>
                            <li class="breadcrumb-item active"><?= e($contact['first_name']) ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Profile Card -->
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="avatar-lg mx-auto mb-3">
                            <div class="avatar-title rounded-circle bg-primary-subtle text-primary fs-24">
                                <?= strtoupper(substr($contact['first_name'], 0, 1)) ?>
                            </div>
                        </div>
                        <h5 class="mb-1"><?= e($contact['first_name'] . ' ' . ($contact['last_name'] ?? '')) ?></h5>
                        <p class="text-muted mb-0"><?= e($contact['position'] ?? '') ?></p>
                        <?php if ($contact['company_name']): ?>
                            <p class="text-muted">
                                <a href="<?= url('companies/' . $contact['company_id']) ?>"><?= e($contact['company_name']) ?></a>
                            </p>
                        <?php endif; ?>

                        <?php
                        $sColors = ['new' => 'info', 'contacted' => 'primary', 'qualified' => 'warning', 'converted' => 'success', 'lost' => 'danger'];
                        $sLabels = ['new' => 'Mới', 'contacted' => 'Đã liên hệ', 'qualified' => 'Tiềm năng', 'converted' => 'Chuyển đổi', 'lost' => 'Mất'];
                        ?>
                        <span class="badge bg-<?= $sColors[$contact['status']] ?? 'secondary' ?> fs-12">
                            <?= $sLabels[$contact['status']] ?? $contact['status'] ?>
                        </span>

                        <!-- Bonus Points -->
                        <div class="mt-3 p-2 bg-warning-subtle rounded">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <i class="ri-star-fill text-warning fs-20"></i>
                                <span class="fw-semibold fs-16"><?= number_format($contact['bonus_points'] ?? 0) ?> điểm</span>
                            </div>
                            <a href="<?= url('contacts/' . $contact['id'] . '/bonus-points') ?>" class="btn btn-sm btn-soft-warning w-100 mt-2">
                                <i class="ri-add-line me-1"></i> Quản lý điểm
                            </a>
                        </div>

                        <div class="mt-4 d-flex gap-2 justify-content-center flex-wrap">
                            <a href="<?= url('contacts/' . $contact['id'] . '/edit') ?>" class="btn btn-primary btn-sm">
                                <i class="ri-pencil-line me-1"></i> Sửa
                            </a>
                            <a href="<?= url('contacts/' . $contact['id'] . '/bonus-points') ?>" class="btn btn-warning btn-sm">
                                <i class="ri-star-line me-1"></i> Điểm thưởng
                            </a>
                            <form method="POST" action="<?= url('contacts/' . $contact['id'] . '/delete') ?>" onsubmit="return confirm('Xác nhận xóa?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-sm"><i class="ri-delete-bin-line me-1"></i> Xóa</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Change Owner -->
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Người phụ trách</h5></div>
                    <div class="card-body">
                        <p class="mb-2"><strong><?= e($contact['owner_name'] ?? 'Chưa gán') ?></strong></p>
                        <form method="POST" action="<?= url('contacts/' . $contact['id'] . '/change-owner') ?>">
                            <?= csrf_field() ?>
                            <div class="input-group input-group-sm">
                                <select name="owner_id" class="form-select form-select-sm">
                                    <option value="">Chọn người mới</option>
                                    <?php
                                    $allUsers = \Core\Database::fetchAll("SELECT id, name FROM users WHERE is_active = 1 ORDER BY name");
                                    foreach ($allUsers as $u): ?>
                                        <option value="<?= $u['id'] ?>" <?= ($contact['owner_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-soft-primary"><i class="ri-refresh-line"></i></button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Thông tin liên hệ</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <th class="text-muted" width="35%"><i class="ri-mail-line me-2"></i>Email</th>
                                        <td><?= e($contact['email'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted"><i class="ri-phone-line me-2"></i>Điện thoại</th>
                                        <td><?= e($contact['phone'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted"><i class="ri-smartphone-line me-2"></i>Di động</th>
                                        <td><?= e($contact['mobile'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted"><i class="ri-map-pin-line me-2"></i>Địa chỉ</th>
                                        <td><?= e($contact['address'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted"><i class="ri-building-line me-2"></i>Thành phố</th>
                                        <td><?= e($contact['city'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted"><i class="ri-user-line me-2"></i>Giới tính</th>
                                        <td><?php $g = ['male' => 'Nam', 'female' => 'Nữ', 'other' => 'Khác']; echo $g[$contact['gender'] ?? ''] ?? '-'; ?></td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted"><i class="ri-cake-2-line me-2"></i>Sinh nhật</th>
                                        <td><?= $contact['date_of_birth'] ? format_date($contact['date_of_birth']) : '-' ?></td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted"><i class="ri-star-line me-2"></i>Điểm</th>
                                        <td><span class="badge bg-primary"><?= $contact['score'] ?? 0 ?></span></td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted"><i class="ri-links-line me-2"></i>Nguồn</th>
                                        <td>
                                            <?php if ($contact['source_name']): ?>
                                                <span class="badge" style="background-color: <?= safe_color($contact['source_color']) ?>"><?= e($contact['source_name']) ?></span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted"><i class="ri-user-star-line me-2"></i>Phụ trách</th>
                                        <td><?= e($contact['owner_name'] ?? '-') ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-xl-8">
                <!-- Description -->
                <?php if ($contact['description']): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Ghi chú</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0"><?= nl2br(e($contact['description'])) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Thêm hoạt động</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('activities/store') ?>" class="row g-2">
                            <?= csrf_field() ?>
                            <input type="hidden" name="contact_id" value="<?= $contact['id'] ?>">
                            <div class="col-md-3">
                                <select name="type" class="form-select">
                                    <option value="note">Ghi chú</option>
                                    <option value="call">Cuộc gọi</option>
                                    <option value="email">Email</option>
                                    <option value="meeting">Cuộc họp</option>
                                </select>
                            </div>
                            <div class="col-md-7">
                                <input type="text" class="form-control" name="title" placeholder="Nội dung..." required>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Thêm</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Activities -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Lịch sử hoạt động</h5>
                    </div>
                    <div class="card-body">
                        <div class="activity-timeline">
                            <?php if (!empty($activities)): ?>
                                <?php foreach ($activities as $act): ?>
                                    <div class="activity-item d-flex mb-3">
                                        <div class="flex-shrink-0">
                                            <?php
                                            $typeIcons = ['note' => 'ri-file-text-line', 'call' => 'ri-phone-line', 'email' => 'ri-mail-line', 'meeting' => 'ri-calendar-line', 'task' => 'ri-task-line'];
                                            $typeColors = ['note' => 'primary', 'call' => 'success', 'email' => 'info', 'meeting' => 'warning', 'task' => 'danger'];
                                            ?>
                                            <div class="avatar-xs">
                                                <div class="avatar-title rounded-circle bg-<?= $typeColors[$act['type']] ?? 'primary' ?>-subtle text-<?= $typeColors[$act['type']] ?? 'primary' ?>">
                                                    <i class="<?= $typeIcons[$act['type']] ?? 'ri-file-text-line' ?>"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-0"><?= e($act['title']) ?></h6>
                                            <?php if ($act['description']): ?>
                                                <p class="text-muted mb-0"><?= e($act['description']) ?></p>
                                            <?php endif; ?>
                                            <small class="text-muted"><?= time_ago($act['created_at']) ?> - <?= e($act['user_name'] ?? 'Hệ thống') ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted text-center">Chưa có hoạt động</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Related Deals -->
                <div class="card">
                    <div class="card-header d-flex">
                        <h5 class="card-title mb-0 flex-grow-1">Cơ hội liên quan</h5>
                        <a href="<?= url('deals/create?contact_id=' . $contact['id']) ?>" class="btn btn-sm btn-soft-primary">Thêm cơ hội</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($deals)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tên</th>
                                            <th>Giá trị</th>
                                            <th>Giai đoạn</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($deals as $deal): ?>
                                            <tr>
                                                <td><a href="<?= url('deals/' . $deal['id']) ?>"><?= e($deal['title']) ?></a></td>
                                                <td><?= format_money($deal['value']) ?></td>
                                                <td><span class="badge" style="background-color: <?= safe_color($deal['stage_color'] ?? null) ?>"><?= e($deal['stage_name'] ?? '') ?></span></td>
                                                <td>
                                                    <?php $dColors = ['open' => 'primary', 'won' => 'success', 'lost' => 'danger']; ?>
                                                    <span class="badge bg-<?= $dColors[$deal['status']] ?? 'secondary' ?>"><?= $deal['status'] ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center">Chưa có cơ hội</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
