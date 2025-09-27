<?php namespace App\Http\Controllers;

use Request;
use Response;
use Illuminate\Support\Facades\Input;
use App\Models\mLoket;
use App\Models\mLogPdambjm;
use App\Models\mPdambjmTrans;
use App\Models\mRekapTransaksi;
use App\User;
use App\Models\mRekapTransaksiAndro;
use App\Models\Register_hp;
use SimpleXMLElement;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use App\APIServices\PdamBjmAPIv2;
use App\Models\vwRekapTransaksi;
use App\Models\vwDetailTransaksi;
use App\Models\mBerita;
use App\Models\PlnTransaksi;
use App\Models\PlnPrepaidTransaksi;
use App\Models\PlnNontaglisTransaksi;
use App\Models\vwPdambjm;
use App\Models\vwTransaksiPln;
use App\Models\vwTransaksiPlnPrepaid;
use App\Models\vwTransaksiPlnNontag;
use App\Models\TanpaImei;
use DB;


class MobileController extends Controller {

	public function doLogin(){

        $username = Request::get('username');
        $password = Request::get('password');
        $imei = Request::get('imei');

        $userdata = array(
            'username' => $username,
            'password' => $password
        );
        // doing login.
        if (Auth::validate($userdata)) {
            if (Auth::attempt($userdata)) {

                $user = Auth::user();
				
                $dataLoket = mLoket::where('id',$user->loket_id)->first();
                if(!$dataLoket){
                    return Response::json(array(
                        'status' => 'Error',
                        'message' => 'Loket Tidak Valid.',
                    ),200);
                }

				$JenisLoket = $dataLoket->jenis;						
                $dtImei = Register_hp::where("username","=",$username)->where("imei","=",$imei)->get();
                $exceptImei = TanpaImei::where("username","=",$username)->get();
				if($dtImei->count()<=0 && $exceptImei->count()<=0){
					return Response::json(array(
						'status' => 'Error',
						'message' => 'Handphone tidak terdaftar untuk user ini',
					),200);
				}

				if($JenisLoket!="ANDROID" && $JenisLoket!="PM"){
					return Response::json(array(
						'status' => 'Error',
						'message' => 'Hanya Loket Android yang bisa Login',
					),200);
				}
				
				$is_blok = $dataLoket->is_blok;
				$blok_message = $dataLoket->blok_message;

				if($is_blok == 1){
					return Response::json(array(
						'status' => 'Error',
						'message' => 'LOKET DIBLOK : '. $blok_message,
					),200);
				}
				
                $user->session_id = Auth::getSession()->getId();
                $user->save();

                $data['kode_loket'] = $dataLoket->loket_code;
                $data['kode_loket'] = $dataLoket->loket_code;
                $data['nama_loket'] = $dataLoket->nama;
                $data['jenis_loket'] = $dataLoket->jenis;
                $data['pulsa'] = $dataLoket->pulsa;

                $userData = array_merge($user->toArray(),$data);

                return Response::json(array(
                    'status' => 'Success',
                    'message' => 'Berhasil',
                    'data' => $userData,
                ),200);
            }
        }else {
            return Response::json(array(
                'status' => 'Error',
                'message' => 'Username atau Password Salah',
            ),200);
        }

    }

    public function getPelanggan($nopel = "", $loket = "", $session_id = "", $username = "", $imei = ""){

        $Response = PdamBjmAPIv2::inqueryPelanggan($nopel,$loket,true,$session_id,$username,$imei);
        return $Response;
    }

    public function doPayment(){
        $PaymentData = Input::get('paymentdata');
        $session_id = Input::get('sessionid');
        $username = Input::get('username');
        $imei = Input::get('imei');

        $PaymentData = json_decode($PaymentData, true); 

        $TotalBayar = 0;
        for($i=0;$i<sizeof($PaymentData);$i++){
            $Total = $PaymentData[$i]['Total'];
            $TotalBayar += $Total;
            $PaymentData[$i]['Diskon'] = "0";
        }

        $loketId = User::where('username',$username)->first()->loket_id;
        $pulsa = mLoket::where('id',$loketId)->first()->pulsa;

        if($TotalBayar > $pulsa){
            return Response::json(array(
                'status' => 'Error',
                'message' => 'Pulsa anda tidak mencukupi',
                'error_code' => '-'
            ),200);
        }else{
            return PdamBjmAPIv2::requestPayment($PaymentData, true, $session_id, $username, $imei);
        }
    }

    public function getUserLoketInfo($username = "", $sessionid = "", $imei = ""){
        try{

            $dtUser = User::where("username","=",$username)->first();
            if(!$dtUser){
                return Response::json(array(
                    'status' => 'Error',
                    'message' => 'Invalid User'
                ),200);    
            }

            $LoketID = $dtUser->loket_id;
            $dataLoket = mLoket::where('id',$LoketID)->first();
            if(!$dataLoket){
                return Response::json(array(
                    'status' => 'Error',
                    'message' => 'Invalid Loket'
                ),200);    
            }

			$arrayUser = $dtUser->toArray();

            $arrayLoket['kode_loket'] = $dataLoket->loket_code;
            $arrayLoket['kode_loket'] = $dataLoket->loket_code;
            $arrayLoket['nama_loket'] = $dataLoket->nama;
            $arrayLoket['jenis_loket'] = $dataLoket->jenis;
            $arrayLoket['pulsa'] = $dataLoket->pulsa;

            $arrayData = array_merge($arrayUser, $arrayLoket);
			
            return Response::json(array(
                'status' => 'Success',
                'message' => '-',
                'data' => $arrayData
            ),200);   

        }catch(\Exception $ex){
            return Response::json(array(
                'status' => 'Error',
                'message' => 'Terjadi Kesalahan Sistem'
            ),200);
        }
    }

    private function getCetakanPdam($arrLap){
        $cetakan = "";

        $dtRek = mPdambjmTrans::where("id",$arrLap['id'])->first();

        $dtRek = $dtRek->toArray(); 

        $unique_id = $dtRek['transaction_code'] . "-CU";
        $tgl_server = $dtRek['transaction_date'];
        $nopel = $dtRek['cust_id'];
        $nama = $dtRek['nama'];
        $alamat = $dtRek['alamat'];
        $thbl = $dtRek['blth'];
        $harga = number_format($dtRek['harga_air']);
        $byadm = number_format($dtRek['abodemen']);
        $materai = number_format($dtRek['materai']);
        $limbah = number_format($dtRek['limbah']);
        $retribusi = number_format($dtRek['retribusi']);
        $denda = number_format($dtRek['denda']);
        $stand_l = $dtRek['stand_lalu'];
        $stand_i = $dtRek['stand_kini'];
        $sub_tot = number_format($dtRek['sub_total']);
        $admin_kop = number_format($dtRek['admin']);
        $total = number_format($dtRek['total']);
        $userid = $dtRek['username'];
        $loket_code = $dtRek['loket_code'];
        $beban_tetap = number_format($dtRek['beban_tetap']);
        $biaya_meter = number_format($dtRek['biaya_meter']);
        $gol = $dtRek['idgol'];
        $pakai = $dtRek['stand_kini']-$dtRek['stand_lalu'];

        $cetakan .= "PPOB - PEDAMI PAYMENT\nPDAM BANDARMASIH\n=======================\nIDPEL : ".$nopel." \nNama : ".$nama."\nAlamat : ".$alamat."\nGol : ".$gol."\nBlth : ".$thbl."\nStand : ".$stand_l." - ".$stand_i."\nPakai : ".$pakai."\nHarga air : ".$harga."\nLimbah : ".$limbah."\nMaterai :".$materai."\nRetribusi : ".$retribusi."\nAbodemen : ".$byadm."\nBeban Tetap : ".$beban_tetap."\nBiaya P.Meter : ".$biaya_meter."\nDenda : ".$denda."\nSub Total : ".$sub_tot."\nAdmin : ".$admin_kop."\nTotal : ".$total."\n=======================\n".$unique_id."/".$userid."/".$loket_code."/".$tgl_server."\n";
        $cetakan .= "\n";

        return $cetakan;
    }

    private function getCetakanPlnPost($arrLap){
        $cetakan = "";

        $Trans = PlnTransaksi::where("id",$arrLap['id'])->first();

        $subcriber_id = $Trans->subcriber_id;
        $subcriber_name = $Trans->subcriber_name;
        $subcriber_segment = $Trans->subcriber_segment;
        $power_consumtion = $Trans->power_consumtion;
        $bill_periode = $Trans->bill_periode;
        $bill_status = $Trans->bill_status;
        $outstanding_bill = $Trans->outstanding_bill;
        $total_elec_bill = $Trans->total_elec_bill;
        $added_tax = $Trans->added_tax;
        $penalty_fee = $Trans->penalty_fee;
        $admin_charge = $Trans->admin_charge;
        $switcher_ref = $Trans->switcher_ref;
        $transaction_code = $Trans->transaction_code;
        $transaction_date = $Trans->transaction_date;

        $total_pln = (int)$total_elec_bill+(int)$added_tax+(int)$penalty_fee;
        $total_transaksi = (int)$total_elec_bill+(int)$added_tax+(int)$penalty_fee+(int)$admin_charge;

        $prev_meter_read_1 = $Trans->prev_meter_read_1;
        $curr_meter_read_1 = $Trans->curr_meter_read_1;
        $sisa_bill =  (int)$outstanding_bill-(int)$bill_status;

        $cetakan .= "PPOB - KOPKAR PEDAMI\n";
        $cetakan .= "STRUK PEMBAYARAN TAGIHAN LISTRIK\n\n";

        $cetakan .= "IDPEL : \n".$subcriber_id."\n";
        $cetakan .= "NAMA : \n".$subcriber_name."\n";
        $cetakan .= "TARIF / DAYA : \n".$subcriber_segment." / ".$power_consumtion."VA \n";
        $cetakan .= "BL / TH : \n".$bill_periode."\n";
        $cetakan .= "STAND : \n".$prev_meter_read_1." - ".$curr_meter_read_1."\n";
        $cetakan .= "RP. TAG PLN : \nRp. ".number_format($total_pln)."\n";
        $cetakan .= "ADMIN BANK : \nRp. ".number_format($admin_charge)."\n";
        $cetakan .= "TOTAL BAYAR : \nRp. ".number_format($total_transaksi)."\n";
        $cetakan .= "NO.REF : \n".$switcher_ref."\n";
        if($sisa_bill > 0){
            $cetakan .= "ANDA MASIH MEMILIKI TUNGGAKAN ".$sisa_bill." BULAN\n";
        }else{
            $cetakan .= "TERIMA KASIH\n";
        }
        $cetakan .= "CU-".$transaction_code."\n";
        $cetakan .= $transaction_date."\n\n";

        return $cetakan;
    }

    private function getCetakanPlnPre($arrLap){

        $cetakan = "";

        $Trans = PlnPrepaidTransaksi::where("id","=",$arrLap['id'])->first();

        $nomorMeter = $Trans->material_number;
        $subscriber_id = $Trans->subscriber_id;
        $subscriber_name = $Trans->subscriber_name;
        $subscriber_segment = $Trans->subscriber_segment;
        $power_categori = (int)$Trans->power_categori;
        $purchase_kwh = $Trans->purchase_kwh;
        $materai = $Trans->stump_duty;
        $ppn = $Trans->addtax;
        $ppj = $Trans->ligthingtax;
        $angsuran = $Trans->cust_payable;
        $rp_token = $Trans->power_purchase;
        $admin_bank = $Trans->admin_charge;

        $rp_bayar = $materai + $ppn + $ppj + $angsuran + $admin_bank + $rp_token;
        $no_ref = $Trans->switcher_ref_number;

        $token = $Trans->token_number;

        $info = $Trans->info_text;
        $transaction_code = $Trans->transaction_code;
        $transaction_date = $Trans->transaction_date;

        $cetakan .= "PPOB - KOPKAR PEDAMI\n";
        $cetakan .= "STRUK PEMBELIAN LISTRIK PRABAYAR\n\n";

        $cetakan .= "NO METER : \n".$nomorMeter."\n";
        $cetakan .= "IDPEL : \n".$subscriber_id."\n";
        $cetakan .= "NAMA : \n".$subscriber_name."\n";
        $cetakan .= "TARIF / DAYA : \n".$subscriber_segment." / ".$power_categori."VA \n";
        $cetakan .= "MATERAI : \nRp. ".number_format($materai)."\n";
        $cetakan .= "PPN : \nRp. ".number_format($ppn)."\n";
        $cetakan .= "PPJ : \nRp. ".number_format($ppj)."\n";
        $cetakan .= "ANGSURAN : \nRp. ".number_format($angsuran)."\n";
        $cetakan .= "RP. STROOM / TOKEN : \nRp. ".number_format($rp_token)."\n";
        $cetakan .= "ADMIN BANK : \nRp. ".number_format($admin_bank)."\n";
        $cetakan .= "RP. BAYAR : \nRp. ".number_format($rp_bayar)."\n";
        $cetakan .= "NO. REF : \n".$no_ref."\n";
        $cetakan .= "STROOM / TOKEN : \n".$token."\n";
        $cetakan .= $info."\n";
        $cetakan .= "CU-".$transaction_code."\n";
        $cetakan .= $transaction_date."\n";

        $cetakan .= "\n";

        return $cetakan;
    }

    private function getCetakanPlnNon($arrLap){

        $cetakan = "";

        $Trans = PlnNontaglisTransaksi::where("id","=",$arrLap['id'])->first();

        $transaction_name = $Trans->transaction_name;
        $register_number = $Trans->register_number;
        $registration_date = $Trans->registration_date;
        $subscriber_name = $Trans->subscriber_name;
        $subscriber_id = $Trans->subscriber_id;
        $pln_bill_value = $Trans->pln_bill_value;
        $admin_charge = $Trans->admin_charge;
        $total_bayar = $pln_bill_value + $admin_charge;

        $switcher_ref = $Trans->switcher_ref_number;
        $transaction_code = $Trans->transaction_code;
        $transaction_date = $Trans->transaction_date;

        $cetakan .= "PPOB - KOPKAR PEDAMI\n";
        $cetakan .= "STRUK NON TAGIHAN LISTRIK\n\n";

        $cetakan .= "TRANSAKSI : \n".$transaction_name."\n";
        $cetakan .= "NO REGISTRASI : \n".$register_number."\n";
        $cetakan .= "TGL REGISTRASI : \n".$registration_date."\n";
        $cetakan .= "NAMA : \n".$subscriber_name."\n";
        $cetakan .= "IDPEL : \n".$subscriber_id."\n";
        $cetakan .= "BIAYA PLN : \nRp. ".number_format($pln_bill_value)."\n";
        $cetakan .= "ADMIN BANK : \nRp. ".number_format($admin_charge)."\n";
        $cetakan .= "TOTAL BAYAR : \nRp. ".number_format($total_bayar)."\n";
        $cetakan .= "NO.REF : \n".$switcher_ref."\n";
        $cetakan .= "CU-".$transaction_code."\n";
        $cetakan .= $transaction_date."\n";

        $cetakan .= "\n";

        return $cetakan;
    }

    public function cetakIdpel($idpel, $tgl_awal, $tgl_akhir, $jenisTransaksi, $session_id, $username, $imei){
        try{

            $cetakan = "";

            switch ($jenisTransaksi) {
                case 'PDAM_BANDARMASIH':
                    $cetak = vwPdambjm::whereBetween(DB::raw('cast(tanggal as date)'),[$tgl_awal, $tgl_akhir])
                        ->where('user_',$username)
                        ->where('jenis_transaksi',$jenisTransaksi)
                        ->where('idpel',$idpel)->get();

                    $arrLap = $cetak->toArray();
                    
                    for($i=0;$i<sizeof($arrLap);$i++){
                        $cetakan .= $this->getCetakanPdam($arrLap[$i]);
                    }
                    break;
                case 'PLN_POSTPAID':
                    $cetak = vwTransaksiPln::whereBetween(DB::raw('cast(tanggal as date)'),[$tgl_awal, $tgl_akhir])
                        ->where('user_',$username)
                        ->where('jenis_transaksi',$jenisTransaksi)
                        ->where('idpel',$idpel)->get();

                    $arrLap = $cetak->toArray();
                    for($i=0;$i<sizeof($arrLap);$i++){
                        $cetakan .= $this->getCetakanPlnPost($arrLap[$i]);
                    }

                    break;
                case 'PLN_PREPAID':
                    $cetak = vwTransaksiPlnPrepaid::whereBetween(DB::raw('cast(tanggal as date)'),[$tgl_awal, $tgl_akhir])
                        ->where('user_',$username)
                        ->where('jenis_transaksi',$jenisTransaksi)
                        ->where('idpel',$idpel)->get();

                    $arrLap = $cetak->toArray();
                    for($i=0;$i<sizeof($arrLap);$i++){
                        $cetakan .= $this->getCetakanPlnPre($arrLap[$i]);
                    }

                    break;
                case 'PLN_NONTAGLIS':
                    $cetak = vwTransaksiPlnNontag::whereBetween(DB::raw('cast(tanggal as date)'),[$tgl_awal, $tgl_akhir])
                        ->where('user_',$username)
                        ->where('jenis_transaksi',$jenisTransaksi)
                        ->where('idpel',$idpel)->get();

                    $arrLap = $cetak->toArray();
                    for($i=0;$i<sizeof($arrLap);$i++){
                        $cetakan .= $this->getCetakanPlnNon($arrLap[$i]);
                    }

                    break;
            }

            if(strlen($cetakan) > 0){
                return Response::json(array(
                    'status' => 'Success',
                    'message' => '-',
                    'idpel' => $idpel,
                    'produk' => $jenisTransaksi,
                    'cetakan' => $cetakan
                ),200);
            }else{
                return Response::json(array(
                    'status' => 'Error',
                    'message' => 'Data tidak ditemukan'
                ),200);
            }

        }catch (Exception $e){

            $error = explode("\r\n",$e->getMessage());
            return Response::json(array(
                'status' => 'Error',
                'message' => 'Terjadi Kesalahan Sistem'
            ),200);
        }
    }

    public function getDetailLaporan($tanggal, $kode_loket, $jenisTransaksi, $session_id, $username, $imei, $userloket){
        try{

            // $Rekap = vwDetailTransaksi::where('tanggal',$tanggal)
            //     ->where('loket_code',$kode_loket)
            //     ->where('user_',$userloket)
            //     ->where('jenis_transaksi',$jenisTransaksi)
            //     ->orderBy('idpel','asc')
            //     ->get();

            $Pdambjm = vwPdambjm::where('tanggal',$tanggal)
                ->where('loket_code',$kode_loket)
                ->where('user_',$userloket)
                ->where('jenis_transaksi',$jenisTransaksi);
            $Pln = vwTransaksiPln::where('tanggal',$tanggal)
                ->where('loket_code',$kode_loket)
                ->where('user_',$userloket)
                ->where('jenis_transaksi',$jenisTransaksi);
            $PlnPrepaid = vwTransaksiPlnPrepaid::where('tanggal',$tanggal)
                ->where('loket_code',$kode_loket)
                ->where('user_',$userloket)
                ->where('jenis_transaksi',$jenisTransaksi);
            $PlnNon = vwTransaksiPlnNontag::where('tanggal',$tanggal)
                ->where('loket_code',$kode_loket)
                ->where('user_',$userloket)
                ->where('jenis_transaksi',$jenisTransaksi);

            $Rekap = $Pdambjm->union($Pln)->union($PlnPrepaid)->union($PlnNon)->get();

            $numRekap = $Rekap->count();
            if($numRekap > 0){
                $arrLap = $Rekap->toArray();

                for($i=0;$i<sizeof($arrLap);$i++){

                    switch ($arrLap[$i]['jenis_transaksi']) {
                        case 'PDAM_BANDARMASIH':

                            $cetakan = "";

                            // $dtRek = mPdambjmTrans::where("cust_id",$arrLap[$i]['idpel'])
                            //     ->where("blth",$arrLap[$i]['periode'])
                            //     ->whereRaw("CAST(transaction_date AS DATE) = '".$arrLap[$i]['tanggal']."'")->first();

                            $dtRek = mPdambjmTrans::where("id",$arrLap[$i]['id'])->first();

                            $dtRek = $dtRek->toArray(); 

                            $unique_id = $dtRek['transaction_code'] . "-CU";
                            $tgl_server = $dtRek['transaction_date'];
                            $nopel = $dtRek['cust_id'];
                            $nama = $dtRek['nama'];
                            $alamat = $dtRek['alamat'];
                            $thbl = $dtRek['blth'];
                            $harga = number_format($dtRek['harga_air']);
                            $byadm = number_format($dtRek['abodemen']);
                            $materai = number_format($dtRek['materai']);
                            $limbah = number_format($dtRek['limbah']);
                            $retribusi = number_format($dtRek['retribusi']);
                            $denda = number_format($dtRek['denda']);
                            $stand_l = $dtRek['stand_lalu'];
                            $stand_i = $dtRek['stand_kini'];
                            $sub_tot = number_format($dtRek['sub_total']);
                            $admin_kop = number_format($dtRek['admin']);
                            $total = number_format($dtRek['total']);
                            $userid = $dtRek['username'];
                            $loket_code = $dtRek['loket_code'];
                            $beban_tetap = number_format($dtRek['beban_tetap']);
                            $biaya_meter = number_format($dtRek['biaya_meter']);
                            $gol = $dtRek['idgol'];
                            $pakai = $dtRek['stand_kini']-$dtRek['stand_lalu'];

                            $cetakan = "PPOB - PEDAMI PAYMENT\nPDAM BANDARMASIH\n=======================\nIDPEL : ".$nopel." \nNama : ".$nama."\nAlamat : ".$alamat."\nGol : ".$gol."\nBlth : ".$thbl."\nStand : ".$stand_l." - ".$stand_i."\nPakai : ".$pakai."\nHarga air : ".$harga."\nLimbah : ".$limbah."\nMaterai :".$materai."\nRetribusi : ".$retribusi."\nAbodemen : ".$byadm."\nBeban Tetap : ".$beban_tetap."\nBiaya P.Meter : ".$biaya_meter."\nDenda : ".$denda."\nSub Total : ".$sub_tot."\nAdmin : ".$admin_kop."\nTotal : ".$total."\n=======================\n".$unique_id."/".$userid."/".$loket_code."/".$tgl_server."\n";

                            $arrLap[$i]['cetakFormat'] = $cetakan;

                            break;

                        case 'PLN_POSTPAID':

                            $cetakan = "";

                            // $Trans = PlnTransaksi::where("subcriber_id",$arrLap[$i]['idpel'])
                            //     ->where("bill_periode",$arrLap[$i]['periode'])
                            //     ->whereRaw("CAST(transaction_date AS DATE) = '".$arrLap[$i]['tanggal']."'")->first();

                            $Trans = PlnTransaksi::where("id",$arrLap[$i]['id'])->first();

                            $subcriber_id = $Trans->subcriber_id;
                            $subcriber_name = $Trans->subcriber_name;
                            $subcriber_segment = $Trans->subcriber_segment;
                            $power_consumtion = $Trans->power_consumtion;
                            $bill_periode = $Trans->bill_periode;
                            $bill_status = $Trans->bill_status;
                            $outstanding_bill = $Trans->outstanding_bill;
                            $total_elec_bill = $Trans->total_elec_bill;
                            $added_tax = $Trans->added_tax;
                            $penalty_fee = $Trans->penalty_fee;
                            $admin_charge = $Trans->admin_charge;
                            $switcher_ref = $Trans->switcher_ref;
                            $transaction_code = $Trans->transaction_code;
                            $transaction_date = $Trans->transaction_date;

                            $total_pln = (int)$total_elec_bill+(int)$added_tax+(int)$penalty_fee;
                            $total_transaksi = (int)$total_elec_bill+(int)$added_tax+(int)$penalty_fee+(int)$admin_charge;

                            $prev_meter_read_1 = $Trans->prev_meter_read_1;
                            $curr_meter_read_1 = $Trans->curr_meter_read_1;
                            $sisa_bill =  (int)$outstanding_bill-(int)$bill_status;

                            $cetakan .= "PPOB - KOPKAR PEDAMI\n";
                            $cetakan .= "STRUK PEMBAYARAN TAGIHAN LISTRIK\n\n";

                            $cetakan .= "IDPEL : \n".$subcriber_id."\n";
                            $cetakan .= "NAMA : \n".$subcriber_name."\n";
                            $cetakan .= "TARIF / DAYA : \n".$subcriber_segment." / ".$power_consumtion."VA \n";
                            $cetakan .= "BL / TH : \n".$bill_periode."\n";
                            $cetakan .= "STAND : \n".$prev_meter_read_1." - ".$curr_meter_read_1."\n";
                            $cetakan .= "RP. TAG PLN : \nRp. ".number_format($total_pln)."\n";
                            $cetakan .= "ADMIN BANK : \nRp. ".number_format($admin_charge)."\n";
                            $cetakan .= "TOTAL BAYAR : \nRp. ".number_format($total_transaksi)."\n";
                            $cetakan .= "NO.REF : \n".$switcher_ref."\n";
                            if($sisa_bill > 0){
                                $cetakan .= "ANDA MASIH MEMILIKI TUNGGAKAN ".$sisa_bill." BULAN\n";
                            }else{
                                $cetakan .= "TERIMA KASIH\n";
                            }
                            $cetakan .= "CU-".$transaction_code."\n";
                            $cetakan .= $transaction_date."\n\n";

                            $arrLap[$i]['cetakFormat'] = $cetakan;

                            break;

                        case 'PLN_PREPAID':

                            $cetakan = "";

                            // $Trans = PlnPrepaidTransaksi::where("subscriber_id","=",$arrLap[$i]['idpel'])
                            //     ->whereRaw("CAST(transaction_date AS DATE) = '".$arrLap[$i]['tanggal']."'")->first();

                            $Trans = PlnPrepaidTransaksi::where("id","=",$arrLap[$i]['id'])->first();

                            $nomorMeter = $Trans->material_number;
                            $subscriber_id = $Trans->subscriber_id;
                            $subscriber_name = $Trans->subscriber_name;
                            $subscriber_segment = $Trans->subscriber_segment;
                            $power_categori = (int)$Trans->power_categori;
                            $purchase_kwh = $Trans->purchase_kwh;
                            $materai = $Trans->stump_duty;
                            $ppn = $Trans->addtax;
                            $ppj = $Trans->ligthingtax;
                            $angsuran = $Trans->cust_payable;
                            $rp_token = $Trans->power_purchase;
                            $admin_bank = $Trans->admin_charge;

                            $rp_bayar = $materai + $ppn + $ppj + $angsuran + $admin_bank + $rp_token;
                            $no_ref = $Trans->switcher_ref_number;

                            // $token_number = str_split($Trans->token_number,4);
                            // $space_token = "";
                            // foreach ($token_number as $token) {
                            //     $space_token .= $token." ";
                            // }

                            $token = $Trans->token_number;

                            $info = $Trans->info_text;
                            $transaction_code = $Trans->transaction_code;
                            $transaction_date = $Trans->transaction_date;

                            $cetakan .= "PPOB - KOPKAR PEDAMI\n";
                            $cetakan .= "STRUK PEMBELIAN LISTRIK PRABAYAR\n\n";

                            $cetakan .= "NO METER : \n".$nomorMeter."\n";
                            $cetakan .= "IDPEL : \n".$subscriber_id."\n";
                            $cetakan .= "NAMA : \n".$subscriber_name."\n";
                            $cetakan .= "TARIF / DAYA : \n".$subscriber_segment." / ".$power_categori."VA \n";
                            $cetakan .= "MATERAI : \nRp. ".number_format($materai)."\n";
                            $cetakan .= "PPN : \nRp. ".number_format($ppn)."\n";
                            $cetakan .= "PPJ : \nRp. ".number_format($ppj)."\n";
                            $cetakan .= "ANGSURAN : \nRp. ".number_format($angsuran)."\n";
                            $cetakan .= "RP. STROOM / TOKEN : \nRp. ".number_format($rp_token)."\n";
                            $cetakan .= "ADMIN BANK : \nRp. ".number_format($admin_bank)."\n";
                            $cetakan .= "RP. BAYAR : \nRp. ".number_format($rp_bayar)."\n";
                            $cetakan .= "NO. REF : \n".$no_ref."\n";
                            $cetakan .= "STROOM / TOKEN : \n".$token."\n";
                            $cetakan .= $info."\n";
                            $cetakan .= "CU-".$transaction_code."\n";
                            $cetakan .= $transaction_date."\n";

                            $arrLap[$i]['cetakFormat'] = $cetakan;

                            break;

                        case 'PLN_NONTAGLIS':

                            $cetakan = "";

                            // $Trans = PlnNontaglisTransaksi::where("register_number","=",$arrLap[$i]['idpel'])
                            //     ->whereRaw("CAST(transaction_date AS DATE) = '".$arrLap[$i]['tanggal']."'")->first();

                            $Trans = PlnNontaglisTransaksi::where("id","=",$arrLap[$i]['id'])->first();

                            $transaction_name = $Trans->transaction_name;
                            $register_number = $Trans->register_number;
                            $registration_date = $Trans->registration_date;
                            $subscriber_name = $Trans->subscriber_name;
                            $subscriber_id = $Trans->subscriber_id;
                            $pln_bill_value = $Trans->pln_bill_value;
                            $admin_charge = $Trans->admin_charge;
                            $total_bayar = $pln_bill_value + $admin_charge;

                            $switcher_ref = $Trans->switcher_ref_number;
                            $transaction_code = $Trans->transaction_code;
                            $transaction_date = $Trans->transaction_date;

                            $cetakan .= "PPOB - KOPKAR PEDAMI\n";
                            $cetakan .= "STRUK NON TAGIHAN LISTRIK\n\n";

                            $cetakan .= "TRANSAKSI : \n".$transaction_name."\n";
                            $cetakan .= "NO REGISTRASI : \n".$register_number."\n";
                            $cetakan .= "TGL REGISTRASI : \n".$registration_date."\n";
                            $cetakan .= "NAMA : \n".$subscriber_name."\n";
                            $cetakan .= "IDPEL : \n".$subscriber_id."\n";
                            $cetakan .= "BIAYA PLN : \nRp. ".number_format($pln_bill_value)."\n";
                            $cetakan .= "ADMIN BANK : \nRp. ".number_format($admin_charge)."\n";
                            $cetakan .= "TOTAL BAYAR : \nRp. ".number_format($total_bayar)."\n";
                            $cetakan .= "NO.REF : \n".$switcher_ref."\n";
                            $cetakan .= "CU-".$transaction_code."\n";
                            $cetakan .= $transaction_date."\n";

                            $arrLap[$i]['cetakFormat'] = $cetakan;
                            break;
                        
                        default:
                            $arrLap[$i]['cetakFormat'] = "CETAK ULANG " . $arrLap[$i]['idpel'];
                            break;
                    }
                }

                return Response::json(array(
                    'status' => 'Success',
                    'message' => '-',
                    'data' => $arrLap
                ),200);

            }else{
                return Response::json(array(
                    'status' => 'Error',
                    'message' => 'Tidak ada transaksi di tanggal : '.$tanggal
                ),200);
            }
        }catch (Exception $e){

            $error = explode("\r\n",$e->getMessage());
            return Response::json(array(
                'status' => 'Error',
                'message' => 'Terjadi Kesalahan Sistem'
            ),200);
        }
    }

    public function getLaporanHarian($tgl_awal, $tgl_akhir, $kode_loket, $session_id, $username, $imei = '12345', $pil = '1'){

        try{
            $pilKasir = "";
            switch ($pil) {
                case '1':
                    $pilKasir = array("SEMUA");
                    break;
                case '2':
                    $pilKasir = array("KASIR","ADMIN","NON_ADMIN");
                    break;
                case '3':
                    $pilKasir = array("ANDROID","PM");
                    break;
                case '4':
                    $pilKasir = array("SWITCHING");
                    break;
            }

            //$Rekap = vwRekapTransaksi::whereBetween('tanggal',[$tgl_awal, $tgl_akhir]);

            $Pdambjm = vwPdambjm::whereBetween('tanggal',[$tgl_awal, $tgl_akhir]);
            $Pln = vwTransaksiPln::whereBetween('tanggal',[$tgl_awal, $tgl_akhir]);
            $PlnPrepaid = vwTransaksiPlnPrepaid::whereBetween('tanggal',[$tgl_awal, $tgl_akhir]);
            $PlnNon = vwTransaksiPlnNontag::whereBetween('tanggal',[$tgl_awal, $tgl_akhir]);

            if($pilKasir[0] != "SEMUA"){
                //$Rekap = $Rekap->whereIn('jenis_loket',$pilKasir);

                $Pdambjm = $Pdambjm->whereIn('jenis_loket',$pilKasir);
                $Pln = $Pln->whereIn('jenis_loket',$pilKasir);
                $PlnPrepaid = $PlnPrepaid->whereIn('jenis_loket',$pilKasir);
                $PlnNon = $PlnNon->whereIn('jenis_loket',$pilKasir);
            }

            $Role = User::where("username","=",$username)->first()->role;

            if($Role != "admin"){
                //$Rekap = $Rekap->where('loket_code',$kode_loket)->where('user_',$username);

                $Pdambjm = $Pdambjm->where('loket_code',$kode_loket)->where('user_',$username);
                $Pln = $Pln->where('loket_code',$kode_loket)->where('user_',$username);
                $PlnPrepaid = $PlnPrepaid->where('loket_code',$kode_loket)->where('user_',$username);
                $PlnNon = $PlnNon->where('loket_code',$kode_loket)->where('user_',$username);
            }

            $Pdambjm = $Pdambjm->select('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi',
                    DB::raw('sum(tagihan) as tagihan'),
                    DB::raw('sum(admin) as admin'),
                    DB::raw('sum(total) as total'),
                    DB::raw('count(*) as jumlah'))
                    ->groupBy('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi');
            $Pln = $Pln->select('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi',
                    DB::raw('sum(tagihan) as tagihan'),
                    DB::raw('sum(admin) as admin'),
                    DB::raw('sum(total) as total'),
                    DB::raw('count(*) as jumlah'))
                    ->groupBy('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi');
            $PlnPrepaid = $PlnPrepaid->select('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi',
                    DB::raw('sum(tagihan) as tagihan'),
                    DB::raw('sum(admin) as admin'),
                    DB::raw('sum(total) as total'),
                    DB::raw('count(*) as jumlah'))
                    ->groupBy('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi');
            $PlnNon = $PlnNon->select('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi',
                    DB::raw('sum(tagihan) as tagihan'),
                    DB::raw('sum(admin) as admin'),
                    DB::raw('sum(total) as total'),
                    DB::raw('count(*) as jumlah'))
                    ->groupBy('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi');

            $Rekap = $Pdambjm->union($Pln)->union($PlnPrepaid)->union($PlnNon)->orderBy('tanggal','asc')->get();

            $numRekap = $Rekap->count();
            if($numRekap > 0){
                $arrLap = $Rekap->toArray();

                return Response::json(array(
                    'status' => 'Success',
                    'message' => '-',
                    'data' => $arrLap
                ),200);

            }else{
                return Response::json(array(
                    'status' => 'Error',
                    'message' => 'Tidak ada transaksi di tanggal : '.$tgl_awal . " s/d ". $tgl_akhir
                ),200);
            }
        }catch (\Exception $e){

            $error = explode("\r\n",$e->getMessage());
            return Response::json(array(
                'status' => 'Error',
                'message' => 'Terjadi Kesalahan Sistem'
            ),200);
        }
    }
    
    public function getLaporanHarianv1($tgl_awal, $tgl_akhir, $kode_loket, $session_id, $username, $pil = '1'){

        try{
            $pilKasir = "";
            switch ($pil) {
                case '1':
                    $pilKasir = array("SEMUA");
                    break;
                case '2':
                    $pilKasir = array("KASIR","ADMIN","NON_ADMIN");
                    break;
                case '3':
                    $pilKasir = array("ANDROID","PM");
                    break;
                case '4':
                    $pilKasir = array("SWITCHING");
                    break;
            }

            //$Rekap = vwRekapTransaksi::whereBetween('tanggal',[$tgl_awal, $tgl_akhir]);

            $Pdambjm = vwPdambjm::whereBetween('tanggal',[$tgl_awal, $tgl_akhir]);
            $Pln = vwTransaksiPln::whereBetween('tanggal',[$tgl_awal, $tgl_akhir]);
            $PlnPrepaid = vwTransaksiPlnPrepaid::whereBetween('tanggal',[$tgl_awal, $tgl_akhir]);
            $PlnNon = vwTransaksiPlnNontag::whereBetween('tanggal',[$tgl_awal, $tgl_akhir]);

            if($pilKasir[0] != "SEMUA"){
                //$Rekap = $Rekap->whereIn('jenis_loket',$pilKasir);

                $Pdambjm = $Pdambjm->whereIn('jenis_loket',$pilKasir);
                $Pln = $Pln->whereIn('jenis_loket',$pilKasir);
                $PlnPrepaid = $PlnPrepaid->whereIn('jenis_loket',$pilKasir);
                $PlnNon = $PlnNon->whereIn('jenis_loket',$pilKasir);
            }

            $Role = User::where("username","=",$username)->first()->role;

            if($Role != "admin"){
                //$Rekap = $Rekap->where('loket_code',$kode_loket)->where('user_',$username);

                $Pdambjm = $Pdambjm->where('loket_code',$kode_loket)->where('user_',$username);
                $Pln = $Pln->where('loket_code',$kode_loket)->where('user_',$username);
                $PlnPrepaid = $PlnPrepaid->where('loket_code',$kode_loket)->where('user_',$username);
                $PlnNon = $PlnNon->where('loket_code',$kode_loket)->where('user_',$username);
            }

            $Pdambjm = $Pdambjm->select('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi',
                    DB::raw('sum(tagihan) as tagihan'),
                    DB::raw('sum(admin) as admin'),
                    DB::raw('sum(total) as total'),
                    DB::raw('count(*) as jumlah'))
                    ->groupBy('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi');
            $Pln = $Pln->select('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi',
                    DB::raw('sum(tagihan) as tagihan'),
                    DB::raw('sum(admin) as admin'),
                    DB::raw('sum(total) as total'),
                    DB::raw('count(*) as jumlah'))
                    ->groupBy('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi');
            $PlnPrepaid = $PlnPrepaid->select('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi',
                    DB::raw('sum(tagihan) as tagihan'),
                    DB::raw('sum(admin) as admin'),
                    DB::raw('sum(total) as total'),
                    DB::raw('count(*) as jumlah'))
                    ->groupBy('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi');
            $PlnNon = $PlnNon->select('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi',
                    DB::raw('sum(tagihan) as tagihan'),
                    DB::raw('sum(admin) as admin'),
                    DB::raw('sum(total) as total'),
                    DB::raw('count(*) as jumlah'))
                    ->groupBy('tanggal','user_','loket_code','loket_name','jenis_loket','jenis_transaksi');

            $Rekap = $Pdambjm->union($Pln)->union($PlnPrepaid)->union($PlnNon)->orderBy('tanggal','asc')->get();

            $numRekap = $Rekap->count();
            if($numRekap > 0){
                $arrLap = $Rekap->toArray();

                return Response::json(array(
                    'status' => 'Success',
                    'message' => '-',
                    'data' => $arrLap
                ),200);

            }else{
                return Response::json(array(
                    'status' => 'Error',
                    'message' => 'Tidak ada transaksi di tanggal : '.$tgl_awal . " s/d ". $tgl_akhir
                ),200);
            }
        }catch (\Exception $e){

            $error = explode("\r\n",$e->getMessage());
            return Response::json(array(
                'status' => 'Error',
                'message' => 'Terjadi Kesalahan Sistem'
            ),200);
        }
    }

    public function getBerita(){
        try{
            $mBerita = mBerita::select('judul','isi','created_at','user')->orderBy('created_at','desc')->get();

            return Response::json(array(
                'status' => 'Success',
                'message' => '-',
                'data' => $mBerita
            ),200);
        }catch (\Exception $e){

            $error = explode("\r\n",$e->getMessage());
            return Response::json(array(
                'status' => 'Error',
                'message' => 'Terjadi Kesalahan Sistem'
            ),200);
        }
    }
}