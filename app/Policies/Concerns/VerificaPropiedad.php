<?php

declare(strict_types=1);

namespace App\Policies\Concerns;

use App\Enums\Rol;
use App\Models\Usuario;

trait VerificaPropiedad
{
    protected function esAdministrador(Usuario $actor): bool
    {
        return $actor->tieneRol(Rol::Administrador);
    }

    protected function esProductor(Usuario $actor): bool
    {
        return $actor->tieneRol(Rol::Productor);
    }

    protected function esMismoUsuario(Usuario $actor, mixed $usuarioId): bool
    {
        return (string) $usuarioId === (string) $actor->getKey();
    }
}

