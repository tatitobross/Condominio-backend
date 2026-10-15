<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vivienda extends Model
{
    use HasFactory;

    protected $table = 'viviendas';

    protected $fillable = [
        'codigo',
        'torre_sector',
        'tipo',
        'estado',
        'cuota_mensual',
        'activo',
    ];

    protected $casts = [
        'cuota_mensual' => 'float',
        'activo' => 'boolean',
    ];

    public function residentes()
    {
        return $this->hasMany(Residente::class);
    }

    public function visitas()
    {
        return $this->hasMany(Visita::class);
    }

    public function cuotas()
    {
        return $this->hasMany(Cuota::class);
    }

    public function incidencias()
    {
        return $this->hasMany(Incidencia::class);
    }
}
?>
