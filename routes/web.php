<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HabitacionController;
use App\Http\Controllers\TipoHabitacionController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\HuespedController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReservaController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['register' => false]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Ver habitaciones y cambiar su estado: administrador, recepcionista y limpieza
Route::middleware(['auth', 'rol:administrador,recepcionista,limpieza'])->group(function () {
    Route::get('habitaciones', [HabitacionController::class, 'index'])->name('habitaciones.index');
    Route::patch('habitaciones/{habitacion}/estado', [HabitacionController::class, 'cambiarEstado'])->name('habitaciones.estado');
});

// Gestión completa: solo administrador
Route::middleware(['auth', 'rol:administrador'])->group(function () {
    Route::resource('habitaciones', HabitacionController::class)
        ->except(['index', 'show'])
        ->parameters(['habitaciones' => 'habitacion']);

    Route::resource('tipos-habitacion', TipoHabitacionController::class)
        ->except(['show'])
        ->parameters(['tipos-habitacion' => 'tipo']);
});

// Huéspedes: administrador y recepcionista
Route::middleware(['auth', 'rol:administrador,recepcionista'])->group(function () {
    Route::resource('huespedes', HuespedController::class)
        ->except(['show', 'destroy'])
        ->parameters(['huespedes' => 'huesped']);
});

// Solo administrador
Route::middleware(['auth', 'rol:administrador'])->group(function () {
    Route::delete('huespedes/{huesped}', [HuespedController::class, 'destroy'])->name('huespedes.destroy');

    Route::resource('empresas', EmpresaController::class)->except(['show']);

    Route::resource('usuarios', UserController::class)
        ->except(['show', 'destroy'])
        ->parameters(['usuarios' => 'usuario']);
    Route::patch('usuarios/{usuario}/activo', [UserController::class, 'cambiarActivo'])->name('usuarios.activo');
});

Route::middleware(['auth', 'rol:administrador,recepcionista'])->group(function () {
    Route::get('reservas/disponibilidad', [ReservaController::class, 'disponibilidad'])->name('reservas.disponibilidad');
    Route::patch('reservas/{reserva}/cancelar', [ReservaController::class, 'cancelar'])->name('reservas.cancelar');
    Route::resource('reservas', ReservaController::class)->except(['destroy']);
});