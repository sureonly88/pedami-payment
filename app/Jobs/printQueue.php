<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Controllers\Helpers;

class printQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $username;
    protected $rek;
    protected $jenisKertas;
    protected $layanan;

    public function __construct($username,$rek,$jenisKertas,$layanan)
    {
        $this->username = $username;
        $this->rek = $rek;
        $this->jenisKertas = $jenisKertas;
        $this->layanan = $layanan;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::setHttpPostQueue(env('QUEUE_SERVER','')."/printing/insert_queue",
            $this->username,
            $this->rek,
            $this->jenisKertas,
            $this->layanan);
    }
}
