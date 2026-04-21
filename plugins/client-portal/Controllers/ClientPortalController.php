<?php

namespace App\Controllers;

use Core\Controller;

class ClientPortalController extends Controller
{
    public function show()
    {
        return $this->view('plugin:client-portal.coming-soon');
    }
}
