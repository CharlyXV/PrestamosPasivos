<?php

namespace App\Filament\Resources\PrestamosResource\Pages\Components;

use Livewire\Component;
use App\Models\Planpago;
use App\Models\Prestamo;

class PlanPagosTable extends Component
{
    public ?Prestamo $prestamo = null;
    
    public function mount($record = null)
    {
        if ($record) {
            // Asegurarse de obtener un solo modelo Prestamo, no una colección
            $this->prestamo = is_a($record, Prestamo::class) 
                ? $record 
                : Prestamo::find($record);
        }
    }
    
    public function render()
    {
        return view('filament.resources.prestamos-resource.pages.components.plan-pagos-table', [
            'pagos' => $this->prestamo?->planPagos()->orderBy('numero_cuota')->get() ?? collect(),
            'moneda' => $this->prestamo?->moneda ?? 'USD',
            'hasPagos' => $this->prestamo?->planPagos()->exists() ?? false
        ]);
    }
}