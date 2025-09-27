<?php

namespace App\Http\Middleware;

use Closure;
use DB;
use Response;
use Request;

class apiRekanan
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
        //$api_token = $request->api_token;
        $api_token = Request::header('api-token');

        //dd($api_token);

        if(!$api_token){
            return Response::json(array(
                'status' => false,
                'response_code' => '0044',
                'message' => 'UNAUTHORIZED TOKEN'
            ),403);
        }

        $user = DB::table('users')
            ->leftJoin('lokets','users.loket_id','=','lokets.id')
            ->where('users.api_token',$api_token)
            ->select('users.username','lokets.loket_code','lokets.nama','lokets.byadmin','lokets.jenis','lokets.is_blok','lokets.blok_message')
            ->first();

        if(!$user){
            return Response::json(array(
                'status' => false,
                'response_code' => '0044',
                'message' => 'UNAUTHORIZED TOKEN'
            ),403);
        }

        if($user->is_blok > 0){
            return Response::json(array(
                'status' => false,
                'response_code' => "0048",
                'message' => "BLOCKED LOKET",
                'blok_message' => $message_blok
            ),403);
        }

        return $next($request);
    }
}
