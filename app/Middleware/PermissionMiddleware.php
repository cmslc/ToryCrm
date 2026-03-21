<?php

namespace App\Middleware;

use App\Services\PermissionService;

class PermissionMiddleware
{
    /**
     * Check permission or redirect with flash message.
     * Usage: PermissionMiddleware::check('contacts', 'delete');
     */
    public static function check(string $module, string $action): void
    {
        PermissionService::canOrFail($module, $action);
    }
}
