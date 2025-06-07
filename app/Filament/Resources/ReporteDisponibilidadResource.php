<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReporteDisponibilidadResource\Pages;
use App\Models\ReporteDisponibilidad;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use App\Filament\Exports\ReporteDisponibilidadExporter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReporteDisponibilidadExport;
use Illuminate\Support\Facades\URL;

class ReporteDisponibilidadResource extends Resource
{
    protected static ?string $model = ReporteDisponibilidad::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Disponibilidad Bancaria';
    protected static ?string $modelLabel = 'Reporte de Disponibilidad';
    protected static ?string $navigationGroup = 'Reportes Gerenciales';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // No se necesita schema para formulario ya que es solo lectura
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre_banco')
                    ->label('Banco')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('saldo')
                    ->label('Saldo Total')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->money('USD')
                    ->color(fn(float $state) => $state < 0 ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('capital_trabajo')
                    ->label('Capital de Trabajo')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->money('USD')
                    ->color('warning'),

                Tables\Columns\TextColumn::make('disponible')
                    ->label('Disponible')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->money('USD')
                    ->color('success')
                    ->weight('bold'),
            ])
            ->filters([
                // Filtros opcionales
            ])
            ->headerActions([
                Action::make('exportar_excel')
                    ->label('Exportar a Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(URL::route('exportar.disponibilidad'))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->label('Exportar Selección')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->exporter(ReporteDisponibilidadExporter::class)
                        ->fileName('reporte_seleccion_'.now()->format('Y-m-d').'.xlsx')
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageReporteDisponibilidads::route('/'),
        ];
    }
}
