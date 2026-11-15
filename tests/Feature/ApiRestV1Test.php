<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Rol;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Productor;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiRestV1Test extends TestCase
{
    private Usuario $consumidor;
    private Usuario $consumidorAjeno;
    private Usuario $administrador;
    private Producto $producto;
    private Productor $finca;

    protected function setUp(): void
    {
        parent::setUp();

        $this->consumidor = Usuario::firstOrNew(['correo' => 'test.api.consumidor@pepeps.co']);
        $this->consumidor->fill([
            'nombre' => 'Consumidor',
            'apellido' => 'API',
            'telefono' => '3111111111',
            'rol' => Rol::Consumidor,
            'estado' => 'activo',
            'direcciones' => [[
                'direccion' => 'Calle 10 #20-30',
                'municipio' => 'Bucaramanga',
                'barrio' => 'Cabecera',
                'referencia' => 'Apto 201',
            ]],
        ]);
        $this->consumidor->contrasena_hash = Hash::make('password123');
        $this->consumidor->save();

        $this->consumidorAjeno = Usuario::firstOrNew(['correo' => 'test.api.ajeno@pepeps.co']);
        $this->consumidorAjeno->fill([
            'nombre' => 'Ajeno',
            'apellido' => 'API',
            'telefono' => '3222222222',
            'rol' => Rol::Consumidor,
            'estado' => 'activo',
            'direcciones' => [],
        ]);
        $this->consumidorAjeno->contrasena_hash = Hash::make('password123');
        $this->consumidorAjeno->save();

        $this->administrador = Usuario::firstOrNew(['correo' => 'test.api.admin@pepeps.co']);
        $this->administrador->fill([
            'nombre' => 'Admin',
            'apellido' => 'API',
            'telefono' => '3333333333',
            'rol' => Rol::Administrador,
            'estado' => 'activo',
            'direcciones' => [],
        ]);
        $this->administrador->contrasena_hash = Hash::make('password123');
        $this->administrador->save();

        $this->finca = Productor::first() ?? Productor::create([
            'usuario_id' => $this->administrador->getKey(),
            'nombre_finca_asociacion' => 'Finca Los Naranjos',
            'municipio' => 'Floridablanca',
            'direccion_rural' => 'Vereda La Esperanza',
            'descripcion' => 'Finca tradicional santandereana.',
            'estado_verificacion' => 'verificado',
            'fecha_registro' => now(),
        ]);

        $this->producto = Producto::where('estado', 'activo')->first() ?? Producto::create([
            'productor_id' => $this->finca->getKey(),
            'categoria_id' => Categoria::first()?->getKey(),
            'nombre' => 'Aguacate Hass de Prueba',
            'descripcion' => 'Aguacate fresco para pruebas API.',
            'precio' => new \MongoDB\BSON\Decimal128('3500'),
            'unidad_medida' => 'kg',
            'estado' => 'activo',
            'imagenes' => ['aguacate.png'],
            'inventario' => [
                'presentacion' => 'Malla x 1 kg',
                'cantidad_disponible' => 50,
                'estado' => 'disponible',
                'fecha_actualizacion' => now(),
            ],
            'fecha_creacion' => now(),
        ]);
    }

    public function test_login_api_con_credenciales_validas_retorna_200_y_token(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'correo' => 'test.api.consumidor@pepeps.co',
            'contrasena' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'token',
                    'token_type',
                    'usuario' => ['id', 'nombre_completo', 'correo', 'rol'],
                ],
            ]);
    }

    public function test_login_api_con_credenciales_invalidas_retorna_401(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'correo' => 'test.api.consumidor@pepeps.co',
            'contrasena' => 'clave_incorrecta',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('status', 'error');
    }

    public function test_registro_api_exitoso_retorna_201_y_token(): void
    {
        $correoUnico = 'nuevo.' . uniqid() . '@pepeps.co';

        $response = $this->postJson('/api/v1/auth/registro', [
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'correo' => $correoUnico,
            'telefono' => '3159998877',
            'contrasena' => 'segura1234',
            'contrasena_confirmation' => 'segura1234',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['token', 'token_type', 'usuario'],
            ]);
    }

    public function test_registro_api_con_datos_invalidos_retorna_422(): void
    {
        $response = $this->postJson('/api/v1/auth/registro', [
            'nombre' => '12345',
            'correo' => 'correo-invalido',
            'contrasena' => 'corta',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonStructure(['errors']);
    }

    public function test_perfil_requiere_autenticacion_y_retorna_401_sin_token(): void
    {
        $this->getJson('/api/v1/auth/perfil')
            ->assertStatus(401);
    }

    public function test_perfil_con_token_valido_retorna_200(): void
    {
        $token = $this->consumidor->createToken('test_perfil')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/perfil');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.correo', 'test.api.consumidor@pepeps.co');
    }

    public function test_logout_revoca_token_y_retorna_200(): void
    {
        $token = $this->consumidor->createToken('test_logout')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    public function test_catalogo_productos_publico_retorna_200_con_estructura(): void
    {
        $response = $this->getJson('/api/v1/productos');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => ['id', 'nombre', 'precio', 'precio_formateado', 'inventario', 'productor', 'categoria'],
                ],
                'meta' => ['total', 'pagina_actual', 'por_pagina'],
            ]);
    }

    public function test_detalle_producto_existente_retorna_200(): void
    {
        $response = $this->getJson('/api/v1/productos/' . (string) $this->producto->getKey());

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', (string) $this->producto->getKey())
            ->assertJsonPath('data.nombre', $this->producto->nombre);
    }

    public function test_detalle_producto_inexistente_retorna_404(): void
    {
        $response = $this->getJson('/api/v1/productos/6aed00000000000000000000');

        $response->assertStatus(404)
            ->assertJsonPath('status', 'error');
    }

    public function test_crear_pedido_sin_token_retorna_401(): void
    {
        $response = $this->postJson('/api/v1/pedidos', [
            'direccion' => 'Calle 45 #20-30',
            'municipio' => 'Bucaramanga',
            'metodo_pago' => 'efectivo',
            'items' => [['producto_id' => (string) $this->producto->getKey(), 'cantidad' => 2]],
        ]);

        $response->assertStatus(401);
    }

    public function test_crear_pedido_con_token_valido_retorna_201_y_descuenta_inventario(): void
    {
        $token = $this->consumidor->createToken('test_pedido')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/pedidos', [
                'direccion' => 'Carrera 27 #34-10',
                'municipio' => 'Bucaramanga',
                'barrio' => 'Antonia Santos',
                'referencia' => 'Apto 402',
                'metodo_pago' => 'efectivo',
                'items' => [
                    ['producto_id' => (string) $this->producto->getKey(), 'cantidad' => 2],
                ],
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'numero_pedido', 'total', 'estado', 'pago'],
            ]);
    }

    public function test_detalle_pedido_ajeno_retorna_403_forbidden(): void
    {
        // 1. Consumidor crea un pedido
        $token = $this->consumidor->createToken('test_pedido_propio')->plainTextToken;
        $creacion = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/pedidos', [
                'direccion' => 'Calle 10 #20-30',
                'municipio' => 'Floridablanca',
                'metodo_pago' => 'efectivo',
                'items' => [['producto_id' => (string) $this->producto->getKey(), 'cantidad' => 1]],
            ]);
        $pedidoId = $creacion->json('data.id');

        // 2. Consumidor ajeno intenta consultarlo
        app('auth')->forgetGuards();
        $tokenAjeno = $this->consumidorAjeno->createToken('test_ajeno')->plainTextToken;
        $response = $this->withHeader('Authorization', 'Bearer ' . $tokenAjeno)
            ->getJson("/api/v1/pedidos/{$pedidoId}");

        $response->assertStatus(403)
            ->assertJsonPath('status', 'error');
    }

    public function test_api_externa_1_clima_finca_con_http_fake_retorna_200(): void
    {
        // Simulación (Mock) del servicio meteorológico externo Open-Meteo
        Http::fake([
            'https://api.open-meteo.com/v1/*' => Http::response([
                'latitude' => 7.06,
                'longitude' => -73.08,
                'current' => [
                    'temperature_2m' => 22.8,
                    'relative_humidity_2m' => 64,
                    'precipitation' => 0.0,
                    'weather_code' => 1,
                ],
            ], 200),
        ]);

        $response = $this->getJson("/api/v1/fincas/" . (string) $this->finca->getKey() . "/clima");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.municipio', $this->finca->municipio)
            ->assertJsonPath('data.temperatura_c', 22.8)
            ->assertJsonPath('data.condicion', 'Parcialmente nublado')
            ->assertJsonStructure([
                'status',
                'data' => [
                    'municipio',
                    'finca',
                    'temperatura_c',
                    'humedad_relativa',
                    'precipitacion_mm',
                    'condicion',
                    'recomendacion_agricola',
                    'fuente',
                ],
            ]);
    }

    public function test_api_externa_2_pasarela_stripe_con_http_fake_retorna_200(): void
    {
        $token = $this->consumidor->createToken('test_stripe')->plainTextToken;

        // Crear pedido previo
        $creacion = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/pedidos', [
                'direccion' => 'Calle 50 #15-20',
                'municipio' => 'San Gil',
                'metodo_pago' => 'tarjeta_credito',
                'items' => [['producto_id' => (string) $this->producto->getKey(), 'cantidad' => 1]],
            ]);
        $pedidoId = $creacion->json('data.id');

        // Simulación (Mock) de la API de Stripe
        Http::fake([
            'https://api.stripe.com/v1/payment_intents' => Http::response([
                'id' => 'pi_test_123456789',
                'client_secret' => 'pi_test_123456789_secret_abcdef',
                'status' => 'requires_payment_method',
                'amount' => 750000,
                'currency' => 'cop',
            ], 200),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/pedidos/{$pedidoId}/pagar");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.payment_intent_id', 'pi_test_123456789')
            ->assertJsonPath('data.client_secret', 'pi_test_123456789_secret_abcdef');
    }

    public function test_api_externa_2_webhook_pasarela_procesa_evento_y_retorna_200(): void
    {
        $token = $this->consumidor->createToken('test_webhook')->plainTextToken;

        $creacion = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/pedidos', [
                'direccion' => 'Calle Real #5-12',
                'municipio' => 'Barichara',
                'metodo_pago' => 'PSE',
                'items' => [['producto_id' => (string) $this->producto->getKey(), 'cantidad' => 1]],
            ]);
        $pedidoId = $creacion->json('data.id');

        $response = $this->postJson('/api/v1/pagos/webhook', [
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_fake_webhook_999',
                    'status' => 'succeeded',
                    'metadata' => [
                        'pedido_id' => $pedidoId,
                    ],
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('event', 'payment_intent.succeeded');
    }
}
