<?php

namespace App\Http\Middleware\Permissions;

use Closure;
use Illuminate\Support\Facades\Auth;

class manageTrx
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
        if (!Auth::user()->hasPermissionTo('Manage Transaksi')) {
            if($request->ajax()){
                return Response::json(array(
                    'status' => false,
                    'message' => 'Access Denied',
                ),200);
            }else{
                abort("401");
            }
        }

        return $next($request);
    }
}
