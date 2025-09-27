<?php namespace App\Http\Controllers;

use App;
use App\Events\PdambjmEvent;
use App\APIServices\PdamBjmAPIv2;

class PdambjmRequest extends Controller {

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('admin.admin');
    }

    public function getPelanggan($nopel = "", $loket = ""){

        //$APIPdamBjm = App::make('App\APIServices\PdamBjmAPIv2');

        //$Response = $APIPdamBjm->inqueryPelanggan($nopel,$loket);
        $Response = PdamBjmAPIv2::inqueryPelanggan($nopel,$loket,false,"","","");

        event(new PdambjmEvent($Response,'INQUERY'));

        return $Response;
    }

}
