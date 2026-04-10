<?php $pageTitle = e($deal['title']); ?>

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-0"><?= e($deal['title']) ?></h4>
                        <div class="mt-1">
                            <?php $dc = ['open'=>'primary','won'=>'success','lost'=>'danger']; $dl = ['open'=>'Đang mở','won'=>'Thắng','lost'=>'Thua']; ?>
                            <span class="badge bg-<?= $dc[$deal['status']] ?? 'secondary' ?> fs-12"><?= $dl[$deal['status']] ?? $deal['status'] ?></span>
                            <span class="badge" style="background-color:<?= safe_color($deal['stage_color'] ?? null) ?>"><?= e($deal['stage_name'] ?? '') ?></span>
                            <?php if ($deal['probability'] ?? false): ?>
                                <span class="badge bg-info-subtle text-info"><?= $deal['probability'] ?>% xác suất</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?= url('deals') ?>">Cơ hội</a></li>
                            <li class="breadcrumb-item active"><?= e($deal['title']) ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stage Timeline -->
        <?php if (!empty($stages)): ?>
        <div class="card">
            <div class="card-body py-3">
                <div class="d-flex align-items-center flex-wrap gap-1">
                    <?php foreach ($stages as $i => $stg): ?>
                        <?php
                        $isActive = ($deal['stage_id'] == $stg['id']);
                        $isPast = ($stg['sort_order'] < ($deal['stage_order'] ?? 0));
                        $bgClass = $isActive ? 'bg-primary text-white' : ($isPast ? 'bg-success text-white' : 'bg-light text-muted');
                        ?>
                        <div class="d-flex align-items-center">
                            <span class="badge <?= $bgClass ?> px-3 py-2 fs-12">
                                <?php if ($isPast): ?><i class="ri-check-line me-1"></i><?php endif; ?>
                                <?= e($stg['name']) ?>
                            </span>
                            <?php if ($i < count($stages) - 1): ?>
                                <i class="ri-arrow-right-s-line text-muted mx-1"></i>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Left Column -->
            <div class="col-xl-4">
                <!-- Deal Value & Actions -->
                <div class="card">
                    <div class="card-body">
                        <h3 class="text-primary mb-3"><?= format_money($deal['value']) ?></h3>
                        <div class="d-flex gap-2 mb-4 flex-wrap">
                            <a href="<?= url('deals/' . $deal['id'] . '/edit') ?>" class="btn btn-primary">
                                <i class="ri-pencil-line me-1"></i> Sửa
                            </a>
                            <?php if ($deal['status'] === 'open'): ?>
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#closeDealModal" onclick="setCloseStatus('won')">
                                    <i class="ri-trophy-line me-1"></i> Thắng
                                </button>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#closeDealModal" onclick="setCloseStatus('lost')">
                                    <i class="ri-close-circle-line me-1"></i> Thua
                                </button>
                            <?php endif; ?>
                            <form method="POST" action="<?= url('deals/' . $deal['id'] . '/delete') ?>" data-confirm="Xác nhận xóa cơ hội?">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-soft-danger"><i class="ri-delete-bin-line me-1"></i> Xóa</button>
                            </form>
                        </div>

                        <table class="table table-borderless mb-0">
                            <tr><th class="text-muted" width="40%"><i class="ri-bar-chart-line me-2"></i>Giai đoạn</th><td><span class="badge" style="background:<?= safe_color($deal['stage_color'] ?? null) ?>"><?= e($deal['stage_name'] ?? '') ?></span></td></tr>
                            <tr><th class="text-muted"><i class="ri-flag-line me-2"></i>Ưu tiên</th><td><?php $pl=['low'=>'Thấp','medium'=>'TB','high'=>'Cao','urgent'=>'Khẩn']; echo $pl[$deal['priority']] ?? ''; ?></td></tr>
                            <tr><th class="text-muted"><i class="ri-calendar-line me-2"></i>Ngày dự kiến</th><td><?= $deal['expected_close_date'] ? format_date($deal['expected_close_date']) : '-' ?></td></tr>
                            <tr><th class="text-muted"><i class="ri-user-star-line me-2"></i>Phụ trách</th><td><?= user_avatar($deal['owner_name'] ?? null) ?></td></tr>
                            <tr><th class="text-muted"><i class="ri-time-line me-2"></i>Ngày tạo</th><td><?= format_datetime($deal['created_at']) ?></td></tr>
                            <?php if ($deal['status'] !== 'open' && !empty($deal['close_reason'])): ?>
                                <tr><th class="text-muted"><i class="ri-information-line me-2"></i>Lý do đóng</th><td><?= e($deal['close_reason']) ?></td></tr>
                            <?php endif; ?>
                            <?php if ($deal['status'] === 'lost' && !empty($deal['loss_reason_category'])): ?>
                                <tr><th class="text-muted"><i class="ri-error-warning-line me-2"></i>Phân loại</th><td><?= e($deal['loss_reason_category']) ?></td></tr>
                            <?php endif; ?>
                            <?php if (!empty($deal['competitor'])): ?>
                                <tr><th class="text-muted"><i class="ri-team-line me-2"></i>Đối thủ</th><td><?= e($deal['competitor']) ?></td></tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <!-- Contact & Company -->
                <div class="card">
                    <div class="card-header p-2"><h5 class="card-title mb-0">Khách hàng / Công ty</h5></div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th class="text-muted" width="35%"><i class="ri-user-line me-2"></i>Liên hệ</th>
                                <td>
                                    <?php if ($deal['contact_id']): ?>
                                        <a href="<?= url('contacts/' . $deal['contact_id']) ?>"><?= e($deal['contact_first_name'] . ' ' . ($deal['contact_last_name'] ?? '')) ?></a>
                                        <?php if (!empty($deal['contact_email'])): ?>
                                            <br><small class="text-muted"><?= e($deal['contact_email']) ?></small>
                                        <?php endif; ?>
                                        <?php if (!empty($deal['contact_phone'])): ?>
                                            <br><small class="text-muted"><?= e($deal['contact_phone']) ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted"><i class="ri-building-line me-2"></i>Công ty</th>
                                <td>
                                    <?php if ($deal['company_id']): ?>
                                        <a href="<?= url('companies/' . $deal['company_id']) ?>"><?= e($deal['company_name']) ?></a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header p-2">
                        <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#tab-activities" role="tab">
                                    <i class="ri-chat-3-line me-1"></i> Hoạt động
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tab-products" role="tab">
                                    <i class="ri-shopping-bag-line me-1"></i> Sản phẩm
                                    <?php if (!empty($dealProducts)): ?><span class="badge bg-primary ms-1"><?= count($dealProducts) ?></span><?php endif; ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tab-orders" role="tab">
                                    <i class="ri-shopping-cart-line me-1"></i> Đơn hàng
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tab-notes" role="tab">
                                    <i class="ri-file-text-line me-1"></i> Ghi chú
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="tab-content">

                            <!-- Tab: Hoạt động -->
                            <div class="tab-pane active" id="tab-activities" role="tabpanel">
                                <!-- Compose Area -->
                                <form method="POST" action="<?= url('activities/store') ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="deal_id" value="<?= $deal['id'] ?>">
                                    <input type="hidden" name="type" value="note" id="activityType">
                                    <div class="mb-3">
                                        <textarea name="title" class="form-control" rows="3" placeholder="Nhập nội dung hoạt động, ghi chú..." required></textarea>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-soft-primary activity-type-btn active" data-type="note" title="Ghi chú">
                                                <i class="ri-file-text-line"></i>
                                            </button>
                                            <button type="button" class="btn btn-soft-success activity-type-btn" data-type="call" title="Cuộc gọi">
                                                <i class="ri-phone-line"></i>
                                            </button>
                                            <button type="button" class="btn btn-soft-info activity-type-btn" data-type="email" title="Email">
                                                <i class="ri-mail-line"></i>
                                            </button>
                                            <button type="button" class="btn btn-soft-warning activity-type-btn" data-type="meeting" title="Cuộc họp">
                                                <i class="ri-calendar-line"></i>
                                            </button>
                                        </div>
                                        <button type="submit" class="btn btn-primary"><i class="ri-send-plane-fill me-1"></i> Gửi</button>
                                    </div>
                                </form>

                                <hr>

                                <!-- Activity Timeline -->
                                <div class="activity-timeline" style="max-height: 500px; overflow-y: auto;">
                                    <?php if (!empty($activities)): ?>
                                        <?php
                                        $typeIcons = ['note' => 'ri-file-text-line', 'call' => 'ri-phone-line', 'email' => 'ri-mail-line', 'meeting' => 'ri-calendar-line', 'deal' => 'ri-hand-coin-line', 'system' => 'ri-settings-line'];
                                        $typeColors = ['note' => 'primary', 'call' => 'success', 'email' => 'info', 'meeting' => 'warning', 'deal' => 'primary', 'system' => 'secondary'];
                                        $typeLabels = ['note' => 'Ghi chú', 'call' => 'Cuộc gọi', 'email' => 'Email', 'meeting' => 'Cuộc họp', 'deal' => 'Cơ hội', 'system' => 'Hệ thống'];
                                        ?>
                                        <?php foreach ($activities as $act): ?>
                                            <div class="activity-item d-flex mb-3">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar-xs">
                                                        <div class="avatar-title rounded-circle bg-<?= $typeColors[$act['type']] ?? 'primary' ?>-subtle text-<?= $typeColors[$act['type']] ?? 'primary' ?>">
                                                            <i class="<?= $typeIcons[$act['type']] ?? 'ri-file-text-line' ?>"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <span class="badge bg-<?= $typeColors[$act['type']] ?? 'primary' ?>-subtle text-<?= $typeColors[$act['type']] ?? 'primary' ?> me-2"><?= $typeLabels[$act['type']] ?? $act['type'] ?></span>
                                                        <small class="text-muted"><?= time_ago($act['created_at']) ?></small>
                                                    </div>
                                                    <p class="mb-0"><?= e($act['title']) ?></p>
                                                    <?php if (!empty($act['description'])): ?>
                                                        <p class="text-muted mb-0 fs-12"><?= e($act['description']) ?></p>
                                                    <?php endif; ?>
                                                    <small class="text-muted"><i class="ri-user-line me-1"></i><?= e($act['user_name'] ?? 'Hệ thống') ?></small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="ri-chat-3-line fs-36 text-muted"></i>
                                            <p class="text-muted mt-2">Chưa có hoạt động</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Tab: Sản phẩm -->
                            <div class="tab-pane" id="tab-products" role="tabpanel">
                                <div class="d-flex justify-content-between mb-3">
                                    <h6 class="mb-0">Sản phẩm trong cơ hội</h6>
                                </div>

                                <!-- Add Product Form -->
                                <form method="POST" action="<?= url('deals/' . $deal['id'] . '/products') ?>" class="row g-2 mb-4 align-items-end">
                                    <?= csrf_field() ?>
                                    <div class="col-md-3">
                                        <label class="form-label">Sản phẩm</label>
                                        <select name="product_id" class="form-select" id="productSelect" required>
                                            <option value="">Chọn sản phẩm</option>
                                            <?php foreach ($products as $p): ?>
                                                <option value="<?= $p['id'] ?>" data-price="<?= $p['price'] ?>"><?= e($p['name']) ?> (<?= e($p['sku'] ?? '') ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Số lượng</label>
                                        <input type="number" name="quantity" class="form-control" value="1" min="1" id="productQty">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Đơn giá</label>
                                        <input type="number" name="unit_price" class="form-control" id="productPrice" step="1000" min="0">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Chiết khấu</label>
                                        <input type="number" name="discount" class="form-control" value="0" step="1000" min="0">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary w-100"><i class="ri-add-line me-1"></i>Thêm</button>
                                    </div>
                                </form>

                                <!-- Products Table -->
                                <?php if (!empty($dealProducts)): ?>
                                    <?php $grandTotal = 0; ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Sản phẩm</th>
                                                    <th>SKU</th>
                                                    <th class="text-end">Số lượng</th>
                                                    <th class="text-end">Đơn giá</th>
                                                    <th class="text-end">Chiết khấu</th>
                                                    <th class="text-end">Thành tiền</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($dealProducts as $dp): ?>
                                                    <?php $grandTotal += (float)($dp['total'] ?? 0); ?>
                                                    <tr>
                                                        <td><?= e($dp['product_name'] ?? '') ?></td>
                                                        <td class="text-muted"><?= e($dp['sku'] ?? '') ?></td>
                                                        <td class="text-end"><?= (int)$dp['quantity'] ?></td>
                                                        <td class="text-end"><?= format_money($dp['unit_price']) ?></td>
                                                        <td class="text-end"><?= format_money($dp['discount'] ?? 0) ?></td>
                                                        <td class="text-end fw-medium"><?= format_money($dp['total']) ?></td>
                                                        <td class="text-end">
                                                            <form method="POST" action="<?= url('deals/' . $deal['id'] . '/products/' . $dp['id'] . '/remove') ?>" data-confirm="Xóa sản phẩm này?" class="d-inline">
                                                                <?= csrf_field() ?>
                                                                <button class="btn btn-soft-danger p-1"><i class="ri-delete-bin-line me-1"></i> Xóa</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-light">
                                                    <th colspan="5" class="text-end">Tổng cộng:</th>
                                                    <th class="text-end text-primary"><?= format_money($grandTotal) ?></th>
                                                    <th></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="ri-shopping-bag-line fs-36 text-muted"></i>
                                        <p class="text-muted mt-2">Chưa có sản phẩm</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Tab: Đơn hàng -->
                            <div class="tab-pane" id="tab-orders" role="tabpanel">
                                <div class="d-flex justify-content-between mb-3">
                                    <h6 class="mb-0">Đơn hàng liên quan</h6>
                                </div>
                                <?php
                                $linkedOrders = \Core\Database::fetchAll(
                                    "SELECT o.* FROM orders o WHERE o.deal_id = ? AND o.is_deleted = 0 ORDER BY o.created_at DESC LIMIT 20",
                                    [$deal['id']]
                                );
                                ?>
                                <?php if (!empty($linkedOrders)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Mã đơn</th>
                                                    <th>Tổng tiền</th>
                                                    <th>Trạng thái</th>
                                                    <th>Thanh toán</th>
                                                    <th>Ngày</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($linkedOrders as $order): ?>
                                                    <tr>
                                                        <td><a href="<?= url('orders/' . $order['id']) ?>"><?= e($order['order_number']) ?></a></td>
                                                        <td><?= format_money($order['total']) ?></td>
                                                        <td>
                                                            <?php $oColors = ['draft'=>'secondary','pending'=>'warning','confirmed'=>'info','processing'=>'primary','completed'=>'success','cancelled'=>'danger']; ?>
                                                            <span class="badge bg-<?= $oColors[$order['status']] ?? 'secondary' ?>-subtle text-<?= $oColors[$order['status']] ?? 'secondary' ?>"><?= e($order['status']) ?></span>
                                                        </td>
                                                        <td>
                                                            <?php $pColors = ['unpaid'=>'danger','partial'=>'warning','paid'=>'success']; ?>
                                                            <span class="badge bg-<?= $pColors[$order['payment_status']] ?? 'secondary' ?>"><?= e($order['payment_status'] ?? 'unpaid') ?></span>
                                                        </td>
                                                        <td class="text-muted"><?= time_ago($order['created_at']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="ri-shopping-cart-line fs-36 text-muted"></i>
                                        <p class="text-muted mt-2">Chưa có đơn hàng liên quan</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Tab: Ghi chú -->
                            <div class="tab-pane" id="tab-notes" role="tabpanel">
                                <h6 class="mb-3">Mô tả / Ghi chú</h6>
                                <?php if ($deal['description']): ?>
                                    <div class="p-3 bg-light rounded">
                                        <p class="mb-0"><?= nl2br(e($deal['description'])) ?></p>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="ri-file-text-line fs-36 text-muted"></i>
                                        <p class="text-muted mt-2">Chưa có ghi chú</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                </div>

                <?php $chatEntityType = 'deal'; $chatEntityId = $deal['id']; include BASE_PATH . '/resources/views/components/internal-chat.php'; ?>
            </div>
        </div>

        <!-- Close Deal Modal -->
        <div class="modal fade" id="closeDealModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="<?= url('deals/' . $deal['id'] . '/close') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="status" id="closeStatus" value="won">
                        <div class="modal-header">
                            <h5 class="modal-title" id="closeDealTitle">Đóng cơ hội</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Lý do <span class="text-danger">*</span></label>
                                <textarea name="close_reason" class="form-control" rows="3" placeholder="Nhập lý do đóng cơ hội..." required></textarea>
                            </div>
                            <div class="mb-3" id="lossReasonGroup" style="display:none;">
                                <label class="form-label">Phân loại lý do thua <span class="text-danger">*</span></label>
                                <select name="loss_reason_category" class="form-select" id="lossReasonCategory">
                                    <option value="">Chọn phân loại</option>
                                    <option value="price">Giá cả</option>
                                    <option value="competitor">Đối thủ cạnh tranh</option>
                                    <option value="timing">Thời điểm không phù hợp</option>
                                    <option value="no_budget">Không có ngân sách</option>
                                    <option value="no_need">Không có nhu cầu</option>
                                    <option value="product_fit">Sản phẩm không phù hợp</option>
                                    <option value="relationship">Mối quan hệ</option>
                                    <option value="other">Khác</option>
                                </select>
                            </div>
                            <div class="mb-3" id="competitorGroup" style="display:none;">
                                <label class="form-label">Đối thủ cạnh tranh</label>
                                <input type="text" name="competitor" class="form-control" placeholder="Tên đối thủ...">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-primary" id="closeDealBtn">Xác nhận</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

<script>
// Activity type buttons
document.querySelectorAll('.activity-type-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.activity-type-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('activityType').value = this.dataset.type;
    });
});

// Product select - auto fill price
document.getElementById('productSelect')?.addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const price = selected.getAttribute('data-price');
    if (price) {
        document.getElementById('productPrice').value = price;
    }
});

// Close deal modal
function setCloseStatus(status) {
    document.getElementById('closeStatus').value = status;
    const title = document.getElementById('closeDealTitle');
    const btn = document.getElementById('closeDealBtn');
    const lossGroup = document.getElementById('lossReasonGroup');
    const competitorGroup = document.getElementById('competitorGroup');
    const lossCategory = document.getElementById('lossReasonCategory');

    if (status === 'won') {
        title.textContent = 'Thắng cơ hội';
        btn.className = 'btn btn-success';
        btn.textContent = 'Xác nhận Thắng';
        lossGroup.style.display = 'none';
        competitorGroup.style.display = 'none';
        lossCategory.removeAttribute('required');
    } else {
        title.textContent = 'Thua cơ hội';
        btn.className = 'btn btn-danger';
        btn.textContent = 'Xác nhận Thua';
        lossGroup.style.display = 'block';
        competitorGroup.style.display = 'block';
        lossCategory.setAttribute('required', 'required');
    }
}

// Activate tab from hash
if (window.location.hash) {
    const tab = document.querySelector('a[href="' + window.location.hash + '"]');
    if (tab) {
        const bsTab = new bootstrap.Tab(tab);
        bsTab.show();
    }
}
</script>
