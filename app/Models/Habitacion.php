<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Habitacion extends Model
{
    protected $table = 'habitaciones';
    protected $fillable = ['tipo_habitacion_id', 'numero', 'estado'];

    public function tipo()
    {
        return $this->belongsTo(TipoHabitacion::class, 'tipo_habitacion_id');
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'habitacion_id');
    }

    /**
     * Habitaciones libres en el rango [entrada, salida).
     * Una reserva se traslapa si: entrada_existente < salida_nueva Y salida_existente > entrada_nueva
     */
    public function scopeDisponibles($query, $entrada, $salida)
    {
        return $query->where('estado', '!=', 'mantenimiento')
            ->whereDoesntHave('reservas', function ($q) use ($entrada, $salida) {
                $q->whereIn('estado', ['reservada', 'check_in'])
                  ->where('fecha_entrada', '<', $salida)
                  ->where('fecha_salida', '>', $entrada);
            });
    }
}
