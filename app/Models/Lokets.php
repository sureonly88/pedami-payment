<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lokets extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'lokets';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['nama', 'alamat', 'loket_code', 'is_blok', 'blok_message', 'byadmin', 'jenis', 'pulsa'];

}
