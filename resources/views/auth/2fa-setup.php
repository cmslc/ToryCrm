<?php $pageTitle = 'Thiết lập 2FA'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Thiết lập xác thực 2 bước (2FA)</h4>
</div>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-body">
                <ol class="mb-4">
                    <li class="mb-2">Cài app <strong>Google Authenticator</strong> / <strong>Authy</strong> / <strong>Microsoft Authenticator</strong> trên điện thoại.</li>
                    <li class="mb-2">Mở app → <strong>Quét mã QR</strong> bên dưới:</li>
                </ol>

                <div class="text-center mb-3 p-3 border rounded bg-light">
                    <div id="qrcode" class="d-inline-block bg-white p-3 rounded"></div>
                    <div class="mt-2 text-muted fs-13">Hoặc nhập mã thủ công:</div>
                    <code class="user-select-all fs-18 d-inline-block mt-1 px-3 py-1 bg-white rounded border" style="letter-spacing:2px"><?= e($secret) ?></code>
                </div>

                <form method="POST" action="<?= url('settings/2fa/enable') ?>">
                    <?= csrf_field() ?>
                    <label class="form-label">3. Sau khi app hiển thị mã 6 chữ số, nhập vào đây để bật:</label>
                    <div class="d-flex gap-2 mb-3">
                        <input type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" class="form-control form-control-lg text-center" placeholder="123456" required style="letter-spacing:8px;font-size:24px">
                        <button type="submit" class="btn btn-primary px-4"><i class="ri-shield-check-line me-1"></i> Bật 2FA</button>
                    </div>
                </form>

                <div class="alert alert-warning mb-0 mt-3">
                    <i class="ri-information-line me-1"></i> Lưu backup code sau khi bật 2FA — cần khi mất điện thoại.
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
new QRCode(document.getElementById('qrcode'), {
    text: <?= json_encode($otpauthUrl) ?>,
    width: 192, height: 192,
    correctLevel: QRCode.CorrectLevel.M
});
</script>
