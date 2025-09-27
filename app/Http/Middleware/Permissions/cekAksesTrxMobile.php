<?php

namespace App\Http\Middleware\Permissions;

use Closure;
use App\User;
use Route;
use Response;

class cekAksesTrxMobile
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
        $cekUser = User::where('username',$username)->first();

        $url = $route = Route::current()->uri();
        try{
            if (strpos($url,'pdambjm')){
                if (!$cekUser->hasPermissionTo('Transaksi Pdambjm')) {
                    return Response::json(array(
                        'status' => false,
                        'message' => 'Access Denied',
                    ),200);
                }
            }

            if (strpos($url,'pln_postpaid')){
                if (!$cekUser->hasPermissionTo('Transaksi PLN Postpaid')) {
                    return Response::json(array(
                        'status' => false,
                        'message' => 'Access Denied',
                    ),200);
                }
            }

            if (strpos($url,'pln_prepaid')){
                if (!$cekUser->hasPermissionTo('Transaksi PLN Prepaid')) {
                    return Response::json(array(
                        'status' => false,
                        'message' => 'Access Denied',
                    ),200);
                }
            }

            if (strpos($url,'pln_nontaglis')){
                if (!$cekUser->hasPermissionTo('Transaksi PLN Nontaglis')) {
                    return Response::json(array(
                        'status' => false,
                        'message' => 'Access Denied',
                    ),200);
                }
            }
        }catch (\Exception $e) {

            return Response::json(array(
                'status' => false,
                'message' => 'Access Denied',
            ),200);

        }

        return $next($request);
    }
}
