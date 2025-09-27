<?php namespace App\Http\Controllers;

use App\Models\mHistoryLog;
use Response;
use Illuminate\Support\Facades\Auth;

class HistoryLogController extends Controller {

    public function __construct()
    {
        ////$this->middleware('auth');
        //$this->middleware('auth');
    }

    public function getHistoryLog($KodeLoket = ""){
        $mData = mHistoryLog::where("loket_code",$KodeLoket)->orderBy('created_at','desc')->get();
        $numRekap = $mData->count();
        if($numRekap > 0){
            return Response::json(array(
                'status' => 'Success',
                'message' => '-',
                'data' => $mData->toArray(),
            ),200);

        }else{
            return Response::json(array(
                'status' => 'Error',
                'message' => 'Tidak ada transaksi di Loket: '.$KodeLoket,
                'data' => ''
            ),200);
        }
    }
}