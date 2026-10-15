<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Home extends Model
{
    protected $fillable = ['code', 'block', 'address', 'status'];
    public function residents() { return $this->hasMany(Resident::class); }
    public function visits() { return $this->hasMany(Visit::class); }
    public function payments() { return $this->hasMany(Payment::class); }
}
