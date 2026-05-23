<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportApiTokensTable extends Migration
{
    /**
     * Tabel untuk menyimpan API Token khusus laporan.
     * Terpisah dari api_token di tabel users agar manajemen akses lebih fleksibel.
     */
    public function up()
    {
        Schema::create('report_api_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nama', 100)->comment('Nama pemilik/client token');
            $table->string('token', 80)->unique()->comment('Token untuk autentikasi API laporan');
            $table->text('allowed_lokets')->nullable()->comment('Kode loket yang boleh diakses (JSON array), null = semua');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('report_api_tokens');
    }
}
