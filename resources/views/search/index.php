<?php $pageTitle = 'Tìm kiếm' . ($q ? ': ' . e($q) : ''); ?>

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Tìm kiếm <?= $q ? '- "' . e($q) . '"' : '' ?></h4>
                </div>
            </div>
        </div>

        <!-- Search Form -->
        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?= url('search') ?>" class="row g-3">
                    <div class="col-md-10">
                        <div class="input-group">
                            <span class="input-group-text"><i class="ri-search-line"></i></span>
                            <input type="text" class="form-control form-control-lg" name="q" placeholder="Tìm kiếm khách hàng, doanh nghiệp, cơ hội, ticket, đơn hàng..." value="<?= e($q ?? '') ?>" autofocus>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="ri-search-line me-1"></i> Tìm kiếm
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php
        $totalResults = count($contacts) + count($companies) + count($deals) + count($tickets) + count($orders);
        ?>

        <?php if ($q && $totalResults === 0): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="ri-search-line fs-1 text-muted d-block mb-3"></i>
                    <h5 class="text-muted">Không tìm thấy kết quả cho '<?= e($q) ?>'</h5>
                    <p class="text-muted mb-0">Hãy thử tìm kiếm với từ khóa khác.</p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($contacts)): ?>
        <!-- Contacts -->
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0">
                    <i class="ri-contacts-line me-1"></i> Khách hàng
                    <span class="badge bg-primary-subtle text-primary ms-2"><?= count($contacts) ?></span>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tên</th>
                                <th>Email</th>
                                <th>Điện thoại</th>
                                <th>Công ty</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contacts as $contact): ?>
                                <tr>
                                    <td>
                                        <a href="<?= url('contacts/' . $contact['id']) ?>" class="fw-medium">
                                            <?= e($contact['first_name'] . ' ' . ($contact['last_name'] ?? '')) ?>
                                        </a>
                                    </td>
                                    <td><?= e($contact['email'] ?? '-') ?></td>
                                    <td><?= e($contact['phone'] ?? '-') ?></td>
                                    <td><?= e($contact['company_name'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($companies)): ?>
        <!-- Companies -->
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0">
                    <i class="ri-building-line me-1"></i> Doanh nghiệp
                    <span class="badge bg-info-subtle text-info ms-2"><?= count($companies) ?></span>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tên</th>
                                <th>Email</th>
                                <th>Điện thoại</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($companies as $company): ?>
                                <tr>
                                    <td>
                                        <a href="<?= url('companies/' . $company['id']) ?>" class="fw-medium">
                                            <?= e($company['name']) ?>
                                        </a>
                                    </td>
                                    <td><?= e($company['email'] ?? '-') ?></td>
                                    <td><?= e($company['phone'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($deals)): ?>
        <!-- Deals -->
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0">
                    <i class="ri-funds-line me-1"></i> Cơ hội
                    <span class="badge bg-success-subtle text-success ms-2"><?= count($deals) ?></span>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tiêu đề</th>
                                <th>Giá trị</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $dealStatusColors = ['open' => 'primary', 'won' => 'success', 'lost' => 'danger', 'negotiation' => 'warning'];
                            $dealStatusLabels = ['open' => 'Mở', 'won' => 'Thắng', 'lost' => 'Thua', 'negotiation' => 'Đang thương lượng'];
                            ?>
                            <?php foreach ($deals as $deal): ?>
                                <tr>
                                    <td>
                                        <a href="<?= url('deals/' . $deal['id']) ?>" class="fw-medium">
                                            <?= e($deal['title']) ?>
                                        </a>
                                    </td>
                                    <td><?= format_money($deal['value'] ?? 0) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $dealStatusColors[$deal['status'] ?? ''] ?? 'secondary' ?>-subtle text-<?= $dealStatusColors[$deal['status'] ?? ''] ?? 'secondary' ?>">
                                            <?= $dealStatusLabels[$deal['status'] ?? ''] ?? ($deal['status'] ?? '-') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($tickets)): ?>
        <!-- Tickets -->
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0">
                    <i class="ri-ticket-line me-1"></i> Ticket
                    <span class="badge bg-warning-subtle text-warning ms-2"><?= count($tickets) ?></span>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mã ticket</th>
                                <th>Tiêu đề</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $ticketStatusColors = ['open' => 'info', 'in_progress' => 'primary', 'resolved' => 'success', 'closed' => 'secondary', 'pending' => 'warning'];
                            $ticketStatusLabels = ['open' => 'Mở', 'in_progress' => 'Đang xử lý', 'resolved' => 'Đã giải quyết', 'closed' => 'Đã đóng', 'pending' => 'Chờ xử lý'];
                            ?>
                            <?php foreach ($tickets as $ticket): ?>
                                <tr>
                                    <td>
                                        <a href="<?= url('tickets/' . $ticket['id']) ?>" class="fw-medium">
                                            <?= e($ticket['ticket_code'] ?? '') ?>
                                        </a>
                                    </td>
                                    <td><?= e($ticket['title']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $ticketStatusColors[$ticket['status'] ?? ''] ?? 'secondary' ?>-subtle text-<?= $ticketStatusColors[$ticket['status'] ?? ''] ?? 'secondary' ?>">
                                            <?= $ticketStatusLabels[$ticket['status'] ?? ''] ?? ($ticket['status'] ?? '-') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($orders)): ?>
        <!-- Orders -->
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0">
                    <i class="ri-shopping-cart-line me-1"></i> Đơn hàng
                    <span class="badge bg-danger-subtle text-danger ms-2"><?= count($orders) ?></span>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mã đơn hàng</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $orderStatusColors = ['pending' => 'warning', 'confirmed' => 'info', 'processing' => 'primary', 'completed' => 'success', 'cancelled' => 'danger'];
                            $orderStatusLabels = ['pending' => 'Chờ xác nhận', 'confirmed' => 'Đã xác nhận', 'processing' => 'Đang xử lý', 'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy'];
                            ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>
                                        <a href="<?= url('orders/' . $order['id']) ?>" class="fw-medium">
                                            <?= e($order['order_number']) ?>
                                        </a>
                                    </td>
                                    <td><?= format_money($order['total_amount'] ?? 0) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $orderStatusColors[$order['status'] ?? ''] ?? 'secondary' ?>-subtle text-<?= $orderStatusColors[$order['status'] ?? ''] ?? 'secondary' ?>">
                                            <?= $orderStatusLabels[$order['status'] ?? ''] ?? ($order['status'] ?? '-') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
