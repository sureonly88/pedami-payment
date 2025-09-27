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

class BeritaController extends Controller
{

	public function __construct()
    {
        //$this->middleware('is_admin');
        //$this->middleware('auth');
    }

	public function index()
	{
		return view('admin.berita');
	}

	public function simpanData(){
		try{

			$Data = Input::all()['Data'];
			$username = Auth::user()->username;
			
			//Edit Validator Here
			$rules = array(
		        'judul' => 'required',
		        'isi' => 'required',
		    );

		    $validator = Validator::make($Data, $rules);

		    if ($validator->fails()) {
		    	return Response::json(array(
					'status' => 'Error',
					'message' => $validator->errors()->all()
				),200);
		    }else{

		    	$Data['user'] = $username;
				$Id = $Data['id'];

		    	if($Id){
		    		$Data['updated_at'] = date('Y-m-d H:i:s');
		    		DB::table('berita')->where('id',$Id)->update($Data);
		    	}else{
		    		unset($Data['id']);
		    		$Data['created_at'] = date('Y-m-d H:i:s');
					$Data['updated_at'] = date('Y-m-d H:i:s');
		    		DB::table('berita')->insert($Data);
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
			$cekId = DB::table('berita')->where('id',$Id)->first();

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
			$mUsers = DB::table('berita')
				->select('berita.*',DB::raw("'' as aksi"))
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
			DB::table('berita')
	            ->where('id',$Id)
	            ->delete();
				
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
