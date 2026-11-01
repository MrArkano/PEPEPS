<?php

declare(strict_types=1);

namespace App\Repositories\Mongo;

use App\Models\Producto;
use App\Models\Productor;
use App\Repositories\Contracts\ProductorRepositorio;
use App\Repositories\Mongo\Concerns\UsaMongo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class ProductorRepositorioMongo implements ProductorRepositorio
{
    use UsaMongo;

    public function encontrarOFallar(string $id): Productor
    {
        return Productor::query()->where('_id', $this->oid($id))->firstOrFail();
    }

    public function delUsuario(string $usuarioId): Collection
    {
        return Productor::query()->where('usuario_id', $this->oid($usuarioId))
            ->orderBy('nombre_finca_asociacion')->get();
    }

    public function todos(): Collection
    {
        return Productor::query()->orderBy('nombre_finca_asociacion')->get();
    }

    public function paginar(?string $usuarioId, int $porPagina): LengthAwarePaginator
    {
        $consulta = Productor::query();

        if ($usuarioId !== null) {
            $consulta->where('usuario_id', $this->oid($usuarioId));
        }

        return $consulta->orderBy('nombre_finca_asociacion')->paginate($porPagina)->withQueryString();
    }

    public function municipios(): array
    {
        /** @var list<string> $municipios */
        $municipios = $this->coleccion('productores')->distinct('municipio');
        sort($municipios);

        return $municipios;
    }

    public function guardar(Productor $productor): void
    {
        $productor->save();
    }

    public function eliminar(Productor $productor): void
    {
        $productor->delete();
    }

    public function tieneProductos(Productor $productor): bool
    {
        return Producto::query()->where('productor_id', $this->oid((string) $productor->getKey()))->exists();
    }
}

