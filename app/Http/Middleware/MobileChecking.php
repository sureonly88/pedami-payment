<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Register_hp;
use Response;
use App\User;
use App\Models\mLoket;
use App\Models\TanpaImei;

class MobileChecking
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $username = $request->username;
        $imei = $request->imei;
        $sessionid = $request->sessionid;

        $dtUser = User::where("username","=",$username)->first();
        if(!$dtUser){
            return Response::json(array(
                'status' => 'Error',
                'message' => 'Invalid User'
            ),200);    
        }

        if($sessionid != $dtUser->session_id){
            return Response::json(array(
                'status' => 'Error',
                'message' => 'Session ID Tidak Valid'
            ),200);    
        }

        $LoketID = $dtUser->loket_id;
        $dataLoket = mLoket::where('id',$LoketID)->first();
        if(!$dataLoket){
            return Response::json(array(
                'status' => 'Error',
                'message' => 'Invalid Loket'
            ),200);    
        }

        $dtImei = Register_hp::where("username","=",$username)->where("imei","=",$imei)->get();
        $exceptImei = TanpaImei::where("username","=",$username)->get();
        if($dtImei->count()<=0 && $exceptImei->count()<=0){
            return Response::json(array(
                'status' => 'Error',
                'message' => 'Handphone tidak terdaftar untuk user ini'
            ),200);
        }

        return $next($request);
    }
}
