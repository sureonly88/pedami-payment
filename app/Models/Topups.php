<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\mLoket;
use App\Users;

class Topups extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'topups';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['loket_id', 'topup_money', 'topup_date', 'note', 'payment_id'];

    public function loket()
    {
        return $this->belongsTo('App\mLoket');
    }

}
