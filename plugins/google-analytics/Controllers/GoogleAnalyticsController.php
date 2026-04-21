<?php

namespace App\Controllers;

use Core\Controller;

class GoogleAnalyticsController extends Controller
{
    public function show()
    {
        return $this->view('plugin:google-analytics.coming-soon');
    }
}
