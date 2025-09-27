<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use App\PlnServices\NonTaglis;
use Illuminate\Support\Facades\Auth;
use App\Models\PlnNontaglisTransaksi;
use App\Models\mLoket;
use DB;

class PlnNontagController extends Controller
{
	public function __construct()
    {
    	$this->middleware('auth');
    	//$this->middleware('akses_pln');
    }

	public function index()
	{
		return view('admin.pln_nontaglis');
	}

    public function Inquery($register_number){

		$inquery = NonTaglis::Inquery($register_number,false,"");

		return Response::json($inquery,200);
	}

	public function Payment(Request $request){

		$payment_message = $request->input('payment_message');
		$register_number = $request->input('register_number');
		$total_bayar = $request->input('total_bayar');

		$payment = NonTaglis::Payment($payment_message,$total_bayar,false,"",$register_number);

		return Response::json($payment,200);
	}

	public function Reversal(Request $request){
		
		$reversal_message = $request->input('reversal_message');
		$register_number = $request->input('register_number');
		$number_request = $request->input('number_request');

		if($number_request > 1){
			$reversal_message = '2401'.substr($reversal_message, 4);
		}

		$reversal = NonTaglis::Reversal($reversal_message,false,"",$register_number);

		return Response::json($reversal,200);
	}

	public function CetakUlang($register_number,$tgl_awal,$tgl_akhir){
		$tanggal = date("Y-m-d");
		$Transaksi = PlnNontaglisTransaksi::whereBetween(DB::raw('cast(transaction_date as date)'),[$tgl_awal, $tgl_akhir])
			->where('register_number','=',$register_number);

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
                'message' => 'Cetak Ulang IDPEL '. $register_number,
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
