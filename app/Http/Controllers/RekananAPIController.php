<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\PdambjmEvent;
use App\APIRekanan\PdambjmRkn;
use Illuminate\Support\Facades\Input;
use Response;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Hash;
use DB;
use Illuminate\Support\Facades\Auth;

class RekananAPIController extends Controller
{
    public function inquery($idpel,Request $request){

        $api_token = $request->header('api-token');
        //dd($api_token);

        $Response = PdambjmRkn::inqueryPelanggan($idpel,$api_token);

        return $Response;
    }


    public function payment(Request $request){

        $payment_data = $request->input('payment_data');

        try {
            $payment_data = decrypt($payment_data);
        } catch (DecryptException $e) {
            return Response::json(array(
                'status' => false,
                'response_code' => '0008',
                'message' => "INVALID PAYMENT MESSAGE"
            ),500);
        }

        $api_token = $request->header('api-token');

        $Response = PdambjmRkn::requestPayment($payment_data,$api_token);

        return $Response;
    }

    public function sisaSaldo(Request $request){
        try{

            $api_token = $request->header('api-token');

            $user = DB::table('users')
                ->leftJoin('lokets','users.loket_id','=','lokets.id')
                ->where('users.api_token',$api_token)
                ->select('users.username','lokets.loket_code','lokets.nama','lokets.pulsa')
                ->first();

            return Response::json(array(
                'status' => true,
                'response_code' => '0000',
                'message' => 'REQUEST SISA SALDO',
                'data' => array(
                        'kode_loket' => $user->loket_code,
                        'nama_loket' => $user->nama,
                        'sisa_saldo' => $user->pulsa
                    )
            ),200);


        }catch(\Exception $e){
            $error = explode("\r\n",$e->getMessage());
            return Response::json(array(
                'status' => false,
                'response_code' => '0005',
                'message' => "ERROR OTHER"
            ),500);
        }

    }
}
