<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RekonPLNPostpaid;
use App\Models\RekonPLNPrepaid;
use App\Models\RekonPLNPostpaidKanan;
use App\Models\RekonPLNPrepaidKanan;
use App\Models\RekonTransPLNPostpaid;
use App\Models\RekonTransPLNPrepaid;
use App\Models\PlnTransaksi;
use App\Models\PlnPrepaidTransaksi;
use App\Models\FileRekonPLN;
use DB;
use Response;
use Storage;

class RekonPlnController extends Controller
{
	private $PCID = "4510517";

	public function __construct()
    {
    	$this->middleware('is_admin');
    }

    public function index()
	{
		return view('admin.rekon_pln');
	}

	public function testFtp(){
		$contents = Storage::disk('ftp')->get('/POSTPAID/201705/4510517-50501-99-20170512.fcn');

		$splitIsi = explode(PHP_EOL, $contents);
		array_splice($splitIsi, 0, 1);
		array_splice($splitIsi, -1, 1);
		array_splice($splitIsi, -1, 1);

		dd($splitIsi);
	}

	public function cekFileRekon($jenis,$bulan,$tahun){
		$blth = $tahun.$bulan;

		$listFile = Storage::disk('ftp')->files($jenis."/".$blth."/");

		$listRekon = array();

		foreach ($listFile as $FileFtp) {
			$Rekon['file_ftp_path'] = $FileFtp;

			$NamaFile = explode("/", $FileFtp);
			$NamaFile = $NamaFile[2];

			$Rekon['file_ftp'] = $NamaFile;

			$tgl_rekon = explode("-",  $NamaFile);
			$tgl_rekon = $tgl_rekon[3];
			$tgl_rekon = explode(".", $tgl_rekon);
			$tgl_rekon = $tgl_rekon[0];

			$tahun = substr($tgl_rekon, 0, 4);
			$bulan = substr($tgl_rekon, 4, 2);
			$tgl = substr($tgl_rekon, 6, 2);

			$tgl_rekon = $tgl."-".$bulan."-".$tahun;

			$cekFile = FileRekonPLN::where('nama_file',$NamaFile)->first();
			if($cekFile){
				$Rekon['jenis'] = $jenis;
				$Rekon['file_rekon'] = $cekFile->nama_file;
				$Rekon['tgl_rekon_file'] = $cekFile->tgl_rekon_file;
				$Rekon['tgl_transaksi'] = $cekFile->tgl_transaksi;
				$Rekon['created_at'] = $cekFile->created_at;
			}else{
				$Rekon['jenis'] = $jenis;
				$Rekon['file_rekon'] = "-";
				$Rekon['tgl_rekon_file'] = $tgl_rekon;
				$Rekon['tgl_transaksi'] = "-";
				$Rekon['created_at'] = "-";
			}

			array_push($listRekon, $Rekon);
		}

		return Response::json(array(
            'status' => 'Success',
            'message' => '-',
            'data' => $listRekon
        ),200);

	}

	public function rekonUploadPost(Request $request)
    {
    	try{
    		$jenis = $request->input('uploadJenis');

    		$nmFile = $request->file('rekonfile')->getClientOriginalName();
	        $pathFile = $request->file('rekonfile')->storeAs('rekonpln', $nmFile);

	        $isiFile = Storage::get($pathFile);

	    	switch ($jenis) {
				case 'POSTPAID':
					$this->prosesRekonPostpaid($isiFile);
					break;

				case 'PREPAID':
					$this->prosesRekonPrepaid($isiFile);
					break;
			}

    		return back()
            	->with('success','UPLOAD DATA REKON BERHASIL...!')
            	->with('alert', 'alert-success');
    	}catch (\Exception $e){
			$error = explode("\r\n",$e->getMessage());

            return back()
            	->with('success','UPLOAD DATA REKON GAGAL...!')
            	->with('alert', 'alert-danger');
            
		}
 
    }

	private function prosesRekonPostpaid($isiFile){
		$splitIsi = explode(PHP_EOL, $isiFile);
		//$splitIsi = explode("\r\n", $isiFile);

		array_splice($splitIsi, 0, 1);
		array_splice($splitIsi, -1, 1);
		array_splice($splitIsi, -1, 1);

		foreach ($splitIsi as $isi) {
			//$data = str_replace("\t", "", $isi);
			$splitData = explode("|", $isi);
			$tanggal = $splitData[0];
			$bank_code = $splitData[1];
			$partner_id = $splitData[2];
			$merchant_code = $splitData[3];
			$switcher_ref_number = $splitData[4];
			$subscriber_id = $splitData[5];
			$bill_period = $this->getDateFormat($splitData[6]);
			$transaction_amount = $splitData[7];
			$total_elec_bill = $splitData[8];
			$elec_bill = $splitData[9];
			$incentive = $splitData[10];
			$value_added_tax = $splitData[11];
			$penalty_fee = $splitData[12];
			//$penalty_fee = "000000000";
			$admin_charge = $splitData[13];
			$terminal_id = $splitData[14];
			// $admin_charge = $splitData[12];
			// $terminal_id = $splitData[13];

			$tgltransaksi = substr($tanggal, 0, 8);

			$cekData = RekonTransPLNPostpaid::where(DB::raw('left(datetime_local,8)'),$tgltransaksi)
				->where('subscriber_id',$subscriber_id)
				->where('bill_period',$bill_period)
				->first();

			if($cekData){
				$postpaid = RekonTransPLNPostpaid::find($cekData->id);
				//$postpaid = $cekData;
			}else{
				$postpaid = new RekonTransPLNPostpaid();
			}
			
			$postpaid->datetime_local = $tanggal;
			$postpaid->bank_code = $bank_code;
			$postpaid->partner_id = $partner_id;
			$postpaid->merchant_code = $merchant_code;
			$postpaid->switcher_ref_number = $switcher_ref_number;
			$postpaid->subscriber_id = $subscriber_id;
			$postpaid->bill_period = $bill_period;
			$postpaid->transaction_amount = (int)$transaction_amount;
			$postpaid->total_elect_bill = (int)$total_elec_bill;
			$postpaid->elect_bill = (int)$elec_bill;
			$postpaid->incentive = $incentive;
			$postpaid->value_added_tax = (int)$value_added_tax;
			$postpaid->penalty_fee = (int)$penalty_fee;
			$postpaid->admin_charge = (int)$admin_charge;
			$postpaid->terminal_id = $terminal_id;
			$postpaid->save();
		}
	}

	private function prosesRekonPrepaid($isiFile){
		$splitIsi = explode(PHP_EOL, $isiFile);
		//$splitIsi = explode("\r\n", $isiFile);

		array_splice($splitIsi, 0, 1);
		array_splice($splitIsi, -1, 1);
		array_splice($splitIsi, -1, 1);

		foreach ($splitIsi as $isi) {
			//$data = str_replace("\t", "", $isi);
			$splitData = explode("|", $isi);
			$tanggal = $splitData[0];
			$partner_id = $splitData[1];
			$merchant_code = $splitData[2];
			$pln_ref_number = $splitData[3];
			$switcher_ref_number = $splitData[4];
			$material_number = $splitData[5];
			$subscriber_id = $splitData[6];
			$transaction_amount = floatval(substr($splitData[7],0,strlen($splitData[7])-2).".".substr($splitData[7],-1*2));
			$admin_charge = floatval($splitData[8]);
			$stump_duty = floatval(substr($splitData[9],0,strlen($splitData[9])-2).".".substr($splitData[9],-1*2));
			$value_added_tax = floatval(substr($splitData[10],0,strlen($splitData[10])-2).".".substr($splitData[10],-1*2));
			$public_lighting_tax = floatval(substr($splitData[11],0,strlen($splitData[11])-2).".".substr($splitData[11],-1*2));
			$cust_payable = floatval(substr($splitData[12],0,strlen($splitData[12])-2).".".substr($splitData[12],-1*2));
			$power_purchase = floatval(substr($splitData[13],0,strlen($splitData[13])-2).".".substr($splitData[13],-1*2));
			$power_kwh_unit = floatval(substr($splitData[14],0,strlen($splitData[14])-2).".".substr($splitData[14],-1*2));
			$token_number = $splitData[15];
			$bank_code = $splitData[16];
			$terminal_id = $splitData[17];

			$tgltransaksi = substr($tanggal, 0, 8);

			$cekData = RekonTransPLNPrepaid::where(DB::raw('left(datetime_local,8)'),$tgltransaksi)
				->where('material_number',$material_number)
				->where('switcher_ref_number',$switcher_ref_number)
				->first();

			if($cekData){
				$prepaid = RekonTransPLNPrepaid::find($cekData->id);
				//$postpaid = $cekData;
			}else{
				$prepaid = new RekonTransPLNPrepaid();
			}
			
			$prepaid->datetime_local = $tanggal;
			$prepaid->partner_id = $partner_id;
			$prepaid->merchant_code = $merchant_code;
			$prepaid->pln_ref_number = $pln_ref_number;
			$prepaid->switcher_ref_number = $switcher_ref_number;
			$prepaid->material_number = $material_number;
			$prepaid->transaction_amount = $transaction_amount;
			$prepaid->admin_charge = $admin_charge;
			$prepaid->stump_duty = $stump_duty;
			$prepaid->value_added_tax = $value_added_tax;
			$prepaid->public_lighting_tax = $public_lighting_tax;
			$prepaid->customer_payable = $cust_payable;
			$prepaid->power_purchase = $power_purchase;
			$prepaid->purchase_kwh_unit = $power_kwh_unit;
			$prepaid->token_number = $token_number;
			$prepaid->bank_code = $bank_code;
			$prepaid->terminal_id = $terminal_id;
			$prepaid->subscriber_id = $subscriber_id;
			$prepaid->save();
		}
	}

	public function ambilRekonStarlink(Request $request){

		$file_path = $request->path;
		$jenis = $request->jenis;

		$NamaFile = explode("/", $file_path);
		$NamaFile = $NamaFile[2];

		$tgl_rekon = explode("-",  $NamaFile);
		$tgl_rekon = $tgl_rekon[3];
		$tgl_rekon = explode(".", $tgl_rekon);
		$tgl_rekon = $tgl_rekon[0];

		$tahun = substr($tgl_rekon, 0, 4);
		$bulan = substr($tgl_rekon, 4, 2);
		$tgl = substr($tgl_rekon, 6, 2);

		$tgl_rekon = $tahun."-".$bulan."-".$tgl;

		try{
			switch ($jenis) {
				case 'POSTPAID':
					$isiFile = Storage::disk('ftp')->get($file_path); //50501 POSTPAID
					$this->prosesRekonPostpaid($isiFile);
					break;

				case 'PREPAID':
					$isiFile = Storage::disk('ftp')->get($file_path); //50502 PREPAID
					$this->prosesRekonPrepaid($isiFile);
					break;
			}

			$cekFile = FileRekonPLN::where('nama_file',$NamaFile)->first();
			if($cekFile){
				$idRekon = $cekFile->id;
				$FileRekon = FileRekonPLN::find($idRekon);
			}else{
				$FileRekon = new FileRekonPLN();
			}

			$FileRekon->nama_file = $NamaFile;
			$FileRekon->tgl_rekon_file = $tgl_rekon;
			$FileRekon->tgl_transaksi = "-";
			$FileRekon->save();

			return Response::json(array(
                'status' => 'Success',
                'message' => 'Proses ambil data rekon selesai.',
            ),200);

		}catch (\Exception $e){
			$error = explode("\r\n",$e->getMessage());
            return Response::json(array(
                'status' => 'Error',
                'message' => 'Data tidak ada.',
            ),200);
		}
	}

	public function getEdit($id){
		$edit = RekonTransPLNPostpaid::where('id',$id)->first();
		return Response::json(array(
            'status' => 'Success',
            'message' => '-',
            'data' => $edit
        ),200);
	}

	private function selisihPostpaid($tanggal,$is_selisih,$jenis){
		$listTransaksi = DB::table('vw_transaksi_pln')
			->where('tanggal',$tanggal)
			->select('idpel','periode');

		$listRekon = DB::table('rekon_pln_postpaid')
			->where(DB::raw('substr(datetime_local,1,8)'),str_replace("-", "", $tanggal))
			->select('subscriber_id','bill_period');

		$Rekon = $listTransaksi->union($listRekon)
			->groupBy('idpel','periode')
			->get();

		$cekPedami = DB::table('vw_transaksi_pln')
			->where('tanggal',$tanggal)
			->select('id','idpel','nama','periode','tagihan','admin','total','tanggal',DB::raw('concat(idpel,periode) as findid'))
			->get();

		$cekStarlink = DB::table('rekon_pln_postpaid')
			->where(DB::raw('substr(datetime_local,1,8)'),str_replace("-", "", $tanggal))
			->select('id','subscriber_id','bill_period','total_elect_bill','admin_charge',DB::raw('total_elect_bill+admin_charge as total'),DB::raw('substr(datetime_local,1,8) as tanggal'),DB::raw('concat(subscriber_id,bill_period) as findid'))
			->get();

		$Hasil = array();
		foreach ($Rekon as $Rek) {

			$firstPedami = array_first($cekPedami, function ($value, $key) use ($Rek) {
			    return $value->findid == $Rek->idpel.$Rek->periode;
			});

			if($firstPedami){
				$data['a_id'] = $firstPedami->id;
				$data['a_idpel'] = $firstPedami->idpel;
				$data['a_nama'] = $firstPedami->nama;
				$data['a_periode'] = $firstPedami->periode;
				$data['a_tagihan'] = $firstPedami->tagihan;
				$data['a_admin'] = $firstPedami->admin;
				$data['a_total'] = $firstPedami->total;
				$data['a_tanggal'] = $firstPedami->tanggal;
			}else{
				$data['a_id'] = "";
				$data['a_idpel'] = "";
				$data['a_nama'] = "";
				$data['a_periode'] = "";
				$data['a_tagihan'] = "";
				$data['a_admin'] = "";
				$data['a_total'] = "";
				$data['a_tanggal'] = "";
			}

			$firstStarlink = array_first($cekStarlink, function ($value, $key) use ($Rek) {
			    return $value->findid == $Rek->idpel.$Rek->periode;
			});

			if($firstStarlink){
				$data['b_id'] = $firstStarlink->id;
				$data['b_idpel'] = $firstStarlink->subscriber_id;
				$data['b_nama'] = "-";
				$data['b_periode'] = $firstStarlink->bill_period;
				$data['b_tagihan'] = $firstStarlink->total_elect_bill;
				$data['b_admin'] = $firstStarlink->admin_charge;
				$data['b_total'] = $firstStarlink->total;
				$data['b_tanggal'] = $firstStarlink->tanggal;
			}else{
				$data['b_id'] = "";
				$data['b_idpel'] = "";
				$data['b_nama'] = "";
				$data['b_periode'] = "";
				$data['b_tagihan'] = "";
				$data['b_admin'] = "";
				$data['b_total'] = "";
				$data['b_tanggal'] = "";
			}

			if($is_selisih > 0){
				if(!$firstStarlink || !$firstPedami){
					array_push($Hasil, $data);
				}
			}else{
				array_push($Hasil, $data);
			}
		}
		return $Hasil;
	}

	private function selisihPrepaid($tanggal,$is_selisih,$jenis){
		$listTransaksi = DB::table('vw_transkasi_pln_prepaid_rekon')
			->where('tanggal',$tanggal)
			->select('idpel','switcher_ref_number');

		$listRekon = DB::table('rekon_pln_prepaid')
			->where(DB::raw('substr(datetime_local,1,8)'),str_replace("-", "", $tanggal))
			->select('subscriber_id','pln_ref_number');

		$Rekon = $listTransaksi->union($listRekon)
			->groupBy('idpel','switcher_ref_number')
			->get();

		$cekPedami = DB::table('vw_transkasi_pln_prepaid_rekon')
			->where('tanggal',$tanggal)
			->select('id','idpel','nama','periode','tagihan','admin','total','tanggal',DB::raw('concat(idpel,switcher_ref_number) as findid'))
			->get();

		$cekStarlink = DB::table('rekon_pln_prepaid')
			->where(DB::raw('substr(datetime_local,1,8)'),str_replace("-", "", $tanggal))
			->select('id','subscriber_id',DB::raw("'-' as periode"),'transaction_amount','admin_charge',DB::raw('transaction_amount+admin_charge as total'),DB::raw('substr(datetime_local,1,8) as tanggal'),DB::raw('concat(subscriber_id,pln_ref_number) as findid'))
			->get();

		$Hasil = array();
		foreach ($Rekon as $Rek) {

			$firstPedami = array_first($cekPedami, function ($value, $key) use ($Rek) {
			    return $value->findid == $Rek->idpel.$Rek->switcher_ref_number;
			});

			if($firstPedami){
				$data['a_id'] = $firstPedami->id;
				$data['a_idpel'] = $firstPedami->idpel;
				$data['a_nama'] = $firstPedami->nama;
				$data['a_periode'] = $firstPedami->periode;
				$data['a_tagihan'] = $firstPedami->tagihan;
				$data['a_admin'] = $firstPedami->admin;
				$data['a_total'] = $firstPedami->total;
				$data['a_tanggal'] = $firstPedami->tanggal;
			}else{
				$data['a_id'] = "";
				$data['a_idpel'] = "";
				$data['a_nama'] = "";
				$data['a_periode'] = "";
				$data['a_tagihan'] = "";
				$data['a_admin'] = "";
				$data['a_total'] = "";
				$data['a_tanggal'] = "";
			}

			$firstStarlink = array_first($cekStarlink, function ($value, $key) use ($Rek) {
			    return $value->findid == $Rek->idpel.$Rek->switcher_ref_number;
			});

			if($firstStarlink){
				$data['b_id'] = $firstStarlink->id;
				$data['b_idpel'] = $firstStarlink->subscriber_id;
				$data['b_nama'] = "-";
				$data['b_periode'] = $firstStarlink->periode;
				$data['b_tagihan'] = $firstStarlink->transaction_amount;
				$data['b_admin'] = $firstStarlink->admin_charge;
				$data['b_total'] = $firstStarlink->total;
				$data['b_tanggal'] = $firstStarlink->tanggal;
			}else{
				$data['b_id'] = "";
				$data['b_idpel'] = "";
				$data['b_nama'] = "";
				$data['b_periode'] = "";
				$data['b_tagihan'] = "";
				$data['b_admin'] = "";
				$data['b_total'] = "";
				$data['b_tanggal'] = "";
			}

			if($is_selisih > 0){
				if(!$firstStarlink || !$firstPedami){
					array_push($Hasil, $data);
				}
			}else{
				array_push($Hasil, $data);
			}
		}
		return $Hasil;
	}


	public function ProsesRekonRev1($tanggal,$is_selisih,$jenis){
		try{

			if($jenis == "POSTPAID"){
				$Hasil = $this->selisihPostpaid($tanggal,$is_selisih,$jenis);
				
				return Response::json(array(
	                'status' => 'Success',
	                'message' => '-',
	                'data' => $Hasil
	            ),200);
			}

			if($jenis == "PREPAID"){
				$Hasil = $this->selisihPrepaid($tanggal,$is_selisih,$jenis);
				
				return Response::json(array(
	                'status' => 'Success',
	                'message' => '-',
	                'data' => $Hasil
	            ),200);
			}
			
			return Response::json(array(
                'status' => 'Error',
                'message' => 'Data tidak ditemukan'
            ),200);
		}catch (Exception $e){
			$error = explode("\r\n",$e->getMessage());
            return Response::json(array(
                'status' => 'Error',
                'message' => 'Terjadi kesalahan sistem',
            ),200);
		}
	}

	public function ProsesRekon($tanggal,$is_selisih,$jenis){
		try{

			switch ($is_selisih) {
				case 0:
					switch ($jenis) {
						case 'POSTPAID':
							$dataRekon = RekonPLNPostpaid::where(function ($query) use ($tanggal) {
								$query->where('a_tanggal',$tanggal)
									->orWhere('b_tanggal',$tanggal);
							}); 
							break;
						case 'PREPAID':
							$dataRekon = RekonPLNPrepaid::where(function ($query) use ($tanggal) {
								$query->where('a_tanggal',$tanggal)
									->orWhere('b_tanggal',$tanggal);
							}); 
							break;
					}
					break;
				case 1:
					switch ($jenis) {
						case 'POSTPAID':
							$dataRekon = RekonPLNPostpaid::where(function ($query) use ($tanggal) {
								$query->where('a_tanggal',$tanggal)
									->orWhere('b_tanggal',$tanggal);
							})
							->where(function ($query) {
								$query->whereNull("a_id")
									->orWhereNull("b_id"); 
							});
							
							break;
						case 'PREPAID':
							$dataRekon = RekonPLNPrepaid::where(function ($query) use ($tanggal) {
								$query->where('a_tanggal',$tanggal)
									->orWhere('b_tanggal',$tanggal);
							})
							->where(function ($query) use ($tanggal) {
								$query->whereNull("a_id")
									->orWhereNull("b_id"); 
							}); 
							break;
					}
					break;
			}

			$dataRekon = $dataRekon->get();
			
			return Response::json(array(
                'status' => 'Error',
                'message' => '-',
                'data' => $dataRekon
            ),200);
		}catch (\Exception $e){
			$error = explode("\r\n",$e->getMessage());
            return Response::json(array(
                'status' => 'Error',
                'message' => 'Terjadi kesalahan sistem',
            ),200);
		}
	}

	public function cancelPayment(Request $request){
		try{
			$id = $request->id;
			$jenis = $request->jenis;
			$cekData = PlnTransaksi::find($id);

			$idData = $cekData->idpelblth."-".$id;
			$cekData->idpelblth = $idData;
			$cekData->flag_transaksi = "cancel";

			$cekData->save();

			return Response::json(array(
                'status' => 'Success',
                'message' => 'Cancel Payment Berhasil. ' . $idData,
            ),200);

		}catch (Exception $e){
			$error = explode("\r\n",$e->getMessage());
            return Response::json(array(
                'status' => 'Error',
                'message' => 'Terjadi kesalahan sistem',
            ),200);
		}
	}

	private function getDateFormat($BlTh){
		$mBulan = substr($BlTh, 4,2);
		$mBl = "";
		switch ($mBulan) {
			case '01':
				$mBl = "JAN";
				break;
			case '02':
				$mBl = "FEB";
				break;
			case '03':
				$mBl = "MAR";
				break;
			case '04':
				$mBl = "APR";
				break;
			case '05':
				$mBl = "MEI";
				break;
			case '06':
				$mBl = "JUN";
				break;
			case '07':
				$mBl = "JUL";
				break;
			case '08':
				$mBl = "AGT";
				break;
			case '09':
				$mBl = "SEP";
				break;
			case '10':
				$mBl = "OKT";
				break;
			case '11':
				$mBl = "NOV";
				break;
			case '12':
				$mBl = "DES";
				break;
		}
		$mTh = substr($BlTh,2,2);
		return $mBl." ".$mTh;
	}
}
