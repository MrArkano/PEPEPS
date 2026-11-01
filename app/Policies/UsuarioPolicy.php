<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Rol;
use App\Models\Usuario;

/** Gestión de usuarios: solo el Administrador; cada usuario puede ver/editar su propio perfil. */
final class UsuarioPolicy
{
    public function viewAny(Usuario $actor): bool
    {
        return $actor->tieneRol(Rol::Administrador);
    }

    public function view(Usuario $actor, Usuario $objetivo): bool
    {
        return $actor->tieneRol(Rol::Administrador) || $actor->is($objetivo);
    }

    public function update(Usuario $actor, Usuario $objetivo): bool
    {
        return $this->view($actor, $objetivo);
    }

    /** Un administrador no puede cambiarse el rol a sí mismo (evita quedarse sin administradores). */
    public function asignarRol(Usuario $actor, Usuario $objetivo): bool
    {
        return $actor->tieneRol(Rol::Administrador) && ! $actor->is($objetivo);
    }

    public function delete(Usuario $actor, Usuario $objetivo): bool
    {
        return $this->asignarRol($actor, $objetivo);
    }
}

