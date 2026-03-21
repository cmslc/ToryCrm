<?php

namespace App\Controllers;

use Core\Controller;

class LegalController extends Controller
{
    public function terms()
    {
        return $this->view('legal.terms');
    }

    public function privacy()
    {
        return $this->view('legal.privacy');
    }
}
