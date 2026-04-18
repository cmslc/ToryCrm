<?php $pageTitle = 'Kiểm soát CV'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Kiểm soát công việc</h4>
            <div>
                <a href="<?= url('tasks') ?>" class="btn btn-soft-secondary me-1"><i class="ri-list-check me-1"></i> Danh sách</a>
                <a href="<?= url('tasks/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Thêm</a>
            </div>
        </div>

        <!-- Toast thông báo -->
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
            <div id="kanban-toast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="ri-check-line me-1"></i> <span id="kanban-toast-msg">Đã cập nhật thành công!</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>

        <div class="d-flex flex-nowrap overflow-auto pb-3" style="gap: 16px;" id="kanban-container">
            <?php
            $columns = [
                'todo' => ['label' => 'Cần làm', 'color' => '#405189', 'icon' => 'ri-list-check'],
                'in_progress' => ['label' => 'Đang làm', 'color' => '#f7b84b', 'icon' => 'ri-loader-4-line'],
                'review' => ['label' => 'Review', 'color' => '#299cdb', 'icon' => 'ri-eye-line'],
                'done' => ['label' => 'Hoàn thành', 'color' => '#0ab39c', 'icon' => 'ri-check-double-line'],
            ];
            foreach ($columns as $status => $info):
                $items = $board[$status] ?? [];
            ?>
                <div class="kanban-stage flex-shrink-0" style="min-width: 280px; max-width: 320px;" data-status="<?= $status ?>">
                    <div class="card mb-0 h-100">
                        <div class="card-header d-flex align-items-center" style="border-top: 3px solid <?= $info['color'] ?>">
                            <h6 class="card-title mb-0 flex-grow-1">
                                <i class="<?= $info['icon'] ?> me-1"></i>
                                <?= $info['label'] ?>
                                <span class="badge bg-secondary-subtle text-secondary ms-1 stage-count"><?= count($items) ?></span>
                            </h6>
                        </div>
                        <div class="card-body kanban-column" data-status="<?= $status ?>" style="min-height: 200px; max-height: 70vh; overflow-y: auto;">
                            <?php foreach ($items as $task): ?>
                                <?php
                                    $overdue = !empty($task['due_date']) && strtotime($task['due_date']) < time() && $status !== 'done';
                                ?>
                                <div class="card border shadow-none mb-2 task-card <?= $overdue ? 'border-danger' : '' ?>" data-task-id="<?= $task['id'] ?>" <?= $overdue ? 'style="border-color: #f06548 !important; border-width: 2px !important;"' : '' ?>>
                                    <div class="card-body p-3">
                                        <a href="<?= url('tasks/' . $task['id']) ?>" class="fw-medium text-dark d-block mb-1" ondblclick="event.preventDefault();this.style.display='none';var i=this.nextElementSibling;i.style.display='';i.focus()"><?= e($task['title']) ?></a>
                                        <input type="text" class="form-control form-control mb-1 kanban-title-edit" value="<?= e($task['title']) ?>" data-id="<?= $task['id'] ?>" style="display:none" onblur="this.style.display='none';this.previousElementSibling.style.display=''"
                                            onkeydown="if(event.key==='Enter'){var el=this;fetch('<?= url('tasks') ?>/'+el.dataset.id+'/quick-update',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'_token=<?= csrf_token() ?>&field=title&value='+encodeURIComponent(el.value)}).then(function(){el.previousElementSibling.textContent=el.value;el.blur()});}"
                                        >
                                        <div class="d-flex justify-content-between align-items-center">
                                            <?php $pc=['low'=>'info','medium'=>'warning','high'=>'danger','urgent'=>'danger']; ?>
                                            <span class="badge bg-<?= $pc[$task['priority']] ?? 'secondary' ?>-subtle text-<?= $pc[$task['priority']] ?? 'secondary' ?>"><?= $task['priority'] ?></span>
                                            <?php if (!empty($task['due_date'])): ?>
                                                <small class="<?= $overdue ? 'text-danger fw-semibold' : 'text-muted' ?>">
                                                    <i class="ri-calendar-line me-1"></i><?= format_date($task['due_date']) ?>
                                                    <?php if ($overdue): ?><i class="ri-alarm-warning-line ms-1"></i><?php endif; ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($task['assigned_name'])): ?>
                                            <div class="d-flex align-items-center mt-2">
                                                <div class="avatar-xs">
                                                    <div class="avatar-title rounded-circle bg-primary-subtle text-primary fs-10">
                                                        <?= strtoupper(substr($task['assigned_name'], 0, 1)) ?>
                                                    </div>
                                                </div>
                                                <span class="ms-2 text-muted fs-12"><?= e($task['assigned_name']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($items)): ?>
                                <div class="text-center text-muted py-4 kanban-empty">
                                    <i class="ri-inbox-line fs-3 d-block mb-1"></i>
                                    <small>Kéo thả vào đây</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>


<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var csrfToken = '<?= csrf_token() ?>';
    var baseUrl = '<?= url('tasks') ?>';
    var toastEl = document.getElementById('kanban-toast');
    var toastMsg = document.getElementById('kanban-toast-msg');
    var bsToast = new bootstrap.Toast(toastEl, { delay: 2500 });

    function showToast(message, success) {
        toastEl.classList.remove('text-bg-success', 'text-bg-danger');
        toastEl.classList.add(success ? 'text-bg-success' : 'text-bg-danger');
        toastMsg.textContent = message;
        bsToast.show();
    }

    function updateColumnHeader(columnEl) {
        var stage = columnEl.closest('.kanban-stage');
        if (!stage) return;
        var cards = columnEl.querySelectorAll('.task-card');
        var countBadge = stage.querySelector('.stage-count');
        if (countBadge) countBadge.textContent = cards.length;
        // Toggle empty placeholder
        var emptyDiv = columnEl.querySelector('.kanban-empty');
        if (cards.length === 0 && !emptyDiv) {
            var placeholder = document.createElement('div');
            placeholder.className = 'text-center text-muted py-4 kanban-empty';
            placeholder.innerHTML = '<i class="ri-inbox-line fs-3 d-block mb-1"></i><small>Kéo thả vào đây</small>';
            columnEl.appendChild(placeholder);
        } else if (cards.length > 0 && emptyDiv) {
            emptyDiv.remove();
        }
    }

    // Initialize SortableJS on each column
    document.querySelectorAll('.kanban-column').forEach(function(column) {
        new Sortable(column, {
            group: 'kanban',
            animation: 200,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            draggable: '.task-card',
            filter: '.kanban-empty',
            onStart: function() {
                document.querySelectorAll('.kanban-column').forEach(function(c) {
                    c.classList.add('drag-highlight');
                });
            },
            onEnd: function(evt) {
                document.querySelectorAll('.kanban-column').forEach(function(c) {
                    c.classList.remove('drag-highlight');
                });

                var taskId = evt.item.dataset.taskId;
                var newStatus = evt.to.dataset.status;
                var fromColumn = evt.from;
                var toColumn = evt.to;

                // Update overdue styling: remove if moved to done
                if (newStatus === 'done') {
                    evt.item.classList.remove('border-danger');
                    evt.item.style.borderColor = '';
                    evt.item.style.borderWidth = '';
                }

                // Update counts
                updateColumnHeader(fromColumn);
                updateColumnHeader(toColumn);

                // POST status change
                fetch(baseUrl + '/' + taskId + '/status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: '_token=' + encodeURIComponent(csrfToken) + '&status=' + encodeURIComponent(newStatus)
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        showToast('Đã cập nhật trạng thái thành công!', true);
                    } else {
                        showToast(data.message || 'Lỗi cập nhật!', false);
                        fromColumn.appendChild(evt.item);
                        updateColumnHeader(fromColumn);
                        updateColumnHeader(toColumn);
                    }
                })
                .catch(function() {
                    showToast('Lỗi kết nối, vui lòng thử lại!', false);
                    fromColumn.appendChild(evt.item);
                    updateColumnHeader(fromColumn);
                    updateColumnHeader(toColumn);
                });
            }
        });
    });
});
</script>
