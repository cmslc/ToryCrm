<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class PersonController extends Controller
{
    /**
     * AJAX search: find persons by phone/email/name within current tenant.
     * Returns person + list of current employments.
     */
    public function search()
    {
        $this->authorize('contacts', 'view');
        $q = trim($this->input('q') ?? '');
        if (strlen($q) < 2) return $this->json([]);

        $tid = Database::tenantId();
        $like = '%' . $q . '%';
        $isPhoneLike = preg_match('/^[0-9+\s\-\.]{3,}$/', $q);

        // Phone match is exact-prefix for accuracy; name/email uses LIKE
        if ($isPhoneLike) {
            $persons = Database::fetchAll(
                "SELECT id, full_name, phone, email, avatar FROM persons
                 WHERE tenant_id = ? AND is_hidden = 0 AND phone LIKE ?
                 ORDER BY full_name LIMIT 10",
                [$tid, $q . '%']
            );
        } else {
            $persons = Database::fetchAll(
                "SELECT id, full_name, phone, email, avatar FROM persons
                 WHERE tenant_id = ? AND is_hidden = 0
                   AND (full_name LIKE ? OR email LIKE ?)
                 ORDER BY full_name LIMIT 10",
                [$tid, $like, $like]
            );
        }

        // Attach current employments (active contact_persons) for context
        foreach ($persons as &$p) {
            $p['employments'] = Database::fetchAll(
                "SELECT cp.id, cp.position, c.id as contact_id, COALESCE(c.company_name, c.full_name, '?') as company_name
                 FROM contact_persons cp
                 LEFT JOIN contacts c ON c.id = cp.contact_id
                 WHERE cp.person_id = ? AND (cp.is_active IS NULL OR cp.is_active = 1)
                 ORDER BY cp.id DESC LIMIT 5",
                [$p['id']]
            );
        }
        unset($p);

        return $this->json($persons);
    }

    /**
     * Person profile — show info + full employment history + linked deals/quotations/orders.
     */
    public function show($id)
    {
        $this->authorize('contacts', 'view');
        $tid = Database::tenantId();
        $person = Database::fetch(
            "SELECT * FROM persons WHERE id = ? AND tenant_id = ?",
            [(int)$id, $tid]
        );
        if (!$person) {
            $this->setFlash('error', 'Không tìm thấy người liên hệ.');
            return $this->redirect('contacts');
        }

        // Employment history, newest first
        $employments = Database::fetchAll(
            "SELECT cp.*, c.company_name, c.full_name as contact_full_name, c.tax_code, c.owner_id, u.name as owner_name
             FROM contact_persons cp
             LEFT JOIN contacts c ON c.id = cp.contact_id
             LEFT JOIN users u ON u.id = c.owner_id
             WHERE cp.person_id = ?
             ORDER BY (cp.is_active IS NULL OR cp.is_active = 1) DESC, cp.start_date DESC, cp.id DESC",
            [(int)$id]
        );

        // Filter employments: hide data at companies the user can't access
        // (but keep the company name visible so user knows person works there)
        foreach ($employments as &$emp) {
            $emp['can_access'] = $this->canAccessOwner((int)($emp['owner_id'] ?? 0), 'contacts');
        }
        unset($emp);

        return $this->view('persons.show', [
            'person' => $person,
            'employments' => $employments,
        ]);
    }
}
