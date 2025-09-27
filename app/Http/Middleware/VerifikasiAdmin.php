<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Request;
use Response;

class VerifikasiAdmin
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

        // if (Auth::check()) {
            
        //     $username = Auth::user()->username;
        //     $role = Auth::user()->role;

        //     if($role != "admin"){
        //         if($request->ajax()){
        //             return Response::json(array(
        //                 'status' => false,
        //                 'message' => array('Error 404, Kamu tidak punya akses.'),
        //             ),200);
        //         }else{
        //             return response()->view('errors.404');
        //         }
                
        //     }
        // }else{
        //     if($request->ajax()){
        //         return Response::json(array(
        //             'status' => false,
        //             'message' => array('Anda belum login, silahkan login ulang'),
        //         ),200);
        //     }else{
        //         Session::flash('error', 'Anda belum login, silahkan login ulang');
        //         return Redirect::to('login');
        //     }
            
        // }

        return $next($request);
    }
}
