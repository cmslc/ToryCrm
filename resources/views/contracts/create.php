<?php $pageTitle = 'Tạo hợp đồng'; ?>

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Tạo hợp đồng</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?= url('contracts') ?>">Hợp đồng</a></li>
                            <li class="breadcrumb-item active">Tạo mới</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="<?= url('contracts/store') ?>">
            <?= csrf_field() ?>

            <div class="row">
                <!-- Left Column -->
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Thông tin hợp đồng</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số hợp đồng</label>
                                    <input type="text" class="form-control" value="<?= e($contractNumber ?? '') ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="title" required placeholder="Tiêu đề hợp đồng">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Loại hợp đồng</label>
                                    <select name="type" class="form-select">
                                        <option value="service">Dịch vụ</option>
                                        <option value="product">Sản phẩm</option>
                                        <option value="rental">Cho thuê</option>
                                        <option value="maintenance">Bảo trì</option>
                                        <option value="other">Khác</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Giá trị hợp đồng</label>
                                    <input type="number" class="form-control" name="value" min="0" step="any" placeholder="0">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Giá trị định kỳ</label>
                                    <input type="number" class="form-control" name="recurring_value" min="0" step="any" placeholder="0">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Chu kỳ</label>
                                    <select name="recurring_cycle" class="form-select">
                                        <option value="">Không</option>
                                        <option value="monthly">Hàng tháng</option>
                                        <option value="quarterly">Hàng quý</option>
                                        <option value="yearly">Hàng năm</option>
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Ghi chú</label>
                                    <textarea name="notes" class="form-control" rows="3" placeholder="Ghi chú hợp đồng..."></textarea>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Điều khoản</label>
                                    <textarea name="terms" class="form-control" rows="4" placeholder="Điều khoản hợp đồng..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Đối tượng & Thời hạn</h5>
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
                            <div class="mb-3">
                                <label class="form-label">Cơ hội liên quan</label>
                                <select name="deal_id" class="form-select">
                                    <option value="">Chọn cơ hội</option>
                                    <?php foreach ($deals ?? [] as $d): ?>
                                        <option value="<?= $d['id'] ?>"><?= e($d['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày bắt đầu</label>
                                    <input type="date" class="form-control" name="start_date" value="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày kết thúc</label>
                                    <input type="date" class="form-control" name="end_date" value="<?= date('Y-m-d', strtotime('+1 year')) ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="auto_renew" id="autoRenew" value="1">
                                    <label class="form-check-label" for="autoRenew">Tự động gia hạn</label>
                                </div>
                            </div>
                            <?php
                            $deptGrouped = [];
                            foreach ($users ?? [] as $u) { $deptGrouped[$u['dept_name'] ?? 'Chưa phân phòng'][] = $u; }
                            ?>
                            <div class="mb-3">
                                <label class="form-label">Người phụ trách</label>
                                <select name="owner_id" class="form-select searchable-select">
                                    <option value="">Chọn người phụ trách</option>
                                    <?php foreach ($deptGrouped as $dept => $dUsers): ?>
                                    <optgroup label="<?= e($dept) ?>">
                                        <?php foreach ($dUsers as $u): ?>
                                        <option value="<?= $u['id'] ?>" <?= ($u['id'] ?? 0) == ($_SESSION['user']['id'] ?? 0) ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-check-line me-1"></i> Tạo hợp đồng</button>
                        <a href="<?= url('contracts') ?>" class="btn btn-soft-secondary">Hủy</a>
                    </div>
                </div>
            </div>
        </form>
