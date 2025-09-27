<?php

namespace App\Services;

use Illuminate\Support\Facades\Facade;
use App\Services\PrintBaruV1Service;

class PrintBaruV1 extends Facade{
    //Setting Class yang akan menjadi Facades
    protected static function getFacadeAccessor() { return 'App\Services\PrintBaruV1Service'; }
}