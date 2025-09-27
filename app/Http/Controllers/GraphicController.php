<?php namespace App\Http\Controllers;

use App\Models\mPdambjmGrvRekap;
use Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class GraphicController extends Controller {

	public function __construct()
    {
        //$this->middleware('auth');
    }

    public function getRekapBulan(){

    	$arrayDt = array();
    	$arrayLabel = array();
    	$Tahun = date('Y');

        $Rekap = mPdambjmGrvRekap::where("TRANSACTION_YEAR",$Tahun)
        ->orderBy('TRANSACTION_MONTH','ASC')
        ->get(['TRANSACTION_MONTH','REKENING']);

        $numRekap = $Rekap->count();
        if($numRekap > 0){
        	foreach ($Rekap as $Rek) {
			    array_push($arrayDt, $Rek->REKENING);
			    array_push($arrayLabel, $Rek->TRANSACTION_MONTH);
			}

            return Response::json(array(
                'status' => 'Success',
                'message' => '-',
                'data' => $arrayDt,
                'label' => $arrayLabel
            ),200);

        }else{
            return Response::json(array(
                'status' => 'Error',
                'message' => 'Tidak ada transaksi di Bulan : ',
                'data' => '',
                'label' => ''
            ),200);
        }
    }
}