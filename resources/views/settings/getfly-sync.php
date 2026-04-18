<?php $pageTitle = 'Đồng bộ Getfly CRM'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0"><i class="ri-refresh-line me-2"></i>Đồng bộ Getfly CRM</h4>
    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="<?= url('settings') ?>">Cài đặt</a></li>
        <li class="breadcrumb-item active">Getfly Sync</li>
    </ol>
</div>

<div class="row">
    <!-- Cấu hình API -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-key-2-line me-1"></i> Cấu hình API</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('settings/getfly-sync/save-config') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Domain Getfly <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">https://</span>
                            <input type="text" class="form-control" name="api_domain" value="<?= e(str_replace(['https://', 'http://'], '', $config['api_domain'] ?? '')) ?>" placeholder="company.getflycrm.com" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">API Key <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="api_key" value="<?= e($config['api_key'] ?? '') ?>" placeholder="30 ký tự" required>
                        <small class="text-muted">Lấy tại: Getfly > Cài đặt > Tích hợp > Getfly API Key</small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="ri-save-line me-1"></i> Lưu cấu hình</button>
                </form>

                <?php if ($connectionStatus): ?>
                <div class="mt-3 alert alert-<?= $connectionStatus['status'] === 'connected' ? 'success' : 'danger' ?> mb-0">
                    <i class="ri-<?= $connectionStatus['status'] === 'connected' ? 'check' : 'close' ?>-circle-line me-1"></i>
                    <?= e($connectionStatus['message']) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Lịch sử đồng bộ -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-history-line me-1"></i> Lịch sử đồng bộ</h5>
            </div>
            <div class="card-body p-2">
                <?php if (empty($logs)): ?>
                <div class="text-center text-muted py-4">Chưa có lịch sử</div>
                <?php else: ?>
                <div class="table-responsive" style="max-height:400px;overflow-y:auto">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Endpoint</th>
                                <th>Trạng thái</th>
                                <th>Records</th>
                                <th>Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><code class="fs-11"><?= e($log['endpoint']) ?></code></td>
                                <td>
                                    <?php
                                    $sc = ['running' => 'warning', 'success' => 'success', 'error' => 'danger'];
                                    $sl = ['running' => 'Đang chạy', 'success' => 'Thành công', 'error' => 'Lỗi'];
                                    ?>
                                    <span class="badge bg-<?= $sc[$log['status']] ?? 'secondary' ?>-subtle text-<?= $sc[$log['status']] ?? 'secondary' ?>">
                                        <?= $sl[$log['status']] ?? $log['status'] ?>
                                    </span>
                                </td>
                                <td><?= number_format($log['records_synced']) ?></td>
                                <td class="text-muted fs-12"><?= time_ago($log['started_at']) ?></td>
                            </tr>
                            <?php if ($log['error_message']): ?>
                            <tr><td colspan="4" class="text-danger fs-12 py-1"><?= e($log['error_message']) ?></td></tr>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Endpoints -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-api-line me-1"></i> Endpoints đồng bộ</h5>
            </div>
            <div class="card-body">
                <?php if (!$config): ?>
                <div class="text-center text-muted py-5">
                    <i class="ri-plug-line fs-48 d-block mb-3"></i>
                    <p>Vui lòng cấu hình API Key trước khi đồng bộ</p>
                </div>
                <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($endpoints as $key => $ep): ?>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100" id="ep-<?= $key ?>">
                            <div class="d-flex align-items-center mb-2">
                                <div class="avatar-sm me-3">
                                    <div class="avatar-title bg-<?= $ep['color'] ?>-subtle text-<?= $ep['color'] ?> rounded-circle fs-20">
                                        <i class="<?= $ep['icon'] ?>"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0"><?= e($ep['name']) ?></h6>
                                    <small class="text-muted"><?= e($ep['description']) ?></small>
                                </div>
                            </div>
                            <div class="mb-2">
                                <code class="fs-11 text-muted"><?= e($ep['method']) ?> /<?= e($ep['api_path']) ?></code>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-muted fs-12" id="ep-<?= $key ?>-status">
                                    <i class="ri-information-line me-1"></i>Chưa kiểm tra
                                </span>
                                <div class="d-flex gap-1 sync-actions" id="actions-<?= $key ?>">
                                    <button type="button" class="btn btn-soft-<?= $ep['color'] ?> btn-test-api" data-endpoint="<?= $key ?>" title="Kiểm tra kết nối">
                                        <i class="ri-link me-1"></i> Test
                                    </button>
                                    <button type="button" class="btn btn-<?= $ep['color'] ?> btn-sync-api" data-endpoint="<?= $key ?>" title="Đồng bộ dữ liệu">
                                        <i class="ri-refresh-line me-1"></i> Đồng bộ
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Modal -->
<div class="modal fade" id="confirmSyncModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="avatar-lg mx-auto mb-3">
                    <div class="avatar-title bg-info-subtle text-info rounded-circle fs-24">
                        <i class="ri-refresh-line"></i>
                    </div>
                </div>
                <h5 class="mb-2">Bắt đầu đồng bộ dữ liệu?</h5>
                <p class="text-muted mb-4" id="confirmSyncText">Quá trình có thể mất vài phút.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-soft-secondary px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary px-4" id="confirmSyncBtn"><i class="ri-refresh-line me-1"></i> Đồng bộ</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($config): ?>
<script>
var token = '<?= csrf_token() ?>';

// Test API
document.querySelectorAll('.btn-test-api').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var ep = this.dataset.endpoint;
        var statusEl = document.getElementById('ep-' + ep + '-status');
        this.disabled = true;
        this.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i> Đang test...';
        statusEl.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i>Đang kiểm tra...';
        statusEl.className = 'text-warning fs-12';

        var controller = new AbortController();
        setTimeout(function() { controller.abort(); }, 90000);
        fetch('<?= url('settings/getfly-sync/test-api') ?>?endpoint=' + ep, {
            headers: {'X-Requested-With': 'XMLHttpRequest'},
            signal: controller.signal
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                var text = d.hide_count ? '' : Number(d.total_records).toLocaleString() + ' records';
                statusEl.innerHTML = '<i class="ri-check-line me-1"></i>OK' + (text ? ' - ' + text : '') + (d.extra || '');
                statusEl.className = 'text-success fs-12 fw-medium';
            } else {
                statusEl.innerHTML = '<i class="ri-close-line me-1"></i>' + (d.error || 'Lỗi');
                statusEl.className = 'text-danger fs-12';
            }
        })
        .catch(function(e) {
            statusEl.innerHTML = '<i class="ri-close-line me-1"></i>' + (e.name === 'AbortError' ? 'Timeout - API phản hồi quá lâu' : 'Lỗi kết nối');
            statusEl.className = 'text-danger fs-12';
        })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="ri-link me-1"></i> Test';
        });
    });
});

// Sync API
var pendingSyncEp = null;
var pendingSyncBtn = null;

document.querySelectorAll('.btn-sync-api').forEach(function(btn) {
    btn.addEventListener('click', function() {
        pendingSyncEp = this.dataset.endpoint;
        pendingSyncBtn = this;
        var names = {};
        document.querySelectorAll('[data-endpoint]').forEach(function(el) {
            var card = el.closest('.border');
            if (card) { var h = card.querySelector('h6'); if (h) names[el.dataset.endpoint] = h.textContent.trim(); }
        });
        document.getElementById('confirmSyncText').textContent = 'Đồng bộ ' + (names[pendingSyncEp] || pendingSyncEp) + '. Quá trình có thể mất vài phút.';
        new bootstrap.Modal(document.getElementById('confirmSyncModal')).show();
    });
});

document.getElementById('confirmSyncBtn')?.addEventListener('click', function() {
    bootstrap.Modal.getInstance(document.getElementById('confirmSyncModal')).hide();
    if (!pendingSyncEp || !pendingSyncBtn) return;
    var btn = pendingSyncBtn;
    var ep = pendingSyncEp;
        var statusEl = document.getElementById('ep-' + ep + '-status');
        var syncBtn = btn;
        btn.disabled = true;
        this.disabled = true;

        // Hide ALL test/sync buttons immediately
        document.querySelectorAll('.sync-actions').forEach(function(c) { c.style.display = 'none'; });

        var syncEndpoints = {
            tasks: {url: '<?= url('settings/getfly-sync/sync-tasks-page') ?>', est: 9200},
            accounts: {url: '<?= url('settings/getfly-sync/sync-accounts-page') ?>', est: 26000},
            products: {url: '<?= url('settings/getfly-sync/sync-products-page') ?>', est: 6300},
            orders_sale: {url: '<?= url('settings/getfly-sync/sync-orders-page') ?>', est: 65, extra: '&order_type=2'},
            orders_purchase: {url: '<?= url('settings/getfly-sync/sync-orders-page') ?>', est: 65, extra: '&order_type=1'},
        };

        if (syncEndpoints[ep]) {
            // Page-by-page sync with progress
            var totalSynced = 0;
            var savedPage = parseInt(localStorage.getItem('gf_sync_' + ep + '_page') || '0');
            var page = 1;
            var estimatedTotal = syncEndpoints[ep].est;
            var syncUrl = syncEndpoints[ep].url;

            // If there's a saved page, ask to continue
            if (savedPage > 1) {
                if (confirm('Lần trước dừng ở trang ' + savedPage + '. Tiếp tục từ trang ' + savedPage + '?\n\nBấm Cancel để đồng bộ lại từ đầu.')) {
                    page = savedPage;
                    totalSynced = (savedPage - 1) * 50; // estimate
                }
            }

            var allBtnContainers = document.querySelectorAll('.sync-actions');

            statusEl.innerHTML = '<div class="w-100"><div class="d-flex justify-content-between mb-1"><small>Đang đồng bộ...</small><small id="sync-percent">0%</small></div><div class="progress" style="height:6px"><div class="progress-bar progress-bar-striped progress-bar-animated" id="sync-bar" style="width:0%"></div></div><small class="text-muted" id="sync-detail">Trang ' + page + '...</small></div>';
            statusEl.className = 'fs-12 w-100';

            function syncPage(pg) {
                // Save current page for resume
                localStorage.setItem('gf_sync_' + ep + '_page', pg);

                fetch(syncUrl, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: '_token=' + token + '&page=' + pg + (syncEndpoints[ep].extra || '')
                })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.error) {
                        // API error on this page - skip and continue
                        document.getElementById('sync-detail').textContent = 'Lỗi trang ' + pg + ': ' + d.error + ', tiếp tục...';
                        setTimeout(function() { syncPage(pg + 1); }, 500);
                        return;
                    }
                    totalSynced += (d.synced || 0);
                    var pct = Math.min(99, Math.round(totalSynced / estimatedTotal * 100));
                    if (d.done) pct = 100;
                    document.getElementById('sync-bar').style.width = pct + '%';
                    document.getElementById('sync-percent').textContent = pct + '%';
                    var detail = d.month ? ('Tháng ' + d.month + ' - ' + totalSynced + ' records') : ('Trang ' + pg + ' - ' + totalSynced + ' records');
                    document.getElementById('sync-detail').textContent = detail;

                    if (d.has_more) {
                        syncPage(pg + 1);
                    } else {
                        localStorage.removeItem('gf_sync_' + ep + '_page');
                        statusEl.innerHTML = '<i class="ri-check-double-line me-1"></i>Hoàn thành! ' + totalSynced + ' records đã đồng bộ';
                        statusEl.className = 'text-success fs-12 fw-medium';
                        syncBtn.disabled = false;
                        syncBtn.innerHTML = '<i class="ri-refresh-line me-1"></i> Đồng bộ';
                        allBtnContainers.forEach(function(c) { c.style.display = ''; });
                        var toast = document.createElement('div');
                        toast.className = 'position-fixed top-0 end-0 m-3 alert alert-success shadow fade show';
                        toast.style.zIndex = 9999;
                        toast.innerHTML = '<i class="ri-check-line me-1"></i>Đã đồng bộ ' + totalSynced + ' records từ Getfly';
                        document.body.appendChild(toast);
                        setTimeout(function() { toast.remove(); }, 3000);
                    }
                })
                .catch(function() {
                    // Network error - retry next page instead of stopping
                    document.getElementById('sync-detail').textContent = 'Lỗi trang ' + pg + ', tiếp tục...';
                    if (pg < 999) syncPage(pg + 1);
                    else {
                        statusEl.innerHTML = '<i class="ri-close-line me-1"></i>Dừng do quá nhiều lỗi';
                        statusEl.className = 'text-danger fs-12';
                        syncBtn.disabled = false;
                        allBtnContainers.forEach(function(c) { c.style.display = ''; });
                    }
                });
            }
            syncPage(1);
        } else {
            // Other endpoints - placeholder
            syncBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i> Đang đồng bộ...';
            fetch('<?= url('settings/getfly-sync/sync') ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: '_token=' + token + '&endpoint=' + ep
            })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.success) {
                    statusEl.innerHTML = '<i class="ri-check-double-line me-1"></i>' + d.message;
                    statusEl.className = 'text-success fs-12';
                } else {
                    statusEl.innerHTML = '<i class="ri-error-warning-line me-1"></i>' + (d.error || 'Lỗi');
                    statusEl.className = 'text-danger fs-12';
                }
            })
            .catch(function() {
                statusEl.innerHTML = '<i class="ri-close-line me-1"></i>Lỗi kết nối';
                statusEl.className = 'text-danger fs-12';
            })
            .finally(function() {
                syncBtn.disabled = false;
                syncBtn.innerHTML = '<i class="ri-refresh-line me-1"></i> Đồng bộ';
            });
        }
    });
</script>
<?php endif; ?>
