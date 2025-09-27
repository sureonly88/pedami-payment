<?php

namespace App\Http\Controllers;

use Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use App\PlnServices\PostPaid;
use App\PlnServices\PrePaid;
use App\PlnServices\NonTaglis;
use App\Models\PlnTransaksi;
use Illuminate\Support\Facades\Log;
use DB;

class PLNMobileController extends Controller
{

    public function Inquery($nomor_pelanggan,$username,$sessionid,$imei = ""){

		$inquery = PostPaid::InqueryTagihanPostPaid($nomor_pelanggan,true,$username);

		if($inquery['status']){
			$subcriber_id = $inquery['customer']['subcriber_id'];
			$nama = $inquery['customer']['subcriber_name'];
			$gol = $inquery['customer']['subcriber_segment']." / ".$inquery['customer']['power_consumtion'];
			$lembar = $inquery['customer']['bill_status'];
			$periode = $inquery['customer']['periode'];
			$total = $inquery['customer']['total_pln'];
			$total_bayar = $inquery['customer']['total_tagihan'];
			$admin = (int)$inquery['customer']['total_admin_charge'];
			$out_bill = $inquery['customer']['outstanding_bill'];

			$TampilPel = "";
            $TampilPel .= "<b style='color:#357ca5'>IDPEL</b> : <br/>".$subcriber_id."<br/>";
            $TampilPel .= "<b style='color:#357ca5'>NAMA</b> : <br/>".$nama."<br/>";
            $TampilPel .= "<b style='color:#357ca5'>KLASIFIKASI</b> : <br/>".$gol."<br/>";
            $TampilPel .= "<b style='color:#357ca5'>PERIODE</b> : <br/>".$periode."<br/>";
            $TampilPel .= "<b style='color:#357ca5'>LEMBAR</b> : <br/>".$lembar."<br/>";
            $TampilPel .= "<b style='color:#357ca5'>TOTAL TAGIHAN PLN</b> : <br/>Rp. ".number_format($total)."<br/>";
            $TampilPel .= "<b style='color:#357ca5'>TOTAL ADMIN</b> : <br/>Rp. ".number_format($admin)."<br/>";
            $TampilPel .= "<b style='color:#357ca5'>TOTAL BAYAR</b> : <br/>Rp. ".number_format($total_bayar)."<br/>";
            $TampilPel .= "<b style='color:#357ca5'>TOTAL LEMBAR</b> : <br/>".$out_bill."<br/>";

			return Response::json(array(
	            'status' => 'Success',
	            'message' => $inquery['message'],
	            'response_code' => $inquery['response_code'],
	            'payment_message' => $inquery['customer']['payment_message'],
	            'reversal_message' => $inquery['customer']['reversal_message'],
	            'inquery' => $TampilPel,
	            'total_bayar' => $total_bayar,
	            'idpel' => $subcriber_id,

	            'nama' => $nama,
	            'lembar' => $lembar,
	            'periode' => $periode,
	            'sub_total' => $total,
	            'admin' => $admin,
	            'out_bill' => $out_bill
	        ),200);
		}else{
			return Response::json(array(
	            'status' => 'Error',
	            'message' => $inquery['message']
	        ),200);
		}
	}

	public function Reversal(Request $request){
		
		$payment_message = Request::get('payment_message');
		$subcriber_id = Request::get('subcriber_id');
		$number_request = Request::get('number_request');

		$session_id = Request::get('sessionid');
        $username = Request::get('username');
        $imei = Request::get('imei');

		if($number_request > 1){
			$payment_message = '2401'.substr($payment_message, 4);
		}

		$payment = PostPaid::ReversalTagihanPostPaid($subcriber_id,$payment_message,true,$username);

		$cetakan = "";
		$response_code = $payment['response_code'];
		if($response_code == '0012'){
			$billing = $payment['customer']['billing'];

			$switcher_ref = $payment['customer']['switcher_ref'];
			$transaction_code = $payment['customer']['transaction_code'];
			$transaction_date = $payment['customer']['transaction_date'];

			for($i=0;$i<sizeof($billing);$i++){

				$bill = $billing[$i];
				$subcriber_id = $bill['subcriber_id'];
				$subcriber_name = $bill['subcriber_name'];
				$subcriber_segment = $bill['subcriber_segment'];
				$power_consumtion = (int)$bill['power_consumtion'];
				$bill_periode = $bill['bill_periode'];
				$bill_status = $bill['bill_status'];
				$outstanding_bill = $bill['outstanding_bill'];
				$total_elec_bill = $bill['total_elec_bill'];
				$added_tax = $bill['added_tax'];
				$penalty_fee = $bill['penalty_fee'];
				$admin_charge = $bill['admin_charge'];	

				$total_pln = (int)$total_elec_bill+(int)$added_tax+(int)$penalty_fee;
				$total_transaksi = (int)$total_elec_bill+(int)$added_tax+(int)$penalty_fee+(int)$admin_charge;

				$prev_meter_read_1 = $bill['prev_meter_read_1'];
				$curr_meter_read_1 = $bill['curr_meter_read_1'];
				$sisa_bill =  (int)$outstanding_bill-(int)$bill;

				$cetakan .= "PPOB - KOPKAR PEDAMI\n";
				$cetakan .= "STRUK PEMBAYARAN TAGIHAN LISTRIK\n\n";

				$cetakan .= "IDPEL : \n".$subcriber_id."\n";
				$cetakan .= "NAMA : \n".$subcriber_name."\n";
				$cetakan .= "TARIF / DAYA : \n".$subcriber_segment." / ".$power_consumtion."VA \n";
				$cetakan .= "BL / TH : \n".$bill_periode."\n";
				$cetakan .= "STAND : \n".$prev_meter_read_1." - ".$curr_meter_read_1."\n";
				$cetakan .= "RP. TAG PLN : \nRp. ".number_format($total_pln)."\n";
				$cetakan .= "ADMIN BANK : \nRp. ".number_format($admin_charge)."\n";
				$cetakan .= "TOTAL BAYAR : \nRp. ".number_format($total_transaksi)."\n";
				$cetakan .= "NO.REF : \n".$switcher_ref."\n";
				if($sisa_bill > 0){
					$cetakan .= "ANDA MASIH MEMILIKI TUNGGAKAN ".$sisa_bill." BULAN\n";
				}else{
					$cetakan .= "TERIMA KASIH\n";
				}
				$cetakan .= $transaction_code."\n";
				$cetakan .= $transaction_date."\n\n";
			}
			
			return Response::json(array(
	            'status' => 'Success',
	            'message' => $payment['message'],
	            'response_code' => $payment['response_code'],
	            'cetakan' => $cetakan
	        ),200);
		}else{
			return Response::json(array(
	            'status' => 'Error',
	            'response_code' => $payment['response_code'],
	            'message' => $payment['message']
	        ),200);
		}

		return Response::json($payment,200);
	}

	public function Payment(Request $request){

		$payment_message = Request::get('payment_message');
		$subcriber_id = Request::get('subcriber_id');
		$total_bayar = Request::get('total_bayar');

		$session_id = Request::get('sessionid');
        $username = Request::get('username');
        $imei = Request::get('imei');

		$payment = PostPaid::PaymentTagihanPostPaid($subcriber_id,$payment_message,$total_bayar,true,$username);
		$cetakan = "";

		$response_code = $payment['response_code'];
		if($payment['status']){
			
			$billing = $payment['customer']['billing'];

			$switcher_ref = $payment['customer']['switcher_ref'];
			$transaction_code = $payment['customer']['transaction_code'];
			$transaction_date = $payment['customer']['transaction_date'];

			for($i=0;$i<sizeof($billing);$i++){

				$bill = $billing[$i];
				$subcriber_id = $bill['subcriber_id'];
				$subcriber_name = $bill['subcriber_name'];
				$subcriber_segment = $bill['subcriber_segment'];
				$power_consumtion = (int)$bill['power_consumtion'];
				$bill_periode = $bill['bill_periode'];
				$bill_status = $bill['bill_status'];
				$outstanding_bill = $bill['outstanding_bill'];
				$total_elec_bill = $bill['total_elec_bill'];
				$added_tax = $bill['added_tax'];
				$penalty_fee = $bill['penalty_fee'];
				$admin_charge = $bill['admin_charge'];	

				$total_pln = (int)$total_elec_bill+(int)$added_tax+(int)$penalty_fee;
				$total_transaksi = (int)$total_elec_bill+(int)$added_tax+(int)$penalty_fee+(int)$admin_charge;

				$prev_meter_read_1 = $bill['prev_meter_read_1'];
				$curr_meter_read_1 = $bill['curr_meter_read_1'];
				$sisa_bill =  (int)$outstanding_bill-(int)$bill_status;

				$cetakan .= "PPOB - KOPKAR PEDAMI\n";
				$cetakan .= "STRUK PEMBAYARAN TAGIHAN LISTRIK\n\n";

				$cetakan .= "IDPEL : \n".$subcriber_id."\n";
				$cetakan .= "NAMA : \n".$subcriber_name."\n";
				$cetakan .= "TARIF / DAYA : \n".$subcriber_segment." / ".$power_consumtion."VA \n";
				$cetakan .= "BL / TH : \n".$bill_periode."\n";
				$cetakan .= "STAND : \n".$prev_meter_read_1." - ".$curr_meter_read_1."\n";
				$cetakan .= "RP. TAG PLN : \nRp. ".number_format($total_pln)."\n";
				$cetakan .= "ADMIN BANK : \nRp. ".number_format($admin_charge)."\n";
				$cetakan .= "TOTAL BAYAR : \nRp. ".number_format($total_transaksi)."\n";
				$cetakan .= "NO.REF : \n".$switcher_ref."\n";
				if($sisa_bill > 0){
					$cetakan .= "ANDA MASIH MEMILIKI TUNGGAKAN ".$sisa_bill." BULAN\n";
				}else{
					$cetakan .= "TERIMA KASIH\n";
				}
				$cetakan .= $transaction_code."\n";
				$cetakan .= $transaction_date."\n\n";
			}

			//Log::info($cetakan);
			
			return Response::json(array(
	            'status' => 'Success',
	            'message' => $payment['message'],
	            'response_code' => $payment['response_code'],
	            'cetakan' => $cetakan
	        ),200);

		}else{
			return Response::json(array(
	            'status' => 'Error',
	            'response_code' => $payment['response_code'],
	            'message' => $payment['message']
	        ),200);
		}
	}

	public function InqueryPrepaid($idpel,$token,$username,$sessionid,$imei = ""){

		$idpelLen = strlen($idpel);
		if($idpelLen == 11){
			$flag = "0";
		}else{
			$flag = "1";
		}
		$inquery = PrePaid::prosesInquery($idpel,$flag,true,$username,$token);

		if($inquery['status']){

			$customer = $inquery['customer']['data'];
			$subscriber_id = $customer['subscriber_id'];
			$material_number = $customer['material_number'];
			$nama = $customer['subscriber_name'];
			$gol = $customer['subscriber_segment']." / ".$customer['power_categori'];
			$admin = (int)$customer['admin_charge'];
			$total_bayar = (int)$admin + (int)$token;

			$TampilPel = "";
            $TampilPel .= "<b style='color:#357ca5'>NOMOR METER</b> : <br/>".$material_number."<br/>";
            $TampilPel .= "<b style='color:#357ca5'>NAMA</b> : <br/>".$nama."<br/>";
            $TampilPel .= "<b style='color:#357ca5'>TARIF / DAYA</b> : <br/>".$gol."<br/>";
            $TampilPel .= "<b style='color:#357ca5'>RP TOKEN</b> : <br/>Rp. ".number_format($token)."<br/>";
            $TampilPel .= "<b style='color:#357ca5'>RP ADMIN</b> : <br/>Rp. ".number_format($admin)."<br/>";
            $TampilPel .= "<b style='color:#357ca5'>RP BAYAR</b> : <br/>Rp. ".number_format($total_bayar)."<br/>";

            $TransactionAmount = str_pad($token, 12, "0", STR_PAD_LEFT);

            $payment_message = $inquery['customer']['purchase_message'];
            $payment_message = str_replace("{value_amount}", $TransactionAmount, $payment_message);
    		$payment_message = str_replace("{buying_option}", "0", $payment_message);

    		$advise_message = $inquery['customer']['reversal_message'];
    		$advise_message = str_replace("{value_amount}", $TransactionAmount, $advise_message);

			return Response::json(array(
	            'status' => 'Success',
	            'message' => $inquery['message'],
	            'response_code' => $inquery['response_code'],
	            'purchase_message' => $payment_message,
	            'advise_message' => $advise_message,
	            'inquery' => $TampilPel,
	            'rupiah_token' => $token,
	            'idpel' => $subscriber_id,

	            'nama' => $nama,
	            'sub_total' => $token,
	            'admin' => $admin,
	            'total_bayar' => $total_bayar
	        ),200);
		}else{
			return Response::json(array(
	            'status' => 'Error',
	            'response_code' => $inquery['response_code'],
	            'message' => $inquery['message']
	        ),200);
		}
	}

	public function purchasePrepaid(Request $request){

		$idpel = Request::get('idpel');
		$purchase_message = Request::get('purchase_message');
		$rupiah_bayar = Request::get('rupiah_bayar');
		//$buying_option = Request::get('buying_option');

		$session_id = Request::get('sessionid');
        $username = Request::get('username');
        $imei = Request::get('imei');

        $cetakan = "";

        //Log::info("bayar : " . $rupiah_bayar . " message : ". $purchase_message);

        $purchaseResponse = PrePaid::prosesPurchase($idpel,$purchase_message,$rupiah_bayar,"1",true,$username);
        $response_code = $purchaseResponse['response_code'];
		if($purchaseResponse['status']){

			$purchase = $purchaseResponse['customer'];
			$nomorMeter = $purchase['material_number'];
			$subscriber_id = $purchase['subscriber_id'];
			$subscriber_name = $purchase['subscriber_name'];
			$subscriber_segment = $purchase['subscriber_segment'];
			$power_categori = (int)$purchase['power_categori'];
			$purchase_kwh = $purchase['purchase_kwh'];
			$materai = $purchase['stump_duty'];
			$ppn = $purchase['addtax'];
			$ppj = $purchase['ligthingtax'];
			$angsuran = $purchase['cust_payable'];
			$rp_token = $purchase['power_purchase'];
			$admin_bank = $purchase['admin_charge'];
			$rp_bayar = $purchase['stump_duty'] + $purchase['addtax'] + $purchase['ligthingtax'] + $purchase['cust_payable'] + $purchase['admin_charge'] + $purchase['power_purchase'];
			$no_ref = $purchase['switcher_ref_number'];
			$token = $purchase['token_number'];
			$info = $purchase['info_text'];
			$transaction_code = $purchase['transaction_code'];
			$transaction_date = $purchase['transaction_date'];

			$cetakan .= "PPOB - KOPKAR PEDAMI\n";
			$cetakan .= "STRUK PEMBELIAN LISTRIK PRABAYAR\n\n";

			$cetakan .= "NO METER : \n".$nomorMeter."\n";
			$cetakan .= "IDPEL : \n".$subscriber_id."\n";
			$cetakan .= "NAMA : \n".$subscriber_name."\n";
			$cetakan .= "TARIF / DAYA : \n".$subscriber_segment." / ".$power_categori."VA \n";
			$cetakan .= "MATERAI : \nRp. ".number_format($materai)."\n";
			$cetakan .= "PPN : \nRp. ".number_format($ppn)."\n";
			$cetakan .= "PPJ : \nRp. ".number_format($ppj)."\n";
			$cetakan .= "ANGSURAN : \nRp. ".number_format($angsuran)."\n";
			$cetakan .= "RP. STROOM / TOKEN : \nRp. ".number_format($rp_token)."\n";
			$cetakan .= "ADMIN BANK : \nRp. ".number_format($admin_bank)."\n";
			$cetakan .= "RP. BAYAR : \nRp. ".number_format($rp_bayar)."\n";
			$cetakan .= "NO. REF : \n".$no_ref."\n";
			$cetakan .= "STROOM / TOKEN : \n".$token."\n";
			$cetakan .= $info."\n";
			$cetakan .= $transaction_code."\n";
			$cetakan .= $transaction_date."\n";

			return Response::json(array(
	            'status' => 'Success',
	            'message' => $purchaseResponse['message'],
	            'response_code' => $purchaseResponse['response_code'],
	            'cetakan' => $cetakan
	        ),200);
		}else{
			return Response::json(array(
	            'status' => 'Error',
	            'response_code' => $purchaseResponse['response_code'],
	            'message' => $purchaseResponse['message']
	        ),200);
		}
	}

	public function advisePrepaid(Request $request){
		$idpel = Request::get('idpel');
		$rupiah_bayar = Request::get('rupiah_bayar');
		$advise_message = Request::get('advise_message');
		$counter = Request::get('counter');

		$session_id = Request::get('sessionid');
        $username = Request::get('username');
        $imei = Request::get('imei');

		if($counter > 1){
			$advise_message = '2221'.substr($advise_message, 4);
		}

		$advise = PrePaid::prosesReversal($idpel,$advise_message,$rupiah_bayar,true,$username);

		$cetakan = "";
		if($advise['status']){

			$purchase = $advise['customer'];
			$nomorMeter = $purchase['material_number'];
			$subscriber_id = $purchase['subscriber_id'];
			$subscriber_name = $purchase['subscriber_name'];
			$subscriber_segment = $purchase['subscriber_segment'];
			$power_categori = (int)$purchase['power_categori'];
			$purchase_kwh = $purchase['purchase_kwh'];
			$materai = $purchase['stump_duty'];
			$ppn = $purchase['addtax'];
			$ppj = $purchase['ligthingtax'];
			$angsuran = $purchase['cust_payable'];
			$rp_token = $purchase['power_purchase'];
			$admin_bank = $purchase['admin_charge'];
			$rp_bayar = $purchase['stump_duty'] + $purchase['addtax'] + $purchase['ligthingtax'] + $purchase['cust_payable'] + $purchase['admin_charge'] + $purchase['power_purchase'];
			$no_ref = $purchase['switcher_ref_number'];
			$token = $purchase['token_number'];
			$info = $purchase['info_text'];
			$transaction_code = $purchase['transaction_code'];
			$transaction_date = $purchase['transaction_date'];

			$cetakan .= "PPOB - KOPKAR PEDAMI\n";
			$cetakan .= "STRUK PEMBELIAN LISTRIK PRABAYAR\n\n";

			$cetakan .= "NO METER : \n".$nomorMeter."\n";
			$cetakan .= "IDPEL : \n".$subscriber_id."\n";
			$cetakan .= "NAMA : \n".$subscriber_name."\n";
			$cetakan .= "TARIF / DAYA : \n".$subscriber_segment." / ".$power_categori."VA \n";
			$cetakan .= "MATERAI : \nRp. ".number_format($materai)."\n";
			$cetakan .= "PPN : \nRp. ".number_format($ppn)."\n";
			$cetakan .= "PPJ : \nRp. ".number_format($ppj)."\n";
			$cetakan .= "ANGSURAN : \nRp. ".number_format($angsuran)."\n";
			$cetakan .= "RP. STROOM / TOKEN : \nRp. ".number_format($rp_token)."\n";
			$cetakan .= "ADMIN BANK : \nRp. ".number_format($admin_bank)."\n";
			$cetakan .= "RP. BAYAR : \nRp. ".number_format($rp_bayar)."\n";
			$cetakan .= "NO. REF : \n".$no_ref."\n";
			$cetakan .= "STROOM / TOKEN : \n".$token."\n";
			$cetakan .= $info."\n";
			$cetakan .= $transaction_code."\n";
			$cetakan .= $transaction_date."\n";

			return Response::json(array(
	            'status' => 'Success',
	            'message' => $advise['message'],
	            'response_code' => $advise['response_code'],
	            'cetakan' => $cetakan
	        ),200);
		}else{
			return Response::json(array(
	            'status' => 'Error',
	            'response_code' => $advise['response_code'],
	            'message' => $advise['message']
	        ),200);
		}
	}

	public function InqueryNontaglis($register_number,$username,$sessionid,$imei = ""){

		$inquery = NonTaglis::Inquery($register_number,true,$username);

		if($inquery['status']){
			$register_number = $inquery['customer']['data']['register_number'];
			$transaction_name = $inquery['customer']['data']['transaction_name'];
			$subscriber_name = $inquery['customer']['data']['subscriber_name'];
			$pln_bill_value = $inquery['customer']['data']['pln_bill_value'];
			$admin_charge = $inquery['customer']['data']['admin_charge'];
			$total_transaksi = $pln_bill_value + $admin_charge;

			$TampilPel = "";
            $TampilPel .= "<b style='color:#357ca5'>NOMOR REGISTRASI</b> : <br/>".$register_number."<br/>";
            $TampilPel .= "<b style='color:#357ca5'>JENIS TRANSAKSI</b> : <br/>".$transaction_name."<br/>";
            $TampilPel .= "<b style='color:#357ca5'>NAMA</b> : <br/>".$subscriber_name."<br/>";
            $TampilPel .= "<b style='color:#357ca5'>RP. BAYAR</b> : <br/>Rp. ".number_format($pln_bill_value)."<br/>";
            $TampilPel .= "<b style='color:#357ca5'>ADMIN BANK</b> : <br/>Rp. ".number_format($admin_charge)."<br/>";
            $TampilPel .= "<b style='color:#357ca5'>TOTAL BAYAR</b> : <br/>Rp. ".number_format($total_transaksi)."<br/>";

			return Response::json(array(
	            'status' => 'Success',
	            'message' => $inquery['message'],
	            'response_code' => $inquery['response_code'],
	            'payment_message' => $inquery['customer']['payment_message'],
	            'reversal_message' => $inquery['customer']['reversal_message'],
	            'inquery' => $TampilPel,
	            'total_bayar' => $total_transaksi,
	            'register_number' => $register_number,

	            'nama' => $subscriber_name,
	            'periode' => $transaction_name,
	            'sub_total' => $pln_bill_value,
	            'admin' => $admin_charge
	        ),200);
		}else{
			return Response::json(array(
	            'status' => 'Error',
	            'message' => $inquery['message']
	        ),200);
		}
	}

	public function PaymentNontaglis(Request $request){
		$payment_message = Request::get('payment_message');
		$register_number = Request::get('register_number');
		$total_bayar = Request::get('total_bayar');

		$session_id = Request::get('sessionid');
        $username = Request::get('username');
        $imei = Request::get('imei');

		$payment = NonTaglis::Payment($payment_message,$total_bayar,true,$username,$register_number);

		$cetakan = "";

		$response_code = $payment['response_code'];
		if($payment['status']){
			
			$transaction_name = $payment['customer']['transaction_name'];
			$register_number = $payment['customer']['register_number'];
			$registration_date = $payment['customer']['registration_date'];
			$subscriber_name = $payment['customer']['subscriber_name'];
			$subscriber_id = $payment['customer']['subscriber_id'];
			$pln_bill_value = $payment['customer']['pln_bill_value'];
			$admin_charge = $payment['customer']['admin_charge'];
			$total_bayar = $pln_bill_value + $admin_charge;

			$switcher_ref = $payment['customer']['switcher_ref_number'];
			$transaction_code = $payment['customer']['transaction_code'];
			$transaction_date = $payment['customer']['transaction_date'];

			$cetakan .= "PPOB - KOPKAR PEDAMI\n";
			$cetakan .= "STRUK NON TAGIHAN LISTRIK\n\n";

			$cetakan .= "TRANSAKSI : \n".$transaction_name."\n";
			$cetakan .= "NO REGISTRASI : \n".$register_number."\n";
			$cetakan .= "TGL REGISTRASI : \n".$registration_date."\n";
			$cetakan .= "NAMA : \n".$subscriber_name."\n";
			$cetakan .= "IDPEL : \n".$subscriber_id."\n";
			$cetakan .= "BIAYA PLN : \nRp. ".number_format($pln_bill_value)."\n";
			$cetakan .= "ADMIN BANK : \nRp. ".number_format($admin_charge)."\n";
			$cetakan .= "TOTAL BAYAR : \nRp. ".number_format($total_bayar)."\n";
			$cetakan .= "NO.REF : \n".$switcher_ref."\n";
			$cetakan .= $transaction_code."\n";
			$cetakan .= $transaction_date."\n";
			
			return Response::json(array(
	            'status' => 'Success',
	            'message' => $payment['message'],
	            'response_code' => $payment['response_code'],
	            'cetakan' => $cetakan
	        ),200);

		}else{
			return Response::json(array(
	            'status' => 'Error',
	            'response_code' => $payment['response_code'],
	            'message' => $payment['message']
	        ),200);
		}
	}

	public function ReversalNontaglis(Request $request){
		$reversal_message = Request::get('reversal_message');
		$register_number = Request::get('register_number');
		$number_request = Request::get('number_request');

		$session_id = Request::get('sessionid');
        $username = Request::get('username');
        $imei = Request::get('imei');

		if($number_request > 1){
			$reversal_message = '2401'.substr($reversal_message, 4);
		}

		$payment = NonTaglis::Reversal($reversal_message,true,$username,$register_number);

		$cetakan = "";
		$response_code = $payment['response_code'];
		if($response_code == '0012'){
			
			$transaction_name = $payment['customer']['transaction_name'];
			$register_number = $payment['customer']['register_number'];
			$registration_date = $payment['customer']['registration_date'];
			$subscriber_name = $payment['customer']['subscriber_name'];
			$subscriber_id = $payment['customer']['subscriber_id'];
			$pln_bill_value = $payment['customer']['pln_bill_value'];
			$admin_charge = $payment['customer']['admin_charge'];
			$total_bayar = $pln_bill_value + $admin_charge;

			$switcher_ref = $payment['customer']['switcher_ref_number'];
			$transaction_code = $payment['customer']['transaction_code'];
			$transaction_date = $payment['customer']['transaction_date'];

			$cetakan .= "PPOB - KOPKAR PEDAMI\n";
			$cetakan .= "STRUK NON TAGIHAN LISTRIK\n\n";

			$cetakan .= "TRANSAKSI : \n".$transaction_name."\n";
			$cetakan .= "NO REGISTRASI : \n".$register_number."\n";
			$cetakan .= "TGL REGISTRASI : \n".$registration_date."\n";
			$cetakan .= "NAMA : \n".$subscriber_name."\n";
			$cetakan .= "IDPEL : \n".$subscriber_id."\n";
			$cetakan .= "BIAYA PLN : \nRp. ".number_format($pln_bill_value)."\n";
			$cetakan .= "ADMIN BANK : \nRp. ".number_format($admin_charge)."\n";
			$cetakan .= "TOTAL BAYAR : \nRp. ".number_format($total_bayar)."\n";
			$cetakan .= "NO.REF : \n".$switcher_ref."\n";
			$cetakan .= $transaction_code."\n";
			$cetakan .= $transaction_date."\n";

			return Response::json(array(
	            'status' => 'Success',
	            'message' => $payment['message'],
	            'response_code' => $payment['response_code'],
	            'cetakan' => $cetakan
	        ),200);
		}else{
			return Response::json(array(
	            'status' => 'Error',
	            'response_code' => $payment['response_code'],
	            'message' => $payment['message']
	        ),200);
		}

		return Response::json($payment,200);
	}
}
