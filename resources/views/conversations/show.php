<?php $pageTitle = 'Cuộc hội thoại'; ?>

        <?php
            $channelColors = ['email' => 'info', 'zalo' => 'success', 'facebook' => 'primary', 'sms' => 'warning', 'livechat' => 'danger'];
            $channelLabels = ['email' => 'Email', 'zalo' => 'Zalo', 'facebook' => 'Facebook', 'sms' => 'SMS', 'livechat' => 'Live Chat'];
            $statusColors = ['open' => 'info', 'pending' => 'warning', 'resolved' => 'success', 'closed' => 'secondary'];
            $statusLabels = ['open' => 'Mở', 'pending' => 'Chờ', 'resolved' => 'Đã xử lý', 'closed' => 'Đóng'];
            $contactName = trim($conversation['contact_name'] ?? '');
            if (empty($contactName)) $contactName = 'Không rõ';
        ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Cuộc hội thoại</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('conversations') ?>">Hộp thư</a></li>
                <li class="breadcrumb-item active"><?= e($contactName) ?></li>
            </ol>
        </div>

        <div class="row">
            <!-- Main conversation -->
            <div class="col-lg-8">
                <div class="card">
                    <!-- Header -->
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-title rounded-circle bg-<?= $channelColors[$conversation['channel']] ?? 'secondary' ?>-subtle text-<?= $channelColors[$conversation['channel']] ?? 'secondary' ?>" style="width:40px;height:40px;font-size:16px;display:flex;align-items:center;justify-content:center;">
                                    <?= mb_strtoupper(mb_substr($contactName, 0, 1)) ?>
                                </div>
                                <div>
                                    <h5 class="mb-0"><?= e($contactName) ?></h5>
                                    <?php if (!empty($conversation['subject'])): ?>
                                        <small class="text-muted"><?= e($conversation['subject']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <!-- Star toggle -->
                                <form method="POST" action="<?= url('conversations/' . $conversation['id'] . '/star') ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-ghost-warning p-1" title="Đánh dấu">
                                        <i class="<?= $conversation['is_starred'] ? 'ri-star-fill' : 'ri-star-line' ?> fs-5"></i>
                                    </button>
                                </form>

                                <span class="badge bg-<?= $channelColors[$conversation['channel']] ?? 'secondary' ?>">
                                    <?= $channelLabels[$conversation['channel']] ?? $conversation['channel'] ?>
                                </span>
                                <span class="badge bg-<?= $statusColors[$conversation['status']] ?? 'secondary' ?>">
                                    <?= $statusLabels[$conversation['status']] ?? $conversation['status'] ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Message area -->
                    <div class="card-body p-0">
                        <div data-simplebar style="max-height: 500px; padding: 1rem;" id="messageArea">
                            <?php if (!empty($messages)): ?>
                                <?php foreach ($messages as $msg): ?>
                                    <?php $isOutbound = ($msg['direction'] === 'outbound'); ?>
                                    <div class="d-flex mb-3 <?= $isOutbound ? 'justify-content-end' : 'justify-content-start' ?>">
                                        <div class="<?= $isOutbound ? 'bg-primary-subtle' : 'bg-light' ?> rounded p-3" style="max-width:70%;">
                                            <div class="mb-1">
                                                <small class="fw-medium <?= $isOutbound ? 'text-primary' : 'text-dark' ?>">
                                                    <?= $isOutbound ? e($msg['sender_name'] ?? 'Bạn') : e($contactName) ?>
                                                </small>
                                            </div>
                                            <div style="white-space: pre-wrap;"><?= e($msg['content']) ?></div>
                                            <div class="mt-1">
                                                <small class="text-muted"><?= time_ago($msg['created_at']) ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="ri-chat-3-line fs-1 d-block mb-2"></i>
                                    Chưa có tin nhắn
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Reply form -->
                    <div class="card-footer">
                        <form method="POST" action="<?= url('conversations/' . $conversation['id'] . '/reply') ?>">
                            <?= csrf_field() ?>
                            <div class="mb-2">
                                <textarea name="content" class="form-control" rows="3" placeholder="Nhập tin nhắn..." required id="replyContent"></textarea>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex gap-2">
                                    <?php if (!empty($cannedResponses)): ?>
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-soft-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                <i class="ri-file-text-line me-1"></i> Mẫu trả lời
                                            </button>
                                            <ul class="dropdown-menu" style="max-height:300px;overflow-y:auto;">
                                                <?php foreach ($cannedResponses as $cr): ?>
                                                    <li>
                                                        <a class="dropdown-item canned-response-item" href="javascript:void(0)" data-content="<?= e($cr['content']) ?>">
                                                            <?= e($cr['title']) ?>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-send-plane-line me-1"></i> Gửi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar: info & actions -->
            <div class="col-lg-4">
                <!-- Contact info -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Thông tin khách hàng</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted">Tên</small>
                            <div class="fw-medium"><?= e($contactName) ?></div>
                        </div>
                        <?php if (!empty($conversation['contact_email'])): ?>
                            <div class="mb-2">
                                <small class="text-muted">Email</small>
                                <div><?= e($conversation['contact_email']) ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($conversation['contact_phone'])): ?>
                            <div class="mb-2">
                                <small class="text-muted">Điện thoại</small>
                                <div><?= e($conversation['contact_phone']) ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($conversation['cid'])): ?>
                            <a href="<?= url('contacts/' . $conversation['cid']) ?>" class="btn btn-soft-primary w-100 mt-2">
                                <i class="ri-user-line me-1"></i> Xem hồ sơ khách hàng
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Assign -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Phụ trách</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('conversations/' . $conversation['id'] . '/assign') ?>">
                            <?= csrf_field() ?>
                            <select name="assigned_to" class="form-select mb-2">
                                <option value="">-- Chưa gán --</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>" <?= ($conversation['assigned_to'] ?? 0) == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-soft-primary w-100">Cập nhật</button>
                        </form>
                    </div>
                </div>

                <!-- Status -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Trạng thái</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('conversations/' . $conversation['id'] . '/status') ?>">
                            <?= csrf_field() ?>
                            <select name="status" class="form-select mb-2">
                                <?php foreach ($statusLabels as $sv => $sl): ?>
                                    <option value="<?= $sv ?>" <?= $conversation['status'] === $sv ? 'selected' : '' ?>><?= $sl ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-soft-primary w-100">Cập nhật</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Scroll message area to bottom
            var msgArea = document.getElementById('messageArea');
            if (msgArea) {
                var simplebar = SimpleBar.instances.get(msgArea);
                if (simplebar) {
                    simplebar.getScrollElement().scrollTop = simplebar.getScrollElement().scrollHeight;
                }
            }

            // Canned responses
            document.querySelectorAll('.canned-response-item').forEach(function(el) {
                el.addEventListener('click', function() {
                    var textarea = document.getElementById('replyContent');
                    if (textarea) {
                        textarea.value = this.getAttribute('data-content');
                        textarea.focus();
                    }
                });
            });
        });
        </script>
