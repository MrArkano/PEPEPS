<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Pedido;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PedidoRepositorio
{
    /** @throws \Illuminate\Database\Eloquent\ModelNotFoundException */
    public function encontrarOFallar(string $id): Pedido;

    public function delConsumidor(string $consumidorId, int $porPagina = 10): LengthAwarePaginator;

    /**
     * Pedidos que contienen productos de las fincas dadas (o todos si null).
     *
     * @param list<string>|null $productorIds
     */
    public function paraProductores(?array $productorIds, ?string $estado = null, int $porPagina = 10): LengthAwarePaginator;

    /**
     * Conteo o listado de pedidos en estado 'pendiente' para seguimiento.
     *
     * @param list<string>|null $productorIds
     * @return Collection<int, Pedido>
     */
    public function pendientes(?array $productorIds): Collection;

    public function guardar(Pedido $pedido): void;
}

