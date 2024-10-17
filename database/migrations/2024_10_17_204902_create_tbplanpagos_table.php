<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tbplanpagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestamo_id')->constrained('tbprestamos')->cascadeOnDelete();
            $table->unsignedInteger('numero_cuota')->nullable();
            $table->date('fecha_pago');
            $table->decimal('monto_principal')->nullable();
            $table->decimal('monto_interes')->nullable();
            $table->decimal('monto_seguro')->nullable();
            $table->decimal('monto_otros')->nullable();
            $table->decimal('saldo_prestamo')->nullable();
            $table->decimal('tasa_interes')->nullable();
            $table->decimal('saldo_principal')->nullable();
            $table->decimal('saldo_interes')->nullable();
            $table->decimal('saldo_seguro')->nullable();
            $table->decimal('saldo_otros')->nullable();
            $table->string('observaciones'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbplanpagos');
    }
};
