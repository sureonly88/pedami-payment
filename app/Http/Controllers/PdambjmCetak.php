<?php namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class PdambjmCetak extends Controller
{

    public function __construct()
    {
        //$this->middleware('auth');
    }

    public function cetakRek($nopel = ""){
        if (Auth::check()) {
            return view('admin.cetak_pdambjm')->with('nopel', $nopel);
        }else{
            Session::flash('error', 'You are not loggin yet');
            return Redirect::to('login');
        }
    }

}