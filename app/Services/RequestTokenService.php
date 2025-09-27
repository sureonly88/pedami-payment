<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use App\Models\TokenLunasin;
use App\Models\TokenLunasinFailed;
use Illuminate\Support\Facades\Log;
use DB;
use App\Http\Controllers\Helpers;

class RequestTokenService
{

    public function LoginLunasin(){

        // $params = [
        //     'form_params' => [
        //         'tipe_pesan'    => 'login',
        //         'username'      => env('PLN_LUNASIN_USERNAME',''),
        //         'password'      => md5(md5(env('PLN_LUNASIN_PASS','')).env('PLN_LUNASIN_ID','')),
        //         'id_app'        => env('PLN_LUNASIN_ID',''),
        //         'tipe_device'   => '1',
        //         'kode_produk'   => 'internal'
        //     ]
        // ];

        $password = md5(md5(env('PLN_LUNASIN_PASS','')).env('PLN_LUNASIN_ID',''));

        $params = '{
            "tipe_pesan":"login",
            "username":"'.env('PLN_LUNASIN_USERNAME','').'",
            "password":"'.$password.'",
            "id_app":"'.env('PLN_LUNASIN_ID','').'",
            "tipe_device":"1",
            "kode_produk":"internal"
        }';

        $response = Helpers::sent_http_post_param("https://".env('PLN_LUNASIN_IP','').":".env('PLN_LUNASIN_PORT',''), $params);
        $response_asli = $response['response'];

        $response = json_decode($response['response'], true);

        //$response = $response['response'];
        //dd($response);die();
        // $response = '{
        //     "rc":"0000",
        //     "rc_msg":"Login sukses",
        //     "tipe_pesan":"login",
        //     "username":"BUDI_1231",
        //     "password":"fb0ac78ee01d59563a64edd143d70059",
        //     "id_app":"a189e5b2e69afa824f5e295af0e4ee531581718378761",
        //     "tipe_device":"1",
        //     "kode_produk":"internal",
        //     "kode_loket":"ABC00010",
        //     "url_trx":"https://123.123.123.123:2053",
        //     "token":"fbd0535f-9a1d-11ea-bd8f-5600029b5e6d",
        //     "nama_loket":"PT ABC",
        //     "keterangan_helpdesk":"",
        //     "kode_ca":"ABC",
        //     "kode_korwil":"ABC",
        //     "deposit":"2988256",
        //     "bailout":"0",
        //     "is_blocked":"0"
        // }';
        
        if($response['rc'] == '0000'){
            DB::delete('delete from token_lunasin');
            
            $tokenLogin = new TokenLunasin();
            $tokenLogin->rc = $response['rc'];
            $tokenLogin->rc_msg = $response['rc_msg'];
            $tokenLogin->tipe_pesan = $response['tipe_pesan'];
            $tokenLogin->username = $response['username'];
            $tokenLogin->password = $response['password'];
            $tokenLogin->id_app = $response['id_app'];
            $tokenLogin->tipe_device = $response['tipe_device'];
            $tokenLogin->kode_produk = $response['kode_produk'];
            $tokenLogin->kode_loket = $response['kode_loket'];
            $tokenLogin->url_trx = $response['url_trx'];
            $tokenLogin->token = $response['token'];
            $tokenLogin->nama_loket = $response['nama_loket'];
            $tokenLogin->keterangan = $response['keterangan_helpdesk'];
            $tokenLogin->kode_ca = $response['kode_ca'];
            $tokenLogin->kode_korwil = $response['kode_korwil'];
            $tokenLogin->deposit = $response['deposit'];
            $tokenLogin->bailout = $response['bailout'];
            $tokenLogin->is_blocked = $response['is_blocked'];
            $tokenLogin->save();
        }else{
            DB::delete('delete from token_lunasin_failed');
            
            $tokenLogin = new TokenLunasinFailed();
            $tokenLogin->response = $response_asli;
            $tokenLogin->save();
        }

        return $response;

    }

}