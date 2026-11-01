<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Productor;
use App\Models\Usuario;
use Tests\TestCase;

final class CatalogoGestionTest extends TestCase
{
    private function usuario(string $correo): Usuario
    {
        return Usuario::query()->where('correo', $correo)->firstOrFail();
    }

    public function test_catalogo_publico_es_accesible_para_invitados(): void
    {
        $this->get('/catalogo')
            ->assertOk()
            ->assertSee('Catálogo campesino')
            ->assertSee('Mango Tommy')
            ->assertSee('Papa Pastusa');
    }

    public function test_filtros_de_catalogo_por_municipio_y_busqueda(): void
    {
        $this->get('/catalogo?municipio=Bucaramanga')
            ->assertOk()
            ->assertSee('Mango Tommy')
            ->assertDontSee('Aguacate Hass'); // Floridablanca

        $this->get('/catalogo?q=Queso')
            ->assertOk()
            ->assertSee('Queso Campesino')
            ->assertDontSee('Tomate Chonto');
    }

    public function test_ficha_publica_muestra_origen_trazabilidad_e_insignia(): void
    {
        $mango = Producto::query()->where('nombre', 'Mango Tommy')->firstOrFail();

        $this->get("/productos/{$mango->getKey()}")
            ->assertOk()
            ->assertSee('Mango Tommy')
            ->assertSee('Finca Los Naranjos')
            ->assertSee('Productor verificado')
            ->assertSee('Bucaramanga');
    }

    public function test_productor_solo_ve_sus_fincas_en_su_panel_y_gestion(): void
    {
        $jose = $this->usuario('jose.gomez@finca.co');

        $this->actingAs($jose)->get('/gestion/productos')
            ->assertOk()
            ->assertSee('Finca Los Naranjos')
            ->assertDontSee('Finca La Esperanza'); // de maria.suarez
    }

    public function test_productor_no_puede_editar_producto_de_otro_productor(): void
    {
        $jose = $this->usuario('jose.gomez@finca.co');
        $aguacateDeMaria = Producto::query()->where('nombre', 'Aguacate Hass')->firstOrFail();

        // 403 Forbidden por policy
        $this->actingAs($jose)
            ->get("/gestion/productos/{$aguacateDeMaria->getKey()}/edit")
            ->assertForbidden();

        $this->actingAs($jose)
            ->put("/gestion/productos/{$aguacateDeMaria->getKey()}", [
                'nombre' => 'Hackeado',
                'descripcion' => 'Intento',
                'productor_id' => (string) $aguacateDeMaria->productor_id,
                'categoria_id' => (string) $aguacateDeMaria->categoria_id,
                'precio' => '1000',
                'unidad_medida' => 'kg',
                'estado' => 'activo',
                'presentacion' => 'Bolsa',
                'cantidad_disponible' => 10,
                'estado_inventario' => 'disponible',
            ])
            ->assertForbidden();
    }

    public function test_actualizacion_rapida_de_inventario(): void
    {
        $jose = $this->usuario('jose.gomez@finca.co');
        $mango = Producto::query()->where('nombre', 'Mango Tommy')->firstOrFail();

        $this->actingAs($jose)
            ->patch("/gestion/inventario/{$mango->getKey()}", [
                'presentacion' => 'Caja x 25 kg',
                'cantidad_disponible' => 90,
                'estado_inventario' => 'disponible',
            ])
            ->assertRedirect();

        $mango->refresh();
        $this->assertSame('Caja x 25 kg', $mango->inventario['presentacion']);
        $this->assertSame(90, $mango->cantidadDisponible());

        // Restaurar estado original para idempotencia
        $mango->inventario = [
            'presentacion' => 'Bulto x 20 kg',
            'cantidad_disponible' => 85,
            'estado' => 'disponible',
            'fecha_actualizacion' => now(),
        ];
        $mango->save();
    }

    public function test_no_se_puede_eliminar_producto_con_pedidos(): void
    {
        $admin = $this->usuario('carlos.ramirez@pepeps.co');
        $mangoConPedidos = Producto::query()->where('nombre', 'Mango Tommy')->firstOrFail();

        $this->actingAs($admin)
            ->delete("/gestion/productos/{$mangoConPedidos->getKey()}")
            ->assertRedirect();

        $this->assertNotNull(Producto::query()->find($mangoConPedidos->getKey()));
    }

    public function test_no_se_puede_eliminar_finca_con_productos(): void
    {
        $admin = $this->usuario('carlos.ramirez@pepeps.co');
        $fincaConProductos = Productor::query()->where('nombre_finca_asociacion', 'Finca Los Naranjos')->firstOrFail();

        $this->actingAs($admin)
            ->delete("/gestion/fincas/{$fincaConProductos->getKey()}")
            ->assertRedirect();

        $this->assertNotNull(Productor::query()->find($fincaConProductos->getKey()));
    }
}

