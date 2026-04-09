<?php $pageTitle = e($company['name']); ?>

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Chi tiết doanh nghiệp</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?= url('companies') ?>">Doanh nghiệp</a></li>
                            <li class="breadcrumb-item active"><?= e($company['name']) ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column - Profile -->
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="avatar-lg mx-auto mb-3">
                            <div class="avatar-title rounded-circle bg-info-subtle text-info fs-24">
                                <?= strtoupper(substr($company['name'], 0, 1)) ?>
                            </div>
                        </div>
                        <h5 class="mb-1"><?= e($company['name']) ?></h5>
                        <p class="text-muted mb-0"><?= e($company['industry'] ?? '') ?></p>
                        <?php if ($company['company_size']): ?>
                            <span class="badge bg-primary-subtle text-primary fs-12 mt-1"><?= e($company['company_size']) ?></span>
                        <?php endif; ?>

                        <div class="mt-4 d-flex gap-2 justify-content-center flex-wrap">
                            <a href="<?= url('companies/' . $company['id'] . '/edit') ?>" class="btn btn-primary">
                                <i class="ri-pencil-line me-1"></i> Sửa
                            </a>
                            <form method="POST" action="<?= url('companies/' . $company['id'] . '/delete') ?>" data-confirm="Xác nhận xóa doanh nghiệp?">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger"><i class="ri-delete-bin-line me-1"></i> Xóa</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Owner Card -->
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Người phụ trách</h5></div>
                    <div class="card-body">
                        <p class="mb-2"><strong><?= e($company['owner_name'] ?? 'Chưa gán') ?></strong></p>
                        <form method="POST" action="<?= url('companies/' . $company['id'] . '/change-owner') ?>">
                            <?= csrf_field() ?>
                            <div class="input-group">
                                <select name="owner_id" class="form-select">
                                    <option value="">Chọn người mới</option>
                                    <?php foreach ($users as $u): ?>
                                        <option value="<?= $u['id'] ?>" <?= ($company['owner_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-soft-primary"><i class="ri-refresh-line me-1"></i> Đổi</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Company Info -->
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Thông tin doanh nghiệp</h5></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <th class="text-muted" width="35%"><i class="ri-mail-line me-2"></i>Email</th>
                                        <td><?= e($company['email'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted"><i class="ri-phone-line me-2"></i>Điện thoại</th>
                                        <td><?= e($company['phone'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted"><i class="ri-global-line me-2"></i>Website</th>
                                        <td>
                                            <?php if ($company['website']): ?>
                                                <a href="<?= e($company['website']) ?>" target="_blank"><?= e($company['website']) ?></a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted"><i class="ri-file-list-line me-2"></i>MST</th>
                                        <td><?= e($company['tax_code'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted"><i class="ri-building-line me-2"></i>Ngành nghề</th>
                                        <td><?= e($company['industry'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted"><i class="ri-team-line me-2"></i>Quy mô</th>
                                        <td><?= e($company['company_size'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted"><i class="ri-map-pin-line me-2"></i>Địa chỉ</th>
                                        <td><?= e($company['address'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted"><i class="ri-building-2-line me-2"></i>Thành phố</th>
                                        <td><?= e($company['city'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted"><i class="ri-calendar-line me-2"></i>Ngày tạo</th>
                                        <td><?= format_datetime($company['created_at']) ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Tabbed Layout -->
            <div class="col-xl-8">
                <!-- Stats Bar -->
                <?php
                $activityCount = count($activities ?? []);
                $lastActivity = !empty($activities) ? $activities[0] : null;
                $orderStats = \Core\Database::fetch(
                    "SELECT COUNT(*) as order_count, COALESCE(SUM(total), 0) as total_value FROM orders WHERE company_id = ? AND is_deleted = 0",
                    [$company['id']]
                );
                $dealStats = \Core\Database::fetch(
                    "SELECT COUNT(*) as deal_count, COALESCE(SUM(value), 0) as total_value FROM deals WHERE company_id = ? AND status = 'won'",
                    [$company['id']]
                );
                ?>
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row text-center">
                            <div class="col">
                                <p class="text-muted mb-1 fs-12">Liên hệ</p>
                                <h5 class="mb-0 text-primary"><?= count($contacts ?? []) ?></h5>
                            </div>
                            <div class="col border-start">
                                <p class="text-muted mb-1 fs-12">Cơ hội</p>
                                <h5 class="mb-0 text-info"><?= count($deals ?? []) ?></h5>
                            </div>
                            <div class="col border-start">
                                <p class="text-muted mb-1 fs-12">Doanh thu (Won)</p>
                                <h5 class="mb-0 text-success"><?= format_money($dealStats['total_value'] ?? 0) ?></h5>
                            </div>
                            <div class="col border-start">
                                <p class="text-muted mb-1 fs-12">Đơn hàng</p>
                                <h5 class="mb-0 text-warning"><?= format_money($orderStats['total_value'] ?? 0) ?></h5>
                            </div>
                            <div class="col border-start">
                                <p class="text-muted mb-1 fs-12">Tương tác</p>
                                <h5 class="mb-0 text-secondary"><?= $activityCount ?></h5>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header p-0">
                        <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#tab-exchange" role="tab">
                                    <i class="ri-chat-3-line me-1"></i> Trao đổi
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tab-contacts" role="tab">
                                    <i class="ri-contacts-line me-1"></i> Liên hệ
                                    <?php if (!empty($contacts)): ?><span class="badge bg-primary ms-1"><?= count($contacts) ?></span><?php endif; ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tab-deals" role="tab">
                                    <i class="ri-hand-coin-line me-1"></i> Cơ hội
                                    <?php if (!empty($deals)): ?><span class="badge bg-primary ms-1"><?= count($deals) ?></span><?php endif; ?>
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button">
                                    <i class="ri-exchange-line me-1"></i> Giao dịch <i class="ri-arrow-down-s-line fs-12"></i>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" data-bs-toggle="tab" href="#tab-orders"><i class="ri-shopping-cart-line me-2"></i>Đơn hàng</a></li>
                                    <li><a class="dropdown-item" data-bs-toggle="tab" href="#tab-contracts"><i class="ri-file-shield-line me-2"></i>Hợp đồng</a></li>
                                    <li><a class="dropdown-item" data-bs-toggle="tab" href="#tab-quotations"><i class="ri-file-text-line me-2"></i>Báo giá</a></li>
                                    <li><a class="dropdown-item" data-bs-toggle="tab" href="#tab-debts"><i class="ri-money-dollar-circle-line me-2"></i>Công nợ</a></li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tab-tickets" role="tab">
                                    <i class="ri-customer-service-line me-1"></i> Ticket
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tab-notes" role="tab">
                                    <i class="ri-file-text-line me-1"></i> Ghi chú
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="tab-content">

                            <!-- Tab: Trao đổi -->
                            <div class="tab-pane active" id="tab-exchange" role="tabpanel">
                                <form method="POST" action="<?= url('activities/store') ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="company_id" value="<?= $company['id'] ?>">
                                    <input type="hidden" name="type" value="note" id="activityType">
                                    <div class="mb-3">
                                        <textarea name="title" class="form-control" rows="3" placeholder="Nhập nội dung trao đổi, ghi chú..." required></textarea>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-soft-primary activity-type-btn active" data-type="note" title="Ghi chú">
                                                <i class="ri-file-text-line"></i>
                                            </button>
                                            <button type="button" class="btn btn-soft-success activity-type-btn" data-type="call" title="Cuộc gọi">
                                                <i class="ri-phone-line"></i>
                                            </button>
                                            <button type="button" class="btn btn-soft-info activity-type-btn" data-type="email" title="Email">
                                                <i class="ri-mail-line"></i>
                                            </button>
                                            <button type="button" class="btn btn-soft-warning activity-type-btn" data-type="meeting" title="Cuộc họp">
                                                <i class="ri-calendar-line"></i>
                                            </button>
                                        </div>
                                        <button type="submit" class="btn btn-primary"><i class="ri-send-plane-fill me-1"></i> Gửi</button>
                                    </div>
                                </form>

                                <hr>

                                <!-- Activity Timeline -->
                                <div class="activity-timeline" id="activityList" style="max-height: 500px; overflow-y: auto;">
                                    <?php if (!empty($activities)): ?>
                                        <?php
                                        $typeIcons = ['note' => 'ri-file-text-line', 'call' => 'ri-phone-line', 'email' => 'ri-mail-line', 'meeting' => 'ri-calendar-line', 'system' => 'ri-settings-line'];
                                        $typeColors = ['note' => 'primary', 'call' => 'success', 'email' => 'info', 'meeting' => 'warning', 'system' => 'secondary'];
                                        $typeLabels = ['note' => 'Ghi chú', 'call' => 'Cuộc gọi', 'email' => 'Email', 'meeting' => 'Cuộc họp', 'system' => 'Hệ thống'];
                                        ?>
                                        <?php foreach ($activities as $act): ?>
                                            <div class="activity-item d-flex mb-3"
                                                 data-type="<?= e($act['type']) ?>"
                                                 data-user="<?= e($act['user_name'] ?? '') ?>"
                                                 data-text="<?= e(strtolower($act['title'] . ' ' . ($act['description'] ?? ''))) ?>">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar-xs">
                                                        <div class="avatar-title rounded-circle bg-<?= $typeColors[$act['type']] ?? 'primary' ?>-subtle text-<?= $typeColors[$act['type']] ?? 'primary' ?>">
                                                            <i class="<?= $typeIcons[$act['type']] ?? 'ri-file-text-line' ?>"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <span class="badge bg-<?= $typeColors[$act['type']] ?? 'primary' ?>-subtle text-<?= $typeColors[$act['type']] ?? 'primary' ?> me-2"><?= $typeLabels[$act['type']] ?? $act['type'] ?></span>
                                                        <small class="text-muted"><?= time_ago($act['created_at']) ?></small>
                                                    </div>
                                                    <p class="mb-0"><?= e($act['title']) ?></p>
                                                    <?php if (!empty($act['description'])): ?>
                                                        <p class="text-muted mb-0 fs-12"><?= e($act['description']) ?></p>
                                                    <?php endif; ?>
                                                    <small class="text-muted"><i class="ri-user-line me-1"></i><?= e($act['user_name'] ?? 'Hệ thống') ?></small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="ri-chat-3-line fs-36 text-muted"></i>
                                            <p class="text-muted mt-2">Chưa có hoạt động trao đổi</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Tab: Liên hệ -->
                            <div class="tab-pane" id="tab-contacts" role="tabpanel">
                                <div class="d-flex justify-content-between mb-3">
                                    <h6 class="mb-0">Danh sách liên hệ</h6>
                                    <a href="<?= url('contacts/create?company_id=' . $company['id']) ?>" class="btn btn-soft-primary"><i class="ri-add-line me-1"></i>Thêm liên hệ</a>
                                </div>
                                <?php if (!empty($contacts)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Tên</th>
                                                    <th>Chức vụ</th>
                                                    <th>Email</th>
                                                    <th>Điện thoại</th>
                                                    <th>Trạng thái</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $sColors = ['new' => 'info', 'contacted' => 'primary', 'qualified' => 'warning', 'converted' => 'success', 'lost' => 'danger'];
                                                $sLabels = ['new' => 'Mới', 'contacted' => 'Đã liên hệ', 'qualified' => 'Tiềm năng', 'converted' => 'Chuyển đổi', 'lost' => 'Mất'];
                                                ?>
                                                <?php foreach ($contacts as $c): ?>
                                                    <tr>
                                                        <td><a href="<?= url('contacts/' . $c['id']) ?>"><?= e($c['first_name'] . ' ' . ($c['last_name'] ?? '')) ?></a></td>
                                                        <td><?= e($c['position'] ?? '-') ?></td>
                                                        <td><?= e($c['email'] ?? '-') ?></td>
                                                        <td><?= e($c['phone'] ?? '-') ?></td>
                                                        <td>
                                                            <span class="badge bg-<?= $sColors[$c['status']] ?? 'secondary' ?>-subtle text-<?= $sColors[$c['status']] ?? 'secondary' ?>"><?= $sLabels[$c['status']] ?? $c['status'] ?></span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="ri-contacts-line fs-36 text-muted"></i>
                                        <p class="text-muted mt-2">Chưa có liên hệ</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Tab: Cơ hội -->
                            <div class="tab-pane" id="tab-deals" role="tabpanel">
                                <div class="d-flex justify-content-between mb-3">
                                    <h6 class="mb-0">Danh sách cơ hội</h6>
                                    <a href="<?= url('deals/create?company_id=' . $company['id']) ?>" class="btn btn-soft-primary"><i class="ri-add-line me-1"></i>Thêm cơ hội</a>
                                </div>
                                <?php if (!empty($deals)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Tên</th>
                                                    <th>Giá trị</th>
                                                    <th>Giai đoạn</th>
                                                    <th>Trạng thái</th>
                                                    <th>Ngày tạo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($deals as $d): ?>
                                                    <tr>
                                                        <td><a href="<?= url('deals/' . $d['id']) ?>"><?= e($d['title']) ?></a></td>
                                                        <td><?= format_money($d['value']) ?></td>
                                                        <td><span class="badge" style="background-color: <?= safe_color($d['stage_color'] ?? null) ?>"><?= e($d['stage_name'] ?? '') ?></span></td>
                                                        <td>
                                                            <?php $dColors = ['open' => 'primary', 'won' => 'success', 'lost' => 'danger']; $dLabels = ['open' => 'Đang mở', 'won' => 'Thắng', 'lost' => 'Thua']; ?>
                                                            <span class="badge bg-<?= $dColors[$d['status']] ?? 'secondary' ?>"><?= $dLabels[$d['status']] ?? $d['status'] ?></span>
                                                        </td>
                                                        <td class="text-muted"><?= time_ago($d['created_at']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="ri-hand-coin-line fs-36 text-muted"></i>
                                        <p class="text-muted mt-2">Chưa có cơ hội</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Tab: Đơn hàng -->
                            <div class="tab-pane" id="tab-orders" role="tabpanel">
                                <div class="d-flex justify-content-between mb-3">
                                    <h6 class="mb-0">Đơn hàng</h6>
                                    <a href="<?= url('orders/create?company_id=' . $company['id']) ?>" class="btn btn-soft-primary"><i class="ri-add-line me-1"></i>Tạo đơn hàng</a>
                                </div>
                                <?php
                                $orders = \Core\Database::fetchAll(
                                    "SELECT o.*, u.name as owner_name FROM orders o LEFT JOIN users u ON o.created_by = u.id WHERE o.company_id = ? AND o.is_deleted = 0 ORDER BY o.created_at DESC LIMIT 20",
                                    [$company['id']]
                                );
                                ?>
                                <?php if (!empty($orders)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Mã đơn</th>
                                                    <th>Tổng tiền</th>
                                                    <th>Trạng thái</th>
                                                    <th>Thanh toán</th>
                                                    <th>Ngày</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($orders as $order): ?>
                                                    <tr>
                                                        <td><a href="<?= url('orders/' . $order['id']) ?>"><?= e($order['order_number']) ?></a></td>
                                                        <td><?= format_money($order['total']) ?></td>
                                                        <td>
                                                            <?php $oColors = ['draft'=>'secondary','pending'=>'warning','confirmed'=>'info','processing'=>'primary','completed'=>'success','cancelled'=>'danger']; ?>
                                                            <span class="badge bg-<?= $oColors[$order['status']] ?? 'secondary' ?>-subtle text-<?= $oColors[$order['status']] ?? 'secondary' ?>"><?= e($order['status']) ?></span>
                                                        </td>
                                                        <td>
                                                            <?php $pColors = ['unpaid'=>'danger','partial'=>'warning','paid'=>'success']; ?>
                                                            <span class="badge bg-<?= $pColors[$order['payment_status']] ?? 'secondary' ?>"><?= e($order['payment_status'] ?? 'unpaid') ?></span>
                                                        </td>
                                                        <td class="text-muted"><?= time_ago($order['created_at']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="ri-shopping-cart-line fs-36 text-muted"></i>
                                        <p class="text-muted mt-2">Chưa có đơn hàng</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Tab: Hợp đồng -->
                            <div class="tab-pane" id="tab-contracts" role="tabpanel">
                                <div class="d-flex justify-content-between mb-3">
                                    <h6 class="mb-0">Hợp đồng</h6>
                                </div>
                                <?php
                                $contracts = \Core\Database::fetchAll(
                                    "SELECT * FROM contracts WHERE company_id = ? ORDER BY created_at DESC LIMIT 20",
                                    [$company['id']]
                                );
                                ?>
                                <?php if (!empty($contracts)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Mã HĐ</th>
                                                    <th>Tên</th>
                                                    <th>Giá trị</th>
                                                    <th>Trạng thái</th>
                                                    <th>Ngày</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($contracts as $ct): ?>
                                                    <tr>
                                                        <td><a href="<?= url('contracts/' . $ct['id']) ?>"><?= e($ct['contract_number'] ?? '#' . $ct['id']) ?></a></td>
                                                        <td><?= e($ct['title'] ?? '') ?></td>
                                                        <td><?= format_money($ct['value'] ?? 0) ?></td>
                                                        <td>
                                                            <?php $ctColors = ['draft'=>'secondary','active'=>'success','expired'=>'danger','cancelled'=>'warning']; ?>
                                                            <span class="badge bg-<?= $ctColors[$ct['status']] ?? 'secondary' ?>"><?= e($ct['status']) ?></span>
                                                        </td>
                                                        <td class="text-muted"><?= time_ago($ct['created_at']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="ri-file-shield-line fs-36 text-muted"></i>
                                        <p class="text-muted mt-2">Chưa có hợp đồng</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Tab: Báo giá -->
                            <div class="tab-pane" id="tab-quotations" role="tabpanel">
                                <div class="d-flex justify-content-between mb-3">
                                    <h6 class="mb-0">Báo giá</h6>
                                </div>
                                <?php
                                $quotations = \Core\Database::fetchAll(
                                    "SELECT * FROM quotations WHERE company_id = ? ORDER BY created_at DESC LIMIT 20",
                                    [$company['id']]
                                );
                                ?>
                                <?php if (!empty($quotations)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Mã BG</th>
                                                    <th>Tổng tiền</th>
                                                    <th>Trạng thái</th>
                                                    <th>Ngày</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($quotations as $q): ?>
                                                    <tr>
                                                        <td><a href="<?= url('quotations/' . $q['id']) ?>"><?= e($q['quotation_number'] ?? '#' . $q['id']) ?></a></td>
                                                        <td><?= format_money($q['total'] ?? 0) ?></td>
                                                        <td>
                                                            <?php $qColors = ['draft'=>'secondary','sent'=>'info','accepted'=>'success','rejected'=>'danger','expired'=>'warning']; ?>
                                                            <span class="badge bg-<?= $qColors[$q['status']] ?? 'secondary' ?>"><?= e($q['status']) ?></span>
                                                        </td>
                                                        <td class="text-muted"><?= time_ago($q['created_at']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="ri-file-text-line fs-36 text-muted"></i>
                                        <p class="text-muted mt-2">Chưa có báo giá</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Tab: Công nợ -->
                            <div class="tab-pane" id="tab-debts" role="tabpanel">
                                <div class="d-flex justify-content-between mb-3">
                                    <h6 class="mb-0">Công nợ</h6>
                                </div>
                                <?php
                                $debts = \Core\Database::fetchAll(
                                    "SELECT * FROM debts WHERE company_id = ? ORDER BY created_at DESC LIMIT 20",
                                    [$company['id']]
                                );
                                ?>
                                <?php if (!empty($debts)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Mô tả</th>
                                                    <th>Số tiền</th>
                                                    <th>Đã trả</th>
                                                    <th>Còn lại</th>
                                                    <th>Trạng thái</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($debts as $debt): ?>
                                                    <tr>
                                                        <td><a href="<?= url('debts/' . $debt['id']) ?>"><?= e($debt['description'] ?? '#' . $debt['id']) ?></a></td>
                                                        <td><?= format_money($debt['amount'] ?? 0) ?></td>
                                                        <td><?= format_money($debt['paid_amount'] ?? 0) ?></td>
                                                        <td class="text-danger fw-medium"><?= format_money(($debt['amount'] ?? 0) - ($debt['paid_amount'] ?? 0)) ?></td>
                                                        <td>
                                                            <?php $dbColors = ['unpaid'=>'danger','partial'=>'warning','paid'=>'success']; ?>
                                                            <span class="badge bg-<?= $dbColors[$debt['status']] ?? 'secondary' ?>"><?= e($debt['status'] ?? 'unpaid') ?></span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="ri-money-dollar-circle-line fs-36 text-muted"></i>
                                        <p class="text-muted mt-2">Chưa có công nợ</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Tab: Ticket -->
                            <div class="tab-pane" id="tab-tickets" role="tabpanel">
                                <div class="d-flex justify-content-between mb-3">
                                    <h6 class="mb-0">Ticket hỗ trợ</h6>
                                </div>
                                <?php if (!empty($tickets)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Tiêu đề</th>
                                                    <th>Ưu tiên</th>
                                                    <th>Trạng thái</th>
                                                    <th>Phụ trách</th>
                                                    <th>Ngày tạo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $tColors = ['open'=>'info','in_progress'=>'primary','resolved'=>'success','closed'=>'secondary'];
                                                $tLabels = ['open'=>'Mở','in_progress'=>'Đang xử lý','resolved'=>'Đã giải quyết','closed'=>'Đóng'];
                                                $pColors = ['low'=>'info','medium'=>'warning','high'=>'danger','urgent'=>'danger'];
                                                $pLabels = ['low'=>'Thấp','medium'=>'TB','high'=>'Cao','urgent'=>'Khẩn'];
                                                ?>
                                                <?php foreach ($tickets as $tk): ?>
                                                    <tr>
                                                        <td><a href="<?= url('tickets/' . $tk['id']) ?>"><?= e($tk['subject'] ?? $tk['title'] ?? '') ?></a></td>
                                                        <td><span class="badge bg-<?= $pColors[$tk['priority']] ?? 'secondary' ?>"><?= $pLabels[$tk['priority']] ?? $tk['priority'] ?></span></td>
                                                        <td><span class="badge bg-<?= $tColors[$tk['status']] ?? 'secondary' ?>-subtle text-<?= $tColors[$tk['status']] ?? 'secondary' ?>"><?= $tLabels[$tk['status']] ?? $tk['status'] ?></span></td>
                                                        <td><?= e($tk['assigned_name'] ?? '-') ?></td>
                                                        <td class="text-muted"><?= time_ago($tk['created_at']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="ri-customer-service-line fs-36 text-muted"></i>
                                        <p class="text-muted mt-2">Chưa có ticket</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Tab: Ghi chú -->
                            <div class="tab-pane" id="tab-notes" role="tabpanel">
                                <h6 class="mb-3">Mô tả / Ghi chú</h6>
                                <?php if ($company['description']): ?>
                                    <div class="p-3 bg-light rounded">
                                        <p class="mb-0"><?= nl2br(e($company['description'])) ?></p>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="ri-file-text-line fs-36 text-muted"></i>
                                        <p class="text-muted mt-2">Chưa có ghi chú</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                </div>

                <?php $chatEntityType = 'company'; $chatEntityId = $company['id']; include BASE_PATH . '/resources/views/components/internal-chat.php'; ?>
            </div>
        </div>

<script>
document.querySelectorAll('.activity-type-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.activity-type-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('activityType').value = this.dataset.type;
    });
});
</script>
