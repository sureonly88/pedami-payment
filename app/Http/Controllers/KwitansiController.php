<?php namespace App\Http\Controllers;

use App\Models\mDaftarTransaksi;
use App\Models\mLoket;
use App\Models\mRekapTransaksi;
use Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use DB;

class KwitansiController extends Controller {

    public function __construct()
    {
        //$this->middleware('auth');
    }

    public function Cetak($KodeLoket = "", $TglTransaksi = ""){
        $Detail = mDaftarTransaksi::where("transaction_date",$TglTransaksi)->where("loket_code",$KodeLoket)->get();
        $numRekap = $Detail->count();
        if($numRekap > 0){
			$Loket = array(
				"kodeloket" => $KodeLoket,
				"tgltransaksi" => $TglTransaksi
			);
			return view('admin.laporan.kwitansi')->with('data', $Detail)->with('loket', $Loket);

        }else{

             echo 'Tidak ada transaksi di tanggal : '.$TglTransaksi;

        }
    }

}
