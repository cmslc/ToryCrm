<?php
$pageTitle = e($task['title']);
$sc = ['todo'=>'secondary','in_progress'=>'primary','review'=>'warning','done'=>'success'];
$sl = ['todo'=>'Cần làm','in_progress'=>'Đang làm','review'=>'Review','done'=>'Hoàn thành'];
$pc = ['low'=>'info','medium'=>'warning','high'=>'danger','urgent'=>'danger'];
$pl = ['low'=>'Thấp','medium'=>'TB','high'=>'Cao','urgent'=>'Khẩn'];
$csrfToken = csrf_token();

function formatDuration($s) {
    $h = floor($s / 3600); $m = floor(($s % 3600) / 60);
    return ($h ? $h . 'h ' : '') . $m . 'p';
}
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0"><?= e($task['title']) ?></h4>
    <ol class="breadcrumb m-0"><li class="breadcrumb-item"><a href="<?= url('tasks') ?>">Công việc</a></li><li class="breadcrumb-item active">Chi tiết</li></ol>
</div>

<div class="row">
    <div class="col-xl-8">
        <!-- Main Info -->
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1"><?= e($task['title']) ?></h5>
                <div class="d-flex gap-1 flex-wrap">
                    <a href="<?= url('tasks/' . $task['id'] . '/edit') ?>" class="btn btn-soft-primary"><i class="ri-pencil-line me-1"></i>Sửa</a>
                    <?php if ($task['status'] !== 'done'): ?>
                        <form method="POST" action="<?= url('tasks/' . $task['id'] . '/complete') ?>" data-confirm="Hoàn thành?"><?= csrf_field() ?><button class="btn btn-soft-success"><i class="ri-check-line me-1"></i>Hoàn thành</button></form>
                    <?php endif; ?>
                    <form method="POST" action="<?= url('tasks/' . $task['id'] . '/delete') ?>" data-confirm="Xóa?"><?= csrf_field() ?><button class="btn btn-soft-danger"><i class="ri-delete-bin-line me-1"></i>Xóa</button></form>
                </div>
            </div>
            <div class="card-body">
                <?php if ($task['description']): ?>
                    <p><?= nl2br(e($task['description'])) ?></p>
                <?php else: ?>
                    <p class="text-muted">Không có mô tả</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Subtasks -->
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h6 class="card-title mb-0 flex-grow-1"><i class="ri-list-check me-1"></i> Công việc con
                    <span class="badge bg-secondary-subtle text-secondary ms-1" id="subtaskCount"><?= count($subtasks) ?></span>
                </h6>
            </div>
            <div class="card-body">
                <?php
                $doneCount = count(array_filter($subtasks, fn($s) => $s['status'] === 'done'));
                $totalSub = count($subtasks);
                $pct = $totalSub > 0 ? round($doneCount / $totalSub * 100) : 0;
                ?>
                <?php if ($totalSub > 0): ?>
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="progress flex-grow-1" style="height:6px"><div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div></div>
                    <small class="text-muted" id="subtaskProgress"><?= $doneCount ?>/<?= $totalSub ?></small>
                </div>
                <?php endif; ?>

                <div id="subtaskList">
                    <?php foreach ($subtasks as $sub): ?>
                    <div class="d-flex align-items-center gap-2 py-2 border-bottom subtask-row" data-id="<?= $sub['id'] ?>">
                        <input type="checkbox" class="form-check-input subtask-check" data-id="<?= $sub['id'] ?>" <?= $sub['status'] === 'done' ? 'checked' : '' ?>>
                        <span class="flex-grow-1 <?= $sub['status'] === 'done' ? 'text-decoration-line-through text-muted' : '' ?>"><?= e($sub['title']) ?></span>
                        <small class="text-muted"><?= e($sub['assigned_name'] ?? '') ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-3">
                    <div class="input-group">
                        <input type="text" class="form-control" id="newSubtask" placeholder="Thêm công việc con...">
                        <button class="btn btn-soft-primary" id="addSubtaskBtn"><i class="ri-add-line me-1"></i> Thêm</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comments -->
        <div class="card">
            <div class="card-header"><h6 class="card-title mb-0"><i class="ri-chat-3-line me-1"></i> Bình luận <span class="badge bg-secondary-subtle text-secondary ms-1"><?= count($comments) ?></span></h6></div>
            <div class="card-body">
                <div id="commentList">
                    <?php foreach ($comments as $c): ?>
                    <div class="d-flex gap-3 mb-3 comment-item" data-id="<?= $c['id'] ?>">
                        <div class="avatar-xs flex-shrink-0"><div class="avatar-title rounded-circle bg-primary-subtle text-primary"><?= mb_strtoupper(mb_substr($c['user_name'] ?? '', 0, 1)) ?></div></div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <strong class="fs-13"><?= e($c['user_name']) ?></strong>
                                <small class="text-muted"><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></small>
                                <?php if ($c['user_id'] == ($_SESSION['user']['id'] ?? 0)): ?>
                                <button class="btn btn-link text-danger p-0 ms-auto delete-comment" data-id="<?= $c['id'] ?>"><i class="ri-delete-bin-line"></i></button>
                                <?php endif; ?>
                            </div>
                            <p class="mb-0"><?= nl2br(e($c['content'])) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($comments)): ?>
                    <p class="text-muted text-center mb-0" id="noComments">Chưa có bình luận</p>
                    <?php endif; ?>
                </div>
                <div class="mt-3 border-top pt-3">
                    <div class="d-flex gap-2">
                        <textarea id="commentInput" class="form-control" rows="2" placeholder="Viết bình luận..."></textarea>
                        <button class="btn btn-primary align-self-end" id="addCommentBtn"><i class="ri-send-plane-fill"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attachments -->
        <div class="card">
            <div class="card-header"><h6 class="card-title mb-0"><i class="ri-attachment-2 me-1"></i> Đính kèm <span class="badge bg-secondary-subtle text-secondary ms-1"><?= count($attachments) ?></span></h6></div>
            <div class="card-body">
                <div id="attachmentList" class="d-flex flex-wrap gap-2 mb-3">
                    <?php foreach ($attachments as $att): ?>
                    <div class="border rounded p-2 d-flex align-items-center gap-2 att-item" data-id="<?= $att['id'] ?>">
                        <i class="ri-file-line text-primary fs-20"></i>
                        <div>
                            <a href="<?= url('uploads/tasks/' . $att['filename']) ?>" target="_blank" class="fw-medium fs-13"><?= e($att['original_name']) ?></a>
                            <div class="text-muted fs-11"><?= number_format($att['file_size'] / 1024, 1) ?> KB · <?= e($att['user_name'] ?? '') ?></div>
                        </div>
                        <button class="btn btn-link text-danger p-0 delete-att" data-id="<?= $att['id'] ?>"><i class="ri-close-line"></i></button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div>
                    <input type="file" id="fileInput" class="d-none" multiple>
                    <button class="btn btn-soft-primary" onclick="document.getElementById('fileInput').click()"><i class="ri-upload-2-line me-1"></i> Tải lên</button>
                    <small class="text-muted ms-2">Tối đa 10MB</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <!-- Info -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Thông tin</h5></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th class="text-muted" style="width:120px">Trạng thái</th><td><span class="badge bg-<?= $sc[$task['status']] ?? 'secondary' ?>"><?= $sl[$task['status']] ?? '' ?></span></td></tr>
                    <tr><th class="text-muted">Ưu tiên</th><td><span class="badge bg-<?= $pc[$task['priority']] ?? 'secondary' ?>-subtle text-<?= $pc[$task['priority']] ?? 'secondary' ?>"><?= $pl[$task['priority']] ?? '' ?></span></td></tr>
                    <tr><th class="text-muted">Giao cho</th><td><?= user_avatar($task['assigned_name'] ?? null) ?></td></tr>
                    <tr><th class="text-muted">Tạo bởi</th><td><?= user_avatar($task['creator_name'] ?? null, 'success') ?></td></tr>
                    <tr><th class="text-muted">Hạn</th><td><?= $task['due_date'] ? date('d/m/Y H:i', strtotime($task['due_date'])) : '-' ?></td></tr>
                    <tr><th class="text-muted">Ngày tạo</th><td><?= date('d/m/Y H:i', strtotime($task['created_at'])) ?></td></tr>
                    <?php if ($task['completed_at']): ?><tr><th class="text-muted">Hoàn thành</th><td class="text-success"><?= date('d/m/Y H:i', strtotime($task['completed_at'])) ?></td></tr><?php endif; ?>
                    <?php if ($task['contact_first_name']): ?><tr><th class="text-muted">Khách hàng</th><td><a href="<?= url('contacts/' . $task['contact_id']) ?>"><?= e($task['contact_first_name']) ?></a></td></tr><?php endif; ?>
                    <?php if ($task['deal_title']): ?><tr><th class="text-muted">Cơ hội</th><td><a href="<?= url('deals/' . $task['deal_id']) ?>"><?= e($task['deal_title']) ?></a></td></tr><?php endif; ?>
                </table>
                <form method="POST" action="<?= url('tasks/' . $task['id'] . '/status') ?>" class="mt-3">
                    <?= csrf_field() ?>
                    <div class="d-flex gap-2">
                        <select name="status" class="form-select"><?php foreach ($sl as $v => $l): ?><option value="<?= $v ?>" <?= $task['status'] === $v ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select>
                        <button class="btn btn-primary flex-shrink-0">Cập nhật</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Followers -->
        <div class="card">
            <div class="card-header"><h6 class="card-title mb-0"><i class="ri-user-follow-line me-1"></i> Người theo dõi</h6></div>
            <div class="card-body">
                <div id="followerTags" class="d-flex flex-wrap gap-1 mb-2">
                    <?php foreach ($followers ?? [] as $f):
                        $isAdmin = false;
                        foreach ($allUsers as $au) { if ($au['id'] == $f['user_id'] && $au['role'] === 'admin') { $isAdmin = true; break; } }
                    ?>
                    <span class="badge bg-primary-subtle text-primary d-flex align-items-center gap-1 follower-tag" data-uid="<?= $f['user_id'] ?>">
                        <?= e($f['name']) ?>
                        <?php if ($isAdmin): ?><i class="ri-shield-star-line" title="Admin"></i><?php endif; ?>
                        <i class="ri-close-line" style="cursor:pointer" onclick="removeFollower(<?= $f['user_id'] ?>, this.closest('.follower-tag'))"></i>
                    </span>
                    <?php endforeach; ?>
                </div>
                <div class="position-relative">
                    <input type="text" class="form-control" id="followerInput" placeholder="Gõ tên để thêm..." autocomplete="off">
                    <div id="followerDropdown" class="dropdown-menu w-100" style="display:none;max-height:200px;overflow-y:auto"></div>
                </div>
            </div>
        </div>

        <!-- Time Tracking -->
        <div class="card">
            <div class="card-header"><h6 class="card-title mb-0"><i class="ri-time-line me-1"></i> Thời gian</h6></div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <h3 class="mb-1" id="totalTimeDisplay"><?= formatDuration($totalTime) ?></h3>
                    <small class="text-muted">Tổng thời gian</small>
                </div>
                <?php if ($runningTimer): ?>
                <button class="btn btn-danger w-100 mb-3" id="stopTimerBtn" data-started="<?= $runningTimer['started_at'] ?>">
                    <i class="ri-stop-circle-line me-1"></i> Dừng bấm giờ <span id="timerDisplay" class="ms-1"></span>
                </button>
                <?php else: ?>
                <button class="btn btn-soft-success w-100 mb-3" id="startTimerBtn"><i class="ri-play-circle-line me-1"></i> Bắt đầu bấm giờ</button>
                <?php endif; ?>
                <div class="border-top pt-3">
                    <h6 class="fs-13 mb-2">Thêm thời gian thủ công</h6>
                    <div class="d-flex gap-2">
                        <input type="number" class="form-control" id="manualHours" placeholder="Giờ" min="0" style="width:70px">
                        <input type="number" class="form-control" id="manualMinutes" placeholder="Phút" min="0" max="59" style="width:70px">
                        <button class="btn btn-soft-primary flex-grow-1" id="addTimeBtn"><i class="ri-add-line"></i></button>
                    </div>
                </div>
                <?php if (!empty($timeLogs)): ?>
                <div class="border-top pt-3 mt-3" style="max-height:200px;overflow-y:auto">
                    <?php foreach (array_slice($timeLogs, 0, 10) as $tl): ?>
                    <div class="d-flex justify-content-between align-items-center py-1 fs-12">
                        <span class="text-muted"><?= e($tl['user_name']) ?> · <?= date('d/m H:i', strtotime($tl['started_at'])) ?></span>
                        <span class="fw-medium"><?= formatDuration($tl['duration']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Dependencies -->
        <div class="card">
            <div class="card-header"><h6 class="card-title mb-0"><i class="ri-links-line me-1"></i> Phụ thuộc</h6></div>
            <div class="card-body">
                <div id="depList">
                    <?php foreach ($dependencies as $dep): ?>
                    <div class="d-flex align-items-center gap-2 py-1 dep-item" data-id="<?= $dep['id'] ?>">
                        <i class="ri-checkbox-<?= $dep['dep_status'] === 'done' ? 'circle' : 'blank-circle' ?>-line text-<?= $dep['dep_status'] === 'done' ? 'success' : 'muted' ?>"></i>
                        <a href="<?= url('tasks/' . $dep['depends_on_id']) ?>" class="flex-grow-1 <?= $dep['dep_status'] === 'done' ? 'text-muted text-decoration-line-through' : '' ?>"><?= e($dep['dep_title']) ?></a>
                        <button class="btn btn-link text-danger p-0 delete-dep" data-id="<?= $dep['id'] ?>"><i class="ri-close-line"></i></button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-2">
                    <select id="depTaskSelect" class="form-select">
                        <option value="">Chọn task phụ thuộc...</option>
                        <?php foreach ($allTasks as $at): ?>
                        <option value="<?= $at['id'] ?>"><?= e($at['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-soft-primary w-100 mt-2" id="addDepBtn"><i class="ri-add-line me-1"></i> Thêm phụ thuộc</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var taskId = <?= $task['id'] ?>;
    var token = '<?= $csrfToken ?>';
    var base = '<?= url("tasks") ?>';
    var allUsers = <?= json_encode(array_map(fn($u) => ['id' => $u['id'], 'name' => $u['name'], 'role' => $u['role']], $allUsers)) ?>;
    var existingFollowers = [<?= implode(',', array_column($followers ?? [], 'user_id')) ?>];

    function post(url, data) {
        data._token = token;
        return fetch(url, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: Object.keys(data).map(k => k + '=' + encodeURIComponent(data[k])).join('&')
        }).then(r => r.json());
    }

    // Subtasks
    document.getElementById('addSubtaskBtn')?.addEventListener('click', function() {
        var input = document.getElementById('newSubtask');
        var title = input.value.trim();
        if (!title) return;
        post(base + '/' + taskId + '/subtask', {title: title}).then(function(d) {
            if (d.success) {
                var row = document.createElement('div');
                row.className = 'd-flex align-items-center gap-2 py-2 border-bottom subtask-row';
                row.innerHTML = '<input type="checkbox" class="form-check-input subtask-check" data-id="' + d.id + '"><span class="flex-grow-1">' + title + '</span>';
                document.getElementById('subtaskList').appendChild(row);
                input.value = '';
                initSubtaskChecks();
            }
        });
    });
    document.getElementById('newSubtask')?.addEventListener('keydown', function(e) { if (e.key === 'Enter') document.getElementById('addSubtaskBtn').click(); });

    function initSubtaskChecks() {
        document.querySelectorAll('.subtask-check').forEach(function(cb) {
            cb.onclick = function() {
                var id = this.dataset.id;
                var span = this.parentElement.querySelector('span');
                post(base + '/' + id + '/toggle-subtask', {}).then(function(d) {
                    if (d.success) {
                        if (d.status === 'done') { span.classList.add('text-decoration-line-through', 'text-muted'); }
                        else { span.classList.remove('text-decoration-line-through', 'text-muted'); }
                    }
                });
            };
        });
    }
    initSubtaskChecks();

    // Comments
    document.getElementById('addCommentBtn')?.addEventListener('click', function() {
        var input = document.getElementById('commentInput');
        var content = input.value.trim();
        if (!content) return;
        post(base + '/' + taskId + '/comment', {content: content}).then(function(d) {
            if (d.success) {
                document.getElementById('noComments')?.remove();
                var c = d.comment;
                var html = '<div class="d-flex gap-3 mb-3 comment-item" data-id="' + c.id + '">'
                    + '<div class="avatar-xs flex-shrink-0"><div class="avatar-title rounded-circle bg-primary-subtle text-primary">' + c.user_name.charAt(0).toUpperCase() + '</div></div>'
                    + '<div class="flex-grow-1"><div class="d-flex align-items-center gap-2 mb-1"><strong class="fs-13">' + c.user_name + '</strong><small class="text-muted">' + c.created_at + '</small></div>'
                    + '<p class="mb-0">' + content.replace(/\n/g, '<br>') + '</p></div></div>';
                document.getElementById('commentList').insertAdjacentHTML('beforeend', html);
                input.value = '';
            }
        });
    });

    document.querySelectorAll('.delete-comment').forEach(function(btn) {
        btn.onclick = function() {
            if (!confirm('Xóa bình luận?')) return;
            var id = this.dataset.id;
            post(base + '/' + taskId + '/comment/' + id + '/delete', {}).then(function(d) {
                if (d.success) document.querySelector('.comment-item[data-id="' + id + '"]')?.remove();
            });
        };
    });

    // Time Tracking
    document.getElementById('startTimerBtn')?.addEventListener('click', function() {
        var btn = this;
        post(base + '/' + taskId + '/timer/start', {}).then(function(d) {
            if (d.success) location.reload();
        });
    });

    document.getElementById('stopTimerBtn')?.addEventListener('click', function() {
        post(base + '/' + taskId + '/timer/stop', {}).then(function(d) {
            if (d.success) location.reload();
        });
    });

    // Running timer display
    var stopBtn = document.getElementById('stopTimerBtn');
    if (stopBtn) {
        var started = new Date(stopBtn.dataset.started.replace(' ', 'T'));
        setInterval(function() {
            var diff = Math.floor((Date.now() - started.getTime()) / 1000);
            var h = Math.floor(diff / 3600), m = Math.floor((diff % 3600) / 60), s = diff % 60;
            document.getElementById('timerDisplay').textContent = (h ? h + 'h ' : '') + m + 'p ' + s + 's';
        }, 1000);
    }

    document.getElementById('addTimeBtn')?.addEventListener('click', function() {
        var h = document.getElementById('manualHours').value || 0;
        var m = document.getElementById('manualMinutes').value || 0;
        if (!h && !m) return;
        post(base + '/' + taskId + '/time-log', {hours: h, minutes: m}).then(function(d) {
            if (d.success) location.reload();
        });
    });

    // File Upload
    document.getElementById('fileInput')?.addEventListener('change', function() {
        Array.from(this.files).forEach(function(file) {
            var fd = new FormData();
            fd.append('file', file);
            fd.append('_token', token);
            fetch(base + '/' + taskId + '/attachment', {method: 'POST', body: fd})
                .then(r => r.json()).then(function(d) { if (d.success) location.reload(); });
        });
    });

    document.querySelectorAll('.delete-att').forEach(function(btn) {
        btn.onclick = function() {
            if (!confirm('Xóa file?')) return;
            var id = this.dataset.id;
            post(base + '/' + taskId + '/attachment/' + id + '/delete', {}).then(function(d) {
                if (d.success) document.querySelector('.att-item[data-id="' + id + '"]')?.remove();
            });
        };
    });

    // Dependencies
    document.getElementById('addDepBtn')?.addEventListener('click', function() {
        var sel = document.getElementById('depTaskSelect');
        var depId = sel.value;
        if (!depId) return;
        post(base + '/' + taskId + '/dependency', {depends_on_id: depId}).then(function(d) {
            if (d.success) location.reload();
        });
    });

    document.querySelectorAll('.delete-dep').forEach(function(btn) {
        btn.onclick = function() {
            var id = this.dataset.id;
            post(base + '/' + taskId + '/dependency/' + id + '/delete', {}).then(function(d) {
                if (d.success) document.querySelector('.dep-item[data-id="' + id + '"]')?.remove();
            });
        };
    });

    // Followers
    var fInput = document.getElementById('followerInput');
    var fDrop = document.getElementById('followerDropdown');
    var fTags = document.getElementById('followerTags');

    if (fInput) {
        fInput.addEventListener('input', function() {
            var q = this.value.toLowerCase().trim();
            if (q.length < 1) { fDrop.style.display = 'none'; return; }
            var html = '';
            allUsers.forEach(function(u) {
                if (existingFollowers.indexOf(u.id) >= 0) return;
                if (u.name.toLowerCase().indexOf(q) >= 0) {
                    var badge = u.role === 'admin' ? ' <span class="badge bg-danger-subtle text-danger">Admin</span>' : '';
                    html += '<a href="#" class="dropdown-item" data-uid="' + u.id + '" data-name="' + u.name + '" data-role="' + u.role + '">' + u.name + badge + '</a>';
                }
            });
            fDrop.innerHTML = html || '<span class="dropdown-item text-muted">Không tìm thấy</span>';
            fDrop.style.display = 'block';
            fDrop.querySelectorAll('[data-uid]').forEach(function(a) {
                a.onclick = function(e) {
                    e.preventDefault();
                    var uid = parseInt(this.dataset.uid);
                    var name = this.dataset.name;
                    var role = this.dataset.role;
                    post(base + '/' + taskId + '/followers', {action: 'add', user_id: uid}).then(function(d) {
                        if (d.success) {
                            existingFollowers.push(uid);
                            var adminIcon = role === 'admin' ? '<i class="ri-shield-star-line" title="Admin"></i>' : '';
                            fTags.insertAdjacentHTML('beforeend',
                                '<span class="badge bg-primary-subtle text-primary d-flex align-items-center gap-1 follower-tag" data-uid="' + uid + '">'
                                + name + adminIcon
                                + '<i class="ri-close-line" style="cursor:pointer" onclick="removeFollower(' + uid + ', this.closest(\'.follower-tag\'))"></i></span>');
                            fInput.value = '';
                            fDrop.style.display = 'none';
                        }
                    });
                };
            });
        });
        fInput.addEventListener('blur', function() { setTimeout(function() { fDrop.style.display = 'none'; }, 200); });
    }

    window.removeFollower = function(uid, el) {
        post(base + '/' + taskId + '/followers', {action: 'remove', user_id: uid}).then(function(d) {
            if (d.success) {
                el?.remove();
                existingFollowers = existingFollowers.filter(function(id) { return id !== uid; });
            }
        });
    };
})();
</script>
