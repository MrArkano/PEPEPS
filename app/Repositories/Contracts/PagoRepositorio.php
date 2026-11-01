<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Pago;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PagoRepositorio
{
    /** @throws \Illuminate\Database\Eloquent\ModelNotFoundException */
    public function encontrarOFallar(string $id): Pago;

    /** @return Collection<int, Pago> */
    public function delPedido(string $pedidoId): Collection;

    public function paginar(int $porPagina = 10): LengthAwarePaginator;

    public function guardar(Pago $pago): void;
}

