<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class KopkarServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //Service Container Manage Saldo
        $this->app->bind('\App\Services\ManageSaldo', function ($app) {
            return new \App\Services\ManageSaldo();
        });

        //Service Container Loket Service
        $this->app->bind('\App\Services\LoketService', function ($app) {
            return new \App\Services\LoketService();
        });

        //Service Container API PDAM BJM
        //Fungsi : Untuk menghandle inquery dan payment API Pembayaran PDAM Bandarmasih
        $this->app->bind('\App\APIServices\PdamBjmAPIv2Service', function ($app) {
            return new \App\APIServices\PdamBjmAPIv2Service();
        });

        //Service Container API PLN
        $this->app->bind('\App\APIServices\PembayaranAPIServices', function ($app) {
            return new \App\APIServices\PembayaranAPIServices();
        });

        //Service Email Transaksi
        //Fungsi : Untuk menghandle pengiriman email beserta Attachment data pada saat transaksi hari itu.
        $this->app->bind('\App\Services\EmailTransaksiServices', function ($app) {
            return new \App\Services\EmailTransaksiServices();
        });

        //API Postpaid PLN
        $this->app->bind('\App\PlnServices\PostPaidService', function ($app) {
            return new \App\PlnServices\PostPaidService();
        });

        $this->app->bind('\App\PlnServices\PostPaidNewService', function ($app) {
            return new \App\PlnServices\PostPaidNewService();
        });

        //API Prepaid PLN
        $this->app->bind('\App\PlnServices\PrePaidService', function ($app) {
            return new \App\PlnServices\PrePaidService();
        });

        $this->app->bind('\App\PlnServices\PrePaidNewService', function ($app) {
            return new \App\PlnServices\PrePaidNewService();
        });

        //API Nontaglis PLN
        $this->app->bind('\App\PlnServices\NonTaglisService', function ($app) {
            return new \App\PlnServices\NonTaglisService();
        });

        //Fungsi : API PDAMBJM Buat Rekanan
        $this->app->bind('\App\APIRekanan\PdambjmRknService', function ($app) {
            return new \App\APIServices\PdambjmRknService();
        });

        $this->app->bind('\App\Services\AksesCheckService', function ($app) {
            return new \App\Services\AksesCheckService();
        });

        $this->app->bind('\App\Services\PrintBaruService', function ($app) {
            return new \App\Services\PrintBaruService();
        });

        $this->app->bind('\App\Services\PrintBaruV1Service', function ($app) {
            return new \App\Services\PrintBaruV1Service();
        });

        $this->app->bind('\App\Services\RequestTokenService', function ($app) {
            return new \App\Services\RequestTokenService();
        });
    }
}
