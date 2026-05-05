<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'app_name', 'app_logo', 'app_address', 'default_jam_masuk', 
        'default_jam_pulang', 'toleransi_keterlambatan', 'office_latitude', 
        'office_longitude', 'office_radius', 'api_key', 'nominal_potongan_telat',
        'nominal_potongan_alfa'
    ];
}