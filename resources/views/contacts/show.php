<?php $pageTitle = e($contact['company_name'] ?? ($contact['first_name'] . ' ' . ($contact['last_name'] ?? ''))); ?>

        <?php if (!empty($_SESSION['_cp_blocked']) && $_SESSION['_cp_blocked']['contact_id'] == $contact['id']): ?>
        <div class="alert alert-warning alert-dismissible mb-3">
            <h6 class="alert-heading"><i class="ri-alert-line me-1"></i>Một số người liên hệ không thể thêm do trùng SĐT/Email</h6>
            <p class="mb-2 fs-13">Những người dưới đây đã có trong hệ thống do sale khác quản. Gửi yêu cầu để được thêm vào KH này.</p>
            <?php foreach ($_SESSION['_cp_blocked']['items'] as $bi): ?>
            <div class="d-flex align-items-center gap-2 p-2 bg-white rounded border mb-2">
                <div class="flex-grow-1">
                    <strong><?= e($bi['name']) ?></strong>
                    <span class="text-muted fs-12">· <?= e($bi['phone'] ?? '') ?> <?= e($bi['email'] ?? '') ?></span>
                </div>
                <button type="button" class="btn btn-soft-warning" onclick='sendPersonMergeReq(<?= json_encode($bi) ?>, <?= (int)$contact['id'] ?>, this)'>
                    <i class="ri-send-plane-line me-1"></i> Gửi yêu cầu
                </button>
            </div>
            <?php endforeach; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <script>
        function sendPersonMergeReq(item, contactId, btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i>Đang gửi...';
            var fd = new FormData();
            fd.append('existing_person_id', item.existing_person_id);
            fd.append('target_contact_id', contactId);
            fd.append('cp_name', item.name);
            fd.append('cp_phone', item.phone || '');
            fd.append('cp_email', item.email || '');
            fd.append('cp_position', item.position || '');
            fd.append('cp_title', item.title || '');
            fd.append('_token', '<?= csrf_token() ?>');
            fetch('<?= url("merge-requests/person") ?>', { method: 'POST', body: fd })
                .then(r => r.json()).then(d => {
                    if (d.success) {
                        btn.innerHTML = '<i class="ri-check-line me-1"></i>Đã gửi';
                        btn.classList.replace('btn-soft-warning', 'btn-soft-success');
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="ri-send-plane-line me-1"></i>Gửi lại';
                        alert(d.error || 'Lỗi');
                    }
                });
        }
        </script>
        <?php unset($_SESSION['_cp_blocked']); endif; ?>

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
            <div class="col-xl-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mx-auto mb-3 position-relative" style="width:80px;height:80px">
                            <?php if (!empty($contact['avatar']) && file_exists(BASE_PATH . '/public/uploads/avatars/' . $contact['avatar'])): ?>
                                <img src="<?= url('uploads/avatars/' . $contact['avatar']) ?>" class="rounded-circle object-fit-cover" style="width:80px;height:80px" id="contactAvatar">
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary fw-bold" style="width:80px;height:80px;font-size:32px" id="contactAvatar">
                                    <?= strtoupper(mb_substr($contact['first_name'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            <label class="position-absolute bottom-0 end-0 bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width:26px;height:26px;cursor:pointer" title="Đổi ảnh">
                                <i class="ri-camera-line text-white fs-12"></i>
                                <input type="file" class="d-none" accept="image/*" onchange="uploadContactAvatar(this)">
                            </label>
                        </div>
                        <h5 class="mb-1"><?= e($contact['company_name'] ?? ($contact['first_name'] . ' ' . ($contact['last_name'] ?? ''))) ?></h5>
                        <?php if ($contact['position']): ?>
                        <p class="text-muted mb-0"><?= e($contact['position']) ?></p>
                        <?php endif; ?>

                        <?php
                        $sColors = []; $sLabels = [];
                        foreach ($contactStatuses ?? [] as $_cs) { $sColors[$_cs['slug']] = $_cs['color']; $sLabels[$_cs['slug']] = $_cs['name']; }
                        if (empty($sLabels)) { $sColors = ['new'=>'info','contacted'=>'primary','qualified'=>'warning','converted'=>'success','lost'=>'danger']; $sLabels = ['new'=>'Mới','contacted'=>'Đã liên hệ','qualified'=>'Tiềm năng','converted'=>'Chuyển đổi','lost'=>'Mất']; }
                        ?>
                        <span class="badge bg-<?= $sColors[$contact['status']] ?? 'secondary' ?> fs-12">
                            <?= $sLabels[$contact['status']] ?? $contact['status'] ?>
                        </span>

                        <!-- Tags (inline badges only) -->
                        <?php $contactTags = \App\Services\TagService::getForEntity('contact', $contact['id']); ?>
                        <?php if (!empty($contactTags)): ?>
                        <div class="mt-2 d-flex gap-1 justify-content-center flex-wrap">
                            <?php foreach ($contactTags as $t): ?>
                                <span class="badge" style="background-color:<?= e($t['color'] ?? '#405189') ?>"><?= e($t['name']) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <div class="mt-3 d-flex gap-2 justify-content-center flex-wrap">
                            <?php if (!empty($contact['email']) && plugin_active('email')): ?>
                            <a href="<?= url('email/compose?to=' . urlencode($contact['email'])) ?>" class="btn btn-soft-info">
                                <i class="ri-mail-send-line me-1"></i> Gửi email
                            </a>
                            <?php endif; ?>
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

                <!-- Thông tin công ty -->
                <div class="card">
                    <div class="card-header p-2">
                        <h5 class="card-title mb-0"><i class="ri-building-line me-1"></i> Thông tin công ty</h5>
                    </div>
                    <div class="card-body py-2">
                        <table class="table table-borderless table-sm mb-0">
                            <tbody>
                                <tr>
                                    <th class="text-muted" width="35%"><i class="ri-hashtag me-2"></i>Mã KH</th>
                                    <td><?= e($contact['account_code'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted"><i class="ri-phone-line me-2"></i>ĐT</th>
                                    <td><?= e($contact['phone'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted"><i class="ri-mail-line me-2"></i>Email</th>
                                    <td><?= e($contact['email'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted"><i class="ri-map-pin-line me-2"></i>Địa chỉ</th>
                                    <td><?= e($contact['address'] ?? '-') ?></td>
                                </tr>
                                <?php if ($contact['province'] ?? ''): ?>
                                <tr>
                                    <th class="text-muted"><i class="ri-road-map-line me-2"></i>Tỉnh/TP</th>
                                    <td><?= e($contact['province']) ?><?= ($contact['district'] ?? '') ? ' - ' . e($contact['district']) : '' ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <th class="text-muted"><i class="ri-file-list-3-line me-2"></i>MST</th>
                                    <td><?= e($contact['tax_code'] ?? '-') ?></td>
                                </tr>
                                <?php if ($contact['website'] ?? ''): ?>
                                <tr>
                                    <th class="text-muted"><i class="ri-global-line me-2"></i>Website</th>
                                    <td><a href="<?= e($contact['website']) ?>" target="_blank" class="text-truncate d-inline-block" style="max-width:180px"><?= e($contact['website']) ?></a></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($contact['industry'] ?? ''): ?>
                                <tr>
                                    <th class="text-muted"><i class="ri-briefcase-line me-2"></i>Ngành KD</th>
                                    <td><?= e($contact['industry']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($contact['fax'] ?? ''): ?>
                                <tr>
                                    <th class="text-muted"><i class="ri-printer-line me-2"></i>Fax</th>
                                    <td><?= e($contact['fax']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <th class="text-muted"><i class="ri-links-line me-2"></i>Nguồn</th>
                                    <td>
                                        <?php if ($contact['source_name'] ?? ''): ?>
                                            <span class="badge" style="background-color: <?= safe_color($contact['source_color'] ?? '#6c757d') ?>"><?= e($contact['source_name']) ?></span>
                                        <?php else: ?>-<?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted"><i class="ri-group-line me-2"></i>Nhóm KH</th>
                                    <td><?= e($contact['customer_group'] ?? '-') ?></td>
                                </tr>
                                <?php if ($contact['description'] ?? ''): ?>
                                <tr>
                                    <th class="text-muted"><i class="ri-file-text-line me-2"></i>Mô tả</th>
                                    <td class="text-muted fs-12"><?= e($contact['description']) ?></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Người liên hệ -->
                <div class="card">
                    <div class="card-header p-2 d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0"><i class="ri-contacts-book-line me-1"></i> Người liên hệ</h5>
                        <span class="badge bg-primary"><?= count($contactPersons ?? []) ?></span>
                    </div>
                    <div class="card-body py-2">
                        <?php if (!empty($contactPersons)): ?>
                            <?php foreach ($contactPersons as $cp): ?>
                            <div class="d-flex align-items-start gap-2 py-2 <?= $cp !== end($contactPersons) ? 'border-bottom' : '' ?>">
                                <div class="avatar-xs flex-shrink-0">
                                    <span class="avatar-title bg-primary-subtle text-primary rounded-circle"><?= strtoupper(mb_substr($cp['full_name'], 0, 1)) ?></span>
                                </div>
                                <div class="flex-grow-1 <?= ($cp['is_active'] ?? 1) ? '' : 'opacity-50' ?>">
                                    <div class="fw-medium">
                                        <?= e(($cp['title'] ?? '') ? ucfirst($cp['title']) . ' ' : '') ?>
                                        <?php if (!empty($cp['person_id'])): ?>
                                            <a href="<?= url('persons/' . $cp['person_id']) ?>" class="<?= ($cp['is_active'] ?? 1) ? '' : 'text-muted text-decoration-line-through' ?>"><?= e($cp['full_name']) ?></a>
                                        <?php else: ?>
                                            <?= e($cp['full_name']) ?>
                                        <?php endif; ?>
                                        <?php if ($cp['is_primary']): ?><span class="badge bg-success-subtle text-success ms-1">Chính</span><?php endif; ?>
                                        <?php if (!($cp['is_active'] ?? 1)): ?>
                                            <span class="badge bg-secondary-subtle text-secondary ms-1">Đã nghỉ<?= !empty($cp['end_date']) ? ' ' . format_date($cp['end_date']) : '' ?></span>
                                        <?php elseif (!empty($cp['start_date'])): ?>
                                            <span class="text-muted fs-12 ms-1">từ <?= format_date($cp['start_date']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($cp['position'] ?? ''): ?><div class="text-muted fs-12"><?= e($cp['position']) ?></div><?php endif; ?>
                                    <div class="d-flex gap-3 mt-1 fs-12">
                                        <?php if ($cp['phone'] ?? ''): ?><span><i class="ri-phone-line me-1"></i><?= e($cp['phone']) ?></span><?php endif; ?>
                                        <?php if ($cp['email'] ?? ''): ?><span><i class="ri-mail-line me-1"></i><?= e($cp['email']) ?></span><?php endif; ?>
                                    </div>
                                    <?php if ($cp['note'] ?? ''): ?><div class="text-muted fs-12 mt-1"><i class="ri-sticky-note-line me-1"></i><?= e($cp['note']) ?></div><?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-3">
                                <i class="ri-user-add-line fs-20 d-block mb-1"></i>
                                <small>Chưa có người liên hệ</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Phụ trách & Theo dõi -->
                <div class="card">
                    <div class="card-header p-2"><h5 class="card-title mb-0"><i class="ri-team-line me-1"></i> Phụ trách & Theo dõi</h5></div>
                    <div class="card-body py-2">
                        <?php $allUsers = \Core\Database::fetchAll("SELECT u.id, u.name, u.avatar, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name"); ?>

                        <!-- Phụ trách chính -->
                        <label class="text-muted fs-12">Phụ trách chính</label>
                        <div class="d-flex align-items-center justify-content-between mb-3 p-2 bg-light rounded">
                            <div class="d-flex align-items-center">
                                <?php if ($contact['owner_avatar'] ?? null): ?>
                                <img src="<?= asset($contact['owner_avatar']) ?>" class="rounded-circle me-2" width="32" height="32" style="object-fit:cover">
                                <?php else: ?>
                                <div class="avatar-xs me-2"><span class="avatar-title bg-primary text-white rounded-circle fs-12"><?= strtoupper(mb_substr($contact['owner_name'] ?? 'N', 0, 1)) ?></span></div>
                                <?php endif; ?>
                                <span class="fw-medium" id="ownerName"><?= e($contact['owner_name'] ?? 'Chưa gán') ?></span>
                            </div>
                            <div class="position-relative">
                                <button type="button" class="btn btn-soft-primary py-0 px-2" id="changeOwnerBtn">Đổi</button>
                                <div id="ownerSearchBox" class="position-absolute end-0 bg-white border rounded shadow p-2" style="display:none;width:220px;z-index:1060;top:100%;margin-top:4px">
                                    <input type="text" class="form-control mb-1" id="ownerSearchInput" placeholder="Tìm người..." autocomplete="off">
                                    <div id="ownerSearchResults" style="max-height:150px;overflow-y:auto"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Người liên quan -->
                        <label class="text-muted fs-12">Người liên quan</label>
                        <div id="followerTags" class="d-flex flex-wrap gap-1 mb-2">
                            <?php
                            $shownIds = [$contact['owner_id'] ?? 0];

                            // Followers (thêm thủ công)
                            foreach ($followers ?? [] as $f):
                                if (in_array($f['user_id'], $shownIds)) continue;
                                $shownIds[] = $f['user_id'];
                            ?>
                                <?php $fAvatar = $allUsers ? array_column($allUsers, 'avatar', 'id')[$f['user_id']] ?? null : null; ?>
                                <span class="badge bg-light text-dark d-inline-flex align-items-center gap-1 py-1 px-2 border fs-12 fw-normal" data-uid="<?= $f['user_id'] ?>">
                                    <?php if ($fAvatar): ?><img src="<?= asset($fAvatar) ?>" class="rounded-circle" width="20" height="20" style="object-fit:cover">
                                    <?php else: ?><span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width:20px;height:20px;font-size:9px"><?= mb_strtoupper(mb_substr($f['name'], 0, 1)) ?></span><?php endif; ?>
                                    <?= e($f['name']) ?>
                                    <i class="ri-close-line text-muted" style="cursor:pointer;font-size:14px" onclick="removeFollower(<?= $f['user_id'] ?>, this)"></i>
                                </span>
                            <?php endforeach; ?>

                            <?php
                            // Trưởng/phó phòng theo hierarchy
                            $placeholders = implode(',', array_map('intval', $shownIds));
                            if ($contact['owner_id'] ?? null) {
                                try {
                                    $ownerDept = \Core\Database::fetch("SELECT department_id FROM users WHERE id = ?", [$contact['owner_id']]);
                                    if ($ownerDept && $ownerDept['department_id']) {
                                        $deptChain = [];
                                        $curDeptId = $ownerDept['department_id'];
                                        $maxD = 10;
                                        while ($curDeptId && $maxD-- > 0) {
                                            $deptChain[] = (int)$curDeptId;
                                            $pd = \Core\Database::fetch("SELECT parent_id FROM departments WHERE id = ?", [$curDeptId]);
                                            $pid = $pd['parent_id'] ?? null;
                                            if (!$pid || $pid == $curDeptId || in_array((int)$pid, $deptChain)) break;
                                            $curDeptId = $pid;
                                        }
                                        $deptPh = implode(',', $deptChain);
                                        $deptMgrs = \Core\Database::fetchAll(
                                            "SELECT DISTINCT u.id, u.name, u.avatar, CASE WHEN d.manager_id = u.id THEN 'Trưởng phòng' ELSE 'Phó phòng' END as role_label
                                             FROM departments d JOIN users u ON (u.id = d.manager_id OR u.id = d.vice_manager_id)
                                             WHERE d.id IN ({$deptPh}) AND u.is_active = 1 AND u.id NOT IN ({$placeholders})"
                                        );
                                        foreach ($deptMgrs as $dm):
                                            $shownIds[] = $dm['id'];
                                        ?>
                                <span class="badge bg-light text-dark d-inline-flex align-items-center gap-1 py-1 px-2 border fs-12 fw-normal" title="<?= e($dm['role_label']) ?>">
                                    <?php if ($dm['avatar'] ?? null): ?><img src="<?= asset($dm['avatar']) ?>" class="rounded-circle" width="20" height="20" style="object-fit:cover">
                                    <?php else: ?><span class="rounded-circle bg-warning text-white d-inline-flex align-items-center justify-content-center" style="width:20px;height:20px;font-size:9px"><?= mb_strtoupper(mb_substr($dm['name'], 0, 1)) ?></span><?php endif; ?>
                                    <?= e($dm['name']) ?>
                                </span>
                                        <?php endforeach;
                                        $placeholders = implode(',', array_map('intval', $shownIds));
                                    }
                                } catch (\Exception $e) {}
                            }
                            ?>

                            <?php
                            // Ban lãnh đạo (system group) + Người có quyền "Xem tất cả"
                            try {
                                $autoUsers = \Core\Database::fetchAll(
                                    "SELECT DISTINCT u.id, u.name, u.avatar,
                                        CASE WHEN pg.is_system = 1 THEN 'Ban lãnh đạo' ELSE 'Xem tất cả' END as role_label
                                     FROM users u
                                     JOIN user_permission_groups upg ON u.id = upg.user_id
                                     JOIN permission_groups pg ON upg.group_id = pg.id
                                     LEFT JOIN group_permissions gp ON pg.id = gp.group_id
                                     LEFT JOIN permissions p ON gp.permission_id = p.id
                                     WHERE u.is_active = 1 AND u.id NOT IN ({$placeholders})
                                     AND (pg.is_system = 1 OR (p.module = 'contacts' AND p.action = 'view_all'))
                                     ORDER BY u.name"
                                );
                                foreach ($autoUsers as $au):
                            ?>
                                <span class="badge bg-light text-dark d-inline-flex align-items-center gap-1 py-1 px-2 border fs-12 fw-normal" title="<?= e($au['role_label']) ?>">
                                    <?php if ($au['avatar'] ?? null): ?><img src="<?= asset($au['avatar']) ?>" class="rounded-circle" width="20" height="20" style="object-fit:cover">
                                    <?php else: ?><span class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center" style="width:20px;height:20px;font-size:9px"><?= mb_strtoupper(mb_substr($au['name'], 0, 1)) ?></span><?php endif; ?>
                                    <?= e($au['name']) ?>
                                </span>
                            <?php endforeach; } catch (\Exception $e) {} ?>
                        </div>
                    </div>
                </div>

                <!-- Owner change script -->
                <script>
                (function() {
                    var cid = <?= $contact['id'] ?>, tok = '<?= $_SESSION['csrf_token'] ?? '' ?>';
                    var users = <?= json_encode($allUsers) ?>;
                    var btn = document.getElementById('changeOwnerBtn');
                    var box = document.getElementById('ownerSearchBox');
                    var input = document.getElementById('ownerSearchInput');
                    var results = document.getElementById('ownerSearchResults');

                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        box.style.display = box.style.display === 'none' ? 'block' : 'none';
                        if (box.style.display === 'block') { input.value = ''; renderOwnerList(''); input.focus(); }
                    });

                    function renderOwnerList(q) {
                        results.innerHTML = '';
                        users.forEach(function(u) {
                            if (q && u.name.toLowerCase().indexOf(q.toLowerCase()) === -1) return;
                            var div = document.createElement('div');
                            div.className = 'px-2 py-1 rounded';
                            div.style.cursor = 'pointer';
                            div.textContent = u.name;
                            div.addEventListener('mouseenter', function() { this.style.backgroundColor = '#f3f6f9'; });
                            div.addEventListener('mouseleave', function() { this.style.backgroundColor = ''; });
                            div.addEventListener('click', function() {
                                fetch('/contacts/' + cid + '/change-owner', {
                                    method: 'POST',
                                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                                    body: '_token=' + tok + '&owner_id=' + u.id
                                }).then(function() {
                                    document.getElementById('ownerName').textContent = u.name;
                                    document.querySelector('#changeOwnerBtn').closest('.mb-3').querySelector('.avatar-title').textContent = u.name.charAt(0).toUpperCase();
                                    box.style.display = 'none';
                                });
                            });
                            results.appendChild(div);
                        });
                    }

                    input.addEventListener('input', function() { renderOwnerList(this.value); });
                    document.addEventListener('click', function(e) { if (!box.contains(e.target) && e.target !== btn) box.style.display = 'none'; });
                })();
                </script>
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
                            var av = u.avatar ? '<img src="/' + u.avatar + '" class="rounded-circle me-2" width="28" height="28" style="object-fit:cover">' : '<span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center me-2" style="width:28px;height:28px;font-size:11px">' + u.name.charAt(0).toUpperCase() + '</span>';
                            html += '<a class="dropdown-item d-flex align-items-center" href="#" data-id="' + u.id + '" data-name="' + u.name + '">' + av + '<div><div class="fw-medium">' + u.name + '</div>' + (u.dept_name ? '<small class="text-muted">' + u.dept_name + '</small>' : '') + '</div></a>';
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
                                span.className = 'badge bg-light text-dark d-inline-flex align-items-center gap-1 py-1 px-2 border fs-12 fw-normal';
                                span.dataset.uid = uid;
                                var av = '';
                                users.forEach(function(u2) { if (u2.id === uid && u2.avatar) av = u2.avatar; });
                                var avHtml = av ? '<img loading="lazy" src="/' + av + '" class="rounded-circle" width="20" height="20" style="object-fit:cover">' : '<span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width:20px;height:20px;font-size:9px">' + name.charAt(0).toUpperCase() + '</span>';
                                span.innerHTML = avHtml + ' ' + name + ' <i class="ri-close-line text-muted" style="cursor:pointer;font-size:14px" onclick="removeFollower(' + uid + ', this)"></i>';
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

                <!-- KT Accounting — receivable balance + recent txns -->
                <?php if (\App\Services\KtAccountingService::isConfigured()):
                    $ktBal = \App\Services\KtAccountingService::fetchCustomerBalance((int)$contact['id']);
                    $ktTx  = \App\Services\KtAccountingService::fetchCustomerTransactions((int)$contact['id'], 5);
                ?>
                <?php if ($ktBal || !empty($ktTx)): ?>
                <div class="card">
                    <div class="card-header py-2">
                        <h6 class="card-title mb-0"><i class="ri-bank-line me-1"></i> Kế toán</h6>
                    </div>
                    <div class="card-body p-0">
                        <?php if ($ktBal): ?>
                        <div class="px-3 py-2 border-bottom">
                            <div class="text-muted fs-12">Công nợ phải thu (131)</div>
                            <div class="fs-5 fw-semibold <?= ((float)($ktBal['receivable_balance'] ?? 0)) > 0 ? 'text-danger' : 'text-success' ?>">
                                <?= format_money($ktBal['receivable_balance'] ?? 0) ?>
                            </div>
                            <small class="text-muted">
                                <?= (int)($ktBal['transaction_count'] ?? 0) ?> giao dịch
                                <?php if ($ktBal['last_transaction_date'] ?? null): ?>
                                    · GD gần nhất <?= format_date($ktBal['last_transaction_date']) ?>
                                <?php endif; ?>
                            </small>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($ktTx)): ?>
                        <div class="px-3 py-2">
                            <div class="text-muted fs-12 mb-1">Giao dịch gần nhất</div>
                            <?php foreach (array_slice($ktTx, 0, 5) as $tx): ?>
                            <div class="d-flex justify-content-between align-items-center py-1">
                                <div>
                                    <span class="badge bg-secondary-subtle text-secondary me-1 fs-11"><?= e($tx['doc_type'] ?? '') ?></span>
                                    <small><?= e($tx['doc_number'] ?? '') ?></small>
                                </div>
                                <small class="fw-medium"><?= format_money($tx['total_amount'] ?? 0) ?></small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>

            </div>

            <!-- Right Column - Tabbed Layout -->
            <div class="col-xl-9">
                <!-- Stats Bar -->
                <?php
                $realActivities = array_filter($activities ?? [], fn($a) => ($a['type'] ?? '') !== 'system');
                $activityCount = count($realActivities);
                $lastActivity = !empty($realActivities) ? reset($realActivities) : null;
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
                    <div class="card-header p-2">
                        <ul class="nav nav-tabs arrow-navtabs nav-primary" role="tablist">
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
                            <div class="tab-pane fade show active" id="tab-exchange" role="tabpanel">
                                <?php if (function_exists('activity_exchange_render')):
                                    activity_exchange_render('contact', $contact['id'], true);
                                else: ?>
                                    <div class="text-center py-5"><i class="ri-chat-3-line fs-48 text-muted"></i><p class="text-muted mt-2">Plugin trao đổi chưa được bật</p></div>
                                <?php endif; ?>
                            </div>

                            <?php if(false): /* removed */ ?>
                                <div class="d-none">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="contact_id" value="<?= $contact['id'] ?>">
                                    <input type="hidden" name="type" value="note" id="activityType">
                                    <input type="hidden" name="tagged_users" id="taggedUsers" value="">
                                    <input type="hidden" name="latitude" id="checkinLat">
                                    <input type="hidden" name="longitude" id="checkinLng">

                                    <div class="border rounded mb-3">
                                        <textarea name="title" class="form-control border-0" rows="4" placeholder="Nhập nội dung trao đổi, ghi chú..." required id="activityTextarea" style="resize:none"></textarea>
                                        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-top bg-light" style="border-radius:0 0 6px 6px">
                                            <div class="d-flex gap-3">
                                                <label class="text-muted" style="cursor:pointer" title="Đính kèm file">
                                                    <i class="ri-attachment-2 fs-18"></i>
                                                    <input type="file" name="attachments[]" class="d-none" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip,.rar,.dwg,.dxf,.cad,.dwf,.skp,.3ds,.obj,.stl,.step,.stp,.iges,.igs" multiple onchange="previewAttach(this)">
                                                </label>
                                                <span class="text-muted" style="cursor:pointer" title="Tag @người dùng" onclick="var ta=document.getElementById('activityTextarea');ta.value+=' @';ta.focus();ta.dispatchEvent(new Event('input'));">
                                                    <i class="ri-at-line fs-18"></i>
                                                </span>
                                                <span class="text-muted" style="cursor:pointer" title="Check-in vị trí" id="btnCheckin">
                                                    <i class="ri-map-pin-line fs-18"></i>
                                                </span>
                                                <span class="text-muted" style="cursor:pointer" title="Emoji" onclick="var ta=document.getElementById('activityTextarea');ta.value+=' 😊';ta.focus();">
                                                    <i class="ri-emotion-happy-line fs-18"></i>
                                                </span>
                                            </div>
                                            <button type="submit" class="btn btn-primary px-4">Gửi</button>
                                        </div>
                                        <div id="attachBadge" style="display:none" class="px-3 py-2 border-top bg-light">
                                            <div class="d-flex align-items-center gap-2">
                                                <img id="attachPreview" src="" class="rounded border" style="max-height:60px;display:none">
                                                <i id="attachIcon" class="ri-file-line fs-20 text-muted" style="display:none"></i>
                                                <div>
                                                    <div class="text-dark fw-medium" style="font-size:13px"><span id="attachName"></span></div>
                                                    <small class="text-muted" id="attachSize"></small>
                                                </div>
                                                <button type="button" class="btn btn-link text-danger p-0 ms-auto" onclick="clearAttach()"><i class="ri-close-line"></i></button>
                                            </div>
                                        </div>
                                    </div>

                                </form>

                                <!-- Activity Feed (Facebook style) -->
                                <div id="activityFeed" style="max-height:600px;overflow-y:auto">
                                    <?php if (!empty($activities)): ?>
                                        <?php
                                        // Build user avatar map
                                        $userAvatars = [];
                                        foreach ($allUsers ?? [] as $u) { $userAvatars[$u['name']] = $u['avatar'] ?? null; }
                                        ?>
                                        <?php foreach ($activities as $act):
                                            $userName = $act['user_name'] ?? 'Hệ thống';
                                            $userAvatar = $userAvatars[$userName] ?? null;
                                            $initial = mb_substr($userName, 0, 1);
                                            $isSystem = in_array($act['type'], ['system','deal']);
                                            // Highlight @mentions in text
                                            $content = e($act['title']);
                                            $content = preg_replace('/@([^\s,\.]+(?:\s[^\s,\.@]+)?)/', '<span class="text-primary fw-medium">@$1</span>', $content);
                                            // Highlight links
                                            $content = preg_replace('/(https?:\/\/\S+)/', '<a href="$1" target="_blank" class="text-primary">$1</a>', $content);
                                        ?>
                                        <div class="d-flex gap-3 py-3 <?= $isSystem ? 'bg-light rounded px-3' : '' ?>" style="border-bottom:1px solid #f3f3f3">
                                            <div class="flex-shrink-0">
                                                <?php if ($userAvatar): ?>
                                                <img src="<?= asset($userAvatar) ?>" class="rounded-circle" width="40" height="40" style="object-fit:cover">
                                                <?php else: ?>
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:40px;height:40px;font-size:14px"><?= strtoupper($initial) ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <strong style="font-size:14px"><?= e($userName) ?></strong>
                                                    <small class="text-muted"><?= !empty($act['created_at']) ? date('d/m/Y H:i', strtotime($act['created_at'])) : '' ?></small>
                                                </div>
                                                <div style="white-space:pre-wrap;word-break:break-word"><?= $content ?></div>
                                                <?php if (!empty($act['description'])): ?>
                                                <div class="text-muted mt-1" style="font-size:13px;white-space:pre-wrap"><?= e($act['description']) ?></div>
                                                <?php endif; ?>
                                                <?php if (!empty($act['tagged_users_display'])): ?>
                                                <div class="mt-1"><i class="ri-price-tag-3-line text-muted me-1"></i><span class="text-primary" style="font-size:13px"><?= e($act['tagged_users_display']) ?></span></div>
                                                <?php endif; ?>
                                                <?php if (!empty($act['attachment'])):
                                                    $attPaths = explode('|', $act['attachment']);
                                                    $attNames = explode('|', $act['attachment_name'] ?? '');
                                                    $fileIcons = ['pdf'=>'ri-file-pdf-line text-danger','doc'=>'ri-file-word-line text-primary','docx'=>'ri-file-word-line text-primary','xls'=>'ri-file-excel-line text-success','xlsx'=>'ri-file-excel-line text-success','ppt'=>'ri-file-ppt-line text-warning','pptx'=>'ri-file-ppt-line text-warning','zip'=>'ri-file-zip-line text-info','rar'=>'ri-file-zip-line text-info','dwg'=>'ri-draft-line text-dark','dxf'=>'ri-draft-line text-dark','cad'=>'ri-draft-line text-dark','dwf'=>'ri-draft-line text-dark','skp'=>'ri-shape-line text-info','3ds'=>'ri-shape-line text-info','obj'=>'ri-shape-line text-info','stl'=>'ri-shape-line text-info','step'=>'ri-shape-line text-info','stp'=>'ri-shape-line text-info'];
                                                ?>
                                                <div class="mt-2 d-flex flex-wrap gap-2">
                                                    <?php foreach ($attPaths as $ai => $aPath):
                                                        $aPath = trim($aPath);
                                                        if (!$aPath) continue;
                                                        $aExt = strtolower(pathinfo($aPath, PATHINFO_EXTENSION));
                                                        $isImage = in_array($aExt, ['jpg','jpeg','png','gif','webp']);
                                                        $aName = trim($attNames[$ai] ?? basename($aPath));
                                                    ?>
                                                        <?php if ($isImage): ?>
                                                        <a href="<?= asset($aPath) ?>" target="_blank">
                                                            <img src="<?= asset($aPath) ?>" class="rounded border" style="max-width:200px;max-height:150px;object-fit:cover">
                                                        </a>
                                                        <?php else: ?>
                                                        <a href="<?= asset($aPath) ?>" target="_blank" class="d-flex align-items-center gap-2 p-2 bg-light rounded text-decoration-none">
                                                            <i class="<?= $fileIcons[$aExt] ?? 'ri-file-line text-muted' ?> fs-20"></i>
                                                            <span class="text-dark" style="font-size:13px"><?= e($aName) ?></span>
                                                            <i class="ri-download-line text-muted"></i>
                                                        </a>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </div>
                                                <?php endif; ?>

                                                <!-- Actions: Like / Dislike / Reply -->
                                                <div class="d-flex align-items-center gap-3 mt-2" style="font-size:13px">
                                                    <span class="act-btn <?= ($act['my_reaction'] ?? '') === 'like' ? 'text-primary fw-medium' : 'text-muted' ?>" style="cursor:pointer" onclick="reactActivity(<?= $act['id'] ?>,'like',this)">
                                                        <i class="ri-thumb-up-<?= ($act['my_reaction'] ?? '') === 'like' ? 'fill' : 'line' ?>"></i><?php if (($act['likes'] ?? 0) > 0): ?> <span class="react-count"><?= $act['likes'] ?></span><?php endif; ?>
                                                    </span>
                                                    <span class="act-btn <?= ($act['my_reaction'] ?? '') === 'dislike' ? 'text-danger fw-medium' : 'text-muted' ?>" style="cursor:pointer" onclick="reactActivity(<?= $act['id'] ?>,'dislike',this)">
                                                        <i class="ri-thumb-down-<?= ($act['my_reaction'] ?? '') === 'dislike' ? 'fill' : 'line' ?>"></i><?php if (($act['dislikes'] ?? 0) > 0): ?> <span class="react-count"><?= $act['dislikes'] ?></span><?php endif; ?>
                                                    </span>
                                                    <span class="text-muted act-btn" style="cursor:pointer" onclick="toggleReplyBox(<?= $act['id'] ?>)">
                                                        <i class="ri-reply-line"></i> Trả lời
                                                    </span>
                                                </div>

                                                <!-- Replies -->
                                                <?php if (!empty($act['replies'])): ?>
                                                <div class="ms-4 mt-2 border-start ps-3">
                                                    <?php foreach ($act['replies'] as $reply):
                                                        $rAvatar = $reply['user_avatar'] ?? null;
                                                        $rName = $reply['user_name'] ?? 'Hệ thống';
                                                        $rContent = e($reply['title']);
                                                        $rContent = preg_replace('/@([^\s,\.]+(?:\s[^\s,\.@]+)?)/', '<span class="text-primary fw-medium">@$1</span>', $rContent);
                                                    ?>
                                                    <div class="d-flex gap-2 py-2" style="border-bottom:1px solid #f8f8f8">
                                                        <?php if ($rAvatar): ?>
                                                        <img src="<?= asset($rAvatar) ?>" class="rounded-circle" width="28" height="28" style="object-fit:cover">
                                                        <?php else: ?>
                                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width:28px;height:28px;font-size:11px"><?= mb_substr($rName, 0, 1) ?></div>
                                                        <?php endif; ?>
                                                        <div class="flex-grow-1">
                                                            <strong style="font-size:13px"><?= e($rName) ?></strong>
                                                            <small class="text-muted ms-1"><?= date('d/m H:i', strtotime($reply['created_at'])) ?></small>
                                                            <div style="font-size:13px"><?= $rContent ?></div>
                                                            <div class="d-flex align-items-center gap-3 mt-1" style="font-size:12px">
                                                                <span class="act-btn <?= ($reply['my_reaction'] ?? '') === 'like' ? 'text-primary fw-medium' : 'text-muted' ?>" style="cursor:pointer" onclick="reactActivity(<?= $reply['id'] ?>,'like',this)">
                                                                    <i class="ri-thumb-up-<?= ($reply['my_reaction'] ?? '') === 'like' ? 'fill' : 'line' ?>"></i><?php if (($reply['likes'] ?? 0) > 0): ?> <span class="react-count"><?= $reply['likes'] ?></span><?php endif; ?>
                                                                </span>
                                                                <span class="act-btn <?= ($reply['my_reaction'] ?? '') === 'dislike' ? 'text-danger fw-medium' : 'text-muted' ?>" style="cursor:pointer" onclick="reactActivity(<?= $reply['id'] ?>,'dislike',this)">
                                                                    <i class="ri-thumb-down-<?= ($reply['my_reaction'] ?? '') === 'dislike' ? 'fill' : 'line' ?>"></i><?php if (($reply['dislikes'] ?? 0) > 0): ?> <span class="react-count"><?= $reply['dislikes'] ?></span><?php endif; ?>
                                                                </span>
                                                                <span class="text-muted act-btn" style="cursor:pointer" onclick="toggleReplyBox(<?= $act['id'] ?>)">
                                                                    <i class="ri-reply-line"></i> Trả lời
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                <?php endif; ?>

                                                <!-- Reply Box (hidden) -->
                                                <div class="ms-4 mt-2 d-none" id="replyBox-<?= $act['id'] ?>">
                                                    <div class="d-flex gap-2 align-items-center">
                                                        <input type="text" class="form-control" placeholder="Viết trả lời..." id="replyInput-<?= $act['id'] ?>" onkeydown="if(event.key==='Enter'){event.preventDefault();submitReply(<?= $act['id'] ?>)}">
                                                        <label class="btn btn-soft-secondary btn-sm mb-0" title="Đính kèm file">
                                                            <i class="ri-attachment-2"></i>
                                                            <input type="file" class="d-none" id="replyFile-<?= $act['id'] ?>" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.dwg,.dxf,.cad,.zip,.rar" onchange="this.closest('label').classList.toggle('btn-soft-primary',!!this.files[0]);this.closest('label').classList.toggle('btn-soft-secondary',!this.files[0])">
                                                        </label>
                                                        <button class="btn btn-primary btn-sm" onclick="submitReply(<?= $act['id'] ?>)">Gửi</button>
                                                    </div>
                                                    <div class="reply-file-preview mt-1" id="replyFilePreview-<?= $act['id'] ?>" style="display:none">
                                                        <small class="text-primary"><i class="ri-file-line me-1"></i><span></span></small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class="ri-chat-3-line fs-48 text-muted"></i>
                                            <p class="text-muted mt-2">Chưa có hoạt động trao đổi</p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                            </div>
                            <?php endif; /* END OLD INLINE CODE */ ?>

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
                                                            <?php
                                                            $oColors = ['draft'=>'secondary','pending'=>'warning','approved'=>'primary','processing'=>'info','completed'=>'success','cancelled'=>'danger'];
                                                            $oLabels = ['draft'=>'Nháp','pending'=>'Chờ duyệt','approved'=>'Đã duyệt','processing'=>'Đang xử lý','completed'=>'Hoàn thành','cancelled'=>'Đã hủy'];
                                                            $oStatus = $order['status'] ?? '';
                                                            ?>
                                                            <span class="badge bg-<?= $oColors[$oStatus] ?? 'secondary' ?>-subtle text-<?= $oColors[$oStatus] ?? 'secondary' ?>"><?= $oLabels[$oStatus] ?? e($oStatus) ?></span>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $pColors = ['unpaid'=>'danger','partial'=>'warning','paid'=>'success'];
                                                            $pLabels = ['unpaid'=>'Chưa TT','partial'=>'Một phần','paid'=>'Đã TT'];
                                                            $pStatus = $order['payment_status'] ?? 'unpaid';
                                                            ?>
                                                            <span class="badge bg-<?= $pColors[$pStatus] ?? 'secondary' ?>-subtle text-<?= $pColors[$pStatus] ?? 'secondary' ?>"><?= $pLabels[$pStatus] ?? e($pStatus) ?></span>
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
                                    <a href="<?= url('quotations/create?contact_id=' . $contact['id']) ?>" class="btn btn-soft-primary"><i class="ri-add-line me-1"></i>Tạo báo giá</a>
                                </div>
                                <?php
                                $quotations = \Core\Database::fetchAll(
                                    "SELECT q.*, u.name as owner_name FROM quotations q LEFT JOIN users u ON q.owner_id = u.id WHERE q.contact_id = ? ORDER BY q.created_at DESC LIMIT 20",
                                    [$contact['id']]
                                );
                                $qColors = ['draft'=>'secondary','pending'=>'warning','approved'=>'primary','rejected'=>'danger','expired'=>'warning','converted'=>'dark'];
                                $qLabels = ['draft'=>'Nháp','pending'=>'Chờ duyệt','approved'=>'Đã duyệt','rejected'=>'Từ chối','expired'=>'Hết hạn','converted'=>'Đã tạo ĐH'];
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
                                                    <td><a href="<?= url('quotations/' . $q['id']) ?>"><?= e($q['quote_number']) ?></a></td>
                                                    <td><?= format_money($q['total']) ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= $qColors[$q['status']] ?? 'secondary' ?>"><?= $qLabels[$q['status']] ?? e($q['status']) ?></span>
                                                    </td>
                                                    <td><?= user_avatar($q['owner_name'] ?? null) ?></td>
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
                                    <a href="<?= url('contracts/create?contact_id=' . $contact['id']) ?>" class="btn btn-soft-primary"><i class="ri-add-line me-1"></i>Tạo hợp đồng</a>
                                </div>
                                <?php
                                $contracts = \Core\Database::fetchAll(
                                    "SELECT c.*, u.name as owner_name FROM contracts c LEFT JOIN users u ON c.owner_id = u.id WHERE c.contact_id = ? ORDER BY c.created_at DESC LIMIT 20",
                                    [$contact['id']]
                                );
                                $ctColors = ['draft'=>'secondary','pending'=>'warning','active'=>'primary','signed'=>'info','executing'=>'primary','completed'=>'success','cancelled'=>'danger','terminated'=>'danger'];
                                $ctLabels = ['draft'=>'Nháp','pending'=>'Chờ duyệt','active'=>'Đang hiệu lực','signed'=>'Đã ký','executing'=>'Đang thực hiện','completed'=>'Hoàn thành','cancelled'=>'Đã hủy','terminated'=>'Chấm dứt'];
                                ?>
                                <?php if (!empty($contracts)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Mã HĐ</th>
                                                    <th>Giá trị</th>
                                                    <th>Trạng thái</th>
                                                    <th>Đã thanh toán</th>
                                                    <th>Người tạo</th>
                                                    <th>Ngày</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($contracts as $ct): ?>
                                                <tr>
                                                    <td><a href="<?= url('contracts/' . $ct['id']) ?>"><?= e($ct['contract_number']) ?></a></td>
                                                    <td class="fw-medium"><?= format_money($ct['value']) ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= $ctColors[$ct['status']] ?? 'secondary' ?>-subtle text-<?= $ctColors[$ct['status']] ?? 'secondary' ?>"><?= $ctLabels[$ct['status']] ?? e($ct['status']) ?></span>
                                                    </td>
                                                    <td class="text-muted"><?= format_money($ct['paid_amount'] ?? 0) ?></td>
                                                    <td><?= user_avatar($ct['owner_name'] ?? null) ?></td>
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
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h6 class="mb-0">Lịch sử email</h6>
                                    <?php if (!empty($contact['email']) && plugin_active('email')): ?>
                                    <a href="<?= url('email/compose?to=' . urlencode($contact['email'])) ?>" class="btn btn-soft-primary"><i class="ri-mail-send-line me-1"></i> Gửi email</a>
                                    <?php endif; ?>
                                </div>
                                <?php
                                $contactEmail = trim($contact['email'] ?? '');
                                $contactEmails = [];
                                try {
                                    if ($contactEmail !== '') {
                                        $contactEmails = \Core\Database::fetchAll(
                                            "SELECT * FROM email_messages WHERE tenant_id = ? AND (from_email = ? OR to_emails LIKE ? OR contact_id = ?) ORDER BY sent_at DESC LIMIT 20",
                                            [$_SESSION['tenant_id'] ?? 1, $contactEmail, '%' . $contactEmail . '%', $contact['id']]
                                        );
                                    } else {
                                        $contactEmails = \Core\Database::fetchAll(
                                            "SELECT * FROM email_messages WHERE tenant_id = ? AND contact_id = ? ORDER BY sent_at DESC LIMIT 20",
                                            [$_SESSION['tenant_id'] ?? 1, $contact['id']]
                                        );
                                    }
                                } catch (\Exception $e) {}
                                ?>
                                <?php if (!empty($contactEmails)): ?>
                                    <?php foreach ($contactEmails as $em): ?>
                                        <a href="<?= url('email/' . $em['id']) ?>" class="d-flex mb-2 p-3 border rounded text-decoration-none text-body">
                                            <div class="avatar-xs flex-shrink-0 me-3">
                                                <span class="avatar-title bg-<?= $em['folder'] === 'sent' ? 'success' : 'info' ?>-subtle text-<?= $em['folder'] === 'sent' ? 'success' : 'info' ?> rounded-circle">
                                                    <i class="ri-<?= $em['folder'] === 'sent' ? 'send-plane' : 'inbox' ?>-line"></i>
                                                </span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center">
                                                    <h6 class="mb-0 flex-grow-1"><?= e($em['subject'] ?: '(Không tiêu đề)') ?></h6>
                                                    <small class="text-muted"><?= $em['sent_at'] ? created_ago($em['sent_at']) : '' ?></small>
                                                </div>
                                                <div class="text-muted fs-12 mt-1">
                                                    <?php if ($em['folder'] === 'sent'): ?>
                                                    <i class="ri-arrow-right-line me-1"></i>Đến: <?= e($em['to_emails']) ?>
                                                    <?php else: ?>
                                                    <i class="ri-arrow-left-line me-1"></i>Từ: <?= e($em['from_name'] ?: $em['from_email']) ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="ri-mail-line fs-36 text-muted"></i>
                                        <p class="text-muted mt-2">Chưa có email nào với khách hàng này</p>
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
                                                $tLabels = ['open'=>'Mở','in_progress'=>'Đang xử lý','waiting'=>'Chờ phản hồi','resolved'=>'Đã xử lý','closed'=>'Đã đóng'];
                                                $pColors = ['low'=>'info','medium'=>'warning','high'=>'danger','urgent'=>'danger'];
                                                $pLabels = ['low'=>'Thấp','medium'=>'Bình thường','high'=>'Cao','urgent'=>'Khẩn'];
                                                ?>
                                                <?php foreach ($tickets as $ticket): ?>
                                                    <tr>
                                                        <td><a href="<?= url('tickets/' . $ticket['id']) ?>"><?= e($ticket['ticket_code']) ?></a></td>
                                                        <td><?= e($ticket['title']) ?></td>
                                                        <td><span class="badge bg-<?= $tColors[$ticket['status']] ?? 'secondary' ?>-subtle text-<?= $tColors[$ticket['status']] ?? 'secondary' ?>"><?= $tLabels[$ticket['status']] ?? e($ticket['status']) ?></span></td>
                                                        <td><span class="badge bg-<?= $pColors[$ticket['priority']] ?? 'secondary' ?>"><?= $pLabels[$ticket['priority']] ?? e($ticket['priority']) ?></span></td>
                                                        <td><?= user_avatar($ticket['assigned_name'] ?? null) ?></td>
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
                                            <tr><th class="text-muted">Người phụ trách</th><td><?= user_avatar($contact['owner_name'] ?? null) ?></td></tr>
                                            <tr><th class="text-muted">Ngày tạo</th><td><?= format_datetime($contact['created_at']) ?></td></tr>
                                            <tr><th class="text-muted">Cập nhật</th><td><?= format_datetime($contact['updated_at']) ?></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Tab: KH phản hồi -->
                            <!-- Tab: Công nợ -->
                            <div class="tab-pane" id="tab-debt" role="tabpanel">
                                <h6 class="mb-3">Công nợ</h6>
                                <?php
                                $debtOrders = \Core\Database::fetchAll(
                                    "SELECT id, order_number, total, paid_amount, (total - paid_amount) as debt, payment_status, due_date, status, created_at
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
/*
    if (form && feed) {
        feed.parentNode.insertBefore(feed, form);
        form.style.display = '';
    }
})();

// Attachment preview (multiple files)
function previewAttach(input) {
    var badge = document.getElementById('attachBadge');
    if (!input.files || !input.files.length) { badge.style.display = 'none'; return; }
    var icons = {pdf:'ri-file-pdf-line text-danger',doc:'ri-file-word-line text-primary',docx:'ri-file-word-line text-primary',xls:'ri-file-excel-line text-success',xlsx:'ri-file-excel-line text-success',dwg:'ri-draft-line text-dark',dxf:'ri-draft-line text-dark',cad:'ri-draft-line text-dark',skp:'ri-shape-line text-info',stl:'ri-shape-line text-info'};
    var html = '<div class="d-flex flex-wrap gap-2">';
    Array.from(input.files).forEach(function(file, i) {
        var size = file.size > 1048576 ? (file.size/1048576).toFixed(1) + 'MB' : Math.round(file.size/1024) + 'KB';
        var ext = file.name.split('.').pop().toLowerCase();
        var isImg = file.type.startsWith('image/');
        var shortName = file.name.length > 20 ? file.name.substring(0, 17) + '...' + ext : file.name;
        var xBtn = '<span class="position-absolute top-0 end-0 bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width:18px;height:18px;cursor:pointer;font-size:10px;transform:translate(5px,-5px)" onclick="removeAttachFile(' + i + ')"><i class="ri-close-line"></i></span>';
        if (isImg) {
            html += '<div class="position-relative" style="width:80px;height:80px;margin:5px">';
            html += '<img src="" class="attach-thumb rounded border" data-idx="' + i + '" style="width:100%;height:100%;object-fit:cover">';
            html += xBtn + '</div>';
        } else {
            html += '<div class="border rounded p-2 d-flex align-items-center gap-2 position-relative" style="max-width:180px">';
            html += '<i class="' + (icons[ext] || 'ri-file-line text-muted') + ' fs-20"></i>';
            html += '<div style="min-width:0"><div class="text-truncate" style="font-size:12px;max-width:120px" title="' + file.name + '">' + shortName + '</div><small class="text-muted">' + size + '</small></div>';
            html += xBtn + '</div>';
        }
    });
    html += '</div>';
    if (input.files.length > 1) {
        html += '<div class="mt-1"><button type="button" class="btn btn-link text-danger p-0" onclick="clearAttach()" style="font-size:12px"><i class="ri-close-line me-1"></i>Xóa tất cả (' + input.files.length + ')</button></div>';
    }
    badge.innerHTML = html;
    badge.style.display = 'block';
    // Load image thumbnails
    Array.from(input.files).forEach(function(file, i) {
        if (!file.type.startsWith('image/')) return;
        var reader = new FileReader();
        reader.onload = function(e) {
            var img = badge.querySelector('.attach-thumb[data-idx="' + i + '"]');
            if (img) img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
}
function clearAttach() {
    var input = document.querySelector('input[name="attachments[]"]');
    input.value = '';
    input._filteredFiles = null;
    document.getElementById('attachBadge').style.display = 'none';
}
function removeAttachFile(idx) {
    var input = document.querySelector('input[name="attachments[]"]');
    var dt = new DataTransfer();
    var files = input._filteredFiles || input.files;
    for (var i = 0; i < files.length; i++) {
        if (i !== idx) dt.items.add(files[i]);
    }
    input.files = dt.files;
    input._filteredFiles = dt.files;
    if (dt.files.length === 0) {
        clearAttach();
    } else {
        previewAttach(input);
    }
}

// React & Reply
function reactActivity(id, type, el) {
    fetch('<?= url("activities") ?>/' + id + '/react', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'<?= csrf_token() ?>'},
        body: JSON.stringify({type: type})
    }).then(r => r.json()).then(function(data) {
        if (!data.likes && data.likes !== 0) return;
        var row = el.closest('.d-flex.align-items-center.gap-3');
        var btns = row.querySelectorAll('.act-btn');
        // Rebuild like button
        btns[0].className = 'act-btn ' + (data.my === 'like' ? 'text-primary fw-medium' : 'text-muted');
        btns[0].innerHTML = '<i class="ri-thumb-up-' + (data.my === 'like' ? 'fill' : 'line') + '"></i>' + (data.likes > 0 ? ' <span class="react-count">' + data.likes + '</span>' : '');
        // Rebuild dislike button
        btns[1].className = 'act-btn ' + (data.my === 'dislike' ? 'text-danger fw-medium' : 'text-muted');
        btns[1].innerHTML = '<i class="ri-thumb-down-' + (data.my === 'dislike' ? 'fill' : 'line') + '"></i>' + (data.dislikes > 0 ? ' <span class="react-count">' + data.dislikes + '</span>' : '');
    });
}

function toggleReplyBox(id) {
    var box = document.getElementById('replyBox-' + id);
    box.classList.toggle('d-none');
    if (!box.classList.contains('d-none')) document.getElementById('replyInput-' + id).focus();
}

function submitReply(id) {
    var input = document.getElementById('replyInput-' + id);
    var fileInput = document.getElementById('replyFile-' + id);
    var content = input.value.trim();
    if (!content && (!fileInput || !fileInput.files[0])) return;
    var fd = new FormData();
    fd.append('content', content);
    fd.append('_token', '<?= csrf_token() ?>');
    if (fileInput && fileInput.files[0]) fd.append('attachment', fileInput.files[0]);
    fetch('<?= url("activities") ?>/' + id + '/reply', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN':'<?= csrf_token() ?>'},
        body: fd
    }).then(r => r.json()).then(function(data) {
        if (!data.success) return;
        var r = data.reply;
        var initial = (r.user_name||'?').charAt(0).toUpperCase();
        var avatar = r.user_avatar ? '<img loading="lazy" src="/' + r.user_avatar + '" class="rounded-circle" width="28" height="28" style="object-fit:cover">' : '<div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width:28px;height:28px;font-size:11px">' + initial + '</div>';
        var actions = '<div class="d-flex align-items-center gap-3 mt-1" style="font-size:12px">'
            + '<span class="act-btn text-muted" style="cursor:pointer" onclick="reactActivity(' + r.id + ',\'like\',this)"><i class="ri-thumb-up-line"></i></span>'
            + '<span class="act-btn text-muted" style="cursor:pointer" onclick="reactActivity(' + r.id + ',\'dislike\',this)"><i class="ri-thumb-down-line"></i></span>'
            + '<span class="text-muted act-btn" style="cursor:pointer" onclick="toggleReplyBox(' + id + ')"><i class="ri-reply-line"></i> Trả lời</span>'
            + '</div>';
        var attachHtml = '';
        if (r.attachment) {
            var aExt = r.attachment.split('.').pop().toLowerCase();
            var isImg = ['jpg','jpeg','png','gif','webp'].indexOf(aExt) !== -1;
            var aName = r.attachment_name || r.attachment.split('/').pop();
            if (isImg) {
                attachHtml = '<div class="mt-1"><a href="/' + r.attachment + '" target="_blank"><img src="/' + r.attachment + '" class="rounded border" style="max-width:200px;max-height:120px"></a></div>';
            } else {
                attachHtml = '<div class="mt-1"><a href="/' + r.attachment + '" target="_blank" class="text-primary" style="font-size:12px"><i class="ri-file-line me-1"></i>' + aName + '</a></div>';
            }
        }
        var html = '<div class="d-flex gap-2 py-2" style="border-bottom:1px solid #f8f8f8">' + avatar + '<div class="flex-grow-1"><strong style="font-size:13px">' + r.user_name + '</strong> <small class="text-muted">vừa xong</small><div style="font-size:13px">' + (r.title||'') + '</div>' + attachHtml + actions + '</div></div>';
        var box = document.getElementById('replyBox-' + id);
        // Reset inputs
        if (fileInput) { fileInput.value = ''; fileInput.closest('label').classList.remove('btn-soft-primary'); fileInput.closest('label').classList.add('btn-soft-secondary'); }
        var repliesDiv = box.previousElementSibling;
        if (!repliesDiv || !repliesDiv.classList.contains('border-start')) {
            var newDiv = document.createElement('div');
            newDiv.className = 'ms-4 mt-2 border-start ps-3';
            box.parentNode.insertBefore(newDiv, box);
            repliesDiv = newDiv;
        }
        repliesDiv.insertAdjacentHTML('beforeend', html);
        input.value = '';
    });
}

// @mention autocomplete (works on any input/textarea)
(function(){
    var users = <?= json_encode(array_map(function($u){ return ['id'=>$u['id'],'name'=>$u['name'],'avatar'=>$u['avatar']??null,'dept'=>$u['dept_name']??'']; }, $allUsers ?? [])) ?>;
    // Create dropdown in body for correct positioning
    var dd = document.createElement('div');
    dd.className = 'border rounded bg-white shadow';
    dd.style.cssText = 'position:fixed;z-index:1070;display:none;max-height:350px;overflow-y:auto;width:280px';
    document.body.appendChild(dd);
    var activeInput = null;

    function updateDropdownPos() {
        if (!activeInput || dd.style.display === 'none') return;
        var rect = activeInput.getBoundingClientRect();
        var spaceBelow = window.innerHeight - rect.bottom;
        if (spaceBelow > 200) {
            dd.style.top = rect.bottom + 4 + 'px';
            dd.style.bottom = 'auto';
        } else {
            dd.style.bottom = (window.innerHeight - rect.top + 4) + 'px';
            dd.style.top = 'auto';
        }
        dd.style.left = rect.left + 'px';
    }
    window.addEventListener('scroll', updateDropdownPos, true);

    function showMention(input, query) {
        activeInput = input;
        var q = (query || '').toLowerCase();
        var words = q.split(/\s+/);
        var filtered = users.filter(function(u) {
            var name = u.name.toLowerCase();
            return !q || words.every(function(w) { return name.indexOf(w) !== -1; });
        });

        if (!filtered.length) { dd.style.display = 'none'; return; }

        // Group by department
        var depts = {};
        filtered.forEach(function(u) {
            var d = u.dept || 'Khác';
            if (!depts[d]) depts[d] = [];
            depts[d].push(u);
        });

        var html = '';
        Object.keys(depts).forEach(function(dept) {
            html += '<div class="px-3 py-1 bg-light border-bottom d-flex justify-content-between"><small class="text-muted text-uppercase fw-medium">' + dept + '</small><small class="text-muted">' + depts[dept].length + '</small></div>';
            depts[dept].forEach(function(u) {
                var initial = u.name.charAt(0).toUpperCase();
                var av = u.avatar ? '<img loading="lazy" src="/' + u.avatar + '" class="rounded-circle" width="28" height="28" style="object-fit:cover">' : '<span class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:28px;height:28px;font-size:12px">' + initial + '</span>';
                html += '<div class="mention-opt d-flex align-items-center gap-2 px-3 py-2" style="cursor:pointer" data-id="' + u.id + '" data-name="' + u.name + '">' + av + '<span style="font-size:13px">' + u.name + '</span></div>';
            });
        });
        dd.innerHTML = html;

        dd.style.display = 'block';
        updateDropdownPos();

        dd.querySelectorAll('.mention-opt').forEach(function(opt) {
            opt.onmouseenter = function() { this.style.backgroundColor = '#f3f6f9'; };
            opt.onmouseleave = function() { this.style.backgroundColor = ''; };
            opt.onmousedown = function(e) {
                e.preventDefault();
                var name = this.dataset.name;
                var val = input.value;
                var cursor = input.selectionStart;
                var before = val.substring(0, cursor).replace(/@[^@]*$/, '@' + name + ' ');
                input.value = before + val.substring(cursor);
                input.selectionStart = input.selectionEnd = before.length;
                input.focus();
                dd.style.display = 'none';
                // Track tagged user IDs
                var tagInput = document.getElementById('taggedUsers');
                if (tagInput) {
                    var ids = tagInput.value ? tagInput.value.split(',') : [];
                    if (ids.indexOf(this.dataset.id) === -1) ids.push(this.dataset.id);
                    tagInput.value = ids.join(',');
                }
            };
        });
    }

    function checkMention(e) {
        var input = e.target;
        var val = input.value;
        var cursor = input.selectionStart;
        var before = val.substring(0, cursor);
        var match = before.match(/@([^@]*)$/);
        if (match) {
            showMention(input, match[1]);
        } else {
            dd.style.display = 'none';
        }
    }

    // Attach to main textarea
    var mainTa = document.getElementById('activityTextarea');
    if (mainTa) {
        mainTa.addEventListener('input', checkMention);
        mainTa.addEventListener('keydown', function(e) { if (e.key === 'Escape') dd.style.display = 'none'; });
    }

    // Attach to all reply inputs (delegate)
    document.addEventListener('input', function(e) {
        if (e.target.id && e.target.id.startsWith('replyInput-')) checkMention(e);
    });

    document.addEventListener('click', function(e) {
        if (!dd.contains(e.target) && e.target !== activeInput) dd.style.display = 'none';
    });
})();

document.getElementById('btnCheckin')?.addEventListener('click', function() {
    var btn = this;
    if (!navigator.geolocation) { alert('Trình duyệt không hỗ trợ GPS'); return; }
    btn.disabled = true;
    btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Đang lấy...';
    navigator.geolocation.getCurrentPosition(function(pos) {
        document.getElementById('checkinLat').value = pos.coords.latitude;
        document.getElementById('checkinLng').value = pos.coords.longitude;
        btn.innerHTML = '<i class="ri-map-pin-fill"></i> Đã check-in';
        btn.classList.remove('btn-soft-secondary');
        btn.classList.add('btn-soft-success');
        btn.disabled = false;
    }, function() {
        btn.innerHTML = '<i class="ri-map-pin-line"></i> Check-in';
        btn.disabled = false;
        alert('Không thể lấy vị trí. Vui lòng cho phép truy cập GPS.');
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

function uploadContactAvatar(input) {
    if (!input.files[0]) return;
    var fd = new FormData();
    fd.append('avatar', input.files[0]);
    fd.append('_token', '<?= csrf_token() ?>');
    fetch('<?= url("contacts/" . $contact["id"] . "/avatar") ?>', {method:'POST', body:fd})
        .then(function(r){return r.json()})
        .then(function(d){
            if (d.success) {
                var el = document.getElementById('contactAvatar');
                if (el.tagName === 'IMG') { el.src = d.url; }
                else { el.outerHTML = '<img src="' + d.url + '" class="rounded-circle object-fit-cover" style="width:80px;height:80px" id="contactAvatar">'; }
            }
        });
}
*/
</script>

