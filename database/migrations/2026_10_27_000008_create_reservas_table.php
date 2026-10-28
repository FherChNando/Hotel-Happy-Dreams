<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('huesped_id')->constrained('huespedes');
            $table->foreignId('habitacion_id')->nullable()->constrained('habitaciones');
            $table->foreignId('empresa_id')->nullable()->constrained('empresas');
            $table->foreignId('user_id')->constrained('users');
            $table->date('fecha_entrada');
            $table->date('fecha_salida');
            // reservada, check_in, check_out, cancelada
            $table->string('estado', 20)->default('reservada');
            // efectivo, transferencia, credito
            $table->string('forma_pago', 20);
            $table->timestamps();

            $table->index(['habitacion_id', 'fecha_entrada', 'fecha_salida']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
