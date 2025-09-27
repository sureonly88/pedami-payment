<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Input;
use Validator;
use Response;
use App\Models\PlnTransaksi;
use Illuminate\Support\Facades\Auth;

class ManageTransaksiPLN extends Controller
{

	public function __construct()
    {
    	$this->middleware('is_admin');
        //$this->middleware('admin_check');
        //$this->middleware('auth');
    }

	public function index()
	{
		return view('admin.man_transaksi_pln')->with('user', Helpers::getLoginDetail());
	}

	public function simpanData(){
		try{

			$Data = Input::all()['Data'];
			$username = Auth::user()->username;
			
			//Edit Validator Here
			$rules = array(
		        'subcriber_id' => 'required','subcriber_name' => 'required','bill_periode' => 'required'
		    );

		    $validator = Validator::make($Data, $rules);

		    if ($validator->fails()) {
		    	return Response::json(array(
					'status' => 'Error',
					'message' => $validator->errors()->all()
				),200);
		    }else{

		    	//$Data['user'] = $username;
				$Id = $Data['id'];

		    	if($Id){

		    		$Data['idpelblth'] = $Data['subcriber_id'].$Data['bill_periode'];
		    		$Data['updated_at'] = date('Y-m-d H:i:s');
		    		DB::table('transaksi_pln')->where('id',$Id)->update($Data);

		    	}else{
		    		unset($Data['id']);

		    		$Data['idpelblth'] = $Data['subcriber_id'].$Data['bill_periode'];
		    		
		    		$Data['created_at'] = date('Y-m-d H:i:s');
					$Data['updated_at'] = date('Y-m-d H:i:s');
		    		DB::table('transaksi_pln')->insert($Data);

		    	}

				return Response::json(array(
					'status' => 'Success',
					'message' => 'Simpan Berhasil',
					'data' => Input::all()
				),200);
		    }

		}catch (\Exception $e){
			$error = explode("\r\n",$e->getMessage());
            return Response::json(array(
                'status' => 'Error',
                'message' => array($error),
            ),200);
		}
	}

	public function getEdit($Id){
		try{
			$cekId = DB::table('transaksi_pln')->where('id',$Id)->first();

			return Response::json(array(
				'status' => 'Success',
				'message' => '-',
				'data' => $cekId,
			),200);
		}catch (\Exception $e){

			$error = explode("\r\n",$e->getMessage());
            return Response::json(array(
                'status' => 'Error',
                'message' => $error,
                'data' => ''
            ),200);
		}
	}
	
	public function getList(){

		$requestData = $_REQUEST;

        $start = $requestData['start'];
        $length = $requestData['length'];
        $mTotalData = DB::table('transaksi_pln')->count();
        $mTotalFiltered = $mTotalData;

        try{

            if(!empty($requestData['search']['value'])){
                $mTotalFiltered = DB::table('transaksi_pln')->where("subcriber_id","like","%".$requestData['search']['value']."%")
                    ->orWhere("subcriber_name","like","%".$requestData['search']['value']."%")
                    ->count();

                $mUsers = DB::table('transaksi_pln')->where("subcriber_id","like","%".$requestData['search']['value']."%")
                    ->orWhere("subcriber_name","like","%".$requestData['search']['value']."%")
                    ->select('*',DB::raw("'' as aksi, 0 as bill_pln, 0 as total"))
                    ->orderBy('transaction_date','desc')
                    ->offset($start)
                    ->limit($length)
                    ->get();
            }else{
                $mUsers = DB::table('transaksi_pln')->select('*',DB::raw("'' as aksi, 0 as bill_pln, 0 as total"))
                    ->orderBy('transaction_date','desc')
                    ->offset($start)
                    ->limit($length)
                    ->get();
            }

            return Response::json(array(
                'status' => 'Success',
                'message' => '-',
                'draw' => $requestData['draw'],
                'recordsTotal' => $mTotalData,
                'recordsFiltered' => $mTotalFiltered,
                'data' => $mUsers
            ),200);
            
        }catch (\Exception $e){
            $error = explode("\r\n",$e->getMessage());
            return Response::json(array(
                'status' => 'Error',
                'message' => $error
            ),200);
        }
	}

	public function deleteData($Id){
		try{
			$Pln = PlnTransaksi::find($Id);
			$DelID = $Pln->idpelblth."-".$Id;

			$Pln->flag_transaksi = "cancel";
			$Pln->idpelblth = $DelID;

			$Pln->save();

			// DB::table('transaksi_pln')
	  //           ->where('id',$Id)
	  //           ->delete();
				
	        return Response::json(array(
				'status' => 'Success',
				'message' => 'Data sudah di hapus.'
			),200);
			
		}catch (\Exception $e){
			$error = explode("\r\n",$e->getMessage());
            return Response::json(array(
                'status' => 'Error',
                'message' => $error
            ),200);
		}
	}

}
