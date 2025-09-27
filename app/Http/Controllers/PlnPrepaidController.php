<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use App\PlnServices\PrePaid;
use App\PlnServices\PrePaidNew;
use App\Models\PlnPrepaidTransaksi;
use Illuminate\Support\Facades\Log;
use App\Models\ManualPrepaid;
use App\Models\mLoket;
use App\Models\AdvisePDAM;
use App\Models\AdviseLunasin;
use Illuminate\Support\Facades\Auth;
use DB;

class PlnPrepaidController extends Controller
{
	public function __construct()
    {
    	$this->middleware('auth');
    	//$this->middleware('akses_pln');
    }
    
    public function index()
	{
		return view('admin.pln_prepaid');
	}

	public function viewCetakUlang(){
		return view('admin.pln_prepaid_cu');
	}

	public function viewManual(){
		$users = DB::table('users')
			->leftJoin('lokets', 'users.loket_id', '=', 'lokets.id')
			->select('users.id','users.username','lokets.nama','lokets.jenis')
			->orderBy('users.username')->get();
		return view('admin.pln_prepaid_manual')->with('users', $users);
	}

	public function InqueryTagihanPrePaid($idpel){

		$idpelLen = strlen($idpel);
		if($idpelLen == 11){
			$flag = "0";
		}else{
			$flag = "1";
		}
		$inqueryResponse = PrePaid::prosesInquery($idpel,$flag,false,"");

		return Response::json($inqueryResponse,200);
	}

	public function PaymentTagihanPrePaid(Request $request){

		$idpel = $request->input('idpel');
		$payment_message = $request->input('payment_message');
		$rupiah_token = $request->input('rupiah_token');
		$buying_option = $request->input('buying_option');

		//return $idpel."-".$payment_message."-".$rupiah_token."-".$buying_option;

		$cek = substr($payment_message,0,7);
		if($cek == "LUNASIN"){
			$payment_message = str_replace("LUNASIN","",$payment_message);
			$purchaseResponse = PrePaidNew::prosesPurchase($idpel,$payment_message,$rupiah_token,$buying_option,false,"");
		}else{
			$purchaseResponse = PrePaid::prosesPurchase($idpel,$payment_message,$rupiah_token,$buying_option,false,"");
		}

		//$purchaseResponse = PrePaid::prosesPurchase($idpel,$payment_message,$rupiah_token,$buying_option,false,"");

		return Response::json($purchaseResponse,200);
	}

	public function ReversalTagihanPrePaid(Request $request){

		$idpel = $request->input('idpel');
		$rupiah_token = $request->input('rupiah_token');
		$reversal_message = $request->input('reversal_message');
		$counter = $request->input('counter');

		if($counter > 1){
			$reversal_message = '2221'.substr($reversal_message, 4);
		}

		//Log::info("Advise : " . $reversal_message);

		$payment = PrePaid::prosesReversal($idpel,$reversal_message,$rupiah_token,false,"");

		return Response::json($payment,200);
	}

	public function getManualPrepaid(){
		$manual = AdviseLunasin::whereNull('deleted_at');

		$pdam = AdvisePDAM::whereNull('deleted_at')->union($manual)->get();

		return array(
            'status' => true,
            'message' => '-',
            'data' => $pdam
        );
	}

	public function CetakTagihanPrePaid($idpel){
		$tanggal = date("Y-m-d");

		$idpelLen = strlen($idpel);

		if($idpelLen == 11){
			$Transaksi = PlnPrepaidTransaksi::where('material_number','=',$idpel);
		}else{
			$Transaksi = PlnPrepaidTransaksi::where('subscriber_id','=',$idpel);
		}

		if (!Auth::user()->hasPermissionTo('Cetak Ulang Semua')) {
            $idLoket = Auth::user()->loket_id;
            $kodeLoket = mLoket::where("id",$idLoket)->first()->loket_code;

            $Transaksi = $Transaksi->where('loket_code',$kodeLoket)->orderBy('transaction_date','desc')->limit(30)->get();
        }else{
            $Transaksi = $Transaksi->orderBy('transaction_date','desc')->limit(30)->get();
        }

		if($Transaksi->count() > 0){

			$mToken = array();
			foreach ($Transaksi as $Trans) {
				$mData['id'] = $Trans->id;
				$mData['material_number'] = $Trans->material_number;
				$mData['admin_charge'] = $Trans->admin_charge ;
				$mData['subscriber_id'] = $Trans->subscriber_id;
				$mData['stump_duty'] = $Trans->stump_duty;
				$mData['subscriber_name'] = $Trans->subscriber_name;
				$mData['addtax'] = $Trans->addtax;
				$mData['subscriber_segment'] = $Trans->subscriber_segment;
				$mData['power_categori'] = $Trans->power_categori;
				$mData['ligthingtax'] = $Trans->ligthingtax;
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
                'message' => 'Cetak Ulang IDPEL '. $idpel,
                'data' => $mToken
            );
		}else{
			return array(
                'status' => false,
                'message' => 'Transaksi tidak ditemukan.'
            );
		}
		
	}
}
