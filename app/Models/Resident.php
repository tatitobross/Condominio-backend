<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resident extends Model
{
    protected $fillable = ['home_id', 'user_id', 'name', 'dpi', 'phone', 'email', 'status'];
    public function home() { return $this->belongsTo(Home::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function visits() { return $this->hasMany(Visit::class); }
}
