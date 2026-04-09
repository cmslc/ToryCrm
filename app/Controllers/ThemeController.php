<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class ThemeController extends Controller
{
    public function toggle()
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }

        $currentTheme = $_SESSION['user']['theme'] ?? 'light';
        $newTheme = $currentTheme === 'dark' ? 'light' : 'dark';

        Database::query(
            "UPDATE users SET theme = ? WHERE id = ?",
            [$newTheme, $this->userId()]
        );

        $_SESSION['user']['theme'] = $newTheme;

        return $this->json(['theme' => $newTheme]);
    }
}
