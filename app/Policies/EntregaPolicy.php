<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\EstadoEntrega;
use App\Models\Entrega;
use App\Models\Pedido;
use App\Models\Usuario;
use App\Policies\Concerns\VerificaPropiedad;

final class EntregaPolicy
{
    use VerificaPropiedad;

    public function view(Usuario $actor, Entrega $entrega): bool
    {
        if ($this->esAdministrador($actor)) {
            return true;
        }

        $pedido = Pedido::query()->find($entrega->pedido_id);
        return $pedido && $this->esMismoUsuario($actor, $pedido->consumidor_id);
    }

    public function update(Usuario $actor, Entrega $entrega): bool
    {
        return $this->esAdministrador($actor) || $this->esProductor($actor);
    }

    public function calificar(Usuario $actor, Entrega $entrega): bool
    {
        $pedido = Pedido::query()->find($entrega->pedido_id);

        return $pedido
            && $this->esMismoUsuario($actor, $pedido->consumidor_id)
            && $entrega->estado_entrega === EstadoEntrega::Entregado;
    }
}

