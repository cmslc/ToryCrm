<?php $noLayout = true; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hợp đồng <?= e($contract['contract_number']) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #525659; font-family: Arial, sans-serif; overflow: hidden; height: 100vh; }

        .print-toolbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            background: #323639; color: #fff; padding: 8px 16px; height: 44px;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0,0,0,.3);
        }
        .print-toolbar .title { font-size: 14px; opacity: .9; display: flex; align-items: center; gap: 12px; }
        .print-toolbar .actions { display: flex; gap: 6px; align-items: center; }
        .print-toolbar button, .print-toolbar a.btn-tool {
            background: rgba(255,255,255,.15); color: #fff; border: none;
            padding: 5px 14px; border-radius: 4px; cursor: pointer; font-size: 13px;
            text-decoration: none; display: inline-flex; align-items: center; gap: 5px;
        }
        .print-toolbar button:hover, .print-toolbar a.btn-tool:hover { background: rgba(255,255,255,.25); }
        .print-toolbar .btn-print { background: #4285f4; }
        .print-toolbar .btn-download { background: #34a853; }
        .print-toolbar .btn-email { background: #ea4335; }
        .print-toolbar select { background: #444; color: #fff; border: 1px solid #666; padding: 4px 8px; border-radius: 4px; font-size: 12px; }

        .print-layout { display: flex; height: calc(100vh - 44px); margin-top: 44px; }

        .print-sidebar {
            width: 160px; min-width: 160px;
            background: #3b3b3b; overflow-y: auto; padding: 12px 8px;
            border-right: 1px solid #555;
        }
        .thumb-item { cursor: pointer; margin-bottom: 12px; text-align: center; }
        .thumb-item .thumb-img {
            width: 100%; background: #fff; border: 2px solid transparent;
            border-radius: 2px; box-shadow: 0 1px 4px rgba(0,0,0,.4);
            aspect-ratio: 210/297; overflow: hidden; position: relative;
        }
        .thumb-item.active .thumb-img { border-color: #4285f4; }
        .thumb-item .thumb-label { color: #aaa; font-size: 11px; margin-top: 4px; }
        .thumb-item.active .thumb-label { color: #4285f4; font-weight: bold; }
        .thumb-inner { transform-origin: top left; position: absolute; top: 0; left: 0; }

        .print-main {
            flex: 1; overflow-y: auto; padding: 20px 0;
            display: flex; flex-direction: column; align-items: center;
        }

        .a4-sheet {
            width: 210mm; height: 297mm;
            padding: 20mm; background: #fff;
            position: relative; overflow: hidden;
            margin: 0 auto 20px; flex-shrink: 0;
            box-shadow: 0 2px 12px rgba(0,0,0,.4);
        }
        .a4-content {
            font-family: 'Times New Roman', serif;
            font-size: 13pt; line-height: 1.6; color: #000;
        }
        .a4-content h2 { font-size: 16pt; margin: 10px 0; }
        .a4-content h3 { font-size: 13pt; margin: 16px 0 8px; }
        .a4-content p { margin: 4px 0; }
        .a4-content table { border-collapse: collapse; width: 100%; }
        .a4-content table td, .a4-content table th { padding: 5px 8px; vertical-align: top; font-size: 11pt; }
        .a4-content table[border] td, .a4-content table[border] th { border: 1px solid #000; }
        /* Avoid breaking individual rows across pages in browser print */
        .a4-content tr { page-break-inside: avoid; break-inside: avoid; }
        .a4-content thead { display: table-header-group; }
        .a4-content tfoot { display: table-footer-group; }

        /* Watermark */
        .watermark {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%) rotate(-35deg);
            font-size: 72pt; font-weight: bold; opacity: .08; color: #000;
            pointer-events: none; white-space: nowrap; z-index: 10;
            font-family: Arial, sans-serif; letter-spacing: 10px;
        }

        /* Email modal */
        .email-overlay { display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,.5); z-index:200; }
        .email-modal { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); background:#fff; border-radius:8px; padding:24px; width:400px; color:#333; }

        @media print {
            body { background: #fff; overflow: visible; height: auto; }
            .print-toolbar, .print-sidebar, .email-overlay { display: none !important; }
            .print-layout { display: block; height: auto; margin-top: 0; }
            .print-main { overflow: visible; padding: 0; display: block; }
            .a4-sheet { margin: 0; box-shadow: none; page-break-after: always; height: auto; min-height: 297mm; }
            .watermark { display: none; }
            @page { margin: 0; size: A4; }
        }
    </style>
</head>
<body>
    <div class="print-toolbar">
        <div class="title">
            <a href="<?= url('contracts/' . $contract['id']) ?>" class="btn-tool">Quay lại</a>
            <span><?= e($contract['contract_number']) ?></span>
            <?php if (count($templates ?? []) > 1): ?>
            <select onchange="location.href='<?= url('contracts/' . $contract['id'] . '/print') ?>?template_id='+this.value">
                <?php foreach ($templates as $t): ?>
                <option value="<?= $t['id'] ?>" <?= ($selectedTemplateId ?? '') == $t['id'] ? 'selected' : '' ?>><?= e($t['name']) ?><?= $t['is_default'] ? ' (mặc định)' : '' ?></option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
        </div>
        <div class="actions">
            <button class="btn-email" onclick="document.getElementById('emailOverlay').style.display='block'">Gửi email</button>
            <a href="<?= url('contracts/' . $contract['id'] . '/download-pdf') ?>" class="btn-tool btn-download">Tải PDF</a>
            <button class="btn-print" onclick="window.print()">In</button>
        </div>
    </div>

    <div class="print-layout">
        <div class="print-sidebar" id="printSidebar"></div>
        <div class="print-main" id="pagesWrapper"></div>
    </div>

    <!-- Email Modal -->
    <div class="email-overlay" id="emailOverlay">
        <div class="email-modal">
            <h5 style="margin-bottom:16px">Gửi hợp đồng qua email</h5>
            <form method="POST" action="<?= url('contracts/' . $contract['id'] . '/email-pdf') ?>">
                <?= csrf_field() ?>
                <input type="email" name="email" class="form-control" placeholder="Email khách hàng" required style="padding:8px;width:100%;margin-bottom:12px;border:1px solid #ddd;border-radius:4px">
                <div style="display:flex;gap:8px;justify-content:flex-end">
                    <button type="button" onclick="document.getElementById('emailOverlay').style.display='none'" style="padding:8px 16px;border:1px solid #ddd;border-radius:4px;background:#fff;cursor:pointer">Hủy</button>
                    <button type="submit" style="padding:8px 16px;border:none;border-radius:4px;background:#ea4335;color:#fff;cursor:pointer">Gửi</button>
                </div>
            </form>
        </div>
    </div>

    <div id="rawContent" style="display:none">
        <div class="a4-content"><?= $html ?></div>
    </div>

    <script>
    (function() {
        // A4 @ 96dpi ≈ 1122px tall, padding 20mm ≈ 76px. Reserve ~20mm extra at the
        // bottom so the last line on a page never sits flush against the edge (and
        // never collides with the page-number strip anchored at bottom:8mm).
        var PAGE_HEIGHT = 1122, PADDING = 76, BOTTOM_SAFETY = 76;
        // Maximum allowed rendered height of the .a4-content container. Measured
        // via currentPage.offsetHeight which naturally includes collapsed margins
        // between paragraphs — avoids the drift that per-element offsetHeight sums
        // would cause.
        var MAX_CONTENT = PAGE_HEIGHT - PADDING * 2 - BOTTOM_SAFETY;
        var watermarkText = '<?= $watermark ?? '' ?>';
        var raw = document.getElementById('rawContent').querySelector('.a4-content');
        var wrapper = document.getElementById('pagesWrapper');
        var sidebar = document.getElementById('printSidebar');

        var currentPage = createPage();
        var children = Array.from(raw.children);

        function pageFull() {
            return currentPage.offsetHeight > MAX_CONTENT;
        }
        function pageEmpty() {
            return currentPage.children.length === 0;
        }
        function placeOnNewPage(el) {
            currentPage = createPage();
            currentPage.appendChild(el);
        }

        function splitBigTable(original) {
            var thead = original.tHead ? original.tHead.cloneNode(true) : null;
            var tfoot = original.tFoot ? original.tFoot.cloneNode(true) : null;
            var rows = [];
            Array.from(original.tBodies).forEach(function(tb) {
                Array.from(tb.rows).forEach(function(r) { rows.push(r); });
            });
            if (rows.length < 2) return false;

            function startChunk() {
                var t = original.cloneNode(false);
                if (thead) t.appendChild(thead.cloneNode(true));
                var tbody = document.createElement('tbody');
                t.appendChild(tbody);
                return { table: t, tbody: tbody };
            }

            var chunk = startChunk();
            currentPage.appendChild(chunk.table);
            rows.forEach(function(row) {
                var rClone = row.cloneNode(true);
                chunk.tbody.appendChild(rClone);
                if (pageFull() && chunk.tbody.rows.length > 1) {
                    chunk.tbody.removeChild(rClone);
                    currentPage = createPage();
                    chunk = startChunk();
                    currentPage.appendChild(chunk.table);
                    chunk.tbody.appendChild(rClone);
                }
            });
            if (tfoot) chunk.table.appendChild(tfoot.cloneNode(true));
            return true;
        }

        function isHeadingLike(el) {
            if (!el) return false;
            if (/^H[1-6]$/i.test(el.tagName)) return true;
            var txt = (el.textContent || '').trim();
            return /^Điều\s+\d/i.test(txt) || /^ĐIỀU\s+\d/.test(txt);
        }

        children.forEach(function(el, idx) {
            var clone = el.cloneNode(true);
            currentPage.appendChild(clone);

            if (!pageFull()) {
                // Orphan protection: if we just placed a heading, make sure the
                // next element also fits on this page — otherwise push the pair.
                var next = children[idx + 1];
                if (isHeadingLike(clone) && next) {
                    var probe = next.cloneNode(true);
                    currentPage.appendChild(probe);
                    var wouldOverflow = pageFull();
                    currentPage.removeChild(probe);
                    if (wouldOverflow) {
                        currentPage.removeChild(clone);
                        placeOnNewPage(clone);
                        return;
                    }
                }
                return;
            }

            // Overflow. Remove and decide.
            currentPage.removeChild(clone);

            // Tall table on a non-empty page: start a new page then split by rows.
            // On an already-empty page: split in place.
            if (clone.tagName === 'TABLE') {
                if (pageEmpty()) {
                    if (splitBigTable(clone)) return;
                } else {
                    currentPage = createPage();
                    if (splitBigTable(clone)) return;
                }
            }

            placeOnNewPage(clone);
        });
        if (children.length === 0) currentPage.innerHTML = raw.innerHTML;

        function createPage() {
            var sheet = document.createElement('div');
            sheet.className = 'a4-sheet';
            if (watermarkText) {
                var wm = document.createElement('div');
                wm.className = 'watermark';
                wm.textContent = watermarkText;
                sheet.appendChild(wm);
            }
            var content = document.createElement('div');
            content.className = 'a4-content';
            sheet.appendChild(content);
            wrapper.appendChild(sheet);
            return content;
        }

        var pages = wrapper.querySelectorAll('.a4-sheet');
        pages.forEach(function(page, i) {
            var num = document.createElement('div');
            num.style.cssText = 'position:absolute;bottom:8mm;width:100%;text-align:center;font-size:9pt;color:#999;font-family:Arial;left:0';
            num.textContent = 'Trang ' + (i + 1) + ' / ' + pages.length;
            page.appendChild(num);
            page.id = 'page-' + i;

            var thumb = document.createElement('div');
            thumb.className = 'thumb-item' + (i === 0 ? ' active' : '');
            var thumbImg = document.createElement('div');
            thumbImg.className = 'thumb-img';
            var thumbInner = document.createElement('div');
            thumbInner.className = 'thumb-inner';
            var clone = page.cloneNode(true);
            clone.style.cssText = 'width:210mm;height:297mm;padding:20mm;position:absolute;top:0;left:0';
            thumbInner.style.transform = 'scale(' + (140 / 793) + ')';
            thumbInner.style.width = '210mm';
            thumbInner.style.height = '297mm';
            thumbInner.appendChild(clone);
            thumbImg.appendChild(thumbInner);
            var label = document.createElement('div');
            label.className = 'thumb-label';
            label.textContent = (i + 1);
            thumb.appendChild(thumbImg);
            thumb.appendChild(label);
            sidebar.appendChild(thumb);

            thumb.addEventListener('click', function() {
                document.querySelectorAll('.thumb-item').forEach(function(t) { t.classList.remove('active'); });
                this.classList.add('active');
                page.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });

        document.getElementById('pagesWrapper').addEventListener('scroll', function() {
            var scrollTop = this.scrollTop, activeIdx = 0;
            pages.forEach(function(p, i) { if (p.offsetTop - wrapper.offsetTop <= scrollTop + 100) activeIdx = i; });
            document.querySelectorAll('.thumb-item').forEach(function(t, i) { t.classList.toggle('active', i === activeIdx); });
        });
    })();
    </script>
</body>
</html>
