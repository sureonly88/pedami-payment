<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class mLoket extends Model
{
    protected $table = 'lokets';

    public static function updateSaldoLoket($pengurang, $kodeloket)
    {
        DB::table('lokets')->where('loket_code', '=', $kodeloket)->decrement('pulsa', $pengurang);
    }
}
