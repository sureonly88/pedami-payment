<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTabelTransaksiPln extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaksi_pln', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('subcriber_id');
            $table->text('subcriber_name');
            $table->text('subcriber_segment');
            $table->text('switcher_ref');
            $table->text('power_consumtion');
            $table->text('trace_audit_number');

            $table->text('bill_periode');
            $table->decimal('added_tax');
            $table->text('incentive');
            $table->decimal('penalty_fee');
            $table->decimal('admin_charge');
            $table->decimal('total_elec_bill');

            $table->text('username');
            $table->text('loket_name');
            $table->text('loket_code');
            $table->text('jenis_loket');
            $table->text('transaction_code');
            $table->timestamp('transaction_date');
            $table->timestamps();
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
