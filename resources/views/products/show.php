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
                            <a href="<?= url('products/' . $product['id'] . '/edit') ?>" class="btn btn-soft-primary"><i class="ri-pencil-line me-1"></i>Sửa</a>
                            <form method="POST" action="<?= url('products/' . $product['id'] . '/delete') ?>" class="d-inline" data-confirm="Xác nhận xóa?">
                                <?= csrf_field() ?><button class="btn btn-soft-danger"><i class="ri-delete-bin-line me-1"></i>Xóa</button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php if (!empty($product['image'])): ?>
                            <div class="col-md-4 text-center mb-3">
                                <img src="<?= e(product_image_url($product['image'])) ?>" class="img-fluid rounded border" style="max-height:260px" alt="">
                            </div>
                            <div class="col-md-8">
                            <?php else: ?>
                            <div class="col-12">
                            <?php endif; ?>
                                <table class="table table-borderless mb-0">
                                    <tbody>
                                        <tr><th width="160">Tên</th><td class="fw-semibold"><?= e($product['name']) ?></td></tr>
                                        <tr><th>SKU</th><td><code><?= e($product['sku'] ?? '-') ?></code></td></tr>
                                        <?php if (!empty($product['barcode'])): ?>
                                        <tr><th>Mã vạch</th><td><code><?= e($product['barcode']) ?></code></td></tr>
                                        <?php endif; ?>
                                        <tr><th>Loại</th><td><?= $product['type'] === 'service' ? '<span class="badge bg-info-subtle text-info">Dịch vụ</span>' : '<span class="badge bg-primary-subtle text-primary">Sản phẩm</span>' ?></td></tr>
                                        <tr><th>Danh mục</th><td><?= e($product['category_name'] ?? '-') ?></td></tr>
                                        <tr><th>Nhà sản xuất</th><td><?= e($product['manufacturer_name'] ?? '-') ?></td></tr>
                                        <tr><th>Xuất xứ</th><td><?= e($product['origin_name'] ?? '-') ?></td></tr>
                                        <tr><th>Đơn vị tính</th><td><?= e($product['unit'] ?? '-') ?></td></tr>
                                        <?php if ($product['weight'] !== null): ?>
                                        <tr><th>Khối lượng</th><td><?= number_format((float)$product['weight'], 3, ',', '.') ?> kg</td></tr>
                                        <?php endif; ?>
                                        <?php if (!empty($product['dimensions'])): ?>
                                        <tr><th>Kích thước</th><td><?= e($product['dimensions']) ?></td></tr>
                                        <?php endif; ?>
                                        <?php if (!empty($product['color'])): ?>
                                        <tr><th>Màu sắc</th><td><?= e($product['color']) ?></td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <?php if (!empty($product['short_description'])): ?>
                        <hr>
                        <div class="mb-2"><strong>Mô tả ngắn</strong></div>
                        <p class="text-muted mb-0"><?= nl2br(e($product['short_description'])) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($product['description'])): ?>
                        <hr>
                        <div class="mb-2"><strong>Mô tả chi tiết</strong></div>
                        <div class="text-muted"><?= nl2br(e($product['description'])) ?></div>
                        <?php endif; ?>
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
                    <div class="card-header"><h5 class="card-title mb-0"><i class="ri-price-tag-3-line me-1"></i>Giá bán</h5></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Đơn giá bán</span>
                            <span class="fw-semibold text-primary fs-5"><?= format_money($product['price']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Giá vốn</span>
                            <span><?= (float)$product['cost_price'] > 0 ? format_money($product['cost_price']) : '-' ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Giá sỉ</span>
                            <span><?= (float)($product['price_wholesale'] ?? 0) > 0 ? format_money($product['price_wholesale']) : '-' ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Giá online</span>
                            <span><?= (float)($product['price_online'] ?? 0) > 0 ? format_money($product['price_online']) : '-' ?></span>
                        </div>
                        <?php if ((float)($product['saleoff_price'] ?? 0) > 0): ?>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Giá khuyến mãi</span>
                            <span class="text-danger fw-semibold"><?= format_money($product['saleoff_price']) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ((float)($product['discount_percent'] ?? 0) > 0): ?>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Giảm giá</span>
                            <span><?= rtrim(rtrim(number_format((float)$product['discount_percent'], 2, ',', '.'), '0'), ',') ?>%</span>
                        </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between mb-0">
                            <span class="text-muted">Thuế VAT</span>
                            <span><?= rtrim(rtrim(number_format((float)$product['tax_rate'], 2, ',', '.'), '0'), ',') ?>%</span>
                        </div>
                    </div>
                </div>

                <?php if ($product['type'] === 'product'): ?>
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="ri-archive-line me-1"></i>Kho</h5></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Tồn kho</span>
                            <span class="fw-semibold <?= $product['stock_quantity'] <= $product['min_stock'] ? 'text-danger' : 'text-success' ?>">
                                <?= (int)$product['stock_quantity'] ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-0">
                            <span class="text-muted">Tồn tối thiểu</span>
                            <span><?= (int)$product['min_stock'] ?></span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="ri-information-line me-1"></i>Thông tin khác</h5></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Trạng thái</span>
                            <?php if ($product['is_active']): ?>
                                <span class="badge bg-success">Hoạt động</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Ngừng</span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($product['getfly_id'])): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Nguồn</span>
                            <span class="badge bg-info-subtle text-info" title="Getfly ID: <?= (int)$product['getfly_id'] ?>">
                                <i class="ri-refresh-line me-1"></i>Getfly #<?= (int)$product['getfly_id'] ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Ngày tạo</span>
                            <span><?= format_date($product['created_at']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-0">
                            <span class="text-muted">Người tạo</span>
                            <span><?= e($product['created_by_name'] ?? '-') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
