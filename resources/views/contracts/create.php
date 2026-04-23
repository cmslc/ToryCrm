<?php $pageTitle = 'Tạo hợp đồng bán'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0"><i class="ri-file-shield-line me-1"></i> Quản lý hợp đồng \ Tạo hợp đồng bán</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('contracts') ?>" class="btn btn-soft-secondary">Quay lại</a>
        <button type="submit" form="contractForm" class="btn btn-primary"><i class="ri-save-line me-1"></i> Cập nhật</button>
    </div>
</div>

<form method="POST" action="<?= url('contracts/store') ?>" id="contractForm">
    <?= csrf_field() ?>

    <!-- SECTION 1: Thông tin hợp đồng -->
    <div class="card">
        <div class="card-header"><h5 class="card-title mb-0"><i class="ri-file-list-3-line me-1"></i> Thông tin hợp đồng</h5></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tên hợp đồng <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="title" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Số hợp đồng</label>
                    <input type="text" class="form-control" name="contract_number" value="<?= e($contractNumber ?? '') ?>" placeholder="Tự tạo nếu để trống">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Mã hợp đồng</label>
                    <input type="text" class="form-control" name="contract_code">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Kiểu hợp đồng</label>
                    <select name="type" class="form-select">
                        <option value="Mới">Mới</option>
                        <option value="Gia hạn">Gia hạn</option>
                        <option value="Bổ sung">Bổ sung</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Ngày có hiệu lực <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="start_date" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Ngày hết hiệu lực</label>
                    <input type="date" class="form-control" name="end_date" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Báo Giá</label>
                    <select name="quote_id" class="form-select searchable-select">
                        <option value="">Mời chọn</option>
                        <?php foreach ($quotes ?? [] as $q): ?>
                        <option value="<?= $q['id'] ?>"><?= e($q['order_number'] . ' - ' . ($q['contact_name'] ?? '')) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Ngày tạo hợp đồng <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="created_date" value="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Hình thức thanh toán <span class="text-danger">*</span></label>
                    <select name="payment_method" class="form-select">
                        <option value="bank_transfer">Chuyển khoản</option>
                        <option value="cash">Tiền mặt</option>
                        <option value="credit_card">Thẻ tín dụng</option>
                        <option value="other">Khác</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Hợp đồng sử dụng</label>
                    <select name="usage_type" class="form-select">
                        <option value="one_time">Một lần</option>
                        <option value="multiple">Nhiều lần</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Thời gian thực tế bắt đầu</label>
                    <input type="date" class="form-control" name="actual_start_date">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Thời gian thực tế kết thúc</label>
                    <input type="date" class="form-control" name="actual_end_date">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Giá trị thực hiện</label>
                    <input type="number" class="form-control" name="executed_amount" value="0" min="0">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Dự án</label>
                    <input type="text" class="form-control" name="project">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Đã thực hiện</label>
                    <input type="number" class="form-control" name="actual_value" value="0" min="0">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Địa điểm</label>
                    <input type="text" class="form-control" name="location">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Hợp đồng gốc</label>
                    <select name="parent_contract_id" class="form-select searchable-select">
                        <option value="">Không</option>
                        <?php foreach ($allContracts ?? [] as $ac): ?>
                        <option value="<?= $ac['id'] ?>"><?= e($ac['contract_number'] . ' - ' . $ac['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Hợp đồng liên quan</label>
                    <select name="related_contract_id" class="form-select searchable-select">
                        <option value="">Mời chọn</option>
                        <?php foreach ($allContracts ?? [] as $ac): ?>
                        <option value="<?= $ac['id'] ?>"><?= e($ac['contract_number'] . ' - ' . $ac['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="pending">Chờ duyệt</option>
                        <option value="approved">Đã duyệt</option>
                        <option value="in_progress">Đang thực hiện</option>
                        <option value="renewed">Đã gia hạn</option>
                        <option value="completed">Đã kết thúc</option>
                        <option value="cancelled">Đã hủy</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Người phụ trách</label>
                    <?php $deptGrouped = []; foreach ($users ?? [] as $u) { $deptGrouped[$u['dept_name'] ?? 'Chưa phân phòng'][] = $u; } ?>
                    <select name="owner_id" class="form-select searchable-select">
                        <option value="">Chọn</option>
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
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Địa chỉ lắp đặt <span class="text-danger">*</span></label>
                    <textarea name="installation_address" class="form-control" rows="2"></textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 2: Bên bán (phải) & Bên mua (trái) -->
    <div class="row">
        <div class="col-lg-6 order-lg-2">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0"><i class="ri-building-line me-1"></i> Thông tin bên bán (Bên B)</h5></div>
                <div class="card-body">
                    <?php $b = $branding ?? []; $cpList = $companyProfiles ?? []; ?>
                    <div class="mb-3">
                        <label class="form-label">Chọn công ty <span class="text-danger">*</span></label>
                        <select name="party_a_company_id" class="form-select" id="partyASelect">
                            <?php if (!empty($cpList)): ?>
                                <?php foreach ($cpList as $cp): ?>
                                <option value="<?= $cp['id'] ?>" <?= $cp['is_default'] ? 'selected' : '' ?>
                                    data-name="<?= e($cp['name']) ?>" data-address="<?= e($cp['address'] ?? '') ?>"
                                    data-phone="<?= e($cp['phone'] ?? '') ?>" data-fax="<?= e($cp['fax'] ?? '') ?>"
                                    data-tax="<?= e($cp['tax_code'] ?? '') ?>" data-rep="<?= e($cp['representative'] ?? '') ?>"
                                    data-title="<?= e($cp['representative_title'] ?? '') ?>"
                                    data-bank="<?= e($cp['bank_account'] ?? '') ?>" data-bankname="<?= e($cp['bank_name'] ?? '') ?>"
                                ><?= e($cp['name']) ?><?= $cp['is_default'] ? ' (mặc định)' : '' ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" data-name="<?= e($b['name'] ?? '') ?>" data-address="<?= e($b['address'] ?? '') ?>"
                                    data-phone="<?= e($b['phone'] ?? '') ?>" data-fax="<?= e($b['fax'] ?? '') ?>"
                                    data-tax="<?= e($b['tax_code'] ?? '') ?>" data-rep="<?= e($b['representative'] ?? '') ?>"
                                    data-title="<?= e($b['representative_title'] ?? '') ?>"
                                    data-bank="<?= e($b['bank_account'] ?? '') ?>" data-bankname="<?= e($b['bank_name'] ?? '') ?>"
                                    selected><?= e($b['name'] ?? 'Chưa cài đặt') ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <?php
                    $defaultCp = !empty($cpList) ? ($cpList[array_key_first(array_filter($cpList, fn($c)=>$c['is_default']))] ?? $cpList[0] ?? []) : [];
                    $aName = $defaultCp['name'] ?? $b['name'] ?? '';
                    $aAddr = $defaultCp['address'] ?? $b['address'] ?? '';
                    $aPhone = $defaultCp['phone'] ?? $b['phone'] ?? '';
                    $aFax = $defaultCp['fax'] ?? $b['fax'] ?? '';
                    $aTax = $defaultCp['tax_code'] ?? $b['tax_code'] ?? '';
                    $aRep = $defaultCp['representative'] ?? $b['representative'] ?? '';
                    $aTitle = $defaultCp['representative_title'] ?? $b['representative_title'] ?? '';
                    $aBank = $defaultCp['bank_account'] ?? $b['bank_account'] ?? '';
                    $aBankName = $defaultCp['bank_name'] ?? $b['bank_name'] ?? '';
                    ?>
                    <input type="hidden" name="party_a_name" id="pAName" value="<?= e($aName) ?>">
                    <div class="row">
                        <div class="col-12 mb-3"><label class="form-label">Địa chỉ</label><input type="text" class="form-control" name="party_a_address" id="pAAddr" value="<?= e($aAddr) ?>"></div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3"><label class="form-label">Điện thoại</label><input type="text" class="form-control" name="party_a_phone" id="pAPhone" value="<?= e($aPhone) ?>"></div>
                        <div class="col-6 mb-3"><label class="form-label">Fax</label><input type="text" class="form-control" name="party_a_fax" id="pAFax" value="<?= e($aFax) ?>"></div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3"><label class="form-label">Người đại diện</label><input type="text" class="form-control" name="party_a_representative" id="pARep" value="<?= e($aRep) ?>"></div>
                        <div class="col-6 mb-3"><label class="form-label">Chức vụ</label><input type="text" class="form-control" name="party_a_position" id="pATitle" value="<?= e($aTitle) ?>"></div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3"><label class="form-label">Tài khoản số</label><input type="text" class="form-control" name="party_a_bank_account" id="pABank" value="<?= e($aBank) ?>"></div>
                        <div class="col-6 mb-3"><label class="form-label">Ngân hàng</label><input type="text" class="form-control" name="party_a_bank_name" id="pABankName" value="<?= e($aBankName) ?>"></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Mã số thuế</label><input type="text" class="form-control" name="party_a_tax_code" id="pATax" value="<?= e($aTax) ?>"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 order-lg-1">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0"><i class="ri-user-3-line me-1"></i> Thông tin bên mua (Bên A)</h5></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Bên B <span class="text-danger">*</span></label>
                        <select name="contact_id" class="form-select searchable-select" id="partyBSelect" required>
                            <option value="">Chọn khách hàng</option>
                            <?php foreach ($contacts ?? [] as $c): ?>
                            <option value="<?= $c['id'] ?>"
                                data-address="<?= e($c['address'] ?? '') ?>"
                                data-phone="<?= e($c['phone'] ?? '') ?>"
                                data-tax="<?= e($c['tax_code'] ?? '') ?>"
                                data-fax="<?= e($c['fax'] ?? '') ?>"
                            ><?= e(trim(($c['company_name'] ?? '') ?: ($c['first_name'] . ' ' . ($c['last_name'] ?? '')))) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label">Địa chỉ</label>
                            <input type="text" class="form-control" name="party_b_address" id="partyBAddress">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Điện thoại</label>
                            <input type="text" class="form-control" name="party_b_phone" id="partyBPhone">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Fax</label>
                            <input type="text" class="form-control" name="party_b_fax" id="partyBFax">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Người đại diện</label>
                            <select name="party_b_representative" class="form-select searchable-select">
                                <option value="">Mời chọn</option>
                                <?php foreach ($contacts ?? [] as $c): ?>
                                <option value="<?= e(trim($c['first_name'] . ' ' . ($c['last_name'] ?? ''))) ?>"><?= e(trim($c['first_name'] . ' ' . ($c['last_name'] ?? ''))) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Chức vụ</label>
                            <input type="text" class="form-control" name="party_b_position">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Tài khoản số</label>
                            <input type="text" class="form-control" name="party_b_bank_account">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Ngân hàng</label>
                            <input type="text" class="form-control" name="party_b_bank_name">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mã số thuế</label>
                        <input type="text" class="form-control" name="party_b_tax_code" id="partyBTax">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 3: Người liên quan -->
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0"><i class="ri-team-line me-1"></i> Người liên quan</h5>
            <button type="button" class="btn btn-soft-primary" id="btnAddRelatedUser"><i class="ri-add-line me-1"></i> Thêm một dòng</button>
        </div>
        <div class="card-body" id="relatedUsersContainer">
            <div class="related-user-row d-flex align-items-center gap-3 mb-2">
                <select name="related_users[0][user_id]" class="form-select searchable-select" style="max-width:250px">
                    <option value="">Chọn</option>
                    <?php foreach ($users ?? [] as $u): ?>
                    <option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" class="form-control" name="related_users[0][commission]" value="0" min="0" style="max-width:150px" placeholder="Hoa hồng">
            </div>
        </div>
    </div>

    <!-- SECTION 4: Sản phẩm liên quan -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="ri-shopping-bag-line me-1"></i> Sản phẩm liên quan</h5>
            <button type="button" class="btn btn-soft-primary" onclick="addContractItem()"><i class="ri-add-line me-1"></i> Thêm sản phẩm</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle mb-0" id="contractItemsTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px">#</th>
                            <th style="width:140px">Mã SP</th>
                            <th style="min-width:200px">Tên sản phẩm</th>
                            <th style="width:70px">Đơn vị</th>
                            <th style="width:80px">Số lượng</th>
                            <th style="width:120px">Đơn giá</th>
                            <th style="width:70px">CK(%)</th>
                            <th style="width:100px">CK thành tiền</th>
                            <th style="width:70px">VAT(%)</th>
                            <th style="width:130px">Thành tiền</th>
                            <th style="width:40px"></th>
                        </tr>
                    </thead>
                    <tbody id="contractItems">
                    </tbody>
                </table>
            </div>

            <!-- Summary -->
            <div class="row mt-3">
                <div class="col-lg-6"></div>
                <div class="col-lg-6">
                    <table class="table table-borderless mb-0" style="background:#f0f6ff;border-radius:8px;">
                        <tr>
                            <td class="fw-medium">Tổng tiền hàng</td>
                            <td class="text-end fw-bold text-primary" id="subtotalDisplay">0</td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    Phí vận chuyển sau thuế
                                    <input type="checkbox" class="form-check-input" name="shipping_after_tax" value="1" checked>
                                </div>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Phí vận chuyển</td>
                            <td>
                                <div class="d-flex gap-2 justify-content-end">
                                    <div class="input-group" style="width:120px">
                                        <input type="number" class="form-control text-end" name="shipping_fee_percent" value="0" min="0" max="100" step="0.01" onchange="calcFeeFromPct('shipping')">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <input type="number" class="form-control text-end" name="shipping_fee" value="0" min="0" style="width:130px" onchange="calcTotal()">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    Chiết khấu sau thuế
                                    <input type="checkbox" class="form-check-input" name="discount_after_tax" value="1">
                                </div>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Chiết khấu</td>
                            <td>
                                <div class="d-flex gap-2 justify-content-end">
                                    <div class="input-group" style="width:120px">
                                        <input type="number" class="form-control text-end" name="discount_percent" value="0" min="0" max="100" step="0.01" onchange="calcFeeFromPct('discount')">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <input type="number" class="form-control text-end" name="discount_amount" value="0" min="0" style="width:130px" onchange="calcTotal()">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    Áp dụng thuế VAT
                                    <input type="checkbox" class="form-check-input" name="apply_vat" value="1" checked>
                                </div>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Thuế VAT (%)</td>
                            <td>
                                <div class="d-flex gap-2 justify-content-end">
                                    <div class="input-group" style="width:120px">
                                        <input type="number" class="form-control text-end" name="vat_percent" value="0" min="0" max="100" step="0.01" onchange="calcFeeFromPct('vat')">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <input type="number" class="form-control text-end" name="vat_amount" value="0" min="0" style="width:130px" onchange="calcTotal()">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Phí lắp đặt</td>
                            <td>
                                <div class="d-flex gap-2 justify-content-end">
                                    <div class="input-group" style="width:120px">
                                        <input type="number" class="form-control text-end" name="installation_fee_percent" value="0" min="0" max="100" step="0.01" onchange="calcFeeFromPct('installation')">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <input type="number" class="form-control text-end" name="installation_fee" value="0" min="0" style="width:130px" onchange="calcTotal()">
                                </div>
                            </td>
                        </tr>
                        <tr class="border-top">
                            <td class="fw-bold fs-5">Tổng tiền thanh toán</td>
                            <td class="text-end fw-bold fs-5 text-primary" id="grandTotalDisplay">0</td>
                        </tr>
                    </table>
                    <input type="hidden" name="value" id="contractValue" value="0">
                    <input type="hidden" name="subtotal" id="contractSubtotal" value="0">
                    <input type="hidden" name="tax_amount" id="contractTaxAmount" value="0">
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 5: Điều khoản -->
    <div class="card">
        <div class="card-header"><h5 class="card-title mb-0"><i class="ri-draft-line me-1"></i> Các điều khoản trong hợp đồng</h5></div>
        <div class="card-body">
            <?php if (!empty($docTemplates)): ?>
            <div class="mb-3">
                <label class="form-label">Chọn mẫu hợp đồng</label>
                <select class="form-select" id="templateSelect" onchange="loadTemplate(this.value)">
                    <?php foreach ($docTemplates as $dt): ?>
                    <option value="<?= $dt['id'] ?>" <?= $dt['is_default'] ? 'selected' : '' ?>><?= e($dt['name']) ?><?= $dt['is_default'] ? ' (mặc định)' : '' ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <textarea name="terms" class="form-control" rows="6" id="contractTerms"><?= e($defaultTemplate ?? '') ?></textarea>
            <script src="<?= asset('libs/ckeditor/ckeditor.js') ?>"></script>
            <script>
            var _docTemplates = <?= json_encode(array_column($docTemplates ?? [], 'content', 'id')) ?>;
            if (typeof CKEDITOR !== 'undefined') {
                CKEDITOR.replace('contractTerms', { language: 'vi', height: 300, allowedContent: true });
            }
            function loadTemplate(id) {
                var content = _docTemplates[id] || '';
                if (CKEDITOR.instances.contractTerms) {
                    CKEDITOR.instances.contractTerms.setData(content);
                } else {
                    document.getElementById('contractTerms').value = content;
                }
            }
            </script>
            <div class="mt-3">
                <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input" name="auto_renew" id="autoRenew" value="1">
                    <label class="form-check-label" for="autoRenew">Tự động gia hạn hợp đồng</label>
                </div>
                <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input" name="auto_create_order" id="autoCreateOrder" value="1">
                    <label class="form-check-label" for="autoCreateOrder">Tự động tạo đơn hàng</label>
                </div>
                <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input" name="auto_notify_expiry" id="autoNotifyExpiry" value="1">
                    <label class="form-check-label" for="autoNotifyExpiry">Tự động báo động sắp hết hạn hợp đồng</label>
                </div>
                <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input" name="auto_send_sms" id="autoSendSms" value="1">
                    <label class="form-check-label" for="autoSendSms">Tự động gửi SMS thông báo</label>
                </div>
                <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input" name="auto_send_email" id="autoSendEmail" value="1">
                    <label class="form-check-label" for="autoSendEmail">Tự động gửi email hợp đồng mới</label>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
const products = <?= json_encode($products ?? []) ?>;
let itemIndex = 0;
let relatedUserIndex = 1;

// === PRODUCT ITEMS ===
function addContractItem(productId = '') {
    const tbody = document.getElementById('contractItems');
    const idx = itemIndex++;
    const tr = document.createElement('tr');
    tr.id = 'item-row-' + idx;

    tr.innerHTML = `
        <td class="text-center text-muted">${idx + 1}</td>
        <td>
            <div class="product-search-wrap">
                <input type="text" class="form-control" id="item-sku-${idx}" placeholder="Mã SP..." autocomplete="off" onfocus="searchProduct(this,${idx},'sku')" oninput="searchProduct(this,${idx},'sku')">
                <div class="product-dropdown" id="item-skudrop-${idx}"></div>
            </div>
        </td>
        <td>
            <div class="product-search-wrap">
                <input type="text" class="form-control" id="item-namesearch-${idx}" placeholder="Tên SP..." autocomplete="off" onfocus="searchProduct(this,${idx},'name')" oninput="searchProduct(this,${idx},'name')">
                <div class="product-dropdown" id="item-namedrop-${idx}"></div>
            </div>
            <input type="hidden" name="items[${idx}][product_id]" id="item-product-${idx}">
            <input type="hidden" name="items[${idx}][product_name]" id="item-name-${idx}">
        </td>
        <td><input type="text" class="form-control" name="items[${idx}][unit]" id="item-unit-${idx}" value="Cái"></td>
        <td><input type="number" class="form-control" name="items[${idx}][quantity]" value="1" min="0.01" step="0.01" onchange="calculateRow(${idx})"></td>
        <td><input type="number" class="form-control" name="items[${idx}][unit_price]" id="item-price-${idx}" value="0" min="0" onchange="calculateRow(${idx})"></td>
        <td><input type="number" class="form-control" name="items[${idx}][discount_percent]" id="item-ckpct-${idx}" value="0" min="0" max="100" step="0.01" onchange="calcDiscountFromPct(${idx})"></td>
        <td><input type="number" class="form-control" name="items[${idx}][discount]" id="item-discount-${idx}" value="0" min="0" onchange="calculateRow(${idx})"></td>
        <td><input type="number" class="form-control" name="items[${idx}][tax_rate]" id="item-tax-${idx}" value="0" min="0" max="100" step="0.01" onchange="calculateRow(${idx})"></td>
        <td class="fw-medium text-end" id="item-total-${idx}">0 ₫</td>
        <td><button type="button" class="btn btn-soft-danger btn-icon" onclick="removeItem(${idx})"><i class="ri-delete-bin-line"></i></button></td>
    `;
    tbody.appendChild(tr);
}

let searchTimer = null;
function searchProduct(input, idx, type) {
    const q = input.value.trim();
    const dropId = type === 'sku' ? 'item-skudrop-' + idx : 'item-namedrop-' + idx;
    const drop = document.getElementById(dropId);
    if (q.length < 1) { drop.style.display = 'none'; return; }
    clearTimeout(searchTimer);
    searchTimer = setTimeout(function() {
        fetch('<?= url("products/search-ajax") ?>?q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(results => {
                if (!results.length) { drop.innerHTML = '<div class="pd-item text-muted">Không tìm thấy</div>'; drop.style.display = 'block'; return; }
                drop.innerHTML = results.map(p =>
                    '<div class="pd-item" onclick=\'pickProduct(' + idx + ',' + JSON.stringify(p).replace(/'/g, "\\'") + ')\'>' +
                    '<strong>' + p.name + '</strong> <span class="pd-sku">' + (p.sku || '') + '</span>' +
                    '<br><small class="text-muted">' + Number(p.price).toLocaleString('vi-VN') + ' ₫ / ' + (p.unit || 'Cái') + '</small></div>'
                ).join('');
                drop.style.display = 'block';
            });
    }, 250);
}

function pickProduct(idx, p) {
    document.getElementById('item-product-' + idx).value = p.id;
    document.getElementById('item-name-' + idx).value = p.name;
    document.getElementById('item-sku-' + idx).value = p.sku || '';
    document.getElementById('item-namesearch-' + idx).value = p.name;
    document.getElementById('item-price-' + idx).value = p.price || 0;
    document.getElementById('item-unit-' + idx).value = p.unit || 'Cái';
    document.getElementById('item-tax-' + idx).value = p.tax_rate || 0;
    document.getElementById('item-skudrop-' + idx).style.display = 'none';
    document.getElementById('item-namedrop-' + idx).style.display = 'none';
    calculateRow(idx);
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.product-search-wrap')) {
        document.querySelectorAll('.product-dropdown').forEach(d => d.style.display = 'none');
    }
});

function calcDiscountFromPct(idx) {
    const qty = parseFloat(document.querySelector('[name="items[' + idx + '][quantity]"]')?.value || 0);
    const price = parseFloat(document.getElementById('item-price-' + idx)?.value || 0);
    const pct = parseFloat(document.getElementById('item-ckpct-' + idx)?.value || 0);
    document.getElementById('item-discount-' + idx).value = Math.round(qty * price * pct / 100);
    calculateRow(idx);
}

function removeItem(idx) {
    document.getElementById('item-row-' + idx)?.remove();
    calcTotal();
}

function calculateRow(idx) {
    const qty = parseFloat(document.querySelector('[name="items[' + idx + '][quantity]"]')?.value || 0);
    const price = parseFloat(document.getElementById('item-price-' + idx)?.value || 0);
    const tax = parseFloat(document.getElementById('item-tax-' + idx)?.value || 0);
    const discount = parseFloat(document.getElementById('item-discount-' + idx)?.value || 0);
    const total = qty * price * (1 + tax / 100) - discount;
    const el = document.getElementById('item-total-' + idx);
    if (el) el.textContent = formatMoney(Math.max(0, total));
    calcTotal();
}

function calcTotal() {
    let subtotal = 0;
    document.querySelectorAll('#contractItems tr').forEach(tr => {
        const qty = parseFloat(tr.querySelector('[name*="[quantity]"]')?.value || 0);
        const price = parseFloat(tr.querySelector('[name*="[unit_price]"]')?.value || 0);
        subtotal += qty * price;
    });

    document.getElementById('subtotalDisplay').textContent = formatMoney(subtotal);
    document.getElementById('contractSubtotal').value = subtotal;

    const shippingFee = parseFloat(document.querySelector('[name="shipping_fee"]').value || 0);
    const discountAmt = parseFloat(document.querySelector('[name="discount_amount"]').value || 0);
    const vatAmt = parseFloat(document.querySelector('[name="vat_amount"]').value || 0);
    const installFee = parseFloat(document.querySelector('[name="installation_fee"]').value || 0);

    const grandTotal = subtotal + shippingFee - discountAmt + vatAmt + installFee;
    document.getElementById('grandTotalDisplay').textContent = formatMoney(Math.max(0, grandTotal));
    document.getElementById('contractValue').value = Math.max(0, grandTotal);
    document.getElementById('contractTaxAmount').value = vatAmt;
}

function calcFeeFromPct(type) {
    const subtotal = parseFloat(document.getElementById('contractSubtotal').value || 0);
    const pctInput = document.querySelector('[name="' + type + (type === 'vat' ? '_percent' : (type === 'discount' ? '_percent' : '_fee_percent')) + '"]');
    const pct = parseFloat(pctInput?.value || 0);

    let amtName;
    if (type === 'shipping') amtName = 'shipping_fee';
    else if (type === 'discount') amtName = 'discount_amount';
    else if (type === 'vat') amtName = 'vat_amount';
    else if (type === 'installation') amtName = 'installation_fee';

    const amtInput = document.querySelector('[name="' + amtName + '"]');
    if (amtInput) amtInput.value = Math.round(subtotal * pct / 100);
    calcTotal();
}

function formatMoney(amount) {
    return new Intl.NumberFormat('vi-VN').format(Math.round(amount)) + ' ₫';
}

// === RELATED USERS ===
document.getElementById('btnAddRelatedUser')?.addEventListener('click', function() {
    const container = document.getElementById('relatedUsersContainer');
    const idx = relatedUserIndex++;
    const row = document.createElement('div');
    row.className = 'related-user-row d-flex align-items-center gap-3 mb-2';
    row.innerHTML = `
        <select name="related_users[${idx}][user_id]" class="form-select" style="max-width:250px">
            <option value="">Chọn</option>
            <?php foreach ($users ?? [] as $u): ?>
            <option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="number" class="form-control" name="related_users[${idx}][commission]" value="0" min="0" style="max-width:150px" placeholder="Hoa hồng">
        <button type="button" class="btn btn-soft-danger btn-icon" onclick="this.closest('.related-user-row').remove()"><i class="ri-delete-bin-line"></i></button>
    `;
    container.appendChild(row);
});

// === PARTY A (BEN BAN) AUTO-FILL from company profile ===
document.getElementById('partyASelect')?.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    document.getElementById('pAName').value = opt.dataset.name || '';
    document.getElementById('pAAddr').value = opt.dataset.address || '';
    document.getElementById('pAPhone').value = opt.dataset.phone || '';
    document.getElementById('pAFax').value = opt.dataset.fax || '';
    document.getElementById('pATax').value = opt.dataset.tax || '';
    document.getElementById('pARep').value = opt.dataset.rep || '';
    document.getElementById('pATitle').value = opt.dataset.title || '';
    document.getElementById('pABank').value = opt.dataset.bank || '';
    document.getElementById('pABankName').value = opt.dataset.bankname || '';
});

// === PARTY B (BEN MUA) AUTO-FILL from contact ===
document.getElementById('partyBSelect')?.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    if (!document.getElementById('partyBAddress').value) document.getElementById('partyBAddress').value = opt.dataset.address || '';
    if (!document.getElementById('partyBPhone').value) document.getElementById('partyBPhone').value = opt.dataset.phone || '';
    if (!document.getElementById('partyBFax').value) document.getElementById('partyBFax').value = opt.dataset.fax || '';
    if (!document.getElementById('partyBTax').value) document.getElementById('partyBTax').value = opt.dataset.tax || '';
});

// Init first item row
addContractItem();
</script>

<style>
.product-search-wrap { position: relative; }
.product-dropdown { position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid #ddd; border-radius: 6px; max-height: 220px; overflow-y: auto; z-index: 1050; display: none; box-shadow: 0 4px 12px rgba(0,0,0,.1); }
.product-dropdown .pd-item { padding: 8px 12px; cursor: pointer; font-size: 13px; border-bottom: 1px solid #f3f3f3; }
.product-dropdown .pd-item:hover { background: #f0f4ff; }
.product-dropdown .pd-item .pd-sku { color: #888; font-size: 12px; }
</style>
