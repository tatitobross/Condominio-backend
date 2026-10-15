<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Residente extends Model
{
    use HasFactory;

    protected $table = 'residentes';

    protected $fillable = [
        'user_id',
        'vivienda_id',
        'nombre',
        'apellido',
        'documento',
        'telefono',
        'correo',
        'tipo',
        'es_titular',
        'activo',
    ];

    protected $casts = [
        'es_titular' => 'boolean',
        'activo' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vivienda()
    {
        return $this->belongsTo(Vivienda::class);
    }

    public function visitas()
    {
        return $this->hasMany(Visita::class, 'residente_id');
    }
}
?>
