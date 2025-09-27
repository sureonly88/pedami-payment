<?php

namespace App\PlnServices;

use Illuminate\Support\Facades\Config;
use App\PlnServices\ISOService;
use App\PlnServices\SocketService;
use Illuminate\Support\Facades\Auth;
use App\Models\mLoket;
use App\Models\PlnLog;
use App\Models\AuditNumber;
use App\Models\LogSistem;
use App\Models\PlnPrepaidTransaksi;
use App\Models\ManualPrepaid;
use App\User;
use SimpleXMLElement;
use App\Http\Controllers\Helpers;

class PrePaidService
{

	protected $isoHelper;
	protected $socketHelper;

	protected $postpaid_code = "0599502";

    public function __construct(ISOService $isoHelper, SocketService $socketHelper)
    {
    	$this->isoHelper = $isoHelper;
    	$this->socketHelper = $socketHelper;
    }

    public function prosesInquery($idpel,$flag,$is_mobile,$username,$token){

    	try{

    		$response_xml = Helpers::sent_pln_get("https://".env('PLN_IPADDR_NEW','').":".env('PLN_PORT_NEW','')."/srvSys/Transactional?q=2100!99502!0!".env('PLN_ID_MITRA','')."!".env('PLN_USER_MITRA','')."!".$idpel."!".$token, env('PLN_AUTH_NEW',''));

			$response = new SimpleXMLElement(htmlspecialchars_decode($response_xml['response']));
			$response = json_encode($response);

			$this->simpanLogPln($idpel,"",$response,'INQUERY_PREPAID');

            $response = json_decode($response, true);

			if($response['data']['rc'] == "000"){
				return array(
	                'status' => true,
	                'response_code' => $response['data']['rc'],
	                'response' => [],
	                'message' => $response['data']['desc'],
	                'customer' => array(
	                	'data' => array(
							'switcher_id' => '',
							'material_number' => $response['data']['serial_meter'],
							'subscriber_id' => $response['data']['subscriber_id'],
							'flag' => '',
							'pln_ref_number' => $response['data']['plnreff'],
							'switcher_ref_number' => $response['data']['swreff'],
							'subscriber_name' => $response['data']['name'],
							'subscriber_segment' => $response['data']['segment'],
							'power_categori' => $response['data']['power'],
							'minor_unit_admin_charge' => '0',
							'admin_charge' => $response['data']['admin'],
							'distribution_code' => '',
							'service_unit' => '',
							'service_phone_unit' => '',
							'max_kwh' => '',
							'total_repeat' => '',
							'purchase_unsold' => ''
						),
	                	'purchase_message' => "2200!99502!0!".env('PLN_ID_MITRA','')."!".env('PLN_USER_MITRA','')."!".$idpel."!".$token."!".$response['data']['trxid'],
	                	'reversal_message' => ""
	                )			                
	            );

			}else{
				return array(
	                'status' => false,
	                'response_code' => $response['data']['rc'],
	                'message' => $response['data']['desc']
	            );
			}

    		// $inqueryMessage = $this->getInqueryMessage($idpel,$flag,$is_mobile,$username);
    		// $response = $this->prosesSocket($inqueryMessage,"inquery",0,$is_mobile,$username);

    		// $response['message'] = str_replace("{IDPEL}", $idpel, $response['message']);

    		//return $response;

    	}catch (\Exception $e) {
    		$err_message = explode("\r\n",$e->getMessage());
    		$this->simpanLogSistem(json_encode($err_message));
	        return array(
                'status' => false,
                'response_code' => "9991",
                'message' => "TERJADI KESALAHAN, TRANSAKSI GAGAL."
            );
	    }
    }

    private function simpanLogSistem($Log){

		$mLog = new LogSistem();
		$mLog->log = $Log;
		$mLog->save();
	}

    private function simpanTransaksiPayment($parse_private_data,$partner_number,$tgl_format,$rupiah_token,$is_mobile,$username){
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

		$Pln = new PlnPrepaidTransaksi();
		$Pln->subscriber_id = $parse_private_data['subscriber_id'];
		$Pln->material_number = $parse_private_data['material_number'];
		$Pln->subscriber_name = $parse_private_data['subscriber_name'];
		$Pln->subscriber_segment = $parse_private_data['subscriber_segment'];
		$Pln->power_categori = $parse_private_data['power_categori'];
		$Pln->switcher_ref_number = $parse_private_data['switcher_ref_number'];
		$Pln->pln_ref_number = $parse_private_data['pln_ref_number'];
		$Pln->token_number = $parse_private_data['token_number'];
		$Pln->trace_audit_number = $partner_number;

		$Pln->vending_recieve_number = $parse_private_data['vending_recieve_number'];
		$Pln->max_kwh = $parse_private_data['max_kwh'];
		$Pln->purchase_kwh = $parse_private_data['purchase_kwh'];
		$Pln->info_text = $parse_private_data['info_text'];
		$Pln->stump_duty = $parse_private_data['stump_duty'];
		$Pln->ligthingtax = $parse_private_data['ligthingtax'];
		$Pln->cust_payable = $parse_private_data['cust_payable'];
		$Pln->admin_charge = $parse_private_data['admin_charge'];
		$Pln->addtax = $parse_private_data['addtax'];
		$Pln->power_purchase = $parse_private_data['power_purchase'];
		$Pln->rupiah_token = $rupiah_token;

		$Pln->username = $username;
		$Pln->loket_name = $loket_name;
		$Pln->loket_code = $loket_code;
		$Pln->jenis_loket = $jenis_loket;
		$Pln->transaction_code = $unique_id;
		$Pln->transaction_date = $tgl_format;

		$total = $parse_private_data['stump_duty'] + $parse_private_data['ligthingtax'] + $parse_private_data['cust_payable'] + $parse_private_data['admin_charge'] + $parse_private_data['addtax'] + $parse_private_data['power_purchase'];

		$Pln->save();

		$loketService = resolve('\App\Services\LoketService');
		$loketService->kurangiSaldo($total,$loket_id);

		return $unique_id;
	}

    private function prosesSocket($message,$jenis_transaksi,$rupiah_token,$is_mobile,$username){

    // 	switch ($jenis_transaksi) {
    // 		case 'inquery':
    // 			$response_socket = $this->socketHelper->runSocket('PREPAID',$message);
    // 			break;
    // 		case 'reversal':
    // 			$response_socket = "223050300041828100060599502360000000002000000000012389520170411121419602107451001707451201700000000000000000048241ST145S301416668900533416668900056282114873186500U426E95624018820SYM21216E59D50653C383546347678556541505IIN MAEMUNAH             R1  00000130002000000000020000000000200000000002000015000020000500000200000135000020000002231185119820551293505940285351128022-1234567    001200064Rincian tagihan dapat diakses di www.pln.co.id atau PLN terdekat";
				// $hasil['socket_create'] = true;
			 //    $hasil['socket_create_message'] = "socket create berhasil";
			 //    $hasil['socket_connect'] = true;
			 //    $hasil['socket_connect_message'] = "socket connect berhasil";
			 //    $hasil['response'] = $response_socket;

			 //    $response_socket = $hasil;
    // 			break;
    // 		case 'purchase':
    // // 			$response_socket = "22105032004182810006059950236000000001000000000001238852017041112074520170412602107451001707451201700000000000000000048241ST145S301428800700511288007004023163848726U102825174687906344020SYM2121674574878107B9832E9B739506993732IJUR                       R100000090002000000000020000000000200000000002000015000020000500000200000935000020000015455067438951580668869910395151128022-1234567    00120000000100000064Rincian tagihan dapat diakses di www.pln.co.id atau PLN terdekat";
				// // $hasil['socket_create'] = true;
			 // //    $hasil['socket_create_message'] = "socket create berhasil";
			 // //    $hasil['socket_connect'] = true;
			 // //    $hasil['socket_connect_message'] = "socket connect berhasil";
			 // //    $hasil['response'] = $response_socket;

			 // //    $response_socket = $hasil;
    // 			//$response_socket = $this->socketHelper->runSocket('PREPAID',$message);
    // 			break;
    // 	}

    	$response_socket = $this->socketHelper->runSocket('PREPAID',$message);
    	

		if($response_socket['socket_connect']){
			$response = $response_socket['response'];
			$response = $this->isoHelper->iso_parser($response);

			$response_code = $response['iso_message'][39]['data']['response_code'];
			$response_message = $this->getResponseMessage($response_code);

			if($response_code == "0000"){
				$privateData1 = $response['iso_message'][48]['data']['private_data'];
				$privateData2 = $response['iso_message'][62]['data']['private_data'];

				//dd($response);

				switch ($jenis_transaksi) {
					case 'inquery':
						$PrimaryAccountNumber = $response['iso_message'][2]['data']['primary_account_number'];
						$TransactionAmount = "3600"."{value_amount}"; //Value Amount 12 Digit.
						$traceAuditNumber = $response['iso_message'][11]['data']['partner_central_number'];
						$LocalDateTime = $response['iso_message'][12]['data']['datetime_local'];
						$merchantCode = $response['iso_message'][26]['data']['merchant_code']; 
						$bankCode = "07".$response['iso_message'][32]['data']['bank_code'];  
						$partnerId = "07".$response['iso_message'][33]['data']['partner_id'];  
						$terminalId = $response['iso_message'][41]['data']['terminal_id'];

						$privateData = $this->getResponseInquery($privateData1,$privateData2);

						$privateData1Len = str_pad(strlen($privateData1)+1, 3, "0", STR_PAD_LEFT); //Tambah bit buat buying option
						$privateData2Len = str_pad(strlen($privateData2), 3, "0", STR_PAD_LEFT);

						$paymentMessage = "22005030004180810004"."05".$PrimaryAccountNumber.$TransactionAmount.$traceAuditNumber.$LocalDateTime.$merchantCode.$bankCode.$partnerId.$terminalId.$privateData1Len.$privateData1."{buying_option}".$privateData2Len.$privateData2;

						$switcher_id = $privateData['switcher_id'];
						$material_number = $privateData['material_number'];
						$subscriber_id = $privateData['subscriber_id'];
						$flag = $privateData['flag'];
						$pln_ref_number = $privateData['pln_ref_number'];
						$switcher_ref_number = $privateData['switcher_ref_number'];

						$privateDataReversal1 = $switcher_id.$material_number.$subscriber_id.$flag.$pln_ref_number.$switcher_ref_number;
						$privateDataReversal2 = $privateData2;

						$privateDataReversal1Len = str_pad(strlen($privateDataReversal1), 3, "0", STR_PAD_LEFT);
						$privateDataReversal2Len = str_pad(strlen($privateDataReversal2), 3, "0", STR_PAD_LEFT);

						$reversalMessage = "22205030004180810004"."05".$PrimaryAccountNumber.$TransactionAmount.$traceAuditNumber.$LocalDateTime.$merchantCode.$bankCode.$partnerId.$terminalId.$privateDataReversal1Len.$privateDataReversal1.$privateDataReversal2Len.$privateDataReversal2;

						return array(
			                'status' => true,
			                'response_code' => $response_code,
			                'response' => $response,
			                'message' => $response_message,
			                'customer' => array(
			                	'data' => $privateData,
			                	'purchase_message' => $paymentMessage,
			                	'reversal_message' => $reversalMessage
			                )			                
			            );

						break;
					
					case 'purchase':

						$datetime_inquery_response = $response['iso_message'][12]['data']['datetime_local'];
						$tgl_format = substr($datetime_inquery_response, 0,4) . "-" . substr($datetime_inquery_response, 4,2). "-". substr($datetime_inquery_response, 6,2)." ". substr($datetime_inquery_response, 8,2).":". substr($datetime_inquery_response, 10,2).":". substr($datetime_inquery_response, 12,2);

						$info_text = $response['iso_message'][63]['data']['private_data'];
						$privateData = $this->getResponsePurchase($privateData1,$privateData2,$info_text,$rupiah_token);
						$partner_number = $response['iso_message'][11]['data']['partner_central_number'];

						$unique_id = $this->simpanTransaksiPayment($privateData,$partner_number,$tgl_format,$rupiah_token,$is_mobile,$username);

						$privateData['transaction_code'] = $unique_id;
						$privateData['transaction_date'] = $tgl_format;

						return array(
			                'status' => true,
			                'response_code' => $response_code,
			                'message' => $response_message,
			                'customer' => $privateData
			            );

						break;

					case 'reversal':

						$datetime_inquery_response = $response['iso_message'][12]['data']['datetime_local'];
						$tgl_format = substr($datetime_inquery_response, 0,4) . "-" . substr($datetime_inquery_response, 4,2). "-". substr($datetime_inquery_response, 6,2)." ". substr($datetime_inquery_response, 8,2).":". substr($datetime_inquery_response, 10,2).":". substr($datetime_inquery_response, 12,2);

						$info_text = $response['iso_message'][63]['data']['private_data'];
						$privateData = $this->getResponsePurchase($privateData1,$privateData2,$info_text,$rupiah_token);
						$partner_number = $response['iso_message'][11]['data']['partner_central_number'];

						$unique_id = $this->simpanTransaksiPayment($privateData,$partner_number,$tgl_format,$rupiah_token,$is_mobile,$username);

						$privateData['transaction_code'] = $unique_id;
						$privateData['transaction_date'] = $tgl_format;

						return array(
			                'status' => true,
			                'response_code' => $response_code,
			                'message' => $response_message,
			                'customer' => $privateData
			            );

						break;
				}

			}else{

				$response = $response_socket['response'];
				$response = $this->isoHelper->iso_parser($response);

				$privateData1 = $response['iso_message'][48]['data']['private_data'];

				$privateData = $this->getResponseManual($privateData1);
				$mtiRequest = substr($message, 0, 4);

				if($jenis_transaksi == 'reversal'){
					if($mtiRequest == "2221"){
						//SIMPAN KE MANUAL ADVISE
						$manual = new ManualPrepaid();
						$manual->material_number = $privateData['material_number'];
						$manual->subscriber_id  = $privateData['subscriber_id'];
						$manual->subscriber_name = $privateData['subscriber_name'];
						$manual->rupiah_token = $rupiah_token;
						//$manual->subscriber_name = "-";
						$manual->advise_message = $message;
						$manual->status = false;
						$manual->save();
					}
					
				}

				return array(
	                'status' => false,
	                'response_code' => $response_code,
	                //'response' => $response,
	                'message' => $response_message
	            );
			}

		}else{
			return array(
                'status' => false,
                'response_code' => "9992",
                'message' => $response_socket['socket_connect_message']
            );
		}
    }

    public function prosesPurchase($idpel,$payment_message,$rupiah_token,$buying_option,$is_mobile,$username){
    	try{

    		$loketService = resolve('\App\Services\LoketService');
			if($is_mobile){
				$loket_id = User::where('username',$username)->select('loket_id')->first()->loket_id;
				$saldo = $loketService->getSaldo($loket_id);
			}else{
				$loket_id = Auth::user()->loket_id;
				$saldo = $loketService->getSaldo($loket_id);
			}

			if($saldo < $rupiah_token){
				return array(
	                'status' => false,
	                'response_code' => "9990",
	                'message' => "SALDO TIDAK CUKUP UNTUK PEMBAYARAN"
	            );
			}

    		// $TransactionAmount = str_pad($rupiah_token, 12, "0", STR_PAD_LEFT);

    		// $payment_message = str_replace("{value_amount}", $TransactionAmount, $payment_message);
    		// $payment_message = str_replace("{buying_option}", $buying_option, $payment_message);

    		// $response = $this->isoHelper->iso_parser($payment_message);

    		// //dd($response);

    		// $response = $this->prosesSocket($payment_message,"purchase",$rupiah_token,$is_mobile,$username);
    		// return $response;

    		// $response_xml = Helpers::sent_pln_get("https://".env('PLN_IPADDR_NEW','').":".env('PLN_PORT_NEW','')."/ws_payplus/Transactional?q=2100!99501!0!".env('PLN_ID_MITRA','')."!".env('PLN_USER_MITRA','')."!".$nomor_pelanggan, env('PLN_AUTH_NEW',''));

			$response_xml = Helpers::sent_pln_get("https://".env('PLN_IPADDR_NEW','').":".env('PLN_PORT_NEW','')."/srvSys/Transactional?q=".$payment_message, env('PLN_AUTH_NEW',''));

			$response = new SimpleXMLElement(htmlspecialchars_decode($response_xml['response']));
			$response = json_encode($response);

			$this->simpanLogPln($idpel,"",$response,'PAYMENT_PREPAID');

            $response = json_decode($response, true);

            if($response['data']['rc'] == "000"){
            	$dataPayment = array(
					'switcher_id' => "",
					'material_number' => $response['data']['serial_meter'],
					'subscriber_id' => $response['data']['subscriber_id'],
					'flag' => "",
					'pln_ref_number' => $response['data']['plnreff'],
					'switcher_ref_number' => $response['data']['swreff'],
					'vending_recieve_number' => "",
					'subscriber_name' => $response['data']['name'],
					'subscriber_segment' => $response['data']['segment'],
					'power_categori' => $response['data']['power'],
					'buying_option' => "",
					'minor_unit_admin_charge' => "",
					'admin' => $response['data']['admin'],
					'admin_charge' => $response['data']['admin'],
					'minor_unit_stump' => "",
					'stampduty' => $response['data']['stampduty'],
					'stump_duty' => $response['data']['stampduty'],
					'minor_unit_addtax' => "",
					'addtax' => $response['data']['valueaddedtax'],
					'valueaddedtax' => $response['data']['valueaddedtax'],
					'minor_unit_ligthingtax' => "",
					'lightingtax' => $response['data']['lightingtax'],
					'ligthingtax' => $response['data']['lightingtax'],
					'minor_unit_cust_payable' => "",
					'cust_payable' => $response['data']['installment'],
					'minor_unit_power_purchase' => "",
					'power_purchase' => $response['data']['power_purchase'],
					'minor_unit_purchase_kwh' => "",
					'purchase_kwh' => $response['data']['kwh'],
					'rupiah_token' => $response['data']['denom'],
					'token_number' => $response['data']['token'],
					'distribution_code' => "",
					'service_unit' => "",
					'service_phone_unit' => "",
					'max_kwh' => "",
					'total_repeat' => "",
					'purchase_unsold' => "",
					'info_text' => $response['data']['msg'],
				);

				$time = $response['data']['time'];

				$tgl_format = substr($time, 0,4) . "-" . substr($time, 4,2). "-". substr($time, 6,2)." ". substr($time, 8,2).":". substr($time, 10,2).":". substr($time, 12,2);

				$unique_id = $this->simpanTransaksiPayment($dataPayment,"",$tgl_format,$rupiah_token,$is_mobile,$username);

				$dataPayment['transaction_code'] = $unique_id;
				$dataPayment['transaction_date'] = $tgl_format;

				return array(
	                'status' => true,
	                'response_code' => $response['data']['rc'],
	                'message' => $response['data']['desc'],
	                'customer' => $dataPayment
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
    		$this->simpanLogSistem(json_encode($err_message));
	        return array(
                'status' => false,
                'response_code' => "9993",
                'message' => "TERJADI KESALAHAN, TRANSAKSI GAGAL."
            );
	    }
    }

    public function prosesReversal($idpel,$reversal_message,$rupiah_token,$is_mobile,$username){
    	try{

    		$loketService = resolve('\App\Services\LoketService');

    		if($is_mobile){
				$loket_id = User::where('username',$username)->select('loket_id')->first()->loket_id;
				$saldo = $loketService->getSaldo($loket_id);
			}else{
				$loket_id = Auth::user()->loket_id;
				$saldo = $loketService->getSaldo($loket_id);
			}

			if($saldo < $rupiah_token){
				return array(
	                'status' => false,
	                'response_code' => "9990",
	                'message' => "SALDO TIDAK CUKUP UNTUK PEMBAYARAN"
	            );
			}

    		$TransactionAmount = str_pad($rupiah_token, 12, "0", STR_PAD_LEFT);

    		$reversal_message = str_replace("{value_amount}", $TransactionAmount, $reversal_message);

    		$response = $this->prosesSocket($reversal_message,"reversal",$rupiah_token,$is_mobile,$username);
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

    private function getResponseManual($privateData1){
    	$switcher_id = substr($privateData1, 0, 7);
		$privateData1 = substr($privateData1, 7);

		$material_number = substr($privateData1, 0, 11);
		$privateData1 = substr($privateData1, 11);

		$subscriber_id = substr($privateData1, 0, 12);
		$privateData1 = substr($privateData1, 12);

		$flag = substr($privateData1, 0, 1);
		$privateData1 = substr($privateData1, 1);

		$pln_ref_number = substr($privateData1, 0, 32);
		$privateData1 = substr($privateData1, 32);

		$switcher_ref_number = substr($privateData1, 0, 32);
		$privateData1 = substr($privateData1, 32);

		$vending_recieve_number = substr($privateData1, 0, 8);
		$privateData1 = substr($privateData1, 8);

		$subscriber_name = substr($privateData1, 0, 25);
		$privateData1 = substr($privateData1, 25);

		return array(
			'switcher_id' => $switcher_id,
			'material_number' => $material_number,
			'subscriber_id' => $subscriber_id,
			'flag' => $flag,
			'pln_ref_number' => $pln_ref_number,
			'switcher_ref_number' => $switcher_ref_number,
			'vending_recieve_number' => $vending_recieve_number,
			'subscriber_name' => $subscriber_name
		);
    }

    private function getResponsePurchase($privateData1,$privateData2,$info_text,$rupiah_token){
    	$switcher_id = substr($privateData1, 0, 7);
		$privateData1 = substr($privateData1, 7);

		$material_number = substr($privateData1, 0, 11);
		$privateData1 = substr($privateData1, 11);

		$subscriber_id = substr($privateData1, 0, 12);
		$privateData1 = substr($privateData1, 12);

		$flag = substr($privateData1, 0, 1);
		$privateData1 = substr($privateData1, 1);

		$pln_ref_number = substr($privateData1, 0, 32);
		$privateData1 = substr($privateData1, 32);

		$switcher_ref_number = substr($privateData1, 0, 32);
		$privateData1 = substr($privateData1, 32);

		$vending_recieve_number = substr($privateData1, 0, 8);
		$privateData1 = substr($privateData1, 8);

		$subscriber_name = substr($privateData1, 0, 25);
		$privateData1 = substr($privateData1, 25);

		$subscriber_segment = substr($privateData1, 0, 4);
		$privateData1 = substr($privateData1, 4);

		$power_categori = substr($privateData1, 0, 9);
		$privateData1 = substr($privateData1, 9);

		$buying_option = substr($privateData1, 0, 1);
		$privateData1 = substr($privateData1, 1);

		$minor_unit_admin_charge = substr($privateData1, 0, 1);
		$privateData1 = substr($privateData1, 1);

		$admin_charge = substr($privateData1, 0, 10);
		$privateData1 = substr($privateData1, 10);

		$minor_unit_stump = substr($privateData1, 0, 1);
		$privateData1 = substr($privateData1, 1);

		$stump_duty = substr($privateData1, 0, 10);
		$privateData1 = substr($privateData1, 10);

		$minor_unit_addtax = substr($privateData1, 0, 1);
		$privateData1 = substr($privateData1, 1);

		$addtax = substr($privateData1, 0, 10);
		$privateData1 = substr($privateData1, 10);

		$minor_unit_ligthingtax = substr($privateData1, 0, 1);
		$privateData1 = substr($privateData1, 1);

		$ligthingtax = substr($privateData1, 0, 10);
		$privateData1 = substr($privateData1, 10);

		$minor_unit_cust_payable = substr($privateData1, 0, 1);
		$privateData1 = substr($privateData1, 1);

		$cust_payable = substr($privateData1, 0, 10);
		$privateData1 = substr($privateData1, 10);

		$minor_unit_power_purchase = substr($privateData1, 0, 1);
		$privateData1 = substr($privateData1, 1);

		$power_purchase = substr($privateData1, 0, 12);
		$privateData1 = substr($privateData1, 12);

		$minor_unit_purchase_kwh = substr($privateData1, 0, 1);
		$privateData1 = substr($privateData1, 1);

		$purchase_kwh = substr($privateData1, 0, 10);
		$privateData1 = substr($privateData1, 10);

		$token_number = substr($privateData1, 0, 20);
		$token_number = str_split($token_number,4);

		$space_token = "";
		foreach ($token_number as $token) {
			$space_token .= $token." ";
		}

		$privateData1 = substr($privateData1, 20);

		//PRIVATE DATA 2
		$distribution_code = substr($privateData2, 0, 2);
		$privateData2 = substr($privateData2, 2); 

		$service_unit = substr($privateData2, 0, 5);
		$privateData2 = substr($privateData2, 5); 

		$service_phone_unit = substr($privateData2, 0, 15);
		$privateData2 = substr($privateData2, 15); 

		$max_kwh = substr($privateData2, 0, 5);
		$privateData2 = substr($privateData2, 5); 

		$total_repeat = substr($privateData2, 0, 1);
		$privateData2 = substr($privateData2, 1); 

		$array_unsold = "";

		if($total_repeat > 0){
			$split_unsold = str_split($privateData2,11);
			foreach ($split_unsold as $unsold) {
				$array_unsold .= (int)$unsold . ",";
			}
			$array_unsold = substr($array_unsold, 0, strlen($array_unsold)-1);
		}

		return array(
			'switcher_id' => $switcher_id,
			'material_number' => $material_number,
			'subscriber_id' => $subscriber_id,
			'flag' => $flag,
			'pln_ref_number' => $pln_ref_number,
			'switcher_ref_number' => $switcher_ref_number,
			'vending_recieve_number' => $vending_recieve_number,
			'subscriber_name' => $subscriber_name,
			'subscriber_segment' => $subscriber_segment,
			'power_categori' => $power_categori,
			'buying_option' => $buying_option,
			'minor_unit_admin_charge' => $minor_unit_admin_charge,
			'admin_charge' => floatval(substr($admin_charge,0,strlen($admin_charge)-$minor_unit_admin_charge).".".substr($admin_charge,-1*$minor_unit_admin_charge)),

			'minor_unit_stump' => $minor_unit_stump,
			'stump_duty' => floatval(substr($stump_duty,0,strlen($stump_duty)-$minor_unit_stump).".".substr($stump_duty,-1*$minor_unit_stump)),
			'minor_unit_addtax' => $minor_unit_addtax,
			'addtax' => floatval(substr($addtax,0,strlen($addtax)-$minor_unit_addtax).".".substr($addtax,-1*$minor_unit_addtax)),
			'minor_unit_ligthingtax' => $minor_unit_ligthingtax,
			'ligthingtax' => floatval(substr($ligthingtax,0,strlen($ligthingtax)-$minor_unit_ligthingtax).".".substr($ligthingtax,-1*$minor_unit_ligthingtax)),
			'minor_unit_cust_payable' => $minor_unit_cust_payable,
			'cust_payable' => floatval(substr($cust_payable,0,strlen($cust_payable)-$minor_unit_cust_payable).".".substr($cust_payable,-1*$minor_unit_cust_payable)),
			'minor_unit_power_purchase' => $minor_unit_power_purchase,
			'power_purchase' => floatval(substr($power_purchase,0,strlen($power_purchase)-$minor_unit_power_purchase).".".substr($power_purchase,-1*$minor_unit_power_purchase)),
			'minor_unit_purchase_kwh' => $minor_unit_purchase_kwh,
			'purchase_kwh' => floatval(substr($purchase_kwh,0,strlen($purchase_kwh)-$minor_unit_purchase_kwh).".".substr($purchase_kwh,-1*$minor_unit_purchase_kwh)),
			'rupiah_token' => $rupiah_token,

			'token_number' => $space_token,

			'distribution_code' => $distribution_code,
			'service_unit' => $service_unit,
			'service_phone_unit' => $service_phone_unit,
			'max_kwh' => $max_kwh,
			'total_repeat' => $total_repeat,
			'purchase_unsold' => $array_unsold,
			'info_text' => $info_text
		);

    }

    private function getResponseInquery($privateData1,$privateData2){
    	$switcher_id = substr($privateData1, 0, 7);
		$privateData1 = substr($privateData1, 7);

		$material_number = substr($privateData1, 0, 11);
		$privateData1 = substr($privateData1, 11);

		$subscriber_id = substr($privateData1, 0, 12);
		$privateData1 = substr($privateData1, 12);

		$flag = substr($privateData1, 0, 1);
		$privateData1 = substr($privateData1, 1);

		$pln_ref_number = substr($privateData1, 0, 32);
		$privateData1 = substr($privateData1, 32);

		$switcher_ref_number = substr($privateData1, 0, 32);
		$privateData1 = substr($privateData1, 32);

		$subscriber_name = substr($privateData1, 0, 25);
		$privateData1 = substr($privateData1, 25);

		$subscriber_segment = substr($privateData1, 0, 4);
		$privateData1 = substr($privateData1, 4);

		$power_categori = substr($privateData1, 0, 9);
		$privateData1 = substr($privateData1, 9);

		$minor_unit_admin_charge = substr($privateData1, 0, 1);
		$privateData1 = substr($privateData1, 1);

		$admin_charge = substr($privateData1, 0, 10);
		$privateData1 = substr($privateData1, 10);

		$distribution_code = substr($privateData2, 0, 2);
		$privateData2 = substr($privateData2, 2); 

		$service_unit = substr($privateData2, 0, 5);
		$privateData2 = substr($privateData2, 5); 

		$service_phone_unit = substr($privateData2, 0, 15);
		$privateData2 = substr($privateData2, 15); 

		$max_kwh = substr($privateData2, 0, 5);
		$privateData2 = substr($privateData2, 5); 

		$total_repeat = substr($privateData2, 0, 1);
		$privateData2 = substr($privateData2, 1); 

		$array_unsold = "";

		if($total_repeat > 0){
			$split_unsold = str_split($privateData2,11);
			foreach ($split_unsold as $unsold) {
				$array_unsold .= (int)$unsold . ",";
			}
			$array_unsold = substr($array_unsold, 0, strlen($array_unsold)-1);
		}

		return array(
			'switcher_id' => $switcher_id,
			'material_number' => $material_number,
			'subscriber_id' => $subscriber_id,
			'flag' => $flag,
			'pln_ref_number' => $pln_ref_number,
			'switcher_ref_number' => $switcher_ref_number,
			'subscriber_name' => $subscriber_name,
			'subscriber_segment' => $subscriber_segment,
			'power_categori' => $power_categori,
			'minor_unit_admin_charge' => $minor_unit_admin_charge,
			//'admin_charge' => $admin_charge,
			'admin_charge' => floatval(substr($admin_charge,0,strlen($admin_charge)-$minor_unit_admin_charge).".".substr($admin_charge,-1*$minor_unit_admin_charge)),
			'distribution_code' => $distribution_code,
			'service_unit' => $service_unit,
			'service_phone_unit' => $service_phone_unit,
			'max_kwh' => $max_kwh,
			'total_repeat' => $total_repeat,
			'purchase_unsold' => $array_unsold
		);

    }

    private function getInqueryMessage($idpel,$flag,$is_mobile,$username){
    	$pln_config = Config::get('app.pln');

    	$Mti = "2100";
    	$Bitmap = "4030004180810000";
    	$PrimaryAccountNumber = $this->postpaid_code;
    	$LocalDateTime = date('YmdHis');

    	$audit_number = AuditNumber::max('id');
		if(!$audit_number){
			$audit_number = "1";
		}else{
			$audit_number = $audit_number + 1;
		}

		$traceAuditNumber = str_pad($audit_number, 12, "0", STR_PAD_LEFT);
		
		//SIMPAN LAST NUMBER TRACE AUDIT NUMBER
		$auditNumber = new AuditNumber();
		$auditNumber->subcriber_id = $idpel;
		$auditNumber->save();

		$merchantCode = "6021";
		$bankCode = "074510017"; //07 BANK CODE LENGTH
		$partnerId = "07".$pln_config['pcid'];

		if($is_mobile){
			$terminalId = strtoupper($username); //DIOLAH SENDIRI
		}else{
			$terminalId = strtoupper(Auth::user()->username); //DIOLAH SENDIRI
		}
		
		//$terminalId = strtoupper("YAKIN");
		$terminalId = str_pad($terminalId, 16, "0", STR_PAD_LEFT);

		$subscriberId = "";
		$materialId = "";
		if($flag == 1){
			$subscriberId = $idpel;
			$materialId = "00000000000";
		}else{
			$subscriberId = "000000000000";
			$materialId = $idpel;
		}

		$privateData = "ST145S3".$materialId.$subscriberId.$flag;
		$dataLen = str_pad(strlen($privateData),3,"0",STR_PAD_LEFT);
		$privateData = $dataLen.$privateData;

		$message = $Mti.$Bitmap.$PrimaryAccountNumber.$traceAuditNumber.$LocalDateTime.$merchantCode.$bankCode.$partnerId.$terminalId.$privateData;

		return $message;
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

    private function getResponseMessage($response_code){
		$message = "";
		switch ($response_code) {
			case '0000':
				$message = "PEMBELIAN TOKEN BERHASIL.";
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
			case '0009':
				$message = "NOMOR METER / IDPEL YANG ANDA MASUKAN SALAH, MOHON TELITI KEMBALI"; //Inactive Account
				break;
			case '0011':
				$message = "NEED TO SIGN ON";
				break;
			case '0014':
				$message = "IDPEL YANG ANDA MASUKKAN SALAH, MOHON TELITI KEMBALI";
				break;
			case '0015':
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
			case '0041':
				$message = "TRANSACTION AMOUNT BELOW MINIMUM PURCHASE AMOUNT";
				break;
			case '0042':
				$message = "TRANSACTION AMOUNT EXCEED MAXIMUM PURCHASE AMOUNT";
				break;
			case '0045':
				$message = "INVAILID ADMIN CHARGE";
				break;
			case '0046':
				$message = "BALANCE IS NOT ENOUGH";
				break;
			case '0047':
				$message = "TOTAL KWH MELEBIHI BATAS MAKSIMUM"; //Over KWH Limit
				break;
			case '0068':
				$message = "TIME OUT";
				break;
			case '0077':
				$message = "KONSUMEN {IDPEL} DIBLOKIR HUBUNGI PLN"; //Subscriber Suspend
				break;
			case '0090':
				$message = "CUT OFF IN PROGRESS";
				break;
			case '0016':
				$message = "KONSUMEN {IDPEL} DIBLOKIR HUBUNGI PLN ";
				break;
			case '0013':
				$message = "NOMINAL PEMBELIAN TIDAK TERDAFTAR";
				break;
			case '0063':
				$message = "TRANSAKSI GAGAL";
				break;
			
			default:
				//$message = $response_code . " - NO ERROR CODE MATCH";
				$message = "NO ERROR CODE MATCH";
				break;
		}
		return $message;
	}

}