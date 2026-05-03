<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id', 'tanggal', 'jam_masuk', 'jam_pulang', 
        'status_kehadiran', 'foto_masuk', 'foto_pulang'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}