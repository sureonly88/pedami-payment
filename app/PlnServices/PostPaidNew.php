<?php

namespace App\PlnServices;

use Illuminate\Support\Facades\Facade;
use App\PlnServices\PostPaidNewService;

class PostPaidNew extends Facade{
    //Setting Class yang akan menjadi Facades
    protected static function getFacadeAccessor() { return 'App\PlnServices\PostPaidNewService'; }
}