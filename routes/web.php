<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HabitacionController;
use App\Http\Controllers\TipoHabitacionController;

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