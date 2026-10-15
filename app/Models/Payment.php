<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['home_id', 'period', 'due_date', 'amount', 'paid_at', 'status', 'reference'];
    protected function casts(): array { return ['due_date' => 'date', 'paid_at' => 'datetime', 'amount' => 'decimal:2']; }
    public function home() { return $this->belongsTo(Home::class); }
}
