<?php

namespace App\Filament\Widgets;

use App\Models\Prestamo;
use App\Enums\PrestamoEstadoEnum;
use App\Helpers\CurrencyHelper;
use Filament\Widgets\ChartWidget;

class PrestamoChart extends ChartWidget
{
    protected static ?string $heading = 'Distribución de Financiamientos';
    protected static ?int $sort = 2;
    
    protected function getData(): array
    {
        $data = Prestamo::select('estado')
            ->selectRaw('count(*) as count')
            ->selectRaw('sum(monto_prestamo) as amount')
            ->groupBy('estado')
            ->get();
            
        $colors = [
            'A' => '#3b82f6', // Azul corporativo
            'L' => '#10b981', // Verde éxito
            'I' => '#64748b', // Gris neutro
        ];
        
        $labels = [
            'A' => 'Vigentes',
            'L' => 'Liquidados',
            'I' => 'En Proceso'
        ];
        
        $values = [];
        $backgroundColors = [];
        
        foreach (PrestamoEstadoEnum::cases() as $case) {
            $record = $data->firstWhere('estado', $case->value);
            $values[] = $record ? $record->count : 0;
            $backgroundColors[] = $colors[$case->value] ?? '#94a3b8';
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Financiamientos',
                    'data' => $values,
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                    'barPercentage' => 0.75,
                ],
            ],
            'labels' => array_values($labels),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                    'labels' => [
                        'font' => [
                            'size' => 14,
                            'family' => "'Inter', sans-serif",
                        ],
                        'padding' => 20,
                    ],
                ],
                'tooltip' => [
                    'enabled' => true,
                    'backgroundColor' => '#1e293b',
                    'titleFont' => [
                        'size' => 16,
                    ],
                    'bodyFont' => [
                        'size' => 14,
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'color' => '#e2e8f0',
                    ],
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
