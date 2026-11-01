<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Usuario;
use App\Policies\Concerns\VerificaPropiedad;

final class PagoPolicy
{
    use VerificaPropiedad;

    public function view(Usuario $actor, Pago $pago): bool
    {
        if ($this->esAdministrador($actor)) {
            return true;
        }

        $pedido = Pedido::query()->find($pago->pedido_id);
        return $pedido && $this->esMismoUsuario($actor, $pedido->consumidor_id);
    }

    public function conciliar(Usuario $actor, Pago $pago): bool
    {
        return $this->esAdministrador($actor);
    }
}

