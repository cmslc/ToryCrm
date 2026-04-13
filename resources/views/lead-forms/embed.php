<?php $pageTitle = 'Mã nhúng - ' . e($form['name']); ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Mã nhúng: <?= e($form['name']) ?></h4>
    <a href="<?= url('lead-forms') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ri-code-line me-2"></i> Mã nhúng iframe</h5></div>
            <div class="card-body">
                <p class="text-muted">Dán mã này vào trang WordPress hoặc bất kỳ website nào:</p>
                <div class="bg-dark text-light rounded p-3 mb-3">
                    <code id="embedIframe">&lt;iframe src="<?= url('form/' . $form['slug']) ?>" width="100%" height="500" frameborder="0" style="border:none"&gt;&lt;/iframe&gt;</code>
                </div>
                <button class="btn btn-primary" onclick="copyCode('embedIframe')"><i class="ri-file-copy-line me-1"></i> Copy</button>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ri-javascript-line me-2"></i> Mã nhúng JavaScript</h5></div>
            <div class="card-body">
                <p class="text-muted">Tự động render form trong div, hỗ trợ AJAX submit:</p>
                <div class="bg-dark text-light rounded p-3 mb-3">
                    <code id="embedJs">&lt;div id="torycrm-form"&gt;&lt;/div&gt;
&lt;script src="<?= url('form/' . $form['slug']) ?>?js=1"&gt;&lt;/script&gt;</code>
                </div>
                <button class="btn btn-primary" onclick="copyCode('embedJs')"><i class="ri-file-copy-line me-1"></i> Copy</button>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ri-links-line me-2"></i> Link trực tiếp</h5></div>
            <div class="card-body">
                <div class="input-group">
                    <input type="text" class="form-control" id="directLink" value="<?= url('form/' . $form['slug']) ?>" readonly>
                    <button class="btn btn-primary" onclick="navigator.clipboard.writeText(document.getElementById('directLink').value);this.innerHTML='<i class=\'ri-check-line\'></i> Đã copy';setTimeout(()=>this.innerHTML='Copy',2000)">Copy</button>
                </div>
                <small class="text-muted mt-2 d-block">Dùng link này để chia sẻ form qua email, chat, QR code...</small>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ri-eye-line me-2"></i> Xem trước</h5></div>
            <div class="card-body p-0">
                <iframe src="<?= url('form/' . $form['slug']) ?>" width="100%" height="500" frameborder="0" style="border:none"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
function copyCode(id) {
    var text = document.getElementById(id).textContent;
    navigator.clipboard.writeText(text).then(function() {
        event.target.innerHTML = '<i class="ri-check-line me-1"></i> Đã copy';
        setTimeout(function() { event.target.innerHTML = '<i class="ri-file-copy-line me-1"></i> Copy'; }, 2000);
    });
}
</script>
