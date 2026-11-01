<?php

declare(strict_types=1);

namespace App\Repositories\Mongo;

use App\Models\Pedido;
use App\Models\Producto;
use App\Repositories\Contracts\PedidoRepositorio;
use App\Repositories\Mongo\Concerns\UsaMongo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class PedidoRepositorioMongo implements PedidoRepositorio
{
    use UsaMongo;

    public function encontrarOFallar(string $id): Pedido
    {
        return Pedido::query()->with(['consumidor'])->where('_id', $this->oid($id))->firstOrFail();
    }

    public function delConsumidor(string $consumidorId, int $porPagina = 10): LengthAwarePaginator
    {
        return Pedido::query()
            ->where('consumidor_id', $this->oid($consumidorId))
            ->orderBy('fecha', 'desc')
            ->paginate($porPagina)
            ->withQueryString();
    }

    public function paraProductores(?array $productorIds, ?string $estado = null, int $porPagina = 10): LengthAwarePaginator
    {
        $consulta = Pedido::query()->with(['consumidor']);

        if ($productorIds !== null) {
            $productoOids = Producto::query()
                ->whereIn('productor_id', $this->oids($productorIds))
                ->get(['_id'])
                ->map(fn (Producto $p) => $this->oid((string) $p->getKey()))
                ->all();

            $consulta->whereIn('items.producto_id', $productoOids);
        }

        if ($estado !== null && $estado !== '') {
            $consulta->where('estado', $estado);
        }

        return $consulta->orderBy('fecha', 'desc')->paginate($porPagina)->withQueryString();
    }

    public function pendientes(?array $productorIds): Collection
    {
        $consulta = Pedido::query()->where('estado', 'pendiente');

        if ($productorIds !== null) {
            $productoOids = Producto::query()
                ->whereIn('productor_id', $this->oids($productorIds))
                ->get(['_id'])
                ->map(fn (Producto $p) => $this->oid((string) $p->getKey()))
                ->all();

            $consulta->whereIn('items.producto_id', $productoOids);
        }

        return $consulta->orderBy('fecha', 'asc')->get();
    }

    public function guardar(Pedido $pedido): void
    {
        $pedido->save();
    }
}

