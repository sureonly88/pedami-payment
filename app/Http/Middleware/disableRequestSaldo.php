<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\mLoket;

class disableRequestSaldo
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    private $whiteListSaldo = array('LYKN','LIRVN');

    public function handle($request, Closure $next)
    {
        if (Auth::check()) {

            $username = Auth::user()->username;
            $loketId = Auth::user()->loket_id;

            $dLoket = mLoket::where("id",$loketId)->first();

            if($dLoket){
                if(!in_array($dLoket->loket_code, $this->whiteListSaldo)){

                    if($request->ajax()){
                        return Response::json(array(
                            'status' => false,
                            'message' => array('Error 404, Kamu tidak punya akses.'),
                        ),200);
                    }else{
                        return response()->view('errors.404');
                    }
                }
            }
        }

        return $next($request);
    }
}
