<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consumo extends Model
{
    protected $table = 'consumos';
    protected $fillable = ['reserva_id', 'servicio_id', 'cantidad', 'precio_unitario', 'fecha'];
    protected $casts = ['fecha' => 'datetime'];

    public function reserva()  { return $this->belongsTo(Reserva::class, 'reserva_id'); }
    public function servicio() { return $this->belongsTo(Servicio::class, 'servicio_id'); }
}
