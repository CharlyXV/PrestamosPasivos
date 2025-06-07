<?php

namespace App\Http\Controllers;

use App\Exports\ReporteDisponibilidadExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportDisponibilidadController extends Controller
{
    public function __invoke(): BinaryFileResponse
    {
        return Excel::download(
            new ReporteDisponibilidadExport(),
            'reporte_disponibilidad_'.now()->format('Y-m-d_H-i-s').'.xlsx'
        );
    }
}