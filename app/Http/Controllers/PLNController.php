<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use App\PlnServices\PostPaid;
use App\PlnServices\PostPaidNew;
use App\Models\PlnTransaksi;
use Illuminate\Support\Facades\Auth;
use App\Models\mLoket;
use DB;

class PLNController extends Controller
{
    public function __construct()
    {
    	$this->middleware('auth');
    	//$this->middleware('akses_pln');
    }

	public function index()
	{
		return view('admin.pln');
	}

	public function InqueryTagihanPostPaid($nomor_pelanggan){

		$inquery = PostPaid::InqueryTagihanPostPaid($nomor_pelanggan,false,"");

		return Response::json($inquery,200);
	}

	public function PaymentTagihanPostPaid(Request $request){

		$payment_message = $request->input('payment_message');
		$subcriber_id = $request->input('subcriber_id');
		$total_bayar = $request->input('total_bayar');

		$cek = substr($payment_message,0,7);
		if($cek == "LUNASIN"){
			$payment_message = str_replace("LUNASIN","",$payment_message);
			$payment = PostPaidNew::PaymentTagihanPostPaid($subcriber_id,$payment_message,$total_bayar,false,"");
		}else{
			$payment = PostPaid::PaymentTagihanPostPaid($subcriber_id,$payment_message,$total_bayar,false,"");
		}

		return Response::json($payment,200);
	}

	public function ReversalTagihanPostPaid(Request $request){
		
		$payment_message = $request->input('payment_message');
		$subcriber_id = $request->input('subcriber_id');
		$number_request = $request->input('number_request');

		if($number_request > 1){
			$payment_message = '2401'.substr($payment_message, 4);
		}

		$payment = PostPaid::ReversalTagihanPostPaid($subcriber_id,$payment_message,false,"");

		return Response::json($payment,200);
	}

	public function CetakTagihanPostPaid($nomor_pelanggan,$tgl_awal,$tgl_akhir){
		//$tanggal = date("Y-m-d");
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
                'data' => $mData
            );
		}else{
			return array(
                'status' => false,
                'message' => 'Transaksi tidak ditemukan.'
            );
		}
		
	}


}
