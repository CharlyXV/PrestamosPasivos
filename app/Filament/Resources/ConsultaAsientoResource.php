<?php

namespace App\Filament\Resources;

use App\Models\ConsultaAsiento;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use App\Filament\Resources\ConsultaAsientoResource\Pages\ManageConsultaAsientos;
class ConsultaAsientoResource extends Resource
{
    protected static ?string $model = ConsultaAsiento::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Consulta de Asientos';
    protected static ?string $navigationGroup = 'Gestión Contable';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('FECHA_ASIENTO')
                    ->label('FECHA')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color('primary')
                    ->weight('medium')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('NUMERO_DOCUMENTO')
                    ->label('DOCUMENTO')
                    ->sortable()
                    ->searchable()
                    ->color('gray')
                    ->alignCenter(),
                    
                Tables\Columns\TextColumn::make('NUMERO_PRESTAMO')
                    ->label('PRÉSTAMO')
                    ->sortable()
                    ->searchable()
                    ->color('gray')
                    ->alignCenter(),
                    
                Tables\Columns\TextColumn::make('MONTO_DEBE')
                    ->label('DÉBITO')
                    ->numeric(decimalPlaces: 2)
                    ->money('USD')
                    ->color('danger')
                    ->weight('medium')
                    ->alignRight(),
                    
                Tables\Columns\TextColumn::make('MONTO_HABER')
                    ->label('CRÉDITO')
                    ->numeric(decimalPlaces: 2)
                    ->money('USD')
                    ->color('success')
                    ->weight('medium')
                    ->alignRight(),
                    
                Tables\Columns\TextColumn::make('DETALLE')
                    ->label('DESCRIPCIÓN')
                    ->searchable()
                    ->wrap()
                    ->color('gray')
                    ->weight('normal')
                    ->size('sm'),
                    
                Tables\Columns\TextColumn::make('MONEDA')
                    ->label('')
                    ->badge()
                    ->color(fn ($state) => $state === 'USD' ? 'success' : 'warning')
                    ->formatStateUsing(fn ($state) => strtoupper($state)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('MONEDA')
                    ->options([
                        'USD' => 'Dólares',
                        'PEN' => 'Soles',
                    ])
                    ->label('Moneda'),
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
            ->defaultSort('FECHA_ASIENTO', 'desc')
            ->striped()
            ->deferLoading();
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageConsultaAsientos::route('/'),
        ];
    }
}