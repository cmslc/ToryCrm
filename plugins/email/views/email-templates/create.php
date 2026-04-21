<?php $pageTitle = 'Tạo Email Template'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Tạo Email Template</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('email-templates') ?>">Email Templates</a></li>
                <li class="breadcrumb-item active">Tạo mới</li>
            </ol>
        </div>

        <form method="POST" action="<?= url('email-templates/store') ?>" id="template-form">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Nội dung template</h5></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Tên template <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" required placeholder="VD: Chào mừng khách hàng mới">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tiêu đề email</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="subject" id="subject-input" placeholder="VD: Chào mừng {{ten_kh}} đến với {{ten_cty}}">
                                    <button type="button" class="btn btn-soft-primary dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="ri-price-tag-3-line"></i> Merge tags
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" id="subject-merge-tags">
                                        <li><a class="dropdown-item merge-tag" href="#" data-tag="{{ten_kh}}" data-target="subject">Tên khách hàng</a></li>
                                        <li><a class="dropdown-item merge-tag" href="#" data-tag="{{email_kh}}" data-target="subject">Email khách hàng</a></li>
                                        <li><a class="dropdown-item merge-tag" href="#" data-tag="{{ten_cty}}" data-target="subject">Tên công ty</a></li>
                                        <li><a class="dropdown-item merge-tag" href="#" data-tag="{{nguoi_phu_trach}}" data-target="subject">Người phụ trách</a></li>
                                        <li><a class="dropdown-item merge-tag" href="#" data-tag="{{ma_don}}" data-target="subject">Mã đơn hàng</a></li>
                                        <li><a class="dropdown-item merge-tag" href="#" data-tag="{{ngay}}" data-target="subject">Ngày</a></li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Toolbar -->
                            <div class="mb-2">
                                <label class="form-label">Nội dung email</label>
                                <div class="btn-toolbar bg-light rounded p-2 gap-1" id="editor-toolbar">
                                    <button type="button" class="btn btn-soft-dark" onclick="execCmd('bold')" title="Đậm"><i class="ri-bold"></i></button>
                                    <button type="button" class="btn btn-soft-dark" onclick="execCmd('italic')" title="Nghiêng"><i class="ri-italic"></i></button>
                                    <button type="button" class="btn btn-soft-dark" onclick="execCmd('underline')" title="Gạch chân"><i class="ri-underline"></i></button>
                                    <div class="vr mx-1"></div>
                                    <button type="button" class="btn btn-soft-dark" onclick="execCmd('justifyLeft')" title="Canh trái"><i class="ri-align-left"></i></button>
                                    <button type="button" class="btn btn-soft-dark" onclick="execCmd('justifyCenter')" title="Canh giữa"><i class="ri-align-center"></i></button>
                                    <button type="button" class="btn btn-soft-dark" onclick="execCmd('justifyRight')" title="Canh phải"><i class="ri-align-right"></i></button>
                                    <div class="vr mx-1"></div>
                                    <button type="button" class="btn btn-soft-dark" onclick="execCmd('insertUnorderedList')" title="Danh sách"><i class="ri-list-unordered"></i></button>
                                    <button type="button" class="btn btn-soft-dark" onclick="execCmd('insertOrderedList')" title="Danh sách số"><i class="ri-list-ordered"></i></button>
                                    <div class="vr mx-1"></div>
                                    <button type="button" class="btn btn-soft-dark" onclick="insertLink()" title="Chèn liên kết"><i class="ri-link"></i></button>
                                    <button type="button" class="btn btn-soft-dark" onclick="insertImage()" title="Chèn ảnh"><i class="ri-image-line"></i></button>
                                    <div class="vr mx-1"></div>
                                    <input type="color" class="form-control form-control-color" id="text-color" value="#000000" title="Màu chữ" style="width:32px;height:32px;padding:2px;" onchange="execCmdVal('foreColor', this.value)">
                                </div>
                            </div>

                            <!-- Editable area -->
                            <div id="email-editor" contenteditable="true" class="form-control" style="min-height: 300px; max-height: 600px; overflow-y: auto; font-size: 14px; line-height: 1.6;"></div>
                            <input type="hidden" name="body" id="body-input">

                            <!-- Merge Tags -->
                            <div class="mt-3">
                                <label class="form-label text-muted">Merge tags (nhấn để chèn vào nội dung):</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php
                                    $mergeTags = [
                                        '{{ten_kh}}' => 'Tên khách hàng',
                                        '{{email_kh}}' => 'Email KH',
                                        '{{sdt_kh}}' => 'SĐT KH',
                                        '{{ten_cty}}' => 'Tên công ty',
                                        '{{nguoi_phu_trach}}' => 'Người phụ trách',
                                        '{{email_npt}}' => 'Email NPT',
                                        '{{sdt_npt}}' => 'SĐT NPT',
                                        '{{ten_sp}}' => 'Tên sản phẩm',
                                        '{{don_gia}}' => 'Đơn giá',
                                        '{{ma_don}}' => 'Mã đơn hàng',
                                        '{{ngay}}' => 'Ngày',
                                    ];
                                    foreach ($mergeTags as $tag => $label): ?>
                                        <button type="button" class="btn btn-soft-primary body-merge-tag" data-tag="<?= $tag ?>" title="<?= $label ?>">
                                            <?= $tag ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Cài đặt</h5></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Danh mục</label>
                                <select name="category" class="form-select">
                                    <option value="general">Chung</option>
                                    <option value="sales">Bán hàng</option>
                                    <option value="marketing">Marketing</option>
                                    <option value="support">Hỗ trợ</option>
                                    <option value="follow_up">Theo dõi</option>
                                    <option value="welcome">Chào mừng</option>
                                    <option value="invoice">Hóa đơn</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Xem trước</h5></div>
                        <div class="card-body">
                            <button type="button" class="btn btn-soft-info w-100 mb-2" id="preview-btn"><i class="ri-eye-line me-1"></i> Xem trước</button>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Gửi thử</h5></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <input type="email" class="form-control" id="test-email" placeholder="Nhập email để gửi thử">
                            </div>
                            <button type="button" class="btn btn-soft-warning w-100" id="send-test-btn"><i class="ri-send-plane-line me-1"></i> Gửi thử</button>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-save-line me-1"></i> Lưu template</button>
                            <a href="<?= url('email-templates') ?>" class="btn btn-soft-secondary">Hủy</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Preview Modal -->
        <div class="modal fade" id="previewModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Xem trước email</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0">
                        <iframe id="preview-iframe" style="width:100%;height:500px;border:none;"></iframe>
                    </div>
                </div>
            </div>
        </div>

        <script>
        // Rich text editor commands
        function execCmd(cmd) {
            document.execCommand(cmd, false, null);
            document.getElementById('email-editor').focus();
        }
        function execCmdVal(cmd, val) {
            document.execCommand(cmd, false, val);
            document.getElementById('email-editor').focus();
        }

        function insertLink() {
            const url = prompt('Nhập URL:', 'https://');
            if (url) document.execCommand('createLink', false, url);
        }

        function insertImage() {
            const url = prompt('Nhập URL ảnh:', 'https://');
            if (url) document.execCommand('insertImage', false, url);
        }

        // Merge tag insertion for subject
        document.querySelectorAll('.merge-tag').forEach(el => {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                const input = document.getElementById('subject-input');
                const tag = this.dataset.tag;
                const pos = input.selectionStart || input.value.length;
                input.value = input.value.substring(0, pos) + tag + input.value.substring(pos);
                input.focus();
            });
        });

        // Merge tag insertion for body
        document.querySelectorAll('.body-merge-tag').forEach(el => {
            el.addEventListener('click', function() {
                const editor = document.getElementById('email-editor');
                editor.focus();
                document.execCommand('insertText', false, this.dataset.tag);
            });
        });

        // Sync body to hidden input before submit
        document.getElementById('template-form').addEventListener('submit', function() {
            document.getElementById('body-input').value = document.getElementById('email-editor').innerHTML;
        });

        // Preview (client-side for unsaved)
        document.getElementById('preview-btn').addEventListener('click', function() {
            const subject = document.getElementById('subject-input').value || '(Không có tiêu đề)';
            const body = document.getElementById('email-editor').innerHTML;

            // Sample data replacement
            const sampleData = {
                '{{ten_kh}}': 'Nguyễn Văn A',
                '{{email_kh}}': 'nguyenvana@email.com',
                '{{sdt_kh}}': '0901234567',
                '{{ten_cty}}': 'Công ty TNHH ABC',
                '{{nguoi_phu_trach}}': 'Trần Thị B',
                '{{email_npt}}': 'tranthib@company.com',
                '{{sdt_npt}}': '0987654321',
                '{{ten_sp}}': 'Sản phẩm mẫu',
                '{{don_gia}}': '1,500,000 VNĐ',
                '{{ma_don}}': 'ORD-2026-001',
                '{{ngay}}': new Date().toLocaleDateString('vi-VN'),
            };

            let previewBody = body;
            let previewSubject = subject;
            for (const [tag, val] of Object.entries(sampleData)) {
                previewBody = previewBody.split(tag).join(val);
                previewSubject = previewSubject.split(tag).join(val);
            }

            const html = `<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f5;padding:20px 0;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
<tr><td style="background:#405189;padding:24px 30px;"><h1 style="margin:0;color:#fff;font-size:20px;">ToryCRM</h1></td></tr>
<tr><td style="padding:10px 30px;background:#f0f0f0;"><strong>Tiêu đề:</strong> ${previewSubject}</td></tr>
<tr><td style="padding:30px;">${previewBody}</td></tr>
<tr><td style="background:#f8f9fa;padding:16px 30px;text-align:center;font-size:12px;color:#878a99;">Email được gửi từ ToryCRM</td></tr>
</table></td></tr></table></body></html>`;

            const iframe = document.getElementById('preview-iframe');
            const modal = new bootstrap.Modal(document.getElementById('previewModal'));
            modal.show();
            setTimeout(() => {
                iframe.contentDocument.open();
                iframe.contentDocument.write(html);
                iframe.contentDocument.close();
            }, 200);
        });

        // Send test (only works for saved templates, show alert for unsaved)
        document.getElementById('send-test-btn').addEventListener('click', function() {
            const email = document.getElementById('test-email').value.trim();
            if (!email) { alert('Vui lòng nhập email để gửi thử'); return; }
            alert('Vui lòng lưu template trước, sau đó gửi thử từ trang chỉnh sửa.');
        });
        </script>
