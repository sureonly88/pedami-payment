<?php

namespace App\Http\Controllers;

use App;
use App\Events\TopupEvent;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Input;
use Validator;
use Response;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendEmailTopup;

class RequestSaldoController extends Controller
{
	public function __construct()
    {
        //$this->middleware('auth');
    }

	public function index()
	{
		$bankTujuan = DB::table('rekening_tujuan')->get();
		return view('admin.request_saldo')->with('bankTujuan',$bankTujuan);
	}

	public function konfirmasi(){
		$manageSaldo = App::make('App\Services\ManageSaldo');

		$Data = Input::all()['Data'];
		$mResponse = $manageSaldo->KonfirmasiPembayaran($Data);
		return Response::json($mResponse,200);
	}

	public function simpanData(){
		$manageSaldo = App::make('App\Services\ManageSaldo');

		$Data = Input::all()['Data'];
		$mResponse = $manageSaldo->SimpdamPermintaan($Data);

		event(new TopupEvent($mResponse,'REQUEST_BARU'));
		//$Job = (new SendEmailTopup());
		//dispatch($Job);

		return Response::json($mResponse,200);
	}

	public function getKonfirmasi($Kode){
		$manageSaldo = App::make('App\Services\ManageSaldo');

		$mResponse = $manageSaldo->getPermintaanByKode($Kode);
		return Response::json($mResponse,200);
	}
	
	public function getList(){
		$manageSaldo = App::make('App\Services\ManageSaldo');

		$mResponse = $manageSaldo->getListPermintaan('user',0);

		return Response::json($mResponse,200);
	}

}
