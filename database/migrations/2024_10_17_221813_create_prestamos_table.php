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
        Schema::create('prestamos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('tbempresas')->cascadeOnDelete();
            $table->string('numero_prestamo');
            $table->foreignId('banco_id')->constrained('tbbancos')->cascadeOnDelete();
            $table->foreignId('linea_id')->constrained('tblineas')->cascadeOnDelete();
            $table->string('forma_pago');
            $table->string('moneda');
            $table->date('formalizacion');
            $table->date('vencimiento');
            $table->date('proximo_pago');
            $table->decimal('monto_prestamo')->nullable();
            $table->decimal('saldo_prestamo')->nullable();
            $table->unsignedInteger('plazo_meses')->nullable();
            $table->foreignId('tipo_tasa_id')->constrained('tbtipotasas')->cascadeOnDelete();
            $table->decimal('tasa_interes')->nullable();
            $table->decimal('tasa_spreed')->nullable();
            $table->string('cuenta_desembolso');
            $table->string('estado');
            $table->unsignedInteger('periodicidad_pago')->nullable();
            $table->string('observacion');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestamos');
    }
};
