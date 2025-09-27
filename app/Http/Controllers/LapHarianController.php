<?php namespace App\Http\Controllers;

use App\Models\mDaftarTransaksi;
use App\Models\mLoket;
use App\Models\mRekapTransaksi;
use Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use DB;

class LapHarianController extends Controller {

    public function __construct()
    {

    }

    public function index()
    {
        try{
            if (Auth::check()) {
                $lokets = mLoket::select(['loket_code','nama'])->get();
                //return view('/admin/laporan/harian')->with('lokets', $lokets)->with('user', Helpers::getLoginDetail());
                return view('admin.lap_harian')->with('lokets', $lokets);
            }else{
                Session::flash('error', 'You are not loggin yet');
                return Redirect::to('login');
            }
        }catch (Exception $e){
            Session::flash('error', 'There is an error occured');
            return Redirect::to('login');
        }
    }

    public function getLaporanHarian($TransactionDate = "", $LoketCode = "", $Jenis = ""){
        try{

            if (strpos($LoketCode,",")) {
                $LoketCode = explode(",", $LoketCode);
            }else{
                $LoketCode = array($LoketCode);
            }

            //return var_dump($LoketCode);

			if($TransactionDate == "-"){
				$TglNow = date("Y-m-d");
				$TglMinus=date_create($TglNow);
				date_sub($TglMinus,date_interval_create_from_date_string("5 days"));
				$TglMinus = date_format($TglMinus,"Y-m-d");
				
				//return $TglMinus;
				if($LoketCode[0] == "-"){
					$Rekap = mRekapTransaksi::where("transaction_date",">=",$TglMinus)
					->where("transaction_date","<=",$TglNow)
					->orderBy('transaction_date','asc')
					->orderBy('user','asc')
					->get();
				}else{
					$Rekap = mRekapTransaksi::where("transaction_date",">=",$TglMinus)
					->where("transaction_date","<=",$TglNow)
					->whereIn("loket_code",$LoketCode)
					->orderBy('transaction_date','asc')
					->orderBy('user','asc')
					->get();
				}

			}else{
				if($LoketCode[0] == "-"){
					if($Jenis == "-"){
                        //return "Disini1";
						$Rekap = mRekapTransaksi::where("transaction_date",$TransactionDate)->get();
					}else{
                        //return "Disini2";
						$Rekap = mRekapTransaksi::where("transaction_date",$TransactionDate)->where("jenis",$Jenis)->get();
					}
				}else{
                    if($Jenis == "-"){
                        $Rekap = mRekapTransaksi::where("transaction_date",$TransactionDate)
                        ->whereIn("loket_code",$LoketCode)
                        ->get();
                    }else{
                        $Rekap = mRekapTransaksi::where("transaction_date",$TransactionDate)
                        ->where("jenis",$Jenis)
                        ->whereIn("loket_code",$LoketCode)
                        ->get();
                    }
				}
			}

            $numRekap = $Rekap->count();
            if($numRekap > 0){
                $arrLap = $Rekap->toArray();
                for($i=0;$i<sizeof($arrLap);$i++) {
                    $arrLap[$i]['AKSI'] = "<a href='#' onclick=\"tampilDetail('".$arrLap[$i]['LOKET_CODE']."','".$arrLap[$i]['TRANSACTION_DATE']."','".$arrLap[$i]['USER']."')\">DETAIL</a>";
                }

                return Response::json(array(
                    'status' => 'Success',
                    'message' => '-',
                    'data' => $arrLap,
                ),200);

            }else{
                return Response::json(array(
                    'status' => 'Error',
                    'message' => 'Tidak ada transaksi di tanggal : '.$TransactionDate,
                    'data' => ''
                ),200);
            }
        }catch (Exception $e){

            $error = explode("\r\n",$e->getMessage());
            return Response::json(array(
                'status' => 'Error',
                'message' => $error,
                'data' => ''
            ),200);
        }
    }

    public function getDetailLaporanHarian($Tgl = "", $KodeLoket = "", $Username = ""){
        $Detail = mDaftarTransaksi::where("transaction_date",$Tgl)->where("loket_code",$KodeLoket)->where("username",$Username)->get();
        $numRekap = $Detail->count();
        if($numRekap > 0){
            return Response::json(array(
                'status' => 'Success',
                'message' => '-',
                'data' => $Detail->toArray(),
            ),200);

        }else{
            return Response::json(array(
                'status' => 'Error',
                'message' => 'Tidak ada transaksi di tanggal : '.$Tgl,
                'data' => ''
            ),200);
        }
    }

}
