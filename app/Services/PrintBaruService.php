<?php

namespace App\Services;

use App\Models\SettingRekPdam;
use App\Models\PrintModel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PrintBaruService
{
	public function handlePrintBaru($arrRek,$jenisKertas,$Layanan){
        switch ($Layanan) {
            case 'PDAMBJM':
                return $this->handlePdamCetak($arrRek,$jenisKertas,$Layanan);
                break;
            default:
                return null;
        }
    }

    private function handlePdamCetak($arrRek,$jenisKertas,$Layanan){
    	$username = Auth::user()->username;

        $Pemisah = 0;
        switch ($jenisKertas) {
            case 'A4-3':
                $Pemisah = 3;
                break;
            case 'A4-4':
                $Pemisah = 4;
                break;
        }

        $arrayDivide = array_chunk($arrRek,$Pemisah);

        $arrayPrint = array();

        for($i=0;$i<sizeof($arrayDivide); $i++){

            $cetakRek = "";
            $jmlRek = 0;

            $spasi = "";
            switch ($jenisKertas) {
                case 'A4-3':
                    $spasi = "\n\n\n\n";
                    break;
                case 'A4-4':
                    $spasi = "\n\n\n\n";
                    break;
            }

            $jmlData = sizeof($arrayDivide[$i])-1;

            for($j=0;$j<sizeof($arrayDivide[$i]); $j++){
                if($j != $jmlData){
                    $cetakRek .= $this->kertasKasir($arrayDivide[$i][$j],$jenisKertas) . "\n\n" . $spasi;
                }else{
                    $cetakRek .= $this->kertasKasir($arrayDivide[$i][$j],$jenisKertas);
                }
                
                $jmlRek ++;
            }

            $print['print_data'] = $cetakRek;
            $print['jenis_kertas'] = $jenisKertas;
            $print['jml_rek'] = $jmlRek;

            array_push($arrayPrint, $print);
            // $printQueue = new PrintModel();
            // $printQueue->username = $username;
            // $printQueue->jenis_layanan = $Layanan;
            // $printQueue->print_data = $cetakRek;
            // $printQueue->is_printed = 0;
            // $printQueue->jenis_kertas = $jenisKertas;
            // $printQueue->jml_rek = $jmlRek;
            // $printQueue->save();
            
        }

        return $arrayPrint;
    }

    private function kertasKasir($Rek,$jenisKertas){
        $c = 1;
        $cetak = Storage::get('prints/pdambjm/'.$jenisKertas.'.struk');
        $cetak = str_replace("[IDLGN]000000000000000000000000000000000", str_pad($Rek['cust_id'],40," ",STR_PAD_RIGHT), $cetak);
        $cetak = str_replace("[GOL]00000000000000000000000000000000000", str_pad($Rek['gol'],40," ",STR_PAD_RIGHT), $cetak);
        $cetak = str_replace("[NAMA]0000000000000000000000000000000000", str_pad($Rek['nama'],40," ",STR_PAD_RIGHT), $cetak);

        $alamat1 = substr($Rek['alamat'],0,40);

        $cetak = str_replace("[ALAMAT]00000000000000000000000000000000", str_pad($alamat1,40," ",STR_PAD_RIGHT), $cetak);

        $stand_m3 = $Rek['stand_kini']."-".$Rek['stand_lalu']."/".$Rek['pakai']." m3";
        $cetak = str_replace("[STAND]000000000000000000000000000000000", str_pad($stand_m3,40," ",STR_PAD_RIGHT), $cetak);

        $blth = $this->bulanTahun($Rek['blth']);

        $biayaMeter = number_format($Rek['biaya_meter']);
        $bebanTetap = number_format($Rek['beban_tetap']);
        $hargaAir = number_format($Rek['harga_air']);
        $abodemen = number_format($Rek['abodemen']);
        $materai = number_format($Rek['materai']);

        $cetak = str_replace("[BLTH]0000000000000000000000000000000000", str_pad($blth,40," ",STR_PAD_RIGHT), $cetak);

        $cetak = str_replace("[BIAYA_METER]", str_pad($biayaMeter,20," ",STR_PAD_RIGHT), $cetak);
        $cetak = str_replace("[BEBAN_TETAP]", str_pad($bebanTetap,20," ",STR_PAD_RIGHT), $cetak);
        $cetak = str_replace("[HARGA_AIR]", str_pad($hargaAir,20," ",STR_PAD_RIGHT), $cetak);
        $cetak = str_replace("[ABODEMEN]", str_pad($abodemen,20," ",STR_PAD_RIGHT), $cetak);
        $cetak = str_replace("[MATERAI]", str_pad($materai,20," ",STR_PAD_RIGHT), $cetak);

        $limbah = number_format($Rek['limbah']);
        $retri = number_format($Rek['retribusi']);
        $denda = number_format($Rek['denda']);
        $subtotal = number_format($Rek['sub_total']);
        $admin = number_format($Rek['admin']);

        $cetak = str_replace("[LIMBAH]0000000000000000000000000000", str_pad($limbah,36," ",STR_PAD_RIGHT), $cetak);
        $cetak = str_replace("[RETRIBUSI]0000000000000000000000000", str_pad($retri,36," ",STR_PAD_RIGHT), $cetak);
        $cetak = str_replace("[DENDA]", str_pad($denda,20," ",STR_PAD_RIGHT), $cetak);
        $cetak = str_replace("[SUB_TOTAL]", str_pad($subtotal,20," ",STR_PAD_RIGHT), $cetak);
        $cetak = str_replace("[ADMIN]", str_pad($admin,20," ",STR_PAD_RIGHT), $cetak);

        $total = number_format($Rek['total']);
        $username = Auth::user()->username;

        $kode1 = $Rek['transaction_code'] . "/" . $username . "/". $Rek['loket_code'] . "/" . $Rek['transaction_date'];

        $cetak = str_replace("[TOTAL]", $total, $cetak);
        $cetak = str_replace("[KODE_TRANSAKSI]", $kode1, $cetak);

        return $cetak;
    }

    private function kertasA4($Rek){

        $jml = sizeof($Rek);
        $cetak = Storage::get('prints/pdambjm/kertas_a4/pdambjm'.$jml.'.struk');

        $c = 1;
        for($i=0;$i<$jml;$i++){
            $cetak = str_replace("A0000".$c."1", $Rek[$i]['cust_id'], $cetak);
            $cetak = str_replace("A0000".$c."2", str_pad($Rek[$i]['gol'],7," ",STR_PAD_RIGHT), $cetak);
            $cetak = str_replace("A0000000000000000000".$c."3", str_pad($Rek[$i]['nama'],22," ",STR_PAD_RIGHT), $cetak);

            $alamat1 = substr($Rek[$i]['alamat'],0,46);
            $alamat2 = substr($Rek[$i]['alamat'],46);

            $cetak = str_replace("A0000000000000000000000000000000000000000000".$c."4", str_pad($alamat1,46," ",STR_PAD_RIGHT), $cetak);
            $cetak = str_replace("A000000000000000000000000000000000000000000".$c."19", str_pad($alamat2,46," ",STR_PAD_RIGHT), $cetak);

            $stand_m3 = $Rek[$i]['stand_kini']."-".$Rek[$i]['stand_lalu']."/".$Rek[$i]['pakai']." m3";
            $cetak = str_replace("A0000000000000".$c."5", str_pad($stand_m3,16," ",STR_PAD_RIGHT), $cetak);

            $blth = $this->bulanTahun($Rek[$i]['blth']);

            $biayaMeter = number_format($Rek[$i]['biaya_meter']);
            $bebanTetap = number_format($Rek[$i]['beban_tetap']);
            $hargaAir = number_format($Rek[$i]['harga_air']);
            $abodemen = number_format($Rek[$i]['abodemen']);
            $materai = number_format($Rek[$i]['materai']);

            $cetak = str_replace("A0000000000000".$c."6", str_pad($blth,16," ",STR_PAD_RIGHT), $cetak);

            $cetak = str_replace("A000000000".$c."7", str_pad($biayaMeter,12," ",STR_PAD_RIGHT), $cetak);
            $cetak = str_replace("A000000000".$c."8", str_pad($bebanTetap,12," ",STR_PAD_RIGHT), $cetak);
            $cetak = str_replace("A000000000".$c."9", str_pad($hargaAir,12," ",STR_PAD_RIGHT), $cetak);
            $cetak = str_replace("A00000000".$c."10", str_pad($abodemen,12," ",STR_PAD_RIGHT), $cetak);
            $cetak = str_replace("A00000000".$c."11", str_pad($materai,12," ",STR_PAD_RIGHT), $cetak);

            $limbah = number_format($Rek[$i]['limbah']);
            $retri = number_format($Rek[$i]['retribusi']);
            $denda = number_format($Rek[$i]['denda']);
            $subtotal = number_format($Rek[$i]['sub_total']);
            $admin = number_format($Rek[$i]['admin']);

            $cetak = str_replace("A00000000".$c."12", str_pad($limbah,12," ",STR_PAD_RIGHT), $cetak);
            $cetak = str_replace("A00000000".$c."13", str_pad($retri,12," ",STR_PAD_RIGHT), $cetak);
            $cetak = str_replace("A00000000".$c."14", str_pad($denda,12," ",STR_PAD_RIGHT), $cetak);
            $cetak = str_replace("A00000000".$c."15", str_pad($subtotal,12," ",STR_PAD_RIGHT), $cetak);
            $cetak = str_replace("A00000000".$c."16", str_pad($admin,12," ",STR_PAD_RIGHT), $cetak);

            $total = number_format($Rek[$i]['total']);
            $username = Auth::user()->username;

            $kode1 = $Rek[$i]['transaction_code'] . "/" . $username;
            $kode2 = $Rek[$i]['loket_code'] . "/" . $Rek[$i]['transaction_date'];

            $cetak = str_replace("A0000000000000".$c."17", str_pad($total,17," ",STR_PAD_RIGHT), $cetak);
            $cetak = str_replace("A00000000000000000000000000000000000000000000000000000000".$c."18", str_pad($kode1,60," ",STR_PAD_RIGHT), $cetak);
            $cetak = str_replace("A00000000000000000000000000000000000000000000000000000000".$c."20", str_pad($kode2,60," ",STR_PAD_RIGHT), $cetak);

            $c++;
        }

        return $cetak;
    }

    private function bulanTahun($Blth){
        $Tahun = substr($Blth, 0, 4);
        $Bulan = substr($Blth, 4, 2);

        $namaBulan = "";
        $BulanTahun = "";
        switch($Bulan){
            case 1:
                $namaBulan = "Januari";
                break;
            case 2:
                $namaBulan = "Februari";
                break;
            case 3:
                $namaBulan = "Maret";
                break;
            case 4:
                $namaBulan = "April";
                break;
            case 5:
                $namaBulan = "Mei";
                break;
            case 6:
                $namaBulan = "Juni";
                break;
            case 7:
                $namaBulan = "Juli";
                break;
            case 8:
                $namaBulan = "Agustus";
                break;
            case 9:
                $namaBulan = "September";
                break;
            case 10:
                $namaBulan = "Oktober";
                break;
            case 11:
                $namaBulan = "Nopember";
                break;
            case 12:
                $namaBulan = "Desember";
                break;
            default:
                $namaBulan = "";
        }
        $BulanTahun = $namaBulan . " " . $Tahun;
        return $BulanTahun;
    }
}