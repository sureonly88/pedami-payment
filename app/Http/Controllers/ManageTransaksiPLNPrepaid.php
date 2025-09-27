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
use Illuminate\Support\Facades\Auth;
use App\Models\PlnPrepaidTransaksi;

class ManageTransaksiPLNPrepaid extends Controller
{

	public function __construct()
    {
    	$this->middleware('is_admin');
    }

	public function index()
	{
		return view('admin.man_pln_prepaid')->with('user', Helpers::getLoginDetail());
	}

	public function simpanData(){
		try{

			$Data = Input::all()['Data'];
			$username = Auth::user()->username;
			
			//Edit Validator Here
			$rules = array(
		        'subscriber_id' => 'required','material_number' => 'required','subscriber_name' => 'required','token_number' => 'required'
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
		    		$Data['updated_at'] = date('Y-m-d H:i:s');
		    		DB::table('transaksi_pln_prepaid')->where('id',$Id)->update($Data);
		    	}else{
		    		unset($Data['id']);
		    		$Data['created_at'] = date('Y-m-d H:i:s');
					$Data['updated_at'] = date('Y-m-d H:i:s');
		    		DB::table('transaksi_pln_prepaid')->insert($Data);
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
			$cekId = DB::table('transaksi_pln_prepaid')->where('id',$Id)->first();

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
        $mTotalData = DB::table('transaksi_pln_prepaid')->count();
        $mTotalFiltered = $mTotalData;

        try{

            if(!empty($requestData['search']['value'])){
                $mTotalFiltered = DB::table('transaksi_pln_prepaid')->where("subcriber_id","like","%".$requestData['search']['value']."%")
                    ->orWhere("subcriber_name","like","%".$requestData['search']['value']."%")
                    ->count();

                $mUsers = DB::table('transaksi_pln_prepaid')->where("subcriber_id","like","%".$requestData['search']['value']."%")
                    ->orWhere("subcriber_name","like","%".$requestData['search']['value']."%")
                    ->select('*',DB::raw("'' as aksi"))
                    ->orderBy('transaction_date','desc')
                    ->offset($start)
                    ->limit($length)
                    ->get();
            }else{
                $mUsers = DB::table('transaksi_pln_prepaid')->select('*',DB::raw("'' as aksi"))
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

			$Pln = PlnPrepaidTransaksi::find($Id);
			$Pln->flag_transaksi = "cancel";

			$Pln->save();

			// DB::table('transaksi_pln_prepaid')
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
