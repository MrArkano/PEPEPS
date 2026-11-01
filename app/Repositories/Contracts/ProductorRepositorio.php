<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Productor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ProductorRepositorio
{
    /** @throws \Illuminate\Database\Eloquent\ModelNotFoundException */
    public function encontrarOFallar(string $id): Productor;

    /** @return Collection<int, Productor> Fincas de un usuario (puede tener varias). */
    public function delUsuario(string $usuarioId): Collection;

    /** @return Collection<int, Productor> */
    public function todos(): Collection;

    /**
     * @param  string|null  $usuarioId  null = todas las fincas.
     * @return LengthAwarePaginator<int, Productor>
     */
    public function paginar(?string $usuarioId, int $porPagina): LengthAwarePaginator;

    /** @return list<string> Municipios con fincas, ordenados. */
    public function municipios(): array;

    public function guardar(Productor $productor): void;

    public function eliminar(Productor $productor): void;

    public function tieneProductos(Productor $productor): bool;
}

