<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Models\mLoket;
use Spatie\Permission\Models\Role;
use DB;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        
        view()->composer('*', function($view){
            if(!empty(Auth::user())){
                $username = Auth::user()->username;
                $role = Auth::user()->role;
                $loket_id = Auth::user()->loket_id;
                $email = Auth::user()->email;
                $lastLogin = Auth::user()->updated_at;

                $namaLoket = "-";
                $codeLoket = "-";
                $pulsa = 0;
                $byadmin = 0;
                $jenis = "-";

                $dLoket = mLoket::where("id",$loket_id)->first();
                if($dLoket != null){
                    $namaLoket = $dLoket->nama;
                    $codeLoket = $dLoket->loket_code;
                    $pulsa = $dLoket->pulsa;
                    $byadmin = $dLoket->byadmin;
                    $jenis = $dLoket->jenis;
                }

                $permissions = Auth::user()->getAllPermissions()->toArray();

                $userId = Auth::user()->id;
                // $roles = DB::table('user_has_roles')
                //     ->leftJoin('roles','user_has_roles.role_id','=','roles.id')
                //     ->where("model_id",$userId)
                //     ->select('roles.name')
                //     ->get();

                // $roles = $roles->toArray();

                $permissions = array_column($permissions, 'name');

                //dd(array_search("Transaksi PLN Postpaid", $permissions));

                $loginData = array(
                    "username" => $username,
                    "role" => $role,
                    "loket_name" => $namaLoket,
                    "loket_code" => $codeLoket,
                    "pulsa" => $pulsa,
                    "byadmin" => $byadmin,
                    "email" => $email,
                    "lastlogin" => $lastLogin,
                    "jenis" => $jenis,
                    "permissions" => $permissions
                    // "roles" => $roles
                );

                $view->with('user',$loginData);
            }
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
