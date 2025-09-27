<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Response;
use Route;
use App\Services\AksesCheck;

class VerifikasiLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    protected $auth;

    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    public function handle($request, Closure $next)
    {
        if ($this->auth->guest())
        {
            if ($request->ajax())
            {
                return Response::json(array(
                    'status' => false,
                    'message' => array('Unauthorized access, Silahkan login ulang.'),
                ),200);
            }
            else
            {
                Session::flash('error', 'Anda belum login');
                return redirect()->guest('/login');
            }
        }

        if (Auth::user()->session_id != Session::getId())
        {
            Auth::logout();
            if($request->ajax()){
                return Response::json(array(
                    'status' => false,
                    'message' => array('Unauthorized access, Silahkan login ulang.'),
                ),200);
            }else{
                Session::flash('error', 'User sudah login di komputer lain');
                return redirect()->guest('/login');
            }
        }

        return $next($request);
    }
}
