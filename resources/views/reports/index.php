<?php $pageTitle = 'Báo cáo'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Báo cáo</h4>
        </div>

        <div class="row">
            <div class="col-md-4 col-lg-3">
                <a href="<?= url('reports/customers') ?>" class="text-decoration-none">
                    <div class="card card-hover">
                        <div class="card-body text-center py-4">
                            <div class="avatar-md bg-primary-subtle rounded mx-auto mb-3 d-flex align-items-center justify-content-center">
                                <i class="ri-contacts-line text-primary fs-2"></i>
                            </div>
                            <h5 class="mb-1">Khách hàng</h5>
                            <p class="text-muted mb-0 small">Nguồn, trạng thái, người phụ trách, tỉnh/thành</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 col-lg-3">
                <a href="<?= url('reports/revenue') ?>" class="text-decoration-none">
                    <div class="card card-hover">
                        <div class="card-body text-center py-4">
                            <div class="avatar-md bg-success-subtle rounded mx-auto mb-3 d-flex align-items-center justify-content-center">
                                <i class="ri-money-dollar-circle-line text-success fs-2"></i>
                            </div>
                            <h5 class="mb-1">Doanh thu</h5>
                            <p class="text-muted mb-0 small">Deal, đơn hàng, nhân viên, so sánh năm</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 col-lg-3">
                <a href="<?= url('reports/deals') ?>" class="text-decoration-none">
                    <div class="card card-hover">
                        <div class="card-body text-center py-4">
                            <div class="avatar-md bg-warning-subtle rounded mx-auto mb-3 d-flex align-items-center justify-content-center">
                                <i class="ri-hand-coin-line text-warning fs-2"></i>
                            </div>
                            <h5 class="mb-1">Cơ hội</h5>
                            <p class="text-muted mb-0 small">Pipeline, tỷ lệ chuyển đổi, thời gian close</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 col-lg-3">
                <a href="<?= url('reports/orders') ?>" class="text-decoration-none">
                    <div class="card card-hover">
                        <div class="card-body text-center py-4">
                            <div class="avatar-md bg-info-subtle rounded mx-auto mb-3 d-flex align-items-center justify-content-center">
                                <i class="ri-shopping-bag-line text-info fs-2"></i>
                            </div>
                            <h5 class="mb-1">Đơn hàng</h5>
                            <p class="text-muted mb-0 small">Trạng thái, sản phẩm bán chạy, doanh số</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 col-lg-3">
                <a href="<?= url('reports/tasks') ?>" class="text-decoration-none">
                    <div class="card card-hover">
                        <div class="card-body text-center py-4">
                            <div class="avatar-md bg-secondary-subtle rounded mx-auto mb-3 d-flex align-items-center justify-content-center">
                                <i class="ri-task-line text-secondary fs-2"></i>
                            </div>
                            <h5 class="mb-1">Công việc</h5>
                            <p class="text-muted mb-0 small">Hoàn thành, quá hạn, năng suất nhân viên</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 col-lg-3">
                <a href="<?= url('reports/staff') ?>" class="text-decoration-none">
                    <div class="card card-hover">
                        <div class="card-body text-center py-4">
                            <div class="avatar-md bg-danger-subtle rounded mx-auto mb-3 d-flex align-items-center justify-content-center">
                                <i class="ri-team-line text-danger fs-2"></i>
                            </div>
                            <h5 class="mb-1">Nhân viên</h5>
                            <p class="text-muted mb-0 small">Hiệu suất bán hàng, hoa hồng, hoạt động</p>
                        </div>
                    </div>
                </a>
            </div>
            <?php if (($_role ?? '') !== 'staff'): ?>
            <div class="col-md-4 col-lg-3">
                <a href="<?= url('finance-reports') ?>" class="text-decoration-none">
                    <div class="card card-hover">
                        <div class="card-body text-center py-4">
                            <div class="avatar-md bg-dark-subtle rounded mx-auto mb-3 d-flex align-items-center justify-content-center">
                                <i class="ri-line-chart-line text-dark fs-2"></i>
                            </div>
                            <h5 class="mb-1">Tài chính</h5>
                            <p class="text-muted mb-0 small">Lãi/lỗ, dòng tiền, công nợ, tuổi nợ</p>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>
        </div>
