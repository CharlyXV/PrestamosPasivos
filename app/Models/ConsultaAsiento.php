<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultaAsiento extends Model
{
    protected $table = 'vw_detalle_asiento';
    protected $primaryKey = 'ID';

    protected $fillable = [
        'ID',
        'DEBE',
        'NUMERO_DOCUMENTO',
        'NUMERO_PRESTAMO',
        'FECHA_ASIENTO',
        'MONTO_DEBE',
        'MONTO_HABER',
        'DETALLE',
        'MONEDA'
    ];
    
    protected $casts = [
        'FECHA_ASIENTO' => 'date',
        'MONTO_DEBE' => 'decimal:2',
        'MONTO_HABER' => 'decimal:2',
    ];

    public $timestamps = false;
}
