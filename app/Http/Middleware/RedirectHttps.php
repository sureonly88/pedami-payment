<?php

namespace App\Http\Middleware;

use Closure;

class RedirectHttps
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
        // if (!$request->secure() && env('IS_SECURE') === true) {
        //     //dd("https://ppob.pedamipayment.com/kopkarbaru/public/"$request->path());
        //     //return redirect("https://ppob.pedamipayment.com/kopkarbaru/public/".$request->path());
        //     return redirect(env('URL_SECURE').$request->path());
        // }

        return $next($request);
    }
}
