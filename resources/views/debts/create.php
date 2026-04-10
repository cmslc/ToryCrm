<?php $pageTitle = 'Tạo công nợ'; ?>

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Tạo công nợ</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?= url('debts') ?>">Công nợ</a></li>
                            <li class="breadcrumb-item active">Tạo mới</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="<?= url('debts/store') ?>">
            <?= csrf_field() ?>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Thông tin công nợ</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Loại công nợ <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select" required>
                                        <option value="receivable">Phải thu (Khách nợ mình)</option>
                                        <option value="payable">Phải trả (Mình nợ khách)</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số tiền <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="amount" required min="0" step="any" placeholder="Nhập số tiền">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày đến hạn</label>
                                    <input type="date" class="form-control" name="due_date" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Đơn hàng liên quan</label>
                                    <select name="order_id" class="form-select">
                                        <option value="">Không liên kết</option>
                                        <?php foreach ($orders ?? [] as $o): ?>
                                            <option value="<?= $o['id'] ?>"><?= e($o['order_number']) ?> - <?= format_money($o['total']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Ghi chú</label>
                                    <textarea name="note" class="form-control" rows="3" placeholder="Ghi chú về công nợ..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Đối tượng</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Khách hàng</label>
                                <select name="contact_id" class="form-select searchable-select">
                                    <option value="">Chọn khách hàng</option>
                                    <?php foreach ($contacts ?? [] as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= e(trim($c['first_name'] . ' ' . $c['last_name'])) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Công ty</label>
                                <select name="company_id" class="form-select searchable-select">
                                    <option value="">Chọn công ty</option>
                                    <?php foreach ($companies ?? [] as $co): ?>
                                        <option value="<?= $co['id'] ?>"><?= e($co['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-check-line me-1"></i> Tạo công nợ</button>
                        <a href="<?= url('debts') ?>" class="btn btn-soft-secondary">Hủy</a>
                    </div>
                </div>
            </div>
        </form>
