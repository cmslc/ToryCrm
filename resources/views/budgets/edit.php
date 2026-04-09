<?php $pageTitle = 'Sửa ngân sách: ' . $budget['name']; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Sửa ngân sách</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('budgets') ?>">Ngân sách</a></li>
                <li class="breadcrumb-item active">Sửa</li>
            </ol>
        </div>

        <form method="POST" action="<?= url('budgets/' . $budget['id'] . '/update') ?>" id="budgetForm">
            <?= csrf_field() ?>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Thông tin ngân sách</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tên ngân sách <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" value="<?= e($budget['name']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Loại</label>
                                    <select name="type" class="form-select">
                                        <option value="general" <?= $budget['type'] === 'general' ? 'selected' : '' ?>>Chung</option>
                                        <option value="department" <?= $budget['type'] === 'department' ? 'selected' : '' ?>>Phòng ban</option>
                                        <option value="project" <?= $budget['type'] === 'project' ? 'selected' : '' ?>>Dự án</option>
                                        <option value="campaign" <?= $budget['type'] === 'campaign' ? 'selected' : '' ?>>Chiến dịch</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Phòng ban</label>
                                    <input type="text" class="form-control" name="department" value="<?= e($budget['department'] ?? '') ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Bắt đầu</label>
                                    <input type="date" class="form-control" name="period_start" value="<?= $budget['period_start'] ?? '' ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Kết thúc</label>
                                    <input type="date" class="form-control" name="period_end" value="<?= $budget['period_end'] ?? '' ?>">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Ghi chú</label>
                                    <textarea name="notes" class="form-control" rows="2"><?= e($budget['notes'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Budget Items -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Hạng mục ngân sách</h5>
                            <button type="button" class="btn btn-soft-primary" onclick="addBudgetItem()">
                                <i class="ri-add-line me-1"></i> Thêm hạng mục
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="45%">Hạng mục</th>
                                            <th width="30%">Ngân sách dự kiến</th>
                                            <th width="15%">Đã chi</th>
                                            <th width="10%"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="budgetItems">
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td class="text-end fw-bold fs-5">Tổng cộng:</td>
                                            <td id="totalBudgetDisplay" class="fw-bold fs-5 text-primary">0 ₫</td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-save-line me-1"></i> Lưu</button>
                            <a href="<?= url('budgets/' . $budget['id']) ?>" class="btn btn-soft-secondary">Hủy</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <script>
        const existingItems = <?= json_encode($items ?? []) ?>;
        let budgetItemIndex = 0;

        function addBudgetItem(data) {
            const tbody = document.getElementById('budgetItems');
            const idx = budgetItemIndex++;
            const tr = document.createElement('tr');
            tr.id = 'budget-item-' + idx;
            const actualDisplay = data?.actual_amount ? new Intl.NumberFormat('vi-VN').format(Math.round(data.actual_amount)) + ' ₫' : '0 ₫';
            tr.innerHTML = `
                <td><input type="text" class="form-control" name="items[${idx}][category]" value="${data?.category || ''}" placeholder="VD: Quảng cáo, Nhân sự..." required></td>
                <td><input type="number" class="form-control" name="items[${idx}][planned_amount]" value="${data?.planned_amount || 0}" min="0" onchange="calculateBudgetTotal()"></td>
                <td class="text-muted">${actualDisplay}</td>
                <td><button type="button" class="btn btn-soft-danger" onclick="removeBudgetItem(${idx})"><i class="ri-close-line"></i></button></td>
            `;
            tbody.appendChild(tr);
            calculateBudgetTotal();
        }

        function removeBudgetItem(idx) {
            document.getElementById('budget-item-' + idx)?.remove();
            calculateBudgetTotal();
        }

        function calculateBudgetTotal() {
            let total = 0;
            document.querySelectorAll('#budgetItems tr').forEach(tr => {
                total += parseFloat(tr.querySelector('[name*="[planned_amount]"]')?.value || 0);
            });
            document.getElementById('totalBudgetDisplay').textContent = formatMoney(total);
        }

        function formatMoney(amount) {
            return new Intl.NumberFormat('vi-VN').format(Math.round(amount)) + ' ₫';
        }

        // Load existing items
        if (existingItems.length > 0) {
            existingItems.forEach(item => addBudgetItem(item));
        } else {
            addBudgetItem();
        }
        </script>
