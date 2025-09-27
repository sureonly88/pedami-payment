<?php namespace App\Http\Controllers;

use App\Models\mDaftarTransaksi;
use App\Models\mLoket;
use App\Models\mRekapBulan;
use App\Models\mRekapTransaksi;
use Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class LapBulananController extends Controller {

    public function __construct()
    {
        ////$this->middleware('auth');
        //$this->middleware('auth');
    }

    public function index()
    {
        if (Auth::check()) {
            $lokets = mLoket::all();
            return view('admin.lap_bulanan')->with('lokets', $lokets);
        }else{
            Session::flash('error', 'You are not loggin yet');
            return Redirect::to('login');
        }
    }

    public function getLaporanBulanan($Tahun = "", $Bulan = "", $KodeLoket = ""){

        if (strpos($KodeLoket,",")) {
            $KodeLoket = explode(",", $KodeLoket);
        }else{
            $KodeLoket = array($KodeLoket);
        }

        if($KodeLoket[0] =="-"){
            $Rekap = mRekapBulan::where("TRANSACTION_YEAR",$Tahun)->where("TRANSACTION_MONTH",$Bulan)->get();
        }else{
            $Rekap = mRekapBulan::where("TRANSACTION_YEAR",$Tahun)->where("TRANSACTION_MONTH",$Bulan)->whereIn("LOKET_CODE",$KodeLoket)->get();
        }

        $numRekap = $Rekap->count();
        if($numRekap > 0){
            return Response::json(array(
                'status' => 'Success',
                'message' => '-',
                'data' => $Rekap->toArray(),
            ),200);

        }else{
            return Response::json(array(
                'status' => 'Error',
                'message' => 'Tidak ada transaksi di Bulan : '.$Bulan . ' ' . $Tahun,
                'data' => ''
            ),200);
        }

    }


}
