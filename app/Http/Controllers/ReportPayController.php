<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use App\Models\Planpago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportPayController extends Controller
{
    // Métodos anteriores sin cambios...

    public function generateReport($prestamoId)
    {
        try {
            $prestamo = Prestamo::with(['planpagos', 'empresa'])->findOrFail($prestamoId);

            // Configurar opciones de PDF
            $options = [
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
                'fontDir' => storage_path('fonts/'),
                'fontCache' => storage_path('fonts/'),
                'chroot' => public_path()
            ];

            // Asegurarse que todos los datos están en UTF-8 correctamente
            $processedPrestamo = clone $prestamo;

            // Limpiar todos los strings en el objeto prestamo para asegurar codificación UTF-8 correcta
            $this->sanitizeObject($processedPrestamo);

            $planPagos = $processedPrestamo->planpagos->map(function ($item) {
                return $this->sanitizeObject($item);
            });

            // Generar PDF con opciones adicionales
            $pdf = Pdf::loadView('report.pay_report', [
                'prestamo' => $processedPrestamo,
                'planPagos' => $planPagos
            ])->setOptions($options);

            // Establecer papel y orientación
            $pdf->setPaper('a4', 'portrait');

            return $pdf->download("plan_pagos_{$prestamo->id}.pdf");
        } catch (\Exception $e) {
            Log::error("Error al generar PDF: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return back()->with('error', 'Error al generar el reporte: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $validatedData = $this->validatePrestamoData($request);

        $prestamo = DB::transaction(function () use ($validatedData) {
            $prestamo = Prestamo::create($validatedData);
            $this->createPaymentPlan($prestamo);
            return $prestamo;
        });

        return redirect()->route('prestamos.index')
            ->with('success', 'Préstamo y plan de pagos creados exitosamente.');
    }

    protected function validatePrestamoData(Request $request)
    {
        return $request->validate([
            'empresa_id' => 'required|integer',
            'numero_prestamo' => 'required|string|unique:prestamos',
            'monto_prestamo' => 'required|numeric|min:0.01',
            'tasa_interes' => 'required|numeric|min:0',
            'plazo_meses' => 'required|integer|min:1',
            'banco_id' => 'required|integer',
            'linea_id' => 'required|integer',
            'formalizacion' => 'required|date',
            // otros campos necesarios
        ]);
    }
    /*
    public function createPaymentPlan(Prestamo $prestamo)
    {
        try {
            Planpago::where('prestamo_id', $prestamo->id)->delete();

            $loanAmount = (float)$prestamo->monto_prestamo;
            $interestRate = (float)$prestamo->tasa_interes / 100;
            $term = (int)$prestamo->plazo_meses;
            $monthlyRate = $interestRate / 12;

            $monthlyPayment = $loanAmount * $monthlyRate / (1 - pow(1 + $monthlyRate, -$term));
            $remainingBalance = $loanAmount;

            for ($i = 1; $i <= $term; $i++) {
                $interestPayment = $remainingBalance * $monthlyRate;
                $principalPayment = $monthlyPayment - $interestPayment;
                $remainingBalance -= $principalPayment;

                Planpago::create([
                    'prestamo_id' => $prestamo->id,
                    'numero_cuota' => $i,
                    'fecha_pago' => $this->calculateDueDate($prestamo->formalizacion, $i),
                    'monto_principal' => $this->roundAmount($principalPayment),
                    'monto_interes' => $this->roundAmount($interestPayment),
                    'monto_seguro' => 1,
                    'monto_otros' => 1,
                    'saldo_prestamo' => $this->roundAmount(max($remainingBalance, 1)),
                    'tasa_interes' => $prestamo->tasa_interes,
                    'saldo_principal' => $this->roundAmount($principalPayment),
                    'saldo_interes' => $this->roundAmount($interestPayment),
                    'saldo_seguro' => 1,
                    'saldo_otros' => 1,
                    'observaciones' => 'Cuota ' . $i . ' de ' . $term,
                    'plp_estados' => 'pendiente'
                ]);
            }

            Log::info("Plan de pagos generado para préstamo {$prestamo->id}");
        } catch (\Exception $e) {
            Log::error("Error generando plan de pagos: " . $e->getMessage());
            throw $e;
        }
    }

    protected function calculateDueDate($startDate, $monthOffset)
    {
        return Carbon::parse($startDate)
            ->addMonths((int)$monthOffset) // Asegurar conversión a entero
            ->format('Y-m-d');
    }

    private function roundAmount(float $amount, int $decimals = 2): float
    {
        return round($amount, $decimals);
    }
    */


    public function createPaymentPlan(Prestamo $prestamo)
    {
        DB::beginTransaction();
    
        try {
            // Validar datos del préstamo
            if (!$prestamo->periodicidad_pago || !$prestamo->plazo_meses || !$prestamo->formalizacion) {
                throw new \Exception("Datos incompletos para generar el plan de pagos");
            }
    
            // Eliminar planes de pago existentes
            Planpago::where('prestamo_id', $prestamo->id)->delete();
    
            $loanAmount = (float)$prestamo->monto_prestamo;
            $interestRate = (float)$prestamo->tasa_interes / 100;
            $numPagos = (int)$prestamo->plazo_meses; // Número de periodos (cuotas)
            $periodicidad = (int)$prestamo->periodicidad_pago;
            $mesesEntrePagos = 12 / $periodicidad; // Meses entre cada pago
            
            // Calcular tasa periódica y pago periódico
            $periodicRate = $interestRate / $periodicidad;
            $periodicPayment = $loanAmount * $periodicRate / (1 - pow(1 + $periodicRate, -$numPagos));
            $remainingBalance = $loanAmount;
    
            // Generar cada cuota del plan de pagos
            for ($i = 1; $i <= $numPagos; $i++) {
                $interestPayment = $remainingBalance * $periodicRate;
                $principalPayment = $periodicPayment - $interestPayment;
                $remainingBalance -= $principalPayment;
    
                // Crear registro de pago
                Planpago::create([
                    'prestamo_id' => $prestamo->id,
                    'numero_cuota' => $i,
                    'fecha_pago' => $this->calculateDueDate($prestamo->formalizacion, $i * $mesesEntrePagos),
                    'monto_principal' => $this->roundAmount($principalPayment),
                    'monto_interes' => $this->roundAmount($interestPayment),
                    'saldo_prestamo' => $this->roundAmount(max($remainingBalance, 0)),
                    'tasa_interes' => $prestamo->tasa_interes,
                    'saldo_principal' => $this->roundAmount($principalPayment),
                    'saldo_interes' => $this->roundAmount($interestPayment),
                    'observaciones' => $this->generatePaymentDescription($i, $numPagos, $periodicidad),
                    'plp_estados' => 'pendiente'
                ]);
            }
    
            DB::commit();
            Log::info("Plan de pagos generado para préstamo {$prestamo->id} con {$numPagos} pagos {$this->getPeriodicidadNombre($periodicidad)}");
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error generando plan de pagos: ".$e->getMessage());
            throw $e;
        }
    }
    
    // Métodos auxiliares (pueden ir en el mismo controlador o en un trait)
    protected function calculateDueDate($startDate, $monthOffset)
    {
        return Carbon::parse($startDate)
            ->addMonths((int)$monthOffset)
            ->format('Y-m-d');
    }
    
    protected function roundAmount(float $amount, int $decimals = 2): float
    {
        return round($amount, $decimals);
    }
    
    protected function generatePaymentDescription($current, $total, $periodicidad): string
    {
        $periodName = match($periodicidad) {
            1 => 'anual',
            2 => 'semestral',
            3 => 'cuatrimestral',
            4 => 'trimestral',
            6 => 'bimestral',
            12 => 'mensual',
            default => 'de pago'
        };
        
        return "Cuota {$current} de {$total} (Pago {$periodName})";
    }
    
    protected function getPeriodicidadNombre($periodicidad): string
    {
        return match((int)$periodicidad) {
            1 => 'anuales',
            2 => 'semestrales',
            3 => 'cuatrimestrales',
            4 => 'trimestrales',
            6 => 'bimestrales',
            12 => 'mensuales',
            default => 'periodos'
        };
    }



    private function sanitizeObject($object)
    {
        if (is_object($object)) {
            $attributes = get_object_vars($object);
            foreach ($attributes as $key => $value) {
                if (is_string($value)) {
                    // Intentar detectar la codificación original y convertir a UTF-8
                    $encoding = mb_detect_encoding($value, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
                    if ($encoding && $encoding !== 'UTF-8') {
                        $object->{$key} = mb_convert_encoding($value, 'UTF-8', $encoding);
                    } elseif (!$encoding) {
                        // Si no se puede detectar la codificación, forzar UTF-8 limpio
                        $object->{$key} = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                    }
                } elseif (is_object($value) || is_array($value)) {
                    $object->{$key} = $this->sanitizeObject($value);
                }
            }
        } elseif (is_array($object)) {
            foreach ($object as $key => $value) {
                if (is_string($value)) {
                    $encoding = mb_detect_encoding($value, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
                    if ($encoding && $encoding !== 'UTF-8') {
                        $object[$key] = mb_convert_encoding($value, 'UTF-8', $encoding);
                    } elseif (!$encoding) {
                        $object[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                    }
                } elseif (is_object($value) || is_array($value)) {
                    $object[$key] = $this->sanitizeObject($value);
                }
            }
        }

        return $object;
    }
}