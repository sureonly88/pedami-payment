<?php

namespace App\Http\Controllers;

use App;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Input;
use Validator;
use Response;
use Illuminate\Support\Facades\Auth;
use App\Events\TopupVerifikasiEvent;
use Illuminate\Support\Facades\Log;

class AdminSaldoController extends Controller
{

	public function __construct()
    {
    	////$this->middleware('auth');
        $this->middleware('is_admin');
    }

	public function index()
	{
		return view('admin.admin_saldo');
	}

	public function simpanData(){
		$manageSaldo = App::make('App\Services\ManageSaldo');

		$Data = Input::all()['Data'];
		$mResponse = $manageSaldo->SimpanVerifikasi($Data);

		//Log::info('mResponse di AdminSaldoController : ' . $mResponse);

		event(new TopupVerifikasiEvent($mResponse,$mResponse['loket']));

		return Response::json($mResponse,200);
	}

	public function getKonfirmasi($Kode){
		$manageSaldo = App::make('App\Services\ManageSaldo');

		$mResponse = $manageSaldo->getPermintaanByKode($Kode);
		return Response::json($mResponse,200);
	}
	
	public function getList($stat){
		$manageSaldo = App::make('App\Services\ManageSaldo');

		$mResponse = $manageSaldo->getListPermintaan('admin',$stat);
		return Response::json($mResponse,200);
	}

	public function getListNotif(){
		$manageSaldo = App::make('App\Services\ManageSaldo');

		$mResponse = $manageSaldo->getListBuatNotif();
		return Response::json($mResponse,200);
	}

}
