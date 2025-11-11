<?php

namespace App\APIServices;

use App\Http\Controllers\Helpers;
use App\Models\mLogPdambjm;
use App\Models\mPdambjmTrans;
use App\Models\AdvisePDAM;
use SimpleXMLElement;
use App\Models\mLoket;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use App\Models\LogErrorPdam;
use App\Models\Register_hp;
use App\User;
use App\Models\SettingRekPdam;
use App\Models\PrintModel;
use Illuminate\Support\Facades\Storage;
use App\Services\PrintBaru;
use DB;
use App\Jobs\printQueue;

class PdamBjmAPIv2Service
{

    private function simpanLogSistem($Log){

		$mLog = new LogErrorPdam();
		$mLog->log = $Log;
		$mLog->save();
	}

	public function inqueryPelanggan($nopel = "", $loket = "", $isMobile = false, $session_id = "", $username = "", $imei = ""){

        if($username == ""){
            $user = Auth::user();
            $username = $user->username;
        }

        //PENGECEKAN DI MOBILE
        if($isMobile){
            // $dtImei = Register_hp::where("username","=",$username)->where("imei","=",$imei)->get();
            // if($dtImei->count()<=0){
            //     return Response::json(array(
            //         'status' => 'Error',
            //         'message' => 'Handphone tidak terdaftar untuk user ini'
            //     ),200);
            // }

            // $dtUser = User::where("username","=",$username)->first();
            // if($dtUser->count() > 0){
            //     $SessionId = $dtUser->session_id;
            //     if($session_id != $SessionId){
            //         return Response::json(array(
            //             'status' => 'Error',
            //             'message' => 'Session ID Tidak Valid'
            //         ),200);
            //     }
            // }else{
            //     return Response::json(array(
            //         'status' => 'Error',
            //         'message' => 'Username Tidak Valid'
            //     ),200);
            // }

            // $is_blok = $dtUser->loket->is_blok;
            // $blok_message = $dtUser->loket->blok_message;
            // $jenis_loket = $dtUser->loket->jenis;
            
            // if($is_blok == 1){
            //     return Response::json(array(
            //         'status' => 'Error',
            //         'message' => 'Message : '. $blok_message,
            //     ),200);
            // }
        }
        //END PENGECEKAN DI MOBILE

        if(strlen($nopel) < 7){
            return Response::json(array(
                'status' => 'Error',
                'message' => 'No Pelanggan Tidak Valid'
            ),200);
        }

        try{

            $setup = mLoket::where('loket_code','=',$loket)->first();
            if(is_null($setup)){
                return Response::json(array(
                    'status' => 'Error',
                    'message' => 'Biaya admin loket belum disetup'
                ),200);
            }

            $byadm = $setup->byadmin;

            $pdambjm_cfg = Config::get('app.pdambjm');

            // $listNonAdmin = array('LBERUNTUNG','LSPARMAN','LSUTOYO','LCEMARA');

            // if(!in_array($loket, $listNonAdmin)){
            //     $clientid = $pdambjm_cfg['clientid_admin'];
            //     $passwd = $pdambjm_cfg['password_admin'];
            // }else{
            //     $clientid = $pdambjm_cfg['clientid_non_admin'];
            //     $passwd = $pdambjm_cfg['password_non_admin'];
            // }

            //SET KE ADMIN SEMUA
            $clientid = $pdambjm_cfg['clientid_admin'];
            $passwd = $pdambjm_cfg['password_admin'];

            $request = $pdambjm_cfg['request'];
            $request = str_replace("{idpel}",$nopel,$request);
            $request = str_replace("{clientid}",$clientid,$request);
            $request = str_replace("{password}",$passwd,$request);

            $xml_pdam = Helpers::sent_http_get($request);

            if($xml_pdam["status"]=="Error"){
                return Response::json(array(
                    'status' => 'Error',
                    'message' => $xml_pdam["message"]
                ),200);
            }

            $xml_pdam = $xml_pdam["response"];
            $response_json = json_decode($xml_pdam,false);
            
            $arrLog = array();
            $arrLog["username"] = $loket . "-" . $username;
            $arrLog["customer_id"] = $nopel;
            $arrLog["response"] = $request;
            $arrLog["response_a"] = json_encode($response_json);

            mLogPdambjm::insert($arrLog);

            if($response_json->RequestPelangganRev2Result->status == "Error"){
                return Response::json(array(
                    'status' => 'Error',
                    'message' => $response_json->RequestPelangganRev2Result->message
                ),200);
            }else{
                $response_json = $response_json->RequestPelangganRev2Result->data;

                if(!is_array($response_json)){
                    $arrayjson = array();
                    $arrayjson[0] = $response_json;
                    $response_json = $arrayjson;
                }

                $TampilMobile = "";
                $TampilPel = "";

                $TotalMobile = 0;
                $RekMobile = 0;

                //dd($response_json);

                for($i=0;$i<sizeof($response_json);$i++){
                    $response_json[$i]->idlgn = $nopel;
                    $response_json[$i]->admin_kop = "$byadm";
                    if(json_encode($response_json[$i]->nama) == "{}") {
                        $response_json[$i]->nama = "empty";
                    }
                    
                    $adminKop = $response_json[$i]->admin_kop;
                    $Total = $response_json[$i]->total;
                    if(!is_numeric($adminKop)){
                        $adminKop = "0";
                    }

                    if(!is_numeric($Total)){
                        $Total = "0";
                    }

                    $GrandTotal = $Total + $adminKop;
                    $Pakai = $response_json[$i]->pakai;
                    $Denda = $response_json[$i]->denda;

                    $TampilMobile .= "<b style='color:#357ca5'>BL/TH</b> : <br/>".$response_json[$i]->thbln."<br/>";
                    $TampilMobile .= "<b style='color:#357ca5'>PAKAI</b> : <br/>".(!is_numeric($Pakai))?0:number_format($Pakai)." m3<br/>";
                    $TampilMobile .= "<b style='color:#357ca5'>DENDA</b> : <br/>Rp. ".(!is_numeric($Denda))?0:number_format($Denda)."<br/>";
                    $TampilMobile .= "<b style='color:#357ca5'>TAGIHAN PDAM</b> : <br/>Rp. ".(!is_numeric($Total))?0:number_format($Total)."<br/>";
                    $TampilMobile .= "<b style='color:#357ca5'>ADMIN</b> : <br/>Rp. ".(!is_numeric($adminKop))?0:number_format($adminKop)."<br/>";
                    $TampilMobile .= "<b style='color:#357ca5'>TOTAL</b> : <br/>Rp. ".(!is_numeric($GrandTotal))?0:number_format($GrandTotal)."<br/>";
                    $TampilMobile .= "<hr/>";

                    $TotalMobile += $GrandTotal;
                    $RekMobile += 1;

                }
                
                // $sisaSaldo = $setup->pulsa;
                // if($TotalMobile > $sisaSaldo){
                //     return Response::json(array(
                //         'status' => 'Error',
                //         'message' => "SALDO TIDAK CUKUP UNTUK PEMBAYARAN"
                //     ),200);
                // }

                $TampilPel .= "<b style='color:#357ca5'>IDPEL</b> : <br/>".$nopel."<br/>";
                $TampilPel .= "<b style='color:#357ca5'>NAMA</b> : <br/>".$response_json[0]->nama."<br/>";
                $TampilPel .= "<b style='color:#357ca5'>ALAMAT</b> : <br/>".$response_json[0]->alamat."<br/>";
                $TampilPel .= "<b style='color:#357ca5'>TOTAL BAYAR</b> : <br/>Rp. ".number_format($TotalMobile)."<br/>";
                $TampilPel .= "<hr/>";
                $TampilPel .= "DETAIL REKENING (".$RekMobile." LEMBAR)<br/>";
                $TampilPel .= "<hr/>";

                $TampilInquery = $TampilPel.$TampilMobile;


                //CEK APAKAH AKSES JML REKENING DIBOLEHKAN
                $jmlData = sizeof($response_json);

                $jmlRek = 0;
                $cekAkses = SettingRekPdam::where('loket_code','=',$loket)->first();
                if($cekAkses){
                    $jmlRek = $cekAkses->jml_rek_pdam;
                }

                if($jmlData > $jmlRek){
                    return Response::json(array(
                        'status' => "Error",
                        'message' => "UNAUTHORIZED INQUERY"
                    ),200);
                }

                if($isMobile){
                    return Response::json(array(
                        'status' => 'Success',
                        'message' => 'None',
                        'tampil' => $TampilInquery,
                        'data' => $response_json
                    ),200);
                }else{
                    return Response::json(array(
                        'status' => 'Success',
                        'message' => 'None',
                        'data' => $response_json
                    ),200);
                }
                
            }
            
        }catch (\Exception $e){
            $error = explode("\r\n",$e->getMessage());

            $arrLog = array();
            $arrLog["username"] = $loket;
            $arrLog["customer_id"] = $nopel;
            $arrLog["response"] = "ERROR_INQUERY";
            $arrLog["response_a"] = json_encode($error);

            mLogPdambjm::insert($arrLog);

            return Response::json(array(
                'status' => 'Error',
                'message' => 'Terjadi kesalahan sistem.'
            ),200);
        }
    }

    public function requestPayment($PaymentData, $isMobile = false, $session_id = "", $username = "", $imei = ""){
        try{

            if($username == ""){
                $user = Auth::user();
                $username = $user->username;
            }

            if($isMobile){
                
                $arrData = $PaymentData;
            }else{
                $arrData = $PaymentData["PaymentData"];
            }

            //REQUEST PAYMENT
            $idpel = $arrData[0]['Idlgn'];
            $dt1 = date("Ymd");
            $dt2 = date("Y-m-d H:i:s");
            $unique_id = strtoupper(date('YmdHis').'-'.uniqid());
            $IdTrans = $unique_id;
            $loket_code = $arrData[0]['LoketCode'];

            //GET JENIS LOKET
            $mLoket = mLoket::where('loket_code',$loket_code)->first();
            if(!$mLoket){
                return Response::json(array(
                    'status' => 'Error',
                    'error_code' => '301',
                    'message' => 'Kode Loket Tidak Valid.'
                ),200);
            }

            //CEK APAKAH AKSES JML REKENING DIBOLEHKAN
            $jmlData = sizeof($arrData);

            $jmlRek = 0;
            $cekAkses = SettingRekPdam::where('loket_code','=',$loket_code)->first();
            if($cekAkses){
                $jmlRek = $cekAkses->jml_rek_pdam;
            }

            if($jmlData > $jmlRek){
                return Response::json(array(
                    'status' => "Error",
                    'error_code' => '201',
                    'message' => "UNAUTHORIZED PAYMENT"
                ),200);
            }

            $JenisLoket = $mLoket->jenis;
            //END GET JENIS LOKET    
            $total = 0;

            for($i=0;$i<sizeof($arrData); $i++) {
                $total += $arrData[$i]['Total'];
            }
            
            $sisaSaldo = $mLoket->pulsa;
            if($total > $sisaSaldo){
                return Response::json(array(
                    'status' => 'Error',
                    'error_code' => '201',
                    'message' => "SALDO TIDAK CUKUP UNTUK PEMBAYARAN"
                ),200);
            }

            $cdata = "1|".$idpel."|"."0"."|".$total."|".$dt1."|".$IdTrans."|".$loket_code."|07|".$dt2;

            //Get Konfigurasi Web Service Address
            $pdambjm_cfg = Config::get('app.pdambjm');

            // $listNonAdmin = array('LBERUNTUNG','LSPARMAN','LSUTOYO','LCEMARA');

            // if(!in_array($loket_code, $listNonAdmin)){
            //     $idclient = $pdambjm_cfg['clientid_admin'];
            //     $passwd = $pdambjm_cfg['password_admin'];
            // }else{
            //     $idclient = $pdambjm_cfg['clientid_non_admin'];
            //     $passwd = $pdambjm_cfg['password_non_admin'];
            // }

            //SET KE ADMIN SEMUA
            $idclient = $pdambjm_cfg['clientid_admin'];
            $passwd = $pdambjm_cfg['password_admin'];
            
            //BEGIN CEK JENIS LOKET ALIHKAN KE PM ATAU ANDROID          
            // switch($JenisLoket){
            //     case "PM":
            //         $idclient = $pdambjm_cfg['clientid_pm'];
            //         $passwd = $pdambjm_cfg['password_pm'];
            //         break;
            //     case "ANDROID":
            //         $idclient = $pdambjm_cfg['clientid_andro'];
            //         $passwd = $pdambjm_cfg['password_andro'];
            //         break;
            //     default :
            //         $idclient = $pdambjm_cfg['clientid'];
            //         $passwd = $pdambjm_cfg['password'];
                
            // }        
            //END CEK JENIS LOKET ALIHKAN KE PM ATAU ANDROID

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
            $simpanAdvise->username = $username;    
            $simpanAdvise->save();
            //END SIMPAN ADVISE PDAM
            
            //SENT HTTP POST
            $response_payment = Helpers::sent_http_post($request,"");

            $arrLog = array();
            $arrLog["username"] = $loket_code . "-" . $username;
            $arrLog["customer_id"] = $idpel;
            $arrLog["response"] = $request;
            $arrLog["response_a"] = json_encode($response_payment);

            mLogPdambjm::insert($arrLog);

            //BILA STATUS HTTP POST ERROR
            if($response_payment['status'] == "Error"){
                return Response::json(array(
                    'status' => 'Error',
                    'error_code' => '301',
                    'message' => 'Error Permintaan Payment'
                ),200);
            }

            $response = $response_payment["response"];
            $response = json_decode($response,false);

            //BILA HASIL DARI API PDAM ERROR
            if($response->RequestPaymentBulk_Rev2Result->status == "Error"){
                return Response::json(array(
                    'status' => 'Error',
                    'error_code' => '401',
                    'message' => $idpel . " " . $response->RequestPaymentBulk_Rev2Result->message
                ),200);
            }

            $arrRek = array();
            $FormatCetakan = "";

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

                    $cetak = "PPOB - PEDAMI PAYMENT\nPDAM BANDARMASIH\n=======================\n\nIdpel : ".$nopel." \nNama : ".$nama."\nAlamat : ".$alamat."\nGol : ".$gol."\nBlth : ".$thbl."\nStand : ".$stand_l." - ".$stand_i."\nPakai : ".$pakai."\nHarga air : ".number_format($harga)."\nLimbah : ".number_format($limbah)."\nMaterai :".number_format($materai)."\nRetribusi : ".number_format($retribusi)."\nAbodemen : ".number_format($byadm)."\nBeban Tetap : ".number_format($beban_tetap)."\nBiaya P.Meter : ".number_format($biaya_meter)."\nDenda : ".number_format($denda)."\nSub Total : ".number_format($sub_tot)."\nAdmin : ".number_format($admin_kop)."\nTotal : ".number_format($total)."\n=======================\n".$unique_id."/".$userid."/".$loket_code."/".$tgl_server."\n";
                    
                    $FormatCetakan .= $cetak;
                }

                $updateAdvise = AdvisePDAM::where("idtrx", $idTrxPdam)->delete();
            }

            if($isMobile){

                return Response::json(array(
                    'status' => 'Success',
                    'message' => 'Payment Berhasil',
                    'error_code' => '200',
                    'cetakan' => $FormatCetakan,
                    'data' => $arrRek
                ),200);
            }else{
                return Response::json(array(
                    'status' => 'Success',
                    'message' => 'Payment Berhasil',
                    'error_code' => '200',
                    'data' => $arrRek
                ),200);
            }
        }catch (\Exception $e){
            $error = explode("\r\n",$e->getMessage());

            $this->simpanLogSistem(json_encode($error));
                
            return Response::json(array(
                'status' => 'Error',
                'message' => 'Terjadi Kesalahan Sistem',
                'error_code' => '501'
            ),200);
        }
    }

    public function requestPaymentBaru($PaymentData, $isMobile = false, $session_id = "", $username = "", $imei = "", 
        $isPrinterBaru, $jenisKertas){
        try{

            if($username == ""){
                $user = Auth::user();
                $username = $user->username;
            }

            if($isMobile){
                
                $arrData = $PaymentData;
            }else{
                $arrData = $PaymentData["PaymentData"];
            }

            //REQUEST PAYMENT
            $idpel = $arrData[0]['Idlgn'];
            $dt1 = date("Ymd");
            $dt2 = date("Y-m-d H:i:s");
            $unique_id = strtoupper(date('YmdHis').'-'.uniqid());
            $IdTrans = $unique_id;
            $loket_code = $arrData[0]['LoketCode'];

            //GET JENIS LOKET
            $mLoket = mLoket::where('loket_code',$loket_code)->first();
            if(!$mLoket){
                return Response::json(array(
                    'status' => 'Error',
                    'error_code' => '301',
                    'message' => 'Kode Loket Tidak Valid.'
                ),200);
            }

            //CEK APAKAH AKSES JML REKENING DIBOLEHKAN
            $jmlData = sizeof($arrData);

            $jmlRek = 0;
            $cekAkses = SettingRekPdam::where('loket_code','=',$loket_code)->first();
            if($cekAkses){
                $jmlRek = $cekAkses->jml_rek_pdam;
            }

            if($jmlData > $jmlRek){
                return Response::json(array(
                    'status' => "Error",
                    'error_code' => '201',
                    'message' => "UNAUTHORIZED PAYMENT"
                ),200);
            }

            $JenisLoket = $mLoket->jenis;
            //END GET JENIS LOKET    
            $total = 0;

            for($i=0;$i<sizeof($arrData); $i++) {
                $total += $arrData[$i]['Total'];
            }
            
            $sisaSaldo = $mLoket->pulsa;
            if($total > $sisaSaldo){
                return Response::json(array(
                    'status' => 'Error',
                    'error_code' => '201',
                    'message' => "SALDO TIDAK CUKUP UNTUK PEMBAYARAN"
                ),200);
            }

            $cdata = "1|".$idpel."|"."0"."|".$total."|".$dt1."|".$IdTrans."|".$loket_code."|07|".$dt2;

            //Get Konfigurasi Web Service Address
            $pdambjm_cfg = Config::get('app.pdambjm');

            //SET KE ADMIN SEMUA
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
            $simpanAdvise->username = $username;
            $simpanAdvise->save();
            //END SIMPAN ADVISE PDAM
            
            //SENT HTTP POST
            $response_payment = Helpers::sent_http_post($request,"");
            
            $arrLog = array();
            $arrLog["username"] = $loket_code . "-" . $username;
            $arrLog["customer_id"] = $idpel;
            $arrLog["response"] = $request;
            $arrLog["response_a"] = json_encode($response_payment);

            mLogPdambjm::insert($arrLog);

            //BILA STATUS HTTP POST ERROR
            if($response_payment['status'] == "Error"){
                return Response::json(array(
                    'status' => 'Error',
                    'error_code' => '301',
                    'message' => 'Error Permintaan Payment'
                ),200);
            }

            $response = $response_payment["response"];
            $response = json_decode($response,false);

            //BILA HASIL DARI API PDAM ERROR
            if($response->RequestPaymentBulk_Rev2Result->status == "Error"){
                return Response::json(array(
                    'status' => 'Error',
                    'error_code' => '401',
                    'message' => $idpel . " " . $response->RequestPaymentBulk_Rev2Result->message
                ),200);
            }

            $arrRek = array();
            $FormatCetakan = "";

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

                    $cetak = "PPOB - PEDAMI PAYMENT\nPDAM BANDARMASIH\n=======================\n\nIdpel : ".$nopel." \nNama : ".$nama."\nAlamat : ".$alamat."\nGol : ".$gol."\nBlth : ".$thbl."\nStand : ".$stand_l." - ".$stand_i."\nPakai : ".$pakai."\nHarga air : ".number_format($harga)."\nLimbah : ".number_format($limbah)."\nMaterai :".number_format($materai)."\nRetribusi : ".number_format($retribusi)."\nAbodemen : ".number_format($byadm)."\nBeban Tetap : ".number_format($beban_tetap)."\nBiaya P.Meter : ".number_format($biaya_meter)."\nDenda : ".number_format($denda)."\nSub Total : ".number_format($sub_tot)."\nAdmin : ".number_format($admin_kop)."\nTotal : ".number_format($total)."\n=======================\n".$unique_id."/".$userid."/".$loket_code."/".$tgl_server."\n";
                    
                    $FormatCetakan .= $cetak;
                }

                $updateAdvise = AdvisePDAM::where("idtrx", $idTrxPdam)->delete();
            }

            if($isMobile){

                return Response::json(array(
                    'status' => 'Success',
                    'message' => 'Payment Berhasil',
                    'error_code' => '200',
                    'cetakan' => $FormatCetakan,
                    'data' => $arrRek
                ),200);
            }else{

                //$dataPrint = PrintBaru::handlePrintBaru($arrRek, $jenisKertas, "PDAMBJM");

                // return Response::json(array(
                //     'status' => 'Success',
                //     'message' => 'Payment Berhasil',
                //     'is_print_baru' => $isPrinterBaru,
                //     'jenis_kertas' => $jenisKertas,
                //     'print_data' => $dataPrint,
                //     'error_code' => '200',
                //     'data' => $arrRek
                // ),200);

                return Response::json(array(
                    'status' => 'Success',
                    'message' => 'Payment Berhasil',
                    'is_print_baru' => $isPrinterBaru,
                    'jenis_kertas' => $jenisKertas,
                    'print_data' => "-",
                    'error_code' => '200',
                    'data' => $arrRek
                ),200);

            }
        }catch (\Exception $e){
            $error = explode("\r\n",$e->getMessage());
            
            $arrLog = array();
            $arrLog["username"] = $idpel;
            $arrLog["customer_id"] = $loket_code;
            $arrLog["response"] = "ERROR_PAYMENT";
            $arrLog["response_a"] = json_encode($error);

            //mLogPdambjm::insert($arrLog);
            $this->simpanLogSistem(json_encode($error));
            
            return Response::json(array(
                'status' => 'Error',
                'message' => 'Terjadi Kesalahan Sistem',
                'error_code' => '501'
            ),200);
        }
    }

    public function requestCetakUlang($idlgn, $tgl_transaksi, $blnrek){
        $arrRek = array();
        
        if($blnrek == '-'){
            $dtRek = mPdambjmTrans::where("cust_id",$idlgn)
            ->whereRaw("CAST(transaction_date AS DATE) = '".$tgl_transaksi."'")->get();
        }else{
            $dtRek = mPdambjmTrans::where("cust_id",$idlgn)
            ->where("blth",$blnrek)
            ->whereRaw("CAST(transaction_date AS DATE) = '".$tgl_transaksi."'")->get();
        }
        
        $numDt = $dtRek->count();
        if($numDt>0){
            $arrData = $dtRek->toArray();

            for($i=0;$i<sizeof($arrData); $i++) {

                $arrRinci = array();
                $arrRinci['transaction_code'] = $arrData[$i]['transaction_code'] . "-CU";
                $arrRinci['transaction_date'] = $arrData[$i]['transaction_date'];
                $arrRinci['cust_id'] = $arrData[$i]['cust_id'];
                $arrRinci['nama'] = $arrData[$i]['nama'];
                $arrRinci['alamat'] = $arrData[$i]['alamat'];
                $arrRinci['blth'] = $arrData[$i]['blth'];
                $arrRinci['harga_air'] = $arrData[$i]['harga_air'];
                $arrRinci['abodemen'] = $arrData[$i]['abodemen'];
                $arrRinci['materai'] = $arrData[$i]['materai'];
                $arrRinci['limbah'] = $arrData[$i]['limbah'];
                $arrRinci['retribusi'] = $arrData[$i]['retribusi'];
                $arrRinci['denda'] = $arrData[$i]['denda'];
                $arrRinci['stand_lalu'] = $arrData[$i]['stand_lalu'];
                $arrRinci['stand_kini'] = $arrData[$i]['stand_kini'];
                $arrRinci['sub_total'] = $arrData[$i]['sub_total'];
                $arrRinci['admin'] = $arrData[$i]['admin'];
                $arrRinci['total'] = $arrData[$i]['total'];
                $arrRinci['diskon'] = $arrData[$i]['diskon'];
                $arrRinci['username'] = $arrData[$i]['username'];
                $arrRinci['loket_code'] = $arrData[$i]['loket_code'];
                $arrRinci['beban_tetap'] = $arrData[$i]['beban_tetap'];
                $arrRinci['biaya_meter'] = $arrData[$i]['biaya_meter'];
                $arrRinci['gol'] = $arrData[$i]['idgol'];
                $arrRinci['pakai'] = $arrData[$i]['stand_kini']-$arrData[$i]['stand_lalu'];
                array_push($arrRek, $arrRinci);
            }
            return Response::json(array(
                'status' => 'Success',
                'message' => '-',
                'error_code' => '200',
                'data' => $arrRek
            ),200);
        }else{
            return Response::json(array(
                'status' => 'Error',
                'message' => 'Data Pembayaran Tidak Ditemukan',
                'error_code' => '404'
            ),200);
        }
    }

    public function requestCetakUlangBaru($idlgn, $tgl_awal, $tgl_akhir, $blnrek, $isPrinterBaru, $jenisKertas){
        $arrRek = array();
        
        if($blnrek == '-'){
            $dtRek = mPdambjmTrans::where("cust_id",$idlgn)
            ->whereBetween(DB::raw('cast(transaction_date as date)'),[$tgl_awal, $tgl_akhir]);
        }else{
            $dtRek = mPdambjmTrans::where("cust_id",$idlgn)
            ->where("blth",$blnrek)
            ->whereBetween(DB::raw('cast(transaction_date as date)'),[$tgl_awal, $tgl_akhir]);
        }

        if (!Auth::user()->hasPermissionTo('Cetak Ulang Semua')) {
            $idLoket = Auth::user()->loket_id;
            $kodeLoket = mLoket::where("id",$idLoket)->first()->loket_code;

            $dtRek = $dtRek->where('loket_code',$kodeLoket)->get();
        }else{
            $dtRek = $dtRek->get();
        }
        
        $numDt = $dtRek->count();
        if($numDt>0){
            $arrData = $dtRek->toArray();

            for($i=0;$i<sizeof($arrData); $i++) {

                $arrRinci = array();
                $arrRinci['transaction_code'] = $arrData[$i]['transaction_code'] . "-CU";
                $arrRinci['transaction_date'] = $arrData[$i]['transaction_date'];
                $arrRinci['cust_id'] = $arrData[$i]['cust_id'];
                $arrRinci['nama'] = $arrData[$i]['nama'];
                $arrRinci['alamat'] = $arrData[$i]['alamat'];
                $arrRinci['blth'] = $arrData[$i]['blth'];
                $arrRinci['harga_air'] = $arrData[$i]['harga_air'];
                $arrRinci['abodemen'] = $arrData[$i]['abodemen'];
                $arrRinci['materai'] = $arrData[$i]['materai'];
                $arrRinci['limbah'] = $arrData[$i]['limbah'];
                $arrRinci['retribusi'] = $arrData[$i]['retribusi'];
                $arrRinci['denda'] = $arrData[$i]['denda'];
                $arrRinci['stand_lalu'] = $arrData[$i]['stand_lalu'];
                $arrRinci['stand_kini'] = $arrData[$i]['stand_kini'];
                $arrRinci['sub_total'] = $arrData[$i]['sub_total'];
                $arrRinci['admin'] = $arrData[$i]['admin'];
                $arrRinci['total'] = $arrData[$i]['total'];
                $arrRinci['diskon'] = $arrData[$i]['diskon'];
                $arrRinci['username'] = $arrData[$i]['username'];
                $arrRinci['loket_code'] = $arrData[$i]['loket_code'];
                $arrRinci['beban_tetap'] = $arrData[$i]['beban_tetap'];
                $arrRinci['biaya_meter'] = $arrData[$i]['biaya_meter'];
                $arrRinci['gol'] = $arrData[$i]['idgol'];
                $arrRinci['pakai'] = $arrData[$i]['stand_kini']-$arrData[$i]['stand_lalu'];
                array_push($arrRek, $arrRinci);
            }

            if($isPrinterBaru > 0){
                $dataPrint = PrintBaru::handlePrintBaru($arrRek, $jenisKertas, "PDAMBJM");
                //$post = $this->postQueue(Auth::user()->username,$arrRek, $jenisKertas, "PDAMBJM");

                return Response::json(array(
                    'status' => 'Success',
                    'message' => '-',
                    'is_print_baru' => $isPrinterBaru,
                    'jenis_kertas' => $jenisKertas,
                    'print_data' => $dataPrint,
                    'error_code' => '200',
                    'data' => $arrRek
                ),200);

            }else{
                return Response::json(array(
                    'status' => 'Success',
                    'message' => '-',
                    'is_print_baru' => $isPrinterBaru,
                    'error_code' => '200',
                    'data' => $arrRek
                ),200);
            }
            
        }else{
            return Response::json(array(
                'status' => 'Error',
                'message' => 'Data Pembayaran Tidak Ditemukan',
                'error_code' => '404'
            ),200);
        }
    }

    private function postQueue($username,$rek,$jenisKertas,$layanan){
        $rek = json_encode($rek);
        $post = Helpers::setHttpPostQueue(env('QUEUE_SERVER','')."/printing/insert_queue",$username,$rek,$jenisKertas,$layanan);
        //printQueue::dispatch($username,$rek,$jenisKertas,$layanan);

        return "Sukses";
    }

    public function prosesAdvise($nomor_pelanggan,$produk,$idtrx,$is_mobile,$username){

		try{
            
            $advise = AdvisePDAM::where("idtrx",$idtrx)->first();
			$adviseMessage = $advise->advise_message;
            
            $response = Helpers::sent_http_get($adviseMessage);

            $response = $response["response"];
            $response = json_decode($response,false);

            //BILA HASIL DARI API PDAM ERROR
            if($response->RequestLppTanggalResult->status == "Error"){
                return array(
                    'status' => true,
                    'response_code' => "0000",
                    'message' => $response->RequestLppTanggalResult->message
                );
            }

            $arrData = json_encode($response->RequestLppTanggalResult->data);
            $arrData = json_decode($arrData, true);

            $loket_name = "";
            $loket_code = "";
            $jenis_loket = "";
            $byadmin = 0;

            $loket_id = User::where('username',$username)->select('loket_id')->first()->loket_id;
            $cekLoket = mLoket::where("id",$loket_id)->first();
            if($cekLoket != null){
                $loket_name = $cekLoket->nama;
                $loket_code = $cekLoket->loket_code;
                $jenis_loket = $cekLoket->jenis;
                $byadmin = $cekLoket->byadmin;
            }

            $unique_id = strtoupper(date('YmdHis').'-'.uniqid());
            $IdTrans = $unique_id;

            $arrRek = array();
            $pesan_payment = $response->RequestLppTanggalResult->status;
            if($pesan_payment=="Success"){

                $loketPDAM = $arrData[0]['kasir'];
                if($loketPDAM != "KPADM"){
                    return array(
                        'status' => false,
                        'response_code' => "9991",
                        'message' => "LOKET LUNAS BUKAN DI PEDAMI"
                    );
                }

                for($i=0;$i<sizeof($arrData); $i++) {

                    $nopel = $arrData[$i]['idlgn'];
                    $nama = $arrData[$i]['nama'];
                    $alamat = $arrData[$i]['alamat'];
                    $gol = $arrData[$i]['gol'];
                    $thbl = $arrData[$i]['thbln'];
                    $pakai = $arrData[$i]['pakai'];
                    $harga = $arrData[$i]['harga'];
                    $byadm = $arrData[$i]['byadmin'];
                    //$beban_tetap = $arrData[$i]['beban_tetap'];
                    //$biaya_meter = $arrData[$i]['biaya_meter'];
                    $beban_tetap = "0";
                    $biaya_meter = "0";
                    $materai = $arrData[$i]['materai'];
                    $retribusi = $arrData[$i]['retribusi'];
                    $denda = $arrData[$i]['denda'];
                    $sub_tot = $arrData[$i]['total'];
                    $limbah = $arrData[$i]['limbah'];

                    $total = $byadmin+$sub_tot;
                    //$diskon = "$arrData[$i]['Diskon']";
                    $diskon = "0";

                    $stand_l = $arrData[$i]['stand_l'];
                    $stand_i = $arrData[$i]['stand_i'];

                    $admin_kop = $byadmin;
                    $tgl_server = $arrData[$i]['tanggal'];

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
                    $arrRinci['stand_lalu'] = str_replace(",",".",$stand_l);
                    $arrRinci['stand_kini'] = str_replace(",",".",$stand_i);
                    $arrRinci['sub_total'] = $sub_tot;
                    $arrRinci['admin'] = $admin_kop;
                    $arrRinci['total'] = $total;
                    $arrRinci['diskon'] = $diskon;
                    $arrRinci['username'] = $username;
                    $arrRinci['loket_name'] = $loket_name;
                    $arrRinci['loket_code'] = $loket_code;
                    $arrRinci['idgol'] = $gol;
                    $arrRinci['jenis_loket'] = $jenis_loket;
                    $arrRinci['beban_tetap'] = $beban_tetap;
                    $arrRinci['biaya_meter'] = $biaya_meter;

                    mPdambjmTrans::insert($arrRinci);
                    mLoket::updateSaldoLoket($total, $loket_code);

                    $arrRinci['gol'] = $gol;
                    $arrRinci['pakai'] = $pakai;
                    array_push($arrRek, $arrRinci);
                }

                $updateAdvise = AdvisePDAM::where("idtrx", $idtrx)->delete();
            }

            return array(
                'status' => true,
                'response_code' => "0000",
                'message' => "Transaksi Advise PDAM Berhasil",
                'customer' => $arrRek
            );

		}catch (Exception $e) {
    		$err_message = explode("\r\n",$e->getMessage());
    		$this->simpanLogSistem(json_encode($err_message));
	        return array(
                'status' => false,
                'response_code' => "9991",
                'message' => "TERJADI KESALAHAN, TRANSAKSI GAGAL."
            );
	    }
	}
}