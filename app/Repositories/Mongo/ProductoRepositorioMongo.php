<?php

declare(strict_types=1);

namespace App\Repositories\Mongo;

use App\DTO\FiltrosProductos;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Productor;
use App\Repositories\Contracts\ProductoRepositorio;
use App\Repositories\Mongo\Concerns\UsaMongo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use MongoDB\BSON\Regex;

final class ProductoRepositorioMongo implements ProductoRepositorio
{
    use UsaMongo;

    public function encontrarOFallar(string $id): Producto
    {
        return Producto::query()->with(['productor', 'categoria'])->where('_id', $this->oid($id))->firstOrFail();
    }

    public function buscar(FiltrosProductos $filtros, int $porPagina): LengthAwarePaginator
    {
        $consulta = Producto::query()->with(['productor', 'categoria']);

        $productorIds = $this->productoresEnAlcance($filtros);
        if ($productorIds !== null) {
            $consulta->whereIn('productor_id', $this->oids($productorIds));
        }

        if ($filtros->categoriaId !== null) {
            $consulta->where('categoria_id', $this->oid($filtros->categoriaId));
        }

        if ($filtros->busqueda !== null) {
            // La entrada se escapa y se envuelve en un Regex BSON: no hay inyección de operadores.
            $consulta->where('nombre', 'regexp', new Regex(preg_quote($filtros->busqueda, '/'), 'i'));
        }

        if ($filtros->soloActivos) {
            $consulta->where('estado', 'activo');
        }

        if ($filtros->soloCriticos) {
            $this->soloCriticos($consulta);
        }

        return $consulta->orderBy('nombre')->paginate($porPagina)->withQueryString();
    }

    public function criticos(?array $productorIds): Collection
    {
        $consulta = Producto::query()->with(['productor']);

        if ($productorIds !== null) {
            $consulta->whereIn('productor_id', $this->oids($productorIds));
        }

        return $this->soloCriticos($consulta)->orderBy('inventario.cantidad_disponible')->get();
    }

    public function guardar(Producto $producto): void
    {
        $producto->save();
    }

    public function eliminar(Producto $producto): void
    {
        $producto->delete();
    }

    public function tienePedidos(Producto $producto): bool
    {
        return Pedido::query()->where('items.producto_id', $this->oid((string) $producto->getKey()))->exists();
    }

    /** Agotado, o con 10 unidades o menos (misma regla que la consulta 5 del proyecto). */
    private function soloCriticos(Builder $consulta): Builder
    {
        return $consulta->where(static function (Builder $q): void {
            $q->where('inventario.estado', 'agotado')
                ->orWhere('inventario.cantidad_disponible', '<=', Producto::STOCK_BAJO_MAXIMO);
        });
    }

    /**
     * Combina por intersección el alcance del actor, el filtro de productor y el de municipio.
     * Evita un $lookup: el municipio vive en `productores`.
     *
     * @return list<string>|null null = sin restricción.
     */
    private function productoresEnAlcance(FiltrosProductos $filtros): ?array
    {
        $ids = $filtros->productorIdsPermitidos;

        $intersecar = static fn (?array $actual, array $otros): array => $actual === null
            ? array_values($otros)
            : array_values(array_intersect($actual, $otros));

        if ($filtros->productorId !== null) {
            $ids = $intersecar($ids, [$filtros->productorId]);
        }

        if ($filtros->municipio !== null) {
            $delMunicipio = Productor::query()->where('municipio', $filtros->municipio)->get(['_id'])
                ->map(static fn (Productor $p): string => (string) $p->getKey())->all();
            $ids = $intersecar($ids, $delMunicipio);
        }

        return $ids;
    }
}

