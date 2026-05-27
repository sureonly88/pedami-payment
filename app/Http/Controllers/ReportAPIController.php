<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\vwPdambjm;
use App\Models\vwTransaksiPln;
use App\Models\vwTransaksiPlnPrepaid;
use Response;
use DB;

class ReportAPIController extends Controller
{
    /**
     * Ambil daftar loket yang boleh diakses oleh token ini.
     * Return null jika semua loket boleh.
     */
    private function getAllowedLokets(Request $request)
    {
        $tokenData = $request->attributes->get('report_token');
        return $tokenData ? $tokenData->getAllowedLoketsArray() : null;
    }

    /**
     * Terapkan filter loket berdasarkan hak akses token.
     */
    private function applyLoketFilter($query, $allowedLokets, $filterLoket = null)
    {
        if ($filterLoket) {
            $requestedLokets = explode(',', $filterLoket);

            if ($allowedLokets) {
                $validLokets = array_intersect($requestedLokets, $allowedLokets);
                if (empty($validLokets)) {
                    return null;
                }
                $query = $query->whereIn('loket_code', $validLokets);
            } else {
                $query = $query->whereIn('loket_code', $requestedLokets);
            }
        } elseif ($allowedLokets) {
            $query = $query->whereIn('loket_code', $allowedLokets);
        }

        return $query;
    }

    /**
     * Validasi parameter tanggal wajib.
     */
    private function validateTanggal(Request $request)
    {
        $tglAwal  = $request->input('tgl_awal');
        $tglAkhir = $request->input('tgl_akhir');

        if (!$tglAwal || !$tglAkhir) {
            return null;
        }
        return [$tglAwal, $tglAkhir];
    }

    /**
     * Bangun query rekap (GROUP BY + SUM) untuk model tertentu.
     */
    private function buildRekapQuery($model, Request $request, $allowedLokets, $tglAwal, $tglAkhir)
    {
        $query = $model::whereBetween('tanggal', [$tglAwal, $tglAkhir]);

        $query = $this->applyLoketFilter($query, $allowedLokets, $request->input('loket_code'));
        if (is_null($query)) return null;

        if ($request->input('jenis_loket')) {
            $query = $query->whereIn('jenis_loket', explode(',', $request->input('jenis_loket')));
        }

        return $query->select(
                'tanggal', 'loket_code', 'loket_name', 'user_',
                'jenis_loket', 'jenis_transaksi',
                DB::raw('SUM(tagihan) as total_tagihan'),
                DB::raw('SUM(admin) as total_admin'),
                DB::raw('SUM(total) as total_total'),
                DB::raw('COUNT(*) as jumlah_transaksi')
            )
            ->groupBy('tanggal', 'loket_code', 'loket_name', 'user_', 'jenis_loket', 'jenis_transaksi')
            ->orderBy('tanggal', 'asc');
    }

    /**
     * Bangun query detail untuk model tertentu.
     */
    private function buildDetailQuery($model, Request $request, $allowedLokets, $tglAwal, $tglAkhir)
    {
        $query = $model::whereBetween('tanggal', [$tglAwal, $tglAkhir]);

        $query = $this->applyLoketFilter($query, $allowedLokets, $request->input('loket_code'));
        if (is_null($query)) return null;

        if ($request->input('idpel')) {
            $query = $query->where('idpel', $request->input('idpel'));
        }

        if ($request->input('user')) {
            $query = $query->where('user_', $request->input('user'));
        }

        return $query->select(
                'id', 'idpel', 'nama', 'periode', 'tanggal', 'jam',
                'tagihan', 'admin', 'total',
                'user_', 'loket_name', 'loket_code',
                'jenis_loket', 'jenis_transaksi'
            )
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam', 'desc');
    }

    /**
     * Format response JSON dengan pagination.
     */
    private function paginatedResponse($query, $perPage, $message)
    {
        $data = $query->paginate($perPage);

        return Response::json([
            'status'        => true,
            'response_code' => '0000',
            'message'       => $message,
            'data'          => $data->items(),
            'pagination'    => [
                'current_page'  => $data->currentPage(),
                'per_page'      => $data->perPage(),
                'total'         => $data->total(),
                'last_page'     => $data->lastPage(),
            ],
        ], 200);
    }

    // =====================================================================
    //  PDAM
    // =====================================================================

    /**
     * GET /report/pdam/rekap
     *
     * Query Parameters:
     *   - tgl_awal     (required) : format Y-m-d
     *   - tgl_akhir    (required) : format Y-m-d
     *   - loket_code   (optional) : kode loket, bisa koma-separated
     *   - jenis_loket  (optional) : jenis loket filter
     *   - page         (optional) : halaman, default 1
     *   - per_page     (optional) : jumlah per halaman, default 50, max 200
     */
    public function rekapPdam(Request $request)
    {
        $tanggal = $this->validateTanggal($request);
        if (!$tanggal) {
            return Response::json([
                'status' => false, 'response_code' => '4001',
                'message' => 'PARAMETER tgl_awal DAN tgl_akhir WAJIB DIISI',
            ], 400);
        }

        $perPage = min((int) $request->input('per_page', 50), 200);
        $allowedLokets = $this->getAllowedLokets($request);

        $query = $this->buildRekapQuery(vwPdambjm::class, $request, $allowedLokets, $tanggal[0], $tanggal[1]);
        if (is_null($query)) {
            return Response::json([
                'status' => false, 'response_code' => '4031',
                'message' => 'LOKET TIDAK DIIZINKAN',
            ], 403);
        }

        return $this->paginatedResponse($query, $perPage, 'REKAP TRANSAKSI PDAM');
    }

    /**
     * GET /report/pdam/detail
     *
     * Query Parameters:
     *   - tgl_awal     (required) : format Y-m-d
     *   - tgl_akhir    (required) : format Y-m-d
     *   - loket_code   (optional) : kode loket, bisa koma-separated
     *   - idpel        (optional) : filter nomor pelanggan
     *   - user         (optional) : filter username operator
     *   - page         (optional) : halaman, default 1
     *   - per_page     (optional) : jumlah per halaman, default 50, max 200
     */
    public function detailPdam(Request $request)
    {
        $tanggal = $this->validateTanggal($request);
        if (!$tanggal) {
            return Response::json([
                'status' => false, 'response_code' => '4001',
                'message' => 'PARAMETER tgl_awal DAN tgl_akhir WAJIB DIISI',
            ], 400);
        }

        $perPage = min((int) $request->input('per_page', 50), 200);
        $allowedLokets = $this->getAllowedLokets($request);

        $query = $this->buildDetailQuery(vwPdambjm::class, $request, $allowedLokets, $tanggal[0], $tanggal[1]);
        if (is_null($query)) {
            return Response::json([
                'status' => false, 'response_code' => '4031',
                'message' => 'LOKET TIDAK DIIZINKAN',
            ], 403);
        }

        return $this->paginatedResponse($query, $perPage, 'DETAIL TRANSAKSI PDAM');
    }

    // =====================================================================
    //  PLN POSTPAID
    // =====================================================================

    /**
     * GET /report/pln/postpaid/rekap
     */
    public function rekapPlnPostpaid(Request $request)
    {
        $tanggal = $this->validateTanggal($request);
        if (!$tanggal) {
            return Response::json([
                'status' => false, 'response_code' => '4001',
                'message' => 'PARAMETER tgl_awal DAN tgl_akhir WAJIB DIISI',
            ], 400);
        }

        $perPage = min((int) $request->input('per_page', 50), 200);
        $allowedLokets = $this->getAllowedLokets($request);

        $query = $this->buildRekapQuery(vwTransaksiPln::class, $request, $allowedLokets, $tanggal[0], $tanggal[1]);
        if (is_null($query)) {
            return Response::json([
                'status' => false, 'response_code' => '4031',
                'message' => 'LOKET TIDAK DIIZINKAN',
            ], 403);
        }

        return $this->paginatedResponse($query, $perPage, 'REKAP TRANSAKSI PLN POSTPAID');
    }

    /**
     * GET /report/pln/postpaid/detail
     */
    public function detailPlnPostpaid(Request $request)
    {
        $tanggal = $this->validateTanggal($request);
        if (!$tanggal) {
            return Response::json([
                'status' => false, 'response_code' => '4001',
                'message' => 'PARAMETER tgl_awal DAN tgl_akhir WAJIB DIISI',
            ], 400);
        }

        $perPage = min((int) $request->input('per_page', 50), 200);
        $allowedLokets = $this->getAllowedLokets($request);

        $query = $this->buildDetailQuery(vwTransaksiPln::class, $request, $allowedLokets, $tanggal[0], $tanggal[1]);
        if (is_null($query)) {
            return Response::json([
                'status' => false, 'response_code' => '4031',
                'message' => 'LOKET TIDAK DIIZINKAN',
            ], 403);
        }

        return $this->paginatedResponse($query, $perPage, 'DETAIL TRANSAKSI PLN POSTPAID');
    }

    // =====================================================================
    //  PLN PREPAID
    // =====================================================================

    /**
     * GET /report/pln/prepaid/rekap
     */
    public function rekapPlnPrepaid(Request $request)
    {
        $tanggal = $this->validateTanggal($request);
        if (!$tanggal) {
            return Response::json([
                'status' => false, 'response_code' => '4001',
                'message' => 'PARAMETER tgl_awal DAN tgl_akhir WAJIB DIISI',
            ], 400);
        }

        $perPage = min((int) $request->input('per_page', 50), 200);
        $allowedLokets = $this->getAllowedLokets($request);

        $query = $this->buildRekapQuery(vwTransaksiPlnPrepaid::class, $request, $allowedLokets, $tanggal[0], $tanggal[1]);
        if (is_null($query)) {
            return Response::json([
                'status' => false, 'response_code' => '4031',
                'message' => 'LOKET TIDAK DIIZINKAN',
            ], 403);
        }

        return $this->paginatedResponse($query, $perPage, 'REKAP TRANSAKSI PLN PREPAID');
    }

    /**
     * GET /report/pln/prepaid/detail
     */
    public function detailPlnPrepaid(Request $request)
    {
        $tanggal = $this->validateTanggal($request);
        if (!$tanggal) {
            return Response::json([
                'status' => false, 'response_code' => '4001',
                'message' => 'PARAMETER tgl_awal DAN tgl_akhir WAJIB DIISI',
            ], 400);
        }

        $perPage = min((int) $request->input('per_page', 50), 200);
        $allowedLokets = $this->getAllowedLokets($request);

        $query = $this->buildDetailQuery(vwTransaksiPlnPrepaid::class, $request, $allowedLokets, $tanggal[0], $tanggal[1]);
        if (is_null($query)) {
            return Response::json([
                'status' => false, 'response_code' => '4031',
                'message' => 'LOKET TIDAK DIIZINKAN',
            ], 403);
        }

        return $this->paginatedResponse($query, $perPage, 'DETAIL TRANSAKSI PLN PREPAID');
    }

    // =====================================================================
    //  EXPORT UNTUK IMPORT KE PERCOBAAN (multi_payment format)
    // =====================================================================

    /**
     * GET /report/export/transaksi
     *
     * Export transaksi dalam format multi_payment untuk diimport ke sistem percobaan.
     *
     * Query Parameters:
     *   - tgl_awal     (required) : format Y-m-d
     *   - tgl_akhir    (required) : format Y-m-d
     *   - jenis        (required) : PDAM | PLN_POSTPAID | PLN_PREPAID
     *   - loket_code   (optional) : kode loket, bisa koma-separated
     *   - page         (optional) : halaman, default 1
     *   - per_page     (optional) : jumlah per halaman, default 100, max 500
     */
    public function exportTransaksi(Request $request)
    {
        $tanggal = $this->validateTanggal($request);
        if (!$tanggal) {
            return Response::json([
                'status' => false, 'response_code' => '4001',
                'message' => 'PARAMETER tgl_awal DAN tgl_akhir WAJIB DIISI',
            ], 400);
        }

        $jenis = strtoupper($request->input('jenis', ''));
        if (!in_array($jenis, ['PDAM', 'PLN_POSTPAID', 'PLN_PREPAID'])) {
            return Response::json([
                'status' => false, 'response_code' => '4002',
                'message' => 'PARAMETER jenis WAJIB: PDAM | PLN_POSTPAID | PLN_PREPAID',
            ], 400);
        }

        [$tglAwal, $tglAkhir] = $tanggal;
        $perPage      = min((int) $request->input('per_page', 100), 500);
        $page         = max(1, (int) $request->input('page', 1));
        $allowedLokets = $this->getAllowedLokets($request);
        $filterLoket  = $request->input('loket_code');

        if ($jenis === 'PDAM') {
            return $this->doExportPdam($tglAwal, $tglAkhir, $allowedLokets, $filterLoket, $page, $perPage);
        } elseif ($jenis === 'PLN_POSTPAID') {
            return $this->doExportPlnPostpaid($tglAwal, $tglAkhir, $allowedLokets, $filterLoket, $page, $perPage);
        } else {
            return $this->doExportPlnPrepaid($tglAwal, $tglAkhir, $allowedLokets, $filterLoket, $page, $perPage);
        }
    }

    // ─── private helpers ─────────────────────────────────────────────────

    private function buildExportLoketFilter($query, $allowedLokets, $filterLoket)
    {
        if ($filterLoket) {
            $requested = array_map('trim', explode(',', $filterLoket));
            if ($allowedLokets) {
                $valid = array_values(array_intersect($requested, $allowedLokets));
                if (empty($valid)) return null;
                return $query->whereIn('loket_code', $valid);
            }
            return $query->whereIn('loket_code', $requested);
        } elseif ($allowedLokets) {
            return $query->whereIn('loket_code', $allowedLokets);
        }
        return $query;
    }

    private function mapProcessingStatus($status)
    {
        if ($status === 'FAILED')  return 'FAILED';
        if ($status === 'PENDING') return 'PENDING';
        return 'SUCCESS'; // NULL or 'SUCCESS' → SUCCESS
    }

    private function runExportQuery($query, $page, $perPage, $message, callable $formatter)
    {
        $countQuery = clone $query;
        $total = $countQuery->count();
        $rows  = $query->offset(($page - 1) * $perPage)->limit($perPage)->get();

        return Response::json([
            'status'        => true,
            'response_code' => '0000',
            'message'       => $message,
            'data'          => $rows->map($formatter)->values(),
            'pagination'    => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total'        => $total,
                'last_page'    => max(1, (int) ceil($total / $perPage)),
            ],
        ], 200);
    }

    private function doExportPdam($tglAwal, $tglAkhir, $allowedLokets, $filterLoket, $page, $perPage)
    {
        $query = DB::table('pdambjm_trans')
            ->whereBetween('transaction_date', [$tglAwal . ' 00:00:00', $tglAkhir . ' 23:59:59'])
            ->where(function ($q) {
                $q->whereNull('flag_transaksi')->orWhere('flag_transaksi', '!=', 'D');
            })
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc');

        $query = $this->buildExportLoketFilter($query, $allowedLokets, $filterLoket);
        if ($query === null) {
            return Response::json(['status' => false, 'response_code' => '4031', 'message' => 'LOKET TIDAK DIIZINKAN'], 403);
        }

        return $this->runExportQuery($query, $page, $perPage, 'EXPORT TRANSAKSI PDAM', function ($row) {
            $status     = $this->mapProcessingStatus($row->processing_status ?? null);
            $multiCode  = 'LEGACY-PDAM-' . $row->transaction_code;
            $idKey      = 'legacy-pdam-' . strtolower($row->transaction_code);
            $amount     = (float) ($row->sub_total ?? 0);
            $adminFee   = (float) ($row->admin    ?? 0);
            $total      = (float) ($row->total    ?? 0);

            return [
                'payment' => [
                    'multi_payment_code' => $multiCode,
                    'idempotency_key'    => $idKey,
                    'status'             => $status,
                    'loket_code'         => (string) ($row->loket_code ?? ''),
                    'loket_name'         => (string) ($row->loket_name ?? ''),
                    'username'           => (string) ($row->username   ?? ''),
                    'total_items'        => 1,
                    'total_amount'       => $amount,
                    'total_admin'        => $adminFee,
                    'grand_total'        => $total,
                    'paid_amount'        => $total,
                    'change_amount'      => 0,
                    'paid_at'            => $row->paid_at  ?? null,
                    'created_at'         => $row->created_at ?? $row->transaction_date,
                    'updated_at'         => $row->updated_at ?? $row->transaction_date,
                ],
                'item' => [
                    'item_code'              => $multiCode,
                    'provider'               => 'PDAM',
                    'service_type'           => 'PDAM',
                    'customer_id'            => (string) ($row->cust_id ?? ''),
                    'customer_name'          => (string) ($row->nama    ?? ''),
                    'period_label'           => (string) ($row->blth    ?? ''),
                    'amount'                 => $amount,
                    'admin_fee'              => $adminFee,
                    'total'                  => $total,
                    'status'                 => $status,
                    'transaction_code'       => (string) ($row->transaction_code       ?? ''),
                    'provider_error_code'    => $row->provider_error_code    ?? null,
                    'provider_error_message' => $row->provider_error_message ?? null,
                    'paid_at'                => $row->paid_at   ?? null,
                    'failed_at'              => $row->failed_at ?? null,
                    'created_at'             => $row->created_at ?? $row->transaction_date,
                    'metadata_json'          => [
                        'source'      => 'pedami-payment',
                        'idgol'       => (string) ($row->idgol       ?? ''),
                        'alamat'      => (string) ($row->alamat      ?? ''),
                        'blth'        => (string) ($row->blth        ?? ''),
                        'harga_air'   => (float)  ($row->harga_air   ?? 0),
                        'abodemen'    => (float)  ($row->abodemen    ?? 0),
                        'materai'     => (float)  ($row->materai     ?? 0),
                        'limbah'      => (float)  ($row->limbah      ?? 0),
                        'retribusi'   => (float)  ($row->retribusi   ?? 0),
                        'denda'       => (float)  ($row->denda       ?? 0),
                        'stand_lalu'  => (float)  ($row->stand_lalu  ?? 0),
                        'stand_kini'  => (float)  ($row->stand_kini  ?? 0),
                        'beban_tetap' => (float)  ($row->beban_tetap ?? 0),
                        'biaya_meter' => (float)  ($row->biaya_meter ?? 0),
                        'diskon'      => isset($row->diskon) ? (float) $row->diskon : null,
                        'jenis_loket' => (string) ($row->jenis_loket ?? ''),
                    ],
                ],
            ];
        });
    }

    private function doExportPlnPostpaid($tglAwal, $tglAkhir, $allowedLokets, $filterLoket, $page, $perPage)
    {
        $query = DB::table('transaksi_pln')
            ->whereBetween('transaction_date', [$tglAwal . ' 00:00:00', $tglAkhir . ' 23:59:59'])
            ->where(function ($q) {
                $q->whereNull('flag_transaksi')->orWhere('flag_transaksi', '!=', 'D');
            })
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc');

        $query = $this->buildExportLoketFilter($query, $allowedLokets, $filterLoket);
        if ($query === null) {
            return Response::json(['status' => false, 'response_code' => '4031', 'message' => 'LOKET TIDAK DIIZINKAN'], 403);
        }

        return $this->runExportQuery($query, $page, $perPage, 'EXPORT TRANSAKSI PLN POSTPAID', function ($row) {
            $multiCode = 'LEGACY-PLN-' . $row->transaction_code;
            $idKey     = 'legacy-pln-' . strtolower($row->transaction_code);
            $amount    = (float) ($row->total_elec_bill ?? 0);
            $adminFee  = (float) ($row->admin_charge   ?? 0);
            $total     = $amount + $adminFee;

            return [
                'payment' => [
                    'multi_payment_code' => $multiCode,
                    'idempotency_key'    => $idKey,
                    'status'             => 'SUCCESS',
                    'loket_code'         => (string) ($row->loket_code ?? ''),
                    'loket_name'         => (string) ($row->loket_name ?? ''),
                    'username'           => (string) ($row->username   ?? ''),
                    'total_items'        => 1,
                    'total_amount'       => $amount,
                    'total_admin'        => $adminFee,
                    'grand_total'        => $total,
                    'paid_amount'        => $total,
                    'change_amount'      => 0,
                    'paid_at'            => $row->transaction_date,
                    'created_at'         => $row->created_at ?? $row->transaction_date,
                    'updated_at'         => $row->updated_at ?? $row->transaction_date,
                ],
                'item' => [
                    'item_code'       => $multiCode,
                    'provider'        => 'LUNASIN',
                    'service_type'    => 'PLN_POSTPAID',
                    'customer_id'     => (string) ($row->subcriber_id   ?? ''),
                    'customer_name'   => (string) ($row->subcriber_name ?? ''),
                    'period_label'    => (string) ($row->bill_periode   ?? ''),
                    'amount'          => $amount,
                    'admin_fee'       => $adminFee,
                    'total'           => $total,
                    'status'          => 'SUCCESS',
                    'transaction_code' => (string) ($row->transaction_code ?? ''),
                    'paid_at'         => $row->transaction_date,
                    'failed_at'       => null,
                    'created_at'      => $row->created_at ?? $row->transaction_date,
                    'metadata_json'   => [
                        'source'             => 'pedami-payment',
                        'subcriber_segment'  => (string) ($row->subcriber_segment  ?? ''),
                        'switcher_ref'       => (string) ($row->switcher_ref       ?? ''),
                        'bill_periode'       => (string) ($row->bill_periode       ?? ''),
                        'added_tax'          => (float)  ($row->added_tax          ?? 0),
                        'incentive'          => (string) ($row->incentive          ?? ''),
                        'penalty_fee'        => (float)  ($row->penalty_fee        ?? 0),
                        'power_consumtion'   => (string) ($row->power_consumtion   ?? ''),
                        'trace_audit_number' => (string) ($row->trace_audit_number ?? ''),
                        'outstanding_bill'   => (string) ($row->outstanding_bill   ?? ''),
                        'bill_status'        => (string) ($row->bill_status        ?? ''),
                        'jenis_loket'        => (string) ($row->jenis_loket        ?? ''),
                        'jenis'              => (string) ($row->jenis              ?? ''),
                    ],
                ],
            ];
        });
    }

    private function doExportPlnPrepaid($tglAwal, $tglAkhir, $allowedLokets, $filterLoket, $page, $perPage)
    {
        $query = DB::table('transaksi_pln_prepaid')
            ->whereBetween('transaction_date', [$tglAwal . ' 00:00:00', $tglAkhir . ' 23:59:59'])
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc');

        $query = $this->buildExportLoketFilter($query, $allowedLokets, $filterLoket);
        if ($query === null) {
            return Response::json(['status' => false, 'response_code' => '4031', 'message' => 'LOKET TIDAK DIIZINKAN'], 403);
        }

        return $this->runExportQuery($query, $page, $perPage, 'EXPORT TRANSAKSI PLN PREPAID', function ($row) {
            $multiCode = 'LEGACY-PLNP-' . $row->transaction_code;
            $idKey     = 'legacy-plnp-' . strtolower($row->transaction_code);
            $amount    = (float) ($row->cust_payable  ?? 0);
            $adminFee  = (float) ($row->admin_charge  ?? 0);
            $total     = $amount + $adminFee;

            return [
                'payment' => [
                    'multi_payment_code' => $multiCode,
                    'idempotency_key'    => $idKey,
                    'status'             => 'SUCCESS',
                    'loket_code'         => (string) ($row->loket_code ?? ''),
                    'loket_name'         => (string) ($row->loket_name ?? ''),
                    'username'           => (string) ($row->username   ?? ''),
                    'total_items'        => 1,
                    'total_amount'       => $amount,
                    'total_admin'        => $adminFee,
                    'grand_total'        => $total,
                    'paid_amount'        => $total,
                    'change_amount'      => 0,
                    'paid_at'            => $row->transaction_date,
                    'created_at'         => $row->created_at ?? $row->transaction_date,
                    'updated_at'         => $row->updated_at ?? $row->transaction_date,
                ],
                'item' => [
                    'item_code'        => $multiCode,
                    'provider'         => 'LUNASIN',
                    'service_type'     => 'PLN_PREPAID',
                    'customer_id'      => (string) ($row->subscriber_id   ?? ''),
                    'customer_name'    => (string) ($row->subscriber_name ?? ''),
                    'period_label'     => null,
                    'amount'           => $amount,
                    'admin_fee'        => $adminFee,
                    'total'            => $total,
                    'status'           => 'SUCCESS',
                    'transaction_code' => (string) ($row->transaction_code ?? ''),
                    'paid_at'          => $row->transaction_date,
                    'failed_at'        => null,
                    'created_at'       => $row->created_at ?? $row->transaction_date,
                    'metadata_json'    => [
                        'source'              => 'pedami-payment',
                        'token_number'        => (string) ($row->token_number        ?? ''),
                        'material_number'     => (string) ($row->material_number     ?? ''),
                        'pln_ref_number'      => (string) ($row->pln_ref_number      ?? ''),
                        'switcher_ref_number' => (string) ($row->switcher_ref_number ?? ''),
                        'purchase_kwh'        => (string) ($row->purchase_kwh        ?? ''),
                        'max_kwh'             => (string) ($row->max_kwh             ?? ''),
                        'trace_audit_number'  => (string) ($row->trace_audit_number  ?? ''),
                        'power_categori'      => (string) ($row->power_categori      ?? ''),
                        'subscriber_segment'  => (string) ($row->subscriber_segment  ?? ''),
                        'stump_duty'          => (float)  ($row->stump_duty          ?? 0),
                        'ligthingtax'         => (float)  ($row->ligthingtax         ?? 0),
                        'addtax'              => (float)  ($row->addtax              ?? 0),
                        'jenis_loket'         => (string) ($row->jenis_loket         ?? ''),
                    ],
                ],
            ];
        });
    }
}
