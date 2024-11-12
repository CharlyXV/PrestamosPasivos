<?php

namespace App\Filament\Resources;

use Log;
use Filament\Forms;
use Filament\Tables;
use App\Models\Empresa;
use App\Models\Prestamo;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PrestamosResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PrestamosResource\RelationManagers;
use Filament\Tables\Actions\Action; 
use Filament\Tables\Columns\TextColumn; 
use Maatwebsite\Excel\Facades\Excel; 
use App\Imports\PrestamosImport;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;


class PrestamosResource extends Resource
{
    protected static ?string $model = Prestamo::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Gestión Financiera';    
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // schema 01
                Section::make('Préstamo')
                ->description('Condiciones del Prestamo')
                ->schema([
                    

                    // schema 02
                  Forms\Components\Select::make('empresa_id')
                  ->relationship(name: 'empresa', 
                                 titleAttribute: 'nombre_empresa', 
                                 )
                  ->label('Empresa')                  
                  ->searchable()                 
                  ->preload() 
                  //->default('ATI')
                  ->required(),
                   //
                  Forms\Components\TextInput::make('numero_prestamo')
                  ->label('Numero')
                  ->required()                   
                  ->maxLength(255),
                  //
                  Forms\Components\Select::make('banco_id')
                  ->relationship(name: 'banco', 
                                 titleAttribute: 'nombre_banco', 
                                 )
                  ->label('Origen Fondos')
                  ->searchable()                 
                  ->preload() 
                  ->required(),
                    //
                    Forms\Components\Select::make('linea_id')
                    ->relationship(name: 'linea', 
                                   titleAttribute: 'nombre_linea', 
                                   )
                    ->label('Nombre Linea')
                    ->searchable()                 
                    ->preload() 
                    ->required(),
                    //
                    Forms\Components\Select::make('forma_pago')
                    ->options([                  
                    'V' => 'Vencimiento',
                    'A' => 'Adelantado',
                    ])
                    ->required(),
                    //
                    Forms\Components\Select::make('moneda')
                            ->options([
                            'USD' => 'USD',
                            'CRC' => 'CRC',
                            ])
                            ->required() ,

                    //
                    Forms\Components\DatePicker::make('formalizacion')
                    ->required(),
                    //->maxDate(now()),
                     //                   
                     Forms\Components\TextInput::make('monto_prestamo')
                     ->label('Monto Linea Prestamo')    
                     ->numeric()
                     ->maxValue(42949672.92),

                     Forms\Components\Select::make('estado')
                     ->options([
                     'A' => 'Activo',
                     'L' => 'Liquidado',
                     'I' => 'Incluido',
                     ])
                     ->required(),


                    //
                    //schema 02
                    ]) 
                    
                ->collapsed()
                ->columns(3),

                Section::make('Tasas')
                ->description('Condiciones del Tasas')
                ->schema([
                    // schema 03

                            Forms\Components\Select::make('tipotasa_id')
                            ->relationship(name: 'tipotasa', 
                                        titleAttribute: 'nombre_tipo_tasa', 
                                        )
                            ->label('Tipo Tasa')   
                            ->searchable()                 
                            ->preload() 
                            ->required(),

                            Forms\Components\Select::make('periodicidad_pago')
                            ->options([
                                '12' => 'Mensual',
                                '6' => 'Biestralmente',
                                '4' => 'Trimestralmente',    
                                '3' => 'Cuatrimestralmente',
                                '2' => 'Semestralmente',
                                '1' => 'Anualmente',
                             
                           
                            ])
                            ->label('Periodicidad de Pago')  
                            ->required(),
 
                            Forms\Components\TextInput::make('plazo_meses')
                            ->numeric()
                            ->required()
                            ->maxValue(999),
     
                            
                            Forms\Components\TextInput::make('tasa_interes')
                            ->label('Tasa Interes')     
                            ->numeric()
                            ->required()
                            ->maxValue(999999.9999),
    
                            Forms\Components\TextInput::make('tasa_spreed')
                            ->label('Spreed Interes')     
                            ->numeric()
                            ->required()
                            ->maxValue(9999.999),

                            //
                    //schema 03
                    ]) 
                    ->collapsed()
                    ->columns(5),

                    Section::make('Detalles')
                    ->description('Desembolsos / Saldos / Estados')
                    ->schema([
                        // schema 04
                       //
                            Forms\Components\DatePicker::make('vencimiento')
                            ->required(),
                            //->maxDate(now()),
                       //             

                       Forms\Components\DatePicker::make('proximo_pago')
                       ->required(),
                       //->maxDate(now()),

                       Forms\Components\TextInput::make('saldo_prestamo')
                       ->label('Saldo Linea')    
                       ->numeric()
                       ->maxValue(42949672.92),
  

                        //
                        Forms\Components\TextInput::make('cuenta_desembolso')
                        ->label('cuenta desembolso')
                        //->required()                   
                        ->maxLength(255),
                        //

                        //
                        Forms\Components\TextInput::make('observacion')
                        ->label('Observaciones')
                        //->required()                   
                        ->maxLength(255),

                          //schema 04
                    ]) 
                    ->collapsed()
                    ->columns(3),

                    
                        // schema 05
                        Section::make('Plan de pagos')
                        ->description('Importar plan de pagos')
                        ->schema([
                            Forms\Components\Repeater::make('planpago') // Debe coincidir con el nombre de la relación
                                ->relationship()                                
                                ->schema([
                                    Forms\Components\TextInput::make('numero_cuota')
                                        ->label('Número de Cuota')
                                        //->label(false)
                                        ->numeric()
                                      //  ->weight('thin')
                                        ->required(),
                                    Forms\Components\DatePicker::make('fecha_pago')
                                       ->label('Fecha de Pago')
                                    //  ->weight('thin')
                                        ->required(),
                                    Forms\Components\TextInput::make('monto_principal')
                                        ->label('Monto Principal')
                                      //  ->weight('thin')
                                        ->numeric()
                                        ->required(),
                                    Forms\Components\TextInput::make('monto_interes')
                                        ->label('Monto Interés')
                                        //->weight('thin')
                                        ->numeric()
                                        ->required(),
                                    Forms\Components\TextInput::make('monto_seguro')
                                        ->label('Monto Seguro')
                                        //->weight('thin')
                                        ->numeric()                                        
                                        ->required(),
                                    Forms\Components\TextInput::make('monto_otros')
                                        ->label('Otros Montos')
                                      //  ->weight('thin')
                                        ->numeric()                                        
                                        ->nullable(), // Puede ser opcional                        
                                ])                               
                                

                               // ->space(1)
                                ->columns(7)
                               // ->minItems(1) // Mínimo un pago
                               // ->maxItems(1) // Por ejemplo, máximo 12 pagos
                                
                            //    ->defaultItems(6) // Seis elementos por defecto   
                              //  ->grid(1)     
                                //->collapsed()
                                ,                        
                                //->createItemButtonLabel('Agregar Cuota') // Texto del botón para agregar cuotas
                                //->deleteItemButtonLabel('Eliminar Cuota'), // Texto del botón para eliminar cuotas
                        ])
                       
                        ->collapsed()
                       // ->columns(4),


                          //schema 05
                
            ]); //schema 01


            
            
    }
    public function mutateFormDataBeforeCreate(array $data): array
    {
        // Copia el valor de 'monto_seguro' al campo 'saldo_seguro'

        logger('Datos antes de crear:', $data);

        $data['saldo_seguro'] = $data['monto_seguro'];
        $data['saldo_interes'] = $data['monto_interes'];
        $data['saldo_principal'] = $data['monto_principal'];
        $data['saldo_otros'] = $data['monto_otros'];
 
        // Obtener el saldo_prestamo de la tabla de 'prestamos'
        $prestamo = Prestamo::find($data['id']); // Usa la llave primaria o un criterio para encontrar el registro adecuado
        $data['saldo_prestamo'] = $prestamo ? $prestamo->saldo_prestamo : 0; // Asigna el valor si existe, de lo contrario, 0

     

        return $data;
    }

    public function mutateFormDataBeforeSave(array $data): array
    {
     //  Log::info('Datos antes de crear:', $data);

       logger('Datos antes de crear:', $data);

        // Copia el valor al actualizar también        
        $data['saldo_seguro'] = $data['monto_seguro'];
        $data['saldo_interes'] = $data['monto_interes'];
        $data['saldo_principal'] = $data['monto_principal'];
        $data['saldo_otros'] = $data['monto_otros'];
 
          // Obtener el saldo_prestamo al actualizar también
          $prestamo = Prestamo::find($data['id']);
          $data['saldo_prestamo'] = $prestamo ? $prestamo->saldo_prestamo : 0;

          


        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);
     
        return $record;
    }

    public static function table(Table $table): Table
    {
        
        return $table
        ->columns([
            //
            Tables\Columns\TextColumn::make('numero_prestamo')
            ->searchable()   
            ->sortable(),
            Tables\Columns\TextColumn::make('formalizacion')
            ->searchable()   
            ->sortable() ,           
            Tables\Columns\TextColumn::make('monto_prestamo')
            ->searchable()   
            ->sortable(),            
            Tables\Columns\TextColumn::make('banco.nombre_banco')
            ->searchable()   
            ->sortable()
            ->toggleable(),                         
            
        ])
            ->filters([
                //
                Tables\Filters\SelectFilter::make('estado')
                ->options([
                  'A' => 'Activos',
                  'P' => 'Pendientes',
                  'L' => 'Liquidados',
                ]),

            ])

            
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),                
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

  

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrestamos::route('/'),
            'create' => Pages\CreatePrestamos::route('/create'),
            'edit' => Pages\EditPrestamos::route('/{record}/edit'),
        ];
    }
}
