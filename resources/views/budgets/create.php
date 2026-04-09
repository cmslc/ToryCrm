<?php $pageTitle = 'Tạo ngân sách'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Tạo ngân sách</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('budgets') ?>">Ngân sách</a></li>
                <li class="breadcrumb-item active">Tạo mới</li>
            </ol>
        </div>

        <form method="POST" action="<?= url('budgets/store') ?>" id="budgetForm">
            <?= csrf_field() ?>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Thông tin ngân sách</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tên ngân sách <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" required placeholder="VD: Ngân sách Marketing Q2/2026">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Loại</label>
                                    <select name="type" class="form-select">
                                        <option value="general">Chung</option>
                                        <option value="department">Phòng ban</option>
                                        <option value="project">Dự án</option>
                                        <option value="campaign">Chiến dịch</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Phòng ban</label>
                                    <input type="text" class="form-control" name="department" placeholder="VD: Marketing, Sales...">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Bắt đầu</label>
                                    <input type="date" class="form-control" name="period_start" value="<?= date('Y-m-01') ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Kết thúc</label>
                                    <input type="date" class="form-control" name="period_end" value="<?= date('Y-m-t', strtotime('+2 months')) ?>">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Ghi chú</label>
                                    <textarea name="notes" class="form-control" rows="2" placeholder="Mô tả mục đích ngân sách..."></textarea>
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
                                            <th width="50%">Hạng mục</th>
                                            <th width="35%">Ngân sách dự kiến</th>
                                            <th width="15%"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="budgetItems">
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td class="text-end fw-bold fs-5">Tổng cộng:</td>
                                            <td id="totalBudgetDisplay" class="fw-bold fs-5 text-primary">0 ₫</td>
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
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-save-line me-1"></i> Lưu ngân sách</button>
                            <a href="<?= url('budgets') ?>" class="btn btn-soft-secondary">Hủy</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <script>
        let budgetItemIndex = 0;

        function addBudgetItem(data) {
            const tbody = document.getElementById('budgetItems');
            const idx = budgetItemIndex++;
            const tr = document.createElement('tr');
            tr.id = 'budget-item-' + idx;
            tr.innerHTML = `
                <td><input type="text" class="form-control" name="items[${idx}][category]" value="${data?.category || ''}" placeholder="VD: Quảng cáo, Nhân sự, Vật tư..." required></td>
                <td><input type="number" class="form-control" name="items[${idx}][planned_amount]" value="${data?.planned_amount || 0}" min="0" onchange="calculateBudgetTotal()"></td>
                <td><button type="button" class="btn btn-soft-danger" onclick="removeBudgetItem(${idx})"><i class="ri-close-line"></i></button></td>
            `;
            tbody.appendChild(tr);
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

        // Add first item
        addBudgetItem();
        </script>
