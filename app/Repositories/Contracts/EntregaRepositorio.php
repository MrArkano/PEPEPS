<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Entrega;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface EntregaRepositorio
{
    /** @throws \Illuminate\Database\Eloquent\ModelNotFoundException */
    public function encontrarOFallar(string $id): Entrega;

    public function delPedido(string $pedidoId): ?Entrega;

    public function paginar(int $porPagina = 10): LengthAwarePaginator;

    public function guardar(Entrega $entrega): void;
}

