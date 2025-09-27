<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailTransaksi;

class SendEmailPdambjm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emailPdambjm:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim email data transaksi PDAMBJM';

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
        EmailTransaksi::kirimEmailTransaksiPdambjm();
    }
}
