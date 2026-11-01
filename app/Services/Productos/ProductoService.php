<?php

declare(strict_types=1);

namespace App\Services\Productos;

use App\Exceptions\Catalogo\ProductoConPedidos;
use App\Models\Producto;
use App\Models\Usuario;
use App\Repositories\Contracts\ProductoRepositorio;
use App\Services\Auditoria\AuditoriaService;

final class ProductoService
{
    public function __construct(
        private readonly ProductoRepositorio $productos,
        private readonly InventarioService $inventario,
        private readonly AuditoriaService $auditoria,
    ) {}

    /**
     * @param  array<string, mixed>  $datos  Ya validados por ProductoRequest.
     */
    public function crear(array $datos): Producto
    {
        $producto = new Producto($this->atributos($datos));
        $producto->imagenes = [];
        $producto->fecha_creacion = now();
        $producto->inventario = $this->inventario->construir($datos);

        $this->productos->guardar($producto);

        return $producto;
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function actualizar(Producto $producto, array $datos, ?Usuario $actor = null): Producto
    {
        $precioAnterior = (string) $producto->precio;
        $precioNuevo = (string) $datos['precio'];

        if ($precioAnterior !== $precioNuevo) {
            $this->auditoria->registrarCambio(
                $actor ?? auth()->user(),
                'productos',
                (string) $producto->getKey(),
                'cambio_precio',
                'precio',
                $precioAnterior,
                $precioNuevo,
            );
        }

        $producto->fill($this->atributos($datos));
        $producto->inventario = $this->inventario->construir($datos);

        $this->productos->guardar($producto);

        return $producto;
    }

    /** @throws ProductoConPedidos */
    public function eliminar(Producto $producto): void
    {
        if ($this->productos->tienePedidos($producto)) {
            throw new ProductoConPedidos;
        }

        $this->productos->eliminar($producto);
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    private function atributos(array $datos): array
    {
        return [
            'productor_id' => $datos['productor_id'],
            'categoria_id' => $datos['categoria_id'],
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'],
            'precio' => $datos['precio'],
            'unidad_medida' => $datos['unidad_medida'],
            'estado' => $datos['estado'],
        ];
    }
}

