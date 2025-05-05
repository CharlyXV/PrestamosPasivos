<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class Recibo extends Model
{
    protected $table = 'recibos';

    protected $fillable = [
        'numero_recibo',
        'tipo_recibo',
        'detalle',
        'estado',
        'banco_id',
        'cuenta_desembolso',
        'monto_recibo',
        'fecha_pago',
        'fecha_deposito',
        'prestamo_id'
    ];

    protected $attributes = [
        'estado' => 'I' // Valor por defecto
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'fecha_deposito' => 'date',
        'monto_recibo' => 'decimal:2'
    ];

    // Relación con banco
    public function banco(): BelongsTo
    {
        return $this->belongsTo(Banco::class);
    }

    // Relación con préstamo
    public function prestamo(): BelongsTo
    {
        return $this->belongsTo(Prestamo::class);
    }

    // Relación con detalles
    public function detalles()
    {
        return $this->hasMany(DetalleRecibo::class);
    }

    public function completar()
    {
        DB::transaction(function () {
            // Marcar recibo como completado
            $this->update(['estado' => 'C']);

            // Actualizar cada cuota asociada
            foreach ($this->detalles as $detalle) {
                if ($planPago = $detalle->planpago) {
                    $planPago->update([
                        'plp_estados' => 'completado',
                        'fecha_pago_real' => $this->fecha_pago
                    ]);

                    // Actualizar saldo del préstamo
                    $prestamo = $this->prestamo;
                    $prestamo->saldo_prestamo -= $detalle->monto_principal;
                    $prestamo->save();
                }
            }
        });
    }

    public function procesarPago()
    {
        DB::transaction(function () {
            // Validar montos antes de procesar
            foreach ($this->detalles as $detalle) {
                $planPago = $detalle->planpago;

                if (!$planPago) {
                    throw new \Exception("La cuota #{$detalle->numero_cuota} no existe");
                }

                // Validar que los montos no excedan los saldos
                if (
                    $detalle->monto_principal > $planPago->saldo_principal ||
                    $detalle->monto_intereses > $planPago->saldo_interes ||
                    $detalle->monto_seguro > $planPago->saldo_seguro ||
                    $detalle->monto_otros > $planPago->saldo_otros
                ) {

                    $mensaje = "Los montos ingresados exceden los saldos disponibles para la cuota #{$detalle->numero_cuota}. ";
                    $mensaje .= "Principal: {$detalle->monto_principal} > {$planPago->saldo_principal}, ";
                    $mensaje .= "Interés: {$detalle->monto_intereses} > {$planPago->saldo_interes}";

                    throw new \Exception($mensaje);
                }
            }

            // Procesar cada detalle
            foreach ($this->detalles as $detalle) {
                $planPago = $detalle->planpago;

                // Guardar montos originales
                $detalle->update([
                    'monto_principal_original' => $planPago->saldo_principal,
                    'monto_intereses_original' => $planPago->saldo_interes,
                    'monto_seguro_original' => $planPago->saldo_seguro,
                    'monto_otros_original' => $planPago->saldo_otros,
                ]);

                // Actualizar saldos
                $planPago->update([
                    'saldo_principal' => $planPago->saldo_principal - $detalle->monto_principal,
                    'saldo_interes' => $planPago->saldo_interes - $detalle->monto_intereses,
                    'saldo_seguro' => $planPago->saldo_seguro - $detalle->monto_seguro,
                    'saldo_otros' => $planPago->saldo_otros - $detalle->monto_otros,
                    'plp_estados' => ($planPago->saldo_principal <= 0 &&
                        $planPago->saldo_interes <= 0 &&
                        $planPago->saldo_seguro <= 0 &&
                        $planPago->saldo_otros <= 0) ? 'completado' : 'pendiente',
                    'fecha_pago_real' => $this->fecha_pago
                ]);
            }

            // Actualizar préstamo
            $this->prestamo->update([
                'saldo_prestamo' => $this->prestamo->saldo_prestamo - $this->detalles->sum('monto_principal'),
                'proximo_pago' => $this->prestamo->planpagos()
                    ->where('plp_estados', 'pendiente')
                    ->orderBy('fecha_pago')
                    ->first()?->fecha_pago
            ]);

            // Cambiar estado a Completado
            $this->update(['estado' => 'C']);
        });
    }

    public function anular()
    {
        DB::transaction(function () {
            // Validar estados permitidos: Incluido (I) o Completado (C)
            if (!in_array($this->estado, ['I', 'C'])) {
                throw new \Exception('Solo se pueden anular recibos en estado Incluido o Completado');
            }

            // Solo revertir saldos si el recibo estaba Completado (C)
            if ($this->estado === 'C') {
                foreach ($this->detalles as $detalle) {
                    $planPago = $detalle->planpago;
                    $planPago->update([
                        'saldo_principal' => $planPago->saldo_principal + ($detalle->monto_principal_original ?? $detalle->monto_principal),
                        'saldo_interes' => $planPago->saldo_interes + ($detalle->monto_intereses_original ?? $detalle->monto_intereses),
                        'saldo_seguro' => $planPago->saldo_seguro + ($detalle->monto_seguro_original ?? $detalle->monto_seguro),
                        'saldo_otros' => $planPago->saldo_otros + ($detalle->monto_otros_original ?? $detalle->monto_otros),
                        'plp_estados' => 'pendiente'
                    ]);
                }

                // Restaurar saldo del préstamo
                $this->prestamo->update([
                    'saldo_prestamo' => $this->prestamo->saldo_prestamo + $this->detalles->sum(function ($detalle) {
                        return $detalle->monto_principal_original ?? $detalle->monto_principal;
                    })
                ]);
            }

            $this->detalles()->delete(); // Elimina los detalles primero
            $this->delete(); // Luego elimina el recibo
        });
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->numero_recibo = $model->numero_recibo ?? 'REC-' . now()->format('YmdHis');
            $model->estado = $model->estado ?? 'I';
        });
    }

    public function canAnular()
    {
        return $this->estado === 'I'; // Solo se puede anular si está Incluido
    }

    public function canCompletar()
    {
        // Verificar que todas las cuotas asociadas existan y estén pendientes
        return $this->estado === 'I' &&
            $this->detalles->every(function ($detalle) {
                return $detalle->planpago &&
                    $detalle->planpago->plp_estados === 'pendiente';
            });
    }
}
