<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    protected $fillable = ['home_id', 'reported_by', 'title', 'description', 'priority', 'status', 'reported_at', 'resolved_at'];
    protected function casts(): array { return ['reported_at' => 'datetime', 'resolved_at' => 'datetime']; }
    public function home() { return $this->belongsTo(Home::class); }
    public function reporter() { return $this->belongsTo(User::class, 'reported_by'); }
}
