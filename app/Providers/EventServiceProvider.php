<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\PdambjmEvent' => [
            'App\Listeners\PdambjmListener',
        ],

        'App\Events\TopupEvent' => [
            'App\Listeners\TopupListener',
        ],

        'App\Events\TopupVerifikasiEvent' => [
            'App\Listeners\TopupVerifikasiListener',
        ],

        'App\Events\CobaEvent' => [
            'App\Listeners\CobaListener',
        ],

        'App\Events\LoginEvent' => [
            'App\Listeners\LoginListener',
        ],

        'App\Events\LogoutEvent' => [
            'App\Listeners\LogoutListener',
        ],

    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
