<?php

namespace App\Middleware;

class AuthMiddleware
{
    public function handle(): bool
    {
        if (!isset($_SESSION['user'])) {
            header("Location: /login");
            exit;
            return false;
        }
        return true;
    }
}
