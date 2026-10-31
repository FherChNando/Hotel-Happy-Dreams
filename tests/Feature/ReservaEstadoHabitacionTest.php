<?php

namespace Tests\Feature;

use App\Models\Habitacion;
use App\Models\Huesped;
use App\Models\Reserva;
use App\Models\Rol;
use App\Models\TipoHabitacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservaEstadoHabitacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_crear_reserva_marca_habitacion_como_ocupada_y_cancelarla_la_libera(): void
    {
        [$usuario, $huesped, $habitacion] = $this->prepararDatos();

        $this->actingAs($usuario)->post(route('reservas.store'), $this->datosReserva($huesped, $habitacion))
            ->assertRedirect();

        $reserva = Reserva::firstOrFail();
        $this->assertSame('ocupada', $habitacion->fresh()->estado);

        $this->actingAs($usuario)->patch(route('reservas.cancelar', $reserva))
            ->assertRedirect();

        $this->assertSame('cancelada', $reserva->fresh()->estado);
        $this->assertSame('disponible', $habitacion->fresh()->estado);
    }

    public function test_muestra_reserva_existente_sin_grupo(): void
    {
        [$usuario, $huesped, $habitacion] = $this->prepararDatos();
        $datos = $this->datosReserva($huesped, $habitacion);
        $reserva = Reserva::create([
            'huesped_id' => $huesped->id,
            'habitacion_id' => $habitacion->id,
            'user_id' => $usuario->id,
            'fecha_entrada' => $datos['fecha_entrada'],
            'fecha_salida' => $datos['fecha_salida'],
            'estado' => 'cancelada',
            'forma_pago' => 'efectivo',
        ]);

        $this->actingAs($usuario)->get(route('reservas.show', $reserva))
            ->assertOk()
            ->assertSee('Habitaciones y huéspedes')
            ->assertSee($huesped->nombre)
            ->assertSee($habitacion->numero);
    }

    public function test_cancelar_una_reserva_no_libera_habitacion_con_otra_reserva_activa(): void
    {
        [$usuario, $huesped, $habitacion] = $this->prepararDatos();

        $this->actingAs($usuario)->post(route('reservas.store'), $this->datosReserva($huesped, $habitacion, 3, 5))
            ->assertRedirect();
        $this->actingAs($usuario)->post(route('reservas.store'), $this->datosReserva($huesped, $habitacion, 6, 8))
            ->assertRedirect();

        $primeraReserva = Reserva::orderBy('id')->firstOrFail();
        $this->actingAs($usuario)->patch(route('reservas.cancelar', $primeraReserva))
            ->assertRedirect();

        $this->assertSame('ocupada', $habitacion->fresh()->estado);
    }

    public function test_cambiar_habitacion_actualiza_el_estado_de_ambas(): void
    {
        [$usuario, $huesped, $habitacionOriginal] = $this->prepararDatos();
        $habitacionNueva = Habitacion::create([
            'tipo_habitacion_id' => $habitacionOriginal->tipo_habitacion_id,
            'numero' => '102',
            'estado' => 'disponible',
        ]);
        $datos = $this->datosReserva($huesped, $habitacionOriginal);

        $this->actingAs($usuario)->post(route('reservas.store'), $datos)->assertRedirect();
        $reserva = Reserva::firstOrFail();

        $this->actingAs($usuario)->put(route('reservas.update', $reserva), [
            'habitaciones' => [[
                'id' => $habitacionNueva->id,
                'huespedes' => [$huesped->id],
            ]],
            'fecha_entrada' => $datos['fecha_entrada'],
            'fecha_salida' => $datos['fecha_salida'],
            'forma_pago' => 'efectivo',
        ])->assertRedirect();

        $this->assertSame('disponible', $habitacionOriginal->fresh()->estado);
        $this->assertSame('ocupada', $habitacionNueva->fresh()->estado);
    }

    public function test_crea_una_reservacion_grupal_con_varias_habitaciones_y_huespedes(): void
    {
        [$usuario, $huesped, $habitacion] = $this->prepararDatos();
        $this->actingAs($usuario)->get(route('reservas.create'))->assertOk();
        $segundoHuesped = Huesped::create([
            'nombre' => 'Segundo huésped',
            'documento' => 'DPI-TEST-2',
        ]);
        $habitacionNueva = Habitacion::create([
            'tipo_habitacion_id' => $habitacion->tipo_habitacion_id,
            'numero' => '102',
            'estado' => 'disponible',
        ]);
        $datos = $this->datosReserva($huesped, $habitacion);
        $datos['habitaciones'] = [
            ['id' => $habitacion->id, 'huespedes' => [$huesped->id, $segundoHuesped->id]],
            ['id' => $habitacionNueva->id, 'huespedes' => [$segundoHuesped->id]],
        ];

        $this->actingAs($usuario)->post(route('reservas.store'), $datos)
            ->assertSessionHasErrors('habitaciones.0.huespedes.1');

        $datos['habitaciones'][1]['huespedes'] = [];
        $this->actingAs($usuario)->post(route('reservas.store'), $datos)
            ->assertSessionHasErrors('habitaciones.1.huespedes');

        $datos['habitaciones'][1]['huespedes'] = [$segundoHuesped->id];
        $datos['habitaciones'] = [
            ['id' => $habitacion->id, 'huespedes' => [$huesped->id]],
            ['id' => $habitacionNueva->id, 'huespedes' => [$segundoHuesped->id]],
        ];

        $this->actingAs($usuario)->post(route('reservas.store'), $datos)
            ->assertRedirect();

        $reservas = Reserva::with('huespedes')->orderBy('id')->get();
        $this->assertCount(2, $reservas);
        $this->assertSame($reservas[0]->grupo_id, $reservas[1]->grupo_id);
        $this->assertSame([$huesped->id], $reservas[0]->huespedes->pluck('id')->all());
        $this->assertSame([$segundoHuesped->id], $reservas[1]->huespedes->pluck('id')->all());
        $this->assertSame('ocupada', $habitacion->fresh()->estado);
        $this->assertSame('ocupada', $habitacionNueva->fresh()->estado);
        $this->actingAs($usuario)->get(route('reservas.show', $reservas[0]))
            ->assertOk()
            ->assertSee('Habitaciones y huéspedes');
        $this->actingAs($usuario)->get(route('reservas.edit', $reservas[0]))
            ->assertOk()
            ->assertSee('Huéspedes asignados');
        $this->actingAs($usuario)->get(route('huespedes.index'))->assertOk();
        $this->actingAs($usuario)->delete(route('huespedes.destroy', $segundoHuesped))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('huespedes', ['id' => $segundoHuesped->id]);
    }

    public function test_cancelar_reservacion_grupal_libera_todas_sus_habitaciones(): void
    {
        [$usuario, $huesped, $habitacion] = $this->prepararDatos();
        $otraHabitacion = Habitacion::create([
            'tipo_habitacion_id' => $habitacion->tipo_habitacion_id,
            'numero' => '102',
            'estado' => 'disponible',
        ]);
        $datos = $this->datosReserva($huesped, $habitacion);
        $datos['habitaciones'][] = [
            'id' => $otraHabitacion->id,
            'huespedes' => [Huesped::create(['nombre' => 'Otro huésped', 'documento' => 'DPI-TEST-2'])->id],
        ];

        $this->actingAs($usuario)->post(route('reservas.store'), $datos)->assertRedirect();
        $primeraReserva = Reserva::firstOrFail();

        $this->actingAs($usuario)->patch(route('reservas.cancelar', $primeraReserva))->assertRedirect();

        $this->assertSame(2, Reserva::where('estado', 'cancelada')->count());
        $this->assertSame('disponible', $habitacion->fresh()->estado);
        $this->assertSame('disponible', $otraHabitacion->fresh()->estado);
    }

    public function test_editar_grupo_actualiza_fechas_pago_y_habitaciones_en_conjunto(): void
    {
        [$usuario, $huesped, $habitacion] = $this->prepararDatos();
        $segundoHuesped = Huesped::create(['nombre' => 'Segundo huésped', 'documento' => 'DPI-TEST-2']);
        $tercerHuesped = Huesped::create(['nombre' => 'Tercer huésped', 'documento' => 'DPI-TEST-3']);
        $habitacionRetirada = Habitacion::create([
            'tipo_habitacion_id' => $habitacion->tipo_habitacion_id,
            'numero' => '102',
            'estado' => 'disponible',
        ]);
        $habitacionNueva = Habitacion::create([
            'tipo_habitacion_id' => $habitacion->tipo_habitacion_id,
            'numero' => '103',
            'estado' => 'disponible',
        ]);
        $datos = $this->datosReserva($huesped, $habitacion);
        $datos['habitaciones'][] = [
            'id' => $habitacionRetirada->id,
            'huespedes' => [$segundoHuesped->id],
        ];

        $this->actingAs($usuario)->post(route('reservas.store'), $datos)->assertRedirect();
        $reserva = Reserva::firstOrFail();

        $this->actingAs($usuario)->put(route('reservas.update', $reserva), [
            'fecha_entrada' => now()->addDays(10)->toDateString(),
            'fecha_salida' => now()->addDays(12)->toDateString(),
            'forma_pago' => 'transferencia',
            'habitaciones' => [
                ['id' => $habitacion->id, 'huespedes' => [$huesped->id]],
                ['id' => $habitacionNueva->id, 'huespedes' => [$tercerHuesped->id]],
            ],
        ])->assertRedirect();

        $this->assertSame('cancelada', Reserva::where('habitacion_id', $habitacionRetirada->id)->firstOrFail()->estado);
        $reservasActivas = Reserva::where('grupo_id', $reserva->grupo_id)->where('estado', 'reservada')->get();
        $this->assertCount(2, $reservasActivas);
        $this->assertSame(['transferencia'], $reservasActivas->pluck('forma_pago')->unique()->all());
        $this->assertSame([now()->addDays(10)->toDateString()], $reservasActivas->pluck('fecha_entrada')->map(fn ($fecha) => $fecha->toDateString())->unique()->values()->all());
        $this->assertSame('ocupada', $habitacion->fresh()->estado);
        $this->assertSame('disponible', $habitacionRetirada->fresh()->estado);
        $this->assertSame('ocupada', $habitacionNueva->fresh()->estado);
    }

    public function test_rechaza_fechas_pasadas_o_salida_anterior_a_entrada(): void
    {
        [$usuario, $huesped, $habitacion] = $this->prepararDatos();
        $datos = $this->datosReserva($huesped, $habitacion);

        $datos['fecha_entrada'] = now()->subDay()->toDateString();
        $this->actingAs($usuario)->post(route('reservas.store'), $datos)
            ->assertSessionHasErrors('fecha_entrada');

        $datos['fecha_entrada'] = now()->addDays(5)->toDateString();
        $datos['fecha_salida'] = now()->addDays(4)->toDateString();
        $this->actingAs($usuario)->post(route('reservas.store'), $datos)
            ->assertSessionHasErrors('fecha_salida');
    }

    public function test_rechaza_reservar_mas_huespedes_que_la_capacidad_de_la_habitacion(): void
    {
        [$usuario, $huesped, $habitacion] = $this->prepararDatos();
        $segundoHuesped = Huesped::create(['nombre' => 'Segundo huésped', 'documento' => 'DPI-TEST-2']);
        $tercerHuesped = Huesped::create(['nombre' => 'Tercer huésped', 'documento' => 'DPI-TEST-3']);
        $datos = $this->datosReserva($huesped, $habitacion);
        $datos['habitaciones'][0]['huespedes'] = [$huesped->id, $segundoHuesped->id, $tercerHuesped->id];

        $this->actingAs($usuario)->post(route('reservas.store'), $datos)
            ->assertSessionHasErrors('habitaciones');

        $this->assertDatabaseCount('reservas', 0);
    }

    private function prepararDatos(): array
    {
        $rol = Rol::create(['nombre' => 'administrador']);
        $usuario = User::create([
            'name' => 'Administración',
            'email' => 'admin@example.test',
            'password' => 'password',
            'rol_id' => $rol->id,
            'activo' => true,
        ]);
        $tipo = TipoHabitacion::create([
            'nombre' => 'Doble',
            'capacidad' => 2,
            'tarifa_noche' => 250,
        ]);
        $habitacion = Habitacion::create([
            'tipo_habitacion_id' => $tipo->id,
            'numero' => '101',
            'estado' => 'disponible',
        ]);
        $huesped = Huesped::create([
            'nombre' => 'Huésped de prueba',
            'documento' => 'DPI-TEST-1',
        ]);

        return [$usuario, $huesped, $habitacion];
    }

    private function datosReserva(Huesped $huesped, Habitacion $habitacion, int $entradaEnDias = 3, int $salidaEnDias = 5): array
    {
        return [
            'habitaciones' => [[
                'id' => $habitacion->id,
                'huespedes' => [$huesped->id],
            ]],
            'fecha_entrada' => now()->addDays($entradaEnDias)->toDateString(),
            'fecha_salida' => now()->addDays($salidaEnDias)->toDateString(),
            'forma_pago' => 'efectivo',
        ];
    }
}
