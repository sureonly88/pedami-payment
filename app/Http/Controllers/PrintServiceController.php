<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrintModel;
use Response;
use Illuminate\Support\Facades\Storage;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Services\PrintBaruV1;

class PrintServiceController extends Controller
{
    public function __construct()
    {

    }

    public function insertQueue(Request $request){
        $username = $request->get('username');

        $arrRek = json_decode($request->get('rek'),true);
        $jenisKertas = $request->get('kertas');
        $layanan = $request->get('layanan');

        PrintBaruV1::handlePrintBaru($username,$arrRek, $jenisKertas, $layanan);

        return Response::json(array(
            'status' => true,
            'message' => "Berhasil"
        ),200);

    }

    public function getPrinterQueue($username){
        //$cetak = Storage::get('prints/pln_postpaid4.struk');

    	$cekQueue = PrintModel::where('username',$username)->where('is_printed',0)->get();
    	if($cekQueue->count() > 0){

    		PrintModel::where('username',$username)
    			->where('is_printed',0)
    			->update(['is_printed' => 1]);
    			
    		return Response::json(array(
	                'status' => true,
	                'message' => "Berhasil",
	                'data' => $cekQueue->toArray()
	            ),200);
    	}else{
    		return Response::json(array(
	                'status' => false,
	                'message' => "Data tidak ada",
	            ),200);
    	}
    }

    public function loginPrinter(Request $request){
        $username = $request->get('username');
        $password = $request->get('password');

        //dd($password);

        $userdata = array(
            'username' => $username,
            'password' => $password
        );

        //$password =  Hash::make($password);

        //$cekLogin = User::where('username',$username)->where('password',$password)->get();
        if (Auth::validate($userdata)) {
            return Response::json(array(
                'status' => true,
                'message' => "Login Success",
                'username' => $username
            ),200);
        }else{
            return Response::json(array(
                'status' => false,
                'message' => "Username atau Password salah.",
            ),200);
        }
    }
}
