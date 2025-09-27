<?php

namespace App\Services;

use Illuminate\Support\Facades\Facade;
use App\Services\PrintBaruService;

class PrintBaru extends Facade{
    //Setting Class yang akan menjadi Facades
    protected static function getFacadeAccessor() { return 'App\Services\PrintBaruService'; }
}