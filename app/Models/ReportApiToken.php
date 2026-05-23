<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportApiToken extends Model
{
    protected $table = 'report_api_tokens';

    protected $fillable = [
        'nama', 'token', 'allowed_lokets', 'is_active',
    ];

    protected $hidden = [
        'token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Ambil daftar loket yang boleh diakses.
     * Return null jika boleh akses semua.
     */
    public function getAllowedLoketsArray()
    {
        if (is_null($this->allowed_lokets)) {
            return null;
        }
        return json_decode($this->allowed_lokets, true);
    }
}
