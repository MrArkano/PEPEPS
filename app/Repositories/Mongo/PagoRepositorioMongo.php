<?php

declare(strict_types=1);

namespace App\Repositories\Mongo;

use App\Models\Pago;
use App\Repositories\Contracts\PagoRepositorio;
use App\Repositories\Mongo\Concerns\UsaMongo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class PagoRepositorioMongo implements PagoRepositorio
{
    use UsaMongo;

    public function encontrarOFallar(string $id): Pago
    {
        return Pago::query()->with('pedido')->where('_id', $this->oid($id))->firstOrFail();
    }

    public function delPedido(string $pedidoId): Collection
    {
        return Pago::query()->where('pedido_id', $this->oid($pedidoId))->orderBy('fecha_pago', 'desc')->get();
    }

    public function paginar(int $porPagina = 10): LengthAwarePaginator
    {
        return Pago::query()->with('pedido')->orderBy('fecha_pago', 'desc')->paginate($porPagina)->withQueryString();
    }

    public function guardar(Pago $pago): void
    {
        $pago->save();
    }
}

