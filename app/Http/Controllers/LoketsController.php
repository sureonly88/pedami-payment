<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Lokets;
use Response;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Input;
use Validator;

class LoketsController extends Controller
{
	public function __construct()
    {
        $this->middleware('is_admin');
        //$this->middleware('auth');
    }

	public function getLokets(){
		$Lokets = Lokets::where('jenis','ANDROID')->get(['id','loket_code','nama']);
		$numLokets = $Lokets->count();
        if($numLokets > 0){
            return Response::json(array(
                'status' => 'Success',
                'message' => '-',
                'data' => $Lokets->toArray(),
            ),200);

        }else{
            return Response::json(array(
                'status' => 'Error',
                'message' => 'Tidak ada data loket',
                'data' => ''
            ),200);
        }
	}

	public function index()
	{
		$lokets = Lokets::all();

		return view('admin.man_lokets');
	}

	public function updateUser($Id){
		try{

			$Data = Input::all()['Data'];

			$rules = array(
		        'nama' => 'required',
		        'alamat' => 'required',
		        'loket_code' => 'required',
		    );

		    $validator = Validator::make($Data, $rules);

		    if ($validator->fails()) {
		    	return Response::json(array(
					'status' => 'Error',
					'message' => $validator->errors()->all()
				),200);
		    }else{

	    		DB::table('lokets')->where('id',$Id)->update($Data);

				return Response::json(array(
					'status' => 'Success',
					'message' => 'Update Berhasil',
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

	public function messages()
	{
	    return [
	        'loket_code.required' => 'Kode Loket tidak boleh kosong',
	    ];
	}

	public function simpanUser(){
		try{

			//return Input::all();

			$Data = Input::all()['Data'];
			$rules = array(
		        'nama' => 'required|unique:lokets',
		        'alamat' => 'required',
		        'loket_code' => 'required|unique:lokets|between:3,10',
		    );

		    $customMessages = [
		    	'nama.required' => 'Nama tidak boleh kosong.',
		    	'nama.unique' => 'Nama Loket sudah ada.',
		        'loket_code.required' => 'Kode Loket tidak boleh kosong.',
		        'loket_code.between' => 'Panjang Kode Loket harus :min - :max.',
		        'loket_code.unique' => 'Kode Loket sudah ada.',
		        'alamat.required' => 'Alamat tidak boleh kosong',
		    ];

		    $validator = Validator::make($Data, $rules, $customMessages);

		    if ($validator->fails()) {
		    	return Response::json(array(
					'status' => 'Error',
					'message' => $validator->errors()->all()
				),200);
		    }else{

	    		unset($Data['id']);
		    	DB::table('lokets')->insert($Data);

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

	public function getUserEdit($Id){
		try{
			$cekId = DB::table('lokets')->where('id',$Id)->first();

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
	
	public function getUsers(){
		try{
			$mUsers = DB::table('lokets')->select('lokets.*',DB::raw("'' as aksi"))->get();

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

	public function deleteUser($Id){
		try{

			$cekLoket = DB::table("users")->where('loket_id',$Id)->get();
			if(count($cekLoket)>0 ){
				return Response::json(array(
	                'status' => 'Error',
	                'message' => 'Loket tidak bisa dihapus karena dipakai'
	            ),200);
			}else{
				DB::table('lokets')
		            ->where('id',$Id)
		            ->delete();
		        return Response::json(array(
					'status' => 'Success',
					'message' => 'User sudah di hapus.'
				),200);
			}	
			
		}catch (Exception $e){
			$error = explode("\r\n",$e->getMessage());
            return Response::json(array(
                'status' => 'Error',
                'message' => $error
            ),200);
		}
	}

}