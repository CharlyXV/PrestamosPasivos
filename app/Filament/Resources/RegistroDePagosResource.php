<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegistroDePagosResource\Pages;
use App\Filament\Resources\RegistroDePagosResource\RelationManagers;
use App\Models\Recibo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RegistroDePagosResource extends Resource
{
    protected static ?string $model = Recibo::class; // Ligado al modelo Recibo

    protected static ?string $navigationLabel = 'Registro de Pagos';
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar'; // Ícono para el menú
    protected static ?string $pluralLabel = 'Registro de Pagos';
    protected static ?string $slug = 'registro-de-pagos'; // Ruta en el menú
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Gestión Financiera'; // Agrupar en el menú

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('empresa_id')
                    ->required(),
                Forms\Components\TextInput::make('tipo_recibo')
                    ->required(),
                Forms\Components\TextInput::make('detalle')
                    ->required(),
                Forms\Components\TextInput::make('estado')
                    ->required(),
     
 
                    Forms\Components\Select::make('cuentas_id')
                    ->relationship('cuentas', 'numero_cuenta')
                    ->searchable()
                    ->preload()                 
                    ->createOptionForm([
                         Forms\Components\TextInput::make('codigo_banco')
                               ->required()
                               ->maxLength(255),
                
                    ])     
                    ->required(),
                    
              
                    /*
                Forms\Components\TextInput::make('moneda_prestamo')
                    ->required(),
                Forms\Components\TextInput::make('monto_recibo')
                    ->required(),
                Forms\Components\DatePicker::make('fecha_pago')
                    ->required(),
                Forms\Components\Repeater::make('detalles') // Relación con DetalleRecibo
                    ->relationship('detalles')
                    ->schema([
                        Forms\Components\TextInput::make('numero_cuota')
                            ->required(),
                        Forms\Components\TextInput::make('monto_principal')
                            ->required(),
                        Forms\Components\TextInput::make('monto_intereses')
                            ->required(),
                        Forms\Components\TextInput::make('monto_seguro')
                            ->required(),
                        Forms\Components\TextInput::make('monto_otros')
                            ->required(),
                        Forms\Components\TextInput::make('monto_cuota')
                            ->required(),
                    ]),
                    */
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('empresa_id'),
                Tables\Columns\TextColumn::make('tipo_recibo'),
                Tables\Columns\TextColumn::make('detalle'),
                Tables\Columns\TextColumn::make('estado'),
                Tables\Columns\TextColumn::make('cuentas.nombre_cuenta') // Relación a la cuenta bancaria
                    ->label('Cuenta Bancaria'),
                Tables\Columns\TextColumn::make('monto_recibo'),
                Tables\Columns\TextColumn::make('fecha_pago')->date(),
            ])
            ->filters([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Puedes definir relaciones aquí si deseas mostrar más detalles
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegistroDePagos::route('/'),
            'create' => Pages\CreateRegistroDePagos::route('/create'),
            'edit' => Pages\EditRegistroDePagos::route('/{record}/edit'),
        ];
    }
}
