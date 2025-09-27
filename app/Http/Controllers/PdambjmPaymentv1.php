<?php namespace App\Http\Controllers;


use App\Models\mPdambjmTrans;
use SimpleXMLElement;
use App\Models\mLoket;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Config;

class PdambjmPaymentv1 extends Controller
{

    public function __construct()
    {
        //$this->middleware('auth');
    }

    public function doPayment(){
        $PaymentData = Input::get('PaymentData');
        try{
            //Data yang dikirim dari Client berupa Format JSON
            $arrData = $PaymentData["PaymentData"];
            //return var_dump($arrData);
            $nopel = $arrData[0]['Idlgn'];
            $nama = $arrData[0]['Nama'];
            $alamat = $arrData[0]['Alamat'];
            $gol = $arrData[0]['Idgol'];
            $thbl = $arrData[0]['Thbln'];
            $pakai = $arrData[0]['Pakai'];
            $harga = $arrData[0]['Harga'];
            $byadm = $arrData[0]['ByAdmin'];
            $materai = $arrData[0]['Materai'];
            $retribusi = $arrData[0]['Retri'];
            $denda = $arrData[0]['Denda'];
            $sub_tot = $arrData[0]['Sub_Tot'];
            $limbah = $arrData[0]['Limbah'];
            $total = $arrData[0]['Total'];
            $stand_l = $arrData[0]['Stand_l'];
            $stand_i = $arrData[0]['Stand_i'];
            $admin_kop = $arrData[0]['Admin_Kop'];
            $userid = $arrData[0]['User'];
            $loket_name = $arrData[0]['LoketName'];
            $loket_code = $arrData[0]['LoketCode'];
            $total_byr = $total+$admin_kop;

            //Generate Unique ID
            $unique_id = strtoupper(date('YmdHis').'-'.uniqid());
            $tgl_server = date('Y-m-d H:i:s');

            //Set Kode Transaksi ke arrayData[0]
            $arrData[0]['Kode_Transaksi'] = $unique_id;
            $kd_trans = $arrData[0]['Kode_Transaksi'];

            //Keluarkan Data Komputer dari arrayData[0]
            unset($arrData[0]['Nama_Komp']);
            unset($arrData[0]['User_Komp']);

            //Data untuk Rincian Transaksi Loket
            $arrRinci = array();
            $arrRinci['transaction_code']=$kd_trans;
            $arrRinci['transaction_date']=$tgl_server;
            $arrRinci['cust_id']=$nopel;
            $arrRinci['nama']=$nama;
            $arrRinci['alamat']=$alamat;
            $arrRinci['blth']=$thbl;
            $arrRinci['harga_air']=$harga;
            $arrRinci['abodemen']=$byadm;
            $arrRinci['materai']=$materai;
            $arrRinci['limbah']=$limbah;
            $arrRinci['retribusi']=$retribusi;
            $arrRinci['denda']=$denda;
            $arrRinci['stand_lalu']=$stand_l;
            $arrRinci['stand_kini']=$stand_i;
            $arrRinci['sub_total']=$sub_tot;
            $arrRinci['admin']=$admin_kop;
            $arrRinci['total']=$total;
            $arrRinci['username']=$userid;
            $arrRinci['loket_name']=$loket_name;
            $arrRinci['loket_code']=$loket_code;

//            mPdambjmTrans::insert($arrRinci);
//
//            return Response::json(array(
//                'status' => 'Success',
//                'message' => 'Berhasil',
//            ),200);

            //Proses pengiriman data ke server PDAM
            $dt1 = date("Ymd");
            $dt2 = date("Y-m-d");
            $IdTrans = $unique_id;
            $cdata = "1|".$nopel."|".$thbl."|".$total."|".$dt1."|".$IdTrans."|".$userid."|07|".$dt2;

            //Get Konfigurasi Web Service Address
            $pdambjm_cfg = Config::get('app.pdambjm');
            $idclient = $pdambjm_cfg['clientid'];
            $passwd = $pdambjm_cfg['password'];

            $request = $pdambjm_cfg['payment'];
            $request = str_replace("{clientid}",$idclient,$request);
            $request = str_replace("{password}",$passwd,$request);
            $request = str_replace("{cdata}",$cdata,$request);

            //Sent Data to Web Service PDAM
            $xml_pdam = Helpers::sent_service_payment($request,"");
            $response_str = htmlentities($xml_pdam);
            $response_str = str_replace("a:","",$response_str);
            $response_xml = new SimpleXMLElement(htmlspecialchars_decode($response_str));

            $response_json = json_encode($response_xml);
            $response_json = json_decode($response_json);

            $pesan_payment = $response_json->RequestPaymentResult->status;
            if($pesan_payment=="SUKSES"){
                //Insert Data Transaksi ke Database
                mPdambjmTrans::insert($arrRinci);
                mLoket::updateSaldoLoket($total, $loket_code);

                //Informasi Tambahan yang dikembalikan ke client seperti : Sisa Pulsa, ID Transaksi, Tgl Server
//                $infoloket = GpUserLoket::where('kodeloket','=',$userid)->firstOrFail();
//                $sisapulsa = $infoloket->tot_pulsa;

                $response_json->RequestPaymentResult->nama = $nama;
                $response_json->RequestPaymentResult->alamat = $alamat;
                $response_json->RequestPaymentResult->gol = $gol;
                $response_json->RequestPaymentResult->pakai = $pakai;
                $response_json->RequestPaymentResult->harga = $harga;
                $response_json->RequestPaymentResult->byadmin = $byadm;
                $response_json->RequestPaymentResult->materai = $materai;
                $response_json->RequestPaymentResult->retri = $retribusi;
                $response_json->RequestPaymentResult->denda = $denda;
                $response_json->RequestPaymentResult->sub_tot = $sub_tot;
                $response_json->RequestPaymentResult->limbah = $limbah;
                $response_json->RequestPaymentResult->stand_l = $stand_l;
                $response_json->RequestPaymentResult->stand_i = $stand_i;
                $response_json->RequestPaymentResult->admin_kop = $admin_kop;

                //$response_json->RequestPaymentResult->sisapulsa = $sisapulsa;
                $response_json->RequestPaymentResult->unique_id = $unique_id;
                $response_json->RequestPaymentResult->tgl_server = $tgl_server;
                $response_json->RequestPaymentResult->kodeloket = $userid;

                unset($response_json->RequestPaymentResult->keterangan);
                unset($response_json->RequestPaymentResult->status);
                $response_json->RequestPaymentResult->status = "Sukses";

                return Response::json(array(
                    'status' => 'Success',
                    'message' => 'None',
                    'data' => $response_json->RequestPaymentResult,
                ),200);
            }else{
                return Response::json(array(
                    'status' => 'Error',
                    'message' => 'Payment Gagal',
                ),200);
            }
        }catch (Exception $e){
            $error = explode("\r\n",$e->getMessage());
            return Response::json(array(
                'status' => 'Error',
                'message' => $error,
            ),200);
        }
    }

}