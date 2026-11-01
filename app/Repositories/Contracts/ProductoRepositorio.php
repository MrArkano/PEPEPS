<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTO\FiltrosProductos;
use App\Models\Producto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ProductoRepositorio
{
    /** @throws \Illuminate\Database\Eloquent\ModelNotFoundException */
    public function encontrarOFallar(string $id): Producto;

    /** @return LengthAwarePaginator<int, Producto> Con productor y categoría ya cargados (sin N+1). */
    public function buscar(FiltrosProductos $filtros, int $porPagina): LengthAwarePaginator;

    /**
     * Productos agotados o con stock bajo.
     *
     * @param  list<string>|null  $productorIds  null = todos.
     * @return Collection<int, Producto>
     */
    public function criticos(?array $productorIds): Collection;

    public function guardar(Producto $producto): void;

    public function eliminar(Producto $producto): void;

    public function tienePedidos(Producto $producto): bool;
}

