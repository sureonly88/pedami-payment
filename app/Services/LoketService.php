<?php

namespace App\Services;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\mLoket;
use DB;

class LoketService
{

	public function getSaldo($loket_id){
		
		$saldo = 0;
		$cekLoket = mLoket::where("id",$loket_id)->first();
		if($cekLoket){
			$saldo = $cekLoket->pulsa;
		}

		return $saldo;
	}

	public function kurangiSaldo($pengurang,$loket_id){
		DB::table('lokets')->where('id', '=', $loket_id)->decrement('pulsa', $pengurang);
	}
}