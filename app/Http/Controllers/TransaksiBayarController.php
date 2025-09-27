<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\PdambjmEvent;
use App\APIServices\PdamBjmAPIv2;
use Response;
use App\PlnServices\PostPaid;
use App\PlnServices\PostPaidNew;
use App\PlnServices\PrePaid;
use App\PlnServices\PrePaidNew;
use App\PlnServices\NonTaglis;
use App\Models\PlnPrepaidTransaksi;
use App\Models\PlnNontaglisTransaksi;
use App\Models\PlnTransaksi;
use App\Models\mLoket;
use Illuminate\Support\Facades\Auth;
use DB;

class TransaksiBayarController extends Controller
{
    public function __construct()
    {

    }

	public function index()
	{
		return view('admin.transaksi_bayar');
	}

	public function Advise(Request $request){

		//$nomor_pelanggan = $request->input('idpel');
		$produk = $request->input('produk');
		$token = $request->input('denom');
		$idtrx = $request->input('idtrx');

		$message = $request->input('message');
		$username = $request->input('username');

		$isMobile = 1;

		switch ($produk) {
			case 'PLN_POSTPAID':
				$message = json_decode($message, true);
				$nomor_pelanggan = $message['input1'];

				$cekAdvise = PostPaidNew::prosesAdvise($nomor_pelanggan,$produk,$idtrx,$isMobile,$username);
				return $cekAdvise;
				break;
		
			case 'PLN_PREPAID':
				$message = json_decode($message, true);
				$nomor_pelanggan = $message['input1'];

				$cekAdvise = PrePaidNew::prosesAdvise($nomor_pelanggan,$produk,$token,$idtrx,$isMobile,$username);
				return $cekAdvise;
				break;

			case 'PDAMBJM':
				$cekAdvise = PdamBjmAPIv2::prosesAdvise("-",$produk,$idtrx,$isMobile,$username);
				return $cekAdvise;
				break;
		}
	}

	public function cetakUlang($nomor_pelanggan,$tgl_awal,$tgl_akhir,$produk){
		switch ($produk) {
			case 'PDAMBJM':
				$dataCetak = PdamBjmAPIv2::requestCetakUlangBaru($nomor_pelanggan, $tgl_awal, $tgl_akhir, "-", 0, "-");
				$content = json_decode($dataCetak->content(), true);
				$content['produk'] = "PDAMBJM";

				if($content['status'] == "Success"){
					$content['status'] = true;
				}else{
					$content['status'] = false;
				}
				return $content;
				break;
			case 'PLN_POSTPAID':
				$Transaksi = PlnTransaksi::whereBetween(DB::raw('cast(transaction_date as date)'),[$tgl_awal, $tgl_akhir])
					->where('subcriber_id','=',$nomor_pelanggan);

				if (!Auth::user()->hasPermissionTo('Cetak Ulang Semua')) {
		            $idLoket = Auth::user()->loket_id;
		            $kodeLoket = mLoket::where("id",$idLoket)->first()->loket_code;

		            $Transaksi = $Transaksi->where('loket_code',$kodeLoket)->get();
		        }else{
		            $Transaksi = $Transaksi->get();
		        }

				if($Transaksi->count() > 0){

					$mData['billing'] = array();
					foreach ($Transaksi as $Trans) {

						$mData['subcriber_id'] = $Trans->subcriber_id;
						$mData['subcriber_name'] = $Trans->subcriber_name ;
						$mData['subcriber_segment'] = $Trans->subcriber_segment;
						$mData['power_consumtion'] = $Trans->power_consumtion;
						$mData['switcher_ref'] = $Trans->switcher_ref;
						$mData['transaction_code'] = $Trans->transaction_code;
						$mData['transaction_date'] = $Trans->transaction_date;

						$mBill['outstanding_bill'] = $Trans->outstanding_bill;
						$mBill['bill_status'] = $Trans->bill_status;
						$mBill['bill_periode'] = $Trans->bill_periode;
						$mBill['prev_meter_read_1'] = $Trans->prev_meter_read_1;
						$mBill['curr_meter_read_1'] = $Trans->curr_meter_read_1;
						$mBill['total_elec_bill'] = $Trans->total_elec_bill;
						$mBill['penalty_fee'] = $Trans->penalty_fee;
						$mBill['admin_charge'] = $Trans->admin_charge;

						array_push($mData['billing'], $mBill);
					}

					return array(
		                'status' => true,
		                'message' => 'Cetak Ulang IDPEL '. $nomor_pelanggan,
		                'produk' => 'PLN_POSTPAID',
		                'data' => $mData
		            );
				}else{
					return array(
		                'status' => false,
		                'message' => 'Transaksi tidak ditemukan.'
		            );
				}
				break;
			case 'PLN_PREPAID':
				$idpelLen = strlen($nomor_pelanggan);

				if($idpelLen == 11){
					$Transaksi = PlnPrepaidTransaksi::where('material_number','=',$nomor_pelanggan)->whereBetween(DB::raw('cast(transaction_date as date)'),[$tgl_awal, $tgl_akhir]);
				}else{
					$Transaksi = PlnPrepaidTransaksi::where('subscriber_id','=',$nomor_pelanggan)->whereBetween(DB::raw('cast(transaction_date as date)'),[$tgl_awal, $tgl_akhir]);
				}

				if (!Auth::user()->hasPermissionTo('Cetak Ulang Semua')) {
		            $idLoket = Auth::user()->loket_id;
		            $kodeLoket = mLoket::where("id",$idLoket)->first()->loket_code;

		            $Transaksi = $Transaksi->where('loket_code',$kodeLoket)->orderBy('transaction_date','desc')->limit(10)->get();
		        }else{
		            $Transaksi = $Transaksi->orderBy('transaction_date','desc')->limit(10)->get();
		        }

				if($Transaksi->count() > 0){

					$mToken = array();
					foreach ($Transaksi as $Trans) {
						$mData['id'] = $Trans->id;
						$mData['material_number'] = $Trans->material_number;
						$mData['admin_charge'] = $Trans->admin_charge;
						$mData['admin'] = $Trans->admin_charge ;
						$mData['subscriber_id'] = $Trans->subscriber_id;
						$mData['stampduty'] = $Trans->stump_duty;
						$mData['stump_duty'] = $Trans->stump_duty;
						$mData['subscriber_name'] = $Trans->subscriber_name;
						$mData['addtax'] = $Trans->addtax;
						$mData['valueaddedtax'] = $Trans->addtax;
						$mData['subscriber_segment'] = $Trans->subscriber_segment;
						$mData['power_categori'] = $Trans->power_categori;
						$mData['ligthingtax'] = $Trans->ligthingtax;
						$mData['lightingtax'] = $Trans->ligthingtax;
						$mData['switcher_ref_number'] = $Trans->switcher_ref_number;
						$mData['cust_payable'] = $Trans->cust_payable;
						$mData['power_purchase'] = $Trans->power_purchase;
						$mData['purchase_kwh'] = $Trans->purchase_kwh;

						$token_number = str_split($Trans->token_number,4);
						$space_token = "";
						foreach ($token_number as $token) {
							$space_token .= $token." ";
						}

						$mData['rupiah_token'] = $Trans->rupiah_token;
						$mData['token_number'] = $space_token;
						$mData['info_text'] = $Trans->info_text;
						$mData['transaction_code'] = $Trans->transaction_code;
						$mData['transaction_date'] = $Trans->transaction_date;

						array_push($mToken, $mData);
					}

					return array(
		                'status' => true,
		                'message' => 'Cetak Ulang IDPEL '. $nomor_pelanggan,
		                'produk' => 'PLN_PREPAID',
		                'data' => $mToken
		            );
				}else{
					return array(
		                'status' => false,
		                'message' => 'Transaksi tidak ditemukan.'
		            );
				}
				break;
			case 'PLN_NONTAG':
				$Transaksi = PlnNontaglisTransaksi::whereBetween(DB::raw('cast(transaction_date as date)'),[$tgl_awal, $tgl_akhir])
					->where('register_number','=',$nomor_pelanggan);

				if (!Auth::user()->hasPermissionTo('Cetak Ulang Semua')) {
		            $idLoket = Auth::user()->loket_id;
		            $kodeLoket = mLoket::where("id",$idLoket)->first()->loket_code;

		            $Transaksi = $Transaksi->where('loket_code',$kodeLoket)->first();
		        }else{
		            $Transaksi = $Transaksi->first();
		        }

				if($Transaksi){

					$mData['register_number'] = $Transaksi->register_number;
					$mData['registration_date'] = $Transaksi->registration_date ;
					$mData['transaction_name'] = $Transaksi->transaction_name;
					$mData['subscriber_id'] = $Transaksi->subscriber_id;
					$mData['subscriber_name'] = $Transaksi->subscriber_name;
					$mData['pln_bill_value'] = $Transaksi->pln_bill_value;
					$mData['switcher_ref_number'] = $Transaksi->switcher_ref_number;
					$mData['admin_charge'] = $Transaksi->admin_charge;
					$mData['info_text'] = $Transaksi->info_text;
					$mData['transaction_code'] = $Transaksi->transaction_code;
					$mData['transaction_date'] = $Transaksi->transaction_date;

					return array(
		                'status' => true,
		                'message' => 'Cetak Ulang IDPEL '. $nomor_pelanggan,
		                'produk' => 'PLN_NONTAG',
		                'data' => $mData
		            );
				}else{
					return array(
		                'status' => false,
		                'message' => 'Transaksi tidak ditemukan.'
		            );
				}
				break;
			
			default:
				return array(
	                'status' => false,
	                'message' => 'Transaksi tidak ditemukan.'
	            );
				break;
		}
	}

	public function inquery($idpel,$jenis,$loket,$token){
		$dataRek = null;
		$arrRek = null;
		$isRekSuccess = false;
		$pesanRek = "";

		$detailRek = "";
		$payment_message = "";
		$reversal_message = "";

		switch ($jenis) {
			case 'PDAMBJM':
				$Response = PdamBjmAPIv2::inqueryPelanggan($idpel,$loket,false,"","","");
				$content = json_decode($Response->content(), true);

				if($content['status'] == "Success"){
					$dataRek = $content['data'];
					
					$diskon = 0;

					$arrRek['idpel'] = $dataRek[0]['idlgn'];
					$arrRek['nama'] = $dataRek[0]['nama'];
					$arrRek['periode'] = $this->getDateFormat($dataRek[0]['thbln']);
					$arrRek['sub_total'] = 0;
					$arrRek['admin'] = 0;
					$arrRek['total'] = 0;
					$arrRek['produk'] = "PDAMBJM";
					$arrRek['jml'] = 0;

					for($i=0; $i<sizeof($dataRek); $i++){
						
						$arrRek['sub_total'] = $arrRek['sub_total'] + $dataRek[$i]['total'];
-						$arrRek['admin'] = $arrRek['admin'] + $dataRek[$i]['admin_kop'];
-						$arrRek['total'] = $arrRek['total'] + ($dataRek[$i]['total'] + $dataRek[$i]['admin_kop']);
						$arrRek['jml'] = $arrRek['jml'] + 1;

						if($i+1 == sizeof($dataRek) && sizeof($dataRek) > 1){
							$arrRek['periode'] = $arrRek['periode'] . " - " . $this->getDateFormat($dataRek[$i]['thbln']);
						}
						$diskon += $dataRek[$i]['diskon'];
					}

					$arrRek['diskon'] = $diskon;

					$arrRek['jml'] = (string)$arrRek['jml'];

					$detailRek = $dataRek;

					$isRekSuccess = true;
				}else{
					$isRekSuccess = false;
					$pesanRek = $content['message'];
				}
				
				break;
			case 'PLN_POSTPAID':
				$Response = PostPaid::InqueryTagihanPostPaid($idpel,false,"");
				$content = $Response;

				if($content['status']){

					$dataRek = $content['customer'];

					$arrRek['idpel'] = $dataRek['subcriber_id'];
					$arrRek['nama'] = $dataRek['subcriber_name'];
					$arrRek['periode'] = $dataRek['periode'];
					$arrRek['sub_total'] = $dataRek['total_pln'];
					$arrRek['admin'] = $dataRek['total_admin_charge'];
					$arrRek['diskon'] = "0";
					$arrRek['total'] = $dataRek['total_tagihan'];
					$arrRek['produk'] = "PLN_POSTPAID";
					if((int)$dataRek['bill_status'] == (int)$dataRek['outstanding_bill']){
						$arrRek['jml'] = $dataRek['bill_status'];
					}else{
						$arrRek['jml'] = $dataRek['bill_status'] . " / " . (int)$dataRek['outstanding_bill'];
					}
					
					$detailRek = $dataRek;
					$payment_message = $dataRek['payment_message'];
					$reversal_message = $dataRek['reversal_message'];

					unset($detailRek['payment_message']);
					unset($detailRek['reversal_message']);

					$isRekSuccess = true;
				}else{
					$isRekSuccess = false;
					$pesanRek = $content['message'];

				}

				break;
			case 'PLN_POSTPAID_NEW':

					$Response = PostPaidNew::InqueryTagihanPostPaid($idpel,false,"");
					$content = $Response;
	
					if($content['status']){
	
						$dataRek = $content['customer'];
	
						$arrRek['idpel'] = $dataRek['subcriber_id'];
						$arrRek['nama'] = $dataRek['subcriber_name'];
						$arrRek['periode'] = $dataRek['periode'];
						$arrRek['sub_total'] = $dataRek['total_pln'];
						$arrRek['admin'] = $dataRek['total_admin_charge'];
						$arrRek['diskon'] = "0";
						$arrRek['total'] = $dataRek['total_tagihan'];
						$arrRek['produk'] = "PLN_POSTPAID";
						if((int)$dataRek['bill_status'] == (int)$dataRek['outstanding_bill']){
							$arrRek['jml'] = $dataRek['bill_status'];
						}else{
							$arrRek['jml'] = $dataRek['bill_status'] . " / " . (int)$dataRek['outstanding_bill'];
						}
						
						$detailRek = $dataRek;
						$payment_message = $dataRek['payment_message'];
						$reversal_message = $dataRek['reversal_message'];
	
						unset($detailRek['payment_message']);
						unset($detailRek['reversal_message']);
	
						$isRekSuccess = true;
					}else{
						$isRekSuccess = false;
						$pesanRek = $content['message'];
	
					}
	
					break;
			case 'PLN_PREPAID':

				$idpelLen = strlen($idpel);
				if($idpelLen == 11){
					$flag = "0";
				}else{
					$flag = "1";
				}
				$Response = PrePaid::prosesInquery($idpel,$flag,false,"",$token);
				$content = $Response;

				if($content['status']){

					$dataRek = $content['customer']['data'];

					$arrRek['idpel'] = $dataRek['subscriber_id'];
					$arrRek['nama'] = $dataRek['subscriber_name'];
					$arrRek['periode'] = "-";
					$arrRek['sub_total'] = $token;
					$arrRek['admin'] = $dataRek['admin_charge'];
					$arrRek['diskon'] = "0";
					$arrRek['total'] = $token+$dataRek['admin_charge'];
					$arrRek['produk'] = "PLN_PREPAID";
					$arrRek['jml'] = "1";

					$detailRek = $content['customer']['data'];
					$payment_message = $content['customer']['purchase_message'];
					$reversal_message = $content['customer']['reversal_message'];

					$TransactionAmount = str_pad($token, 12, "0", STR_PAD_LEFT);
					$buying_option = "0";
					
					$isRekSuccess = true;
				}else{
					$isRekSuccess = false;
					$pesanRek = $content['message'];

				}

				break;
			case 'PLN_PREPAID_NEW':

					$idpelLen = strlen($idpel);
					if($idpelLen == 11){
						$flag = "0";
					}else{
						$flag = "1";
					}
					$Response = PrePaidNew::prosesInquery($idpel,$flag,false,"",$token);
					$content = $Response;
	
					if($content['status']){
	
						$dataRek = $content['customer']['data'];
	
						$arrRek['idpel'] = $dataRek['subscriber_id'];
						$arrRek['nama'] = $dataRek['subscriber_name'];
						$arrRek['periode'] = "-";
						$arrRek['sub_total'] = $token;
						$arrRek['admin'] = $dataRek['admin_charge'];
						$arrRek['diskon'] = "0";
						$arrRek['total'] = $token+$dataRek['admin_charge'];
						$arrRek['produk'] = "PLN_PREPAID";
						$arrRek['jml'] = "1";
	
						$detailRek = $content['customer']['data'];
						$payment_message = $content['customer']['purchase_message'];
						$reversal_message = $content['customer']['reversal_message'];
	
						$TransactionAmount = str_pad($token, 12, "0", STR_PAD_LEFT);
						$buying_option = "0";
						
						$isRekSuccess = true;
					}else{
						$isRekSuccess = false;
						$pesanRek = $content['message'];
	
					}
	
					break;
			case 'PLN_NONTAG':
				$Response = NonTaglis::Inquery($idpel,false,"");
				$content = $Response;

				if($content['status']){

					$dataRek = $content['customer']['data'];

					$arrRek['idpel'] = $dataRek['register_number'];
					$arrRek['nama'] = $dataRek['subscriber_name'];
					$arrRek['periode'] = $dataRek['transaction_name'];
					$arrRek['sub_total'] = $dataRek['total_transaction'];
					$arrRek['admin'] = $dataRek['admin_charge'];
					$arrRek['diskon'] = "0";
					$arrRek['total'] = $dataRek['total_transaction'] + $dataRek['admin_charge'];
					$arrRek['produk'] = "PLN_NONTAG";
					$arrRek['jml'] = "1";

					$detailRek = $content['customer']['data'];
					$payment_message = $content['customer']['payment_message'];
					$reversal_message = $content['customer']['reversal_message'];

					$isRekSuccess = true;
				}else{
					$isRekSuccess = false;
					$pesanRek = $content['message'];

				}
				break;
		}
		
		if(!$isRekSuccess){
			return Response::json(array(
                'status' => false,
                'message' => $pesanRek
            ),200);
		}else{
			return Response::json(array(
                'status' => true,
                'message' => $pesanRek,
                'data' => $arrRek,
                'detail' => $detailRek,
                'payment_message' => $payment_message,
                'reversal_message' => $reversal_message
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
