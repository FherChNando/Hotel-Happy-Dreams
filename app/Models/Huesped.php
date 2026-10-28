<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Huesped extends Model
{
    protected $table = 'huespedes';
    protected $fillable = ['nombre', 'documento', 'telefono', 'email'];

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'huesped_id');
    }
}
