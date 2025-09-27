<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\SendEmailPdambjm::class,
        \App\Console\Commands\SendEmailSisaSaldo::class,
        \App\Console\Commands\SendLoginLunasin::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();

        $schedule->command('emailPdambjm:send')->dailyAt('01:00');
        $schedule->command('emailSisaSaldo:send')->dailyAt('02:00');
        //$schedule->command('emailPdambjm:send')->everyMinute();
        //$schedule->command('loginLunasin:send')->cron('0 */3 * * *');
        //0 */3 * * *
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
