<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReporteDisponibilidad extends Model
{
    protected $table = 'vw_disponibilidad_bancos';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre_banco',
        'saldo',
        'capital_trabajo',
        'disponible'
    ];
    
    protected $casts = [
        'saldo' => 'decimal:2',
        'capital_trabajo' => 'decimal:2',
        'disponible' => 'decimal:2',
    ];
}