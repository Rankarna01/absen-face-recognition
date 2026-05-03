<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model {
    protected $fillable = ['division_id', 'nama_jabatan'];
    public function division() { return $this->belongsTo(Division::class); }
}
