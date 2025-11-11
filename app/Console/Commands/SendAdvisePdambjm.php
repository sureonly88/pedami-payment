<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AdvisePdambjm;

class SendAdvisePdambjm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'advisePDAM:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Advise Otomatis PDAM BJM';

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
        AdvisePdambjm::ProsesAdvisePdambjm();
    }
}
