<?php $pageTitle = 'Báo cáo'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Báo cáo</h4>
        </div>

        <div class="row">
            <div class="col-md-4">
                <a href="<?= url('reports/customers') ?>" class="text-decoration-none">
                    <div class="card card-hover">
                        <div class="card-body text-center py-4">
                            <div class="avatar-md bg-primary-subtle rounded mx-auto mb-3 d-flex align-items-center justify-content-center">
                                <i class="ri-contacts-line text-primary fs-2"></i>
                            </div>
                            <h5 class="mb-1">Báo cáo khách hàng</h5>
                            <p class="text-muted mb-0">Thống kê khách hàng theo nguồn, trạng thái, người phụ trách</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="<?= url('reports/revenue') ?>" class="text-decoration-none">
                    <div class="card card-hover">
                        <div class="card-body text-center py-4">
                            <div class="avatar-md bg-success-subtle rounded mx-auto mb-3 d-flex align-items-center justify-content-center">
                                <i class="ri-money-dollar-circle-line text-success fs-2"></i>
                            </div>
                            <h5 class="mb-1">Báo cáo doanh thu</h5>
                            <p class="text-muted mb-0">Thống kê doanh thu theo Deal, đơn hàng, nhân viên</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
