<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\APIServices\PdamBjmAPIv2;
use Illuminate\Support\Facades\Log;

class ProcessPdamAdvise implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $advise;
    
    // Jumlah percobaan ulang jika gagal
    public $tries = 3;
    
    // Timeout untuk job ini
    public $timeout = 120;

    public function __construct($advise)
    {
        $this->advise = $advise;
    }

    public function handle()
    {
        try {
            Log::info("Processing Advise PDAM BJM - ID Trx: ".$this->advise->idtrx);
            
            PdamBjmAPIv2::prosesAdvise(
                "-",
                $this->advise->produk,
                $this->advise->idtrx,
                1,
                $this->advise->username
            );
            
            Log::info("Success processing Advise - ID Trx: ".$this->advise->idtrx);
            
        } catch (\Exception $e) {
            Log::error("Error processing Advise ID Trx ".$this->advise->idtrx.": ".$e->getMessage());
            
            // Jika ingin retry, throw exception
            // throw $e;
        }
    }
    
    // Method yang dipanggil ketika job gagal setelah semua retry
    public function failed(\Throwable $exception)
    {
        Log::error("Job failed permanently for Advise ID Trx ".$this->advise->idtrx.": ".$exception->getMessage());
    }
}