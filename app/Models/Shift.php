<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model {
    protected $fillable = ['nama_shift', 'jam_masuk', 'jam_pulang', 'warna'];
    public function schedules() { return $this->hasMany(Schedule::class); }
}
