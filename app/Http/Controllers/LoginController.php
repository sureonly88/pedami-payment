<?php

namespace App\Http\Controllers;

use Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use App\Models\mLoket;
use App\Events\LoginEvent;
use App\Events\LogoutEvent;

class LoginController extends Controller
{
    public function index()
    {
        return view('login');
    }

    public function login(){
        $username = Request::get('username');
        $password = Request::get('password');

        $userdata = array(
            'username' => $username,
            'password' => $password
        );
        // doing login.
        if (Auth::validate($userdata)) {
            if (Auth::attempt($userdata)) {

                $user = Auth::user();

                $mLoket = mLoket::where('id',$user->loket_id)->first();

				$is_blok = $mLoket->is_blok;
				$blok_message = $mLoket->blok_message;
				$jenis = $mLoket->jenis;
				
				if($is_blok == 1){
					Session::flash('error', $blok_message);
					return Redirect::to('login');
				}
				
				if($jenis == 'ANDROID'){
					Session::flash('error', 'Hanya user Kasir yang bisa login');
					return Redirect::to('login');
				}
				
				if($jenis == 'PM'){
					Session::flash('error', 'Hanya user Kasir yang bisa login');
					return Redirect::to('login');
				}
				
                $user->session_id = Auth::getSession()->getId();
                $user->save();
                event(new LoginEvent($username));

                return Redirect::intended('/admin');
            }
        }else {
            // if any error send back with message.
            Session::flash('error', 'Wrong Username or Password');
            return Redirect::to('login');
        }

    }

    public function logout() {
        try{
            event(new LogoutEvent(Auth::user()->username));
            Auth::logout(); // logout user
            return Redirect::to('login'); //redirect back to login
        }catch (\Exception $e){
            Auth::logout(); // logout user
            return Redirect::to('login'); //redirect back to login
        }
        
    }
}
