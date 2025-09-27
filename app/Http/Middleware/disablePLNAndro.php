<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\mLoket;
use App\User;
use Response;

class disablePLNAndro
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    private $whiteListPLN = array('LYKN','LIRVN','LTES1','LYKNA','PIRVAN');

    public function handle($request, Closure $next)
    {
        $username = $request->username;

        $loketId = User::where("username","=",$username)->first()->loket_id;

        $dLoket = mLoket::where("id",$loketId)->first();

        if($dLoket){
            if(!in_array($dLoket->loket_code, $this->whiteListPLN)){
                return Response::json(array(
                    'status' => 'Error',
                    'response_code' => '-',
                    'message' => 'Aplikasi masih dalam tahap Develop'
                ),200);
            }
        }else{
            return Response::json(array(
                'status' => 'Error',
                'response_code' => '-',
                'message' => 'Invalid Loket'
            ),200);
        }

        return $next($request);
    }
}
