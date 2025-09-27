<?php

namespace App\Services;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Input;
use Validator;
use Response;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Helpers;

class ManageSaldo
{

	public function __construct(){

	}

	public function KonfirmasiPembayaran($Data){
		try{

			$rules = array(
		        'tgl_bayar' => 'required',
		        'total_konfirmasi' => 'required|numeric',
		        'metode_bayar' => 'required',
		        'bank_konfirmasi' => 'required',
		        'nama_pemilik_bank' => 'required',
		    );

		    $validator = Validator::make($Data, $rules);

		    if ($validator->fails()) {
		    	return array(
					'status' => 'Error',
					'message' => $validator->errors()->all()
				);
		    }

			$KodeRequest = $Data['request_code'];
			unset($Data['request_code']);

			$cekKode = DB::table('request_saldo')
				->where('request_code',$KodeRequest)
				->count();
			if($cekKode > 0){

				$Data['is_konfirmasi'] = 1;
				$Data['tgl_konfirmasi'] = date('Y-m-d H:i:s');

				DB::table('request_saldo')->where('request_code',$KodeRequest)->update($Data);

				return array(
					'status' => 'Success',
					'message' => 'Konfirmasi Berhasil'
				);
			}else{
				return array(
	                'status' => 'Error',
	                'message' => array("Nomor Request tidak ditemukan."),
	            );
			}

		}catch (Exception $e){
			$error = explode("\r\n",$e->getMessage());
            return array(
                'status' => 'Error',
                'message' => array($error),
            );
		}
	}

	public function SimpdamPermintaan($Data){
		try{

			//$Data = Input::all()['Data'];
			
			$userLogin = Helpers::getLoginDetail();
			$username = $userLogin['username'];
			$kodeLoket = $userLogin['loket_code'];
			
			//Edit Validator Here
			$rules = array(
		        'request_saldo' => 'required|numeric',
		        'ket_request' => 'required',
		    );

		    $validator = Validator::make($Data, $rules);

		    if ($validator->fails()) {
		    	return array(
					'status' => 'Error',
					'message' => $validator->errors()->all()
				);
		    }else{

		    	$cekValid = DB::table('request_saldo')
		    		->where('kode_loket',$kodeLoket)
		    		->where(DB::raw("IFNULL(is_verifikasi,0)"),'=',0)
		    		->count();
		    	if($cekValid > 0){
		    		return array(
		                'status' => 'Error',
		                'message' => array("Permintaan sebelumnya belum selesai."),
		            );
		    	}

				$Id = $Data['id'];

	    		unset($Data['id']);
	    		$unique_id = strtoupper(date('YmdHis').$kodeLoket);

	    		$Data['request_code'] = $unique_id;
	    		$Data['username'] = $username;
	    		$Data['kode_loket'] = $kodeLoket;
	    		$Data['tgl_request'] = date('Y-m-d H:i:s');
	    		$Data['created_at'] = date('Y-m-d H:i:s');
				$Data['updated_at'] = date('Y-m-d H:i:s');
	    		DB::table('request_saldo')->insert($Data);

				return array(
					'status' => 'Success',
					'message' => 'Simpan Berhasil',
					'data' => $Data
				);
		    }

		}catch (Exception $e){
			$error = explode("\r\n",$e->getMessage());
            return array(
                'status' => 'Error',
                'message' => array($error),
            );
		}
	}

	public function SimpanVerifikasi($Data){
		try{

			//$Data = Input::all()['Data'];
			
			$userLogin = Helpers::getLoginDetail();
			$kodeRequest = $Data['request_code'];
			$username = $userLogin['username'];
			$kodeLoket = $userLogin['loket_code'];

			$LoketMinta = DB::table('request_saldo')
				->where('request_code','=',$kodeRequest)
				->select(['kode_loket'])->first()->kode_loket;
			
			//Edit Validator Here
			$rules = array(
		        'status_verifikasi' => 'required',
		        'verifikasi_saldo' => 'required|numeric',
		        'request_code' => 'required',
		    );

		    $validator = Validator::make($Data, $rules);

		    if ($validator->fails()) {
		    	return array(
					'status' => 'Error',
					'message' => $validator->errors()->all()
				);
		    }else{

		    	$cekValid = DB::table('request_saldo')
		    		->where('request_code',$kodeRequest)
		    		->where(DB::raw("IFNULL(is_verifikasi,0)"),'=',1)
		    		->count();
		    	if($cekValid > 0){
		    		return array(
		                'status' => 'Error',
		                'message' => array("Permintaan sudah diverifikasi."),
		            );
		    	}

		    	$Kode = $Data['request_code'];

	    		unset($Data['request_code']);
	    		$Data['is_verifikasi'] = 1;
	    		$Data['username_verifikasi'] = $username;
	    		$Data['tgl_verifikasi'] = date('Y-m-d H:i:s');

	    		$KodeLoketTujuan = $Data['kode_loket_tujuan'];
	    		unset($Data['kode_loket_tujuan']);

	    		if($Data['status_verifikasi'] == 'DISETUJUI'){
	    			DB::table('lokets')->where('loket_code',$KodeLoketTujuan)->increment('pulsa',$Data['verifikasi_saldo']);
	    		}else{
	    			$Data['verifikasi_saldo'] = 0;
	    		}

	    		DB::table('request_saldo')->where('request_code',$kodeRequest)->update($Data);

	    		$Data['request_code'] = $Kode;

				return array(
					'status' => 'Success',
					'message' => 'Simpan Berhasil',
					'data' => $Data,
					'loket' => $LoketMinta
				);
		    }

		}catch (Exception $e){
			$error = explode("\r\n",$e->getMessage());
            return array(
                'status' => 'Error',
                'message' => array($error),
            );
		}
	}

	public function getPermintaanByKode($Kode){
		try{
			$cekId = DB::table('request_saldo')->where('request_code',$Kode)->first();

			if($cekId){
				return array(
					'status' => 'Success',
					'message' => '-',
					'data' => $cekId,
				);
			}else{
				return array(
	                'status' => 'Error',
	                'message' => 'Data tidak ditemukan.',
	                'data' => ''
	            );
			}
			
		}catch (Exception $e){

			$error = explode("\r\n",$e->getMessage());
            return array(
                'status' => 'Error',
                'message' => $error,
                'data' => ''
            );
		}
	}

	public function getListBuatNotif(){
		try{
			$mList = DB::table('request_saldo')
				->whereNull('is_verifikasi')
				->select('username','kode_loket','request_code','tgl_request','request_saldo')
				->orderBy('tgl_request','desc')
				->limit(15)
				->get();
			return array(
				'status' => 'Success',
				'message' => '-',
				'data' => $mList
			);
		}catch (Exception $e){
			$error = explode("\r\n",$e->getMessage());
            return array(
                'status' => 'Error',
                'message' => $error
            );
		}
	}

	public function getListPermintaan($Kondisi, $stat){
		try{
			$userLogin = Helpers::getLoginDetail();
			$username = $userLogin['username'];
			$kodeLoket = $userLogin['loket_code'];

			$mUsers = "";
			switch ($Kondisi) {
				case 'user':
					$mUsers = DB::table('request_saldo')
						->leftJoin('lokets','request_saldo.kode_loket','=','lokets.loket_code')
						->leftJoin('rekening_tujuan','rekening_tujuan.id','=','request_saldo.id_bank_tujuan')
						->select('request_saldo.id',
							DB::raw("concat(rekening_tujuan.nama,' - ',rekening_tujuan.nomor,' a/n ',rekening_tujuan.atas_nama) as nama_bank_tujuan"),
							'request_saldo.request_code',
							'request_saldo.username',
							'request_saldo.kode_loket',
							'lokets.nama',
							'request_saldo.request_saldo',
							'request_saldo.tgl_request',
							'request_saldo.tgl_konfirmasi',
							'request_saldo.ket_request',
							'request_saldo.verifikasi_saldo',
							'request_saldo.ket_verifikasi',
							'request_saldo.ket_konfirmasi',
							DB::raw("IFNULL(request_saldo.is_konfirmasi,0) is_konfirmasi"),	
							DB::raw("IFNULL(request_saldo.is_verifikasi,0) is_verifikasi"),	
							DB::raw("IFNULL(request_saldo.tgl_verifikasi,'-') tgl_verifikasi"),	
							DB::raw("IFNULL(request_saldo.tgl_konfirmasi,'-') tgl_konfirmasi"),	
							DB::raw("'' as aksi"))
						->where('request_saldo.kode_loket',$kodeLoket)
						->orderBy("request_saldo.tgl_request","desc")
						->get();
					break;

				case 'admin':
					$mUsers = DB::table('request_saldo')
						->leftJoin('lokets','request_saldo.kode_loket','=','lokets.loket_code')
						->leftJoin('rekening_tujuan','rekening_tujuan.id','=','request_saldo.id_bank_tujuan')
						->select('request_saldo.id',
							DB::raw("concat(rekening_tujuan.nama,' - ',rekening_tujuan.nomor,' a/n ',rekening_tujuan.atas_nama) as nama_bank_tujuan"),
							'request_saldo.request_code',
							'request_saldo.username',
							DB::raw("request_saldo.kode_loket"),
							DB::raw("CONCAT(request_saldo.kode_loket,'-',lokets.nama) as tmp_kode_loket"),
							'request_saldo.request_saldo',
							'request_saldo.tgl_request',
							'request_saldo.tgl_konfirmasi',
							'request_saldo.ket_request',
							'request_saldo.verifikasi_saldo',
							'request_saldo.ket_verifikasi',
							'request_saldo.ket_konfirmasi',
							DB::raw("IFNULL(request_saldo.is_konfirmasi,0) is_konfirmasi"),	
							DB::raw("IFNULL(request_saldo.is_verifikasi,0) is_verifikasi"),	
							DB::raw("concat(request_saldo.bank_konfirmasi,' a/n ', request_saldo.nama_pemilik_bank) as bank_pengirim"),
							DB::raw("CASE IFNULL(request_saldo.is_konfirmasi,0) WHEN 0 THEN 'Belum Konfirmasi' ELSE 'Sudah Konfirmasi' END status_konfirmasi"),
							DB::raw("CASE IFNULL(request_saldo.is_verifikasi,0) WHEN 0 THEN 'Belum Verifikasi' ELSE 'Sudah Verifikasi' END status_verifikasi"),
							DB::raw("IFNULL(request_saldo.tgl_verifikasi,'-') tgl_verifikasi"),	
							DB::raw("IFNULL(request_saldo.tgl_konfirmasi,'-') tgl_konfirmasi"),	
							DB::raw("'' as aksi"))
						->where(DB::raw("IFNULL(request_saldo.is_verifikasi,0)"),"=",$stat)
						->orderBy("request_saldo.tgl_request","desc")
						->get();
					break;
				
				default:
					$mUsers = "";
					break;
			}

			return array(
				'status' => 'Success',
				'message' => '-',
				'data' => $mUsers
			);
		}catch (Exception $e){
			$error = explode("\r\n",$e->getMessage());
            return array(
                'status' => 'Error',
                'message' => $error
            );
		}
	}
}