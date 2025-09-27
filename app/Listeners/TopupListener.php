<?php

namespace App\Listeners;

use App\Jobs\Job;
use App\Events\TopupEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use App\Services\EmailTransaksi;

class TopupListener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  TopupEvent  $event
     * @return void
     */
    public function handle(TopupEvent $event)
    {
        $mJenis = $event->JenisEvent;
        $mResponse = $event->Response;
        EmailTransaksi::kirimEmailTopup($mJenis,$mResponse);
        //Log::info('Request saldo baru : ' . $mResponse);
    }
}
