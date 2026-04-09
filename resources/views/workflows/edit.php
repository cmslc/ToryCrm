<?php $pageTitle = 'Sửa Workflow'; ?>

<style>
.workflow-canvas-wrap {
    display: flex;
    min-height: 600px;
    border: 1px solid var(--vz-border-color);
    border-radius: .25rem;
    background: var(--vz-light);
    position: relative;
    overflow: hidden;
}
.workflow-palette {
    width: 250px;
    min-width: 250px;
    background: #fff;
    border-right: 1px solid var(--vz-border-color);
    padding: 1rem;
    overflow-y: auto;
}
.workflow-palette h6 {
    font-size: .75rem;
    text-transform: uppercase;
    color: var(--vz-secondary);
    margin-bottom: .5rem;
    margin-top: 1rem;
}
.workflow-palette h6:first-child {
    margin-top: 0;
}
.palette-item {
    padding: .5rem .75rem;
    margin-bottom: .25rem;
    border: 1px solid var(--vz-border-color);
    border-radius: .25rem;
    cursor: grab;
    background: #fff;
    font-size: .875rem;
    display: flex;
    align-items: center;
    gap: .5rem;
    transition: box-shadow .15s;
    user-select: none;
}
.palette-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,.1);
}
.palette-item:active {
    cursor: grabbing;
}
.workflow-canvas {
    flex: 1;
    position: relative;
    overflow: auto;
    min-height: 600px;
}
.workflow-canvas-inner {
    position: relative;
    width: 2000px;
    height: 1200px;
}
.wf-node {
    position: absolute;
    width: 200px;
    background: #fff;
    border: 2px solid var(--vz-border-color);
    border-radius: .5rem;
    box-shadow: 0 1px 4px rgba(0,0,0,.08);
    cursor: move;
    user-select: none;
    z-index: 2;
}
.wf-node.selected {
    border-color: var(--vz-primary);
    box-shadow: 0 0 0 3px rgba(64,81,137,.2);
}
.wf-node-header {
    padding: .5rem .75rem;
    border-bottom: 1px solid var(--vz-border-color);
    display: flex;
    align-items: center;
    gap: .5rem;
    font-weight: 600;
    font-size: .8rem;
}
.wf-node-header .wf-node-delete {
    margin-left: auto;
    cursor: pointer;
    color: var(--vz-danger);
    opacity: .6;
    font-size: 1rem;
    line-height: 1;
}
.wf-node-header .wf-node-delete:hover {
    opacity: 1;
}
.wf-node-body {
    padding: .5rem .75rem;
    font-size: .75rem;
    color: var(--vz-secondary);
    min-height: 30px;
}
.wf-node .wf-port {
    position: absolute;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: var(--vz-primary);
    border: 2px solid #fff;
    box-shadow: 0 0 0 1px var(--vz-primary);
    z-index: 3;
    cursor: crosshair;
}
.wf-port-in {
    top: -6px;
    left: 50%;
    transform: translateX(-50%);
}
.wf-port-out {
    bottom: -6px;
    left: 50%;
    transform: translateX(-50%);
}
.workflow-config-panel {
    width: 300px;
    min-width: 300px;
    background: #fff;
    border-left: 1px solid var(--vz-border-color);
    padding: 1rem;
    overflow-y: auto;
    display: none;
}
.workflow-config-panel.open {
    display: block;
}
#wfSvg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 1;
}
#wfSvg line {
    pointer-events: stroke;
    cursor: pointer;
}
</style>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Sửa Workflow</h4>
            <div class="d-flex gap-2">
                <a href="<?= url('workflows') ?>" class="btn btn-light"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
                <button type="button" class="btn btn-primary" id="btnSaveWorkflow"><i class="ri-save-line me-1"></i> Lưu</button>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tên workflow <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="wfName" value="<?= e($workflow['name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Mô tả</label>
                        <input type="text" class="form-control" id="wfDescription" value="<?= e($workflow['description'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="workflow-canvas-wrap">
            <!-- Left palette -->
            <div class="workflow-palette">
                <h6>Trigger</h6>
                <div class="palette-item" data-type="trigger" data-trigger="contact.created" draggable="true">
                    <i class="ri-user-add-line text-primary"></i> Khách hàng mới
                </div>
                <div class="palette-item" data-type="trigger" data-trigger="deal.stage_changed" draggable="true">
                    <i class="ri-exchange-line text-warning"></i> Deal thay đổi
                </div>
                <div class="palette-item" data-type="trigger" data-trigger="task.overdue" draggable="true">
                    <i class="ri-alarm-warning-line text-danger"></i> Task quá hạn
                </div>
                <div class="palette-item" data-type="trigger" data-trigger="order.created" draggable="true">
                    <i class="ri-shopping-cart-2-line text-success"></i> Đơn hàng mới
                </div>
                <div class="palette-item" data-type="trigger" data-trigger="ticket.created" draggable="true">
                    <i class="ri-customer-service-line text-info"></i> Ticket mới
                </div>

                <h6>Action</h6>
                <div class="palette-item" data-type="action" data-action="send_email" draggable="true">
                    <i class="ri-mail-send-line text-primary"></i> Gửi email
                </div>
                <div class="palette-item" data-type="action" data-action="send_sms" draggable="true">
                    <i class="ri-message-2-line text-success"></i> Gửi SMS
                </div>
                <div class="palette-item" data-type="action" data-action="create_task" draggable="true">
                    <i class="ri-task-line text-warning"></i> Tạo task
                </div>
                <div class="palette-item" data-type="action" data-action="assign_user" draggable="true">
                    <i class="ri-user-settings-line text-info"></i> Gán người
                </div>
                <div class="palette-item" data-type="action" data-action="update_field" draggable="true">
                    <i class="ri-edit-line text-secondary"></i> Cập nhật trường
                </div>
                <div class="palette-item" data-type="action" data-action="send_notification" draggable="true">
                    <i class="ri-notification-3-line text-danger"></i> Gửi thông báo
                </div>
                <div class="palette-item" data-type="action" data-action="delay" draggable="true">
                    <i class="ri-time-line text-muted"></i> Chờ (delay)
                </div>

                <h6>Điều kiện</h6>
                <div class="palette-item" data-type="condition" data-action="check_condition" draggable="true">
                    <i class="ri-git-merge-line text-primary"></i> Kiểm tra điều kiện
                </div>
                <div class="palette-item" data-type="condition" data-action="branch" draggable="true">
                    <i class="ri-git-branch-line text-warning"></i> Chia nhánh
                </div>
            </div>

            <!-- Main canvas -->
            <div class="workflow-canvas" id="wfCanvas">
                <div class="workflow-canvas-inner" id="wfCanvasInner">
                    <svg id="wfSvg"></svg>
                </div>
            </div>

            <!-- Right config panel -->
            <div class="workflow-config-panel" id="wfConfigPanel">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="mb-0">Cấu hình node</h6>
                    <button type="button" class="btn-close" id="btnCloseConfig"></button>
                </div>
                <div id="wfConfigForm"></div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var nodes = <?= $workflow['nodes'] ?? '[]' ?>;
            var edges = <?= $workflow['edges'] ?? '[]' ?>;
            var nodeIdCounter = 0;
            var selectedNodeId = null;
            var dragNode = null;
            var dragOffsetX = 0, dragOffsetY = 0;
            var connecting = false;
            var connectFrom = null;

            var canvas = document.getElementById('wfCanvas');
            var canvasInner = document.getElementById('wfCanvasInner');
            var svg = document.getElementById('wfSvg');
            var configPanel = document.getElementById('wfConfigPanel');
            var configForm = document.getElementById('wfConfigForm');

            var nodeLabels = {
                'contact.created': 'Khách hàng mới',
                'deal.stage_changed': 'Deal thay đổi',
                'task.overdue': 'Task quá hạn',
                'order.created': 'Đơn hàng mới',
                'ticket.created': 'Ticket mới',
                'send_email': 'Gửi email',
                'send_sms': 'Gửi SMS',
                'create_task': 'Tạo task',
                'assign_user': 'Gán người',
                'update_field': 'Cập nhật trường',
                'send_notification': 'Gửi thông báo',
                'delay': 'Chờ (delay)',
                'check_condition': 'Kiểm tra điều kiện',
                'branch': 'Chia nhánh'
            };

            var nodeIcons = {
                'contact.created': 'ri-user-add-line',
                'deal.stage_changed': 'ri-exchange-line',
                'task.overdue': 'ri-alarm-warning-line',
                'order.created': 'ri-shopping-cart-2-line',
                'ticket.created': 'ri-customer-service-line',
                'send_email': 'ri-mail-send-line',
                'send_sms': 'ri-message-2-line',
                'create_task': 'ri-task-line',
                'assign_user': 'ri-user-settings-line',
                'update_field': 'ri-edit-line',
                'send_notification': 'ri-notification-3-line',
                'delay': 'ri-time-line',
                'check_condition': 'ri-git-merge-line',
                'branch': 'ri-git-branch-line'
            };

            var nodeTypeColors = {
                'trigger': '#405189',
                'action': '#0ab39c',
                'condition': '#f7b84b'
            };

            // Determine max ID from loaded nodes
            nodes.forEach(function(n) {
                var match = (n.id || '').match(/node_(\d+)/);
                if (match) {
                    var num = parseInt(match[1]);
                    if (num > nodeIdCounter) nodeIdCounter = num;
                }
            });

            function generateId() {
                return 'node_' + (++nodeIdCounter);
            }

            function addNode(type, subtype, x, y) {
                var id = generateId();
                var label = nodeLabels[subtype] || subtype;
                var node = {
                    id: id,
                    type: type,
                    subtype: subtype,
                    label: label,
                    config: {},
                    position: { x: x, y: y }
                };
                nodes.push(node);
                renderNode(node);
                return node;
            }

            function renderNode(node) {
                var existing = document.getElementById(node.id);
                if (existing) existing.remove();

                var el = document.createElement('div');
                el.className = 'wf-node' + (selectedNodeId === node.id ? ' selected' : '');
                el.id = node.id;
                el.style.left = node.position.x + 'px';
                el.style.top = node.position.y + 'px';

                var color = nodeTypeColors[node.type] || '#6c757d';
                var icon = nodeIcons[node.subtype] || 'ri-flashlight-line';
                var configSummary = getConfigSummary(node);

                el.innerHTML = '<div class="wf-port wf-port-in" data-port="in" data-node="' + node.id + '"></div>' +
                    '<div class="wf-node-header" style="border-top:3px solid ' + color + '">' +
                    '<i class="' + icon + '" style="color:' + color + '"></i>' +
                    '<span>' + node.label + '</span>' +
                    '<span class="wf-node-delete" data-node="' + node.id + '">&times;</span>' +
                    '</div>' +
                    '<div class="wf-node-body">' + (configSummary || '<span class="text-muted fst-italic">Click để cấu hình</span>') + '</div>' +
                    '<div class="wf-port wf-port-out" data-port="out" data-node="' + node.id + '"></div>';

                canvasInner.appendChild(el);

                el.addEventListener('mousedown', function(e) {
                    if (e.target.classList.contains('wf-port') || e.target.classList.contains('wf-node-delete')) return;
                    dragNode = node;
                    var rect = el.getBoundingClientRect();
                    dragOffsetX = e.clientX - rect.left;
                    dragOffsetY = e.clientY - rect.top;
                    e.preventDefault();
                });

                el.addEventListener('click', function(e) {
                    if (e.target.classList.contains('wf-port') || e.target.classList.contains('wf-node-delete')) return;
                    selectNode(node.id);
                });

                el.querySelector('.wf-node-delete').addEventListener('click', function() {
                    deleteNode(node.id);
                });

                var ports = el.querySelectorAll('.wf-port');
                ports.forEach(function(port) {
                    port.addEventListener('mousedown', function(e) {
                        e.stopPropagation();
                        if (port.dataset.port === 'out') {
                            connecting = true;
                            connectFrom = port.dataset.node;
                        }
                    });
                    port.addEventListener('mouseup', function(e) {
                        e.stopPropagation();
                        if (connecting && port.dataset.port === 'in' && connectFrom !== port.dataset.node) {
                            var alreadyExists = edges.some(function(edge) {
                                return edge.from === connectFrom && edge.to === port.dataset.node;
                            });
                            if (!alreadyExists) {
                                edges.push({ from: connectFrom, to: port.dataset.node });
                                renderEdges();
                            }
                        }
                        connecting = false;
                        connectFrom = null;
                    });
                });
            }

            function getConfigSummary(node) {
                var c = node.config || {};
                if (node.subtype === 'send_email' && c.to) return 'Tới: ' + c.to;
                if (node.subtype === 'send_sms' && c.phone) return 'SĐT: ' + c.phone;
                if (node.subtype === 'create_task' && c.title) return 'Task: ' + c.title;
                if (node.subtype === 'delay' && c.minutes) return 'Chờ ' + c.minutes + ' phút';
                if (node.subtype === 'assign_user' && c.user_name) return 'Gán: ' + c.user_name;
                if (node.subtype === 'update_field' && c.field) return 'Trường: ' + c.field;
                if (node.subtype === 'send_notification' && c.message) return c.message.substring(0, 30);
                if (node.subtype === 'check_condition' && c.field) return c.field + ' ' + (c.operator || '') + ' ' + (c.value || '');
                if (node.subtype === 'branch' && c.branches) return c.branches + ' nhánh';
                return '';
            }

            function deleteNode(id) {
                nodes = nodes.filter(function(n) { return n.id !== id; });
                edges = edges.filter(function(e) { return e.from !== id && e.to !== id; });
                var el = document.getElementById(id);
                if (el) el.remove();
                if (selectedNodeId === id) {
                    selectedNodeId = null;
                    configPanel.classList.remove('open');
                }
                renderEdges();
            }

            function selectNode(id) {
                selectedNodeId = id;
                document.querySelectorAll('.wf-node').forEach(function(el) {
                    el.classList.toggle('selected', el.id === id);
                });
                showConfigPanel(id);
            }

            function showConfigPanel(nodeId) {
                var node = nodes.find(function(n) { return n.id === nodeId; });
                if (!node) return;

                configPanel.classList.add('open');
                var html = '<p class="text-muted mb-3">' + node.label + '</p>';

                if (node.type === 'trigger') {
                    html += '<div class="mb-3"><label class="form-label">Trigger type</label>' +
                        '<input type="text" class="form-control" value="' + (node.subtype || '') + '" readonly></div>';
                } else if (node.subtype === 'send_email') {
                    html += '<div class="mb-3"><label class="form-label">Tới (email)</label>' +
                        '<input type="text" class="form-control wf-cfg" data-key="to" value="' + (node.config.to || '') + '" placeholder="email@example.com"></div>' +
                        '<div class="mb-3"><label class="form-label">Tiêu đề</label>' +
                        '<input type="text" class="form-control wf-cfg" data-key="subject" value="' + (node.config.subject || '') + '"></div>' +
                        '<div class="mb-3"><label class="form-label">Nội dung</label>' +
                        '<textarea class="form-control wf-cfg" data-key="body" rows="3">' + (node.config.body || '') + '</textarea></div>';
                } else if (node.subtype === 'send_sms') {
                    html += '<div class="mb-3"><label class="form-label">Số điện thoại</label>' +
                        '<input type="text" class="form-control wf-cfg" data-key="phone" value="' + (node.config.phone || '') + '"></div>' +
                        '<div class="mb-3"><label class="form-label">Nội dung</label>' +
                        '<textarea class="form-control wf-cfg" data-key="message" rows="3">' + (node.config.message || '') + '</textarea></div>';
                } else if (node.subtype === 'create_task') {
                    html += '<div class="mb-3"><label class="form-label">Tiêu đề task</label>' +
                        '<input type="text" class="form-control wf-cfg" data-key="title" value="' + (node.config.title || '') + '"></div>' +
                        '<div class="mb-3"><label class="form-label">Mô tả</label>' +
                        '<textarea class="form-control wf-cfg" data-key="description" rows="2">' + (node.config.description || '') + '</textarea></div>';
                } else if (node.subtype === 'assign_user') {
                    html += '<div class="mb-3"><label class="form-label">User ID</label>' +
                        '<input type="text" class="form-control wf-cfg" data-key="user_id" value="' + (node.config.user_id || '') + '"></div>' +
                        '<div class="mb-3"><label class="form-label">Tên người dùng</label>' +
                        '<input type="text" class="form-control wf-cfg" data-key="user_name" value="' + (node.config.user_name || '') + '"></div>';
                } else if (node.subtype === 'update_field') {
                    html += '<div class="mb-3"><label class="form-label">Tên trường</label>' +
                        '<input type="text" class="form-control wf-cfg" data-key="field" value="' + (node.config.field || '') + '"></div>' +
                        '<div class="mb-3"><label class="form-label">Giá trị mới</label>' +
                        '<input type="text" class="form-control wf-cfg" data-key="value" value="' + (node.config.value || '') + '"></div>';
                } else if (node.subtype === 'send_notification') {
                    html += '<div class="mb-3"><label class="form-label">Nội dung thông báo</label>' +
                        '<textarea class="form-control wf-cfg" data-key="message" rows="3">' + (node.config.message || '') + '</textarea></div>';
                } else if (node.subtype === 'delay') {
                    html += '<div class="mb-3"><label class="form-label">Thời gian chờ (phút)</label>' +
                        '<input type="number" class="form-control wf-cfg" data-key="minutes" value="' + (node.config.minutes || '') + '" min="1"></div>';
                } else if (node.subtype === 'check_condition') {
                    html += '<div class="mb-3"><label class="form-label">Trường</label>' +
                        '<input type="text" class="form-control wf-cfg" data-key="field" value="' + (node.config.field || '') + '"></div>' +
                        '<div class="mb-3"><label class="form-label">Toán tử</label>' +
                        '<select class="form-select wf-cfg" data-key="operator">' +
                        '<option value="==" ' + ((node.config.operator||'') === '==' ? 'selected' : '') + '>Bằng (==)</option>' +
                        '<option value="!=" ' + ((node.config.operator||'') === '!=' ? 'selected' : '') + '>Khác (!=)</option>' +
                        '<option value=">" ' + ((node.config.operator||'') === '>' ? 'selected' : '') + '>Lớn hơn (>)</option>' +
                        '<option value="<" ' + ((node.config.operator||'') === '<' ? 'selected' : '') + '>Nhỏ hơn (<)</option>' +
                        '<option value="contains" ' + ((node.config.operator||'') === 'contains' ? 'selected' : '') + '>Chứa</option>' +
                        '</select></div>' +
                        '<div class="mb-3"><label class="form-label">Giá trị</label>' +
                        '<input type="text" class="form-control wf-cfg" data-key="value" value="' + (node.config.value || '') + '"></div>';
                } else if (node.subtype === 'branch') {
                    html += '<div class="mb-3"><label class="form-label">Số nhánh</label>' +
                        '<input type="number" class="form-control wf-cfg" data-key="branches" value="' + (node.config.branches || '2') + '" min="2" max="5"></div>';
                }

                html += '<button type="button" class="btn btn-primary w-100" id="btnApplyConfig"><i class="ri-check-line me-1"></i> Áp dụng</button>';

                configForm.innerHTML = html;

                document.getElementById('btnApplyConfig').addEventListener('click', function() {
                    var cfgInputs = configForm.querySelectorAll('.wf-cfg');
                    var config = {};
                    cfgInputs.forEach(function(input) {
                        config[input.dataset.key] = input.value;
                    });
                    node.config = config;
                    renderNode(node);
                    renderEdges();
                });
            }

            document.getElementById('btnCloseConfig').addEventListener('click', function() {
                configPanel.classList.remove('open');
                selectedNodeId = null;
                document.querySelectorAll('.wf-node.selected').forEach(function(el) {
                    el.classList.remove('selected');
                });
            });

            document.addEventListener('mousemove', function(e) {
                if (dragNode) {
                    var canvasRect = canvasInner.getBoundingClientRect();
                    var x = e.clientX - canvasRect.left - dragOffsetX;
                    var y = e.clientY - canvasRect.top - dragOffsetY;
                    x = Math.max(0, Math.min(x, 1800));
                    y = Math.max(0, Math.min(y, 1100));
                    dragNode.position.x = x;
                    dragNode.position.y = y;
                    var el = document.getElementById(dragNode.id);
                    if (el) {
                        el.style.left = x + 'px';
                        el.style.top = y + 'px';
                    }
                    renderEdges();
                }
            });

            document.addEventListener('mouseup', function() {
                dragNode = null;
                connecting = false;
                connectFrom = null;
            });

            var paletteItems = document.querySelectorAll('.palette-item');
            paletteItems.forEach(function(item) {
                item.addEventListener('dragstart', function(e) {
                    e.dataTransfer.setData('text/plain', JSON.stringify({
                        type: item.dataset.type,
                        subtype: item.dataset.trigger || item.dataset.action
                    }));
                });
            });

            canvas.addEventListener('dragover', function(e) {
                e.preventDefault();
            });

            canvas.addEventListener('drop', function(e) {
                e.preventDefault();
                var data;
                try {
                    data = JSON.parse(e.dataTransfer.getData('text/plain'));
                } catch(ex) {
                    return;
                }
                var canvasRect = canvasInner.getBoundingClientRect();
                var x = e.clientX - canvasRect.left - 100;
                var y = e.clientY - canvasRect.top - 30;
                x = Math.max(0, x);
                y = Math.max(0, y);
                addNode(data.type, data.subtype, x, y);
            });

            function renderEdges() {
                while (svg.firstChild) svg.removeChild(svg.firstChild);

                edges.forEach(function(edge, idx) {
                    var fromEl = document.getElementById(edge.from);
                    var toEl = document.getElementById(edge.to);
                    if (!fromEl || !toEl) return;

                    var fromPort = fromEl.querySelector('.wf-port-out');
                    var toPort = toEl.querySelector('.wf-port-in');

                    var canvasRect = canvasInner.getBoundingClientRect();
                    var fp = fromPort.getBoundingClientRect();
                    var tp = toPort.getBoundingClientRect();

                    var x1 = fp.left + fp.width / 2 - canvasRect.left;
                    var y1 = fp.top + fp.height / 2 - canvasRect.top;
                    var x2 = tp.left + tp.width / 2 - canvasRect.left;
                    var y2 = tp.top + tp.height / 2 - canvasRect.top;

                    var line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                    line.setAttribute('x1', x1);
                    line.setAttribute('y1', y1);
                    line.setAttribute('x2', x2);
                    line.setAttribute('y2', y2);
                    line.setAttribute('stroke', '#405189');
                    line.setAttribute('stroke-width', '2');
                    line.setAttribute('marker-end', 'url(#arrowhead)');
                    line.setAttribute('data-edge-idx', idx);
                    line.style.pointerEvents = 'stroke';
                    line.style.cursor = 'pointer';

                    line.addEventListener('dblclick', function() {
                        edges.splice(idx, 1);
                        renderEdges();
                    });

                    svg.appendChild(line);
                });

                if (!document.getElementById('arrowhead')) {
                    var defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
                    var marker = document.createElementNS('http://www.w3.org/2000/svg', 'marker');
                    marker.setAttribute('id', 'arrowhead');
                    marker.setAttribute('markerWidth', '10');
                    marker.setAttribute('markerHeight', '7');
                    marker.setAttribute('refX', '10');
                    marker.setAttribute('refY', '3.5');
                    marker.setAttribute('orient', 'auto');
                    var polygon = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
                    polygon.setAttribute('points', '0 0, 10 3.5, 0 7');
                    polygon.setAttribute('fill', '#405189');
                    marker.appendChild(polygon);
                    defs.appendChild(marker);
                    svg.insertBefore(defs, svg.firstChild);
                }
            }

            // Load existing nodes
            nodes.forEach(function(node) {
                renderNode(node);
            });
            setTimeout(function() { renderEdges(); }, 100);

            // Save
            document.getElementById('btnSaveWorkflow').addEventListener('click', function() {
                var name = document.getElementById('wfName').value.trim();
                if (!name) {
                    alert('Vui lòng nhập tên workflow.');
                    return;
                }

                var triggerNode = nodes.find(function(n) { return n.type === 'trigger'; });
                var triggerType = triggerNode ? triggerNode.subtype : '';

                var nodesData = nodes.map(function(n) {
                    return { id: n.id, type: n.type, subtype: n.subtype, label: n.label, config: n.config, position: n.position };
                });

                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= url("workflows/" . $workflow["id"] . "/update") ?>';
                form.style.display = 'none';

                var fields = {
                    '_token': '<?= csrf_token() ?>',
                    'name': name,
                    'description': document.getElementById('wfDescription').value.trim(),
                    'trigger_type': triggerType,
                    'trigger_config': triggerNode ? JSON.stringify(triggerNode.config) : '{}',
                    'nodes': JSON.stringify(nodesData),
                    'edges': JSON.stringify(edges)
                };

                for (var key in fields) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = fields[key];
                    form.appendChild(input);
                }

                document.body.appendChild(form);
                form.submit();
            });
        });
        </script>
