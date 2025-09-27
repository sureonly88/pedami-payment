<?php namespace App\Http\Controllers;

use App\Models\mLoket;
use Illuminate\Support\Facades\Response;

class PulsaController extends Controller
{
    public function __construct()
    {
        //$this->middleware('is_admin');
        //$this->middleware('auth');
    }
    
    public function cekPulsa($KodeLoket = "", $Total = 0){
        try{
            $cekLoket = mLoket::where("loket_code",$KodeLoket)->first();
            $numLoket = $cekLoket->count();

            $Total = str_replace(",","",$Total);
            $Total = str_replace(".","",$Total);

            if($numLoket > 0){
                $Pulsa = $cekLoket->pulsa;
                //return $Pulsa;
                if($Pulsa >= $Total){
                    return Response::json(array(
                        'status' => 'Success',
                        'message' => '-',
                    ), 200);
                }else{
                    return Response::json(array(
                        'status' => 'Error',
                        'message' => 'Pulsa tidak mencukupi ' . $Pulsa . ' < ' . $Total,
                    ), 200);
                }
            }else{
                return Response::json(array(
                    'status' => 'Error',
                    'message' => 'Loket Not Found',
                ), 200);
            }
        }catch (Exception $e) {
            $error = explode("\r\n", $e->getMessage());
            return Response::json(array(
                'status' => 'Error',
                'message' => $error,
            ), 200);
        }
    }


    public function infoPulsa($KodeLoket){
        try{
            $cekLoket = mLoket::where("loket_code",$KodeLoket)->first();
            //$numLoket = $cekLoket->count();
            if($cekLoket){
                $Pulsa = $cekLoket->pulsa;

                return Response::json(array(
                    'status' => 'Success',
                    'message' => '-',
                    'pulsa' => $Pulsa,
                ), 200);
            }else{
                return Response::json(array(
                    'status' => 'Error',
                    'message' => 'Loket Not Found',
                ), 200);
            }
        }catch (Exception $e) {
            $error = explode("\r\n", $e->getMessage());
            return Response::json(array(
                'status' => 'Error',
                'message' => $error,
            ), 200);
        }
    }
}