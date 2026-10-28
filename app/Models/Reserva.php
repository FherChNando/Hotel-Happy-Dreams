<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $table = 'reservas';

    protected $fillable = [
        'huesped_id', 'habitacion_id', 'empresa_id', 'user_id',
        'fecha_entrada', 'fecha_salida', 'estado', 'forma_pago',
    ];

    protected $casts = [
        'fecha_entrada' => 'date',
        'fecha_salida' => 'date',
    ];

    public function huesped()   { return $this->belongsTo(Huesped::class, 'huesped_id'); }
    public function habitacion(){ return $this->belongsTo(Habitacion::class, 'habitacion_id'); }
    public function empresa()   { return $this->belongsTo(Empresa::class, 'empresa_id'); }
    public function user()      { return $this->belongsTo(User::class, 'user_id'); }
    public function consumos()  { return $this->hasMany(Consumo::class, 'reserva_id'); }
    public function pagos()     { return $this->hasMany(Pago::class, 'reserva_id'); }

    public function noches(): int
    {
        return max(1, (int) $this->fecha_entrada->diffInDays($this->fecha_salida));
    }

    /** Noches x tarifa (el desayuno va incluido) */
    public function totalHospedaje(): float
    {
        $tarifa = $this->habitacion?->tipo?->tarifa_noche ?? 0;
        return $this->noches() * (float) $tarifa;
    }

    public function totalConsumos(): float
    {
        return (float) $this->consumos->sum(fn ($c) => $c->cantidad * $c->precio_unitario);
    }

    public function total(): float
    {
        return $this->totalHospedaje() + $this->totalConsumos();
    }

    public function totalPagado(): float
    {
        return (float) $this->pagos->sum('monto');
    }

    public function saldo(): float
    {
        return $this->total() - $this->totalPagado();
    }
}
