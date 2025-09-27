<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdvisePDAM extends Model
{
    use SoftDeletes;
    
    protected $table = 'advise_pdam';
    protected $dates = ['deleted_at'];
}
