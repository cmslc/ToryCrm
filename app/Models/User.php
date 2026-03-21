<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class User extends Model
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        return $this->whereFirst('email', $email);
    }

    public function getActiveUsers(): array
    {
        return Database::fetchAll("SELECT * FROM users WHERE is_active = 1 ORDER BY name");
    }

    public function updateLastLogin(int $id): void
    {
        $this->update($id, ['last_login' => date('Y-m-d H:i:s')]);
    }
}
