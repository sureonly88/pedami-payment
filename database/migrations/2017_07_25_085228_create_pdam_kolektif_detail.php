<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePdamKolektifDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pdam_kolektif_detail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_pelanggan',100);
            $table->string('nama_pelanggan',100);
            $table->bigInteger('id_kolektif');
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
