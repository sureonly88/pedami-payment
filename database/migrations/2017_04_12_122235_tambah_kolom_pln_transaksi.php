<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TambahKolomPlnTransaksi extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaksi_pln', function (Blueprint $table) {
            $table->text('outstanding_bill');
            $table->text('bill_status');
            $table->text('prev_meter_read_1');
            $table->text('curr_meter_read_1');
            $table->text('prev_meter_read_2');
            $table->text('curr_meter_read_2');
            $table->text('prev_meter_read_3');
            $table->text('curr_meter_read_3');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
