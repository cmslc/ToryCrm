<?php
$isReceipt = $transaction['type'] === 'receipt';
$pageTitle = 'Sửa ' . ($isReceipt ? 'phiếu thu' : 'phiếu chi') . ' ' . $transaction['transaction_code'];
?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0"><?= $pageTitle ?></h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('fund') ?>">Quỹ</a></li>
                <li class="breadcrumb-item active">Sửa</li>
            </ol>
        </div>

        <form method="POST" action="<?= url('fund/' . $transaction['id'] . '/update') ?>">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Thông tin phiếu</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mã phiếu</label>
                                    <input type="text" class="form-control" value="<?= e($transaction['transaction_code']) ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số tiền <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="amount" value="<?= $transaction['amount'] ?>" required min="0" step="any">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tài khoản quỹ</label>
                                    <select name="fund_account_id" class="form-select">
                                        <option value="">Chọn quỹ</option>
                                        <?php foreach ($accounts ?? [] as $acc): ?>
                                            <option value="<?= $acc['id'] ?>" <?= ($transaction['fund_account_id'] ?? '') == $acc['id'] ? 'selected' : '' ?>><?= e($acc['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Danh mục</label>
                                    <input type="text" class="form-control" name="category" value="<?= e($transaction['category'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày giao dịch</label>
                                    <input type="date" class="form-control" name="transaction_date" value="<?= $transaction['transaction_date'] ?>">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Mô tả</label>
                                    <textarea name="description" class="form-control" rows="3"><?= e($transaction['description'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Đối tượng liên quan</h5></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Khách hàng</label>
                                <select name="contact_id" class="form-select searchable-select">
                                    <option value="">Chọn</option>
                                    <?php foreach ($contacts ?? [] as $c): ?>
                                        <option value="<?= $c['id'] ?>" <?= ($transaction['contact_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['first_name'] . ' ' . ($c['last_name'] ?? '')) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Công ty</label>
                                <select name="company_id" class="form-select searchable-select">
                                    <option value="">Chọn</option>
                                    <?php foreach ($companies ?? [] as $comp): ?>
                                        <option value="<?= $comp['id'] ?>" <?= ($transaction['company_id'] ?? '') == $comp['id'] ? 'selected' : '' ?>><?= e($comp['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-save-line me-1"></i> Cập nhật</button>
                            <a href="<?= url('fund/' . $transaction['id']) ?>" class="btn btn-soft-secondary">Hủy</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
