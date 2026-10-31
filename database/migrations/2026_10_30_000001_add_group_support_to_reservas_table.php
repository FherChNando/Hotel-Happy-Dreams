<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->uuid('grupo_id')->nullable()->index();
        });

        Schema::create('reserva_huesped', function (Blueprint $table) {
            $table->foreignId('reserva_id')->constrained('reservas')->cascadeOnDelete();
            $table->foreignId('huesped_id')->constrained('huespedes');
            $table->primary(['reserva_id', 'huesped_id']);
        });

        DB::table('reservas')->select(['id', 'huesped_id'])->orderBy('id')->chunkById(500, function ($reservas) {
            DB::table('reserva_huesped')->insertOrIgnore(
                $reservas->map(fn ($reserva) => [
                    'reserva_id' => $reserva->id,
                    'huesped_id' => $reserva->huesped_id,
                ])->all()
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reserva_huesped');

        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn('grupo_id');
        });
    }
};
