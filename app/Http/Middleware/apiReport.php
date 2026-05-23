<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\ReportApiToken;
use Response;

class apiReport
{
    /**
     * Middleware autentikasi khusus untuk API Laporan.
     * Menggunakan tabel report_api_tokens yang terpisah dari api_token users.
     *
     * Header yang dibutuhkan: report-token
     */
    public function handle($request, Closure $next)
    {
        $token = $request->header('report-token');

        if (!$token) {
            return Response::json([
                'status'        => false,
                'response_code' => '4010',
                'message'       => 'REPORT TOKEN REQUIRED',
            ], 401);
        }

        $apiToken = ReportApiToken::where('token', $token)
            ->where('is_active', true)
            ->first();

        if (!$apiToken) {
            return Response::json([
                'status'        => false,
                'response_code' => '4011',
                'message'       => 'INVALID OR INACTIVE REPORT TOKEN',
            ], 401);
        }

        // Update last_used_at (silent update)
        $apiToken->timestamps = false;
        $apiToken->last_used_at = now();
        $apiToken->save();

        // Simpan data token ke request agar controller bisa pakai tanpa query ulang
        $request->attributes->set('report_token', $apiToken);

        return $next($request);
    }
}
