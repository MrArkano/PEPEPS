<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use App\Repositories\Contracts\ReporteRepositorio;
use Tests\TestCase;

final class ReportesAuditoriaTest extends TestCase
{
    private function usuario(string $correo): Usuario
    {
        return Usuario::query()->where('correo', $correo)->firstOrFail();
    }

    public function test_los_cinco_reportes_nosql_devuelven_datos_correctos(): void
    {
        $repo = app(ReporteRepositorio::class);

        // 1. Productos más vendidos por municipio
        $r1 = $repo->productosMasVendidosPorMunicipio();
        $this->assertNotEmpty($r1);
        $this->assertArrayHasKey('municipio', $r1[0]);
        $this->assertArrayHasKey('producto', $r1[0]);
        $this->assertArrayHasKey('cantidad_total_vendida', $r1[0]);

        // 2. Productores con pedidos pendientes
        $r2 = $repo->productoresConPedidosPendientes();
        $this->assertNotEmpty($r2);
        $this->assertArrayHasKey('finca_asociacion', $r2[0]);
        $this->assertArrayHasKey('cantidad_pedidos_pendientes', $r2[0]);

        // 3. Historial por consumidor
        $r3 = $repo->historialPedidosPorConsumidor();
        $this->assertNotEmpty($r3);
        $this->assertArrayHasKey('consumidor', $r3[0]);
        $this->assertArrayHasKey('total', $r3[0]);

        // 4. Pagos por medio y estado
        $r4 = $repo->pagosPorMedioYEstado();
        $this->assertNotEmpty($r4);
        $this->assertArrayHasKey('metodo_pago', $r4[0]);
        $this->assertArrayHasKey('estado_pago', $r4[0]);
        $this->assertArrayHasKey('valor_total', $r4[0]);

        // 5. Stock agotado / bajo por productor
        $r5 = $repo->productosAgotadosOStockBajo();
        $this->assertNotEmpty($r5);
        $this->assertArrayHasKey('producto', $r5[0]);
        $this->assertArrayHasKey('cantidad_disponible', $r5[0]);
    }

    public function test_pantalla_de_reportes_carga_las_cinco_consultas(): void
    {
        $admin = $this->usuario('carlos.ramirez@pepeps.co');

        foreach (['productos-mas-vendidos', 'productores-pendientes', 'historial-consumidores', 'pagos-por-medio', 'stock-critico'] as $pestana) {
            $this->actingAs($admin)
                ->get("/reportes?reporte={$pestana}")
                ->assertOk()
                ->assertSee('Analítica y Consultas NoSQL');
        }
    }

    public function test_consumidor_no_puede_acceder_a_reportes_ni_auditoria(): void
    {
        $consumidor = $this->usuario('andres.mantilla@gmail.com');

        $this->actingAs($consumidor)->get('/reportes')->assertForbidden();
        $this->actingAs($consumidor)->get('/gestion/auditoria')->assertForbidden();
    }

    public function test_auditoria_registra_cambios_de_estado_de_pedidos(): void
    {
        $admin = $this->usuario('carlos.ramirez@pepeps.co');
        $pedido = Pedido::query()->firstOrFail();
        $estadoAnterior = $pedido->estado->value;
        $nuevoEstadoStr = $estadoAnterior === 'pendiente' ? 'confirmado' : 'pendiente';

        $this->actingAs($admin)
            ->patch("/gestion/pedidos/{$pedido->getKey()}/estado", ['estado' => $nuevoEstadoStr])
            ->assertRedirect();

        $registro = Auditoria::query()
            ->where('entidad', 'pedidos')
            ->where('entidad_id', new \MongoDB\BSON\ObjectId((string) $pedido->getKey()))
            ->orderBy('fecha', 'desc')
            ->first();

        $this->assertNotNull($registro);
        $this->assertSame('estado', $registro->campo);
        $this->assertSame($nuevoEstadoStr, $registro->valor_nuevo);

        // Restaurar estado del pedido y limpiar registro de prueba
        $pedido->estado = $estadoAnterior;
        $pedido->save();
        $registro->delete();
    }

    public function test_auditoria_pantalla_accesible_solo_para_administrador(): void
    {
        $admin = $this->usuario('carlos.ramirez@pepeps.co');
        $productor = $this->usuario('jose.gomez@finca.co');

        $this->actingAs($admin)->get('/gestion/auditoria')->assertOk()->assertSee('Bitácora de Auditoría');
        $this->actingAs($productor)->get('/gestion/auditoria')->assertForbidden();
    }
}
