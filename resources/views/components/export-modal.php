<?php
/**
 * Reusable Export column picker modal.
 *
 * Required vars (set before include):
 *   $exportUrl    : base URL of export endpoint (e.g. url('orders/export'))
 *   $exportColumns: assoc array key => label (e.g. ['order_number' => 'Mã ĐH', 'total' => 'Tổng'])
 *   $exportId     : unique id for the modal (default 'exportModal')
 *   $exportFilters: (optional) assoc array of current filter query params to forward
 */
$exportId = $exportId ?? 'exportModal';
$exportFilters = $exportFilters ?? [];
?>
<div class="modal fade" id="<?= e($exportId) ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ri-download-2-line me-1"></i> Tùy chọn xuất CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-2">Chọn các cột muốn xuất. Mặc định chọn tất cả.</p>
                <div class="mb-2 d-flex gap-2">
                    <button type="button" class="btn btn-soft-secondary py-1 px-2 export-check-all">Chọn tất cả</button>
                    <button type="button" class="btn btn-soft-secondary py-1 px-2 export-check-none">Bỏ chọn</button>
                </div>
                <div class="border rounded p-2" style="max-height:320px;overflow-y:auto">
                    <?php $__i = 0; foreach ($exportColumns as $key => $label): $__i++; ?>
                    <div class="form-check">
                        <input class="form-check-input export-col-cb" type="checkbox" id="<?= e($exportId) ?>_col_<?= e($key) ?>" value="<?= e($key) ?>" checked>
                        <label class="form-check-label" for="<?= e($exportId) ?>_col_<?= e($key) ?>"><?= e($label) ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-3 small text-muted">Tổng <?= $__i ?> cột · File sẽ có BOM UTF-8 để Excel mở đúng tiếng Việt.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-soft-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary export-submit-btn"
                        data-base-url="<?= e($exportUrl) ?>"
                        data-filters='<?= e(json_encode($exportFilters, JSON_UNESCAPED_UNICODE)) ?>'>
                    <i class="ri-download-line me-1"></i> Tải CSV
                </button>
            </div>
        </div>
    </div>
</div>
<script>
(function(){
    var modal = document.getElementById('<?= e($exportId) ?>');
    if (!modal) return;
    modal.querySelector('.export-check-all').addEventListener('click', function(){
        modal.querySelectorAll('.export-col-cb').forEach(c => c.checked = true);
    });
    modal.querySelector('.export-check-none').addEventListener('click', function(){
        modal.querySelectorAll('.export-col-cb').forEach(c => c.checked = false);
    });
    modal.querySelector('.export-submit-btn').addEventListener('click', function(){
        var base = this.dataset.baseUrl;
        var filters = JSON.parse(this.dataset.filters || '{}');
        var cols = Array.from(modal.querySelectorAll('.export-col-cb:checked')).map(c => c.value);
        if (!cols.length) { alert('Hãy chọn ít nhất 1 cột.'); return; }
        var qs = new URLSearchParams();
        Object.keys(filters).forEach(k => { if (filters[k] !== '' && filters[k] !== null) qs.append(k, filters[k]); });
        qs.append('format', 'csv');
        qs.append('columns', cols.join(','));
        window.location.href = base + (base.includes('?') ? '&' : '?') + qs.toString();
        bootstrap.Modal.getInstance(modal).hide();
    });
})();
</script>
