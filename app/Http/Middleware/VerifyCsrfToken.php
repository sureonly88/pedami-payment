<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'mobile/login',
        'mobile/pdambjm/pay',
        'mobile/pln_postpaid/payment',
        'mobile/pln_postpaid/reversal',
        'mobile/pln_prepaid/purchase',
        'mobile/pln_prepaid/advise',
        'mobile/pln_nontaglis/payment',
        'mobile/pln_nontaglis/reversal',
        'gateway/pdambjm/payment',
        'gateway/issue_token',
        'printing/login',
        'printing/insert_queue'
    ];

    // protected $except = [
    //     'mobile/login',
    //     'mobile/pdambjm/pay',
    //     'pelanggan-grid',
    //     'pelanggan-crud',
    //     'api/pln/*',
    //     'pln/*',
    //     'mobile/pln_postpaid/payment',
    //     'mobile/pln_postpaid/reversal',
    //     'mobile/pln_prepaid/purchase',
    //     'mobile/pln_prepaid/advise',
    //     'mobile/pln_nontaglis/payment',
    //     'mobile/pln_nontaglis/reversal',
    //     'gateway/pdambjm/payment',
    //     'gateway/issue_token'
    // ];
}
