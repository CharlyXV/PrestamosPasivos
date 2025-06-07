<?php

namespace App\Filament\Resources;

use App\Models\ReporteGasto;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use App\Filament\Resources\ReporteGastoResource\Pages\ManageReporteGastos;
use Filament\Forms\Components\DatePicker; // Importación añadida

class ReporteGastoResource extends Resource
{
    protected static ?string $model = ReporteGasto::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Reportes Gerenciales';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fecha_pago')
                    ->label('FECHA')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color('primary')
                    ->weight('medium')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('recibo')
                    ->label('RECIBO')
                    ->sortable()
                    ->searchable()
                    ->color('gray')
                    ->alignCenter(),
                    
                Tables\Columns\TextColumn::make('numero_prestamo')
                    ->label('PRÉSTAMO')
                    ->sortable()
                    ->searchable()
                    ->color('gray')
                    ->alignCenter(),
                    
                Tables\Columns\TextColumn::make('monto_principal')
                    ->label('PRINCIPAL')
                    ->numeric(decimalPlaces: 2)
                    ->money('USD')
                    ->color('primary')
                    ->weight('medium')
                    ->alignRight(),
                    
                Tables\Columns\TextColumn::make('monto_intereses')
                    ->label('INTERESES')
                    ->numeric(decimalPlaces: 2)
                    ->money('USD')
                    ->color('warning')
                    ->weight('medium')
                    ->alignRight(),
                    
                Tables\Columns\TextColumn::make('total')
                    ->label('TOTAL')
                    ->numeric(decimalPlaces: 2)
                    ->money('USD')
                    ->color('success')
                    ->weight('bold')
                    ->alignRight()
                    ->state(function ($record) {
                        return $record->monto_principal + $record->monto_intereses;
                    }),
            ])
            ->filters([
                Tables\Filters\Filter::make('fecha_pago')
                    ->form([
                        DatePicker::make('desde') // Usando la clase importada
                            ->label('Desde'),
                        DatePicker::make('hasta') // Usando la clase importada
                            ->label('Hasta'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['desde'],
                                fn($q) => $q->whereDate('fecha_pago', '>=', $data['desde']))
                            ->when($data['hasta'],
                                fn($q) => $q->whereDate('fecha_pago', '<=', $data['hasta']));
                    })
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Exportar Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->label('Exportar Selección')
                        ->icon('heroicon-o-arrow-down-tray')
                ]),
            ])
            ->defaultSort('fecha_pago', 'desc')
            ->striped()
            ->deferLoading();
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageReporteGastos::route('/'),
        ];
    }
}
