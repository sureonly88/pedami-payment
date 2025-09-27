<?php

namespace App\APIRekanan;

use Illuminate\Support\Facades\Facade;
use App\APIRekanan\PdambjmRknService;

class PdambjmRkn extends Facade{
    //Setting Class yang akan menjadi Facades
    protected static function getFacadeAccessor() { return 'App\APIRekanan\PdambjmRknService'; }
}