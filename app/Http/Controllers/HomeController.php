<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\Empresa;
use App\Models\Habitacion;
use App\Models\Huesped;
use App\Models\Reserva;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $porEstado = Habitacion::selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $hoy = now()->toDateString();

        $proximasEntradas = Reserva::with(['huesped', 'habitacion.tipo'])
            ->where('estado', 'reservada')
            ->whereDate('fecha_entrada', '>=', $hoy)
            ->orderBy('fecha_entrada')
            ->limit(5)
            ->get();

        $ocupadasHoy = Reserva::with(['huesped', 'habitacion.tipo'])
            ->where('estado', 'check_in')
            ->orderBy('fecha_salida')
            ->limit(5)
            ->get();

        $bitacora = auth()->user()->tieneRol('administrador')
            ? Bitacora::with('user')->orderByDesc('fecha')->limit(6)->get()
            : collect();

        return view('home', [
            'disponibles' => $porEstado['disponible'] ?? 0,
            'ocupadas' => $porEstado['ocupada'] ?? 0,
            'limpieza' => $porEstado['limpieza'] ?? 0,
            'mantenimiento' => $porEstado['mantenimiento'] ?? 0,
            'entradasHoy' => Reserva::whereDate('fecha_entrada', $hoy)->whereIn('estado', ['reservada', 'check_in'])->count(),
            'salidasHoy' => Reserva::whereDate('fecha_salida', $hoy)->whereIn('estado', ['check_in', 'check_out'])->count(),
            'totalHuespedes' => Huesped::count(),
            'empresasActivas' => Empresa::where('activo', true)->count(),
            'proximasEntradas' => $proximasEntradas,
            'ocupadasHoy' => $ocupadasHoy,
            'bitacora' => $bitacora,
        ]);
    }
}
