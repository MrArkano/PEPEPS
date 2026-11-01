<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Producto;
use App\Models\Usuario;
use App\Policies\Concerns\VerificaPropiedad;

/** Un Productor solo gestiona productos de sus propias fincas; el Administrador, todos. */
final class ProductoPolicy
{
    use VerificaPropiedad;

    public function create(Usuario $actor): bool
    {
        return $this->esAdministrador($actor) || $this->esProductor($actor);
    }

    public function update(Usuario $actor, Producto $producto): bool
    {
        return $this->esAdministrador($actor)
            || ($this->esProductor($actor) && $this->esMismoUsuario($actor, $producto->productor?->usuario_id));
    }

    public function delete(Usuario $actor, Producto $producto): bool
    {
        return $this->update($actor, $producto);
    }
}

