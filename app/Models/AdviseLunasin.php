<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdviseLunasin extends Model
{
    use SoftDeletes;
    
    protected $table = 'advise_lunasin';
    protected $dates = ['deleted_at'];
}
