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
}
