<?php

declare(strict_types=1);

namespace App\DTO;

use App\Models\Producto;

/**
 * Datos de la ficha pública con trazabilidad.
 *
 * @phpstan-type Promedio array{promedio: float, total: int}
 */
final readonly class FichaProducto
{
    /**
     * @param  array{promedio: float, total: int}|null  $calificacionProducto
     * @param  array{promedio: float, total: int}|null  $calificacionProductor
     */
    public function __construct(
        public Producto $producto,
        public ?array $calificacionProducto,
        public ?array $calificacionProductor,
    ) {}
}

