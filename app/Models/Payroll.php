<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = [
        'user_id', 'periode', 'gaji_pokok', 'tunjangan', 'bonus', 
        'jumlah_telat', 'potongan_telat', 'total_bersih', 'status', 'jumlah_alfa', 'potongan_alfa'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}