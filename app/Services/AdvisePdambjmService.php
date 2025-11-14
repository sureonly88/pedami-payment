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

        //Kalau pakai queue
        // foreach($dtAdvise as $advise){
        //     // Kirim ke queue untuk diproses secara asynchronous
        //     ProcessPdamAdvise::dispatch($advise)
        //         ->onQueue('advise-pdam')
        //         ->delay(now()->addSeconds(rand(1, 5))); // Random delay untuk distribute load
        // }
        //php artisan queue:work --queue=advise-pdam --tries=3 --timeout=120
        
        //Log::info("Total ".count($dtAdvise)." advise PDAM BJM telah di-queue");

        // Batasi jumlah data per batch untuk menghindari timeout
        $batchSize = 20; // Sesuaikan dengan kebutuhan
        $chunks = $dtAdvise->chunk($batchSize);

        foreach($chunks as $batch){
            foreach($batch as $advise){
                try {
                    // Set timeout untuk setiap request
                    // Pastikan PdamBjmAPIv2::prosesAdvise memiliki timeout configuration
                    
                    Log::info("Memproses Advise PDAM BJM - ID Trx: ".$advise->idtrx);
                    
                    PdamBjmAPIv2::prosesAdvise("-",$advise->produk,$advise->idtrx,1,$advise->username);
                    
                    // Tambahkan delay kecil untuk menghindari rate limiting dari API eksternal
                    usleep(100000); // 0.1 detik delay
                    
                } catch (\Exception $e) {
                    // Log error tapi lanjutkan ke data berikutnya
                    Log::error("Error proses advise ID Trx ".$advise->idtrx.": ".$e->getMessage());
                    continue;
                }
            }
            
            // Optional: delay antar batch
            sleep(1);
        }
    }
}