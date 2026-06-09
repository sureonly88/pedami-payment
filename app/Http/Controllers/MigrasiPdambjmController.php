<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Response;
use Carbon\Carbon;

class MigrasiPdambjmController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('manageTrx');
    }

    /**
     * Tampilkan halaman migrasi manual.
     */
    public function index()
    {
        return view('admin.migrasi_pdambjm', [
            'switcher_url' => rtrim(env('MIGRASI_SWITCHER_URL', 'https://gateway.paymentpedami.com'), '/'),
        ]);
    }

    /**
     * Jalankan migrasi via AJAX.
     * Mengambil data dari API switcher per halaman dan meng-upsert ke pdambjm_trans lokal.
     *
     * POST /admin/migrasi_pdambjm/jalankan
     */
    public function jalankan(Request $request)
    {
        $tglAwal  = $request->input('tgl_awal');
        $tglAkhir = $request->input('tgl_akhir');

        if (!$tglAwal || !$tglAkhir) {
            return Response::json([
                'status'  => false,
                'message' => 'tgl_awal dan tgl_akhir wajib diisi.',
            ], 422);
        }

        // Validasi format tanggal
        if (!$this->isValidDate($tglAwal) || !$this->isValidDate($tglAkhir)) {
            return Response::json([
                'status'  => false,
                'message' => 'Format tanggal tidak valid. Gunakan Y-m-d.',
            ], 422);
        }

        if ($tglAwal > $tglAkhir) {
            return Response::json([
                'status'  => false,
                'message' => 'tgl_awal tidak boleh lebih besar dari tgl_akhir.',
            ], 422);
        }

        $perPage        = min(max(1, (int) $request->input('per_page', 1000)), 1000);
        $loketCode      = $request->input('loket_code', '');
        $includeDeleted = (int) $request->input('include_deleted', 0);

        set_time_limit(600); // 10 menit maksimal untuk request besar

        $switcher_url   = rtrim(env('MIGRASI_SWITCHER_URL', 'https://gateway.paymentpedami.com'), '/');
        $switcher_token = env('MIGRASI_SWITCHER_TOKEN', '');

        $result = $this->runMigrasi(
            $switcher_url,
            $switcher_token,
            $tglAwal,
            $tglAkhir,
            $perPage,
            $loketCode,
            $includeDeleted
        );

        return Response::json($result, $result['status'] ? 200 : 500);
    }

    /**
     * Jalankan proses migrasi: fetch dari API switcher → upsert ke DB lokal.
     * Method ini juga dipakai oleh Artisan Command.
     */
    public static function runMigrasi(
        string $switcherUrl,
        string $switcherToken,
        string $tglAwal,
        string $tglAkhir,
        int    $perPage        = 1000,
        string $loketCode      = '',
        int    $includeDeleted = 0
    ): array {
        $page         = 1;
        $lastPage     = 1;
        $totalFetched = 0;
        $totalUpsert  = 0;
        $totalSkip    = 0;
        $errors       = [];

        do {
            $params = [
                'tgl_awal'        => $tglAwal,
                'tgl_akhir'       => $tglAkhir,
                'page'            => $page,
                'per_page'        => $perPage,
                'include_deleted' => $includeDeleted,
            ];

            if ($loketCode) {
                $params['loket_code'] = $loketCode;
            }

            $queryString = http_build_query($params);
            $apiUrl      = $switcherUrl . '/report/migrasi/pdambjm?' . $queryString;

            $ctx = stream_context_create([
                'http' => [
                    'method'  => 'GET',
                    'header'  => "report-token: {$switcherToken}\r\nAccept: application/json\r\n",
                    'timeout' => 60,
                    'ignore_errors' => true,
                ],
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ],
            ]);

            $raw = @file_get_contents($apiUrl, false, $ctx);

            if ($raw === false) {
                $errors[] = "Halaman {$page}: Gagal menghubungi API switcher.";
                break;
            }

            $body = json_decode($raw, true);

            if (!isset($body['status']) || $body['status'] !== true) {
                $msg = isset($body['message']) ? $body['message'] : 'Response tidak valid dari API.';
                $errors[] = "Halaman {$page}: {$msg}";
                break;
            }

            $rows     = $body['data']     ?? [];
            $lastPage = $body['pagination']['last_page'] ?? 1;

            $totalFetched += count($rows);

            // Batch upsert per 200 rows
            foreach (array_chunk($rows, 200) as $chunk) {
                DB::beginTransaction();
                try {
                    foreach ($chunk as $row) {
                        $row = (array) $row;

                        // Hapus id agar tidak conflict dengan auto-increment lokal
                        $switcherOriginalId = $row['id'] ?? null;
                        unset($row['id']);

                        // Simpan reference id switcher jika kolom ada
                        if ($switcherOriginalId !== null && self::columnExists('switcher_id')) {
                            $row['switcher_id'] = $switcherOriginalId;
                        }

                        if (empty($row['transaction_code'])) {
                            $totalSkip++;
                            continue;
                        }

                        DB::table('pdambjm_trans')->updateOrInsert(
                            ['transaction_code' => $row['transaction_code']],
                            $row
                        );

                        $totalUpsert++;
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $errors[] = "Halaman {$page}: Error batch — " . $e->getMessage();
                    break 2; // keluar dari semua loop
                }
            }

            $page++;

        } while ($page <= $lastPage);

        return [
            'status'        => empty($errors),
            'message'       => empty($errors)
                ? "Migrasi selesai. {$totalUpsert} data berhasil diproses."
                : 'Migrasi selesai dengan error.',
            'tgl_awal'      => $tglAwal,
            'tgl_akhir'     => $tglAkhir,
            'total_fetched' => $totalFetched,
            'total_upsert'  => $totalUpsert,
            'total_skip'    => $totalSkip,
            'last_page'     => $lastPage,
            'errors'        => $errors,
        ];
    }

    // ─── helpers ─────────────────────────────────────────────────────────

    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Cek apakah kolom tertentu ada di tabel pdambjm_trans.
     * Dijalankan sekali dan di-cache di memori per request.
     */
    private static $columnCache = null;

    private static function columnExists(string $column): bool
    {
        if (self::$columnCache === null) {
            self::$columnCache = DB::getSchemaBuilder()->getColumnListing('pdambjm_trans');
        }
        return in_array($column, self::$columnCache, true);
    }
}
