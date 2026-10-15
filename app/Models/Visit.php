<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $fillable = ['home_id', 'resident_id', 'visitor_name', 'visitor_dpi', 'vehicle_plate', 'scheduled_at', 'authorized_at', 'entry_at', 'exit_at', 'status'];
    protected function casts(): array { return ['scheduled_at' => 'datetime', 'authorized_at' => 'datetime', 'entry_at' => 'datetime', 'exit_at' => 'datetime']; }
    public function home() { return $this->belongsTo(Home::class); }
    public function resident() { return $this->belongsTo(Resident::class); }
}
