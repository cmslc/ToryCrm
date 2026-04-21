<?php $pageTitle = 'Tạo Automation Rule'; ?>

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Tạo Automation Rule</h4>
                    <div class="page-title-right">
                        <a href="<?= url('automation') ?>" class="btn btn-soft-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Quay lại
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="<?= url('automation/store') ?>" id="automationForm">
            <?= csrf_field() ?>

            <div class="row">
                <!-- Basic Info -->
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Thông tin cơ bản</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="name" class="form-label">Tên rule <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" placeholder="VD: Tự động gán khách hàng mới" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="module" class="form-label">Module <span class="text-danger">*</span></label>
                                    <select class="form-select" id="module" name="module" required>
                                        <option value="">-- Chọn module --</option>
                                        <option value="contact">Khách hàng</option>
                                        <option value="deal">Cơ hội</option>
                                        <option value="task">Công việc</option>
                                        <option value="ticket">Ticket</option>
                                        <option value="order">Đơn hàng</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="trigger_event" class="form-label">Trigger Event <span class="text-danger">*</span></label>
                                    <select class="form-select" id="trigger_event" name="trigger_event" required>
                                        <option value="">-- Chọn trigger --</option>
                                        <option value="created">Khi tạo mới</option>
                                        <option value="updated">Khi cập nhật</option>
                                        <option value="status_changed">Khi đổi trạng thái</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Conditions -->
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0"><i class="ri-filter-line me-1"></i> Điều kiện (Conditions)</h5>
                            <button type="button" class="btn btn btn-soft-primary" id="btnAddCondition">
                                <i class="ri-add-line me-1"></i> Thêm điều kiện
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="conditionsContainer">
                                <div class="text-muted text-center py-3" id="noConditionsMsg">
                                    <i class="ri-information-line me-1"></i> Chưa có điều kiện. Rule sẽ chạy cho tất cả bản ghi phù hợp với trigger.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0"><i class="ri-flashlight-line me-1"></i> Hành động (Actions)</h5>
                            <button type="button" class="btn btn btn-soft-primary" id="btnAddAction">
                                <i class="ri-add-line me-1"></i> Thêm hành động
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="actionsContainer">
                                <div class="text-muted text-center py-3" id="noActionsMsg">
                                    <i class="ri-information-line me-1"></i> Vui lòng thêm ít nhất 1 hành động.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="col-lg-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-1"></i> Lưu Automation Rule
                        </button>
                        <a href="<?= url('automation') ?>" class="btn btn-soft-secondary">Hủy</a>
                    </div>
                </div>
            </div>
        </form>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var conditionIndex = 0;
            var actionIndex = 0;

            var users = <?= json_encode($users ?? []) ?>;

            // ===== CONDITIONS =====
            document.getElementById('btnAddCondition').addEventListener('click', function() {
                addConditionRow();
            });

            function addConditionRow() {
                document.getElementById('noConditionsMsg').style.display = 'none';

                var idx = conditionIndex++;
                var row = document.createElement('div');
                row.className = 'row g-2 mb-2 align-items-center condition-row';
                row.setAttribute('data-index', idx);

                row.innerHTML = '' +
                    '<div class="col-md-3">' +
                        '<input type="text" class="form-control" name="conditions[' + idx + '][field]" placeholder="Tên trường (VD: status, email...)" required>' +
                    '</div>' +
                    '<div class="col-md-3">' +
                        '<select class="form-select" name="conditions[' + idx + '][operator]" required>' +
                            '<option value="">-- Toán tử --</option>' +
                            '<option value="equals">Bằng (=)</option>' +
                            '<option value="not_equals">Khác (!=)</option>' +
                            '<option value="contains">Chứa</option>' +
                            '<option value="not_contains">Không chứa</option>' +
                            '<option value="greater_than">Lớn hơn (>)</option>' +
                            '<option value="less_than">Nhỏ hơn (<)</option>' +
                            '<option value="is_empty">Rỗng</option>' +
                            '<option value="is_not_empty">Không rỗng</option>' +
                        '</select>' +
                    '</div>' +
                    '<div class="col-md-4">' +
                        '<input type="text" class="form-control" name="conditions[' + idx + '][value]" placeholder="Giá trị">' +
                    '</div>' +
                    '<div class="col-md-2">' +
                        '<button type="button" class="btn btn btn-soft-danger btn-remove-condition" title="Xóa">' +
                            '<i class="ri-delete-bin-line"></i> Xóa' +
                        '</button>' +
                    '</div>';

                document.getElementById('conditionsContainer').appendChild(row);

                row.querySelector('.btn-remove-condition').addEventListener('click', function() {
                    row.remove();
                    if (document.querySelectorAll('.condition-row').length === 0) {
                        document.getElementById('noConditionsMsg').style.display = 'block';
                    }
                });
            }

            // ===== ACTIONS =====
            document.getElementById('btnAddAction').addEventListener('click', function() {
                addActionRow();
            });

            function getUserSelectHtml(name) {
                var html = '<select class="form-select" name="' + name + '"><option value="">-- Chọn user --</option>';
                users.forEach(function(u) {
                    html += '<option value="' + u.id + '">' + u.name + '</option>';
                });
                html += '</select>';
                return html;
            }

            function addActionRow() {
                document.getElementById('noActionsMsg').style.display = 'none';

                var idx = actionIndex++;
                var row = document.createElement('div');
                row.className = 'card border mb-2 action-row';
                row.setAttribute('data-index', idx);

                row.innerHTML = '' +
                    '<div class="card-body py-3">' +
                        '<div class="row g-2 align-items-start">' +
                            '<div class="col-md-3">' +
                                '<label class="form-label">Loại hành động</label>' +
                                '<select class="form-select action-type-select" name="actions[' + idx + '][type]" required>' +
                                    '<option value="">-- Chọn loại --</option>' +
                                    '<option value="assign_to">Gán cho user</option>' +
                                    '<option value="send_email">Gửi email</option>' +
                                    '<option value="create_notification">Tạo thông báo</option>' +
                                    '<option value="update_field">Cập nhật trường</option>' +
                                '</select>' +
                            '</div>' +
                            '<div class="col-md-7 action-fields"></div>' +
                            '<div class="col-md-2 d-flex align-items-end">' +
                                '<button type="button" class="btn btn btn-soft-danger btn-remove-action mt-4" title="Xóa">' +
                                    '<i class="ri-delete-bin-line"></i> Xóa' +
                                '</button>' +
                            '</div>' +
                        '</div>' +
                    '</div>';

                document.getElementById('actionsContainer').appendChild(row);

                var typeSelect = row.querySelector('.action-type-select');
                var fieldsDiv = row.querySelector('.action-fields');

                typeSelect.addEventListener('change', function() {
                    var type = this.value;
                    fieldsDiv.innerHTML = '';

                    if (type === 'assign_to') {
                        fieldsDiv.innerHTML = '' +
                            '<label class="form-label">Gán cho</label>' +
                            getUserSelectHtml('actions[' + idx + '][user_id]');
                    } else if (type === 'send_email') {
                        fieldsDiv.innerHTML = '' +
                            '<div class="row g-2">' +
                                '<div class="col-12"><label class="form-label">Gửi đến (trường email)</label>' +
                                    '<input type="text" class="form-control" name="actions[' + idx + '][to_field]" placeholder="VD: contact.email" value="contact.email">' +
                                '</div>' +
                                '<div class="col-12"><label class="form-label">Tiêu đề</label>' +
                                    '<input type="text" class="form-control" name="actions[' + idx + '][subject]" placeholder="Tiêu đề email">' +
                                '</div>' +
                                '<div class="col-12"><label class="form-label">Nội dung</label>' +
                                    '<textarea class="form-control" name="actions[' + idx + '][body]" rows="3" placeholder="Nội dung email..."></textarea>' +
                                '</div>' +
                            '</div>';
                    } else if (type === 'create_notification') {
                        fieldsDiv.innerHTML = '' +
                            '<div class="row g-2">' +
                                '<div class="col-12"><label class="form-label">Tiêu đề thông báo</label>' +
                                    '<input type="text" class="form-control" name="actions[' + idx + '][title]" placeholder="Tiêu đề thông báo">' +
                                '</div>' +
                                '<div class="col-12"><label class="form-label">Nội dung</label>' +
                                    '<textarea class="form-control" name="actions[' + idx + '][message]" rows="2" placeholder="Nội dung thông báo..."></textarea>' +
                                '</div>' +
                            '</div>';
                    } else if (type === 'update_field') {
                        fieldsDiv.innerHTML = '' +
                            '<div class="row g-2">' +
                                '<div class="col-6"><label class="form-label">Tên trường</label>' +
                                    '<input type="text" class="form-control" name="actions[' + idx + '][field_name]" placeholder="VD: status, owner_id...">' +
                                '</div>' +
                                '<div class="col-6"><label class="form-label">Giá trị mới</label>' +
                                    '<input type="text" class="form-control" name="actions[' + idx + '][field_value]" placeholder="Giá trị mới">' +
                                '</div>' +
                            '</div>';
                    }
                });

                row.querySelector('.btn-remove-action').addEventListener('click', function() {
                    row.remove();
                    if (document.querySelectorAll('.action-row').length === 0) {
                        document.getElementById('noActionsMsg').style.display = 'block';
                    }
                });
            }
        });
        </script>
