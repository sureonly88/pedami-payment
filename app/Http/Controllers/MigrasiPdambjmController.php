<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
     * POST /admin/migrasi_pdambjm/jalankan
     *
     * Selalu mengembalikan HTTP 200 dengan JSON berisi status true/false
     * agar AJAX success handler yang menangani semua hasil (termasuk error).
     */
    public function jalankan(Request $request)
    {
        try {
            $tglAwal  = $request->input('tgl_awal');
            $tglAkhir = $request->input('tgl_akhir');

            if (!$tglAwal || !$tglAkhir) {
                return Response::json([
                    'status'  => false,
                    'message' => 'tgl_awal dan tgl_akhir wajib diisi.',
                    'errors'  => ['tgl_awal dan tgl_akhir wajib diisi.'],
                ], 200);
            }

            if (!$this->isValidDate($tglAwal) || !$this->isValidDate($tglAkhir)) {
                return Response::json([
                    'status'  => false,
                    'message' => 'Format tanggal tidak valid. Gunakan Y-m-d.',
                    'errors'  => ['Format tanggal tidak valid. Gunakan Y-m-d.'],
                ], 200);
            }

            if ($tglAwal > $tglAkhir) {
                return Response::json([
                    'status'  => false,
                    'message' => 'tgl_awal tidak boleh lebih besar dari tgl_akhir.',
                    'errors'  => ['tgl_awal tidak boleh lebih besar dari tgl_akhir.'],
                ], 200);
            }

            $perPage        = min(max(1, (int) $request->input('per_page', 1000)), 1000);
            $loketCode      = (string) $request->input('loket_code', '');
            $includeDeleted = (int) $request->input('include_deleted', 0);

            @set_time_limit(600);

            $switcherUrl   = rtrim(env('MIGRASI_SWITCHER_URL', 'https://gateway.paymentpedami.com'), '/');
            $switcherToken = (string) env('MIGRASI_SWITCHER_TOKEN', '');

            if (!$switcherToken) {
                return Response::json([
                    'status'  => false,
                    'message' => 'MIGRASI_SWITCHER_TOKEN belum dikonfigurasi di .env server kasir.',
                    'errors'  => ['MIGRASI_SWITCHER_TOKEN belum dikonfigurasi di .env server kasir.'],
                ], 200);
            }

            $result = self::runMigrasi(
                $switcherUrl,
                $switcherToken,
                $tglAwal,
                $tglAkhir,
                $perPage,
                $loketCode,
                $includeDeleted
            );

            return Response::json($result, 200);

        } catch (\Exception $e) {
            return Response::json([
                'status'        => false,
                'message'       => 'Exception: ' . $e->getMessage(),
                'errors'        => [$e->getMessage()],
                'total_fetched' => 0,
                'total_upsert'  => 0,
                'total_skip'    => 0,
                'last_page'     => 0,
            ], 200);
        }
    }

    /**
     * Jalankan proses migrasi: fetch dari API switcher → upsert ke DB lokal.
     * Method ini juga dipakai oleh Artisan Command.
     */
    public static function runMigrasi(
        $switcherUrl,
        $switcherToken,
        $tglAwal,
        $tglAkhir,
        $perPage        = 1000,
        $loketCode      = '',
        $includeDeleted = 0
    ) {
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
                    'method'        => 'GET',
                    'header'        => "report-token: {$switcherToken}\r\nAccept: application/json\r\n",
                    'timeout'       => 60,
                    'ignore_errors' => true,
                ],
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ],
            ]);

            $raw = @file_get_contents($apiUrl, false, $ctx);

            if ($raw === false) {
                $errors[] = "Halaman {$page}: Gagal menghubungi API switcher ({$apiUrl}).";
                break;
            }

            $body = json_decode($raw, true);

            if (!is_array($body) || empty($body['status'])) {
                $msg = is_array($body) && isset($body['message']) ? $body['message'] : 'Response tidak valid dari API.';
                $errors[] = "Halaman {$page}: {$msg}";
                break;
            }

            $rows     = isset($body['data']) && is_array($body['data']) ? $body['data'] : [];
            $lastPage = isset($body['pagination']['last_page']) ? (int) $body['pagination']['last_page'] : 1;

            $totalFetched += count($rows);

            // Batch upsert per 200 rows
            foreach (array_chunk($rows, 200) as $chunk) {
                try {
                    DB::beginTransaction();

                    foreach ($chunk as $row) {
                        $row = (array) $row;

                        // Hapus id switcher agar tidak conflict dengan auto-increment lokal
                        unset($row['id']);

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
                    try { DB::rollBack(); } catch (\Exception $re) {}
                    $errors[] = "Halaman {$page}: Error batch — " . $e->getMessage();
                    break 2;
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

    private function isValidDate($date)
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
