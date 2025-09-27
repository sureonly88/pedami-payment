<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;
use App\Models\mLoket;

class AdminController extends Controller
{
	public function __construct()
    {
        ////$this->middleware('auth');
    }

    public function index()
    {
    	//$TotalPenerimaan = DB::table("pdambjm_trans")->sum("total");
        //$TotalAdmin = DB::table("pdambjm_trans")->sum("admin");
        $Berita = DB::table("berita")
            ->select('judul','isi','created_at')
            ->orderBy('created_at','desc')
            ->limit(5)
            ->get();

        // $saldo = 0;
        // $loket_id = Auth::user()->loket_id;

        // $kodeLoket = "";
        // $dLoket = mLoket::where("id",$loket_id)->first();
        // if($dLoket != null){
        //     $kodeLoket = $dLoket->loket_code;
        //     $saldo =  $dLoket->pulsa;
        // }

        // $limit = 0;
        // $shareLoket = DB::table("web_mntr_shareLoket")->where('id_lokets',$loket_id)->select('limit')->first();
        // if($shareLoket){
        //     $limit =  $shareLoket->limit;
        // }

        // $tglHariIni = date('Y-m-d');

        // $TotalHariIni = DB::table('vw_rekap_transaksi')
        //     ->where('loket_code',$kodeLoket)
        //     ->where('tanggal',$tglHariIni)
        //     ->sum('total');

        // $transaksi = $saldo-$limit;

        // //dd($saldo);

        // $wajibSetor = $transaksi-$TotalHariIni;

        // //dd($TotalHariIni);

        // if($wajibSetor == 0) $wajibSetor = $transaksi;

        return view('admin.home')
            //->with('wajib_setor', $wajibSetor)
        	//->with('total', $TotalPenerimaan)
            //->with('total_admin', $TotalAdmin)
            ->with('list_berita',$Berita);
    }
}
