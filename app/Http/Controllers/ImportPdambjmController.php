<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Response;
use Excel;
use Validator;

class ImportPdambjmController extends Controller
{
    public function __construct()
    {
        $this->middleware('is_admin');
    }

    public function index()
    {
        return view('admin.import_pdambjm');
    }

    /**
     * Upload Excel dan kembalikan header kolom untuk mapping.
     */
    public function upload(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file_excel' => 'required|mimes:xlsx,xls',
            ]);

            if ($validator->fails()) {
                return Response::json([
                    'status'  => 'Error',
                    'message' => $validator->errors()->first(),
                ], 200);
            }

            $file     = $request->file('file_excel');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(storage_path('app/import_pdambjm'), $fileName);

            $filePath = storage_path('app/import_pdambjm/' . $fileName);

            // Baca header dan beberapa baris sample
            $data = Excel::load($filePath, function ($reader) {
                $reader->noHeading();
            })->get();

            if ($data->count() === 0 || !isset($data[0])) {
                return Response::json([
                    'status'  => 'Error',
                    'message' => 'File Excel kosong.',
                ], 200);
            }

            // Sheet pertama
            $sheet = $data;
            if ($data->first() instanceof \Maatwebsite\Excel\Collections\SheetCollection
                || $data->first() instanceof \Illuminate\Support\Collection && isset($data[0][0])) {
                $sheet = $data->first();
            }

            $rows = $sheet->toArray();

            // Header = baris pertama
            $headers = [];
            if (count($rows) > 0) {
                foreach ($rows[0] as $idx => $val) {
                    $headers[] = trim((string) $val);
                }
            }

            // Sample data (5 baris setelah header)
            $sample = [];
            for ($i = 1; $i <= min(5, count($rows) - 1); $i++) {
                $row = [];
                foreach ($rows[$i] as $val) {
                    $row[] = (string) $val;
                }
                $sample[] = $row;
            }

            // Kolom database yang bisa di-mapping
            $dbColumns = $this->getDbColumns();

            return Response::json([
                'status'     => 'Success',
                'file_name'  => $fileName,
                'headers'    => $headers,
                'sample'     => $sample,
                'db_columns' => $dbColumns,
            ], 200);

        } catch (\Exception $e) {
            return Response::json([
                'status'  => 'Error',
                'message' => 'Gagal membaca file: ' . $e->getMessage(),
            ], 200);
        }
    }

    /**
     * Proses import dengan mapping kolom yang dipilih user.
     */
    public function proses(Request $request)
    {
        try {
            $fileName = $request->input('file_name');
            $mapping  = $request->input('mapping'); // array: index_excel => db_column
            $loketCode = $request->input('loket_code');
            $username  = $request->input('username');
            $jenisLoket = $request->input('jenis_loket', 'GATEWAY');

            if (!$fileName || !$mapping) {
                return Response::json([
                    'status'  => 'Error',
                    'message' => 'Data mapping tidak lengkap.',
                ], 200);
            }

            $filePath = storage_path('app/import_pdambjm/' . basename($fileName));

            if (!file_exists($filePath)) {
                return Response::json([
                    'status'  => 'Error',
                    'message' => 'File tidak ditemukan, silakan upload ulang.',
                ], 200);
            }

            // Baca semua data
            $data = Excel::load($filePath, function ($reader) {
                $reader->noHeading();
            })->get();

            $sheet = $data;
            if ($data->first() instanceof \Maatwebsite\Excel\Collections\SheetCollection
                || $data->first() instanceof \Illuminate\Support\Collection && isset($data[0][0])) {
                $sheet = $data->first();
            }

            $rows = $sheet->toArray();

            // Hapus header row
            array_shift($rows);

            $inserted  = 0;
            $skipped   = 0;
            $errors    = [];
            $batchSize = 100;
            $batch     = [];

            foreach ($rows as $rowIdx => $row) {
                $record = [];

                foreach ($mapping as $excelIdx => $dbCol) {
                    if ($dbCol === '' || $dbCol === null) continue;
                    $value = isset($row[$excelIdx]) ? trim((string) $row[$excelIdx]) : '';
                    $record[$dbCol] = $value;
                }

                // Skip baris kosong (tidak ada cust_id)
                if (empty($record['cust_id'])) {
                    continue;
                }

                // Cek duplikat cust_id + blth
                $blth = isset($record['blth']) ? $record['blth'] : '';
                if ($blth !== '') {
                    $exists = DB::table('pdambjm_trans')
                        ->where('cust_id', $record['cust_id'])
                        ->where('blth', $blth)
                        ->exists();

                    if ($exists) {
                        $skipped++;
                        continue;
                    }
                }

                // Generate transaction_code otomatis
                $record['transaction_code'] = strtoupper(date('YmdHis') . '-' . uniqid());

                // Set default fields
                if (!empty($loketCode)) {
                    $record['loket_code'] = $loketCode;
                }
                if (!empty($username)) {
                    $record['username'] = $username;
                }
                if (!isset($record['jenis_loket']) || empty($record['jenis_loket'])) {
                    $record['jenis_loket'] = $jenisLoket;
                }

                $record['created_at'] = date('Y-m-d H:i:s');
                $record['updated_at'] = date('Y-m-d H:i:s');

                // Set transaction_date jika tidak di-mapping
                if (!isset($record['transaction_date']) || empty($record['transaction_date'])) {
                    $record['transaction_date'] = date('Y-m-d H:i:s');
                }

                // Cast numeric fields
                $numericFields = ['harga_air', 'abodemen', 'materai', 'limbah', 'retribusi', 'denda',
                                  'stand_lalu', 'stand_kini', 'sub_total', 'admin', 'total',
                                  'beban_tetap', 'biaya_meter', 'diskon'];
                foreach ($numericFields as $nf) {
                    if (isset($record[$nf])) {
                        $record[$nf] = (float) str_replace([',', '.'], ['', '.'], $record[$nf]);
                    }
                }

                $batch[] = $record;

                if (count($batch) >= $batchSize) {
                    DB::table('pdambjm_trans')->insert($batch);
                    $inserted += count($batch);
                    $batch = [];
                }
            }

            // Insert sisa batch
            if (count($batch) > 0) {
                DB::table('pdambjm_trans')->insert($batch);
                $inserted += count($batch);
            }

            // Hapus file temporary
            @unlink($filePath);

            return Response::json([
                'status'   => 'Success',
                'message'  => "Import selesai. {$inserted} data berhasil diimport, {$skipped} data dilewati (duplikat cust_id + blth).",
                'inserted' => $inserted,
                'skipped'  => $skipped,
            ], 200);

        } catch (\Exception $e) {
            return Response::json([
                'status'  => 'Error',
                'message' => 'Gagal import: ' . $e->getMessage(),
            ], 200);
        }
    }

    /**
     * Daftar kolom database pdambjm_trans yang bisa di-mapping.
     */
    private function getDbColumns()
    {
        return [
            ''              => '-- Abaikan --',
            'cust_id'       => 'No. Pelanggan (cust_id) *',
            'nama'          => 'Nama',
            'alamat'        => 'Alamat',
            'blth'          => 'Bulan/Tahun Rek (blth) *',
            'harga_air'     => 'Harga Air',
            'abodemen'      => 'Abodemen',
            'materai'       => 'Materai',
            'limbah'        => 'Limbah',
            'retribusi'     => 'Retribusi',
            'denda'         => 'Denda',
            'stand_lalu'    => 'Stand Lalu',
            'stand_kini'    => 'Stand Kini',
            'sub_total'     => 'Sub Total',
            'admin'         => 'Admin',
            'total'         => 'Total',
            'idgol'         => 'ID Golongan',
            'loket_name'    => 'Nama Loket',
            'loket_code'    => 'Kode Loket',
            'username'      => 'Username',
            'jenis_loket'   => 'Jenis Loket',
            'beban_tetap'   => 'Beban Tetap',
            'biaya_meter'   => 'Biaya Meter',
            'flag_transaksi' => 'Flag Transaksi',
            'diskon'        => 'Diskon',
        ];
    }
}
