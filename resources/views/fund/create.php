<?php $pageTitle = ($type ?? 'receipt') === 'receipt' ? 'Tạo phiếu thu' : 'Tạo phiếu chi'; ?>

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0"><?= $pageTitle ?></h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?= url('fund') ?>">Quỹ</a></li>
                            <li class="breadcrumb-item active"><?= $pageTitle ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="<?= url('fund/store') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="type" value="<?= e($type ?? 'receipt') ?>">

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Thông tin phiếu</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mã phiếu</label>
                                    <input type="text" class="form-control" name="transaction_code" value="<?= e($transactionCode ?? '') ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số tiền <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="amount" value="<?= e($old['amount'] ?? '') ?>" required min="0" step="any">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tài khoản quỹ</label>
                                    <select name="fund_account_id" class="form-select">
                                        <option value="">Chọn quỹ</option>
                                        <?php foreach ($accounts ?? [] as $account): ?>
                                            <option value="<?= $account['id'] ?>" <?= ($old['fund_account_id'] ?? '') == $account['id'] ? 'selected' : '' ?>><?= e($account['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Danh mục</label>
                                    <input type="text" class="form-control" name="category" value="<?= e($old['category'] ?? '') ?>" placeholder="Nhập danh mục">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày giao dịch</label>
                                    <input type="date" class="form-control" name="transaction_date" value="<?= e($old['transaction_date'] ?? date('Y-m-d')) ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Trạng thái</label>
                                    <select name="status" class="form-select">
                                        <option value="draft" <?= ($old['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Nháp</option>
                                        <option value="confirmed" <?= ($old['status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Mô tả</label>
                                    <textarea name="description" class="form-control" rows="3" placeholder="Mô tả giao dịch..."><?= e($old['description'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Đối tượng liên quan</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Liên hệ</label>
                                <select name="contact_id" class="form-select">
                                    <option value="">Chọn liên hệ</option>
                                    <?php foreach ($contacts ?? [] as $contact): ?>
                                        <option value="<?= $contact['id'] ?>" <?= ($old['contact_id'] ?? '') == $contact['id'] ? 'selected' : '' ?>><?= e($contact['first_name'] . ' ' . ($contact['last_name'] ?? '')) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Công ty</label>
                                <select name="company_id" class="form-select">
                                    <option value="">Chọn công ty</option>
                                    <?php foreach ($companies ?? [] as $company): ?>
                                        <option value="<?= $company['id'] ?>" <?= ($old['company_id'] ?? '') == $company['id'] ? 'selected' : '' ?>><?= e($company['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1">
                                    <i class="ri-save-line me-1"></i> Lưu
                                </button>
                                <a href="<?= url('fund') ?>" class="btn btn-soft-secondary">Hủy</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
