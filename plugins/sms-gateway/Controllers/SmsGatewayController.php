<?php

namespace App\Controllers;

use Core\Controller;

class SmsGatewayController extends Controller
{
    public function show()
    {
        return $this->view('plugin:sms-gateway.coming-soon');
    }
}
