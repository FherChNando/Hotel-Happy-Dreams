<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresas';
    protected $fillable = ['nombre', 'nit', 'contacto', 'telefono', 'limite_credito', 'activo'];
    protected $casts = ['activo' => 'boolean'];

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'empresa_id');
    }

    /** Suma de saldos pendientes de reservas a credito */
    public function saldoPendiente(): float
    {
        return (float) $this->reservas()
            ->where('forma_pago', 'credito')
            ->where('estado', '!=', 'cancelada')
            ->with(['habitacion.tipo', 'consumos', 'pagos'])
            ->get()
            ->sum(fn ($r) => $r->saldo());
    }
}
