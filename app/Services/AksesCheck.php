<?php

namespace App\Services;

use Illuminate\Support\Facades\Facade;
use App\Services\AksesCheckService;

class AksesCheck extends Facade{
    //Setting Class yang akan menjadi Facades
    protected static function getFacadeAccessor() { return 'App\Services\AksesCheckService'; }
}