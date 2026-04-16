<?php
// Usage: $perPageUrl = base URL for per_page links; $currentPerPage = current value
$currentPerPage = $currentPerPage ?? 20;
$perPageOptions = [10, 20, 50, 100];
?>
<select class="form-select" style="width:auto;min-width:90px" onchange="var url=new URL(window.location);url.searchParams.set('per_page',this.value);url.searchParams.delete('page');window.location=url">
    <?php foreach ($perPageOptions as $pp): ?>
    <option value="<?= $pp ?>" <?= $currentPerPage == $pp ? 'selected' : '' ?>><?= $pp ?> dòng</option>
    <?php endforeach; ?>
</select>
