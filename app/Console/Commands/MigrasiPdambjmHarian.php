<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Http\Controllers\MigrasiPdambjmController;

class MigrasiPdambjmHarian extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Contoh penggunaan:
     *   php artisan migrasi:pdambjm                          → migrasi data kemarin
     *   php artisan migrasi:pdambjm --date=2024-01-15        → migrasi 1 hari tertentu
     *   php artisan migrasi:pdambjm --tgl_awal=2024-01-01 --tgl_akhir=2024-01-31  → range
     *   php artisan migrasi:pdambjm --date=2024-01-15 --loket_code=L001,L002
     *   php artisan migrasi:pdambjm --tgl_awal=2024-01-01 --tgl_akhir=2024-12-31 --per_page=1000
     *
     * @var string
     */
    protected $signature = 'migrasi:pdambjm
                            {--date=           : Tanggal spesifik (Y-m-d). Default: kemarin. Diabaikan jika tgl_awal/tgl_akhir diisi.}
                            {--tgl_awal=       : Tanggal awal range (Y-m-d). Wajib diisi bersama --tgl_akhir.}
                            {--tgl_akhir=      : Tanggal akhir range (Y-m-d). Wajib diisi bersama --tgl_awal.}
                            {--loket_code=     : Filter loket code, koma-separated. Kosong = semua loket.}
                            {--per_page=1000   : Jumlah data per halaman API (maks 1000).}
                            {--include_deleted : Sertakan data dengan flag_transaksi=D.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrasi data pdambjm_trans dari server switcher ke server kasir secara otomatis.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $switcherUrl   = rtrim(env('MIGRASI_SWITCHER_URL', ''), '/');
        $switcherToken = env('MIGRASI_SWITCHER_TOKEN', '');

        if (!$switcherUrl || !$switcherToken) {
            $this->error('MIGRASI_SWITCHER_URL dan MIGRASI_SWITCHER_TOKEN wajib diset di file .env');
            return 1;
        }

        // Tentukan range tanggal
        $tglAwal  = $this->option('tgl_awal');
        $tglAkhir = $this->option('tgl_akhir');

        if ($tglAwal && $tglAkhir) {
            if (!$this->isValidDate($tglAwal) || !$this->isValidDate($tglAkhir)) {
                $this->error('Format tanggal tidak valid. Gunakan Y-m-d.');
                return 1;
            }
            if ($tglAwal > $tglAkhir) {
                $this->error('tgl_awal tidak boleh lebih besar dari tgl_akhir.');
                return 1;
            }
        } elseif ($this->option('date')) {
            $date = $this->option('date');
            if (!$this->isValidDate($date)) {
                $this->error('Format --date tidak valid. Gunakan Y-m-d.');
                return 1;
            }
            $tglAwal  = $date;
            $tglAkhir = $date;
        } else {
            // Default: kemarin
            $kemarin  = Carbon::yesterday()->format('Y-m-d');
            $tglAwal  = $kemarin;
            $tglAkhir = $kemarin;
        }

        $perPage        = min(max(1, (int) $this->option('per_page')), 1000);
        $loketCode      = (string) ($this->option('loket_code') ?? '');
        $includeDeleted = $this->option('include_deleted') ? 1 : 0;

        $this->info("=================================================");
        $this->info(" MIGRASI DATA PDAMBJM_TRANS");
        $this->info("=================================================");
        $this->info(" Sumber : {$switcherUrl}");
        $this->info(" Tanggal: {$tglAwal} s/d {$tglAkhir}");
        $this->info(" Loket  : " . ($loketCode ?: 'semua'));
        $this->info(" PerPage: {$perPage}");
        $this->info(" Mulai  : " . now()->format('Y-m-d H:i:s'));
        $this->info("-------------------------------------------------");

        $startTime = microtime(true);

        $result = MigrasiPdambjmController::runMigrasi(
            $switcherUrl,
            $switcherToken,
            $tglAwal,
            $tglAkhir,
            $perPage,
            $loketCode,
            $includeDeleted
        );

        $elapsed = round(microtime(true) - $startTime, 2);

        $this->info(" Selesai: " . now()->format('Y-m-d H:i:s') . " ({$elapsed} detik)");
        $this->info("-------------------------------------------------");
        $this->info(" Total diambil  : {$result['total_fetched']}");
        $this->info(" Total upsert   : {$result['total_upsert']}");
        $this->info(" Dilewati       : {$result['total_skip']}");
        $this->info(" Jumlah halaman : {$result['last_page']}");

        if (!empty($result['errors'])) {
            $this->warn("\n[!] Error yang terjadi:");
            foreach ($result['errors'] as $err) {
                $this->error("  - {$err}");
            }
        }

        if ($result['status']) {
            $this->info("\n[OK] Migrasi berhasil: {$result['message']}");
            $this->info("=================================================\n");
            return 0;
        } else {
            $this->error("\n[GAGAL] {$result['message']}");
            $this->info("=================================================\n");
            return 1;
        }
    }

    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
