<?php

namespace App\Services;

use Illuminate\Support\Facades\Facade;
use App\Services\RequestTokenService;

class RequestToken extends Facade{
    //Setting Class yang akan menjadi Facades
    protected static function getFacadeAccessor() { return 'App\Services\RequestTokenService'; }
}