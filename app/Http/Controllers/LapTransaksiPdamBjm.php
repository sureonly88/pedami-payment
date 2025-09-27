<?php namespace App\Http\Controllers;

use ZanySoft\LaravelPDF\PDF;
use App\Models\mDaftarTransaksi;
use App\Models\mRekapBulan;
use App\Models\mRekapTransaksi;
use Excel;

class LapTransaksiPdamBjm extends Controller {

	private $pdf;

    public function __construct(Pdf $pdf)
    {
        //$this->middleware('auth');
        $this->pdf = $pdf;
    }


    public function Laporan($TransactionDate = "", $LoketCode = "", $Username = "")
    {
        if (strpos($LoketCode,",")) {
            $LoketCode = explode(",", $LoketCode);
        }else{
            $LoketCode = array($LoketCode);
        }

    	if($LoketCode[0]=="-"){
            $dtTrans = mDaftarTransaksi::where("transaction_date",$TransactionDate)->get();
        }else{
            $dtTrans = mDaftarTransaksi::where("transaction_date",$TransactionDate)
            ->whereIn("loket_code",$LoketCode)
            ->where("username",$Username)
            ->get();
        }

        $content = view('admin.laporan.transaksi_pdam')->with('transaksi',$dtTrans)->render();

        $pdf = new PDF();
        $pdf->loadHTML($content);
        return $pdf->Stream('document.pdf');

        // $this->pdf->setPaper('letter','landscape');

        // return $this->pdf
        //     ->load($html)
        //     ->show();
    }

    public function exportLaporan($TransactionDate = "", $LoketCode = "", $Username = "")
    {
        if (strpos($LoketCode,",")) {
            $LoketCode = explode(",", $LoketCode);
        }else{
            $LoketCode = array($LoketCode);
        }

        if($LoketCode[0]=="-"){
            $dtTrans = mDaftarTransaksi::where("transaction_date",$TransactionDate)->get();
        }else{
            $dtTrans = mDaftarTransaksi::where("transaction_date",$TransactionDate)
            ->whereIn("loket_code",$LoketCode)
            ->where("username",$Username)
            ->get();
        }

        Excel::create('dtTrans', function($excel) use($dtTrans) {
            $excel->sheet('Sheet 1', function($sheet) use($dtTrans) {
                $sheet->fromArray($dtTrans);
            });
        })->export('xls');
    }


    public function LaporanHarian($TransactionDate = "", $LoketCode = "", $Jenis = "")
    {
        if (strpos($LoketCode,",")) {
            $LoketCode = explode(",", $LoketCode);
        }else{
            $LoketCode = array($LoketCode);
        }

        if($LoketCode[0]=="-"){
			if($Jenis == "-"){
				$Rekap = mRekapTransaksi::where("transaction_date",$TransactionDate)->get();
			}else{
				$Rekap = mRekapTransaksi::where("transaction_date",$TransactionDate)->where("jenis",$Jenis)->get();
			}
            
        }else{
            if($Jenis == "-"){
                $Rekap = mRekapTransaksi::where("transaction_date",$TransactionDate)->whereIn("loket_code",$LoketCode)->get();
            }else{
                $Rekap = mRekapTransaksi::where("transaction_date",$TransactionDate)
                ->where("jenis",$Jenis)
                ->whereIn("loket_code",$LoketCode)->get();
            }
            
        }

        $content = view('admin.laporan.rekap_harian_pdam')->with('transaksi',$Rekap)->render();

        $pdf = new PDF();
        $pdf->loadHTML($content);
        return $pdf->Stream('document.pdf');

        // $this->pdf->setPaper('letter','landscape');

        // return $this->pdf
        //     ->load($html)
        //     ->show();


        //return view('admin.laporan.transaksi_pdam')->with('transaksi',$dtTrans);
    }

    public function Laporan_Bulanan($Tahun = "", $Bulan = "", $KodeLoket = "")
    {
    	if (strpos($KodeLoket,",")) {
            $KodeLoket = explode(",", $KodeLoket);
        }else{
            $KodeLoket = array($KodeLoket);
        }

    	if($KodeLoket[0]=="-"){
            $Rekap = mRekapBulan::where("TRANSACTION_YEAR",$Tahun)->where("TRANSACTION_MONTH",$Bulan)->get();
        }else{
            $Rekap = mRekapBulan::where("TRANSACTION_YEAR",$Tahun)->where("TRANSACTION_MONTH",$Bulan)->whereIn("LOKET_CODE",$KodeLoket)->get();
        }

        $content = view('admin.laporan.transaksi_pdam_bln')->with('transaksi',$Rekap)->render();

        $pdf = new PDF();
        $pdf->loadHTML($content);
        return $pdf->Stream('document.pdf');


        //return view('admin.laporan.transaksi_pdam')->with('transaksi',$dtTrans);
    }

}