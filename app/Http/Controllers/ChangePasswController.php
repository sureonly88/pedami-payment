<?php namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Hash;
use App\User;

class ChangePasswController extends Controller {

    public function __construct()
    {
        //$this->middleware('auth');
    }

    public function index()
    {
        //return var_dump(Helpers::getLoginDetail());

        return view('admin.change_passw');
    }

    public function edit(Request $request){

        $Data = Request::all();
        $Username = $Data['username'];
        $Password = $Data['password'];
        $HashPassw = Hash::make($Password);


        if(strlen($Password) < 3){
            return view('admin.change_passw')
                ->with('user', Helpers::getLoginDetail())
                ->with('error','Password terlalu pendek, minimal 3 character.');
        }

        $findUser = User::where('username','=',$Username)->first();
        if($findUser){
            $arrInsert = array();
            $mUsername = Auth::user()->username;

            if(!$mUsername){
                return view('admin.change_passw')
                    ->with('user', Helpers::getLoginDetail())
                    ->with('error','Password gagal diganti karena User tidak login.');
            }

            $arrInsert['username'] = $mUsername;
            $arrInsert['password'] = $HashPassw;

            //return $findUser->password . "<>" . $HashPassLama;
            
            // if($findUser->password != $HashPassLama){
            //     return view('admin.change_passw')
            //         ->with('user', Helpers::getLoginDetail())
            //         ->with('error','Password lama salah, coba lagi.');
            // }

            User::where('username','=',$Username)->update($arrInsert);
            return view('admin.change_passw')
                ->with('user', Helpers::getLoginDetail())
                ->with('pesan','Password Berhasil Diganti, Gunakan password baru pada login selanjutnya.');
        }else{
            return view('admin.change_passw')
                ->with('user', Helpers::getLoginDetail())
                ->with('error','Password Gagal Diganti.');
        }
    }

}
