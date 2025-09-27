<?php

namespace App\PlnServices;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;

class ISOService
{
    public function __construct()
    {

    }

    public function iso_parser($iso_message){
		//$iso_message = "2100403000418081000005995010000001197602017030110511760210745100170745102160000000000000048019ST145S3541120114555";

		$mti = substr($iso_message,0, 4);
		$iso_message = substr($iso_message, 4);

		$bitmap = substr($iso_message, 0, 16);
		$iso_message = substr($iso_message, 16);

		$binary = "0".base_convert($bitmap, 16, 2);

		$mElement = array();

		$jenis_transaksi = $this->getJenisMessage($mti);

		$mHasil = "";
		for($i=0; $i<strlen($binary); $i++){
			$mBin = substr($binary, $i, 1);
			if((int) $mBin == 1){
				//$mHasil .= $i+1 ." ";
				//if($i+1 == 48) dd($iso_message);

				$isoParsing = $this->messageToArray($i+1, $iso_message);
				$mElement[$i+1] = $isoParsing;

				$iso_message = substr($iso_message, $isoParsing['data_length']);

				//if($i+1 == 48) dd($isoParsing['data_length']);
			}
		}

		$Response = array(
			"mti" => $mti,
			"bitmap" => $bitmap,
			"message_type" => $jenis_transaksi,
			"iso_message" => $mElement
		);

		return $Response;

	}

	private function getJenisMessage($mti){
		$jenis_transaksi = "";
		switch ($mti) {
			case '2100':
				$jenis_transaksi = "INQUIRY REQUEST MESSAGE";
				break;
			case '2110':
				$jenis_transaksi = "INQUIRY RESPONSE MESSAGE";
				break;
			case '2200':
				$jenis_transaksi = "PAYMENT/PURCHASE REQUEST MESSAGE";
				break;
			case '2210':
				$jenis_transaksi = "PAYMENT/PURCHASE RESPONSE MESSAGE";
				break;
			case '2400':
				$jenis_transaksi = "REVERSAL (REPEAT) REQUEST MESSAGE 1";
				break;
			case '2401':
				$jenis_transaksi = "REVERSAL (REPEAT) REQUEST MESSAGE 2";
				break;
			case '2410':
				$jenis_transaksi = "REVERSAL (REPEAT)RESPONSE MESSAGE 1";
				break;
			case '2411':
				$jenis_transaksi = "REVERSAL (REPEAT)RESPONSE MESSAGE 2";
				break;
			case '2220':
				$jenis_transaksi = "ADVICE(REPEAT) REQUEST MESSAGE 1";
				break;
			case '2221':
				$jenis_transaksi = "ADVICE(REPEAT) REQUEST MESSAGE 2";
				break;
			case '2230':
				$jenis_transaksi = "ADVICE(REPEAT) RESPONSE MESSAGE 1";
				break;
			case '2231':
				$jenis_transaksi = "ADVICE(REPEAT) RESPONSE MESSAGE 2";
				break;
			
			default:
				# code...
				break;
		}
		return $jenis_transaksi;
	}

	private function messageToArray($elementNumber, $iso_message){
		$mElement = array();
		$length = 0;
		switch ($elementNumber) {
			case 2:
				$mElement['description'] = "PRIMARY ACCOUNT NUMBER";
				$mElement['data_element'] = 2;
				$mElement['data_length'] = 7;
				$mElement['data'] = array (
					'primary_account_number_length' => (int)substr($iso_message,0,2),
				 	'primary_account_number' => substr($iso_message,2,5)
				);
				break;
			case 4:
				$mElement['description'] = "TRANSACTION AMOUNT";
				$mElement['data_element'] = 4;
				$mElement['data_length'] = 16;
				$mElement['data'] = array (
					'currency_code' => substr($iso_message,0,3),
					'minor_unit' => substr($iso_message,3,1),
					'value_amount' => (int)substr($iso_message,4,12)
				);
				break;
			case 11:
				$mElement['description'] = "PATNER CENTRAL NUMBER";
				$mElement['data_element'] = 11;
				$mElement['data_length'] = 12;
				$mElement['data'] = array (
					'partner_central_number' => substr($iso_message,0,12)
				);
				break;
			case 12:
				$mElement['description'] = "DATETIME LOCAL TRANSACTION";
				$mElement['data_element'] = 12;
				$mElement['data_length'] = 14;
				$mElement['data'] = array (
					'datetime_local' => substr($iso_message,0,14)
				);
				break;
			case 15:
				$mElement['description'] = "DATE SATTLEMENT";
				$mElement['data_element'] = 15;
				$mElement['data_length'] = 8;
				$mElement['data'] = array (
					'datetime_local' => substr($iso_message,0,8)
				);
				break;
			case 26:
				$mElement['description'] = "MERCHANT CODE";
				$mElement['data_element'] = 26;
				$mElement['data_length'] = 4;
				$mElement['data'] = array (
					'merchant_code' => substr($iso_message,0,4)
				);
				break;
			case 32:
				$mElement['description'] = "LENGTH OF BANK CODE";
				$mElement['data_element'] = 32;
				$mElement['data_length'] = 9;
				$mElement['data'] = array (
					'bank_code_length' => (int)substr($iso_message,0,2),
					'bank_code' => substr($iso_message,2,7)
				);
				break;
			case 33:
				$mElement['description'] = "PARTNER ID";
				$mElement['data_element'] = 33;
				$mElement['data_length'] = 9;
				$mElement['data'] = array (
					'partner_id_length' => (int)substr($iso_message,0,2),
					'partner_id' => substr($iso_message,2,7)
				);
				break;
			case 39:
				$mElement['description'] = "RESPONSE CODE";
				$mElement['data_element'] = 39;
				$mElement['data_length'] = 4;
				$mElement['data'] = array (
					'response_code' => substr($iso_message,0,4)
				);
				break;
			case 41:
				$mElement['description'] = "TERMINAL ID";
				$mElement['data_element'] = 41;
				$mElement['data_length'] = 16;
				$mElement['data'] = array (
					'terminal_id' => substr($iso_message,0,16)
				);
				break;
			case 48:
				$length = strlen($iso_message);
				$private_data_length = substr($iso_message,0,3);
				$private_data = substr($iso_message,3,$private_data_length);

				//dd($private_data_length+3);
				//dd(substr($iso_message, (int)$private_data_length + 3));

				$mElement['description'] = "PRIVATE DATA 1";
				$mElement['data_element'] = 48;
				$mElement['data_length'] = $private_data_length + 3;
				$mElement['data'] = array (
					'private_data_length' => (int)$private_data_length,
					'private_data' => $private_data
				);
				break;
			case 56:
				//dd($iso_message);

				$length = strlen($iso_message);
				$private_data_length = substr($iso_message,0,2);

				$private_data = substr($iso_message,2,$private_data_length);

				$mElement['description'] = "ORIGINAL DATA ELEMENT 1";
				$mElement['data_element'] = 56;
				$mElement['data_length'] = $private_data_length + 2;
				$mElement['data'] = array (
					'private_data_length' => (int)$private_data_length,
					'private_data' => $private_data
				);
				break;

			case 62:
				$length = strlen($iso_message);
				$private_data_length = substr($iso_message,0,3);
				$private_data = substr($iso_message,3,$private_data_length);

				$mElement['description'] = "PRIVATE DATA 2";
				$mElement['data_element'] = 62;
				$mElement['data_length'] = $private_data_length + 3;
				$mElement['data'] = array (
					'private_data_length' => (int)$private_data_length,
					'private_data' => $private_data
				);
				break;

			case 63:
				$length = strlen($iso_message);
				$private_data_length = substr($iso_message,0,3);
				$private_data = substr($iso_message,3,$private_data_length);

				$mElement['description'] = "INFO TEXT";
				$mElement['data_element'] = 63;
				$mElement['data_length'] = $private_data_length + 3;
				$mElement['data'] = array (
					'private_data_length' => (int)$private_data_length,
					'private_data' => $private_data
				);
				break;
			
			default:
				# code...
				break;
		}

		return $mElement;
	}

}