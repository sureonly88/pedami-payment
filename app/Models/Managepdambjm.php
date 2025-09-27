<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Managepdambjm extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'pdambjm_trans';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['transaction_code', 'transaction_date', 'cust_id', 'nama', 'alamat', 'blth', 'harga_air', 'abodemen', 'materai', 'limbah', 'retribusi', 'denda', 'stand_lalu', 'stand_kini', 'sub_total', 'admin', 'total', 'username', 'loket_name', 'loket_code'];

}
