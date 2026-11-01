<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface CalificacionRepositorio
{
    /**
     * Promedio de calificaciones de productos vendidos por productor.
     *
     * @param  list<string>  $productorIds
     * @return array<string, array{promedio: float, total: int}> Indexado por id de productor.
     */
    public function promedioPorProductor(array $productorIds): array;

    /** @return array{promedio: float, total: int}|null */
    public function promedioDeProducto(string $productoId): ?array;
}

