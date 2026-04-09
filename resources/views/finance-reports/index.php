<?php $pageTitle = 'Báo cáo tài chính'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Báo cáo tài chính</h4>
        </div>

        <!-- Top Metrics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-success-subtle rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-arrow-up-circle-line text-success fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Doanh thu tháng</p>
                                <h4 class="mb-0 text-success"><?= format_money($totalRevenue ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-danger">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-danger-subtle rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-arrow-down-circle-line text-danger fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Chi phí tháng</p>
                                <h4 class="mb-0 text-danger"><?= format_money($totalExpense ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-primary-subtle rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-line-chart-line text-primary fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Lợi nhuận ròng</p>
                                <h4 class="mb-0 <?= ($netProfit ?? 0) >= 0 ? 'text-primary' : 'text-danger' ?>"><?= format_money($netProfit ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-warning-subtle rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-money-dollar-circle-line text-warning fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Công nợ phải thu</p>
                                <h4 class="mb-0 text-warning"><?= format_money($receivables ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="row">
            <div class="col-md-4">
                <a href="<?= url('finance-reports/profit-loss') ?>" class="card card-body text-decoration-none">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-primary-subtle rounded me-3 d-flex align-items-center justify-content-center">
                            <i class="ri-file-chart-line text-primary fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Lãi / Lỗ (P&L)</h5>
                            <p class="text-muted mb-0">Doanh thu, chi phí, lợi nhuận theo kỳ</p>
                        </div>
                        <i class="ri-arrow-right-s-line fs-4 ms-auto text-muted"></i>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="<?= url('finance-reports/cash-flow') ?>" class="card card-body text-decoration-none">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-success-subtle rounded me-3 d-flex align-items-center justify-content-center">
                            <i class="ri-exchange-funds-line text-success fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Dòng tiền</h5>
                            <p class="text-muted mb-0">Thu chi theo tháng, số dư luỹ kế</p>
                        </div>
                        <i class="ri-arrow-right-s-line fs-4 ms-auto text-muted"></i>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="<?= url('finance-reports/aging') ?>" class="card card-body text-decoration-none">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-warning-subtle rounded me-3 d-flex align-items-center justify-content-center">
                            <i class="ri-timer-line text-warning fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Tuổi nợ</h5>
                            <p class="text-muted mb-0">Phân tích công nợ phải thu theo độ tuổi</p>
                        </div>
                        <i class="ri-arrow-right-s-line fs-4 ms-auto text-muted"></i>
                    </div>
                </a>
            </div>
        </div>
