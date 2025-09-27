<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePrepaidPln extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaksi_pln_prepaid', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('subscriber_id');
            $table->text('material_number');
            $table->text('subscriber_name');
            $table->text('subscriber_segment');
            $table->text('power_categori');
            $table->text('switcher_ref_number');
            $table->text('pln_ref_number');
            $table->text('token_number');
            $table->text('trace_audit_number');
            $table->text('vending_recieve_number');
            $table->text('max_kwh');
            $table->text('purchase_kwh');
            $table->text('info_text');

            $table->decimal('stump_duty');
            $table->decimal('ligthingtax');
            $table->decimal('cust_payable');
            $table->decimal('admin_charge');
            $table->decimal('addtax');

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
