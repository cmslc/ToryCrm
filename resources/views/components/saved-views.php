<?php
/**
 * Saved Views Component
 * Usage: include with $module variable set (e.g., 'contacts', 'deals', 'orders', 'tasks', 'tickets')
 */
$svModule = $module ?? 'contacts';
?>

<div class="dropdown d-inline-block" id="savedViewsDropdown">
    <button class="btn btn-soft-info" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" id="savedViewsBtn">
        <i class="ri-bookmark-line me-1"></i> Lưu bộ lọc
    </button>
    <div class="dropdown-menu dropdown-menu-end p-0" style="min-width: 300px;" id="savedViewsMenu">
        <div class="p-3 border-bottom">
            <h6 class="mb-0"><i class="ri-bookmark-line me-1"></i> Bộ lọc đã lưu</h6>
        </div>
        <div id="savedViewsList" class="py-2" style="max-height: 250px; overflow-y: auto;">
            <div class="text-center py-3 text-muted" id="savedViewsLoading">
                <div class="spinner-border spinner-border-sm me-1"></div> Đang tải...
            </div>
        </div>
        <div class="border-top p-3">
            <button class="btn btn-primary w-100" type="button" id="saveCurrentViewBtn">
                <i class="ri-save-line me-1"></i> Lưu bộ lọc hiện tại
            </button>
        </div>
    </div>
</div>

<!-- Save View Modal -->
<div class="modal fade" id="saveViewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ri-bookmark-line me-2"></i>Lưu bộ lọc</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tên bộ lọc <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="saveViewName" placeholder="VD: Khách hàng mới tháng này...">
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="saveViewShared">
                    <label class="form-check-label" for="saveViewShared">
                        Chia sẻ với team
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="confirmSaveView">
                    <i class="ri-save-line me-1"></i> Lưu
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const MODULE = '<?= e($svModule) ?>';
    const listEl = document.getElementById('savedViewsList');
    const loadingEl = document.getElementById('savedViewsLoading');

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) return meta.getAttribute('content');
        const input = document.querySelector('input[name="_token"]');
        return input ? input.value : '';
    }

    function getCurrentFilters() {
        const params = new URLSearchParams(window.location.search);
        const filters = {};
        params.forEach((value, key) => {
            if (key !== 'page' && value) filters[key] = value;
        });
        return filters;
    }

    function buildFilterUrl(filters) {
        try {
            const parsed = typeof filters === 'string' ? JSON.parse(filters) : filters;
            const params = new URLSearchParams();
            Object.entries(parsed).forEach(([k, v]) => {
                if (v) params.set(k, v);
            });
            const qs = params.toString();
            return '/' + MODULE + (qs ? '?' + qs : '');
        } catch (e) {
            return '/' + MODULE;
        }
    }

    function loadViews() {
        listEl.innerHTML = '<div class="text-center py-3 text-muted"><div class="spinner-border spinner-border-sm me-1"></div> Đang tải...</div>';

        fetch('/saved-views/' + MODULE, {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.views || data.views.length === 0) {
                listEl.innerHTML = '<div class="text-center py-3 text-muted fs-13">Chưa có bộ lọc nào được lưu</div>';
                return;
            }

            let html = '';
            data.views.forEach(view => {
                const isDefault = view.is_default == 1;
                const isShared = view.is_shared == 1;
                const filterUrl = buildFilterUrl(view.filters);

                html += '<div class="d-flex align-items-center px-3 py-2 saved-view-item" data-id="' + view.id + '">';
                html += '  <a href="' + filterUrl + '" class="flex-grow-1 text-body text-decoration-none">';
                html += '    ' + (isDefault ? '<i class="ri-star-fill text-warning me-1"></i>' : '');
                html += '    ' + view.name;
                html += '    ' + (isShared ? ' <span class="badge bg-info-subtle text-info ms-1">Team</span>' : '');
                html += '  </a>';
                html += '  <div class="d-flex gap-1 ms-2">';
                if (!isDefault) {
                    html += '    <button class="btn btn-soft-warning btn-icon" style="width:28px;height:28px;padding:0;" title="Đặt mặc định" onclick="savedViewSetDefault(' + view.id + ')">';
                    html += '      <i class="ri-star-line fs-14"></i>';
                    html += '    </button>';
                }
                html += '    <button class="btn btn-soft-danger btn-icon" style="width:28px;height:28px;padding:0;" title="Xóa" onclick="savedViewDelete(' + view.id + ')">';
                html += '      <i class="ri-delete-bin-line fs-14"></i>';
                html += '    </button>';
                html += '  </div>';
                html += '</div>';
            });

            listEl.innerHTML = html;
        })
        .catch(() => {
            listEl.innerHTML = '<div class="text-center py-3 text-danger fs-13">Lỗi tải dữ liệu</div>';
        });
    }

    // Load views when dropdown opens
    document.getElementById('savedViewsBtn')?.addEventListener('click', loadViews);

    // Open save modal
    document.getElementById('saveCurrentViewBtn')?.addEventListener('click', function() {
        const bsDropdown = bootstrap.Dropdown.getInstance(document.getElementById('savedViewsBtn'));
        if (bsDropdown) bsDropdown.hide();
        new bootstrap.Modal(document.getElementById('saveViewModal')).show();
        document.getElementById('saveViewName').value = '';
        document.getElementById('saveViewShared').checked = false;
        setTimeout(() => document.getElementById('saveViewName').focus(), 300);
    });

    // Confirm save
    document.getElementById('confirmSaveView')?.addEventListener('click', function() {
        const name = document.getElementById('saveViewName').value.trim();
        if (!name) {
            document.getElementById('saveViewName').classList.add('is-invalid');
            return;
        }

        const body = new FormData();
        body.append('_token', getCsrfToken());
        body.append('module', MODULE);
        body.append('name', name);
        body.append('filters', JSON.stringify(getCurrentFilters()));
        body.append('is_shared', document.getElementById('saveViewShared').checked ? '1' : '0');

        fetch('/saved-views/store', {
            method: 'POST',
            body: body
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('saveViewModal'))?.hide();
                if (typeof Toastify !== 'undefined') {
                    Toastify({ text: 'Đã lưu bộ lọc thành công', duration: 2000, gravity: 'top', position: 'right', className: 'bg-success' }).showToast();
                }
            } else {
                alert(data.error || 'Lỗi khi lưu bộ lọc');
            }
        })
        .catch(() => alert('Lỗi kết nối'));
    });

    // Global functions for inline handlers
    window.savedViewDelete = function(id) {
        if (!confirm('Xóa bộ lọc này?')) return;

        const body = new FormData();
        body.append('_token', getCsrfToken());

        fetch('/saved-views/' + id + '/delete', { method: 'POST', body: body })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const item = listEl.querySelector('[data-id="' + id + '"]');
                if (item) item.remove();
            }
        });
    };

    window.savedViewSetDefault = function(id) {
        const body = new FormData();
        body.append('_token', getCsrfToken());

        fetch('/saved-views/' + id + '/default', { method: 'POST', body: body })
        .then(r => r.json())
        .then(data => {
            if (data.success) loadViews();
        });
    };
})();
</script>
