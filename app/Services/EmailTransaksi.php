<?php

namespace App\Services;

use Illuminate\Support\Facades\Facade;
use App\Services\EmailTransaksiServices;

class EmailTransaksi extends Facade{
    //Setting Class yang akan menjadi Facades
    protected static function getFacadeAccessor() { return 'App\Services\EmailTransaksiService'; }
}