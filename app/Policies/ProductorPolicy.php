<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Productor;
use App\Models\Usuario;
use App\Policies\Concerns\VerificaPropiedad;

/** Fincas/asociaciones: el Productor gestiona las suyas; el Administrador, todas. */
final class ProductorPolicy
{
    use VerificaPropiedad;

    public function create(Usuario $actor): bool
    {
        return $this->esAdministrador($actor) || $this->esProductor($actor);
    }

    public function update(Usuario $actor, Productor $finca): bool
    {
        return $this->esAdministrador($actor)
            || ($this->esProductor($actor) && $this->esMismoUsuario($actor, $finca->usuario_id));
    }

    public function delete(Usuario $actor, Productor $finca): bool
    {
        return $this->update($actor, $finca);
    }
}

