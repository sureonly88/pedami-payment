<?php

namespace App\Listeners;

use App\Events\TopupVerifikasiEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\EmailTransaksi;

class TopupVerifikasiListener implements ShouldQueue
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
     * @param  TopupVerifikasiEvent  $event
     * @return void
     */
    public function handle(TopupVerifikasiEvent $event)
    {
        $mResponse = $event->Response;
        $mLoket = $event->LoketTopup;

        EmailTransaksi::kirimEmailTopupVerifikasi($mResponse, $mLoket);
    }
}
