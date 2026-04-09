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
                        $sColors = []; $sLabels = [];
                        foreach ($contactStatuses ?? [] as $_cs) { $sColors[$_cs['slug']] = $_cs['color']; $sLabels[$_cs['slug']] = $_cs['name']; }
                        if (empty($sLabels)) { $sColors = ['new'=>'info','contacted'=>'primary','qualified'=>'warning','converted'=>'success','lost'=>'danger']; $sLabels = ['new'=>'Mới','contacted'=>'Đã liên hệ','qualified'=>'Tiềm năng','converted'=>'Chuyển đổi','lost'=>'Mất']; }
                        ?>
                        <span class="badge bg-<?= $sColors[$contact['status']] ?? 'secondary' ?> fs-12">
                            <?= $sLabels[$contact['status']] ?? $contact['status'] ?>
                        </span>

                        <!-- Tags -->
                        <div class="mt-3">
                            <?php
                            $contactTags = \App\Services\TagService::getForEntity('contact', $contact['id']);
                            $entityType = 'contact';
                            $entityId = $contact['id'];
                            $selectedTags = $contactTags;
                            include __DIR__ . '/../components/tag-input.php';
                            ?>
                        </div>

                        <div class="mt-4 d-flex gap-2 justify-content-center flex-wrap">
                            <a href="<?= url('contacts/' . $contact['id'] . '/edit') ?>" class="btn btn-primary">
                                <i class="ri-pencil-line me-1"></i> Sửa
                            </a>
                            <form method="POST" action="<?= url('contacts/' . $contact['id'] . '/delete') ?>" data-confirm="Xác nhận xóa?">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger"><i class="ri-delete-bin-line me-1"></i> Xóa</button>
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
                            <div class="input-group">
                                <select name="owner_id" class="form-select">
                                    <option value="">Chọn người mới</option>
                                    <?php
                                    $allUsers = \Core\Database::fetchAll("SELECT id, name FROM users WHERE is_active = 1 ORDER BY name");
                                    foreach ($allUsers as $u): ?>
                                        <option value="<?= $u['id'] ?>" <?= ($contact['owner_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-soft-primary"><i class="ri-refresh-line me-1"></i> Đổi</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Followers (Người xem) - Tag style -->
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Người theo dõi</h5></div>
                    <div class="card-body">
                        <div id="followerTags" class="d-flex flex-wrap gap-1 mb-2">
                            <?php foreach ($followers ?? [] as $f): ?>
                                <span class="badge bg-info-subtle text-info d-inline-flex align-items-center gap-1 py-1 px-2" data-uid="<?= $f['user_id'] ?>">
                                    <?= e($f['name']) ?>
                                    <i class="ri-close-line" style="cursor:pointer;font-size:14px" onclick="removeFollower(<?= $f['user_id'] ?>, this)"></i>
                                </span>
                            <?php endforeach; ?>
                        </div>
                        <div class="position-relative">
                            <input type="text" class="form-control" id="followerInput" placeholder="Gõ tên để thêm..." autocomplete="off">
                            <div id="followerDropdown" class="dropdown-menu w-100" style="display:none;max-height:200px;overflow-y:auto"></div>
                        </div>
                    </div>
                </div>
                <script>
                (function() {
                    var cid = <?= $contact['id'] ?>, tok = '<?= $_SESSION['csrf_token'] ?? '' ?>';
                    var users = <?= json_encode($allUsers) ?>;
                    var existing = [<?= implode(',', array_column($followers ?? [], 'user_id')) ?>];
                    var input = document.getElementById('followerInput');
                    var dd = document.getElementById('followerDropdown');
                    var tags = document.getElementById('followerTags');

                    input.addEventListener('input', function() {
                        var q = this.value.toLowerCase().trim();
                        if (!q) { dd.style.display = 'none'; return; }
                        var html = '';
                        users.forEach(function(u) {
                            if (existing.includes(u.id)) return;
                            if (u.name.toLowerCase().indexOf(q) === -1) return;
                            html += '<a class="dropdown-item" href="#" data-id="' + u.id + '" data-name="' + u.name + '">' + u.name + '</a>';
                        });
                        dd.innerHTML = html || '<span class="dropdown-item text-muted">Không tìm thấy</span>';
                        dd.style.display = 'block';
                    });

                    input.addEventListener('blur', function() { setTimeout(function(){ dd.style.display = 'none'; }, 200); });

                    dd.addEventListener('click', function(e) {
                        e.preventDefault();
                        var a = e.target.closest('[data-id]');
                        if (!a) return;
                        var uid = parseInt(a.dataset.id), name = a.dataset.name;
                        fetch('/contacts/' + cid + '/followers', {
                            method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'},
                            body: '_token=' + tok + '&user_id=' + uid + '&action=add'
                        }).then(function(r){return r.json()}).then(function(d) {
                            if (d.success) {
                                existing.push(uid);
                                var span = document.createElement('span');
                                span.className = 'badge bg-info-subtle text-info d-inline-flex align-items-center gap-1 py-1 px-2';
                                span.dataset.uid = uid;
                                span.innerHTML = name + ' <i class="ri-close-line" style="cursor:pointer;font-size:14px" onclick="removeFollower(' + uid + ', this)"></i>';
                                tags.appendChild(span);
                                input.value = '';
                                dd.style.display = 'none';
                            }
                        });
                    });

                    window.removeFollower = function(uid, el) {
                        fetch('/contacts/' + cid + '/followers', {
                            method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'},
                            body: '_token=' + tok + '&user_id=' + uid + '&action=remove'
                        }).then(function(r){return r.json()}).then(function(d) {
                            if (d.success) {
                                el.closest('[data-uid]').remove();
                                existing = existing.filter(function(id){ return id !== uid; });
                            }
                        });
                    };
                })();
                </script>

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

            <!-- Right Column - Tabbed Layout -->
            <div class="col-xl-8">
                <!-- Stats Bar -->
                <?php
                $activityCount = count($activities ?? []);
                $lastActivity = !empty($activities) ? $activities[0] : null;
                $orderStats = \Core\Database::fetch(
                    "SELECT COUNT(*) as order_count, COALESCE(SUM(total), 0) as total_value FROM orders WHERE contact_id = ? AND is_deleted = 0",
                    [$contact['id']]
                );
                ?>
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row text-center">
                            <div class="col">
                                <p class="text-muted mb-1 fs-12">Mối quan hệ</p>
                                <span class="badge bg-<?= $sColors[$contact['status']] ?? 'secondary' ?> fs-12"><?= $sLabels[$contact['status']] ?? $contact['status'] ?></span>
                            </div>
                            <div class="col border-start">
                                <p class="text-muted mb-1 fs-12">Người phụ trách</p>
                                <h6 class="mb-0 fs-13"><?= e($contact['owner_name'] ?? 'Chưa gán') ?></h6>
                            </div>
                            <div class="col border-start">
                                <p class="text-muted mb-1 fs-12">Liên hệ lần cuối</p>
                                <h5 class="mb-0 <?= $lastActivity ? 'text-primary' : 'text-muted' ?>"><?= $lastActivity ? time_ago($lastActivity['created_at']) : '0' ?></h5>
                            </div>
                            <div class="col border-start">
                                <p class="text-muted mb-1 fs-12">Tương tác</p>
                                <h5 class="mb-0 text-info"><?= $activityCount ?></h5>
                            </div>
                            <div class="col border-start">
                                <p class="text-muted mb-1 fs-12">Giá trị đơn hàng</p>
                                <h5 class="mb-0 text-success"><?= format_money($orderStats['total_value'] ?? 0) ?></h5>
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
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button">
                                    <i class="ri-exchange-line me-1"></i> Giao dịch <i class="ri-arrow-down-s-line fs-12"></i>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" data-bs-toggle="tab" href="#tab-stats"><i class="ri-bar-chart-line me-2"></i>Thống kê</a></li>
                                    <li><a class="dropdown-item" data-bs-toggle="tab" href="#tab-debt"><i class="ri-money-dollar-circle-line me-2"></i>Công nợ</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" data-bs-toggle="tab" href="#tab-quotations"><i class="ri-file-text-line me-2"></i>Báo giá</a></li>
                                    <li><a class="dropdown-item" data-bs-toggle="tab" href="#tab-contracts"><i class="ri-file-shield-line me-2"></i>Hợp đồng</a></li>
                                    <li><a class="dropdown-item" data-bs-toggle="tab" href="#tab-orders"><i class="ri-shopping-cart-line me-2"></i>Đơn hàng</a></li>
                                    <li><a class="dropdown-item" data-bs-toggle="tab" href="#tab-products"><i class="ri-shopping-bag-line me-2"></i>Sản phẩm</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" data-bs-toggle="tab" href="#tab-emails"><i class="ri-mail-line me-2"></i>Email</a></li>
                                    <li><a class="dropdown-item" data-bs-toggle="tab" href="#tab-sms"><i class="ri-message-2-line me-2"></i>SMS</a></li>
                                    <li><a class="dropdown-item" data-bs-toggle="tab" href="#tab-calls"><i class="ri-phone-line me-2"></i>Cuộc gọi</a></li>
                                    <li><a class="dropdown-item" data-bs-toggle="tab" href="#tab-docs"><i class="ri-file-list-line me-2"></i>Tài liệu</a></li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tab-calendar" role="tab">
                                    <i class="ri-calendar-line me-1"></i> Lịch hẹn
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tab-deals" role="tab">
                                    <i class="ri-hand-coin-line me-1"></i> Cơ hội
                                    <?php if (!empty($deals)): ?><span class="badge bg-primary ms-1"><?= count($deals) ?></span><?php endif; ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tab-routes" role="tab">
                                    <i class="ri-route-line me-1"></i> Lịch đi tuyến
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tab-automation" role="tab">
                                    <i class="ri-robot-line me-1"></i> Automation
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button">
                                    <i class="ri-more-line me-1"></i> Thêm <i class="ri-arrow-down-s-line fs-12"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" data-bs-toggle="tab" href="#tab-info"><i class="ri-information-line me-2"></i>Giới thiệu</a></li>
                                    <li><a class="dropdown-item" data-bs-toggle="tab" href="#tab-tickets"><i class="ri-customer-service-line me-2"></i>Ticket</a></li>
                                    <li><a class="dropdown-item" data-bs-toggle="tab" href="#tab-notes"><i class="ri-file-text-line me-2"></i>Ghi chú</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="tab-content">

                            <!-- Tab: Trao đổi -->
                            <div class="tab-pane active" id="tab-exchange" role="tabpanel">
                                <!-- Compose Area -->
                                <form method="POST" action="<?= url('activities/store') ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="contact_id" value="<?= $contact['id'] ?>">
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

                                <!-- Filter Bar -->
                                <div class="row g-2 mb-3">
                                    <div class="col-md-4">
                                        <div class="search-box">
                                            <input type="text" class="form-control" placeholder="Tìm kiếm..." id="activitySearch">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select" id="activityUserFilter">
                                            <option value="">Tất cả nhân viên</option>
                                            <?php foreach ($allUsers as $u): ?>
                                                <option value="<?= e($u['name']) ?>"><?= e($u['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select" id="activityTypeFilter">
                                            <option value="">Tất cả loại</option>
                                            <option value="note">Ghi chú</option>
                                            <option value="call">Cuộc gọi</option>
                                            <option value="email">Email</option>
                                            <option value="meeting">Cuộc họp</option>
                                            <option value="feedback">KH phản hồi</option>
                                            <option value="sms">SMS</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Activity Timeline -->
                                <div class="activity-timeline" id="activityList" style="max-height: 500px; overflow-y: auto;">
                                    <?php if (!empty($activities)): ?>
                                        <?php
                                        $typeIcons = ['note' => 'ri-file-text-line', 'call' => 'ri-phone-line', 'email' => 'ri-mail-line', 'meeting' => 'ri-calendar-line', 'task' => 'ri-task-line'];
                                        $typeColors = ['note' => 'primary', 'call' => 'success', 'email' => 'info', 'meeting' => 'warning', 'task' => 'danger'];
                                        $typeLabels = ['note' => 'Ghi chú', 'call' => 'Cuộc gọi', 'email' => 'Email', 'meeting' => 'Cuộc họp', 'task' => 'Công việc'];
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

                            <!-- Tab: Cơ hội -->
                            <div class="tab-pane" id="tab-deals" role="tabpanel">
                                <div class="d-flex justify-content-between mb-3">
                                    <h6 class="mb-0">Danh sách cơ hội</h6>
                                    <a href="<?= url('deals/create?contact_id=' . $contact['id']) ?>" class="btn btn-soft-primary"><i class="ri-add-line me-1"></i>Thêm cơ hội</a>
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
                                                <?php foreach ($deals as $deal): ?>
                                                    <tr>
                                                        <td><a href="<?= url('deals/' . $deal['id']) ?>"><?= e($deal['title']) ?></a></td>
                                                        <td><?= format_money($deal['value']) ?></td>
                                                        <td><span class="badge" style="background-color: <?= safe_color($deal['stage_color'] ?? null) ?>"><?= e($deal['stage_name'] ?? '') ?></span></td>
                                                        <td>
                                                            <?php $dColors = ['open' => 'primary', 'won' => 'success', 'lost' => 'danger']; ?>
                                                            <span class="badge bg-<?= $dColors[$deal['status']] ?? 'secondary' ?>"><?= $deal['status'] ?></span>
                                                        </td>
                                                        <td class="text-muted"><?= time_ago($deal['created_at']) ?></td>
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

                            <!-- Tab: Giao dịch -->
                            <div class="tab-pane" id="tab-orders" role="tabpanel">
                                <div class="d-flex justify-content-between mb-3">
                                    <h6 class="mb-0">Đơn hàng</h6>
                                    <a href="<?= url('orders/create?contact_id=' . $contact['id']) ?>" class="btn btn-soft-primary"><i class="ri-add-line me-1"></i>Tạo đơn hàng</a>
                                </div>
                                <?php
                                $orders = \Core\Database::fetchAll(
                                    "SELECT o.*, u.name as owner_name FROM orders o LEFT JOIN users u ON o.created_by = u.id WHERE o.contact_id = ? ORDER BY o.created_at DESC LIMIT 20",
                                    [$contact['id']]
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

                            <!-- Tab: Thống kê -->
                            <div class="tab-pane" id="tab-stats" role="tabpanel">
                                <h6 class="mb-3">Thống kê khách hàng</h6>
                                <?php
                                $totalOrders = \Core\Database::fetch("SELECT COUNT(*) as cnt, COALESCE(SUM(total),0) as total FROM orders WHERE contact_id = ? AND is_deleted = 0", [$contact['id']]);
                                $totalDeals = \Core\Database::fetch("SELECT COUNT(*) as cnt, COALESCE(SUM(value),0) as total FROM deals WHERE contact_id = ?", [$contact['id']]);
                                $wonDeals = \Core\Database::fetch("SELECT COUNT(*) as cnt, COALESCE(SUM(value),0) as total FROM deals WHERE contact_id = ? AND status = 'won'", [$contact['id']]);
                                $totalTickets = \Core\Database::fetch("SELECT COUNT(*) as cnt FROM tickets WHERE contact_id = ?", [$contact['id']]);
                                $totalActivities = \Core\Database::fetch("SELECT COUNT(*) as cnt FROM activities WHERE contact_id = ?", [$contact['id']]);
                                ?>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <div class="p-3 border rounded text-center">
                                            <h3 class="text-primary mb-1"><?= $totalOrders['cnt'] ?? 0 ?></h3>
                                            <p class="text-muted mb-0">Đơn hàng</p>
                                            <small class="text-success fw-medium"><?= format_money($totalOrders['total'] ?? 0) ?></small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 border rounded text-center">
                                            <h3 class="text-info mb-1"><?= $totalDeals['cnt'] ?? 0 ?></h3>
                                            <p class="text-muted mb-0">Cơ hội</p>
                                            <small class="text-success fw-medium"><?= format_money($totalDeals['total'] ?? 0) ?></small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 border rounded text-center">
                                            <h3 class="text-success mb-1"><?= $wonDeals['cnt'] ?? 0 ?></h3>
                                            <p class="text-muted mb-0">Thắng</p>
                                            <small class="text-success fw-medium"><?= format_money($wonDeals['total'] ?? 0) ?></small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 border rounded text-center">
                                            <h3 class="text-danger mb-1"><?= $totalTickets['cnt'] ?? 0 ?></h3>
                                            <p class="text-muted mb-0">Ticket</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 border rounded text-center">
                                            <h3 class="text-secondary mb-1"><?= $totalActivities['cnt'] ?? 0 ?></h3>
                                            <p class="text-muted mb-0">Tương tác</p>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="mb-3">Doanh thu theo tháng</h6>
                                <?php
                                $monthlyRevenue = \Core\Database::fetchAll(
                                    "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total) as revenue, COUNT(*) as cnt
                                     FROM orders WHERE contact_id = ? AND is_deleted = 0 AND status != 'cancelled'
                                     GROUP BY month ORDER BY month DESC LIMIT 12",
                                    [$contact['id']]
                                );
                                ?>
                                <?php if (!empty($monthlyRevenue)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr><th>Tháng</th><th>Số đơn</th><th>Doanh thu</th></tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($monthlyRevenue as $mr): ?>
                                                <tr>
                                                    <td><?= e($mr['month']) ?></td>
                                                    <td><?= $mr['cnt'] ?></td>
                                                    <td class="fw-medium text-success"><?= format_money($mr['revenue']) ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted text-center">Chưa có dữ liệu doanh thu</p>
                                <?php endif; ?>
                            </div>

                            <!-- Tab: Báo giá -->
                            <div class="tab-pane" id="tab-quotations" role="tabpanel">
                                <div class="d-flex justify-content-between mb-3">
                                    <h6 class="mb-0">Báo giá</h6>
                                    <a href="<?= url('orders/create?type=quote&contact_id=' . $contact['id']) ?>" class="btn btn-soft-primary"><i class="ri-add-line me-1"></i>Tạo báo giá</a>
                                </div>
                                <?php
                                $quotations = \Core\Database::fetchAll(
                                    "SELECT o.*, u.name as owner_name FROM orders o LEFT JOIN users u ON o.owner_id = u.id WHERE o.contact_id = ? AND o.type = 'quote' AND o.is_deleted = 0 ORDER BY o.created_at DESC LIMIT 20",
                                    [$contact['id']]
                                );
                                ?>
                                <?php if (!empty($quotations)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Mã báo giá</th>
                                                    <th>Tổng tiền</th>
                                                    <th>Trạng thái</th>
                                                    <th>Người tạo</th>
                                                    <th>Ngày</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($quotations as $q): ?>
                                                <tr>
                                                    <td><a href="<?= url('orders/' . $q['id']) ?>"><?= e($q['order_number']) ?></a></td>
                                                    <td><?= format_money($q['total']) ?></td>
                                                    <td>
                                                        <?php $qColors = ['draft'=>'secondary','pending'=>'warning','confirmed'=>'success','cancelled'=>'danger']; ?>
                                                        <span class="badge bg-<?= $qColors[$q['status']] ?? 'secondary' ?>-subtle text-<?= $qColors[$q['status']] ?? 'secondary' ?>"><?= e($q['status']) ?></span>
                                                    </td>
                                                    <td class="text-muted"><?= e($q['owner_name'] ?? '-') ?></td>
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

                            <!-- Tab: Hợp đồng -->
                            <div class="tab-pane" id="tab-contracts" role="tabpanel">
                                <div class="d-flex justify-content-between mb-3">
                                    <h6 class="mb-0">Hợp đồng</h6>
                                    <a href="<?= url('orders/create?type=contract&contact_id=' . $contact['id']) ?>" class="btn btn-soft-primary"><i class="ri-add-line me-1"></i>Tạo hợp đồng</a>
                                </div>
                                <?php
                                $contracts = \Core\Database::fetchAll(
                                    "SELECT o.*, u.name as owner_name FROM orders o LEFT JOIN users u ON o.owner_id = u.id WHERE o.contact_id = ? AND o.type = 'contract' AND o.is_deleted = 0 ORDER BY o.created_at DESC LIMIT 20",
                                    [$contact['id']]
                                );
                                ?>
                                <?php if (!empty($contracts)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Mã HĐ</th>
                                                    <th>Giá trị</th>
                                                    <th>Trạng thái</th>
                                                    <th>Hạn thanh toán</th>
                                                    <th>Người tạo</th>
                                                    <th>Ngày</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($contracts as $ct): ?>
                                                <tr>
                                                    <td><a href="<?= url('orders/' . $ct['id']) ?>"><?= e($ct['order_number']) ?></a></td>
                                                    <td class="fw-medium"><?= format_money($ct['total']) ?></td>
                                                    <td>
                                                        <?php $ctColors = ['draft'=>'secondary','pending'=>'warning','confirmed'=>'info','processing'=>'primary','completed'=>'success','cancelled'=>'danger']; ?>
                                                        <span class="badge bg-<?= $ctColors[$ct['status']] ?? 'secondary' ?>-subtle text-<?= $ctColors[$ct['status']] ?? 'secondary' ?>"><?= e($ct['status']) ?></span>
                                                    </td>
                                                    <td class="text-muted"><?= $ct['due_date'] ? format_date($ct['due_date']) : '-' ?></td>
                                                    <td class="text-muted"><?= e($ct['owner_name'] ?? '-') ?></td>
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

                            <!-- Tab: Sản phẩm đã mua -->
                            <div class="tab-pane" id="tab-products" role="tabpanel">
                                <h6 class="mb-3">Sản phẩm đã mua</h6>
                                <?php
                                $boughtProducts = \Core\Database::fetchAll(
                                    "SELECT p.name, p.sku, p.price, oi.quantity, oi.unit_price as sold_price, oi.total as line_total, o.order_number, o.created_at
                                     FROM order_items oi
                                     JOIN orders o ON oi.order_id = o.id
                                     JOIN products p ON oi.product_id = p.id
                                     WHERE o.contact_id = ? AND o.is_deleted = 0
                                     ORDER BY o.created_at DESC LIMIT 30",
                                    [$contact['id']]
                                );
                                ?>
                                <?php if (!empty($boughtProducts)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr><th>Sản phẩm</th><th>SKU</th><th>Đơn giá</th><th>SL</th><th>Thành tiền</th><th>Đơn hàng</th></tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($boughtProducts as $bp): ?>
                                                <tr>
                                                    <td><?= e($bp['name']) ?></td>
                                                    <td class="text-muted"><?= e($bp['sku'] ?? '-') ?></td>
                                                    <td><?= format_money($bp['sold_price']) ?></td>
                                                    <td><?= $bp['quantity'] ?></td>
                                                    <td class="fw-medium"><?= format_money($bp['line_total']) ?></td>
                                                    <td><span class="text-muted fs-12"><?= e($bp['order_number']) ?> - <?= time_ago($bp['created_at']) ?></span></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="ri-shopping-bag-line fs-36 text-muted"></i>
                                        <p class="text-muted mt-2">Chưa mua sản phẩm nào</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Tab: Email -->
                            <div class="tab-pane" id="tab-emails" role="tabpanel">
                                <h6 class="mb-3">Email đã gửi</h6>
                                <?php
                                $emails = \Core\Database::fetchAll(
                                    "SELECT * FROM email_logs WHERE to_email = ? ORDER BY created_at DESC LIMIT 20",
                                    [$contact['email'] ?? '']
                                );
                                ?>
                                <?php if (!empty($emails)): ?>
                                    <?php foreach ($emails as $em): ?>
                                        <div class="d-flex mb-3 p-3 border rounded">
                                            <div class="avatar-xs flex-shrink-0 me-3">
                                                <span class="avatar-title bg-info-subtle text-info rounded-circle"><i class="ri-mail-line"></i></span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1"><?= e($em['subject']) ?></h6>
                                                <div class="text-muted fs-12">
                                                    <i class="ri-arrow-right-line me-1"></i><?= e($em['to_email']) ?>
                                                    <span class="ms-2 badge bg-<?= $em['status'] === 'sent' ? 'success' : 'danger' ?>"><?= $em['status'] === 'sent' ? 'Đã gửi' : 'Lỗi' ?></span>
                                                </div>
                                                <small class="text-muted"><?= time_ago($em['created_at']) ?></small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="ri-mail-line fs-36 text-muted"></i>
                                        <p class="text-muted mt-2">Chưa có email nào</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Tab: Cuộc gọi -->
                            <div class="tab-pane" id="tab-calls" role="tabpanel">
                                <h6 class="mb-3">Lịch sử cuộc gọi</h6>
                                <?php
                                $calls = \Core\Database::fetchAll(
                                    "SELECT cl.*, u.name as user_name FROM call_logs cl LEFT JOIN users u ON cl.user_id = u.id WHERE cl.contact_id = ? ORDER BY cl.created_at DESC LIMIT 20",
                                    [$contact['id']]
                                );
                                ?>
                                <?php if (!empty($calls)): ?>
                                    <?php foreach ($calls as $call): ?>
                                        <div class="d-flex mb-3 p-3 border rounded">
                                            <div class="avatar-xs flex-shrink-0 me-3">
                                                <?php $cIcon = $call['direction'] === 'inbound' ? 'ri-phone-fill' : 'ri-phone-line'; ?>
                                                <?php $cColor = $call['direction'] === 'inbound' ? 'success' : 'primary'; ?>
                                                <span class="avatar-title bg-<?= $cColor ?>-subtle text-<?= $cColor ?> rounded-circle"><i class="<?= $cIcon ?>"></i></span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between">
                                                    <h6 class="mb-1"><?= $call['direction'] === 'inbound' ? 'Cuộc gọi đến' : 'Cuộc gọi đi' ?></h6>
                                                    <span class="badge bg-<?= ($call['status'] ?? '') === 'completed' ? 'success' : 'warning' ?>"><?= e($call['status'] ?? 'unknown') ?></span>
                                                </div>
                                                <?php if (!empty($call['duration'])): ?>
                                                    <div class="text-muted fs-12"><i class="ri-time-line me-1"></i><?= gmdate('i:s', $call['duration']) ?></div>
                                                <?php endif; ?>
                                                <?php if (!empty($call['notes'])): ?>
                                                    <p class="text-muted mb-0 fs-12"><?= e($call['notes']) ?></p>
                                                <?php endif; ?>
                                                <small class="text-muted"><?= e($call['user_name'] ?? '') ?> - <?= time_ago($call['created_at']) ?></small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="ri-phone-line fs-36 text-muted"></i>
                                        <p class="text-muted mt-2">Chưa có cuộc gọi</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Tab: Tài liệu -->
                            <div class="tab-pane" id="tab-docs" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">Tài liệu đính kèm</h6>
                                    <form method="POST" action="<?= url('contacts/' . $contact['id'] . '/upload') ?>" enctype="multipart/form-data" class="d-flex gap-2">
                                        <?= csrf_field() ?>
                                        <input type="file" name="file" class="form-control" style="max-width: 250px;" required>
                                        <button type="submit" class="btn btn-soft-primary"><i class="ri-add-line me-1"></i>Thêm tài liệu</button>
                                    </form>
                                </div>
                                <?php
                                $docs = \Core\Database::fetchAll(
                                    "SELECT * FROM file_uploads WHERE entity_type = 'contact' AND entity_id = ? ORDER BY created_at DESC",
                                    [$contact['id']]
                                );
                                ?>
                                <?php if (!empty($docs)): ?>
                                    <?php foreach ($docs as $doc): ?>
                                        <div class="d-flex align-items-center mb-2 p-2 border rounded">
                                            <div class="avatar-xs flex-shrink-0 me-3">
                                                <span class="avatar-title bg-secondary-subtle text-secondary rounded"><i class="ri-file-line"></i></span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <a href="<?= url('uploads/' . ($doc['directory'] ?? '') . '/' . $doc['filename']) ?>" target="_blank" class="fw-medium"><?= e($doc['original_name'] ?? $doc['filename']) ?></a>
                                                <div class="text-muted fs-12"><?= time_ago($doc['created_at']) ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="ri-file-list-line fs-36 text-muted"></i>
                                        <p class="text-muted mt-2">Chưa có tài liệu</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Tab: Lịch hẹn -->
                            <div class="tab-pane" id="tab-calendar" role="tabpanel">
                                <div class="d-flex justify-content-between mb-3">
                                    <h6 class="mb-0">Lịch hẹn</h6>
                                    <a href="<?= url('calendar/create?contact_id=' . $contact['id']) ?>" class="btn btn-soft-primary"><i class="ri-add-line me-1"></i>Tạo lịch hẹn</a>
                                </div>
                                <?php
                                $events = \Core\Database::fetchAll(
                                    "SELECT * FROM calendar_events WHERE contact_id = ? ORDER BY start_at DESC LIMIT 20",
                                    [$contact['id']]
                                );
                                ?>
                                <?php if (!empty($events)): ?>
                                    <?php foreach ($events as $event): ?>
                                        <div class="d-flex align-items-start mb-3 p-3 border rounded">
                                            <div class="avatar-xs flex-shrink-0 me-3">
                                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle">
                                                    <i class="ri-calendar-event-line"></i>
                                                </span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <a href="<?= url('calendar/' . $event['id']) ?>" class="fw-medium"><?= e($event['title']) ?></a>
                                                <div class="text-muted fs-12">
                                                    <i class="ri-time-line me-1"></i><?= format_datetime($event['start_at']) ?>
                                                    <?php if ($event['end_at']): ?> - <?= format_datetime($event['end_at']) ?><?php endif; ?>
                                                </div>
                                                <?php if ($event['location']): ?>
                                                    <div class="text-muted fs-12"><i class="ri-map-pin-line me-1"></i><?= e($event['location']) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="ri-calendar-line fs-36 text-muted"></i>
                                        <p class="text-muted mt-2">Chưa có lịch hẹn</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Tab: Ticket -->
                            <div class="tab-pane" id="tab-tickets" role="tabpanel">
                                <div class="d-flex justify-content-between mb-3">
                                    <h6 class="mb-0">Ticket hỗ trợ</h6>
                                    <a href="<?= url('tickets/create?contact_id=' . $contact['id']) ?>" class="btn btn-soft-primary"><i class="ri-add-line me-1"></i>Tạo ticket</a>
                                </div>
                                <?php
                                $tickets = \Core\Database::fetchAll(
                                    "SELECT t.*, u.name as assigned_name FROM tickets t LEFT JOIN users u ON t.assigned_to = u.id WHERE t.contact_id = ? ORDER BY t.created_at DESC LIMIT 20",
                                    [$contact['id']]
                                );
                                ?>
                                <?php if (!empty($tickets)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Mã</th>
                                                    <th>Tiêu đề</th>
                                                    <th>Trạng thái</th>
                                                    <th>Ưu tiên</th>
                                                    <th>Phụ trách</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $tColors = ['open'=>'info','in_progress'=>'primary','waiting'=>'warning','resolved'=>'success','closed'=>'secondary'];
                                                $pColors = ['low'=>'info','medium'=>'warning','high'=>'danger','urgent'=>'danger'];
                                                ?>
                                                <?php foreach ($tickets as $ticket): ?>
                                                    <tr>
                                                        <td><a href="<?= url('tickets/' . $ticket['id']) ?>"><?= e($ticket['ticket_code']) ?></a></td>
                                                        <td><?= e($ticket['title']) ?></td>
                                                        <td><span class="badge bg-<?= $tColors[$ticket['status']] ?? 'secondary' ?>-subtle text-<?= $tColors[$ticket['status']] ?? 'secondary' ?>"><?= e($ticket['status']) ?></span></td>
                                                        <td><span class="badge bg-<?= $pColors[$ticket['priority']] ?? 'secondary' ?>"><?= e($ticket['priority']) ?></span></td>
                                                        <td><?= e($ticket['assigned_name'] ?? '-') ?></td>
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
                                <h6 class="mb-3">Ghi chú</h6>
                                <?php if ($contact['description']): ?>
                                    <div class="p-3 bg-light rounded">
                                        <p class="mb-0"><?= nl2br(e($contact['description'])) ?></p>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="ri-file-text-line fs-36 text-muted"></i>
                                        <p class="text-muted mt-2">Chưa có ghi chú</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Tab: Giới thiệu -->
                            <div class="tab-pane" id="tab-info" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tbody>
                                            <tr><th class="text-muted" width="30%">Mã KH</th><td><?= e($contact['account_code'] ?? '-') ?></td></tr>
                                            <tr><th class="text-muted">Họ tên</th><td><?= e($contact['first_name'] . ' ' . ($contact['last_name'] ?? '')) ?></td></tr>
                                            <tr><th class="text-muted">Email</th><td><?= e($contact['email'] ?? '-') ?></td></tr>
                                            <tr><th class="text-muted">Điện thoại</th><td><?= e($contact['phone'] ?? '-') ?></td></tr>
                                            <tr><th class="text-muted">Chức danh</th><td><?= e($contact['position'] ?? '-') ?></td></tr>
                                            <tr><th class="text-muted">Công ty</th><td><?= e($contact['company_name'] ?? '-') ?></td></tr>
                                            <tr><th class="text-muted">Địa chỉ</th><td><?= e($contact['address'] ?? '-') ?></td></tr>
                                            <tr><th class="text-muted">Thành phố</th><td><?= e($contact['city'] ?? '-') ?></td></tr>
                                            <tr><th class="text-muted">Nguồn</th><td><?= e($contact['source_name'] ?? '-') ?></td></tr>
                                            <tr><th class="text-muted">Người phụ trách</th><td><?= e($contact['owner_name'] ?? '-') ?></td></tr>
                                            <tr><th class="text-muted">Ngày tạo</th><td><?= format_datetime($contact['created_at']) ?></td></tr>
                                            <tr><th class="text-muted">Cập nhật</th><td><?= format_datetime($contact['updated_at']) ?></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Tab: KH phản hồi -->
                            <div class="tab-pane" id="tab-feedback" role="tabpanel">
                                <h6 class="mb-3">Phản hồi từ khách hàng</h6>
                                <?php
                                $feedbacks = \Core\Database::fetchAll(
                                    "SELECT a.*, u.name as user_name FROM activities a LEFT JOIN users u ON a.user_id = u.id
                                     WHERE a.contact_id = ? AND a.type = 'feedback'
                                     ORDER BY a.created_at DESC LIMIT 20",
                                    [$contact['id']]
                                );
                                ?>
                                <?php if (!empty($feedbacks)): ?>
                                    <div style="max-height: 400px; overflow-y: auto;">
                                        <?php foreach ($feedbacks as $fb): ?>
                                            <div class="d-flex mb-3 p-3 bg-light rounded">
                                                <div class="avatar-xs flex-shrink-0 me-3">
                                                    <span class="avatar-title bg-warning-subtle text-warning rounded-circle"><i class="ri-chat-quote-line"></i></span>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <p class="mb-1"><?= e($fb['title']) ?></p>
                                                    <?php if (!empty($fb['description'])): ?>
                                                        <p class="text-muted mb-1 fs-12"><?= e($fb['description']) ?></p>
                                                    <?php endif; ?>
                                                    <small class="text-muted"><?= time_ago($fb['created_at']) ?></small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="ri-chat-quote-line fs-36 text-muted"></i>
                                        <p class="text-muted mt-2">Chưa có phản hồi từ khách hàng</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Tab: Công nợ -->
                            <div class="tab-pane" id="tab-debt" role="tabpanel">
                                <h6 class="mb-3">Công nợ</h6>
                                <?php
                                $debtOrders = \Core\Database::fetchAll(
                                    "SELECT order_number, total, paid_amount, (total - paid_amount) as debt, payment_status, due_date, status, created_at
                                     FROM orders WHERE contact_id = ? AND is_deleted = 0 AND payment_status != 'paid' AND status != 'cancelled'
                                     ORDER BY due_date ASC",
                                    [$contact['id']]
                                );
                                $totalDebt = array_sum(array_column($debtOrders, 'debt'));
                                ?>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <div class="p-3 border rounded text-center">
                                            <p class="text-muted mb-1 fs-12">Tổng công nợ</p>
                                            <h4 class="text-danger mb-0"><?= format_money($totalDebt) ?></h4>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 border rounded text-center">
                                            <p class="text-muted mb-1 fs-12">Số đơn chưa thanh toán</p>
                                            <h4 class="text-warning mb-0"><?= count($debtOrders) ?></h4>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 border rounded text-center">
                                            <p class="text-muted mb-1 fs-12">Quá hạn</p>
                                            <?php $overdue = array_filter($debtOrders, fn($d) => $d['due_date'] && $d['due_date'] < date('Y-m-d')); ?>
                                            <h4 class="<?= count($overdue) > 0 ? 'text-danger' : 'text-success' ?> mb-0"><?= count($overdue) ?></h4>
                                        </div>
                                    </div>
                                </div>
                                <?php if (!empty($debtOrders)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr><th>Mã đơn</th><th>Tổng tiền</th><th>Đã trả</th><th>Còn nợ</th><th>Hạn TT</th><th>Trạng thái</th></tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($debtOrders as $do): ?>
                                                <tr class="<?= ($do['due_date'] && $do['due_date'] < date('Y-m-d')) ? 'table-danger' : '' ?>">
                                                    <td><a href="<?= url('orders/' . ($do['id'] ?? '#')) ?>"><?= e($do['order_number']) ?></a></td>
                                                    <td><?= format_money($do['total']) ?></td>
                                                    <td class="text-success"><?= format_money($do['paid_amount']) ?></td>
                                                    <td class="fw-medium text-danger"><?= format_money($do['debt']) ?></td>
                                                    <td class="<?= ($do['due_date'] && $do['due_date'] < date('Y-m-d')) ? 'text-danger fw-medium' : 'text-muted' ?>">
                                                        <?= $do['due_date'] ? format_date($do['due_date']) : '-' ?>
                                                        <?= ($do['due_date'] && $do['due_date'] < date('Y-m-d')) ? ' <span class="badge bg-danger">Quá hạn</span>' : '' ?>
                                                    </td>
                                                    <td><span class="badge bg-<?= $do['payment_status'] === 'partial' ? 'warning' : 'danger' ?>"><?= $do['payment_status'] === 'partial' ? 'Trả một phần' : 'Chưa trả' ?></span></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="ri-checkbox-circle-line fs-36 text-success"></i>
                                        <p class="text-muted mt-2">Không có công nợ</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Tab: SMS -->
                            <div class="tab-pane" id="tab-sms" role="tabpanel">
                                <h6 class="mb-3">Lịch sử SMS</h6>
                                <?php
                                $smsList = \Core\Database::fetchAll(
                                    "SELECT a.*, u.name as user_name FROM activities a LEFT JOIN users u ON a.user_id = u.id
                                     WHERE a.contact_id = ? AND a.type = 'sms'
                                     ORDER BY a.created_at DESC LIMIT 20",
                                    [$contact['id']]
                                );
                                ?>
                                <?php if (!empty($smsList)): ?>
                                    <div style="max-height: 400px; overflow-y: auto;">
                                        <?php foreach ($smsList as $sms): ?>
                                            <div class="d-flex mb-3 p-3 border rounded">
                                                <div class="avatar-xs flex-shrink-0 me-3">
                                                    <span class="avatar-title bg-success-subtle text-success rounded-circle"><i class="ri-message-2-line"></i></span>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <p class="mb-1"><?= e($sms['title']) ?></p>
                                                    <small class="text-muted"><i class="ri-user-line me-1"></i><?= e($sms['user_name'] ?? 'Hệ thống') ?> - <?= time_ago($sms['created_at']) ?></small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="ri-message-2-line fs-36 text-muted"></i>
                                        <p class="text-muted mt-2">Chưa có SMS</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Tab: Lịch đi tuyến -->
                            <div class="tab-pane" id="tab-routes" role="tabpanel">
                                <div class="d-flex justify-content-between mb-3">
                                    <h6 class="mb-0">Lịch đi tuyến</h6>
                                    <a href="<?= url('calendar/create?type=visit&contact_id=' . $contact['id']) ?>" class="btn btn-soft-primary"><i class="ri-add-line me-1"></i>Thêm lịch</a>
                                </div>
                                <?php
                                $routes = \Core\Database::fetchAll(
                                    "SELECT ce.*, u.name as user_name FROM calendar_events ce LEFT JOIN users u ON ce.user_id = u.id
                                     WHERE ce.contact_id = ? AND ce.type = 'visit'
                                     ORDER BY ce.start_at DESC LIMIT 20",
                                    [$contact['id']]
                                );
                                ?>
                                <?php if (!empty($routes)): ?>
                                    <?php foreach ($routes as $route): ?>
                                        <div class="d-flex mb-3 p-3 border rounded">
                                            <div class="avatar-xs flex-shrink-0 me-3">
                                                <span class="avatar-title bg-<?= $route['is_completed'] ? 'success' : 'primary' ?>-subtle text-<?= $route['is_completed'] ? 'success' : 'primary' ?> rounded-circle">
                                                    <i class="ri-<?= $route['is_completed'] ? 'checkbox-circle' : 'route' ?>-line"></i>
                                                </span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between">
                                                    <h6 class="mb-1"><?= e($route['title']) ?></h6>
                                                    <?php if ($route['is_completed']): ?>
                                                        <span class="badge bg-success">Đã hoàn thành</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-primary">Chưa đi</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-muted fs-12">
                                                    <i class="ri-time-line me-1"></i><?= format_datetime($route['start_at']) ?>
                                                    <?php if ($route['location']): ?>
                                                        <span class="ms-2"><i class="ri-map-pin-line me-1"></i><?= e($route['location']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <small class="text-muted"><i class="ri-user-line me-1"></i><?= e($route['user_name'] ?? '-') ?></small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="ri-route-line fs-36 text-muted"></i>
                                        <p class="text-muted mt-2">Chưa có lịch đi tuyến</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Tab: Automation -->
                            <div class="tab-pane" id="tab-automation" role="tabpanel">
                                <h6 class="mb-3">Automation rules đang áp dụng</h6>
                                <?php
                                try {
                                    $autoRules = \Core\Database::fetchAll(
                                        "SELECT * FROM automation_rules WHERE is_active = 1 AND module = 'contact' ORDER BY name"
                                    );
                                } catch (\Exception $e) { $autoRules = []; }
                                try {
                                    $autoLogs = \Core\Database::fetchAll(
                                        "SELECT al.*, ar.name as rule_name FROM automation_logs al
                                         JOIN automation_rules ar ON al.rule_id = ar.id
                                         ORDER BY al.created_at DESC LIMIT 20"
                                    );
                                } catch (\Exception $e) { $autoLogs = []; }
                                ?>
                                <?php if (!empty($autoRules)): ?>
                                    <div class="mb-4">
                                        <p class="text-muted mb-2">Rules đang hoạt động:</p>
                                        <?php foreach ($autoRules as $rule): ?>
                                            <div class="d-flex align-items-center mb-2 p-2 border rounded">
                                                <i class="ri-robot-line text-primary me-2 fs-20"></i>
                                                <div class="flex-grow-1">
                                                    <span class="fw-medium"><?= e($rule['name']) ?></span>
                                                    <span class="badge bg-primary-subtle text-primary ms-2"><?= e($rule['trigger_event']) ?></span>
                                                </div>
                                                <span class="badge bg-success">Đang bật</span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <h6 class="mb-3">Lịch sử automation</h6>
                                <?php if (!empty($autoLogs)): ?>
                                    <div style="max-height: 300px; overflow-y: auto;">
                                        <?php foreach ($autoLogs as $log): ?>
                                            <div class="d-flex mb-2 p-2 border-start border-3 border-<?= $log['status'] === 'success' ? 'success' : 'danger' ?> bg-light rounded-end">
                                                <div class="flex-grow-1 ms-2">
                                                    <div class="d-flex justify-content-between">
                                                        <span class="fw-medium fs-13"><?= e($log['rule_name'] ?? 'Rule') ?></span>
                                                        <span class="badge bg-<?= $log['status'] === 'success' ? 'success' : 'danger' ?>"><?= $log['status'] === 'success' ? 'OK' : 'Lỗi' ?></span>
                                                    </div>
                                                    <small class="text-muted"><?= time_ago($log['created_at']) ?></small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="ri-robot-line fs-36 text-muted"></i>
                                        <p class="text-muted mt-2">Chưa có automation nào chạy cho KH này</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

<script>
// Activity type toggle
document.querySelectorAll('.activity-type-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.activity-type-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('activityType').value = this.dataset.type;
    });
});

// Filter activities (client-side)
function filterActivities() {
    const search = document.getElementById('activitySearch')?.value.toLowerCase() || '';
    const user = document.getElementById('activityUserFilter')?.value || '';
    const type = document.getElementById('activityTypeFilter')?.value || '';

    document.querySelectorAll('.activity-item').forEach(item => {
        const matchSearch = !search || item.dataset.text.includes(search);
        const matchUser = !user || item.dataset.user === user;
        const matchType = !type || item.dataset.type === type;
        item.style.display = (matchSearch && matchUser && matchType) ? '' : 'none';
    });
}

document.getElementById('activitySearch')?.addEventListener('input', filterActivities);
document.getElementById('activityUserFilter')?.addEventListener('change', filterActivities);
document.getElementById('activityTypeFilter')?.addEventListener('change', filterActivities);
</script>

        <div class="row">
            <div class="col-xl-8 offset-xl-4">
                <?php $chatEntityType = 'contact'; $chatEntityId = $contact['id']; include BASE_PATH . '/resources/views/components/internal-chat.php'; ?>
            </div>
        </div>
