<?php $pageTitle = 'Tạo cuộc hội thoại'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Tạo cuộc hội thoại</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('chat') ?>">Chat</a></li>
                <li class="breadcrumb-item active">Tạo mới</li>
            </ol>
        </div>

        <form method="POST" action="<?= url('chat/store') ?>">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Thông tin cuộc hội thoại</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Khách hàng</label>
                                    <select name="contact_id" class="form-select searchable-select">
                                        <option value="">-- Chọn khách hàng --</option>
                                        <?php foreach ($contacts as $c): ?>
                                            <option value="<?= $c['id'] ?>" <?= ($selectedContactId ?? 0) == $c['id'] ? 'selected' : '' ?>>
                                                <?= e($c['first_name'] . ' ' . $c['last_name']) ?>
                                                <?= !empty($c['email']) ? '(' . e($c['email']) . ')' : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Kênh</label>
                                    <select name="channel" class="form-select">
                                        <option value="email">Email</option>
                                        <option value="zalo">Zalo</option>
                                        <option value="facebook">Facebook</option>
                                        <option value="sms">SMS</option>
                                        <option value="livechat">Live Chat</option>
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Chủ đề</label>
                                    <input type="text" class="form-control" name="subject" placeholder="Chủ đề cuộc hội thoại...">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Tin nhắn đầu tiên <span class="text-danger">*</span></label>
                                    <textarea name="content" class="form-control" rows="5" placeholder="Nhập nội dung tin nhắn..." required></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <a href="<?= url('chat') ?>" class="btn btn-soft-secondary me-2">Hủy</a>
                            <button type="submit" class="btn btn-primary"><i class="ri-send-plane-line me-1"></i> Tạo và gửi</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
