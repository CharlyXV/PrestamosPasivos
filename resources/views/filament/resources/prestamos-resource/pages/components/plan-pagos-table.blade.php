<!-- resources/views/vendor/filament/resources/prestamos-resource/pages/components/plan-pagos-table.blade.php -->
<div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="fi-section-content flex flex-col gap-y-6 p-6">
        @if($hasPagos)
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200 dark:divide-white/10">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="px-4 py-3.5 text-left text-sm font-semibold text-gray-950 dark:text-white">Cuota</th>
                        <th class="px-4 py-3.5 text-left text-sm font-semibold text-gray-950 dark:text-white">Fecha Pago</th>
                        <th class="px-4 py-3.5 text-left text-sm font-semibold text-gray-950 dark:text-white">Principal</th>
                        <th class="px-4 py-3.5 text-left text-sm font-semibold text-gray-950 dark:text-white">Interés</th>
                        <th class="px-4 py-3.5 text-left text-sm font-semibold text-gray-950 dark:text-white">Total</th>
                        <th class="px-4 py-3.5 text-left text-sm font-semibold text-gray-950 dark:text-white">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                    @foreach($pagos as $pago)
                    <tr>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-950 dark:text-white">
                            {{ $pago->numero_cuota }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-950 dark:text-white">
                            @if($pago->fecha_pago)
                            {{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}
                            @else
                            N/A
                            @endif
                        </td>
                        @php
                        $formatMoney = function($amount, $currency) {
                        return match($currency) {
                        'CRC' => '₡' . number_format($amount, 2),
                        'USD' => '$' . number_format($amount, 2),
                        'EUR' => '€' . number_format($amount, 2),
                        default => number_format($amount, 2) . ' ' . $currency
                        };
                        };
                        @endphp

                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-950 dark:text-white">
                            {{ $formatMoney($pago->monto_principal, $moneda) }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-950 dark:text-white">
                            {{ $formatMoney($pago->monto_interes, $moneda) }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-primary-600 dark:text-primary-400">
                            {{ $formatMoney($pago->monto_principal + $pago->monto_interes, $moneda) }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm">
                            @php
                            $estado = $pago->plp_estados ?? 'pendiente';
                            $color = match($estado) {
                            'completado' => 'success',
                            'pendiente' => 'warning',
                            'vencido' => 'danger',
                            default => 'gray'
                            };
                            $texto = match($estado) {
                            'completado' => 'Pagado',
                            'pendiente' => 'Pendiente',
                            'vencido' => 'Vencido',
                            default => $estado
                            };
                            @endphp
                            <span class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium px-2 py-1 fi-color-{{ $color }}">
                                {{ $texto }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="flex flex-col items-center justify-center space-y-4 py-8 text-center">
            <x-heroicon-o-document-text class="h-12 w-12 text-gray-400" />
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">No hay plan de pagos generado</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                El plan de pagos debe generarse manualmente desde el panel de Plan de Pagos
            </p>
            <a href="{{ route('filament.admin.resources.pagos.index', ['prestamo_id' => $prestamo->id]) }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-primary-600 bg-primary-600 px-3 py-2 text-sm font-medium text-white shadow-sm transition-all hover:border-primary-700 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:border-primary-500 dark:bg-primary-500 dark:hover:border-primary-400 dark:hover:bg-primary-400 dark:focus:ring-primary-400 dark:focus:ring-offset-gray-900">
                <x-heroicon-o-arrow-right class="h-4 w-4" />
                Generar Plan de Pagos
            </a>
        </div>
        @endif
    </div>
</div>