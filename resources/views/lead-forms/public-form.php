<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($form['name']) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8f9fa; padding: 20px; }
        .form-container { max-width: 500px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 32px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .form-title { font-size: 22px; font-weight: 600; margin-bottom: 4px; color: #333; }
        .form-desc { color: #666; font-size: 14px; margin-bottom: 24px; }
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 14px; font-weight: 500; color: #444; margin-bottom: 6px; }
        .form-label .required { color: #e74c3c; }
        .form-input { width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 8px; font-size: 15px; transition: border-color 0.2s; outline: none; }
        .form-input:focus { border-color: <?= e($form['settings']['button_color'] ?? '#405189') ?>; box-shadow: 0 0 0 3px <?= e($form['settings']['button_color'] ?? '#405189') ?>22; }
        textarea.form-input { resize: vertical; min-height: 80px; }
        .form-btn { width: 100%; padding: 12px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; color: #fff; cursor: pointer; transition: opacity 0.2s; }
        .form-btn:hover { opacity: 0.9; }
        .form-btn:disabled { opacity: 0.6; cursor: not-allowed; }
        .success-msg { text-align: center; padding: 40px 20px; }
        .success-msg i { font-size: 48px; color: #22c55e; display: block; margin-bottom: 12px; }
        .success-msg p { font-size: 16px; color: #333; }
        .error-msg { background: #fef2f2; color: #dc2626; padding: 10px 14px; border-radius: 8px; font-size: 14px; margin-bottom: 16px; display: none; }
        .powered { text-align: center; margin-top: 16px; font-size: 12px; color: #aaa; }
        .powered a { color: #999; text-decoration: none; }
    </style>
</head>
<body>
    <div class="form-container" id="formContainer">
        <div class="form-title"><?= e($form['name']) ?></div>
        <?php if ($form['description']): ?><div class="form-desc"><?= e($form['description']) ?></div><?php endif; ?>

        <div class="error-msg" id="errorMsg"></div>

        <form id="leadForm" onsubmit="return submitForm(event)">
            <?php foreach ($form['fields'] as $f): ?>
            <div class="form-group">
                <label class="form-label"><?= e($f['label']) ?> <?php if ($f['required']): ?><span class="required">*</span><?php endif; ?></label>
                <?php if ($f['type'] === 'textarea'): ?>
                <textarea class="form-input" name="<?= e($f['name']) ?>" <?= $f['required'] ? 'required' : '' ?> placeholder="<?= e($f['label']) ?>"></textarea>
                <?php elseif ($f['type'] === 'select'): ?>
                <select class="form-input" name="<?= e($f['name']) ?>" <?= $f['required'] ? 'required' : '' ?>>
                    <option value="">Chọn...</option>
                    <?php foreach ($f['options'] ?? [] as $opt): ?><option value="<?= e($opt) ?>"><?= e($opt) ?></option><?php endforeach; ?>
                </select>
                <?php else: ?>
                <input type="<?= e($f['type']) ?>" class="form-input" name="<?= e($f['name']) ?>" <?= $f['required'] ? 'required' : '' ?> placeholder="<?= e($f['label']) ?>">
                <?php endif; ?>
            </div>
            <?php endforeach; ?>

            <button type="submit" class="form-btn" id="submitBtn" style="background:<?= e($form['settings']['button_color'] ?? '#405189') ?>">
                <?= e($form['settings']['button_text'] ?? 'Gửi') ?>
            </button>
        </form>

        <div class="powered">Powered by <a href="https://torycrm.com" target="_blank">ToryCRM</a></div>
    </div>

    <div class="form-container success-msg" id="successMsg" style="display:none">
        <i>&#10003;</i>
        <p id="thankYouText"><?= e($form['settings']['thank_you_message'] ?? 'Cảm ơn bạn!') ?></p>
    </div>

    <script>
    function submitForm(e) {
        e.preventDefault();
        var btn = document.getElementById('submitBtn');
        var errorDiv = document.getElementById('errorMsg');
        btn.disabled = true;
        btn.textContent = 'Đang gửi...';
        errorDiv.style.display = 'none';

        var formData = {};
        var inputs = document.getElementById('leadForm').elements;
        for (var i = 0; i < inputs.length; i++) {
            if (inputs[i].name) formData[inputs[i].name] = inputs[i].value;
        }
        formData._source_url = window.location.href;

        fetch('<?= url('form/' . $form['slug'] . '/submit') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(formData)
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                document.getElementById('formContainer').style.display = 'none';
                document.getElementById('successMsg').style.display = 'block';
                if (d.message) document.getElementById('thankYouText').textContent = d.message;
            } else {
                errorDiv.textContent = d.error || 'Có lỗi xảy ra';
                errorDiv.style.display = 'block';
                btn.disabled = false;
                btn.textContent = '<?= e($form['settings']['button_text'] ?? 'Gửi') ?>';
            }
        })
        .catch(function() {
            errorDiv.textContent = 'Lỗi kết nối';
            errorDiv.style.display = 'block';
            btn.disabled = false;
            btn.textContent = '<?= e($form['settings']['button_text'] ?? 'Gửi') ?>';
        });

        return false;
    }
    </script>
</body>
</html>
