<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class IntegrationController extends Controller
{
    public function index()
    {
        // Fetch all integrations for current tenant
        $integrations = [];
        try {
            $integrations = Database::fetchAll(
                "SELECT * FROM integrations WHERE tenant_id = ? ORDER BY name",
                [$this->tenantId()]
            );
        } catch (\Throwable $e) {
            // Table may not exist yet
        }

        // Build status map by type
        $statusMap = [];
        foreach ($integrations as $int) {
            $statusMap[$int['provider']] = $int;
        }

        return $this->view('integrations.index', [
            'integrations' => $integrations,
            'statusMap' => $statusMap,
        ]);
    }
}
