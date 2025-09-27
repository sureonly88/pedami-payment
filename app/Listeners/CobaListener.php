<?php

namespace App\Listeners;

use App\Events\CobaEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CobaListener
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
     * @param  CobaEvent  $event
     * @return void
     */
    public function handle(CobaEvent $event)
    {
        //
    }
}
