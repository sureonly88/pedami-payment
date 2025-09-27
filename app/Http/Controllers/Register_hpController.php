<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use App\Models\Register_hp;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Input;
use Validator;
use Response;

class Register_hpController extends Controller
{

	public function __construct()
    {
        $this->middleware('is_admin');
        //$this->middleware('auth');
    }

	public function index()
	{
		$allUsers = User::all();

		return view('admin.man_handphone')
			->with('users', $allUsers);
	}

	public function updateUser($Id){
		try{

			$Data = Input::all()['Data'];

			$rules = array(
		        'username' => 'required',
		        'imei' => 'required',
		    );

		    $validator = Validator::make($Data, $rules);

		    if ($validator->fails()) {
		    	return Response::json(array(
					'status' => 'Error',
					'message' => $validator->errors()->all()
				),200);
		    }else{

	    		DB::table('register_hps')->where('id',$Id)->update($Data);

				return Response::json(array(
					'status' => 'Success',
					'message' => 'Update Berhasill',
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

	public function simpanUser(){
		try{

			$Data = Input::all()['Data'];
			$rules = array(
		        'username' => 'required',
		        'imei' => 'required'
		    );

		    $validator = Validator::make($Data, $rules);

		    if ($validator->fails()) {
		    	return Response::json(array(
					'status' => 'Error',
					'message' => $validator->errors()->all()
				),200);
		    }else{

		    	DB::table('register_hps')->insert($Data);

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
			$cekId = DB::table('register_hps')->where('id',$Id)->first();

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
			$mUsers = DB::table('register_hps')
				->leftJoin('users','register_hps.username','=','users.username')
				->select('register_hps.id','users.username','register_hps.imei',DB::raw("'' as aksi"))->get();

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
			DB::table('register_hps')
	            ->where('id',$Id)
	            ->delete();
	        return Response::json(array(
				'status' => 'Success',
				'message' => 'User sudah di hapus.'
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
