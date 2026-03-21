<?php $pageTitle = e($product['name']); ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0"><?= e($product['name']) ?></h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('products') ?>">Sản phẩm</a></li>
                <li class="breadcrumb-item active">Chi tiết</li>
            </ol>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Thông tin sản phẩm</h5>
                        <div>
                            <a href="<?= url('products/' . $product['id'] . '/edit') ?>" class="btn btn-sm btn-soft-primary"><i class="ri-pencil-line me-1"></i>Sửa</a>
                            <form method="POST" action="<?= url('products/' . $product['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Xác nhận xóa?')">
                                <?= csrf_field() ?><button class="btn btn-sm btn-soft-danger"><i class="ri-delete-bin-line me-1"></i>Xóa</button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-borderless mb-0">
                                <tbody>
                                    <tr><th width="150">Tên</th><td><?= e($product['name']) ?></td></tr>
                                    <tr><th>SKU</th><td><code><?= e($product['sku'] ?? '-') ?></code></td></tr>
                                    <tr><th>Loại</th><td><?= $product['type'] === 'service' ? '<span class="badge bg-info">Dịch vụ</span>' : '<span class="badge bg-primary">Sản phẩm</span>' ?></td></tr>
                                    <tr><th>Danh mục</th><td><?= e($product['category_name'] ?? '-') ?></td></tr>
                                    <tr><th>Đơn vị</th><td><?= e($product['unit']) ?></td></tr>
                                    <tr><th>Mô tả</th><td><?= nl2br(e($product['description'] ?? '-')) ?></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Order history -->
                <?php if (!empty($orderItems)): ?>
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Lịch sử đơn hàng</h5></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mã đơn</th>
                                        <th>Loại</th>
                                        <th>SL</th>
                                        <th>Đơn giá</th>
                                        <th>Thành tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orderItems as $oi): ?>
                                    <tr>
                                        <td><a href="<?= url('orders/' . $oi['order_id']) ?>"><?= e($oi['order_number']) ?></a></td>
                                        <td><?= $oi['order_type'] === 'quote' ? 'Báo giá' : 'Đơn hàng' ?></td>
                                        <td><?= $oi['quantity'] ?></td>
                                        <td><?= format_money($oi['unit_price']) ?></td>
                                        <td><?= format_money($oi['total']) ?></td>
                                        <td>
                                            <?php
                                            $sc = ['draft'=>'secondary','sent'=>'info','confirmed'=>'primary','processing'=>'warning','completed'=>'success','cancelled'=>'danger'];
                                            $sl = ['draft'=>'Nháp','sent'=>'Đã gửi','confirmed'=>'Đã xác nhận','processing'=>'Đang xử lý','completed'=>'Hoàn thành','cancelled'=>'Đã hủy'];
                                            ?>
                                            <span class="badge bg-<?= $sc[$oi['order_status']] ?? 'secondary' ?>"><?= $sl[$oi['order_status']] ?? '' ?></span>
                                        </td>
                                        <td><?= format_date($oi['order_date']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Giá & Kho</h5></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Đơn giá bán</span>
                            <span class="fw-semibold text-primary fs-5"><?= format_money($product['price']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Giá vốn</span>
                            <span><?= format_money($product['cost_price']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Thuế VAT</span>
                            <span><?= $product['tax_rate'] ?>%</span>
                        </div>
                        <?php if ($product['type'] === 'product'): ?>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Tồn kho</span>
                            <span class="fw-semibold <?= $product['stock_quantity'] <= $product['min_stock'] ? 'text-danger' : 'text-success' ?>">
                                <?= $product['stock_quantity'] ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Tồn tối thiểu</span>
                            <span><?= $product['min_stock'] ?></span>
                        </div>
                        <?php endif; ?>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Trạng thái</span>
                            <?php if ($product['is_active']): ?>
                                <span class="badge bg-success">Hoạt động</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Ngừng</span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Ngày tạo</span>
                            <span><?= format_date($product['created_at']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Người tạo</span>
                            <span><?= e($product['created_by_name'] ?? '-') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
