<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Pruebas de solo lectura sobre los usuarios sembrados en MongoDB
 * (UsuariosPasswordSeeder, clave de desarrollo password123).
 */
final class AutenticacionTest extends TestCase
{
    private function usuario(string $correo): Usuario
    {
        return Usuario::query()->where('correo', $correo)->firstOrFail();
    }

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear(hash('sha256', 'carlos.ramirez@pepeps.co|127.0.0.1'));
    }

    public function test_invitado_es_redirigido_al_login(): void
    {
        $this->get('/panel')->assertRedirect(route('login'));
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_login_correcto_redirige_al_panel_del_rol(): void
    {
        $this->post('/ingresar', ['correo' => 'carlos.ramirez@pepeps.co', 'contrasena' => 'password123'])
            ->assertRedirect(route('panel'));

        $this->assertAuthenticated();
        $this->get('/panel')->assertRedirect(route('admin.panel'));
    }

    public function test_login_incorrecto_no_autentica_ni_revela_causa(): void
    {
        $this->from('/ingresar')
            ->post('/ingresar', ['correo' => 'carlos.ramirez@pepeps.co', 'contrasena' => 'incorrecta1'])
            ->assertRedirect('/ingresar')
            ->assertSessionHasErrors('correo');

        $this->assertGuest();
    }

    public function test_bloqueo_tras_demasiados_intentos(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/ingresar', ['correo' => 'carlos.ramirez@pepeps.co', 'contrasena' => 'incorrecta1']);
        }

        $this->post('/ingresar', ['correo' => 'carlos.ramirez@pepeps.co', 'contrasena' => 'password123'])
            ->assertSessionHasErrors('correo');

        $this->assertGuest();
    }

    public function test_consumidor_no_accede_a_paneles_ajenos(): void
    {
        $consumidor = $this->usuario('andres.mantilla@gmail.com');

        $this->actingAs($consumidor)->get('/consumidor')->assertOk();
        $this->actingAs($consumidor)->get('/admin')->assertForbidden();
        $this->actingAs($consumidor)->get('/productor')->assertForbidden();
    }

    public function test_productor_ve_solo_su_panel_y_sus_fincas(): void
    {
        $productor = $this->usuario('jose.gomez@finca.co');

        $this->actingAs($productor)->get('/productor')
            ->assertOk()
            ->assertSee('Finca Los Naranjos')
            ->assertSee('Finca El Mirador')
            ->assertDontSee('Finca La Esperanza');
        $this->actingAs($productor)->get('/admin')->assertForbidden();
    }

    public function test_administrador_tiene_acceso_total(): void
    {
        $admin = $this->usuario('carlos.ramirez@pepeps.co');

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/consumidor')->assertOk();
    }

    public function test_no_se_expone_el_hash_en_las_vistas(): void
    {
        $admin = $this->usuario('carlos.ramirez@pepeps.co');
        $hash = $admin->getAttributes()['contrasena_hash'];

        $this->actingAs($admin)->get('/admin')->assertDontSee($hash, false);
        $this->assertArrayNotHasKey('contrasena_hash', $admin->toArray());
    }

    public function test_encabezados_de_seguridad(): void
    {
        $this->get('/ingresar')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Content-Security-Policy');
    }

    public function test_registro_valida_entradas(): void
    {
        $this->post('/registro', [
            'nombre' => '<script>', 'apellido' => '', 'correo' => 'no-es-correo',
            'telefono' => '123', 'contrasena' => 'corta', 'contrasena_confirmation' => 'otra',
            'rol' => 'Administrador',
        ])->assertSessionHasErrors(['nombre', 'apellido', 'correo', 'telefono', 'contrasena']);
    }

    public function test_registro_rechaza_correo_existente(): void
    {
        $this->post('/registro', [
            'nombre' => 'Ana', 'apellido' => 'Perez', 'correo' => 'carlos.ramirez@pepeps.co',
            'telefono' => '3001234567', 'contrasena' => 'clave1234', 'contrasena_confirmation' => 'clave1234',
        ])->assertSessionHasErrors('correo');
    }
}

