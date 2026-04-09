<?php

namespace App\Services;

use Core\Database;

class DuplicateDetector
{
    /**
     * Scan contacts for duplicates: same email OR same phone
     */
    public static function scanContacts(int $tenantId): int
    {
        $groupsCreated = 0;

        // Find contacts with same email (non-empty)
        $emailDupes = Database::fetchAll(
            "SELECT email, GROUP_CONCAT(id ORDER BY id) as ids, COUNT(*) as cnt
             FROM contacts
             WHERE tenant_id = ? AND email IS NOT NULL AND email != '' AND deleted_at IS NULL
             GROUP BY email
             HAVING cnt > 1",
            [$tenantId]
        );

        foreach ($emailDupes as $dupe) {
            $ids = explode(',', $dupe['ids']);
            $exists = Database::fetch(
                "SELECT id FROM duplicate_groups
                 WHERE tenant_id = ? AND entity_type = 'contact' AND match_field = 'email' AND match_value = ? AND status = 'pending'",
                [$tenantId, $dupe['email']]
            );
            if ($exists) continue;

            $groupId = Database::insert('duplicate_groups', [
                'tenant_id' => $tenantId,
                'entity_type' => 'contact',
                'match_field' => 'email',
                'match_value' => $dupe['email'],
                'record_ids' => json_encode(array_map('intval', $ids)),
                'status' => 'pending',
            ]);

            foreach ($ids as $contactId) {
                Database::insert('duplicate_group_items', [
                    'group_id' => $groupId,
                    'entity_id' => (int) $contactId,
                ]);
            }
            $groupsCreated++;
        }

        // Find contacts with same phone (non-empty)
        $phoneDupes = Database::fetchAll(
            "SELECT phone, GROUP_CONCAT(id ORDER BY id) as ids, COUNT(*) as cnt
             FROM contacts
             WHERE tenant_id = ? AND phone IS NOT NULL AND phone != '' AND deleted_at IS NULL
             GROUP BY phone
             HAVING cnt > 1",
            [$tenantId]
        );

        foreach ($phoneDupes as $dupe) {
            $ids = explode(',', $dupe['ids']);
            $exists = Database::fetch(
                "SELECT id FROM duplicate_groups
                 WHERE tenant_id = ? AND entity_type = 'contact' AND match_field = 'phone' AND match_value = ? AND status = 'pending'",
                [$tenantId, $dupe['phone']]
            );
            if ($exists) continue;

            $groupId = Database::insert('duplicate_groups', [
                'tenant_id' => $tenantId,
                'entity_type' => 'contact',
                'match_field' => 'phone',
                'match_value' => $dupe['phone'],
                'record_ids' => json_encode(array_map('intval', $ids)),
                'status' => 'pending',
            ]);

            foreach ($ids as $contactId) {
                Database::insert('duplicate_group_items', [
                    'group_id' => $groupId,
                    'entity_id' => (int) $contactId,
                ]);
            }
            $groupsCreated++;
        }

        return $groupsCreated;
    }

    /**
     * Scan companies for duplicates: same tax_code OR exact name match
     */
    public static function scanCompanies(int $tenantId): int
    {
        $groupsCreated = 0;

        // Same tax_code
        $taxDupes = Database::fetchAll(
            "SELECT tax_code, GROUP_CONCAT(id ORDER BY id) as ids, COUNT(*) as cnt
             FROM companies
             WHERE tenant_id = ? AND tax_code IS NOT NULL AND tax_code != ''
             GROUP BY tax_code
             HAVING cnt > 1",
            [$tenantId]
        );

        foreach ($taxDupes as $dupe) {
            $ids = explode(',', $dupe['ids']);
            $exists = Database::fetch(
                "SELECT id FROM duplicate_groups
                 WHERE tenant_id = ? AND entity_type = 'company' AND match_field = 'tax_code' AND match_value = ? AND status = 'pending'",
                [$tenantId, $dupe['tax_code']]
            );
            if ($exists) continue;

            $groupId = Database::insert('duplicate_groups', [
                'tenant_id' => $tenantId,
                'entity_type' => 'company',
                'match_field' => 'tax_code',
                'match_value' => $dupe['tax_code'],
                'record_ids' => json_encode(array_map('intval', $ids)),
                'status' => 'pending',
            ]);

            foreach ($ids as $companyId) {
                Database::insert('duplicate_group_items', [
                    'group_id' => $groupId,
                    'entity_id' => (int) $companyId,
                ]);
            }
            $groupsCreated++;
        }

        // Same name (exact match)
        $nameDupes = Database::fetchAll(
            "SELECT name, GROUP_CONCAT(id ORDER BY id) as ids, COUNT(*) as cnt
             FROM companies
             WHERE tenant_id = ? AND name IS NOT NULL AND name != ''
             GROUP BY name
             HAVING cnt > 1",
            [$tenantId]
        );

        foreach ($nameDupes as $dupe) {
            $ids = explode(',', $dupe['ids']);
            $exists = Database::fetch(
                "SELECT id FROM duplicate_groups
                 WHERE tenant_id = ? AND entity_type = 'company' AND match_field = 'name' AND match_value = ? AND status = 'pending'",
                [$tenantId, $dupe['name']]
            );
            if ($exists) continue;

            $groupId = Database::insert('duplicate_groups', [
                'tenant_id' => $tenantId,
                'entity_type' => 'company',
                'match_field' => 'name',
                'match_value' => $dupe['name'],
                'record_ids' => json_encode(array_map('intval', $ids)),
                'status' => 'pending',
            ]);

            foreach ($ids as $companyId) {
                Database::insert('duplicate_group_items', [
                    'group_id' => $groupId,
                    'entity_id' => (int) $companyId,
                ]);
            }
            $groupsCreated++;
        }

        return $groupsCreated;
    }

    /**
     * Get pending duplicate groups
     */
    public static function getGroups(int $tenantId, string $entityType = ''): array
    {
        $where = "dg.tenant_id = ? AND dg.status = 'pending'";
        $params = [$tenantId];

        if ($entityType) {
            $where .= " AND dg.entity_type = ?";
            $params[] = $entityType;
        }

        $groups = Database::fetchAll(
            "SELECT dg.*, COUNT(dgi.id) as item_count
             FROM duplicate_groups dg
             LEFT JOIN duplicate_group_items dgi ON dg.id = dgi.group_id
             WHERE {$where}
             GROUP BY dg.id
             ORDER BY dg.created_at DESC",
            $params
        );

        // Load items for each group
        foreach ($groups as &$group) {
            if ($group['entity_type'] === 'contact') {
                $group['items'] = Database::fetchAll(
                    "SELECT c.*, dgi.id as item_id
                     FROM duplicate_group_items dgi
                     JOIN contacts c ON dgi.entity_id = c.id
                     WHERE dgi.group_id = ?
                     ORDER BY c.id ASC",
                    [$group['id']]
                );
            } else {
                $group['items'] = Database::fetchAll(
                    "SELECT co.*, dgi.id as item_id
                     FROM duplicate_group_items dgi
                     JOIN companies co ON dgi.entity_id = co.id
                     WHERE dgi.group_id = ?
                     ORDER BY co.id ASC",
                    [$group['id']]
                );
            }
        }
        unset($group);

        return $groups;
    }

    /**
     * Merge duplicates: keep one record, move related records, soft-delete others
     */
    public static function merge(int $groupId, int $keepId): bool
    {
        $group = Database::fetch("SELECT * FROM duplicate_groups WHERE id = ?", [$groupId]);
        if (!$group) return false;

        $items = Database::fetchAll(
            "SELECT entity_id FROM duplicate_group_items WHERE group_id = ?",
            [$groupId]
        );

        $entityIds = array_column($items, 'entity_id');
        $removeIds = array_filter($entityIds, fn($id) => (int) $id !== (int) $keepId);

        if (empty($removeIds)) return false;

        if ($group['entity_type'] === 'contact') {
            // Move related records to the kept contact
            foreach ($removeIds as $oldId) {
                Database::query("UPDATE deals SET contact_id = ? WHERE contact_id = ?", [$keepId, $oldId]);
                Database::query("UPDATE orders SET contact_id = ? WHERE contact_id = ?", [$keepId, $oldId]);
                Database::query("UPDATE activities SET contact_id = ? WHERE contact_id = ?", [$keepId, $oldId]);
                Database::query("UPDATE tickets SET contact_id = ? WHERE contact_id = ?", [$keepId, $oldId]);
                Database::query("UPDATE taggables SET entity_id = ? WHERE entity_type = 'contact' AND entity_id = ?", [$keepId, $oldId]);

                // Soft-delete the duplicate
                Database::query(
                    "UPDATE contacts SET deleted_at = NOW() WHERE id = ?",
                    [$oldId]
                );
            }
        } else {
            // Company merge
            foreach ($removeIds as $oldId) {
                Database::query("UPDATE contacts SET company_id = ? WHERE company_id = ?", [$keepId, $oldId]);
                Database::query("UPDATE deals SET company_id = ? WHERE company_id = ?", [$keepId, $oldId]);
                Database::query("UPDATE orders SET company_id = ? WHERE company_id = ?", [$keepId, $oldId]);
                Database::query("UPDATE activities SET company_id = ? WHERE company_id = ?", [$keepId, $oldId]);
                Database::query("UPDATE taggables SET entity_id = ? WHERE entity_type = 'company' AND entity_id = ?", [$keepId, $oldId]);

                // Delete company
                Database::query("DELETE FROM companies WHERE id = ?", [$oldId]);
            }
        }

        // Mark group as merged
        Database::query(
            "UPDATE duplicate_groups SET status = 'merged', merged_keep_id = ?, merged_at = NOW() WHERE id = ?",
            [$keepId, $groupId]
        );

        // Log the merge
        ActivityLogger::created('duplicates', $groupId, "Gộp trùng lặp #{$groupId} → giữ #{$keepId}");

        return true;
    }

    /**
     * Ignore a duplicate group
     */
    public static function ignore(int $groupId): void
    {
        Database::query(
            "UPDATE duplicate_groups SET status = 'ignored' WHERE id = ?",
            [$groupId]
        );
    }
}
