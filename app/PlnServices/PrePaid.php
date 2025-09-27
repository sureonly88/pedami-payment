<?php

namespace App\PlnServices;

use Illuminate\Support\Facades\Facade;
use App\PlnServices\PrePaidService;

class PrePaid extends Facade{
    //Setting Class yang akan menjadi Facades
    protected static function getFacadeAccessor() { return 'App\PlnServices\PrePaidService'; }
}