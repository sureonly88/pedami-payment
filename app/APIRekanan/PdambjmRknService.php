<?php

namespace App\APIRekanan;

use App\Http\Controllers\Helpers;
use App\Models\mLogPdambjm;
use App\Models\mPdambjmTrans;
use App\Models\AdvisePDAM;
use App\Models\mDaftarTransaksi;
use SimpleXMLElement;
use App\Models\mLoket;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use App\Models\Register_hp;
use App\Models\LogErrorPdam;
use App\Models\User;
use DB;
use App\Models\SettingRekPdam;

class PdambjmRknService
{

    private function simpanLogSistem($Log){

		$mLog = new LogErrorPdam();
		$mLog->log = $Log;
		$mLog->save();
	}

	public function inqueryPelanggan($idpel,$api_token){

        if(strlen($idpel) < 7){
            return Response::json(array(
                'status' => false,
                'response_code' => '0041',
                'message' => 'INVALID CUSTOMER ID'
            ),403);
        }

        try{

            $user = DB::table('users')
                ->leftJoin('lokets','users.loket_id','=','lokets.id')
                ->where('users.api_token',$api_token)
                ->select('users.username','lokets.loket_code','lokets.nama','lokets.pulsa')
                ->first();

            $loket = $user->loket_code;

            $setup = mLoket::where('loket_code','=',$loket)->first();
            if(is_null($setup)){
                return Response::json(array(
                    'status' => false,
                    'response_code' => '0042',
                    'message' => 'INVALID LOKET CODE'
                ),403);
            }

            $byadm = $setup->byadmin;

            $pdambjm_cfg = Config::get('app.pdambjm');
            // $clientid = $pdambjm_cfg['clientid'];
            // $passwd = $pdambjm_cfg['password'];

            $clientid = $pdambjm_cfg['clientid_admin'];
            $passwd = $pdambjm_cfg['password_admin'];

            $request = $pdambjm_cfg['request'];
            $request = str_replace("{idpel}",$idpel,$request);
            $request = str_replace("{clientid}",$clientid,$request);
            $request = str_replace("{password}",$passwd,$request);

            $xml_pdam = Helpers::sent_http_get($request);

            if($xml_pdam["status"]=="Error"){
                return Response::json(array(
                    'status' => false,
                    'response_code' => '0031',
                    'message' => 'SERVER UNREACHABLE'
                ),403);
            }

            $xml_pdam = $xml_pdam["response"];
            $response_json = json_decode($xml_pdam,false);

            //CATAT LOG INQUERY
            $arrLog = array();
            $arrLog["username"] = "SW_".$loket;
            $arrLog["customer_id"] = $idpel;
            $arrLog["response"] = $request;
            $arrLog["response_a"] = json_encode($response_json);

            mLogPdambjm::insert($arrLog);
            //END LOG

            if($response_json->RequestPelangganRev2Result->status == "Error"){
                $dateNow = date("Y-m-d");

                $cekPayment = mDaftarTransaksi::where("CUST_ID","=",$idpel)
                    ->where("TRANSACTION_DATE","=",$dateNow)
                    ->get();

                if($cekPayment->count() > 0){

                    $dataPayment = $cekPayment->toArray();

                    $paymentLoket = $dataPayment[0]['LOKET_CODE'];
                    $tglbayar = $dataPayment[0]['TRANSACTION_DATE'];

                    if($loket == $paymentLoket){

                        return Response::json(array(
                            'status' => false,
                            'response_code' => '0003',
                            'message' => strtoupper('Rekening Sudah Dibayar Tanggal '.$tglbayar.' oleh '.$paymentLoket),
                            'data' => $dataPayment

                        ),403);
                    }
                }

                $pesanPdam =  $response_json->RequestPelangganRev2Result->message;
                return Response::json(array(
                    'status' => false,
                    'response_code' => '0032',
                    'message' => 'ERROR MESSAGE PDAM',
                    'message_pdam' => $pesanPdam
                ),403);
            }

            $response_json = $response_json->RequestPelangganRev2Result->data;

            if(!is_array($response_json)){
                $arrayjson = array();
                $arrayjson[0] = $response_json;
                $response_json = $arrayjson;
            }

            for($i=0;$i<sizeof($response_json);$i++){
                $response_json[$i]->idlgn = $idpel;
                $response_json[$i]->admin_kop = "$byadm";
                if(json_encode($response_json[$i]->nama) == "{}") {
                    $response_json[$i]->nama = "empty";
                }
            }

            $jmlData = sizeof($response_json);

            $paymentData = array();

            $jmlRek = 0;
            $cekAkses = SettingRekPdam::where('loket_code','=',$loket)->first();
            if($cekAkses){
                $jmlRek = $cekAkses->jml_rek_pdam;
            }

            $tglSekarang = date('d');
            if((int)$tglSekarang > 20){
                //$jmlRek = $jmlRek - 1;
            }

            if($jmlData > $jmlRek){
                return Response::json(array(
                    'status' => false,
                    'response_code' => '0043',
                    'message' => "UNAUTHORIZED INQUERY"
                ),403); 
            }

            $TotalTagihan = 0;

            for($i=0;$i<sizeof($response_json);$i++){
                $data = $response_json[$i];

                $payment["Idlgn"] = $data->idlgn;
                $payment["Nama"] = $data->nama;
                $payment["Idgol"] = $data->gol;
                $payment["Alamat"] = $data->alamat;
                $payment["Thbln"] = $data->thbln;
                $payment["Pakai"] = floatval($data->stand_i-$data->stand_l);
                $payment["Harga"] = floatval($data->harga);
                $payment["ByAdmin"] = floatval($data->byadmin); 
                $payment["Materai"] = floatval($data->materai); 
                $payment["Retri"] = floatval($data->retribusi);
                $payment["Denda"] = floatval($data->denda);                
                $payment["Limbah"] = floatval($data->limbah);
                $payment["Stand_l"] = floatval($data->stand_l); 
                $payment["Stand_i"] = floatval($data->stand_i);
                $payment["Sub_Tot"] = floatval($data->total); 
                $payment["Diskon"] = floatval($data->diskon); 
                $payment["Admin_Kop"] = floatval($data->admin_kop); 
                $payment["Total"] = floatval($data->total+$data->admin_kop);
                $payment["User"] = $user->username; 
                $payment["LoketName"] = $user->nama;
                $payment["LoketCode"] = $user->loket_code; 
                $payment["Biaya_tetap"] = floatval($data->biaya_tetap); 
                $payment["Biaya_meter"] = floatval($data->biaya_meter);
                $payment["Gma"] = floatval($data->gma);
                $payment["Angsuran"] = floatval($data->angsuran);

                $TotalTagihan+= ($data->total+$data->admin_kop);

                array_push($paymentData, $payment);
            }

            //dd($TotalTagihan . "-". $user->pulsa);

            if($TotalTagihan > $user->pulsa){
                return Response::json(array(
                    'status' => false,
                    'response_code' => '0045',
                    'message' => "BALANCE IS NOT ENOUGH"
                ),403);
            }

            return Response::json(array(
                'status' => true,
                'response_code' => '0000',
                'message' => 'INQUERY SUCCESS',
                'data' => $paymentData,
                'payment_message' => encrypt($paymentData)
            ),200);
            
        }catch (\Exception $e){
            $error = explode("\r\n",$e->getMessage());

            $this->simpanLogSistem(json_encode($error));

            return Response::json(array(
                'status' => false,
                'response_code' => '0005',
                'message' => "ERROR OTHER"
            ),500);
        }
    }

    public function requestPayment($payment_data, $api_token){
        try{
            $arrData = $payment_data;

            $idpel = $arrData[0]['Idlgn'];
            $dt1 = date("Ymd");
            $dt2 = date("Y-m-d H:i:s");
            $unique_id = strtoupper(date('YmdHis').'-'.uniqid());
            $IdTrans = $unique_id;

            $user = DB::table('users')
                ->leftJoin('lokets','users.loket_id','=','lokets.id')
                ->where('users.api_token',$api_token)
                ->select('lokets.loket_code','lokets.pulsa')
                ->first();

            $loket_code = $user->loket_code;

            $mLoket = mLoket::where('loket_code',$loket_code)->first();
            if(!$mLoket){
                return Response::json(array(
                    'status' => false,
                    'response_code' => '0042',
                    'message' => 'INVALID LOKET CODE'
                ),403);
            }

            $JenisLoket = $mLoket->jenis; 
            $total = 0;

            for($i=0;$i<sizeof($arrData); $i++) {
                $total += $arrData[$i]['Total'];
            }

            if($total > $user->pulsa){
                return Response::json(array(
                    'status' => false,
                    'response_code' => '0045',
                    'message' => "BALANCE IS NOT ENOUGH"
                ),403);
            }

            $cdata = "1|".$idpel."|"."0"."|".$total."|".$dt1."|".$IdTrans."|".$loket_code."|07|".$dt2;

            $pdambjm_cfg = Config::get('app.pdambjm');
                   
            // $idclient = $pdambjm_cfg['clientid'];
            // $passwd = $pdambjm_cfg['password'];     

            $idclient = $pdambjm_cfg['clientid_admin'];
            $passwd = $pdambjm_cfg['password_admin'];

            $request = $pdambjm_cfg['paymentv1'];
            $request = str_replace("{clientid}",$idclient,$request);
            $request = str_replace("{password}",$passwd,$request);
            $request = str_replace("{cdata}",$cdata,$request);

            //SIMPAN ADVISE PDAM
            $adviseMessage = $pdambjm_cfg['checklpp'];
            $adviseMessage = str_replace("{clientid}",$idclient,$adviseMessage);
            $adviseMessage = str_replace("{password}",$passwd,$adviseMessage);
            $adviseMessage = str_replace("{idpel}",$idpel,$adviseMessage);
            $adviseMessage = str_replace("{tgl_bayar}",date("Y-m-d"),$adviseMessage);
            
            $idTrxPdam = $idpel."|".date("Ymd");

            $simpanAdvise = new AdvisePDAM();
			$simpanAdvise->idtrx = $idTrxPdam;
			$simpanAdvise->produk = "PDAMBJM";
			$simpanAdvise->advise_message = $adviseMessage;
			$simpanAdvise->status = 0;
            $simpanAdvise->save();
            //END SIMPAN ADVISE PDAM
            
            $response_payment = Helpers::sent_http_post($request,"");

            //CATAT LOG PAYMENT
            $arrLog = array();
            $arrLog["username"] = "SW_".$loket_code;
            $arrLog["customer_id"] = $idpel;
            $arrLog["response"] = $request;
            $arrLog["response_a"] = json_encode($response_payment);

            mLogPdambjm::insert($arrLog);
            //END LOG

            if($response_payment['status'] == "Error"){
                return Response::json(array(
                    'status' => false,
                    'response_code' => '0031',
                    'message' => 'SERVER UNREACHABLE'
                ),403);
            }

            $response = $response_payment["response"];
            $response = json_decode($response,false);

            if($response->RequestPaymentBulk_Rev2Result->status == "Error"){
                $pesanPdam =  $response->RequestPaymentBulk_Rev2Result->message;
                return Response::json(array(
                    'status' => false,
                    'response_code' => '0032',
                    'message' => 'ERROR MESSAGE PDAM',
                    'message_pdam' => $pesanPdam
                ),403);
            }

            $arrRek = array();
            $pesan_payment = $response->RequestPaymentBulk_Rev2Result->status;
            if($pesan_payment=="Success"){

                for($i=0;$i<sizeof($arrData); $i++) {

                    $nopel = $arrData[$i]['Idlgn'];
                    $nama = $arrData[$i]['Nama'];
                    $alamat = $arrData[$i]['Alamat'];
                    $gol = $arrData[$i]['Idgol'];
                    $thbl = $arrData[$i]['Thbln'];
                    $pakai = $arrData[$i]['Pakai'];
                    $harga = $arrData[$i]['Harga'];
                    $byadm = $arrData[$i]['ByAdmin'];
                    $beban_tetap = $arrData[$i]['Biaya_tetap'];
                    $biaya_meter = $arrData[$i]['Biaya_meter'];
                    $materai = $arrData[$i]['Materai'];
                    $retribusi = $arrData[$i]['Retri'];
                    $denda = $arrData[$i]['Denda'];
                    $sub_tot = $arrData[$i]['Sub_Tot'];
                    $limbah = $arrData[$i]['Limbah'];
                    $total = $arrData[$i]['Total'];
                    $diskon = $arrData[$i]['Diskon'];
                    $stand_l = $arrData[$i]['Stand_l'];
                    $stand_i = $arrData[$i]['Stand_i'];
                    $admin_kop = $arrData[$i]['Admin_Kop'];
                    $userid = $arrData[$i]['User'];
                    $loket_name = $arrData[$i]['LoketName'];
                    $loket_code = $arrData[$i]['LoketCode'];
                    $tgl_server = date('Y-m-d H:i:s');

                    $arrRinci = array();
                    $arrRinci['transaction_code'] = $unique_id;
                    $arrRinci['transaction_date'] = $tgl_server;
                    $arrRinci['cust_id'] = $nopel;
                    $arrRinci['nama'] = $nama;
                    $arrRinci['alamat'] = $alamat;
                    $arrRinci['blth'] = $thbl;
                    $arrRinci['harga_air'] = $harga;
                    $arrRinci['abodemen'] = $byadm;
                    $arrRinci['materai'] = $materai;
                    $arrRinci['limbah'] = $limbah;
                    $arrRinci['retribusi'] = $retribusi;
                    $arrRinci['denda'] = $denda;
                    $arrRinci['stand_lalu'] = $stand_l;
                    $arrRinci['stand_kini'] = $stand_i;
                    $arrRinci['sub_total'] = $sub_tot;
                    $arrRinci['admin'] = $admin_kop;
                    $arrRinci['total'] = $total;
                    $arrRinci['diskon'] = $diskon;
                    $arrRinci['username'] = $userid;
                    $arrRinci['loket_name'] = $loket_name;
                    $arrRinci['loket_code'] = $loket_code;
                    $arrRinci['idgol'] = $gol;
                    $arrRinci['jenis_loket'] = $JenisLoket;
                    $arrRinci['beban_tetap'] = $beban_tetap;
                    $arrRinci['biaya_meter'] = $biaya_meter;

                    mPdambjmTrans::insert($arrRinci);
                    mLoket::updateSaldoLoket($total, $loket_code);

                    $arrRinci['gol'] = $gol;
                    $arrRinci['pakai'] = $pakai;
                    array_push($arrRek, $arrRinci);
                }

                // $arrLog = array();
                // $arrLog["username"] = $arrData[0]['User'];
                // $arrLog["customer_id"] = $arrData[0]['Idlgn'];
                // $arrLog["response"] = json_encode($response->RequestPaymentBulk_Rev2Result);
                // $arrLog["response_a"] = json_encode($arrRek);

                //mLogPdambjm::insert($arrLog);
                $updateAdvise = AdvisePDAM::where("idtrx", $idTrxPdam)->delete();
            }

            return Response::json(array(
                'status' => true,
                'response_code' => '0000',
                'message' => 'PAYMENT BERHASIL',
                'ref_number' => $unique_id,
                'data' => $arrData
            ),200);

        }catch (\Exception $e){
            $error = explode("\r\n",$e->getMessage());

            $this->simpanLogSistem(json_encode($error));
            
            return Response::json(array(
                'status' => false,
                'response_code' => '0005',
                'message' => 'ERROR OTHER'
            ),403);
        }
    }
}