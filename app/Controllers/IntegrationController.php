<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class IntegrationController extends Controller
{
    public function index()
    {
        return $this->redirect('plugins/marketplace?tab=integrations');
    }
}
