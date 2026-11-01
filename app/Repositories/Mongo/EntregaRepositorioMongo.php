<?php

declare(strict_types=1);

namespace App\Repositories\Mongo;

use App\Models\Entrega;
use App\Repositories\Contracts\EntregaRepositorio;
use App\Repositories\Mongo\Concerns\UsaMongo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EntregaRepositorioMongo implements EntregaRepositorio
{
    use UsaMongo;

    public function encontrarOFallar(string $id): Entrega
    {
        return Entrega::query()->with('pedido')->where('_id', $this->oid($id))->firstOrFail();
    }

    public function delPedido(string $pedidoId): ?Entrega
    {
        return Entrega::query()->where('pedido_id', $this->oid($pedidoId))->first();
    }

    public function paginar(int $porPagina = 10): LengthAwarePaginator
    {
        return Entrega::query()->with('pedido')->orderBy('fecha_programada', 'desc')->paginate($porPagina)->withQueryString();
    }

    public function guardar(Entrega $entrega): void
    {
        $entrega->save();
    }
}

