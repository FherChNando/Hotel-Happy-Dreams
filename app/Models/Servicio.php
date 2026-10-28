<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    protected $table = 'servicios';
    protected $fillable = ['nombre', 'categoria', 'precio'];

    public function consumos()
    {
        return $this->hasMany(Consumo::class, 'servicio_id');
    }
}
