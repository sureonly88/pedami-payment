<?php namespace App\Http\Controllers;

use Request;
use App\Models\mUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class PdambjmController extends Controller {
    public function __construct()
    {
        //$this->middleware('is_admin');
        //$this->middleware('auth');
    }

    public function index()
    {
        if (Auth::check()) {
            return view('admin.pdambjm');
        }else{
            Session::flash('error', 'You are not loggin yet');
            return Redirect::to('login');
        }
    }
}