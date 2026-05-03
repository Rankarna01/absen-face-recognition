<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    protected $fillable = [
        'user_id', 'jenis_izin', 'tanggal_mulai', 'tanggal_selesai', 
        'alasan', 'lampiran', 'status', 'keterangan_admin'
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}