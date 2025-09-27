<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\mLoket;
use Illuminate\Contracts\Encryption\DecryptException;
use DB;
use Illuminate\Support\Facades\Auth;
use Response;

class ManTokenContoller extends Controller
{
    public function __construct()
    {
        $this->middleware('is_admin');
    }

    public function index()
    {
    	$users = DB::table('users')
    			->join('lokets','users.loket_id','=','lokets.id')
    			->select('users.username','lokets.loket_code','lokets.nama')->get();

        return view('admin.issue_token')->with('users',$users);
    }

    public function getToken($username)
    {
    	try{
    		$user = DB::table('users')->where('username',$username)->select('username','api_token')->first();
    		if($user){
    			return Response::json(array(
	                'status' => true,
	                'message' => "-",
	                'token' => $user->api_token
	            ),200);
    		}else{
    			return Response::json(array(
	                'status' => false,
	                'message' => "DATA TIDAK DITEMUKAN."
	            ),200);
    		}
    	}catch (\Exception $e) {
	        return Response::json(array(
                'status' => false,
                'message' => "ERROR GET TOKEN"
            ),200);
	    }
    }

    public function issue_token(Request $request){

    	$username = $request->input('username');
    	try{

            $user = DB::table('users')
                ->join('lokets','users.loket_id','=','lokets.id')
                ->where('users.username',$username)
                ->select('users.id','users.username','lokets.loket_code','lokets.nama','lokets.byadmin','lokets.jenis','lokets.is_blok','lokets.blok_message')
                ->first();

            $is_blok = $user->is_blok;
            $message_blok = $user->blok_message;

            if($is_blok > 0){
                return Response::json(array(
                    'status' => false,
                    'response_code' => "0048",
                    'message' => "BLOCKED LOKET",
                    'blok_message' => $message_blok
                ),200);
            }

            $token = str_random(30);

            DB::table('users')
                ->where('id', $user->id)
                ->update(array('api_token' => $token));

            return Response::json(array(
                'status' => true,
                'response_code' => "0000",
                'message' => "REQUEST SUCCESS",
                'username' => $username,
                'kode_loket' => $user->loket_code,
                'nama_loket' => $user->nama,
                'token' => $token,
            ),200);
           
    	}catch (\Exception $e) {
	        return Response::json(array(
                'status' => false,
                'response_code' => "0005",
                'message' => "ERROR GENERATE TOKEN"
            ),200);
	    }
    }
}
