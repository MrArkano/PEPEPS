<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\EstadoPago;
use App\Enums\EstadoPedido;
use App\Models\Entrega;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use MongoDB\BSON\ObjectId;
use Tests\TestCase;

final class PedidosPagosEntregasTest extends TestCase
{
    private function usuario(string $correo): Usuario
    {
        return Usuario::query()->where('correo', $correo)->firstOrFail();
    }

    public function test_operaciones_del_carrito_de_compras(): void
    {
        $producto = Producto::query()->where('estado', 'activo')->firstOrFail();
        $prodId = (string) $producto->getKey();

        // 1. Ver carrito vacío inicialmente
        $this->get('/carrito')
            ->assertOk()
            ->assertSee('Tu carrito', false);

        // 2. Agregar ítem al carrito y verificar sesión
        $response = $this->withSession([])->post('/carrito/agregar', [
            'producto_id' => $prodId,
            'cantidad' => 2,
        ]);
        $response->assertSessionHas('pepeps_carrito', [$prodId => 2]);

        // 3. Modificar cantidad en sesión
        $response = $this->withSession(['pepeps_carrito' => [$prodId => 2]])
            ->patch("/carrito/{$prodId}", ['cantidad' => 5]);
        $response->assertSessionHas('pepeps_carrito', [$prodId => 5]);

        // 4. Remover ítem
        $response = $this->withSession(['pepeps_carrito' => [$prodId => 5]])
            ->delete("/carrito/{$prodId}");
        $response->assertSessionMissing("pepeps_carrito.{$prodId}");
    }

    public function test_proceso_de_checkout_y_creacion_de_pedido_completo(): void
    {
        $consumidor = $this->usuario('andres.mantilla@gmail.com');
        $mango = Producto::query()->where('nombre', 'Mango Tommy')->firstOrFail();
        $stockInicial = $mango->cantidadDisponible();
        $mangoId = (string) $mango->getKey();

        // Simular carrito con 2 unidades y procesar checkout
        $response = $this->actingAs($consumidor)
            ->withSession(['pepeps_carrito' => [$mangoId => 2]])
            ->post('/checkout', [
                'tipo_entrega' => 'domicilio',
                'metodo_pago' => 'PSE',
                'municipio' => 'Bucaramanga',
                'direccion' => 'Calle 45 # 20-30',
                'barrio' => 'Álvarez',
                'referencia' => 'Apto 101',
            ]);

        $response->assertRedirect();

        // Verificar que el pedido existe con ObjectId
        $pedido = Pedido::query()
            ->where('consumidor_id', new ObjectId((string) $consumidor->getKey()))
            ->orderBy('fecha', 'desc')
            ->firstOrFail();

        $this->assertSame('domicilio', $pedido->tipo_entrega->value);
        $this->assertSame('4000.00', (string) $pedido->costo_envio);
        $this->assertCount(1, $pedido->items);
        $this->assertSame('Mango Tommy', $pedido->items[0]['nombre_producto']);
        $this->assertSame(2, $pedido->items[0]['cantidad']);

        // Verificar descuento de stock
        $mango->refresh();
        $this->assertSame($stockInicial - 2, $mango->cantidadDisponible());

        // Verificar pago
        $pago = Pago::query()->where('pedido_id', new ObjectId((string) $pedido->getKey()))->first();
        $this->assertNotNull($pago);
        $this->assertSame('PSE', $pago->metodo_pago->value);

        // Verificar entrega
        $entrega = Entrega::query()->where('pedido_id', new ObjectId((string) $pedido->getKey()))->first();
        $this->assertNotNull($entrega);
        $this->assertSame('programada', $entrega->estado_entrega->value);

        // Restaurar estado para no alterar el dataset
        $mango->inventario = [
            'presentacion' => 'Bulto x 20 kg',
            'cantidad_disponible' => $stockInicial,
            'estado' => 'disponible',
            'fecha_actualizacion' => now(),
        ];
        $mango->save();

        $pago->delete();
        $entrega->delete();
        $pedido->delete();
    }

    public function test_productor_puede_cambiar_estado_de_pedido_asociado(): void
    {
        $jose = $this->usuario('jose.gomez@finca.co');
        $mango = Producto::query()->where('nombre', 'Mango Tommy')->firstOrFail();
        $pedidoConMango = Pedido::query()
            ->where('items.producto_id', new ObjectId((string) $mango->getKey()))
            ->firstOrFail();

        $estadoOriginal = $pedidoConMango->estado;

        $this->actingAs($jose)
            ->patch("/gestion/pedidos/{$pedidoConMango->getKey()}/estado", ['estado' => 'confirmado'])
            ->assertRedirect();

        $pedidoConMango->refresh();
        $this->assertSame(EstadoPedido::Confirmado, $pedidoConMango->estado);

        // Restaurar estado original
        $pedidoConMango->estado = $estadoOriginal;
        $pedidoConMango->save();
    }

    public function test_administrador_puede_conciliar_pago(): void
    {
        $admin = $this->usuario('carlos.ramirez@pepeps.co');
        $pago = Pago::query()->firstOrFail();
        $estadoOriginal = $pago->estado;

        $this->actingAs($admin)
            ->patch("/gestion/pagos/{$pago->getKey()}/conciliar", ['estado' => 'aprobado'])
            ->assertRedirect();

        $pago->refresh();
        $this->assertSame(EstadoPago::Aprobado, $pago->estado);

        // Restaurar
        $pago->estado = $estadoOriginal;
        $pago->save();
    }

    public function test_consumidor_puede_calificar_producto_y_entrega_en_pedido_entregado(): void
    {
        $consumidor = $this->usuario('andres.mantilla@gmail.com');
        $pedidoEntregado = Pedido::query()
            ->where('consumidor_id', new ObjectId((string) $consumidor->getKey()))
            ->where('estado', 'entregado')
            ->firstOrFail();

        $productoId = (string) $pedidoEntregado->items[0]['producto_id'];

        $this->actingAs($consumidor)
            ->post("/mis-pedidos/{$pedidoEntregado->getKey()}/calificar-item", [
                'producto_id' => $productoId,
                'calificacion' => 5,
                'comentario' => 'Excelente producto.',
            ])->assertRedirect();

        $pedidoEntregado->refresh();
        $this->assertSame(5, $pedidoEntregado->items[0]['calificacion_producto']);
        $this->assertSame('Excelente producto.', $pedidoEntregado->items[0]['comentario_producto']);
    }
}
