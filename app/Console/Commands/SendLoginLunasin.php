<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RequestToken;

class SendLoginLunasin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loginLunasin:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Request Token Lunasin';

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
        RequestToken::LoginLunasin();
    }
}
