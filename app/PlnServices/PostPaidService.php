<?php

namespace App\PlnServices;

use Illuminate\Support\Facades\Config;
use App\PlnServices\ISOService;
use App\PlnServices\SocketService;
use Illuminate\Support\Facades\Auth;
use App\Models\PlnTransaksi;
use App\Models\mLoket;
use App\Models\PlnLog;
use App\Models\AuditNumber;
use App\Models\LogSistem;
use App\User;
use SimpleXMLElement;
use App\Http\Controllers\Helpers;

class PostPaidService
{
	protected $isoHelper;
	protected $socketHelper;

	protected $postpaid_code = "0599501";

    public function __construct(ISOService $isoHelper, SocketService $socketHelper)
    {
    	$this->isoHelper = $isoHelper;
    	$this->socketHelper = $socketHelper;
    }

    public function InqueryTagihanPostPaid($nomor_pelanggan,$is_mobile,$username){

    	try{
   //  		$pln_config = Config::get('app.pln');

   //  		$audit_number = AuditNumber::max('id');
   //  		if(!$audit_number){
   //  			$audit_number = "1";
   //  		}else{
   //  			$audit_number = $audit_number + 1;
   //  		}
    		
   //  		//SIMPAN LAST NUMBER TRACE AUDIT NUMBER
   //  		$aNumber = new AuditNumber();
			// $aNumber->subcriber_id = $nomor_pelanggan;
			// $aNumber->save();

   //  		//$PlnTransaksi = str_pad($PlnTransaksi, 8, "0", STR_PAD_RIGHT);

			// //$trace_audit_number = date('ymdHs'); //DIOLAH SENDIRI
			// $trace_audit_number = str_pad($audit_number, 12, "0", STR_PAD_LEFT);
			// //dd($trace_audit_number);

			// $datetime_local = date('YmdHis');
			// $merchant_code = "6021";
			// $bank_code = "4510017";
			// $partner_id = $pln_config['pcid']; //PCID DAPAT DARI STALINK.

			// if($is_mobile){
			// 	$terminal_id = strtoupper($username);
			// }else{
			// 	$terminal_id = strtoupper(Auth::user()->username); //DIOLAH SENDIRI
			// }
			
			// $terminal_id = str_pad($terminal_id, 16, "0", STR_PAD_LEFT);

			// $subcriber_id = $nomor_pelanggan; //NOMOR PELANGGAN PLN

			// $message_format = "21004030004180810000".$this->postpaid_code.$trace_audit_number.$datetime_local.$merchant_code."07".$bank_code."07".$partner_id.$terminal_id."019ST145S3".$subcriber_id;

			// $parsing = $this->isoHelper->iso_parser($message_format);
			// //$this->simpanLogPln($nomor_pelanggan,$message_format,json_encode($parsing),'INQUERY_REQUEST');

			// $response = $this->startSocket($message_format,'inquery',$is_mobile,$username);

			//return $response;

			$response_xml = Helpers::sent_pln_get("https://".env('PLN_IPADDR_NEW','').":".env('PLN_PORT_NEW','')."/srvSys/Transactional?q=2100!99501!0!".env('PLN_ID_MITRA','')."!".env('PLN_USER_MITRA','')."!".$nomor_pelanggan, env('PLN_AUTH_NEW',''));

			$response = new SimpleXMLElement(htmlspecialchars_decode($response_xml['response']));
			$response = json_encode($response);

			$this->simpanLogPln($nomor_pelanggan,"",$response,'INQUERY_POSTPAID');

            $response = json_decode($response, true);

            //dd($response);die();

            if($response['data']['rc'] == "000"){

            	$array_bill = array();

            	if($response['data']['bill'] > 1){
					//$bill_detail = $response['data']['data_element']['item'];
					$bill_detail = $response['data']['data_element']['item'];
            	}else{
            		$bill_detail = $response['data']['data_element'];
				}
				
				//var_dump($bill_detail);die();
            	
            	foreach ($bill_detail as $bill) {
            		$pel_bill = array(
						"subcriber_id" => $response['data']['subscriber_id'],
						"subcriber_name" => $response['data']['name'],
						"subcriber_segment" => $response['data']['segment'],
						"power_consumtion" => $response['data']['power'],
						"bill_periode" => $bill['billperiod'],
						"due_date" => "",
						"meter_read_date" => "",
						"incentive" => "",
						"total_elec_bill" => "".intval($bill['electricity_bill']),
						"added_tax" => "".intval($bill['tax']),
						"penalty_fee" => "".intval($bill['penalty']),
						"admin_charge" => "".($response['data']['admin']/$response['data']['bill']),
						"prev_meter_read_1" => "".intval($bill['stmold']),
						"curr_meter_read_1" => "".intval($bill['stmnew']),
						"prev_meter_read_2" => "0",
						"curr_meter_read_2" => "0",
						"prev_meter_read_3" => "0",
						"curr_meter_read_3" => "0",
					);
					array_push($array_bill, $pel_bill);
            	}

            	return array(
	                'status' => true,
	                'response_code' => $response['data']['rc'],
	                'message' => $response['data']['desc'],
	                'customer' => array(
						"switcher_id" => $response['data']['merchant'],
						"subcriber_id" => $response['data']['subscriber_id'],
						"bill_status" => $response['data']['bill'],
						//"outstanding_bill" => $response['data']['ots_bill'],
						"outstanding_bill" => $response['data']['bill'],
						"switcher_ref" => $response['data']['swreff'],
						"subcriber_name" => $response['data']['name'],
						"periode" => $response['data']['blth'],
						"service_unit" => '',
						"service_unit_phone" => '',
						"subcriber_segment" => $response['data']['segment'],
						"power_consumtion" => $response['data']['power'],
						"admin_charge" => $response['data']['admin'],
						"total_admin_charge" => $response['data']['admin'],
						"total_billing" => "".($response['data']['amount']-$response['data']['admin']),
						"total_added_tax" => "0",
						"total_penalty_fee" => "0",
						"total_pln" => "".($response['data']['amount']-$response['data']['admin']), //TAGIHAN PLN + DENDA
						"total_tagihan" => $response['data']['amount'], //TAGIHAN PLN + DENDA + ADMIN
						"billing" => $array_bill,
						"payment_message" => "2200!99501!0!".env('PLN_ID_MITRA','')."!".env('PLN_USER_MITRA','')."!".$nomor_pelanggan."!".$response['data']['amount']."!".$response['data']['trxid'],
						"reversal_message" => "",
						//"sisa_bill" => "".($response['data']['bill'] - $response['data']['ots_bill'])
						"sisa_bill" => "".($response['data']['bill'] - $response['data']['bill'])
					)
	            );
            }else{
            	$pesanError = $response['data']['desc'];
            	if($pesanError == 'Error rc 88'){
            		$pesanError = "Tagihan sudah dibayar";
            	}
            	return array(
	                'status' => false,
	                'response_code' => $response['data']['rc'],
	                'message' => $pesanError
	            );
            }

    	}catch (Exception $e) {
    		$err_message = explode("\r\n",$e->getMessage());
    		$this->simpanLogSistem(json_encode($err_message));
	        return array(
                'status' => false,
                'response_code' => "9991",
                'message' => "TERJADI KESALAHAN, TRANSAKSI GAGAL."
                //'message' => $err_message
            );
	    }
	}

	//TOTAL TRANSAKSI = TOTAL BILLING+ADMIN CHARGE
	//REQUEST PAYMENT
	public function PaymentTagihanPostPaid($subcriber_id,$payment_message,$total_bayar,$is_mobile,$username){

		try{

			$loketService = resolve('\App\Services\LoketService');

			if($is_mobile){
				$loket_id = User::where('username',$username)->select('loket_id')->first()->loket_id;
				$saldo = $loketService->getSaldo($loket_id);
			}else{
				$loket_id = Auth::user()->loket_id;
				$saldo = $loketService->getSaldo($loket_id);
			}

			if($saldo < $total_bayar){
				return array(
	                'status' => false,
	                'response_code' => "9990",
	                'message' => "SALDO TIDAK CUKUP UNTUK PEMBAYARAN"
	            );
			}

			// $parsing = $this->isoHelper->iso_parser($payment_message);
			// //$this->simpanLogPln($subcriber_id,$payment_message,json_encode($parsing),'PAYMENT_REQUEST');

			// $response_payment = $this->startSocket($payment_message,'payment',$is_mobile,$username);
			// return $response_payment;

			$response_xml = Helpers::sent_pln_get("https://".env('PLN_IPADDR_NEW','').":".env('PLN_PORT_NEW','')."/srvSys/Transactional?q=".$payment_message, env('PLN_AUTH_NEW',''));

			$response = new SimpleXMLElement(htmlspecialchars_decode($response_xml['response']));
			$response = json_encode($response);

			$this->simpanLogPln($subcriber_id,"",$response,'PAYMENT_POSTPAID');

            $response = json_decode($response, true);

            $total_billing = 0;
            $total_added_tax = 0;
            $total_penalty_fee = 0;
            $total_pln = 0;
            if($response['data']['rc'] == "000"){

            	$array_bill = array();

            	if($response['data']['bill'] > 1){
					$bill_detail = $response['data']['data_element']['item'];
            	}else{
            		$bill_detail = $response['data']['data_element'];
            	}
            	foreach ($bill_detail as $bill) {
            		
            		$pel_bill = array(
						"subcriber_id" => $response['data']['subscriber_id'],
						"subcriber_name" => $response['data']['name'],
						"subcriber_segment" => $response['data']['segment'],
						"power_consumtion" => $response['data']['power'],
						"bill_periode" => $this->getDateFormat($bill['billperiod']),
						"bill_status" => $response['data']['bill'],
						//"outstanding_bill" => $response['data']['ots_bill'],
						"outstanding_bill" => $response['data']['bill'],
						"due_date" => "",
						"meter_read_date" => "",
						"incentive" => "",
						"total_elec_bill" => "".intval($bill['electricity_bill']),
						"added_tax" => "".intval($bill['tax']),
						"penalty_fee" => "".intval($bill['penalty']),
						"admin_charge" => "".($response['data']['admin']/$response['data']['bill']),
						"prev_meter_read_1" => "".intval($bill['stmold']),
						"curr_meter_read_1" => "".intval($bill['stmnew']),
						"prev_meter_read_2" => "0",
						"curr_meter_read_2" => "0",
						"prev_meter_read_3" => "0",
						"curr_meter_read_3" => "0",
					);

					$total_billing+= $bill['electricity_bill'];
					$total_added_tax+= $bill['tax'];
					$total_penalty_fee+= $bill['penalty'];
					$total_pln += ($bill['electricity_bill']+$bill['tax']+$bill['penalty']);

					array_push($array_bill, $pel_bill);
            	}

            	$total_tagihan = $total_pln + $response['data']['admin'];

            	$paymentData = array(
					"switcher_id" => "",
					"subcriber_id" => $response['data']['subscriber_id'],
					"bill_status" => $response['data']['bill'],
					//"outstanding_bill" => $response['data']['ots_bill'],
					"outstanding_bill" => $response['data']['bill'],
					"switcher_ref" => $response['data']['swreff'],
					"subcriber_name" => $response['data']['name'],
					"service_unit" => "",
					"service_unit_phone" => "",
					"subcriber_segment" => $response['data']['segment'],
					"power_consumtion" => $response['data']['power'],
					"admin_charge" => $response['data']['admin'],
					"total_admin_charge" => $response['data']['admin'],
					"total_billing" => "".$total_billing,
					"total_added_tax" => "".$total_added_tax,
					"total_penalty_fee" => "".$total_penalty_fee,
					"total_pln" => "".$total_pln, //TAGIHAN PLN + DENDA
					"total_tagihan" => "".$total_tagihan, //TAGIHAN PLN + DENDA + ADMIN
					"billing" => $array_bill,
					//"sisa_bill" => "".($response['data']['bill'] - $response['data']['ots_bill'])
					"sisa_bill" => "".($response['data']['bill'] - $response['data']['bill'])
				);

            	$time = $response['data']['time'];
            	$tgl_format = substr($time, 0,4) . "-" . substr($time, 4,2). "-". substr($time, 6,2)." ". substr($time, 8,2).":". substr($time, 10,2).":". substr($time, 12,2);

				$unique_id = $this->simpanTransaksiPayment($paymentData,"",$tgl_format,$is_mobile,$username);

				$paymentData['transaction_code'] = $unique_id;
				$paymentData['transaction_date'] = $tgl_format;

				return array(
	                'status' => true,
	                'response_code' => $response['data']['rc'],
	                'message' => $response['data']['desc'],
	                'customer' => $paymentData
	            );

            }else{
            	return array(
	                'status' => false,
	                'response_code' => $response['data']['rc'],
	                'message' => $response['data']['desc']
	            );
            }

		}catch (\Exception $e) {

    		$err_message = explode("\r\n",$e->getMessage());
    		$this->simpanLogSistem(json_encode($err_message) . " ID=".$subcriber_id. " MESSAGE:". $payment_message);
	        return array(
                'status' => false,
                'response_code' => "9993",
                'message' => "TERJADI KESALAHAN, TRANSAKSI GAGAL."
            );
	    }

	}

	public function ReversalTagihanPostPaid($subcriber_id,$payment_message,$is_mobile,$username){

		try{

			$parsing = $this->isoHelper->iso_parser($payment_message);
			//$this->simpanLogPln($subcriber_id,$payment_message,json_encode($parsing),'REVERSAL_REQUEST');

			$response = $this->startSocket($payment_message, 'reversal',$is_mobile,$username);
			return $response;

		}catch (\Exception $e) {

    		$err_message = explode("\r\n",$e->getMessage());
    		$this->simpanLogSistem(json_encode($err_message));
	        return array(
                'status' => false,
                'response_code' => "9994",
                'message' => "TERJADI KESALAHAN, TRANSAKSI GAGAL."
            );
	    }
	}

	private function simpanLogSistem($Log){

		$mLog = new LogSistem();
		$mLog->log = $Log;
		$mLog->save();
	}

	private function simpanLogPln($subcriber_id,$iso_message,$json_message,$jenis){
		$Log = env("PLN_LOG",0);
		if($Log > 0){
			$Pln = new PlnLog();
			$Pln->iso_message = $iso_message;
			$Pln->json_message = $json_message;
			$Pln->jenis = $jenis;
			$Pln->subcriber_id = $subcriber_id;
			$Pln->save();
		}
		
	}

	private function simpanTransaksiPayment($parse_private_data,$partner_number,$tgl_format,$is_mobile,$username){
		if($is_mobile){
			$loket_id = User::where('username',$username)->select('loket_id')->first()->loket_id;
		}else{
			$username = Auth::user()->username;
			$loket_id = Auth::user()->loket_id;
		}

		$loket_name = "";
		$loket_code = "";
		$jenis_loket = "";

		$cekLoket = mLoket::where("id",$loket_id)->first();
        if($cekLoket != null){
            $loket_name = $cekLoket->nama;
            $loket_code = $cekLoket->loket_code;
            $jenis_loket = $cekLoket->jenis;
        }

        $unique_id = strtoupper($username)."-".strtoupper(date('YmdHis').'-'.uniqid());

		$bill_data = $parse_private_data['billing'];
		$total_bayar = 0;
		foreach ($bill_data as $bill) {

			$Pln = new PlnTransaksi();
			$Pln->subcriber_id = $parse_private_data['subcriber_id'];
			$Pln->subcriber_name = $parse_private_data['subcriber_name'];
			$Pln->subcriber_segment = $parse_private_data['subcriber_segment'];
			$Pln->switcher_ref = $parse_private_data['switcher_ref'];
			$Pln->power_consumtion = $parse_private_data['power_consumtion'];
			$Pln->outstanding_bill = $parse_private_data['outstanding_bill'];
			$Pln->bill_status = $parse_private_data['bill_status'];
			$Pln->trace_audit_number = $partner_number;

			$Pln->bill_periode = $bill['bill_periode'];
			$Pln->added_tax = $bill['added_tax'];
			$Pln->incentive = $bill['incentive'];
			$Pln->penalty_fee = $bill['penalty_fee'];
			$Pln->admin_charge = $bill['admin_charge'];
			$Pln->total_elec_bill = $bill['total_elec_bill'];
			$Pln->prev_meter_read_1 = $bill['prev_meter_read_1'];
			$Pln->curr_meter_read_1 = $bill['curr_meter_read_1'];
			$Pln->prev_meter_read_2 = $bill['prev_meter_read_2'];
			$Pln->curr_meter_read_2 = $bill['curr_meter_read_2'];
			$Pln->prev_meter_read_3 = $bill['prev_meter_read_3'];
			$Pln->curr_meter_read_3 = $bill['curr_meter_read_3'];

			$Pln->username = $username;
			$Pln->loket_name = $loket_name;
			$Pln->loket_code = $loket_code;
			$Pln->jenis_loket = $jenis_loket;
			$Pln->transaction_code = $unique_id;
			$Pln->transaction_date = $tgl_format;
			$Pln->idpelblth = $parse_private_data['subcriber_id']."-".$bill['bill_periode'];

			$total = $bill['penalty_fee']+$bill['admin_charge']+$bill['total_elec_bill'];
			$total_bayar += $total;

			$Pln->save();
		}
		$loketService = resolve('\App\Services\LoketService');
		$loketService->kurangiSaldo($total_bayar,$loket_id);

		return $unique_id;
	}

	private function startSocket($message_format, $jenis, $is_mobile, $username){

		//DUMP FOR RESPONSE PAYMENT
		// if($jenis == "payment"){
		// 	$response_payment = "22105032004182810000059950136000000000280000000001197602017030110591920170302602107451001707451021600000000000000000048233ST145S354112011455511010SYM21216526540D277D419893408414INDRY RAHMAWATI          51221022-1234567    R1  000000900000000000201501201002200000000000000028000000000000000000000000000000000000179700001816200000000000000000000000000000000";
		// 	$hasil['socket_create'] = true;
		//     $hasil['socket_create_message'] = "socket create berhasil";
		//     $hasil['socket_connect'] = true;
		//     $hasil['socket_connect_message'] = "socket connect berhasil";
		//     $hasil['response'] = $response_payment;

		//     $response_socket = $hasil;
		// }else{
		// 	$response_socket = $this->socketHelper->runSocket('POSTPAID',$message_format);
		// }
		//END DUMP

		$response_socket = $this->socketHelper->runSocket('POSTPAID',$message_format);

		if($response_socket['socket_connect']){
			$response = $response_socket['response'];
			//PARSING RESPONSE TO ARRAY
			$response = $this->isoHelper->iso_parser($response);

			$response_code = $response['iso_message'][39]['data']['response_code'];

			$response_message = "";
			switch ($jenis) {
				case 'inquery':
					$response_message = $this->getInqueryCode($response_code);
					break;
				case 'payment':
					$response_message = $this->getResponsePaymentCode($response_code);
					break;
				case 'reversal':
					$response_message = $this->getResponseReversalCode($response_code);
					break;
			}

			//APABILA TRANSAKSI BERHASIL DENGAN CODE 000
			if($response_code === "0000"){

				$private_data = $response['iso_message'][48]['data']['private_data'];
				$parse_private_data = "";

				switch ($jenis) {
					case 'inquery':
						
						$transaction_amount = $response['iso_message'][4]['data']['value_amount'];
						$audit_number = $response['iso_message'][11]['data']['partner_central_number'];
						$datetime_inquery_response = $response['iso_message'][12]['data']['datetime_local'];
						$merchant_code = $response['iso_message'][26]['data']['merchant_code'];
						$bank_code = $response['iso_message'][32]['data']['bank_code'];
						$partner_id = $response['iso_message'][33]['data']['partner_id'];
						$terminal_id = $response['iso_message'][41]['data']['terminal_id'];

						$parse_private_data = $this->parsing_inquery_response_data($private_data,$audit_number,$datetime_inquery_response,$merchant_code,$bank_code,$partner_id,$terminal_id,$transaction_amount);

						//SIMPAN LOG INQUERY RESPONSE
						//$this->simpanLogPln($parse_private_data['subcriber_id'],$message_format,json_encode($response),'INQUERY_RESPONSE');

						break;
					case 'payment':

						$datetime_inquery_response = $response['iso_message'][12]['data']['datetime_local'];
						$tgl_format = substr($datetime_inquery_response, 0,4) . "-" . substr($datetime_inquery_response, 4,2). "-". substr($datetime_inquery_response, 6,2)." ". substr($datetime_inquery_response, 8,2).":". substr($datetime_inquery_response, 10,2).":". substr($datetime_inquery_response, 12,2);

						$parse_private_data = $this->parsing_payment_response_data($private_data);

						//SIMPAN LOG PAYMENT RESPONSE
						//$this->simpanLogPln($parse_private_data['subcriber_id'],$message_format,json_encode($response),'PAYMENT_RESPONSE');

						//SIMPAN KE DATABASE PAYMENT RESPONSE
						$partner_number = $response['iso_message'][11]['data']['partner_central_number'];
						$unique_id = $this->simpanTransaksiPayment($parse_private_data,$partner_number,$tgl_format,$is_mobile,$username);

						//$this->simpanLogPln($parse_private_data['subcriber_id'],$message_format,json_encode($response),'PAYMENT_SUKSES');

						$parse_private_data['transaction_code'] = $unique_id;
						$parse_private_data['transaction_date'] = $tgl_format;
						break;
					case 'reversal':
						$parse_private_data = $this->parsing_payment_response_data($private_data);

						//SIMPAN LOG REVERSAL RESPONSE
						//$this->simpanLogPln($parse_private_data['subcriber_id'],$message_format,json_encode($response),'REVERSAL_RESPONSE');
						break;
				}

				return array(
	                'status' => true,
	                'response_code' => $response_code,
	                'message' => $response_message,
	                //'data' => $response,
	                'customer' => $parse_private_data
	            );
			}else{
				//FOR REVERSAL RESPONSE CODE 0012 MASIH PERLU DICETAK DAN SIMPAN KE DATABASE
				$private_data = $response['iso_message'][48]['data']['private_data'];
				$partner_number = $response['iso_message'][11]['data']['partner_central_number'];
				$datetime_inquery_response = $response['iso_message'][12]['data']['datetime_local'];
				$tgl_format = substr($datetime_inquery_response, 0,4) . "-" . substr($datetime_inquery_response, 4,2). "-". substr($datetime_inquery_response, 6,2)." ". substr($datetime_inquery_response, 8,2).":". substr($datetime_inquery_response, 10,2).":". substr($datetime_inquery_response, 12,2);

				$parse_private_data = $this->parsing_payment_response_data($private_data,$partner_number,$tgl_format);

				if($jenis == "reversal"){
					if($response_code == "0012"){
						$unique_id = $this->simpanTransaksiPayment($parse_private_data,$partner_number,$tgl_format,$is_mobile,$username);

						$parse_private_data['transaction_code'] = $unique_id;
						$parse_private_data['transaction_date'] = $tgl_format;
					}
				}

				if($response_code == "0016"){
					$response_message = str_replace("{IDPEL}", $parse_private_data['subcriber_id'], $response_message);
				}


				//SIMPAN LOG ISO MESSAGE UNTUK CODE BUKAN 0000
				// switch ($jenis) {
				// 	case 'inquery':
				// 		$this->simpanLogPln($parse_private_data['subcriber_id'],$message_format,json_encode($response),'INQUERY_RESPONSE_GAGAL');
				// 		break;
				// 	case 'payment':
				// 		$this->simpanLogPln($parse_private_data['subcriber_id'],$message_format,json_encode($response),'PAYMENT_RESPONSE_GAGAL');
				// 		break;
				// 	case 'reversal':
				// 		$this->simpanLogPln($parse_private_data['subcriber_id'],$message_format,json_encode($response),'REVERSAL_RESPONSE_GAGAL');
				// 		break;
				// }
				//END SIMPAN LOG ISO MESSAGE UNTUK CODE BUKAN 0000

				return array(
	                'status' => false,
	                'response_code' => $response_code,
	                'message' => $response_message,
	                'customer' => $parse_private_data
	                //'data' => $response
	            );
			}
			
		}else{
			$this->simpanLogPln("505",$message_format,$response_socket['socket_connect_message'],'ERROR_SOCKET');
			return array(
                'status' => false,
                'response_code' => "9992",
                'message' => $response_socket['socket_connect_message']
            );
		}
	}

	//PARSING PAYMENT RESPONSE DATA
	private function parsing_payment_response_data($private_data){
		$switcher_id = substr($private_data, 0, 7);
		$private_data = substr($private_data, 7);

		$subcriber_id = substr($private_data, 0, 12);
		$private_data = substr($private_data, 12);

		$bill_status = substr($private_data, 0, 1);
		$private_data = substr($private_data, 1);

		$payment_status = substr($private_data, 0, 1);
		$private_data = substr($private_data, 1);

		$outstanding_bill = substr($private_data, 0, 2);
		$private_data = substr($private_data, 2);

		$switcher_ref = substr($private_data, 0, 32);
		$private_data = substr($private_data, 32);

		$subcriber_name = substr($private_data, 0, 25);
		$private_data = substr($private_data, 25);

		$service_unit = substr($private_data, 0, 5);
		$private_data = substr($private_data, 5);

		$service_unit_phone = substr($private_data, 0, 15);
		$private_data = substr($private_data, 15);

		$subcriber_segment = substr($private_data, 0, 4);
		$private_data = substr($private_data, 4);

		$power_consumtion = substr($private_data, 0, 9);
		$private_data = substr($private_data, 9);

		$admin_charge = substr($private_data, 0, 9); //TOTAL ADMIN CHARGE CURRENTLY IS ALWAYS SET TO DEFINED BY BANK 
		$private_data = substr($private_data, 9);

		$split_bill = str_split($private_data,111);
		$array_bill = array();

		$tot_bill = 0;
		$total_added_tax = 0;
		$total_penalty_fee = 0;
		$total_admin_charge = 0;
		foreach ($split_bill as $bill) {
			$bill_data = $bill;

			$bill_periode = substr($bill_data, 0, 6);
			$bill_data = substr($bill_data, 6);

			$bill_periode = $this->getDateFormat($bill_periode);

			$due_date = substr($bill_data, 0, 8);
			$bill_data = substr($bill_data, 8);

			$meter_read_date = substr($bill_data, 0, 8);
			$bill_data = substr($bill_data, 8);

			$total_elec_bill = substr($bill_data, 0, 11);
			$bill_data = substr($bill_data, 11);

			$incentive = substr($bill_data, 0, 11);
			$bill_data = substr($bill_data, 11);

			$added_tax = substr($bill_data, 0, 10);
			$bill_data = substr($bill_data, 10);

			$penalty_fee = substr($bill_data, 0, 9);
			$bill_data = substr($bill_data, 9);

			$prev_meter_read_1 = substr($bill_data, 0, 8);
			$bill_data = substr($bill_data, 8);

			$curr_meter_read_1 = substr($bill_data, 0, 8);
			$bill_data = substr($bill_data, 8);

			$prev_meter_read_2 = substr($bill_data, 0, 8);
			$bill_data = substr($bill_data, 8);

			$curr_meter_read_2 = substr($bill_data, 0, 8);
			$bill_data = substr($bill_data, 8);

			$prev_meter_read_3 = substr($bill_data, 0, 8);
			$bill_data = substr($bill_data, 8);

			$curr_meter_read_3 = substr($bill_data, 0, 8);
			$bill_data = substr($bill_data, 8);

			$pel_bill = array(
				"subcriber_id" => $subcriber_id,
				"subcriber_name" => $subcriber_name,
				"subcriber_segment" => $subcriber_segment,
				"power_consumtion" => $power_consumtion,
				"bill_periode" => $bill_periode,
				"bill_status" => $bill_status,
				"outstanding_bill" => $outstanding_bill,
				"due_date" => $due_date,
				"meter_read_date" => $meter_read_date,
				"incentive" => $incentive,
				"total_elec_bill" => $total_elec_bill,
				"added_tax" => $added_tax,
				"penalty_fee" => $penalty_fee,
				"admin_charge" => $admin_charge,
				"prev_meter_read_1" => $prev_meter_read_1,
				"curr_meter_read_1" => $curr_meter_read_1,
				"prev_meter_read_2" => $prev_meter_read_2,
				"curr_meter_read_2" => $curr_meter_read_2,
				"prev_meter_read_3" => $prev_meter_read_3,
				"curr_meter_read_3" => $curr_meter_read_3
			);
			$tot_bill+= (int)$total_elec_bill;
			$total_added_tax += (int)$added_tax;
			$total_penalty_fee += (int)$penalty_fee;
			$total_admin_charge += (int)$admin_charge;

			array_push($array_bill, $pel_bill);
		}

		//TOTAL ADMIN CHARGE AS REGISTERED AT STARLINK (PER MONTH BILL) TOTAL = PS * MONTH BILL ADMIN CHARGE
		$total_transaksi = $tot_bill+$total_penalty_fee+$total_admin_charge;	
		$total_pln = $tot_bill+$total_penalty_fee;	

		$sisa_bill = (int)$outstanding_bill - (int)$bill_status;
		return array(
			"switcher_id" => $switcher_id,
			"subcriber_id" => $subcriber_id,
			"bill_status" => $bill_status,
			"outstanding_bill" => $outstanding_bill,
			"switcher_ref" => $switcher_ref,
			"subcriber_name" => $subcriber_name,
			"service_unit" => $service_unit,
			"service_unit_phone" => $service_unit_phone,
			"subcriber_segment" => $subcriber_segment,
			"power_consumtion" => $power_consumtion,
			"admin_charge" => $admin_charge,
			"total_admin_charge" => $total_admin_charge,
			"total_billing" => $tot_bill,
			"total_added_tax" => $total_added_tax,
			"total_penalty_fee" => $total_penalty_fee,
			"total_pln" => $total_pln, //TAGIHAN PLN + DENDA
			"total_tagihan" => $total_transaksi, //TAGIHAN PLN + DENDA + ADMIN
			"billing" => $array_bill,
			"sisa_bill" => $sisa_bill
		);
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
		return $BlTh;
		//return $mBl." ".$mTh;
	}

	//PARSING INQUERY RESPONSE DATA
	private function parsing_inquery_response_data($private_data,$audit_number,$datetime_inquery_response,$merchant_code,$bank_code,$partner_id,$terminal_id,$transaction_amount){

		$switcher_id = substr($private_data, 0, 7);
		$private_data = substr($private_data, 7);

		$subcriber_id = substr($private_data, 0, 12);
		$private_data = substr($private_data, 12);

		$bill_status = substr($private_data, 0, 1);
		$private_data = substr($private_data, 1);

		$outstanding_bill = substr($private_data, 0, 2);
		$private_data = substr($private_data, 2);

		$switcher_ref = substr($private_data, 0, 32);
		$private_data = substr($private_data, 32);

		$subcriber_name = substr($private_data, 0, 25);
		$private_data = substr($private_data, 25);

		$service_unit = substr($private_data, 0, 5);
		$private_data = substr($private_data, 5);

		$service_unit_phone = substr($private_data, 0, 15);
		$private_data = substr($private_data, 15);

		$subcriber_segment = substr($private_data, 0, 4);
		$private_data = substr($private_data, 4);

		$power_consumtion = substr($private_data, 0, 9);
		$private_data = substr($private_data, 9);

		$admin_charge = substr($private_data, 0, 9); //TOTAL ADMIN CHARGE CURRENTLY IS ALWAYS SET TO DEFINED BY BANK 
		$private_data = substr($private_data, 9);

		$split_bill = str_split($private_data,111);
		$array_bill = array();

		$tot_bill = 0;
		$total_added_tax = 0;
		$total_penalty_fee = 0;
		$periode = "";
		$jml = 1;
		$tot_jml = count($split_bill);
		foreach ($split_bill as $bill) {
			$bill_data = $bill;

			$bill_periode = substr($bill_data, 0, 6);
			$bill_data = substr($bill_data, 6);

			$bill_periode = $this->getDateFormat($bill_periode);

			if($jml == 1){
				$periode .= $bill_periode;
			}
			if($jml == $tot_jml && $tot_jml > 1){
				$periode .= " - ".$bill_periode;
			}

			$due_date = substr($bill_data, 0, 8);
			$bill_data = substr($bill_data, 8);

			$meter_read_date = substr($bill_data, 0, 8);
			$bill_data = substr($bill_data, 8);

			$total_elec_bill = substr($bill_data, 0, 11);
			$bill_data = substr($bill_data, 11);

			$incentive = substr($bill_data, 0, 11);
			$bill_data = substr($bill_data, 11);

			$added_tax = substr($bill_data, 0, 10);
			$bill_data = substr($bill_data, 10);

			$penalty_fee = substr($bill_data, 0, 9);
			$bill_data = substr($bill_data, 9);

			$prev_meter_read_1 = substr($bill_data, 0, 8);
			$bill_data = substr($bill_data, 8);

			$curr_meter_read_1 = substr($bill_data, 0, 8);
			$bill_data = substr($bill_data, 8);

			$prev_meter_read_2 = substr($bill_data, 0, 8);
			$bill_data = substr($bill_data, 8);

			$curr_meter_read_2 = substr($bill_data, 0, 8);
			$bill_data = substr($bill_data, 8);

			$prev_meter_read_3 = substr($bill_data, 0, 8);
			$bill_data = substr($bill_data, 8);

			$curr_meter_read_3 = substr($bill_data, 0, 8);
			$bill_data = substr($bill_data, 8);

			$pel_bill = array(
				"subcriber_id" => $subcriber_id,
				"subcriber_name" => $subcriber_name,
				"subcriber_segment" => $subcriber_segment,
				"power_consumtion" => $power_consumtion,
				"bill_periode" => $bill_periode,
				"due_date" => $due_date,
				"meter_read_date" => $meter_read_date,
				"incentive" => $incentive,
				"total_elec_bill" => $total_elec_bill,
				"added_tax" => $added_tax,
				"penalty_fee" => $penalty_fee,
				"admin_charge" => $admin_charge,
				"prev_meter_read_1" => $prev_meter_read_1,
				"curr_meter_read_1" => $curr_meter_read_1,
				"prev_meter_read_2" => $prev_meter_read_2,
				"curr_meter_read_2" => $curr_meter_read_2,
				"prev_meter_read_3" => $prev_meter_read_3,
				"curr_meter_read_3" => $curr_meter_read_3
			);
			$tot_bill+= (int)$total_elec_bill;
			$total_added_tax += (int)$added_tax;
			$total_penalty_fee += (int)$penalty_fee;
			$jml++;

			array_push($array_bill, $pel_bill);
		}

		//TOTAL ADMIN CHARGE AS REGISTERED AT STARLINK (PER MONTH BILL) TOTAL = PS * MONTH BILL ADMIN CHARGE
		$total_admin_charge = (int)$admin_charge*$bill_status; 
		$total_admin_charge = str_pad($total_admin_charge, 9, "0", STR_PAD_LEFT);

		$total_transaksi = $tot_bill+$total_penalty_fee+$total_admin_charge;	
		$total_pln = $tot_bill+$total_penalty_fee;	

		//TOTAL TRANSAKSI SAMAKAN DENGAN TRANSACTION AMOUNT DI INQUERY
		$total_payment = $tot_bill+$total_penalty_fee;
		$transaction_amount = "3600".str_pad($total_payment, 12, "0", STR_PAD_LEFT);

		$bank_code = "07".$bank_code;
		$partner_id = "07".$partner_id;

		//PAYMENT MESSAGE
		$message_payment1 = "22005030004180810000".$this->postpaid_code.$transaction_amount.$audit_number.$datetime_inquery_response.$merchant_code.$bank_code.$partner_id.$terminal_id;

		//PRIVATE ADDITIONAL DATA 
		$message_payment2 = $switcher_id.$subcriber_id.$bill_status.$bill_status.$outstanding_bill.$switcher_ref.$subcriber_name.$service_unit.$service_unit_phone.$subcriber_segment.$power_consumtion.$admin_charge.$private_data;

		//PANJANG PRIVATE ADDITIONAL DATA
		$payment_data_len = (string)strlen($message_payment2);

		//REVERSAL MESSAGE
		$message_reversal = "24005030004180810100".$this->postpaid_code.$transaction_amount.$audit_number.$datetime_inquery_response.$merchant_code.$bank_code.$partner_id.$terminal_id;

		//DATA ELEMENT 56 FOR REVERSAL MESSAGE
		$original_data_element = "372200".$audit_number.$datetime_inquery_response.$bank_code;

		//PAYMENT ISO MESSAGE
		$payment_message = $message_payment1.$payment_data_len.$message_payment2;
		//REVERSAL ISO MESSAGE
		$reversal_message = $message_reversal.$payment_data_len.$message_payment2.$original_data_element;

		$sisa_bill = (int)$outstanding_bill - (int)$bill_status;

		return array(
			"switcher_id" => $switcher_id,
			"subcriber_id" => $subcriber_id,
			"bill_status" => $bill_status,
			"outstanding_bill" => $outstanding_bill,
			"switcher_ref" => $switcher_ref,
			"subcriber_name" => $subcriber_name,
			"periode" => $periode,
			"service_unit" => $service_unit,
			"service_unit_phone" => $service_unit_phone,
			"subcriber_segment" => $subcriber_segment,
			"power_consumtion" => $power_consumtion,
			"admin_charge" => $admin_charge,
			"total_admin_charge" => $total_admin_charge,
			"total_billing" => $tot_bill,
			"total_added_tax" => $total_added_tax,
			"total_penalty_fee" => $total_penalty_fee,
			"total_pln" => $total_pln, //TAGIHAN PLN + DENDA
			"total_tagihan" => $total_transaksi, //TAGIHAN PLN + DENDA + ADMIN
			"billing" => $array_bill,
			"payment_message" => $payment_message,
			"reversal_message" => $reversal_message,
			"sisa_bill" => $sisa_bill
		);
	}

	private function getInqueryCode($response_code){
		$message = "";
		switch ($response_code) {
			case '0000':
				$message = "SUCCSSESFULL";
				break;
			case '0005':
				$message = "ERROR OTHER";
				break;
			case '0006':
				$message = "BLOCK PARTNER ID";
				break;
			case '0008':
				$message = "INVAILID ACCESS TIME";
				break;
			case '0011':
				$message = "NEED TO SIGN ON";
				break;
			case '0014':
				$message = "IDPEL YANG ANDA MASUKKAN SALAH, MOHON TELITI KEMBALI";
				break;
			case '0030':
				$message = "INVAILID MESSAGE";
				break;
			case '0031':
				$message = "UNERGISTERED BANK CODE";
				break;
			case '0032':
				$message = "UNERGISTERED PARTNER ID";
				break;
			case '0033':
				$message = "UNREGISTERED PRODUCT";
				break;
			case '0068':
				$message = "TIME OUT";
				break;
			case '0088':
				$message = "TAGIHAN SUDAH TERBAYAR";
				break;
			case '0089':
				$BlTh = $this->getDateFormat(date('Ym'));
				$message = "TAGIHAN BULAN ".$BlTh." BELUM TERSEDIA";
				break;
			case '0090':
				$message = "CUT OFF IN PROGRESS";
				break;
			case '0016':
				$message = "KONSUMEN {IDPEL} DIBLOKIR HUBUNGI PLN ";
				break;
			
			default:
				$message = "NO ERROR CODE MATCH";
				break;
		}
		return $message;
	}

	private function getResponsePaymentCode($response_code){
		$message = "";
		switch ($response_code) {
			case '0000':
				$message = "PAYMENT BERHASIL DILAKUKAN.";
				break;
			case '0005':
			    $message = 'ERROR OTHER';
			    break;
			case '0006':
			    $message = 'BLOCKED PARTNER ID ';
			    break;
			case '0011':
			    $message = 'NEED TO SIGN ON ';
			    break;
			case '0013':
			    $message = 'INVAILID TRANSACTION AMOUNT ';
			    break;
			case '0014':
			    $message = 'UNKNOWN SUBSCRIBER ';
			    break;
			case '0030':
			    $message = 'INVAILID MESSAGE ';
			    break;
			case '0031':
			    $message = 'UNREGISTERED BANK CODE ';
			    break;
			case '0032':
			    $message = 'UNREGISTERED PARTNER ID ';
			    break;
			case '0033':
			    $message = 'UNREGISTERED PRODUCT ';
			    break;
			case '0045':
			    $message = 'INVAILID ADMIN CHARGES ';
			    break;
			case '0046':
			    $message = 'BALANCE IS NOT ENOUGH ';
			    break;
			case '0068':
			    $message = 'TIME OUT ';
			    break;
			case '0088':
			    $message = 'TAGIHAN SUDAH TERBAYAR';
			    break;
			case '0090':
			    $message = 'CUT OFF IN PROGRESS ';
			    break;
			case '0092':
			    $message = 'SWREF IS NOT AVAILABLE ';
			    break;
			case '0093':
			    $message = 'INVAILID PARTNER CENTRAL AUDIT NUMBER ';
			    break;
			case '0097':
			    $message = 'SWREF / BANK CODE NOT IDENTICAL WITH INQUIRY ';
			    break;
			case '0098':
			    $message = 'PLNREF IS NOT VALID ';
			    break;
			
			default:
				$message = "NO ERROR CODE MATCH";
				break;
		}
		return $message;
	}

	private function getResponseReversalCode($response_code){
		$message = "";
		switch ($response_code) {
			case '0000':
				$message = "TRANSAKSI GAGAL.";
				break;
			case '0005':
			    $message = 'ERROR OTHER';
			    break;
			case '0006':
			    $message = 'BLOCKED PARTNER ID ';
			    break;
			case '0011':
			    $message = 'NEED TO SIGN ON ';
			    break;
			case '0012':
			    $message = 'REVERSAL GAGAL.';
			    break;
			case '0013':
			    $message = 'INVAILID TRANSACTION AMOUNT ';
			    break;
			case '0014':
			    $message = 'UNKNOWN SUBSCRIBER ';
			    break;
			case '0030':
			    $message = 'INVAILID MESSAGE ';
			    break;
			case '0031':
			    $message = 'UNREGISTERED BANK CODE ';
			    break;
			case '0032':
			    $message = 'UNREGISTERED PARTNER ID ';
			    break;
			case '0033':
			    $message = 'UNREGISTERED PRODUCT ';
			    break;
			case '0045':
			    $message = 'INVAILID ADMIN CHARGES ';
			    break;
			case '0046':
			    $message = 'BALANCE IS NOT ENOUGH ';
			    break;
			case '0063':
			    $message = 'REVERSAL GAGAL';
			    break;
			case '0068':
			    $message = 'TIME OUT ';
			    break;
			case '0088':
			    $message = 'TAGIHAN SUDAH TERBAYAR';
			    break;
			case '0090':
			    $message = 'CUT OFF IN PROGRESS ';
			    break;
			case '0092':
			    $message = 'INVALID SWREF ';
			    break;
			case '0093':
			    $message = 'INVAILID PARTNER CENTRAL AUDIT NUMBER ';
			    break;
			case '0094':
			    $message = 'REVERSAL GAGAL';
			    break;
			case '0097':
			    $message = 'SWITCHING / BANK CODE NOT IDENTICAL WITH PAYMENT ';
			    break;
			case '0098':
			    $message = 'PLNREF IS NOT VALID ';
			    break;
			
			default:
				$message = "NO ERROR CODE MATCH";
				break;
		}
		return $message;
	}

}