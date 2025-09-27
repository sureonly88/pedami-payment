<?php namespace App\Http\Controllers;

use App;
use App\APIServices\PdamBjmAPIv2;
use Illuminate\Support\Facades\Input;

class PdambjmPaymentv2 extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function cetakUlang($idlgn, $tgl_transaksi, $blnrek){
        //$APIPdamBjm = App::make('App\APIServices\PdamBjmAPIv2');

        return PdamBjmAPIv2::requestCetakUlang($idlgn, $tgl_transaksi, $blnrek);
    }

    public function doPayment(){
        $PaymentData = Input::get('PaymentData');
        $isPrinterBaru = Input::get('isPrinterBaru');

        //dd($isPrinterBaru);

        if($isPrinterBaru && $isPrinterBaru > 0){
            $jenisKertas = Input::get('jenisKertas');
            return PdamBjmAPIv2::requestPaymentBaru($PaymentData, false,"","","", $isPrinterBaru, $jenisKertas);
        }else{
            return PdamBjmAPIv2::requestPayment($PaymentData, false,"","","");
        }
        
    }

    public function cetakUlangBaru($idlgn, $tgl_awal,$tgl_akhir, $blnrek, $isPrinterBaru, $jenisKertas){

        return PdamBjmAPIv2::requestCetakUlangBaru($idlgn, $tgl_awal, $tgl_akhir, $blnrek, $isPrinterBaru, $jenisKertas);
            
    }

}