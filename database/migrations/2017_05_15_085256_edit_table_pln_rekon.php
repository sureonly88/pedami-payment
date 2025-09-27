<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditTablePlnRekon extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaksi_pln', function (Blueprint $table) {
            $table->string('flag_transaksi',100)->nullable();
        });

        Schema::table('transaksi_pln_prepaid', function (Blueprint $table) {
            $table->string('flag_transaksi',100)->nullable();
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
