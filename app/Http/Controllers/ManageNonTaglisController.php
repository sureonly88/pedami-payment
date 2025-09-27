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
use App\Models\PlnNontaglisTransaksi;

class ManageNonTaglisController extends Controller
{

	public function __construct()
    {
        $this->middleware('is_admin');
    }

	public function index()
	{
		return view('admin.man_nontaglis')->with('user', Helpers::getLoginDetail());
	}

	public function simpanData(){
		try{

			$Data = Input::all()['Data'];
			$username = Auth::user()->username;
			
			//Edit Validator Here
			$rules = array(
		        'register_number' => 'required',
		        'transaction_code' => 'required'
		    );

		    $validator = Validator::make($Data, $rules);

		    if ($validator->fails()) {
		    	return Response::json(array(
					'status' => 'Error',
					'message' => $validator->errors()->all()
				),200);
		    }else{

				$Id = $Data['id'];

		    	if($Id){
		    		$Data['updated_at'] = date('Y-m-d H:i:s');
		    		DB::table('transaksi_pln_nontaglis')->where('id',$Id)->update($Data);
		    	}else{
		    		unset($Data['id']);
		    		$Data['created_at'] = date('Y-m-d H:i:s');
					$Data['updated_at'] = date('Y-m-d H:i:s');
		    		DB::table('transaksi_pln_nontaglis')->insert($Data);
		    	}

				return Response::json(array(
					'status' => 'Success',
					'message' => 'Simpan Berhasil',
					'data' => Input::all()
				),200);
		    }

		}catch (Exception $e){
			$error = explode("\r\n",$e->getMessage());
            return Response::json(array(
                'status' => 'Error',
                'message' => array($error),
            ),200);
		}
	}

	public function getEdit($Id){
		try{
			$cekId = DB::table('transaksi_pln_nontaglis')->where('id',$Id)->first();

			return Response::json(array(
				'status' => 'Success',
				'message' => '-',
				'data' => $cekId,
			),200);
		}catch (Exception $e){

			$error = explode("\r\n",$e->getMessage());
            return Response::json(array(
                'status' => 'Error',
                'message' => $error,
                'data' => ''
            ),200);
		}
	}
	
	public function getList(){
		try{
			$mUsers = DB::table('transaksi_pln_nontaglis')
				->select('transaksi_pln_nontaglis.*',DB::raw("'' as aksi"))
				->get();

			return Response::json(array(
				'status' => 'Success',
				'message' => '-',
				'data' => $mUsers
			),200);
		}catch (Exception $e){
			$error = explode("\r\n",$e->getMessage());
            return Response::json(array(
                'status' => 'Error',
                'message' => $error
            ),200);
		}
	}

	public function deleteData($Id){
		try{
			$Pln = PlnNontaglisTransaksi::find($Id);
			$Pln->flag_transaksi = "cancel";
			$Pln->save();
				
	        return Response::json(array(
				'status' => 'Success',
				'message' => 'Data sudah di hapus.'
			),200);
			
		}catch (Exception $e){
			$error = explode("\r\n",$e->getMessage());
            return Response::json(array(
                'status' => 'Error',
                'message' => $error
            ),200);
		}
	}

}
