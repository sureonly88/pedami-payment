<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Response;
use App\Models\SettingRekPdam;
use Validator;

class AksesRekPdamController extends Controller
{
    public function __construct()
    {
        $this->middleware('is_admin');
    }

    public function index()
    {
    	$lokets = DB::table('lokets')
    			->select('id','loket_code','nama')->get();

        return view('admin.akses_rek_pdam')->with('lokets',$lokets);
    }

    public function getLoket($loket_code)
    {
    	try{
    		$user = DB::table('lokets')
    			->Join('setting_rek_pdam','lokets.loket_code','=','setting_rek_pdam.loket_code')
    			->where('lokets.loket_code',$loket_code)
    			->select('lokets.loket_code','lokets.nama','setting_rek_pdam.jml_rek_pdam')
    			->first();
    		if($user){
    			return Response::json(array(
	                'status' => true,
	                'message' => "-",
	                'loket_code' =>  $user->loket_code,
	                'jml_rek_pdam' => $user->jml_rek_pdam
	            ),200);
    		}else{
    			return Response::json(array(
	                'status' => false,
	                'message' => "JML REKENING BELUM DISETTING"
	            ),200);
    		}
    	}catch (\Exception $e) {
	        return Response::json(array(
                'status' => false,
                'message' => "ERROR GET LOKET SETTING"
            ),200);
	    }
    }

    public function simpan(Request $request){

    	$loket_code = $request->input('loket_code');
    	$jml_rek = $request->input('jml_rek');

    	$rules = array(
	        'loket_code' => 'required',
	        'jml_rek' => 'required|numeric',
	    );

	    $validator = Validator::make(array("loket_code" => $loket_code, "jml_rek" => $jml_rek), $rules);

	    if ($validator->fails()) {
	    	return Response::json(array(
				'status' => false,
				'response_code' => "0005",
				'message' => "ISIAN TIDAK VALID"
			),200);
		}

    	try{

            $loket = SettingRekPdam::where('loket_code','=',$loket_code)->first();

            if($loket){
            	$loket->jml_rek_pdam = $jml_rek;
            }else{
            	$loket = new SettingRekPdam();
            	$loket->loket_code = $loket_code;
            	$loket->jml_rek_pdam = $jml_rek;
            }

            $loket->save();

            return Response::json(array(
                'status' => true,
                'response_code' => "0000",
                'message' => "DATA SUDAH TERSIMPAN",
                'loket_code' => $loket_code,
                'jml_rek_pdam' => $jml_rek
            ),200);
           
    	}catch (\Exception $e) {
	        return Response::json(array(
                'status' => false,
                'response_code' => "0005",
                'message' => "ERROR SIMPAN"
            ),200);
	    }
	}
}
