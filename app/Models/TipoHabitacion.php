<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoHabitacion extends Model
{
    protected $table = 'tipos_habitacion';
    protected $fillable = ['nombre', 'capacidad', 'tarifa_noche'];

    public function habitaciones()
    {
        return $this->hasMany(Habitacion::class, 'tipo_habitacion_id');
    }
}
