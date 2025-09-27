<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:60,1',
            'bindings',
        ],

        // 'admin' => [
        //     \App\Http\Middleware\VerifikasiLogin::class,
        // ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        //'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'auth' => \App\Http\Middleware\VerifikasiLogin::class,
        'is_admin' => \App\Http\Middleware\VerifikasiAdmin::class,
        'akses_pln' => \App\Http\Middleware\aksesPLN::class,
        'akses_saldo' => \App\Http\Middleware\disableRequestSaldo::class,
        'mobile' => \App\Http\Middleware\MobileChecking::class,
        'akses_pln_andro' => \App\Http\Middleware\disablePLNAndro::class,
        'api_rekanan' => \App\Http\Middleware\apiRekanan::class,
        'secure' => \App\Http\Middleware\RedirectHttps::class,

        'berita' => \App\Http\Middleware\Permissions\konfigBerita::class,
        'email' => \App\Http\Middleware\Permissions\konfigEmail::class,
        'hp' => \App\Http\Middleware\Permissions\konfigHp::class,
        'loket' => \App\Http\Middleware\Permissions\konfigLoket::class,
        'permissions' => \App\Http\Middleware\Permissions\konfigPermissions::class,
        'roles' => \App\Http\Middleware\Permissions\konfigRoles::class,
        'token' => \App\Http\Middleware\Permissions\konfigToken::class,
        'topup' => \App\Http\Middleware\Permissions\konfigTopup::class,
        'user' => \App\Http\Middleware\Permissions\konfigUser::class,
        'rekon' => \App\Http\Middleware\Permissions\manageRekon::class,
        'manageTrx' => \App\Http\Middleware\Permissions\manageTrx::class,
        'laporan' => \App\Http\Middleware\Permissions\menuLaporan::class,
        'pdambjm' => \App\Http\Middleware\Permissions\trxPdambjm::class,
        'postpaid' => \App\Http\Middleware\Permissions\trxPlnPostpaid::class,
        'prepaid' => \App\Http\Middleware\Permissions\trxPlnPrepaid::class,
        'nontaglis' => \App\Http\Middleware\Permissions\trxPlnNontaglis::class,
        'vsaldo' => \App\Http\Middleware\Permissions\verifikasiSaldo::class,
        'rsaldo' => \App\Http\Middleware\Permissions\RequestSaldo::class,
        'trxmobile' => \App\Http\Middleware\Permissions\cekAksesTrxMobile::class,
        'settingrek' => \App\Http\Middleware\Permissions\aksesSettingRekPdam::class,
        'transaksi' => \App\Http\Middleware\Permissions\trxTransaksi::class,
        'trxadvise' => \App\Http\Middleware\Permissions\trxAdvise::class,
    ];
}
