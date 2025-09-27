<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use App\Mail\excelPdambjm;
use App\Mail\TopupEmail;
use App\Mail\TopupVerifikasiEmail;
use App\Mail\SisaSaldoEmail;
use Illuminate\Support\Facades\Mail;
use App\Models\mDaftarTransaksi;
use App\Models\mPdambjmTrans;
use App\Models\SetupEmail;
use Excel;
use Illuminate\Support\Facades\Log;
use DB;

class EmailTransaksiService
{
	function kirimEmailTransaksiPdambjm(){

        $date = date_create(date('Y-m-d'));
        date_sub($date, date_interval_create_from_date_string('1 days'));

		$dateNow = date_format($date, 'Y-m-d');
        $fileName = 'pdambjm_'.$dateNow;

        $pesanEmail = "";
        $isTransaksi = true;

        $dtTrans = mPdambjmTrans::where(DB::raw("cast(transaction_date as date)"),"=",$dateNow)
            ->where(DB::raw("ifnull(flag_transaksi,'')"),"<>","cancel")
            ->where('jenis_loket','<>','NON_ADMIN')
            ->select('cust_id','nama','blth','sub_total','admin','total','loket_code','transaction_date')
            ->get();

        $dtTransNon = mPdambjmTrans::where(DB::raw("cast(transaction_date as date)"),"=",$dateNow)
            ->where(DB::raw("ifnull(flag_transaksi,'')"),"<>","cancel")
            ->where('jenis_loket','=','NON_ADMIN')
            ->select('cust_id','nama','blth','sub_total','admin','total','loket_code','transaction_date')
            ->get();

        if($dtTrans->count() > 0 || $dtTransNon->count() > 0){
            $exists = Storage::exists('exports/'.$fileName.'.xls');
            if(!$exists){

                Excel::create($fileName, function($excel) use($dtTrans,$dtTransNon) {
                    $excel->sheet('ADMIN', function($sheet) use($dtTrans) {
                        $sheet->fromArray($dtTrans);
                    });

                     $excel->sheet('NON_ADMIN', function($sheet) use($dtTransNon) {
                        $sheet->fromArray($dtTransNon);
                    });
                })->store('xls');
                // ->store('xls', storage_path('excel/exports'));
            }

            $pesanEmail = "Data Transaksi PDAM Bandarmasih Tanggal ". $dateNow;
            $isTransaksi = true;
        }else{
            $pesanEmail = "Tidak ada transaksi PDAM Bandarmasih di Tanggal ". $dateNow;
            $isTransaksi = false;
        }

        $mEmail = SetupEmail::where('jenis','PDAMBJM')->where('is_aktif',1)->select(['email'])->get();

        //Log::info("Email Baru : " . $mEmail);

        //Mail::to('sureonly88@gmail.com')->send(new excelPdambjm($pesanEmail, $isTransaksi));
        Mail::to($mEmail)->send(new excelPdambjm($pesanEmail, $isTransaksi));
        //Contoh masuk ke antrian / queue
        //Mail::to('sureonly88@gmail.com')->queue(new excelPdambjm($pesanEmail, $isTransaksi));
	}

    function kirimEmailTopup($mJenis, $mResponse){

        $mEmail = SetupEmail::where('jenis','TOPUP')->where('is_aktif',1)->select(['email'])->get();

        //Log::info("Email Baru : " . $mEmail);
        //Log::info('Log Response : ' . $mResponse['message']);

        Mail::to($mEmail)->send(new TopupEmail($mJenis, $mResponse));
    }

    function kirimEmailTopupVerifikasi($mResponse,$mLoket){
        $KodeLoket = $mResponse['loket'];

        //Log::info("Email Verifikasi : " . $KodeLoket);
        
        $mEmail = DB::table('users')
            ->leftJoin('lokets','users.loket_id','=','lokets.id')
            ->where('lokets.loket_code','=',$KodeLoket)
            ->select(['users.email'])
            ->get();

        //Log::info("Email Verifikasi : " . $mEmail);

        Mail::to($mEmail)->send(new TopupVerifikasiEmail($KodeLoket, $mResponse));
    }

    function kirimSisaSaldo(){

        //Kirim Email Sisa Saldo KIPO
        $sisaSaldo = DB::table('lokets')->where('loket_code','RKIPO')->select('nama','pulsa')->first();
        if($sisaSaldo){

            $mEmail = SetupEmail::where('jenis','DJISALDO')->where('is_aktif',1)->select(['email'])->get();

            $dtNow = date("d-m-Y H:i:s");
            $isiPesan = "Sisa Saldo Mitra " . $sisaSaldo->nama . " per Tanggal ".$dtNow." Rp. " . number_format($sisaSaldo->pulsa);
            Mail::to($mEmail)->send(new SisaSaldoEmail($isiPesan));
        }
        //End Kirim Email Sisa Saldo KIPO
        
    }
}