<?php

namespace Modules\ApiPeruDev\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\ApiPeruDev\Data\ServiceData;

class ServiceController extends Controller
{
    public function service($type, $number)
    {
        return (new ServiceData)->service2($type, $number);
    }

    public function exchange($date)
    {
        return (new ServiceData)->exchange($date);
    }
}
