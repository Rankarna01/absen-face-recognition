<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model {
    protected $fillable = ['user_id', 'division_id', 'position_id', 'foto', 'face_descriptor'];
    
    public function user() { return $this->belongsTo(User::class); }
    public function division() { return $this->belongsTo(Division::class); }
    public function position() { return $this->belongsTo(Position::class); }
}