<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use DB;
use App\Models\AdvisePDAM;
use App\APIServices\PdamBjmAPIv2;

class AdvisePdambjmService
{
    function ProsesAdvisePdambjm(){

        $dateNow = date('Y-m-d');
        $dateYesterday = date('Y-m-d', strtotime('-1 day'));

        $dtAdvise = AdvisePDAM::whereNull('deleted_at')
            ->where(DB::raw("cast(created_at as date)"), "=", $dateYesterday)
            ->where('produk', '=', 'PDAMBJM')
            ->whereNotNull('username')
            ->get();

        foreach($dtAdvise as $advise){
            //proses advise disini
            //Log::info("Proses Advise PDAM BJM untuk Cust ID : ".$advise->cust_id." , Blth : ".$advise->blth);

            PdamBjmAPIv2::prosesAdvise("-",$advise->produk,$advise->idtrx,1,$advise->username);
        }
    }
}