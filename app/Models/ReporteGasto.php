<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReporteGasto extends Model
{
    protected $table = 'vw_reporte_gastos';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'recibo',
        'numero_prestamo',
        'fecha_pago',
        'monto_principal',
        'monto_intereses'
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'monto_principal' => 'decimal:2',
        'monto_intereses' => 'decimal:2',
    ];

    public $timestamps = false;
}
