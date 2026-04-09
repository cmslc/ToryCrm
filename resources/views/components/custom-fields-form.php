<?php
/**
 * Reusable component to render custom fields in entity forms.
 * Usage: <?php include __DIR__ . '/../components/custom-fields-form.php'; ?>
 *
 * Expected variables:
 *   $module  - string: module name (contacts, deals, orders, tasks, tickets, products)
 *   $values  - array: key => value pairs for existing values (optional)
 */

use App\Services\CustomFieldService;
use Core\Database;

$_cfModule = $module ?? '';
$_cfValues = $values ?? [];

if (!empty($_cfModule)) {
    $_cfHtml = CustomFieldService::renderFormFields($_cfModule, Database::tenantId(), $_cfValues);
    if (!empty($_cfHtml)):
?>
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-input-method-line me-1"></i> Trường tùy chỉnh</h5>
    </div>
    <div class="card-body">
        <?= $_cfHtml ?>
    </div>
</div>
<?php
    endif;
}
?>
