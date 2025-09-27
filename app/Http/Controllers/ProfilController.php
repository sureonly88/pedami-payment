<?php namespace App\Http\Controllers;

use App\Models\mLoket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class ProfilController extends Controller {

    public function __construct()
    {
        //$this->middleware('auth');
    }

    public function index()
    {
        return view('admin.profil');
    }

}
