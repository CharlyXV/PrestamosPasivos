<?php

namespace App\Filament\Exports;

use App\Models\ReporteDisponibilidad;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReporteDisponibilidadExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReporteDisponibilidadExporter extends Exporter
{
    protected static ?string $model = ReporteDisponibilidad::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('nombre_banco')->label('Banco'),
            ExportColumn::make('saldo')
                ->label('Saldo Total')
                ->formatStateUsing(fn (float $state): string => '$' . number_format($state, 2)),
            ExportColumn::make('capital_trabajo')
                ->label('Capital de Trabajo')
                ->formatStateUsing(fn (float $state): string => '$' . number_format($state, 2)),
            ExportColumn::make('disponible')
                ->label('Disponible')
                ->formatStateUsing(fn (float $state): string => '$' . number_format($state, 2)),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your reporte disponibilidad export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

    public static function canQueue(): bool
    {
        return false;
    }

    // Método personalizado para la exportación directa
    public function export(): BinaryFileResponse
    {
        // Usamos el modelo directamente ya que no tenemos acceso a getEloquentQuery()
        $query = ReporteDisponibilidad::query();
        
        // Si hay filtros o selecciones, serán manejados por Filament automáticamente
        
        return Excel::download(
            new ReporteDisponibilidadExport($query),
            'reporte_disponibilidad_' . date('Y-m-d_H-i-s') . '.xlsx'
        );
    }
}
