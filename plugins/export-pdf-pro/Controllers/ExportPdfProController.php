<?php

namespace App\Controllers;

use Core\Controller;

class ExportPdfProController extends Controller
{
    public function show()
    {
        return $this->view('plugin:export-pdf-pro.coming-soon');
    }
}
