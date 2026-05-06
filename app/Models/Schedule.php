<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model {
    
    // PERBAIKAN: Tambahkan 'is_libur' agar diizinkan masuk ke database
    protected $fillable = [
        'user_id', 
        'shift_id', 
        'tanggal', 
        'is_libur', 
        'keterangan_libur'
    ];

    public function user() { 
        return $this->belongsTo(User::class); 
    }
    
    public function shift() { 
        return $this->belongsTo(Shift::class); 
    }
}