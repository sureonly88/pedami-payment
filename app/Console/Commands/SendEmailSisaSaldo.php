<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailTransaksi;

class SendEmailSisaSaldo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emailSisaSaldo:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim email sisa saldo Loket';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        EmailTransaksi::kirimSisaSaldo();
    }
}
