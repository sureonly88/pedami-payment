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
use App\Models\PlnNontaglisTransaksi;
use App\User;

class NonTaglisService
{
	protected $isoHelper;
	protected $socketHelper;

	protected $notaglis_code = "0599504"; //KODE NON TAGLIS

    public function __construct(ISOService $isoHelper, SocketService $socketHelper)
    {
    	$this->isoHelper = $isoHelper;
    	$this->socketHelper = $socketHelper;
    }

    public function Inquery($register_number,$is_mobile,$username){
    	
    	$pln_config = Config::get('app.pln');
    	$audit_number = AuditNumber::max('id');
		if(!$audit_number){
			$audit_number = "1";
		}else{
			$audit_number = $audit_number + 1;
		}
		
		//SIMPAN LAST NUMBER TRACE AUDIT NUMBER
		$aNumber = new AuditNumber();
		$aNumber->subcriber_id = $register_number;
		$aNumber->save();

		$trace_audit_number = str_pad($audit_number, 12, "0", STR_PAD_LEFT);

		$datetime_local = date('YmdHis');
		$merchant_code = "6021";
		$bank_code = "4510017";
		$partner_id = $pln_config['pcid'];

		if($is_mobile){
			$terminal_id = strtoupper($username);
		}else{
			$terminal_id = strtoupper(Auth::user()->username);
			//$terminal_id = strtoupper("TEST01");
		}
		
		$terminal_id = str_pad($terminal_id, 16, "0", STR_PAD_LEFT);

		$message_format = "21004030004180810000".$this->notaglis_code.$trace_audit_number.$datetime_local.$merchant_code."07".$bank_code."07".$partner_id.$terminal_id."023ST145S3".$register_number."000";

		//$parsing = $this->isoHelper->iso_parser($message_format);
		//dd($parsing);

		$response = $this->startSocket($message_format,'inquery',$is_mobile,$username,$register_number);

		return $response;

    }

     public function Payment($payment_message,$total_bayar,$is_mobile,$username,$register_number){
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

			//$parsing = $this->isoHelper->iso_parser($payment_message);
			//$this->simpanLogPln($subcriber_id,$payment_message,json_encode($parsing),'PAYMENT_REQUEST');

			$response_payment = $this->startSocket($payment_message,'payment',$is_mobile,$username,$register_number);
			return $response_payment;

		}catch (\Exception $e) {

    		$err_message = explode("\r\n",$e->getMessage());
    		//$this->simpanLogSistem(json_encode($err_message));
	        return array(
                'status' => false,
                'response_code' => "9993",
                'message' => "TERJADI KESALAHAN, TRANSAKSI GAGAL."
            );
	    }
    }

    public function Reversal($reversal_message,$is_mobile,$username,$register_number){

		try{

			$parsing = $this->isoHelper->iso_parser($reversal_message);
			//$this->simpanLogPln($subcriber_id,$payment_message,json_encode($parsing),'REVERSAL_REQUEST');

			$response = $this->startSocket($reversal_message,'reversal',$is_mobile,$username,$register_number);
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

        $total_bayar = $parse_private_data['pln_bill_value']+$parse_private_data['admin_charge'];

		$Pln = new PlnNontaglisTransaksi();
		$Pln->register_number = $parse_private_data['register_number'];
		$Pln->transaction_code_pln = $parse_private_data['transaction_code_pln'];
		$Pln->transaction_name = $parse_private_data['transaction_name'];
		$Pln->registration_date = $parse_private_data['registration_date'];
		$Pln->expiration_date = $parse_private_data['expiration_date'];
		$Pln->subscriber_id = $parse_private_data['subscriber_id'];
		$Pln->subscriber_name = $parse_private_data['subscriber_name'];
		$Pln->pln_ref_number = $parse_private_data['pln_ref_number'];

		$Pln->switcher_ref_number = $parse_private_data['switcher_ref_number'];
		$Pln->service_unit_address = $parse_private_data['service_unit_address'];
		$Pln->service_unit_phone = $parse_private_data['service_unit_phone'];
		//$Pln->total_transaction = $parse_private_data['total_transaction'];
		$Pln->total_transaction = $total_bayar;
		$Pln->pln_bill_value = $parse_private_data['pln_bill_value'];
		$Pln->admin_charge = $parse_private_data['admin_charge'];
		$Pln->info_text = $parse_private_data['info_text'];

		$Pln->trace_audit_number = $partner_number;

		$Pln->username = $username;
		$Pln->loket_name = $loket_name;
		$Pln->loket_code = $loket_code;
		$Pln->jenis_loket = $jenis_loket;
		$Pln->transaction_code = $unique_id;
		$Pln->transaction_date = $tgl_format;

		//$total_bayar += $total;

		$Pln->save();

		$loketService = resolve('\App\Services\LoketService');
		$loketService->kurangiSaldo($total_bayar,$loket_id);

		return $unique_id;
	}


    private function startSocket($message_format,$jenis,$is_mobile,$username,$register_number){

    	$response_socket = $this->socketHelper->runSocket('NONTAGLIS',$message_format);

  //   	switch ($jenis) {
		// 	case 'inquery':
		// 		$response_socket = $this->socketHelper->runSocket('NONTAGLIS',$message_format);
		// 		break;
		// 	case 'payment':
		// 		$response_payment = "2210503200418281000605995043600000000272500000000000001201703091449562017030960210745100170745120170000000000000XXDEV01267ST145S35123123321556000PEMASANGAN BARU          2015121020170316512312332006FIRDA'US/MIRNA           675356E81557019514329905528I079I0SYM21216934044406199023A147060599999JL. PELAJAR PEJUANG NO. 100        022-45152244   2000000000272500002000000000272500002000000000002001200000000027250000064Rincian tagihan dapat diakses di www.pln.co.id atau PLN terdekat";

		// 		$hasil['socket_create'] = true;
		// 	    $hasil['socket_create_message'] = "socket create berhasil";
		// 	    $hasil['socket_connect'] = true;
		// 	    $hasil['socket_connect_message'] = "socket connect berhasil";
		// 	    $hasil['response'] = $response_payment;

		// 	    $response_socket = $hasil;
		// 		break;
		// 	case 'reversal':
		// 		$response_reversal = "24105030004182810104059950436000000000340000000000000012017030914432460210745100170745120170000000000000XXDEV01267ST145S35211666202011000PERUBAHAN DAYA           2017030920170316521166667980FIRMAN S                 9175654338052788091A712397914I400SYM21216597308358C513025500A62D99999JL. AH NASUTION NO. 76             022-45152244   2000000000034000002000000000034000002000000000037220000000000000120170309144324451001702001200000000003400000";

		// 		$hasil['socket_create'] = true;
		// 	    $hasil['socket_create_message'] = "socket create berhasil";
		// 	    $hasil['socket_connect'] = true;
		// 	    $hasil['socket_connect_message'] = "socket connect berhasil";
		// 	    $hasil['response'] = $response_reversal;

		// 	    $response_socket = $hasil;
		// 		break;
		// }

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
			if($response_code == "000"){

				switch ($jenis) {
					case 'inquery':
						
						$private_data1 = $response['iso_message'][48]['data']['private_data'];
						$private_data2 = $response['iso_message'][62]['data']['private_data'];	

						$parse_private_data = $this->parse_private_data($private_data1,$private_data2);

						$PrimaryAccountNumber = $response['iso_message'][2]['data']['primary_account_number'];
						$traceAuditNumber = $response['iso_message'][11]['data']['partner_central_number'];
						$LocalDateTime = $response['iso_message'][12]['data']['datetime_local'];
						$merchantCode = $response['iso_message'][26]['data']['merchant_code']; 
						$bankCode = $response['iso_message'][32]['data']['bank_code'];  
						$partnerId = $response['iso_message'][33]['data']['partner_id'];  
						$terminalId = $response['iso_message'][41]['data']['terminal_id'];

						$TransactionAmount = $response['iso_message'][4]['data']['currency_code'].$response['iso_message'][4]['data']['minor_unit'];
						$TransactionAmount .= str_pad($response['iso_message'][4]['data']['value_amount'], 12, "0", STR_PAD_LEFT);

						$privateData1Len = str_pad(strlen($private_data1), 3, "0", STR_PAD_LEFT);
						$privateData2Len = str_pad(strlen($private_data2), 3, "0", STR_PAD_LEFT);

						$paymentMessage = "22005030004180810004"."05".$PrimaryAccountNumber.$TransactionAmount.$traceAuditNumber.$LocalDateTime.$merchantCode."07".$bankCode."07".$partnerId.$terminalId.$privateData1Len.$private_data1.$privateData2Len.$private_data2;

						$originalData = "37"."2200".$traceAuditNumber.$LocalDateTime.$bankCode;

						//dd($originalData);

						$reversalMessage = "24005030004180810104"."05".$PrimaryAccountNumber.$TransactionAmount.$traceAuditNumber.$LocalDateTime.$merchantCode."07".$bankCode."07".$partnerId.$terminalId.$privateData1Len.$private_data1.$originalData.$privateData2Len.$private_data2;

						return array(
			                'status' => true,
			                'response_code' => $response_code,
			                'message' => $response_message,
			                'iso_message' => $response,
			                'customer' => array (
			                	'data' => $parse_private_data,
			                	'payment_message' => $paymentMessage,
			                	'reversal_message' => $reversalMessage
			                )
			            );
						break;

					case 'payment':

						$private_data1 = $response['iso_message'][48]['data']['private_data'];
						$private_data2 = $response['iso_message'][62]['data']['private_data'];
						$private_data3 = $response['iso_message'][63]['data']['private_data'];

						$parse_private_data = $this->parse_payment_response($private_data1,$private_data2,$private_data3);

						$partner_number = $response['iso_message'][11]['data']['partner_central_number'];
						$datetime_inquery_response = $response['iso_message'][12]['data']['datetime_local'];
						$tgl_format = substr($datetime_inquery_response, 0,4) . "-" . substr($datetime_inquery_response, 4,2). "-". substr($datetime_inquery_response, 6,2)." ". substr($datetime_inquery_response, 8,2).":". substr($datetime_inquery_response, 10,2).":". substr($datetime_inquery_response, 12,2);

						$unique_id = $this->simpanTransaksiPayment($parse_private_data,$partner_number,$tgl_format,$is_mobile,$username);

						$parse_private_data['transaction_code'] = $unique_id;
						$parse_private_data['transaction_date'] = $tgl_format;

						return array(
			                'status' => true,
			                'response_code' => $response_code,
			                'message' => $response_message,
			                'customer' => $parse_private_data
			            );
						break;

					case 'reversal':

						//TRANSAKSI GAGAL, STRUK TIDAK TERCETAK
						$private_data1 = $response['iso_message'][48]['data']['private_data'];
						$private_data2 = $response['iso_message'][62]['data']['private_data'];

						$parse_private_data = $this->parse_private_data($private_data1,$private_data2);

						return array(
			                'status' => true,
			                'response_code' => $response_code,
			                'message' => $response_message,
			                'customer' => $parse_private_data
			            );

						break;
				}

			}else{

				//FOR REVERSAL RESPONSE CODE 0012 MASIH PERLU DICETAK DAN SIMPAN KE DATABASE
				if($jenis == "reversal"){
					if($response_code == "0012"){
						$private_data1 = $response['iso_message'][48]['data']['private_data'];
						$private_data2 = $response['iso_message'][62]['data']['private_data'];

						$parse_private_data = $this->parse_private_data($private_data1,$private_data2);

						$partner_number = $response['iso_message'][11]['data']['partner_central_number'];
						$datetime_inquery_response = $response['iso_message'][12]['data']['datetime_local'];
						$tgl_format = substr($datetime_inquery_response, 0,4) . "-" . substr($datetime_inquery_response, 4,2). "-". substr($datetime_inquery_response, 6,2)." ". substr($datetime_inquery_response, 8,2).":". substr($datetime_inquery_response, 10,2).":". substr($datetime_inquery_response, 12,2);

						$unique_id = $this->simpanTransaksiPayment($parse_private_data,$partner_number,$tgl_format,$is_mobile,$username);

						$parse_private_data['transaction_code'] = $unique_id;
						$parse_private_data['transaction_date'] = $tgl_format;

						return array(
			                'status' => false,
			                'response_code' => $response_code,
			                'message' => $response_message,
			                'customer' => $parse_private_data
			            );
					}
				}

				if($response_code == "0016"){
					$response_message = str_replace("{IDPEL}", $register_number, $response_message);
				}

				return array(
	                'status' => false,
	                'response_code' => $response_code,
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

    private function parse_reversal_response($private_data1,$private_data2){

    }

    private function parse_payment_response($private_data1,$private_data2,$private_data3){
    	$switcher_id = substr($private_data1, 0, 7);
		$private_data1 = substr($private_data1, 7);

		$register_number = substr($private_data1, 0, 13);
		$private_data1 = substr($private_data1, 13);

		$transaction_code = substr($private_data1, 0, 3);
		$private_data1 = substr($private_data1, 3);

		$transaction_name = substr($private_data1, 0, 25);
		$private_data1 = substr($private_data1, 25);

		$registration_date = substr($private_data1, 0, 8);
		$private_data1 = substr($private_data1, 8);

		$expiration_date = substr($private_data1, 0, 8);
		$private_data1 = substr($private_data1, 8);

		$subscriber_id = substr($private_data1, 0, 12);
		$private_data1 = substr($private_data1, 12);

		$subscriber_name = substr($private_data1, 0, 25);
		$private_data1 = substr($private_data1, 25);

		$pln_ref_number = substr($private_data1, 0, 32);
		$private_data1 = substr($private_data1, 32);

		$switcher_ref_number = substr($private_data1, 0, 32);
		$private_data1 = substr($private_data1, 32);

		$service_unit = substr($private_data1, 0, 5);
		$private_data1 = substr($private_data1, 5);

		$service_unit_address = substr($private_data1, 0, 35);
		$private_data1 = substr($private_data1, 35);

		$service_unit_phone = substr($private_data1, 0, 15);
		$private_data1 = substr($private_data1, 15);

		$minor_total_transaction = substr($private_data1, 0, 1);
		$private_data1 = substr($private_data1, 1);

		$total_transaction = substr($private_data1, 0, 17);
		$private_data1 = substr($private_data1, 17);

		$minor_pln_bill_value = substr($private_data1, 0, 1);
		$private_data1 = substr($private_data1, 1);

		$pln_bill_value = substr($private_data1, 0, 17);
		$private_data1 = substr($private_data1, 17);

		$minor_admin_charge = substr($private_data1, 0, 1);
		$private_data1 = substr($private_data1, 1);

		$admin_charge = substr($private_data1, 0, 10);
		$private_data1 = substr($private_data1, 10);

		$total_repeat = substr($private_data2, 0, 2);
		$private_data2 = substr($private_data2, 2);

		$array_custom_detail = array();
		$list_custom_detail = str_split($private_data2,18);

		//dd($list_custom_detail);

		foreach ($list_custom_detail as $custom_detail) {
			$detail = $custom_detail;

			$minor_custom_detail = substr($detail, 0, 1);
			$detail = substr($detail, 1);

			$custom_detail = substr($detail, 0, 17);
			$detail = substr($detail, 17);

			$custom = array(
					"minor_custom_detail_value" => $minor_custom_detail,
					"custom_detail_value" => $custom_detail
				);

			array_push($array_custom_detail, $custom);
		}

		return array(
			"switcher_id" => $switcher_id,
			"register_number" => $register_number,
			"transaction_code_pln" => $transaction_code,
			"transaction_name" => $transaction_name,
			"registration_date" => $registration_date,
			"expiration_date" => $expiration_date,
			"subscriber_id" => $subscriber_id,
			"subscriber_name" => $subscriber_name,
			"pln_ref_number" => $pln_ref_number,
			"switcher_ref_number" => $switcher_ref_number,
			"service_unit_address" => $service_unit_address,
			"service_unit_phone" => $service_unit_phone,
			"minor_total_transaction" => $minor_total_transaction,
			"total_transaction" => floatval(substr($total_transaction,0,strlen($total_transaction)-$minor_total_transaction).".".substr($total_transaction,-1*$minor_total_transaction)),
			"minor_pln_bill_value" => $minor_pln_bill_value,
			"pln_bill_value" => floatval(substr($pln_bill_value,0,strlen($pln_bill_value)-$minor_pln_bill_value).".".substr($pln_bill_value,-1*$minor_pln_bill_value)),
			"minor_admin_charge" => $minor_admin_charge,
			"admin_charge" => floatval(substr($admin_charge,0,strlen($admin_charge)-$minor_admin_charge).".".substr($admin_charge,-1*$minor_admin_charge)),
			"total_repeat" => $total_repeat,
			"custom_detail" => $array_custom_detail,
			"info_text" => $private_data3
		);


    }

	private function parse_private_data($private_data1,$private_data2){
		$switcher_id = substr($private_data1, 0, 7);
		$private_data1 = substr($private_data1, 7);

		$register_number = substr($private_data1, 0, 13);
		$private_data1 = substr($private_data1, 13);

		$transaction_code = substr($private_data1, 0, 3);
		$private_data1 = substr($private_data1, 3);

		$transaction_name = substr($private_data1, 0, 25);
		$private_data1 = substr($private_data1, 25);

		$registration_date = substr($private_data1, 0, 8);
		$private_data1 = substr($private_data1, 8);

		$expiration_date = substr($private_data1, 0, 8);
		$private_data1 = substr($private_data1, 8);

		$subscriber_id = substr($private_data1, 0, 12);
		$private_data1 = substr($private_data1, 12);

		$subscriber_name = substr($private_data1, 0, 25);
		$private_data1 = substr($private_data1, 25);

		$pln_ref_number = substr($private_data1, 0, 32);
		$private_data1 = substr($private_data1, 32);

		$switcher_ref_number = substr($private_data1, 0, 32);
		$private_data1 = substr($private_data1, 32);

		$service_unit = substr($private_data1, 0, 5);
		$private_data1 = substr($private_data1, 5);

		$service_unit_address = substr($private_data1, 0, 35);
		$private_data1 = substr($private_data1, 35);

		$service_unit_phone = substr($private_data1, 0, 15);
		$private_data1 = substr($private_data1, 15);

		$minor_total_transaction = substr($private_data1, 0, 1);
		$private_data1 = substr($private_data1, 1);

		$total_transaction = substr($private_data1, 0, 17);
		$private_data1 = substr($private_data1, 17);

		$minor_pln_bill_value = substr($private_data1, 0, 1);
		$private_data1 = substr($private_data1, 1);

		$pln_bill_value = substr($private_data1, 0, 17);
		$private_data1 = substr($private_data1, 17);

		$minor_admin_charge = substr($private_data1, 0, 1);
		$private_data1 = substr($private_data1, 1);

		$admin_charge = substr($private_data1, 0, 10);
		$private_data1 = substr($private_data1, 10);

		$total_repeat = substr($private_data2, 0, 2);
		$private_data2 = substr($private_data2, 2);

		$array_custom_detail = array();
		$list_custom_detail = str_split($private_data2,18);

		//dd($list_custom_detail);

		foreach ($list_custom_detail as $custom_detail) {
			$detail = $custom_detail;

			$minor_custom_detail = substr($detail, 0, 1);
			$detail = substr($detail, 1);

			$custom_detail = substr($detail, 0, 17);
			$detail = substr($detail, 17);

			$custom = array(
					"minor_custom_detail_value" => $minor_custom_detail,
					"custom_detail_value" => $custom_detail
				);

			array_push($array_custom_detail, $custom);
		}

		return array(
			"switcher_id" => $switcher_id,
			"register_number" => $register_number,
			"transaction_code_pln" => $transaction_code,
			"transaction_name" => $transaction_name,
			"registration_date" => $registration_date,
			"expiration_date" => $expiration_date,
			"subscriber_id" => $subscriber_id,
			"subscriber_name" => $subscriber_name,
			"pln_ref_number" => $pln_ref_number,
			"switcher_ref_number" => $switcher_ref_number,
			"service_unit_address" => $service_unit_address,
			"service_unit_phone" => $service_unit_phone,
			"minor_total_transaction" => $minor_total_transaction,
			"total_transaction" => floatval(substr($total_transaction,0,strlen($total_transaction)-$minor_total_transaction).".".substr($total_transaction,-1*$minor_total_transaction)),
			"minor_pln_bill_value" => $minor_pln_bill_value,
			"pln_bill_value" => floatval(substr($pln_bill_value,0,strlen($pln_bill_value)-$minor_pln_bill_value).".".substr($pln_bill_value,-1*$minor_pln_bill_value)),
			"minor_admin_charge" => $minor_admin_charge,
			"admin_charge" => floatval(substr($admin_charge,0,strlen($admin_charge)-$minor_admin_charge).".".substr($admin_charge,-1*$minor_admin_charge)),
			"total_repeat" => $total_repeat,
			"custom_detail" => $array_custom_detail,
			"info_text" => "Rincian tagihan dapat diakses di www.pln.co.id atau PLN terdekat"

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
				$message = "UNKNOWN SUBSCRIBER ID";
				break;
			case '0015':
				$message = "NOMOR REGISTRASI YANG ANDA MASUKKAN SALAH. MOHON TELITI KEMBALI";
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
			case '0048':
				$message = "NOMOR REGISTRASI KADALUWARSA, MOHON HUBUNGI PLN";
				break;
			case '0068':
				$message = "TIME OUT";
				break;
			case '0088':
				$message = "TAGIHAN SUDAH TERBAYAR";
				break;
			case '0089':
				$message = "CURRENT BILL IS NOT AVAILABLE ";
				break;
			case '0090':
				$message = "CUT OFF IN PROGRESS";
				break;
			case '0016':
				$message = "KONSUMEN {IDPEL} DIBLOKIR HUBUNGI PLN ";
				break;
			
			default:
				$message = $response_code . " - NO ERROR CODE MATCH";
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
			case '0008':
			    $message = 'INVALID ACCESS TIME';
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
			case '0016':
			    $message = 'BLOCKED SUBSCRIBER ID';
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
			case '0089':
			    $message = 'CURRENT BILL IS NOT AVAILABLE ';
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
			    $message = 'UNREGISTERED SWITCHING ';
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
			    $message = 'TRANSAKSI GAGAL.';
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
			    $message = 'TRANSAKSI GAGAL.';
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