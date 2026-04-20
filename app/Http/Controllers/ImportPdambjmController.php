<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Response;
use Excel;
use Validator;
use Log;

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
            ini_set('memory_limit', '512M');

            $validator = Validator::make($request->all(), [
                'file_excel' => 'required|file',
            ]);

            if ($validator->fails()) {
                return Response::json([
                    'status'  => 'Error',
                    'message' => $validator->errors()->first(),
                ], 200);
            }

            $ext = strtolower($request->file('file_excel')->getClientOriginalExtension());
            if (!in_array($ext, ['xlsx', 'xls'])) {
                return Response::json([
                    'status'  => 'Error',
                    'message' => 'File harus berformat .xlsx atau .xls.',
                ], 200);
            }

            $file     = $request->file('file_excel');
            $fileName = time() . '_' . $file->getClientOriginalName();

            $destDir = storage_path('app/import_pdambjm');
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }

            $file->move($destDir, $fileName);
            $filePath = storage_path('app/import_pdambjm/' . $fileName);

            // Buka Excel dengan PHPExcel row iterator — hemat memory
            $objReader = \PHPExcel_IOFactory::createReaderForFile($filePath);
            $objReader->setReadDataOnly(true);
            $objExcel  = $objReader->load($filePath);
            $sheet     = $objExcel->getActiveSheet();

            $headers = [];
            $sample  = [];
            $rowNum  = 0;

            foreach ($sheet->getRowIterator() as $row) {
                $rowNum++;
                $cells = [];
                $cellIter = $row->getCellIterator();
                $cellIter->setIterateOnlyExistingCells(false);
                foreach ($cellIter as $cell) {
                    $cells[] = trim((string) $cell->getValue());
                }

                if ($rowNum === 1) {
                    $headers = $cells;
                } else {
                    $sample[] = $cells;
                    if (count($sample) >= 5) break;
                }
            }

            // Bebaskan memory segera
            $objExcel->disconnectWorksheets();
            unset($objExcel, $objReader);

            if (empty($headers)) {
                return Response::json([
                    'status'  => 'Error',
                    'message' => 'File Excel kosong atau tidak bisa dibaca.',
                ], 200);
            }

            return Response::json([
                'status'     => 'Success',
                'file_name'  => $fileName,
                'headers'    => $headers,
                'sample'     => $sample,
                'db_columns' => $this->getDbColumns(),
            ], 200);

        } catch (\Throwable $e) {
            Log::error('ImportPdambjm upload error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
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
            ini_set('memory_limit', '512M');
            ini_set('max_execution_time', '300');

            $fileName   = $request->input('file_name');
            $mapping    = $request->input('mapping');
            $loketCode  = $request->input('loket_code');
            $username   = $request->input('username');
            $jenisLoket = $request->input('jenis_loket', 'ADMIN');

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

            // Buka Excel dengan PHPExcel row iterator — hemat memory
            $objReader = \PHPExcel_IOFactory::createReaderForFile($filePath);
            $objReader->setReadDataOnly(true);
            $objExcel  = $objReader->load($filePath);
            $sheet     = $objExcel->getActiveSheet();

            $numericFields = ['harga_air','abodemen','materai','limbah','retribusi','denda',
                              'stand_lalu','stand_kini','sub_total','admin','total',
                              'beban_tetap','biaya_meter','diskon'];

            $inserted  = 0;
            $skipped   = 0;
            $batchSize = 100;
            $batch     = [];
            $isHeader  = true;

            foreach ($sheet->getRowIterator() as $row) {
                // Lewati baris header
                if ($isHeader) {
                    $isHeader = false;
                    continue;
                }

                // Baca cells row ini
                $cells = [];
                $cellIter = $row->getCellIterator();
                $cellIter->setIterateOnlyExistingCells(false);
                foreach ($cellIter as $cell) {
                    $cells[] = $cell->getValue();
                }

                // Terapkan mapping
                $record = [];
                foreach ($mapping as $excelIdx => $dbCol) {
                    if ($dbCol === '' || $dbCol === null) continue;
                    $record[$dbCol] = isset($cells[$excelIdx]) ? trim((string) $cells[$excelIdx]) : '';
                }

                // Skip baris kosong
                if (empty($record['cust_id'])) continue;

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

                // Generate transaction_code
                $record['transaction_code'] = strtoupper(date('YmdHis') . '-' . uniqid());

                // Default fields
                if (!empty($loketCode) && empty($record['loket_code'])) {
                    $record['loket_code'] = $loketCode;
                }
                if (!empty($username) && empty($record['username'])) {
                    $record['username'] = $username;
                }
                if (empty($record['jenis_loket'])) {
                    $record['jenis_loket'] = $jenisLoket;
                }
                if (empty($record['transaction_date'])) {
                    $record['transaction_date'] = date('Y-m-d H:i:s');
                }
                $record['created_at'] = date('Y-m-d H:i:s');
                $record['updated_at'] = date('Y-m-d H:i:s');

                // Cast numeric
                foreach ($numericFields as $nf) {
                    if (isset($record[$nf]) && $record[$nf] !== '') {
                        $record[$nf] = (float) str_replace(',', '', $record[$nf]);
                    }
                }

                $batch[] = $record;

                if (count($batch) >= $batchSize) {
                    DB::table('pdambjm_trans')->insert($batch);
                    $inserted += count($batch);
                    $batch = [];
                }
            }

            // Bebaskan memory
            $objExcel->disconnectWorksheets();
            unset($objExcel, $objReader);

            // Insert sisa batch
            if (count($batch) > 0) {
                DB::table('pdambjm_trans')->insert($batch);
                $inserted += count($batch);
            }

            @unlink($filePath);

            return Response::json([
                'status'   => 'Success',
                'message'  => "Import selesai. {$inserted} data berhasil diimport, {$skipped} data dilewati (duplikat cust_id + blth).",
                'inserted' => $inserted,
                'skipped'  => $skipped,
            ], 200);

        } catch (\Throwable $e) {
            Log::error('ImportPdambjm proses error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
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
