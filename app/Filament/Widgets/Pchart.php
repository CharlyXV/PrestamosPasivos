<?php

namespace App\Filament\Widgets;

use App\Models\Prestamo;
use App\Enums\PrestamoEstadoEnum;
use App\Helpers\CurrencyHelper;
use Filament\Widgets\ChartWidget;

class PrestamoMontoChart extends ChartWidget
{
    protected static ?string $heading = 'Composición de la Deuda';
    protected static ?int $sort = 3;
    
    protected function getData(): array
    {
        $prestamos = Prestamo::all();
        $dataByEstado = [
            'A' => 0,
            'L' => 0,
            'I' => 0,
        ];
        
        foreach ($prestamos as $prestamo) {
            $amountUSD = CurrencyHelper::convertToBaseCurrency($prestamo->monto_prestamo, $prestamo->moneda);
            $dataByEstado[$prestamo->estado] += $amountUSD;
        }
        
        $colors = [
            'A' => ['bg' => '#3b82f6', 'border' => '#2563eb'],
            'L' => ['bg' => '#10b981', 'border' => '#059669'],
            'I' => ['bg' => '#64748b', 'border' => '#475569'],
        ];
        
        $labels = [
            'A' => 'Vigentes',
            'L' => 'Liquidados',
            'I' => 'En Proceso'
        ];
        
        $values = [];
        $backgroundColors = [];
        $borderColors = [];
        
        foreach ($dataByEstado as $estado => $amount) {
            if ($amount > 0) {
                $values[] = $amount;
                $backgroundColors[] = $colors[$estado]['bg'];
                $borderColors[] = $colors[$estado]['border'];
            }
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Monto (USD)',
                    'data' => $values,
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => $borderColors,
                    'borderWidth' => 2,
                ],
            ],
            'labels' => array_values($labels),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                    'labels' => [
                        'font' => [
                            'size' => 14,
                        ],
                        'padding' => 20,
                    ],
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            const label = context.label || "";
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return ` ${label}: $${value.toLocaleString()} (${percentage}%)`;
                        }'
                    ]
                ],
            ],
            'cutout' => '60%',
            'maintainAspectRatio' => false,
        ];
    }
}
