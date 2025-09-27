<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\mLoket;
use App\User;
use Response;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Validator;
use Illuminate\Support\Facades\Input;
use DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UsersController extends Controller
{

	public function __construct()
    {
        $this->middleware('is_admin');
    }

	public function index()
	{
		$lokets = mLoket::all();
		$roles = Role::get();

		return view('admin.man_users')
			->with('lokets', $lokets)
			->with('roles', $roles);
	}

	public function updateUser($Id){
		try{

			$Data = Input::all()['Data'];

			$rules = array(
		        'username' => 'required',
		        'email' => 'required|email',
		    );

		    $validator = Validator::make($Data, $rules);

		    if ($validator->fails()) {
		    	return Response::json(array(
					'status' => 'Error',
					'message' => $validator->errors()->all()
				),200);
		    }else{

		    	if(strlen($Data['password']) > 0){
		    		$hasPassword = Hash::make($Data['password']);
	    			$Data['password'] = $hasPassword;
		    	}else{
		    		unset($Data['password']);
		    	}

		    	$roles = $Data['roles'];
		    	unset($Data['roles']);

	    		DB::table('users')->where('id',$Id)->update($Data);

	    		//Setting Roles
	    		$user = User::findOrFail($Id);
		        if (isset($roles)) {        
		            $user->roles()->sync($roles);            
		        }        
		        else {
		            $user->roles()->detach();
		        }

				return Response::json(array(
					'status' => 'Success',
					'message' => 'Update Berhasil',
					'data' => Input::all()
				),200);
		    }

		}catch (\Exception $e){
			$error = explode("\r\n",$e->getMessage());
            return Response::json(array(
                'status' => 'Error',
                'message' => 'Terjadi kesalahan, cek kembali isian.',
            ),200);
		}
	}

	public function simpanUser(){
		try{

			$Data = Input::all()['Data'];
			$rules = array(
		        'username' => 'required|unique:users',
		        'password' => 'required',
		        'email' => 'required|email',
		    );

		    $validator = Validator::make($Data, $rules);

		    if ($validator->fails()) {
		    	return Response::json(array(
					'status' => 'Error',
					'message' => $validator->errors()->all()
				),200);
		    }else{

	    		unset($Data['id']);
	    		$hasPassword = Hash::make($Data['password']);
	    		$Data['password'] = $hasPassword;

	    		$roles = $Data['roles'];
	    		unset($Data['roles']);

		    	DB::table('users')->insert($Data);

		    	$user = User::where('username',$Data['username'])->first();

		    	//Setting Roles
		        if (isset($roles)) {
		            foreach ($roles as $role) {
			            $role_r = Role::where('id', '=', $role)->firstOrFail();            
			            $user->assignRole($role_r);
		            }
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
                'message' => 'Terjadi kesalahan, cek kembali isian.',
            ),200);
		}
	}

	public function getUserEdit($Id){
		try{
			$cekId = User::where('id',$Id)->first();
			$roles = $cekId->roles()->pluck('name');

			return Response::json(array(
				'status' => 'Success',
				'message' => '-',
				'data' => $cekId,
				'roles' => $roles
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
	
	public function getUsers(){
		try{
			$mUsers = DB::table('users')
	            ->leftJoin('lokets','users.loket_id','=','lokets.id')
	            ->select('users.id','users.username','users.role','lokets.nama','users.email')
	            ->get();

	        $listUser = array();
	        foreach ($mUsers as $myUser) {
	        	$myUser = User::findOrFail($myUser->id);

	        	$user['id'] = $myUser->id;
	        	$user['username'] = $myUser->username;
	        	$user['role'] = $myUser->roles()->pluck('name')->implode(', ');
	        	$user['email'] = $myUser->email;
	        	$user['nama'] = $myUser->loket->nama;
	        	$user['aksi'] = '';

	        	array_push($listUser, $user);
	        }

			return Response::json(array(
				'status' => 'Success',
				'message' => '-',
				'data' => $listUser
			),200);
		}catch (\Exception $e){
			$error = explode("\r\n",$e->getMessage());
            return Response::json(array(
                'status' => 'Error',
                'message' => $error
            ),200);
		}
	}

	public function deleteUser($Id){
		try{
			DB::table('users')
	            ->where('id',$Id)
	            ->delete();

			return Response::json(array(
				'status' => 'Success',
				'message' => 'User sudah di hapus.'
			),200);
		}catch (\Exception $e){
			$error = explode("\r\n",$e->getMessage());
            return Response::json(array(
                'status' => 'Error',
                'message' => 'Terjadi kesalahan, cek kembali isian.',
            ),200);
		}
	}

	public function closeConnUser($Id){
		try{

			DB::table('users')
	            ->where('id',$Id)
	            ->update(['session_id' => '']);

			return Response::json(array(
				'status' => 'Success',
				'message' => 'Koneksi User sudah diputus.'
			),200);
		}catch (\Exception $e){
			$error = explode("\r\n",$e->getMessage());
            return Response::json(array(
                'status' => 'Error',
                'message' => 'Terjadi kesalahan, cek kembali isian.',
            ),200);
		}
	}

}
