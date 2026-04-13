<?php
$style = $form['settings']['form_style'] ?? 'classic';
$btnColor = $form['settings']['button_color'] ?? '#405189';
$btnText = $form['settings']['button_text'] ?? 'Gửi';

$themes = [
    'classic' => ['bg'=>'#fff','text'=>'#333','input_bg'=>'#fff','input_border'=>'#ddd','radius'=>'8px','body_bg'=>'#f8f9fa','label'=>'#444','shadow'=>'0 2px 12px rgba(0,0,0,0.08)'],
    'modern' => ['bg'=>'#f8f9fa','text'=>'#333','input_bg'=>'#fff','input_border'=>'#e9ecef','radius'=>'16px','body_bg'=>'#fff','label'=>'#555','shadow'=>'0 8px 32px rgba(0,0,0,0.06)'],
    'dark' => ['bg'=>'#1a1d21','text'=>'#e0e0e0','input_bg'=>'#2a2d32','input_border'=>'#3a3d42','radius'=>'12px','body_bg'=>'#111315','label'=>'#aaa','shadow'=>'0 4px 24px rgba(0,0,0,0.3)'],
    'gradient' => ['bg'=>'transparent','text'=>'#fff','input_bg'=>'rgba(255,255,255,0.15)','input_border'=>'rgba(255,255,255,0.3)','radius'=>'16px','body_bg'=>'linear-gradient(135deg,#667eea,#764ba2)','label'=>'rgba(255,255,255,0.8)','shadow'=>'none'],
    'minimal' => ['bg'=>'#fff','text'=>'#000','input_bg'=>'#fff','input_border'=>'#000','radius'=>'0','body_bg'=>'#fff','label'=>'#333','shadow'=>'none'],
];
$t = $themes[$style] ?? $themes['classic'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($form['name']) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: <?= $t['body_bg'] ?>; padding: 20px; min-height: 100vh; <?= str_starts_with($t['body_bg'], 'linear') ? 'display:flex;align-items:center;justify-content:center;' : '' ?> }
        .form-container { max-width: 500px; margin: 0 auto; background: <?= $t['bg'] ?>; border-radius: <?= $t['radius'] ?>; padding: 32px; box-shadow: <?= $t['shadow'] ?>; color: <?= $t['text'] ?>; }
        .form-title { font-size: 22px; font-weight: 600; margin-bottom: 4px; }
        .form-desc { color: <?= $t['label'] ?>; font-size: 14px; margin-bottom: 24px; }
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 14px; font-weight: 500; color: <?= $t['label'] ?>; margin-bottom: 6px; }
        .form-label .required { color: #e74c3c; }
        .form-input { width: 100%; padding: 10px 14px; border: 1px solid <?= $t['input_border'] ?>; border-radius: <?= $t['radius'] ?>; font-size: 15px; transition: border-color 0.2s; outline: none; background: <?= $t['input_bg'] ?>; color: <?= $t['text'] ?>; }
        .form-input:focus { border-color: <?= $btnColor ?>; box-shadow: 0 0 0 3px <?= $btnColor ?>22; }
        .form-input::placeholder { color: <?= $t['label'] ?>; opacity: 0.6; }
        textarea.form-input { resize: vertical; min-height: 80px; }
        .form-btn { width: 100%; padding: 12px; border: none; border-radius: <?= $t['radius'] ?>; font-size: 16px; font-weight: 600; color: #fff; cursor: pointer; transition: opacity 0.2s; background: <?= $btnColor ?>; }
        .form-btn:hover { opacity: 0.9; }
        .form-btn:disabled { opacity: 0.6; cursor: not-allowed; }
        .success-msg { text-align: center; padding: 40px 20px; }
        .success-msg .check { font-size: 48px; color: #22c55e; display: block; margin-bottom: 12px; }
        .success-msg p { font-size: 16px; }
        .error-msg { background: #fef2f2; color: #dc2626; padding: 10px 14px; border-radius: 8px; font-size: 14px; margin-bottom: 16px; display: none; }
        .powered { text-align: center; margin-top: 16px; font-size: 12px; color: <?= $t['label'] ?>; opacity: 0.6; }
        .powered a { color: inherit; text-decoration: none; }
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

            <button type="submit" class="form-btn" id="submitBtn"><?= e($btnText) ?></button>
        </form>

        <div class="powered">Powered by <a href="https://torycrm.com" target="_blank">ToryCRM</a></div>
    </div>

    <div class="form-container success-msg" id="successMsg" style="display:none">
        <span class="check">&#10003;</span>
        <p id="thankYouText"><?= e($form['settings']['thank_you_message'] ?? 'Cảm ơn bạn!') ?></p>
    </div>

    <script>
    function submitForm(e) {
        e.preventDefault();
        var btn = document.getElementById('submitBtn');
        var errorDiv = document.getElementById('errorMsg');
        btn.disabled = true; btn.textContent = 'Đang gửi...'; errorDiv.style.display = 'none';

        var formData = {};
        var inputs = document.getElementById('leadForm').elements;
        for (var i = 0; i < inputs.length; i++) { if (inputs[i].name) formData[inputs[i].name] = inputs[i].value; }
        formData._source_url = window.location.href;

        fetch('<?= url('form/' . $form['slug'] . '/submit') ?>', {
            method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(formData)
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                document.getElementById('formContainer').style.display = 'none';
                document.getElementById('successMsg').style.display = 'block';
                if (d.message) document.getElementById('thankYouText').textContent = d.message;
            } else {
                errorDiv.textContent = d.error || 'Có lỗi xảy ra'; errorDiv.style.display = 'block';
                btn.disabled = false; btn.textContent = '<?= e($btnText) ?>';
            }
        })
        .catch(function() {
            errorDiv.textContent = 'Lỗi kết nối'; errorDiv.style.display = 'block';
            btn.disabled = false; btn.textContent = '<?= e($btnText) ?>';
        });
        return false;
    }
    </script>
</body>
</html>
