<?php

namespace Database\Seeders;

use App\Models\Habitacion;
use App\Models\Rol;
use App\Models\Servicio;
use App\Models\TipoHabitacion;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        $admin = Rol::create(['nombre' => 'administrador']);
        $recep = Rol::create(['nombre' => 'recepcionista']);
        Rol::create(['nombre' => 'limpieza']);

        // Usuarios de prueba (CAMBIAR contraseñas antes del despliegue)
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@happydreams.test',
            'password' => 'Admin12345',
            'rol_id' => $admin->id,
        ]);
        User::create([
            'name' => 'Recepcionista',
            'email' => 'recepcion@happydreams.test',
            'password' => 'Recepcion12345',
            'rol_id' => $recep->id,
        ]);

        // Tipos de habitación (TARIFAS DE EJEMPLO: ajustar con las reales del hotel)
        $individual = TipoHabitacion::create(['nombre' => 'Individual', 'capacidad' => 1, 'tarifa_noche' => 150]);
        $doble      = TipoHabitacion::create(['nombre' => 'Doble',      'capacidad' => 2, 'tarifa_noche' => 250]);
        $familiar   = TipoHabitacion::create(['nombre' => 'Familiar',   'capacidad' => 5, 'tarifa_noche' => 450]);

        // 30 habitaciones: 10 individuales, 15 dobles, 5 familiares
        for ($i = 1; $i <= 10; $i++) {
            Habitacion::create(['tipo_habitacion_id' => $individual->id, 'numero' => (string) (100 + $i)]);
        }
        for ($i = 1; $i <= 15; $i++) {
            Habitacion::create(['tipo_habitacion_id' => $doble->id, 'numero' => (string) (200 + $i)]);
        }
        for ($i = 1; $i <= 5; $i++) {
            Habitacion::create(['tipo_habitacion_id' => $familiar->id, 'numero' => (string) (300 + $i)]);
        }

        // Servicios (PRECIOS DE EJEMPLO: ajustar)
        Servicio::create(['nombre' => 'Almuerzo',            'categoria' => 'almuerzo',     'precio' => 60]);
        Servicio::create(['nombre' => 'Cena',                'categoria' => 'cena',         'precio' => 70]);
        Servicio::create(['nombre' => 'Servicio a habitación','categoria' => 'room_service','precio' => 15]);
        Servicio::create(['nombre' => 'Lavandería (por libra)','categoria' => 'lavanderia', 'precio' => 12]);
    }
}
