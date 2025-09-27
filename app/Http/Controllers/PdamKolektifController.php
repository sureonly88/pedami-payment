<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PdamKolektif;
use App\Models\PdamKolektifDetail;
use Illuminate\Support\Facades\Auth;
use Response;
use Illuminate\Support\Facades\Input;
use Validator;
use App\Models\mLoket;

class PdamKolektifController extends Controller
{
    public function __construct()
    {
        
    }

    public function index()
    {
    	return view('admin.pdam_kolektif');
    }

    public function aksiKolektif(){
    	try{

    		$username = Auth::user()->username;
            $loket_id = Auth::user()->loket_id;

            $kodeloket = "";
            $dLoket = mLoket::where("id",$loket_id)->first();
            if($dLoket != null){
                $kodeloket = $dLoket->loket_code;
            }

    		$params = Input::all();
    		$aksi = $params['aksi'];
    		//$aksi = "";

			$rules = array(
		        'nama_kolektif' => 'required'
		    );

		    $validator = Validator::make($params, $rules);

		    if ($validator->fails()) {
		    	$messageValidator = implode(",", $validator->errors()->all());
		    	return Response::json(array(
					'status' => false,
					'message' => $messageValidator
				),200);
		    }

		    $status = "";
    		switch ($aksi) {
    			case 'simpan':
    				$mKolektif = new PdamKolektif();
					$mKolektif->nama = $params['nama_kolektif'];
					$mKolektif->username = $username;
                    $mKolektif->kodeloket = $kodeloket;
					$mKolektif->save();
					$status = "Simpan daftar kolektif berhasil.";
    				break;

    			case 'edit':
    				$mKolektif = PdamKolektif::find($params['id']);
					$mKolektif->nama = $params['nama_kolektif'];
					$mKolektif->username = $username;
                    $mKolektif->kodeloket = $kodeloket;
					$mKolektif->save();
					$status = "Edit ".$params['nama_kolektif']." kolektif berhasil.";
    				break;
    			
    			case 'delete':
    				$cekDetail = PdamKolektifDetail::where('id_kolektif',$params['id'])->get();
    				if($cekDetail->count() <= 0){
    					$mKolektif = PdamKolektif::find($params['id']);
	    				$mKolektif->forceDelete();

	    				$status = "Hapus ".$params['id']." kolektif berhasil.";
    				}else{
    					return Response::json(array(
			                'status' => false,
			                'message' => "Data detail pelanggan masih ada, Kosongkan dulu."
			            ),200);
    				}
    				
    				break;
    			default:
    				return Response::json(array(
		                'status' => false,
		                'message' => "Aksi tidak valid"
		            ),200);
    				break;
    		}

    		return Response::json(array(
                'status' => true,
                'message' => $status
            ),200);
    	}catch (Exception $e) {
	        return Response::json(array(
                'status' => false,
                'message' => "Error simpan."
            ),200);
	    }
    }


    public function aksiDetailKolektif(){
    	try{

    		$params = Input::all();
			$rules = array(
		        'id_pelanggan' => 'required',
		        'nama_pelanggan' => 'required',
		        'id_kolektif' => 'required',
		    );

		    $aksi = $params['aksi'];

		    $validator = Validator::make($params, $rules);

		    if ($validator->fails()) {
		    	$messageValidator = implode(",", $validator->errors()->all());
		    	return Response::json(array(
					'status' => false,
					'message' => $messageValidator
				),200);
		    }

		    $message = "";
    		switch ($aksi) {
    			case 'simpan':
    				$mKolektif = new PdamKolektifDetail();
					$mKolektif->id_pelanggan = $params['id_pelanggan'];
					$mKolektif->nama_pelanggan = $params['nama_pelanggan'];
					$mKolektif->id_kolektif = $params['id_kolektif'];
					$mKolektif->jenis = $params['jenis'];
					$mKolektif->save();

					$message = "Simpan kolektif berhasil.";
    				break;

    			case 'edit':
    				$mKolektif = PdamKolektifDetail::find($params['id']);
					$mKolektif->id_pelanggan = $params['id_pelanggan'];
					$mKolektif->nama_pelanggan = $params['nama_pelanggan'];
					$mKolektif->id_kolektif = $params['id_kolektif'];
					$mKolektif->jenis = $params['jenis'];
					$mLog->save();

					$message = "Edit kolektif berhasil.";
    				break;
    			
    			case 'delete':
    				$mKolektif = PdamKolektifDetail::find($params['id']);
    				$mKolektif->forceDelete();

    				$message = "Hapus kolektif berhasil.";
    				break;
    			default:
    				return Response::json(array(
		                'status' => false,
		                'message' => "Aksi tidak valid."
		            ),200);
    				break;
    		}

    		return Response::json(array(
                'status' => true,
                'message' => $message
            ),200);
    	}catch (\Exception $e) {
	        return Response::json(array(
                'status' => false,
                'message' => "Error simpan."
            ),200);
	    }
    }

    public function getKolektifId($vId){
    	try{
    		//$username = Auth::user()->username;
    		$kolektif = PdamKolektif::where('id',$vId)->first();

    		if($kolektif){
    			return Response::json(array(
	                'status' => true,
	                'message' => "-",
	                'data' => $kolektif->toArray()
	            ),200);
    		}else{
    			return Response::json(array(
	                'status' => false,
	                'message' => "DATA TIDAK ADA"
	            ),200);
    		}
    	}catch (\Exception $e) {
	        return Response::json(array(
                'status' => false,
                'message' => "ERROR GET DAFTAR KOLEKTIF"
            ),200);
	    }
    }


    public function getKolektif(){
    	try{
    		$username = Auth::user()->username;
            $loket_id = Auth::user()->loket_id;

            $kodeloket = "";
            $dLoket = mLoket::where("id",$loket_id)->first();
            if($dLoket != null){
                $kodeloket = $dLoket->loket_code;
            }
            
    		$kolektif = PdamKolektif::where('kodeloket',$kodeloket)->get();

    		if($kolektif->count() > 0){
    			return Response::json(array(
	                'status' => true,
	                'message' => "-",
	                'data' => $kolektif->toArray()
	            ),200);
    		}else{
    			return Response::json(array(
	                'status' => false,
	                'message' => "DATA TIDAK ADA"
	            ),200);
    		}
    	}catch (\Exception $e) {
	        return Response::json(array(
                'status' => false,
                'message' => "ERROR GET DAFTAR KOLEKTIF"
            ),200);
	    }
    }

    public function getDetailKolektif($idKolektif){
    	try{
    		$detail = PdamKolektifDetail::where('id_kolektif',$idKolektif)->get();

    		if($detail->count() > 0){
    			return Response::json(array(
	                'status' => true,
	                'message' => "-",
	                'data' => $detail->toArray()
	            ),200);
    		}else{
    			return Response::json(array(
	                'status' => false,
	                'message' => "DATA TIDAK ADA"
	            ),200);
    		}
    	}catch (\Exception $e) {
	        return Response::json(array(
                'status' => false,
                'message' => "ERROR GET DETAIL KOLEKTIF"
            ),200);
	    }
    }
}
