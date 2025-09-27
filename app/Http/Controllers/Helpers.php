<?php namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\mLoket;
use GuzzleHttp\Client;

class Helpers extends Controller {

    public function __construct()
    {

    }

    public static function getLoginDetail(){
        $username = Auth::user()->username;
        $role = Auth::user()->role;
        $loket_id = Auth::user()->loket_id;
        $email = Auth::user()->email;
        $lastLogin = Auth::user()->updated_at;

        $namaLoket = "-";
        $codeLoket = "-";
        $pulsa = 0;
        $byadmin = 0;
		$jenis = "-";

        $dLoket = mLoket::where("id",$loket_id)->first();
        if($dLoket != null){
            $namaLoket = $dLoket->nama;
            $codeLoket = $dLoket->loket_code;
            $pulsa = $dLoket->pulsa;
            $byadmin = $dLoket->byadmin;
			$jenis = $dLoket->jenis;
        }

        $loginData = array(
            "username" => $username,
            "role" => $role,
            "loket_name" => $namaLoket,
            "loket_code" => $codeLoket,
            "pulsa" => $pulsa,
            "byadmin" => $byadmin,
            "email" => $email,
            "lastlogin" => $lastLogin,
			"jenis" => $jenis
        );

        return $loginData;
    }

    public static function get_service_request($path){
        $arrResponse = array();
//        try{
            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL,$path);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch,CURLOPT_HEADER, 0);
            curl_setopt($ch,CURLOPT_CONNECTTIMEOUT ,0);
            curl_setopt($ch,CURLOPT_TIMEOUT, 400);
            $response=curl_exec($ch);

            if(curl_errno($ch)){
                $arrResponse["status"]="Error";
                $arrResponse["message"]=curl_error($ch);
                $arrResponse["response"]="";

                return $arrResponse;
            }

            curl_close($ch);
            $arrResponse["status"]="Success";
            $arrResponse["message"]="None";
            $arrResponse["response"]=$response;

            return $arrResponse;
//        } catch(Exception $e){
//            $error = explode("\r\n",$e->getMessage());
//            $arrResponse["status"]="Error";
//            $arrResponse["message"]=$error;
//            $arrResponse["response"]="";
//
//            return $arrResponse;
//        }
    }

    public static function sent_service_payment($url,$params){
        $arrResponse = array();

        $postData = '';
        if($params != ""){
            foreach($params as $k => $v)
            {
                $postData .= $k . '='.$v.'&';
            }
            rtrim($postData, '&');
        }

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_HEADER, false);
        curl_setopt($ch,CURLOPT_POST, count($postData));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT ,0);
        curl_setopt($ch,CURLOPT_TIMEOUT, 400);

        $output=curl_exec($ch);

        if(curl_errno($ch)){
            $arrResponse["status"]="Error";
            $arrResponse["message"]=curl_error($ch);
            $arrResponse["response"]="";

            return $arrResponse;
        }

        curl_close($ch);
        $arrResponse["status"]="Success";
        $arrResponse["message"]="None";
        $arrResponse["response"]=$output;

        //dd($arrResponse);

        return $arrResponse;
    }

    public static function sent_http_post($url,$params){
        $client = new Client();

        $response = $client->request('POST', $url, []);

        $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK

        if($code == 200){
            $arrResponse["status"]="Success";
            $arrResponse["message"]="OK";
            $arrResponse["response"]=(string)$response->getBody();
        }else{
            $arrResponse["status"]="Error";
            $arrResponse["message"]="Terjadi Error";
            $arrResponse["response"]="";
        }

        return $arrResponse;
    }

    public static function sent_http_post_param($url,$params){
        $client = new Client();

        $response = $client->request('POST', $url, [
            'body' => $params
        ]);

        $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK

        if($code == 200){
            $arrResponse["status"]="Success";
            $arrResponse["message"]="OK";
            $arrResponse["response"]=(string)$response->getBody();
        }else{
            $arrResponse["status"]="Error";
            $arrResponse["message"]="Terjadi Error";
            $arrResponse["response"]="";
        }

        return $arrResponse;
    }

    public static function setHttpPostQueue($url,$username,$rek,$kertas,$layanan){
        $client = new Client();

        $response = $client->request('POST', $url, [
        'form_params' => [
            'username' => $username,
            'rek' => $rek,
            'kertas' => $kertas,
            'layanan' => $layanan
            ]
        ]);

        $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK

        if($code == 200){
            $arrResponse["status"]="Success";
            $arrResponse["message"]="OK";
            $arrResponse["response"]=(string)$response->getBody();
        }else{
            $arrResponse["status"]="Error";
            $arrResponse["message"]="Terjadi Error";
            $arrResponse["response"]="";
        }

        return $arrResponse;
    }

    public static function sent_http_get($url){
        $client = new Client();

        $response = $client->request('GET', $url);

        $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK

        if($code == 200){
            $arrResponse["status"]="Success";
            $arrResponse["message"]="OK";
            $arrResponse["response"]=(string)$response->getBody();
        }else{
            $arrResponse["status"]="Error";
            $arrResponse["message"]="Terjadi Error";
            $arrResponse["response"]="";
        }

        return $arrResponse;
    }


    public static function sent_pln_get($url, $Authority){
        $client = new Client(['verify' => false ]);

        $response = $client->request('GET', $url, [
            'headers' => [
                'Authority' => $Authority,
                'user_key'  => env('PLN_USER_KEY','')
            ]
        ]);

        $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK

        if($code == 200){
            $arrResponse["status"]="Success";
            $arrResponse["message"]="OK";
            $arrResponse["response"]=(string)$response->getBody();
        }else{
            $arrResponse["status"]="Error";
            $arrResponse["message"]="Terjadi Error";
            $arrResponse["response"]="";
        }

        return $arrResponse;
    }



}
