<?php $pageTitle = 'Pipeline'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Pipeline bán hàng</h4>
            <div>
                <a href="<?= url('deals') ?>" class="btn btn-soft-secondary me-1"><i class="ri-list-check me-1"></i> Danh sách</a>
                <a href="<?= url('deals/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Thêm cơ hội</a>
            </div>
        </div>

        <!-- Toast thông báo -->
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
            <div id="pipeline-toast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="ri-check-line me-1"></i> <span id="pipeline-toast-msg">Đã cập nhật thành công!</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>

        <div class="pipeline-board">
            <div class="d-flex flex-nowrap overflow-auto pb-3" style="gap: 16px;" id="pipeline-container">
                <?php foreach ($pipeline ?? [] as $stage): ?>
                    <div class="pipeline-column flex-shrink-0" style="min-width: 280px; max-width: 320px;" data-stage-id="<?= $stage['id'] ?>">
                        <div class="card mb-0 h-100">
                            <div class="card-header d-flex align-items-center" style="border-top: 3px solid <?= $stage['color'] ?>">
                                <h6 class="card-title mb-0 flex-grow-1">
                                    <?= e($stage['name']) ?>
                                    <span class="badge bg-secondary-subtle text-secondary ms-1 stage-count"><?= count($stage['deals'] ?? []) ?></span>
                                </h6>
                                <span class="text-muted fs-12 stage-total"><?= format_money(array_sum(array_column($stage['deals'] ?? [], 'value'))) ?></span>
                            </div>
                            <div class="card-body pipeline-cards" data-stage-id="<?= $stage['id'] ?>" style="min-height: 200px; max-height: 70vh; overflow-y: auto;">
                                <?php if (!empty($stage['deals'])): ?>
                                    <?php foreach ($stage['deals'] as $deal): ?>
                                        <div class="card border shadow-none mb-2 deal-card" data-deal-id="<?= $deal['id'] ?>" data-deal-value="<?= $deal['value'] ?>">
                                            <div class="card-body p-3">
                                                <a href="<?= url('deals/' . $deal['id']) ?>" class="fw-medium text-dark d-block mb-1">
                                                    <?= e($deal['title']) ?>
                                                </a>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="fw-semibold text-primary"><?= format_money($deal['value']) ?></span>
                                                    <?php $pc = ['low'=>'info','medium'=>'warning','high'=>'danger','urgent'=>'danger']; ?>
                                                    <span class="badge bg-<?= $pc[$deal['priority']] ?? 'secondary' ?>-subtle text-<?= $pc[$deal['priority']] ?? 'secondary' ?>">
                                                        <?= $deal['priority'] ?>
                                                    </span>
                                                </div>
                                                <?php if (!empty($deal['company_name'])): ?>
                                                    <p class="text-muted mb-1 fs-12"><i class="ri-building-line me-1"></i><?= e($deal['company_name']) ?></p>
                                                <?php endif; ?>
                                                <?php if (!empty($deal['contact_first_name'])): ?>
                                                    <p class="text-muted mb-1 fs-12"><i class="ri-user-line me-1"></i><?= e($deal['contact_first_name'] . ' ' . ($deal['contact_last_name'] ?? '')) ?></p>
                                                <?php endif; ?>
                                                <?php if (!empty($deal['owner_name'])): ?>
                                                    <div class="d-flex align-items-center mt-2">
                                                        <div class="avatar-xs">
                                                            <div class="avatar-title rounded-circle bg-primary-subtle text-primary fs-10">
                                                                <?= strtoupper(substr($deal['owner_name'], 0, 1)) ?>
                                                            </div>
                                                        </div>
                                                        <span class="ms-2 text-muted fs-12"><?= e($deal['owner_name']) ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center text-muted py-4 pipeline-empty">
                                        <i class="ri-inbox-line fs-3 d-block mb-1"></i>
                                        <small>Kéo thả cơ hội vào đây</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>


<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var csrfToken = '<?= csrf_token() ?>';
    var baseUrl = '<?= url('deals') ?>';
    var toastEl = document.getElementById('pipeline-toast');
    var toastMsg = document.getElementById('pipeline-toast-msg');
    var bsToast = new bootstrap.Toast(toastEl, { delay: 2500 });

    function showToast(message, success) {
        toastEl.classList.remove('text-bg-success', 'text-bg-danger');
        toastEl.classList.add(success ? 'text-bg-success' : 'text-bg-danger');
        toastMsg.textContent = message;
        bsToast.show();
    }

    function updateStageHeader(columnEl) {
        var stageCol = columnEl.closest('.pipeline-column');
        if (!stageCol) return;
        var cards = columnEl.querySelectorAll('.deal-card');
        var countBadge = stageCol.querySelector('.stage-count');
        var totalSpan = stageCol.querySelector('.stage-total');
        if (countBadge) countBadge.textContent = cards.length;
        if (totalSpan) {
            var total = 0;
            cards.forEach(function(c) { total += parseFloat(c.dataset.dealValue) || 0; });
            totalSpan.textContent = formatMoney(total);
        }
        // Toggle empty placeholder
        var emptyDiv = columnEl.querySelector('.pipeline-empty');
        if (cards.length === 0 && !emptyDiv) {
            var placeholder = document.createElement('div');
            placeholder.className = 'text-center text-muted py-4 pipeline-empty';
            placeholder.innerHTML = '<i class="ri-inbox-line fs-3 d-block mb-1"></i><small>Kéo thả cơ hội vào đây</small>';
            columnEl.appendChild(placeholder);
        } else if (cards.length > 0 && emptyDiv) {
            emptyDiv.remove();
        }
    }

    // Initialize SortableJS on each column
    document.querySelectorAll('.pipeline-cards').forEach(function(column) {
        new Sortable(column, {
            group: 'pipeline',
            animation: 200,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            draggable: '.deal-card',
            filter: '.pipeline-empty',
            onStart: function() {
                document.querySelectorAll('.pipeline-cards').forEach(function(c) {
                    c.classList.add('drag-highlight');
                });
            },
            onEnd: function(evt) {
                document.querySelectorAll('.pipeline-cards').forEach(function(c) {
                    c.classList.remove('drag-highlight');
                });

                var dealId = evt.item.dataset.dealId;
                var newStageId = evt.to.dataset.stageId;
                var fromColumn = evt.from;
                var toColumn = evt.to;

                // Update headers for both source and target
                updateStageHeader(fromColumn);
                updateStageHeader(toColumn);

                // POST stage change
                fetch(baseUrl + '/' + dealId + '/stage', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: '_token=' + encodeURIComponent(csrfToken) + '&stage_id=' + encodeURIComponent(newStageId)
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        showToast('Đã chuyển giai đoạn thành công!', true);
                    } else {
                        showToast(data.message || 'Lỗi cập nhật!', false);
                        // Revert: move card back
                        fromColumn.appendChild(evt.item);
                        updateStageHeader(fromColumn);
                        updateStageHeader(toColumn);
                    }
                })
                .catch(function() {
                    showToast('Lỗi kết nối, vui lòng thử lại!', false);
                    fromColumn.appendChild(evt.item);
                    updateStageHeader(fromColumn);
                    updateStageHeader(toColumn);
                });
            }
        });
    });
});
</script>
