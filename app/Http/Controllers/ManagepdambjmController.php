<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use App\Models\Lokets;
use App\User;
use Illuminate\Support\Facades\Input;
use Validator;
use App\Models\Managepdambjm;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Response;

class ManagepdambjmController extends Controller
{

	public function __construct()
    {
        $this->middleware('is_admin');
        //$this->middleware('auth');
    }

	public function index()
	{
		$lokets = Lokets::all();
		$users = User::select('username')->get();

		return view('admin.man_pdambjm')
			->with('list_users',$users);
	}

	public function updateUser($Id){
		try{

			$Data = Input::all()['Data'];

			$rules = array(
		        'transaction_code' => 'required',
		        'cust_id' => 'required',
		        'blth' => 'required',
		        'loket_name' => 'required',
		        'loket_code' => 'required',
		        'transaction_date' => 'required',
		        'jenis_loket' => 'required'
		    );

		    $validator = Validator::make($Data, $rules);

		    if ($validator->fails()) {
		    	return Response::json(array(
					'status' => 'Error',
					'message' => $validator->errors()->all()
				),200);
		    }else{

	    		DB::table('pdambjm_trans')->where('id',$Id)->update($Data);

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

	public function simpanUser(){
		try{

			//return Input::all();

			$Data = Input::all()['Data'];
			$rules = array(
		        'transaction_code' => 'required|unique:pdambjm_trans',
		        'cust_id' => 'required',
		        'blth' => 'required',
		        'loket_name' => 'required',
		        'loket_code' => 'required',
		        'transaction_date' => 'required',
		        'jenis_loket' => 'required'
		    );

		    $validator = Validator::make($Data, $rules);

		    if ($validator->fails()) {
		    	return Response::json(array(
					'status' => 'Error',
					'message' => $validator->errors()->all()
				),200);
		    }else{

		    	unset($Data['Id']);
		    	DB::table('pdambjm_trans')->insert($Data);

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

	public function getInfoLoket($username){
		try{
			$cekId = DB::table('users')
				->leftJoin('lokets','users.loket_id','=','lokets.id')
				->where('users.username',$username)
				->select('users.username','lokets.loket_code','lokets.nama')
				->first();

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

	public function getUserEdit($Id){
		try{
			$cekId = DB::table('pdambjm_trans')->where('id',$Id)->first();

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
	
	public function getUsers($value){
		$requestData = $_REQUEST;
		// $columns  array(
		// 	0 => 'id',
		// 	1 => 'transaction_date',
		// 	2 => 'transaction_code'
		// );
		$start = $requestData['start'];
		$length = $requestData['length'];
		$mTotalData = DB::table('pdambjm_trans')->count();
		$mTotalFiltered = $mTotalData;

		try{

			if(!empty($requestData['search']['value'])){
				$mTotalFiltered = DB::table('pdambjm_trans')
					->where("cust_id","like","%".$requestData['search']['value']."%")
					->orWhere("nama","like","%".$requestData['search']['value']."%")
					->count();

				$mUsers = DB::table('pdambjm_trans')
					->where("cust_id","like","%".$requestData['search']['value']."%")
					->orWhere("nama","like","%".$requestData['search']['value']."%")
					->select('pdambjm_trans.*',DB::raw("'' as aksi"))
					->orderBy('transaction_date','desc')
					->offset($start)
					->limit($length)
					->get();
			}else{
				$mUsers = DB::table('pdambjm_trans')
					->select('pdambjm_trans.*',DB::raw("'' as aksi"))
					->orderBy('transaction_date','desc')
					->offset($start)
					->limit($length)
					->get();
			}

			//return var_dump($mUsers);

			return Response::json(array(
				'status' => 'Success',
				'message' => '-',
				'draw' => $requestData['draw'],
				'recordsTotal' => $mTotalData,
				'recordsFiltered' => $mTotalFiltered,
				'data' => $mUsers
			),200);
			
			// return Response::json(array(
			// 	'status' => 'Success',
			// 	'message' => '-',
			// 	'data' => $mUsers
			// ),200);
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

			// DB::table('pdambjm_trans')
	  //           ->where('id',$Id)
	  //           ->delete();
			$Pdam = Managepdambjm::find($Id);
			$Pdam->flag_transaksi = "cancel";
			$Pdam->save();

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
