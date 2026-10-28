<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pagos';
    protected $fillable = ['reserva_id', 'monto', 'metodo', 'referencia', 'fecha'];
    protected $casts = ['fecha' => 'datetime'];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }
}
