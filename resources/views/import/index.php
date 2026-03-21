<?php $pageTitle = 'Import / Export'; ?>

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Import / Export</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Import Card -->
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="ri-upload-2-line me-1"></i> Import dữ liệu</h5>
                    </div>
                    <div class="card-body">
                        <!-- Tabs -->
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#tab-import-contacts" role="tab">
                                    <i class="ri-contacts-line me-1"></i> Khách hàng
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tab-import-products" role="tab">
                                    <i class="ri-shopping-bag-line me-1"></i> Sản phẩm
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content pt-3">
                            <!-- Import Contacts -->
                            <div class="tab-pane active" id="tab-import-contacts" role="tabpanel">
                                <form method="POST" action="<?= url('import-export/import-contacts') ?>" enctype="multipart/form-data">
                                    <?= csrf_field() ?>
                                    <div class="mb-3">
                                        <label for="file-contacts" class="form-label">Chọn file CSV</label>
                                        <input type="file" class="form-control" id="file-contacts" name="file" accept=".csv" required>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-upload-2-line me-1"></i> Import khách hàng
                                        </button>
                                        <a href="<?= url('import-export/download-template/contacts') ?>" class="btn btn-soft-info">
                                            <i class="ri-download-line me-1"></i> Tải template
                                        </a>
                                    </div>
                                </form>
                                <div class="alert alert-light border mb-0">
                                    <i class="ri-information-line me-1"></i>
                                    <strong>Lưu ý:</strong> File CSV phải dùng mã hóa UTF-8, phân cách bằng dấu phẩy (,). Dòng đầu tiên là tiêu đề cột.
                                    <br>Các cột bắt buộc: <code>first_name</code>. Các cột khác: <code>last_name, email, phone, company, source, status</code>.
                                </div>
                            </div>

                            <!-- Import Products -->
                            <div class="tab-pane" id="tab-import-products" role="tabpanel">
                                <form method="POST" action="<?= url('import-export/import-products') ?>" enctype="multipart/form-data">
                                    <?= csrf_field() ?>
                                    <div class="mb-3">
                                        <label for="file-products" class="form-label">Chọn file CSV</label>
                                        <input type="file" class="form-control" id="file-products" name="file" accept=".csv" required>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-upload-2-line me-1"></i> Import sản phẩm
                                        </button>
                                        <a href="<?= url('import-export/download-template/products') ?>" class="btn btn-soft-info">
                                            <i class="ri-download-line me-1"></i> Tải template
                                        </a>
                                    </div>
                                </form>
                                <div class="alert alert-light border mb-0">
                                    <i class="ri-information-line me-1"></i>
                                    <strong>Lưu ý:</strong> File CSV phải dùng mã hóa UTF-8, phân cách bằng dấu phẩy (,). Dòng đầu tiên là tiêu đề cột.
                                    <br>Các cột bắt buộc: <code>name</code>. Các cột khác: <code>sku, type, unit, price, cost_price, category, description</code>.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Card -->
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="ri-download-2-line me-1"></i> Export dữ liệu</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Khoảng thời gian (tùy chọn)</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="date" class="form-control" id="export-date-from" placeholder="Từ ngày">
                                </div>
                                <div class="col-6">
                                    <input type="date" class="form-control" id="export-date-to" placeholder="Đến ngày">
                                </div>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="<?= url('import-export/export-contacts') ?>" class="btn btn-success export-btn" id="btn-export-contacts">
                                <i class="ri-contacts-line me-1"></i> Export Khách hàng
                            </a>
                            <a href="<?= url('import-export/export-products') ?>" class="btn btn-info export-btn" id="btn-export-products">
                                <i class="ri-shopping-bag-line me-1"></i> Export Sản phẩm
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dateFrom = document.getElementById('export-date-from');
            const dateTo = document.getElementById('export-date-to');
            const btnContacts = document.getElementById('btn-export-contacts');
            const btnProducts = document.getElementById('btn-export-products');

            const baseUrlContacts = '<?= url('import-export/export-contacts') ?>';
            const baseUrlProducts = '<?= url('import-export/export-products') ?>';

            function updateExportUrls() {
                let params = [];
                if (dateFrom.value) params.push('date_from=' + dateFrom.value);
                if (dateTo.value) params.push('date_to=' + dateTo.value);
                const qs = params.length > 0 ? '?' + params.join('&') : '';
                btnContacts.href = baseUrlContacts + qs;
                btnProducts.href = baseUrlProducts + qs;
            }

            dateFrom.addEventListener('change', updateExportUrls);
            dateTo.addEventListener('change', updateExportUrls);
        });
        </script>
