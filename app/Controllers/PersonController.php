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

        // Attach current employments + filter by access (hide company names
        // of companies the user can't access, so sale B doesn't learn where
        // sale C's customer lists their contacts).
        foreach ($persons as &$p) {
            $rows = Database::fetchAll(
                "SELECT cp.id, cp.position, c.id as contact_id, c.owner_id,
                        COALESCE(c.company_name, c.full_name, '?') as company_name
                 FROM contact_persons cp
                 LEFT JOIN contacts c ON c.id = cp.contact_id
                 WHERE cp.person_id = ? AND (cp.is_active IS NULL OR cp.is_active = 1)
                 ORDER BY cp.id DESC LIMIT 10",
                [$p['id']]
            );
            $visible = [];
            $hiddenCount = 0;
            foreach ($rows as $r) {
                if ($this->canAccessOwner((int)($r['owner_id'] ?? 0), 'contacts')) {
                    $visible[] = [
                        'id' => $r['id'],
                        'position' => $r['position'],
                        'contact_id' => $r['contact_id'],
                        'company_name' => $r['company_name'],
                    ];
                } else {
                    $hiddenCount++;
                }
            }
            $p['employments'] = array_slice($visible, 0, 5);
            $p['hidden_count'] = $hiddenCount;
        }
        unset($p);

        return $this->json($persons);
    }

    /**
     * Admin: list groups of persons with same phone/email (for manual merge).
     */
    public function duplicates()
    {
        $this->authorize('contacts', 'edit');
        $tid = Database::tenantId();

        // Groups by phone (more reliable than email for VN data)
        $phoneGroups = Database::fetchAll(
            "SELECT phone, GROUP_CONCAT(id ORDER BY id) as ids, COUNT(*) as cnt
             FROM persons
             WHERE tenant_id = ? AND phone IS NOT NULL AND phone != ''
             GROUP BY phone HAVING cnt > 1
             ORDER BY cnt DESC, phone",
            [$tid]
        );

        $groups = [];
        foreach ($phoneGroups as $g) {
            $ids = array_map('intval', explode(',', $g['ids']));
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $persons = Database::fetchAll(
                "SELECT p.*,
                    (SELECT COUNT(*) FROM contact_persons WHERE person_id = p.id) as emp_count,
                    (SELECT GROUP_CONCAT(DISTINCT COALESCE(c.company_name, c.full_name, '?') SEPARATOR ', ')
                     FROM contact_persons cp LEFT JOIN contacts c ON c.id = cp.contact_id
                     WHERE cp.person_id = p.id) as companies
                 FROM persons p WHERE p.id IN ({$placeholders})
                 ORDER BY p.id",
                $ids
            );
            $groups[] = [
                'key' => $g['phone'],
                'key_type' => 'phone',
                'persons' => $persons,
            ];
        }

        return $this->view('persons.duplicates', [
            'groups' => $groups,
            'totalGroups' => count($groups),
        ]);
    }

    /**
     * Merge source persons into a target person.
     * Re-points all contact_persons.person_id to target, then deletes source persons.
     * POST: target_id (int), source_ids (int[])
     */
    public function merge()
    {
        if (!$this->isPost()) return $this->redirect('persons/duplicates');
        $this->authorize('contacts', 'edit');
        $tid = Database::tenantId();

        $targetId = (int)$this->input('target_id');
        $sourceIds = $this->input('source_ids') ?? [];
        if (!is_array($sourceIds)) $sourceIds = [];
        $sourceIds = array_values(array_filter(array_map('intval', $sourceIds), fn($i) => $i > 0 && $i !== $targetId));

        if ($targetId <= 0 || empty($sourceIds)) {
            $this->setFlash('error', 'Vui lòng chọn person mục tiêu và nguồn.');
            return $this->redirect('persons/duplicates');
        }

        // Verify all IDs belong to current tenant
        $allIds = array_merge([$targetId], $sourceIds);
        $ph = implode(',', array_fill(0, count($allIds), '?'));
        $ownRows = Database::fetchAll(
            "SELECT id FROM persons WHERE id IN ({$ph}) AND tenant_id = ?",
            array_merge($allIds, [$tid])
        );
        $validIds = array_column($ownRows, 'id');
        if (count($validIds) !== count($allIds)) {
            $this->setFlash('error', 'Một số person không hợp lệ.');
            return $this->redirect('persons/duplicates');
        }

        Database::beginTransaction();
        try {
            // Fill empty fields in target from sources (soft-merge info)
            $target = Database::fetch("SELECT * FROM persons WHERE id = ?", [$targetId]);
            $fill = [];
            foreach (['full_name','phone','email','gender','date_of_birth','avatar','note'] as $f) {
                if (empty($target[$f])) {
                    foreach ($sourceIds as $sid) {
                        $src = Database::fetch("SELECT {$f} FROM persons WHERE id = ?", [$sid]);
                        if (!empty($src[$f])) { $fill[$f] = $src[$f]; break; }
                    }
                }
            }
            if (!empty($fill)) Database::update('persons', $fill, 'id = ?', [$targetId]);

            // Re-point contact_persons
            $srcPh = implode(',', array_fill(0, count($sourceIds), '?'));
            Database::query(
                "UPDATE contact_persons SET person_id = ? WHERE person_id IN ({$srcPh})",
                array_merge([$targetId], $sourceIds)
            );

            // Delete source persons
            Database::query(
                "DELETE FROM persons WHERE id IN ({$srcPh}) AND tenant_id = ?",
                array_merge($sourceIds, [$tid])
            );

            Database::commit();
        } catch (\Exception $e) {
            Database::rollback();
            $this->setFlash('error', 'Lỗi khi gộp: ' . $e->getMessage());
            return $this->redirect('persons/duplicates');
        }

        $this->setFlash('success', 'Đã gộp ' . count($sourceIds) . ' person vào #' . $targetId . '.');
        return $this->redirect('persons/duplicates');
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
