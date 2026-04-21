<?php

namespace App\Services;

use Core\Database;

class PersonService
{
    /**
     * Find existing person in tenant by phone (primary) then email, or create new one.
     * Returns the person_id.
     *
     * @param int    $tenantId
     * @param string|null $phone
     * @param string|null $email
     * @param string $fullName  Required for creation
     * @param array  $extra     Optional: gender, date_of_birth, note, avatar
     * @return int person_id
     */
    public static function findOrCreate(int $tenantId, ?string $phone, ?string $email, string $fullName, array $extra = []): int
    {
        $phone = $phone !== null ? trim($phone) : null;
        $email = $email !== null ? trim($email) : null;
        $fullName = trim($fullName);

        // Lookup by phone (VN: strongest unique signal for a person)
        if ($phone !== null && $phone !== '') {
            $row = Database::fetch(
                "SELECT id FROM persons WHERE tenant_id = ? AND phone = ? LIMIT 1",
                [$tenantId, $phone]
            );
            if ($row) return (int)$row['id'];
        }

        // Then by email
        if ($email !== null && $email !== '') {
            $row = Database::fetch(
                "SELECT id FROM persons WHERE tenant_id = ? AND email = ? LIMIT 1",
                [$tenantId, $email]
            );
            if ($row) return (int)$row['id'];
        }

        // Create new
        return (int) Database::insert('persons', [
            'tenant_id' => $tenantId,
            'full_name' => $fullName !== '' ? $fullName : '(Chưa có tên)',
            'phone' => $phone ?: null,
            'email' => $email ?: null,
            'gender' => $extra['gender'] ?? null,
            'date_of_birth' => $extra['date_of_birth'] ?? null,
            'note' => $extra['note'] ?? null,
            'avatar' => $extra['avatar'] ?? null,
        ]);
    }
}
