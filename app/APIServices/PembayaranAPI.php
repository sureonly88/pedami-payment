<?php

namespace App\APIServices;

use Illuminate\Support\Facades\Facade;
use App\APIServices\PembayaranAPIServices;

class PembayaranAPI extends Facade{
    //Setting Class yang akan menjadi Facades
    protected static function getFacadeAccessor() { return 'App\APIServices\PembayaranAPIServices'; }
}