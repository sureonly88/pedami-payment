<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Response;

class ThrottleApiRekanan
{
    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     * Key rate limit by api-token header so each loket has its own quota.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $maxAttempts  Max requests per window
     * @param  int  $decayMinutes  Window duration in minutes
     * @return mixed
     */
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1)
    {
        $apiToken = $request->header('api-token');

        // Fall back to IP if token not present (apiRekanan middleware will reject it anyway)
        $key = sha1(($apiToken ?: $request->ip()) . '|' . $request->path());

        if ($this->limiter->tooManyAttempts($key, $maxAttempts, $decayMinutes)) {
            $retryAfter = $this->limiter->availableIn($key);

            return Response::json([
                'status'        => false,
                'response_code' => '0429',
                'message'       => 'TOO MANY REQUESTS - RATE LIMIT EXCEEDED',
                'retry_after'   => $retryAfter,
            ], 429);
        }

        $this->limiter->hit($key, $decayMinutes);

        return $next($request);
    }
}
