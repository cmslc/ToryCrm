/**
 * ToryCRM Bulk Actions
 *
 * Usage:
 *   - Add class="row-check" value="ID" to each row checkbox
 *   - Add id="checkAll" to the header checkbox
 *   - Set window.__bulkConfig = { url: '/contacts/bulk', module: 'contacts', statuses: {...}, users: [...] }
 *   - Include this script at the bottom of the page
 */
(function () {
    'use strict';

    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) return meta.getAttribute('content');
        var input = document.querySelector('input[name="_token"]');
        return input ? input.value : '';
    }

    var config = window.__bulkConfig || {};
    var bulkUrl = config.url || '';
    var statuses = config.statuses || {};
    var users = config.users || [];

    // Create floating action bar
    var bar = document.createElement('div');
    bar.id = 'bulkActionBar';
    bar.style.cssText = 'position:fixed;bottom:0;left:0;right:0;z-index:1050;display:none;';
    bar.innerHTML =
        '<div class="container-fluid">' +
        '  <div class="card mb-0 border-0 rounded-0 shadow-lg">' +
        '    <div class="card-body py-3">' +
        '      <div class="d-flex align-items-center justify-content-between">' +
        '        <div class="d-flex align-items-center gap-3">' +
        '          <span class="fw-medium" id="bulkSelectedCount">0 mục đã chọn</span>' +
        '          <button class="btn btn-soft-primary" id="bulkAssignBtn"><i class="ri-user-add-line me-1"></i>Gán người</button>' +
        '          <button class="btn btn-soft-warning" id="bulkStatusBtn"><i class="ri-exchange-line me-1"></i>Đổi trạng thái</button>' +
        '          <button class="btn btn-soft-danger" id="bulkDeleteBtn"><i class="ri-delete-bin-line me-1"></i>Xóa</button>' +
        '        </div>' +
        '        <button class="btn btn-light" id="bulkDeselectBtn"><i class="ri-close-line me-1"></i>Bỏ chọn</button>' +
        '      </div>' +
        '    </div>' +
        '  </div>' +
        '</div>';
    document.body.appendChild(bar);

    // Create Assign Modal
    var assignModal = document.createElement('div');
    assignModal.className = 'modal fade';
    assignModal.id = 'bulkAssignModal';
    assignModal.tabIndex = -1;
    var userOptions = '<option value="">-- Chọn người phụ trách --</option>';
    users.forEach(function (u) {
        userOptions += '<option value="' + u.id + '">' + u.name + '</option>';
    });
    assignModal.innerHTML =
        '<div class="modal-dialog modal-dialog-centered">' +
        '  <div class="modal-content">' +
        '    <div class="modal-header">' +
        '      <h5 class="modal-title"><i class="ri-user-add-line me-2"></i>Gán người phụ trách</h5>' +
        '      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
        '    </div>' +
        '    <div class="modal-body">' +
        '      <select class="form-select" id="bulkAssignUser">' + userOptions + '</select>' +
        '    </div>' +
        '    <div class="modal-footer">' +
        '      <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>' +
        '      <button type="button" class="btn btn-primary" id="bulkAssignConfirm"><i class="ri-check-line me-1"></i>Xác nhận</button>' +
        '    </div>' +
        '  </div>' +
        '</div>';
    document.body.appendChild(assignModal);

    // Create Status Modal
    var statusModal = document.createElement('div');
    statusModal.className = 'modal fade';
    statusModal.id = 'bulkStatusModal';
    statusModal.tabIndex = -1;
    var statusOptions = '<option value="">-- Chọn trạng thái --</option>';
    Object.keys(statuses).forEach(function (k) {
        statusOptions += '<option value="' + k + '">' + statuses[k] + '</option>';
    });
    statusModal.innerHTML =
        '<div class="modal-dialog modal-dialog-centered">' +
        '  <div class="modal-content">' +
        '    <div class="modal-header">' +
        '      <h5 class="modal-title"><i class="ri-exchange-line me-2"></i>Đổi trạng thái</h5>' +
        '      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
        '    </div>' +
        '    <div class="modal-body">' +
        '      <select class="form-select" id="bulkStatusSelect">' + statusOptions + '</select>' +
        '    </div>' +
        '    <div class="modal-footer">' +
        '      <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>' +
        '      <button type="button" class="btn btn-warning" id="bulkStatusConfirm"><i class="ri-check-line me-1"></i>Xác nhận</button>' +
        '    </div>' +
        '  </div>' +
        '</div>';
    document.body.appendChild(statusModal);

    // Create Delete Confirm Modal
    var deleteModal = document.createElement('div');
    deleteModal.className = 'modal fade';
    deleteModal.id = 'bulkDeleteModal';
    deleteModal.tabIndex = -1;
    deleteModal.innerHTML =
        '<div class="modal-dialog modal-dialog-centered">' +
        '  <div class="modal-content">' +
        '    <div class="modal-header">' +
        '      <h5 class="modal-title"><i class="ri-error-warning-line me-2 text-danger"></i>Xác nhận xóa</h5>' +
        '      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
        '    </div>' +
        '    <div class="modal-body">' +
        '      <p class="mb-0">Bạn có chắc chắn muốn xóa <strong id="bulkDeleteCount">0</strong> mục đã chọn?</p>' +
        '    </div>' +
        '    <div class="modal-footer">' +
        '      <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>' +
        '      <button type="button" class="btn btn-danger" id="bulkDeleteConfirm"><i class="ri-delete-bin-line me-1"></i>Xóa</button>' +
        '    </div>' +
        '  </div>' +
        '</div>';
    document.body.appendChild(deleteModal);

    function getSelectedIds() {
        var ids = [];
        document.querySelectorAll('.row-check:checked').forEach(function (cb) {
            ids.push(cb.value);
        });
        return ids;
    }

    function updateBar() {
        var ids = getSelectedIds();
        var count = ids.length;
        document.getElementById('bulkSelectedCount').textContent = count + ' mục đã chọn';
        bar.style.display = count > 0 ? 'block' : 'none';
    }

    function executeBulkAction(action, value) {
        var ids = getSelectedIds();
        if (ids.length === 0) return;

        var body = new FormData();
        body.append('_token', getCsrfToken());
        ids.forEach(function (id) { body.append('ids[]', id); });
        body.append('action', action);
        if (value) body.append('value', value);

        fetch(bulkUrl, { method: 'POST', body: body })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.error || 'Lỗi thực hiện hành động');
            }
        })
        .catch(function () { alert('Lỗi kết nối'); });
    }

    // Check All
    var checkAll = document.getElementById('checkAll');
    if (checkAll) {
        checkAll.addEventListener('change', function () {
            var checked = this.checked;
            document.querySelectorAll('.row-check').forEach(function (cb) {
                cb.checked = checked;
            });
            updateBar();
        });
    }

    // Individual checkboxes
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('row-check')) {
            updateBar();
            // Update checkAll state
            if (checkAll) {
                var all = document.querySelectorAll('.row-check');
                var checked = document.querySelectorAll('.row-check:checked');
                checkAll.checked = all.length > 0 && all.length === checked.length;
                checkAll.indeterminate = checked.length > 0 && checked.length < all.length;
            }
        }
    });

    // Deselect all
    document.getElementById('bulkDeselectBtn')?.addEventListener('click', function () {
        document.querySelectorAll('.row-check:checked').forEach(function (cb) { cb.checked = false; });
        if (checkAll) { checkAll.checked = false; checkAll.indeterminate = false; }
        updateBar();
    });

    // Assign button
    document.getElementById('bulkAssignBtn')?.addEventListener('click', function () {
        new bootstrap.Modal(document.getElementById('bulkAssignModal')).show();
    });

    document.getElementById('bulkAssignConfirm')?.addEventListener('click', function () {
        var userId = document.getElementById('bulkAssignUser').value;
        if (!userId) return;
        bootstrap.Modal.getInstance(document.getElementById('bulkAssignModal'))?.hide();
        executeBulkAction('assign', userId);
    });

    // Status button
    document.getElementById('bulkStatusBtn')?.addEventListener('click', function () {
        new bootstrap.Modal(document.getElementById('bulkStatusModal')).show();
    });

    document.getElementById('bulkStatusConfirm')?.addEventListener('click', function () {
        var status = document.getElementById('bulkStatusSelect').value;
        if (!status) return;
        bootstrap.Modal.getInstance(document.getElementById('bulkStatusModal'))?.hide();
        executeBulkAction('status', status);
    });

    // Delete button
    document.getElementById('bulkDeleteBtn')?.addEventListener('click', function () {
        document.getElementById('bulkDeleteCount').textContent = getSelectedIds().length;
        new bootstrap.Modal(document.getElementById('bulkDeleteModal')).show();
    });

    document.getElementById('bulkDeleteConfirm')?.addEventListener('click', function () {
        bootstrap.Modal.getInstance(document.getElementById('bulkDeleteModal'))?.hide();
        executeBulkAction('delete', '');
    });

})();
