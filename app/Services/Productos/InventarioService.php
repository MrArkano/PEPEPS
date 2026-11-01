<?php

declare(strict_types=1);

namespace App\Services\Productos;

use App\Models\Producto;
use App\Models\Usuario;
use App\Services\Auditoria\AuditoriaService;
use MongoDB\BSON\UTCDateTime;

/** Reglas del inventario embebido en el producto. */
final class InventarioService
{
    public function __construct(private readonly AuditoriaService $auditoria) {}

    /**
     * @param  array{presentacion: string, cantidad_disponible: int|string, estado_inventario: string}  $datos
     * @return array{presentacion: string, cantidad_disponible: int, estado: string, fecha_actualizacion: UTCDateTime}
     */
    public function construir(array $datos): array
    {
        return [
            'presentacion' => $datos['presentacion'],
            'cantidad_disponible' => (int) $datos['cantidad_disponible'],
            'estado' => $datos['estado_inventario'],
            'fecha_actualizacion' => new UTCDateTime(now()),
        ];
    }

    /**
     * @param  array{presentacion: string, cantidad_disponible: int|string, estado_inventario: string}  $datos
     */
    public function actualizar(Producto $producto, array $datos, ?Usuario $actor = null): Producto
    {
        $cantAnterior = $producto->cantidadDisponible();
        $cantNueva = (int) $datos['cantidad_disponible'];

        if ($cantAnterior !== $cantNueva) {
            $this->auditoria->registrarCambio(
                $actor ?? auth()->user(),
                'productos',
                (string) $producto->getKey(),
                'cambio_inventario',
                'inventario.cantidad_disponible',
                $cantAnterior,
                $cantNueva,
            );
        }

        $estadoAnterior = $producto->inventario['estado'] ?? 'disponible';
        if ($estadoAnterior !== $datos['estado_inventario']) {
            $this->auditoria->registrarCambio(
                $actor ?? auth()->user(),
                'productos',
                (string) $producto->getKey(),
                'cambio_estado_inventario',
                'inventario.estado',
                $estadoAnterior,
                $datos['estado_inventario'],
            );
        }

        $producto->inventario = $this->construir($datos);
        $producto->save();

        return $producto;
    }
}
