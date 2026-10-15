<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visita extends Model
{
    use HasFactory;

    protected $table = 'visitas';

    protected $fillable = [
        'vivienda_id',
        'residente_id',
        'visitante_nombre',
        'visitante_documento',
        'placa',
        'motivo',
        'fecha_esperada',
        'vigente_hasta',
        'estado',
        'entrada_en',
        'salida_en',
        'guardia_entrada_id',
        'guardia_salida_id',
        'observaciones',
    ];

    protected $casts = [
        'fecha_esperada' => 'datetime',
        'vigente_hasta'   => 'datetime',
        'entrada_en'      => 'datetime',
        'salida_en'       => 'datetime',
    ];

    public function vivienda()
    {
        return $this->belongsTo(Vivienda::class);
    }

    public function residente()
    {
        return $this->belongsTo(Residente::class);
    }

    public function guardiaEntrada()
    {
        return $this->belongsTo(User::class, 'guardia_entrada_id');
    }

    public function guardiaSalida()
    {
        return $this->belongsTo(User::class, 'guardia_salida_id');
    }
}
?>
