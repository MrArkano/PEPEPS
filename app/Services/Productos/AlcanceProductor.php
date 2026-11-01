<?php

declare(strict_types=1);

namespace App\Services\Productos;

use App\Enums\Rol;
use App\Models\Usuario;
use App\Repositories\Contracts\ProductorRepositorio;

/** Determina qué fincas puede gestionar un usuario (aislamiento de la oferta del productor). */
final class AlcanceProductor
{
    public function __construct(private readonly ProductorRepositorio $productores) {}

    /** @return list<string>|null null = sin restricción (Administrador). */
    public function idsDe(Usuario $actor): ?array
    {
        if ($actor->tieneRol(Rol::Administrador)) {
            return null;
        }

        return $this->productores->delUsuario((string) $actor->getKey())
            ->map(static fn ($p): string => (string) $p->getKey())->values()->all();
    }

    /** @return list<string> Fincas a las que el actor puede asociar productos. */
    public function idsAsignables(Usuario $actor): array
    {
        return $this->idsDe($actor)
            ?? $this->productores->todos()->map(static fn ($p): string => (string) $p->getKey())->values()->all();
    }
}

