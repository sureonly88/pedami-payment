<?php namespace App\Http\Controllers;

use App\Models\mDaftarTransaksi;
use App\Models\mLoket;
use App\Models\mRekapTransaksi;
use App\Models\mPdambjmAndroid;
use Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use DB;
use Excel;
use ZanySoft\LaravelPDF\PDF;


class LapAndroidController extends Controller {

    private $pdf;

	public function __construct(Pdf $pdf)
    {
        $this->pdf = $pdf;
        $this->middleware('is_admin');
        //$this->middleware('auth');
    }

    //Menampilkan Halaman Laporan Android
    public function index()
    {
        try{
            if (Auth::check()) {
                $lokets = mLoket::where('jenis','ANDROID')->get();
                //return view('/admin/laporan/harian')->with('lokets', $lokets)->with('user', Helpers::getLoginDetail());
                return view('admin.lap_android')->with('lokets', $lokets);
            }else{
                Session::flash('error', 'You are not loggin yet');
                return Redirect::to('login');
            }
        }catch (Exception $e){
            Session::flash('error', 'There is an error occured');
            return Redirect::to('login');
        }
    }

    //Mengambil Data Laporan
    public function getLaporan($TglAwal, $TglAkhir, $KodeLoket){
    	try{
            if (strpos($KodeLoket,",")) {
                $KodeLoket = explode(",", $KodeLoket);
            }else{
                $KodeLoket = array($KodeLoket);
            }

    		$mLapAndroid = mPdambjmAndroid::whereBetween('TRANSACTION_DATE', array($TglAwal, $TglAkhir))
    			->whereIn('LOKET_CODE',$KodeLoket)
                ->orderBy('LOKET_CODE','ASC')
    			->orderBy('TRANSACTION_DATE','ASC')
    			->get();
    		$numLap = $mLapAndroid->count();
            if($numLap > 0){
            	return Response::json(array(
	                'status' => 'Success',
	                'message' => '-',
	                'data' => $mLapAndroid->toArray(),
	            ),200);
            }else{
            	return Response::json(array(
	                'status' => 'Error',
	                'message' => 'Tidak ada transaksi',
	                'data' => ''
	            ),200);
            }
    	}catch(Exception $e){
    		$error = explode("\r\n",$e->getMessage());
			return Response::json(array(
                'status' => 'Error',
                'message' => $error,
                'data' => ''
            ),200);
    	}
    }

    //Cetak Laporan
    public function cetakLaporan($TglAwal, $TglAkhir, $KodeLoket){
        try{

            if (strpos($KodeLoket,",")) {
                $KodeLoket = explode(",", $KodeLoket);
            }else{
                $KodeLoket = array($KodeLoket);
            }

            $mLoket = mLoket::whereIn('loket_code',$KodeLoket)->get();
            $mLapAndroid = mPdambjmAndroid::whereBetween('TRANSACTION_DATE', array($TglAwal, $TglAkhir))
                ->whereIn('LOKET_CODE',$KodeLoket)
                ->orderBy('LOKET_CODE','ASC')
                ->orderBy('TRANSACTION_DATE','ASC')
                ->get();

            $content = view('admin.laporan.lap_android_pdam')->with('transaksi',$mLapAndroid)->with('loket',$mLoket)->render();

            $pdf = new PDF();
            $pdf->loadHTML($content);
            return $pdf->Stream('document.pdf');

        }catch(Exception $e){
            
        }
    }

    public function exportLaporan($TglAwal, $TglAkhir, $KodeLoket){
        try{

            if (strpos($KodeLoket,",")) {
                $KodeLoket = explode(",", $KodeLoket);
            }else{
                $KodeLoket = array($KodeLoket);
            }

            //$mLoket = mLoket::where('loket_code',$KodeLoket)->first();
            $mLapAndroid = mPdambjmAndroid::whereBetween('TRANSACTION_DATE', array($TglAwal, $TglAkhir))
                ->whereIn('LOKET_CODE',$KodeLoket)
                ->orderBy('LOKET_CODE','ASC')
                ->orderBy('TRANSACTION_DATE','ASC')
                ->get();

            Excel::create('mLapAndroid', function($excel) use($mLapAndroid) {
                $excel->sheet('Sheet 1', function($sheet) use($mLapAndroid) {
                    $sheet->fromArray($mLapAndroid);
                });
            })->export('xls');

        }catch(Exception $e){
            
        }
    }

}