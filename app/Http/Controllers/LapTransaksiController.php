<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\mLoket;
use App\Models\vwDetailTransaksi;
use App\Models\vwRekapTransaksi;
use App\Models\vwBulanTransaksi;
use Response;
use Excel;
use ZanySoft\LaravelPDF\PDF;
use Illuminate\Support\Facades\Auth;
use DB;
use App\Models\vwPdambjm;
use App\Models\vwPdambjm010620;
use App\Models\vwTransaksiPln;
use App\Models\vwTransaksiPlnPrepaid;
use App\Models\vwTransaksiPlnNontag;

class LapTransaksiController extends Controller
{
	private $pdf;

	public function __construct(Pdf $pdf)
    {
    	$this->pdf = $pdf;
        //$this->middleware('is_admin');
    }

    public function index()
	{
		$lokets = mLoket::select(['loket_code','nama','jenis'])->get();
		return view('admin.lap_transaksi')->with('lokets', $lokets);
	}

	public function index_bulan()
	{
		$lokets = mLoket::select(['loket_code','nama','jenis'])->get();
		return view('admin.lap_transaksi_bulan')->with('lokets', $lokets);
	}

	public function getRekap($tgl_awal,$tgl_akhir,$KodeLoket,$jenisTransaksi,$jenisLoket, $tampil){

		$Pdambjm010620 = vwPdambjm010620::whereBetween('tanggal',[$tgl_awal, $tgl_akhir]);
		$Pdambjm = vwPdambjm::whereBetween('tanggal',[$tgl_awal, $tgl_akhir]);
		$Pln = vwTransaksiPln::whereBetween('tanggal',[$tgl_awal, $tgl_akhir]);
		$PlnPrepaid = vwTransaksiPlnPrepaid::whereBetween('tanggal',[$tgl_awal, $tgl_akhir]);
		$PlnNon = vwTransaksiPlnNontag::whereBetween('tanggal',[$tgl_awal, $tgl_akhir]);

		if ($KodeLoket != "-") {
            $KodeLoket = explode(",", $KodeLoket);
			$Pdambjm = $Pdambjm->whereIn('loket_code',$KodeLoket);
			$Pdambjm010620 = $Pdambjm010620->whereIn('loket_code',$KodeLoket);
            $Pln = $Pln->whereIn('loket_code',$KodeLoket);
            $PlnPrepaid = $PlnPrepaid->whereIn('loket_code',$KodeLoket);
            $PlnNon = $PlnNon->whereIn('loket_code',$KodeLoket);
        }

        if ($jenisTransaksi != "-") {
            $jenisTransaksi = explode(",", $jenisTransaksi);
			$Pdambjm = $Pdambjm->whereIn('jenis_transaksi',$jenisTransaksi);
			$Pdambjm010620 = $Pdambjm010620->whereIn('jenis_transaksi',$jenisTransaksi);
            $Pln = $Pln->whereIn('jenis_transaksi',$jenisTransaksi);
            $PlnPrepaid = $PlnPrepaid->whereIn('jenis_transaksi',$jenisTransaksi);
            $PlnNon = $PlnNon->whereIn('jenis_transaksi',$jenisTransaksi);
        }

        if ($jenisLoket != "-") {
            $jenisLoket = explode(",", $jenisLoket);
			$Pdambjm = $Pdambjm->whereIn('jenis_loket',$jenisLoket);
			$Pdambjm010620 = $Pdambjm010620->whereIn('jenis_loket',$jenisLoket);
            $Pln = $Pln->whereIn('jenis_loket',$jenisLoket);
            $PlnPrepaid = $PlnPrepaid->whereIn('jenis_loket',$jenisLoket);
            $PlnNon = $PlnNon->whereIn('jenis_loket',$jenisLoket);
        }

        $Pdambjm = $Pdambjm->select('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi',
				DB::raw('sum(tagihan) as tagihan'),
				DB::raw('sum(admin) as admin'),
				DB::raw('sum(total) as total'),
				DB::raw('count(*) as jumlah'))
				->groupBy('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi');
		$Pdambjm010620 = $Pdambjm010620->select('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi',
				DB::raw('sum(tagihan) as tagihan'),
				DB::raw('sum(admin) as admin'),
				DB::raw('sum(total) as total'),
				DB::raw('count(*) as jumlah'))
				->groupBy('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi');
        $Pln = $Pln->select('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi',
				DB::raw('sum(tagihan) as tagihan'),
				DB::raw('sum(admin) as admin'),
				DB::raw('sum(total) as total'),
				DB::raw('count(*) as jumlah'))
				->groupBy('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi');
        $PlnPrepaid = $PlnPrepaid->select('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi',
				DB::raw('sum(tagihan) as tagihan'),
				DB::raw('sum(admin) as admin'),
				DB::raw('sum(total) as total'),
				DB::raw('count(*) as jumlah'))
				->groupBy('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi');
        $PlnNon = $PlnNon->select('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi',
				DB::raw('sum(tagihan) as tagihan'),
				DB::raw('sum(admin) as admin'),
				DB::raw('sum(total) as total'),
				DB::raw('count(*) as jumlah'))
				->groupBy('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi');

		$Rekap = $Pdambjm->union($Pdambjm010620)->union($Pln)->union($PlnPrepaid)->union($PlnNon)->orderBy('tanggal','asc')->get();

		switch ($tampil) {
			case 'pdf':
				$content = view('admin.laporan.lap_transaksi_rekap_harian')
					->with('transaksi',$Rekap)
					->with('tgl_awal',$tgl_awal)
					->with('tgl_akhir',$tgl_akhir)
					->with('kode_loket',$KodeLoket)
					->with('jenis_transaksi',$jenisTransaksi)
					->with('jenis_loket',$jenisLoket)
					->render();

		        $pdf = new PDF();
		        $pdf->AddPage('L');
		        $pdf->loadHTML($content);
		        return $pdf->Stream('document.pdf');
				break;
			case 'excel':
				$fileName = "Rekap".$KodeLoket;
				Excel::create($fileName, function($excel) use($Rekap) {
		            $excel->sheet('Transaksi', function($sheet) use($Rekap) {
		                $sheet->fromArray($Rekap);
		            });
		        })->export('xls');
				break;
			default:
				return Response::json(array(
		            'status' => true,
		            'message' => '-',
		            'data' => $Rekap,
		        ),200);
		}
	}

	public function getDetail($tanggal,$LoketCode,$User,$jenisTransaksi, $tampil){

		// $Detail = vwDetailTransaksi::where('tanggal','=',$tanggal)
		// 	->where('loket_code','=',$LoketCode)
		// 	->where('jenis_transaksi','=',$jenisTransaksi)
		// 	->where('user_','=',$User)
		// 	->get();

		$Pdambjm010620 = vwPdambjm010620::where('tanggal','=',$tanggal)
			->where('loket_code','=',$LoketCode)
			->where('jenis_transaksi','=',$jenisTransaksi)
			->where('user_','=',$User);

		$Pdambjm = vwPdambjm::where('tanggal','=',$tanggal)
			->where('loket_code','=',$LoketCode)
			->where('jenis_transaksi','=',$jenisTransaksi)
			->where('user_','=',$User);

		$Pln = vwTransaksiPln::where('tanggal','=',$tanggal)
			->where('loket_code','=',$LoketCode)
			->where('jenis_transaksi','=',$jenisTransaksi)
			->where('user_','=',$User);
		$PlnPrepaid = vwTransaksiPlnPrepaid::where('tanggal','=',$tanggal)
			->where('loket_code','=',$LoketCode)
			->where('jenis_transaksi','=',$jenisTransaksi)
			->where('user_','=',$User);
		$PlnNon = vwTransaksiPlnNontag::where('tanggal','=',$tanggal)
			->where('loket_code','=',$LoketCode)
			->where('jenis_transaksi','=',$jenisTransaksi)
			->where('user_','=',$User);

		$Detail = $Pdambjm->union($Pdambjm010620)->union($Pln)->union($PlnPrepaid)->union($PlnNon)->get();

		switch ($tampil) {
			case 'pdf':
				$content = view('admin.laporan.lap_transaksi_detail_harian')
					->with('transaksi',$Detail)
					->with('tanggal',$tanggal)
					->with('kode_loket',$LoketCode)
					->with('jenis_transaksi',$jenisTransaksi)
					->with('user_',$User)
					->render();

		        $pdf = new PDF();
		        $pdf->loadHTML($content);
		        return $pdf->Stream('document.pdf');
				break;
			case 'excel':
				$fileName = "Detail".$LoketCode.$tanggal;
				Excel::create($fileName, function($excel) use($Detail) {
		            $excel->sheet('Transaksi', function($sheet) use($Detail) {
		                $sheet->fromArray($Detail);
		            });
		        })->export('xls');
				break;
			default:
				return Response::json(array(
		            'status' => true,
		            'message' => '-',
		            'data' => $Detail,
		        ),200);
		}	
	}

	public function getBulanan($Tahun, $Bulan, $KodeLoket, $jenisTransaksi, $jenisLoket, $tampil)
    {
    	//$Rekap = vwBulanTransaksi::where("tahun",$Tahun)->where("bulan",$Bulan);

		$Pdambjm010620 = vwPdambjm010620::whereYear('tanggal',$Tahun)->whereMonth('tanggal',$Bulan);
		$Pdambjm = vwPdambjm::whereYear('tanggal',$Tahun)->whereMonth('tanggal',$Bulan);

		$Pln = vwTransaksiPln::whereYear('tanggal',$Tahun)->whereMonth('tanggal',$Bulan);
		$PlnPrepaid = vwTransaksiPlnPrepaid::whereYear('tanggal',$Tahun)->whereMonth('tanggal',$Bulan);
		$PlnNon = vwTransaksiPlnNontag::whereYear('tanggal',$Tahun)->whereMonth('tanggal',$Bulan);

    	if ($KodeLoket != "-") {
            $KodeLoket = explode(",", $KodeLoket);
			$Pdambjm = $Pdambjm->whereIn('loket_code',$KodeLoket);
			$Pdambjm010620 = $Pdambjm010620->whereIn('loket_code',$KodeLoket);
            $Pln = $Pln->whereIn('loket_code',$KodeLoket);
            $PlnPrepaid = $PlnPrepaid->whereIn('loket_code',$KodeLoket);
            $PlnNon = $PlnNon->whereIn('loket_code',$KodeLoket);
        }

        if ($jenisTransaksi != "-") {
            $jenisTransaksi = explode(",", $jenisTransaksi);
			$Pdambjm = $Pdambjm->whereIn('jenis_transaksi',$jenisTransaksi);
			$Pdambjm010620 = $Pdambjm010620->whereIn('jenis_transaksi',$jenisTransaksi);
            $Pln = $Pln->whereIn('jenis_transaksi',$jenisTransaksi);
            $PlnPrepaid = $PlnPrepaid->whereIn('jenis_transaksi',$jenisTransaksi);
            $PlnNon = $PlnNon->whereIn('jenis_transaksi',$jenisTransaksi);
        }

        if ($jenisLoket != "-") {
            $jenisLoket = explode(",", $jenisLoket);
			$Pdambjm = $Pdambjm->whereIn('jenis_loket',$jenisLoket);
			$Pdambjm010620 = $Pdambjm010620->whereIn('jenis_loket',$jenisLoket);
            $Pln = $Pln->whereIn('jenis_loket',$jenisLoket);
            $PlnPrepaid = $PlnPrepaid->whereIn('jenis_loket',$jenisLoket);
            $PlnNon = $PlnNon->whereIn('jenis_loket',$jenisLoket);
        }

        $Pdambjm = $Pdambjm->select(DB::raw('year(tanggal) as tahun'),DB::raw('month(tanggal) as bulan'),
        	'loket_code','loket_name','jenis_loket','jenis_transaksi',
				DB::raw('sum(tagihan) as tagihan'),
				DB::raw('sum(admin) as admin'),
				DB::raw('sum(total) as total'),
				DB::raw('count(*) as jumlah'))
				->groupBy(DB::raw('year(tanggal)'),DB::raw('month(tanggal)'),'loket_code','loket_name','jenis_loket','jenis_transaksi');

		$Pdambjm010620 = $Pdambjm010620->select(DB::raw('year(tanggal) as tahun'),DB::raw('month(tanggal) as bulan'),
			'loket_code','loket_name','jenis_loket','jenis_transaksi',
			DB::raw('sum(tagihan) as tagihan'),
			DB::raw('sum(admin) as admin'),
			DB::raw('sum(total) as total'),
			DB::raw('count(*) as jumlah'))
			->groupBy(DB::raw('year(tanggal)'),DB::raw('month(tanggal)'),'loket_code','loket_name','jenis_loket','jenis_transaksi');

		$Pln = $Pln->select(DB::raw('year(tanggal) as tahun'),DB::raw('month(tanggal) as bulan'),
        	'loket_code','loket_name','jenis_loket','jenis_transaksi',
				DB::raw('sum(tagihan) as tagihan'),
				DB::raw('sum(admin) as admin'),
				DB::raw('sum(total) as total'),
				DB::raw('count(*) as jumlah'))
				->groupBy(DB::raw('year(tanggal)'),DB::raw('month(tanggal)'),'loket_code','loket_name','jenis_loket','jenis_transaksi');

		$PlnPrepaid = $PlnPrepaid->select(DB::raw('year(tanggal) as tahun'),DB::raw('month(tanggal) as bulan'),
        	'loket_code','loket_name','jenis_loket','jenis_transaksi',
				DB::raw('sum(tagihan) as tagihan'),
				DB::raw('sum(admin) as admin'),
				DB::raw('sum(total) as total'),
				DB::raw('count(*) as jumlah'))
				->groupBy(DB::raw('year(tanggal)'),DB::raw('month(tanggal)'),'loket_code','loket_name','jenis_loket','jenis_transaksi');

		$PlnNon = $PlnNon->select(DB::raw('year(tanggal) as tahun'),DB::raw('month(tanggal) as bulan'),
        	'loket_code','loket_name','jenis_loket','jenis_transaksi',
				DB::raw('sum(tagihan) as tagihan'),
				DB::raw('sum(admin) as admin'),
				DB::raw('sum(total) as total'),
				DB::raw('count(*) as jumlah'))
				->groupBy(DB::raw('year(tanggal)'),DB::raw('month(tanggal)'),'loket_code','loket_name','jenis_loket','jenis_transaksi');


    	$Rekap = $Pdambjm->union($Pdambjm010620)->union($Pln)->union($PlnPrepaid)->union($PlnNon)
    		->orderBy('jenis_loket','asc')
    		->orderBy('loket_code','asc')
    		->get();

    	switch ($tampil) {
			case 'pdf':
				$content = view('admin.laporan.lap_transaksi_rekap_bulan')
					->with('transaksi',$Rekap)
					->with('tahun',$Tahun)
					->with('bulan',$Bulan)
					->with('kode_loket',$KodeLoket)
					->with('jenis_transaksi',$jenisTransaksi)
					->with('jenis_loket',$jenisLoket)
					->render();

		        $pdf = new PDF();
		        $pdf->AddPage('L');
		        $pdf->loadHTML($content);
		        return $pdf->Stream('document.pdf');
				break;

			case 'excel':
				$fileName = "Bulan".$KodeLoket.$Tahun.$Bulan;
				Excel::create($fileName, function($excel) use($Rekap) {
		            $excel->sheet('Transaksi', function($sheet) use($Rekap) {
		                $sheet->fromArray($Rekap);
		            });
		        })->export('xls');
				break;

			default: 
				return Response::json(array(
		            'status' => true,
		            'message' => '-',
		            'data' => $Rekap
		        ),200);
		}   
    }

    public function getSetoranHarian($tglawal,$tglakhir){
    	$User = Auth::user()->username;

    	$Pdambjm = vwPdambjm::whereBetween('tanggal',[$tglawal, $tglakhir])->where('user_','=',$User);
		$Pln = vwTransaksiPln::whereBetween('tanggal',[$tglawal, $tglakhir])->where('user_','=',$User);
		$PlnPrepaid = vwTransaksiPlnPrepaid::whereBetween('tanggal',[$tglawal, $tglakhir])->where('user_','=',$User);
		$PlnNon = vwTransaksiPlnNontag::whereBetween('tanggal',[$tglawal, $tglakhir])->where('user_','=',$User);

		$Pdambjm = $Pdambjm->select('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi',
				DB::raw('sum(tagihan) as tagihan'),
				DB::raw('sum(admin) as admin'),
				DB::raw('sum(total) as total'),
				DB::raw('count(*) as jumlah'))
				->groupBy('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi');
        $Pln = $Pln->select('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi',
				DB::raw('sum(tagihan) as tagihan'),
				DB::raw('sum(admin) as admin'),
				DB::raw('sum(total) as total'),
				DB::raw('count(*) as jumlah'))
				->groupBy('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi');
        $PlnPrepaid = $PlnPrepaid->select('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi',
				DB::raw('sum(tagihan) as tagihan'),
				DB::raw('sum(admin) as admin'),
				DB::raw('sum(total) as total'),
				DB::raw('count(*) as jumlah'))
				->groupBy('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi');
        $PlnNon = $PlnNon->select('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi',
				DB::raw('sum(tagihan) as tagihan'),
				DB::raw('sum(admin) as admin'),
				DB::raw('sum(total) as total'),
				DB::raw('count(*) as jumlah'))
				->groupBy('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi');

		$Transaksi = $Pdambjm->union($Pln)->union($PlnPrepaid)->union($PlnNon)->get();

   //  	$Transaksi = vwRekapTransaksi::whereBetween('tanggal',[$tglawal, $tglakhir])
			// ->where('user_','=',$User)
			// ->get();

		$Transaksi = $Transaksi->toArray();

		//dd($Transaksi);

		return view('cetakan.setoran')->with('transaksi', $Transaksi);
    }
}
