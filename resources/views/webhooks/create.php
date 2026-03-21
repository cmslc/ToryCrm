<?php $pageTitle = 'Thêm Webhook'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Thêm Webhook</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('webhooks') ?>">Webhook</a></li>
                <li class="breadcrumb-item active">Thêm mới</li>
            </ol>
        </div>

        <form method="POST" action="<?= url('webhooks/store') ?>">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Cấu hình Webhook</h5></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" required placeholder="VD: Sync ERP">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">URL nhận webhook <span class="text-danger">*</span></label>
                                <input type="url" class="form-control" name="url" required placeholder="https://your-server.com/webhook">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Secret Key</label>
                                <input type="text" class="form-control" name="secret_key" placeholder="Để trống sẽ tự tạo">
                                <small class="text-muted">Dùng để xác thực request từ ToryCRM</small>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Events <span class="text-danger">*</span></h5></div>
                        <div class="card-body">
                            <div class="row">
                                <?php
                                $groups = [
                                    'Khách hàng' => ['customer.created', 'customer.updated', 'customer.deleted'],
                                    'Đơn hàng' => ['order.created', 'order.approved'],
                                    'Sản phẩm' => ['product.created', 'product.updated'],
                                    'Chiến dịch' => ['campaign.created', 'campaign.updated'],
                                    'Cơ hội' => ['opportunity.created', 'opportunity.updated'],
                                    'Công việc' => ['task.created', 'task.updated'],
                                    'Ticket' => ['ticket.created', 'ticket.updated'],
                                ];
                                foreach ($groups as $group => $evts): ?>
                                <div class="col-md-4 mb-3">
                                    <h6 class="text-muted"><?= $group ?></h6>
                                    <?php foreach ($evts as $evt): ?>
                                    <div class="form-check mb-1">
                                        <input class="form-check-input" type="checkbox" name="events[]" value="<?= $evt ?>" id="evt_<?= str_replace('.', '_', $evt) ?>">
                                        <label class="form-check-label" for="evt_<?= str_replace('.', '_', $evt) ?>">
                                            <code class="small"><?= $evt ?></code>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-sm btn-soft-primary mt-2" onclick="document.querySelectorAll('[name=\'events[]\']').forEach(c=>c.checked=true)">Chọn tất cả</button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-save-line me-1"></i> Lưu</button>
                            <a href="<?= url('webhooks') ?>" class="btn btn-soft-secondary">Hủy</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
