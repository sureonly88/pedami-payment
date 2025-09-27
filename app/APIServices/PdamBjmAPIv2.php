<?php

namespace App\APIServices;

use Illuminate\Support\Facades\Facade;
use App\APIServices\PdamBjmAPIv2Services;

class PdamBjmAPIv2 extends Facade{
    //Setting Class yang akan menjadi Facades
    protected static function getFacadeAccessor() { return 'App\APIServices\PdamBjmAPIv2Service'; }
}