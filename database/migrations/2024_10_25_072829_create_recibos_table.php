<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('recibos', function (Blueprint $table) {
            $table->id(); // Primary Key: ID
            $table->string('empresa_id');
            $table->string('tipo_recibo'); // CUOTA, ANTICIPADO, LIQUIDACION
            $table->string('detalle');
            $table->string('estado'); // I = INCLUIDO, C = CONTABILIZADO, A = ANULADO
            $table->foreignId('cuentas_id')->constrained('cuentas')->cascadeOnDelete();            
            $table->string('moneda_prestamo');
            $table->string('numero_prestamo');
            $table->decimal('monto_recibo');
            $table->date('fecha_pago');
            $table->string('razon_anulacion')->nullable();
            $table->date('fecha_anulacion')->nullable();
            $table->decimal('saldo_anterior');
            $table->decimal('saldo_actual');
            $table->timestamps();
    
            
            
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recibos');
    }
};
