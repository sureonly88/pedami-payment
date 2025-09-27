<?php

namespace App\Listeners;

use App\Events\PdambjmEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class PdambjmListener
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
     * @param  PdambjmEvent  $event
     * @return void
     */
    public function handle(PdambjmEvent $event)
    {
        //
    }
}
