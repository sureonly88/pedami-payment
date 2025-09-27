<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableNontaglis extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaksi_pln_nontaglis', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('register_number');
            $table->text('transaction_code');
            $table->text('transaction_name');
            $table->text('registration_date');
            $table->text('expiration_date');
            $table->text('subscriber_id');
            $table->text('subscriber_name');
            $table->text('pln_ref_number');
            $table->text('switcher_ref_number');
            $table->text('service_unit_address');
            $table->text('service_unit_phone');
            $table->decimal('total_transaction',35);
            $table->decimal('pln_bill_value',35);
            $table->decimal('admin_charge',35);
            $table->text('info_text');

            $table->text('username');
            $table->text('loket_name');
            $table->text('loket_code');
            $table->text('jenis_loket');
            $table->string('flag_transaksi',100)->nullable();
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
