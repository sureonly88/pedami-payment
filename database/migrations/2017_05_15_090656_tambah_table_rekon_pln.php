<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TambahTableRekonPln extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('rekon_pln_postpaid', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('datetime_local',35);
            $table->string('bank_code',35);
            $table->string('partner_id',35);
            $table->string('merchant_code',35);
            $table->string('switcher_ref_number',35);
            $table->string('subscriber_id',35);
            $table->string('bill_period',35);
            $table->decimal('transaction_amount',35);
            $table->decimal('total_elect_bill',35);
            $table->decimal('elect_bill',35);
            $table->string('incentive',35);
            $table->decimal('value_added_tax',35);
            $table->decimal('penalty_fee',35);
            $table->decimal('admin_charge',35);
            $table->string('terminal_id',35);
            $table->timestamps();
        });

        Schema::create('rekon_pln_prepaid', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('datetime_local',35);
            $table->string('partner_id',35);
            $table->string('merchant_code',35);
            $table->string('pln_ref_number',35);
            $table->string('switcher_ref_number',35);
            $table->string('material_number',35);
            $table->decimal('transaction_amount',35);
            $table->decimal('admin_charge',35);
            $table->decimal('stump_duty',35);
            $table->decimal('value_added_tax',35);
            $table->decimal('public_lighting_tax',35);
            $table->decimal('customer_payable',35);
            $table->decimal('power_purchase',35);
            $table->decimal('purchase_kwh_unit',35);
            $table->string('token_number',35);
            $table->string('bank_code',35);
            $table->string('terminal_id',35);
            $table->string('subscriber_id',35);
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
