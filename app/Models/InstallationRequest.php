<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class InstallationRequest extends Model
{
    protected string $table = 'installation_requests';

    public function generateCode(): string
    {
        $prefix = 'CF';
        $ymd = date('ymd'); // YYMMDD
        $tid = Database::tenantId();

        $last = Database::fetch(
            "SELECT code FROM installation_requests WHERE tenant_id = ? AND code LIKE ? ORDER BY id DESC LIMIT 1",
            [$tid, $prefix . $ymd . '%']
        );

        if ($last) {
            $num = (int)substr($last['code'], -3) + 1;
        } else {
            $num = 1;
        }

        return $prefix . $ymd . str_pad($num, 3, '0', STR_PAD_LEFT);
    }

    public function getItems(int $requestId): array
    {
        return Database::fetchAll(
            "SELECT iri.*, p.sku as p_sku
             FROM installation_request_items iri
             LEFT JOIN products p ON iri.product_id = p.id
             WHERE iri.request_id = ?
             ORDER BY iri.sort_order, iri.id",
            [$requestId]
        );
    }
}
