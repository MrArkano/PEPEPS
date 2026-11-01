<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\EstadoPedido;
use App\Enums\Rol;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use App\Policies\Concerns\VerificaPropiedad;
use MongoDB\BSON\ObjectId;

final class PedidoPolicy
{
    use VerificaPropiedad;

    public function view(Usuario $actor, Pedido $pedido): bool
    {
        if ($this->esAdministrador($actor)) {
            return true;
        }

        if ($this->esMismoUsuario($actor, $pedido->consumidor_id)) {
            return true;
        }

        if ($this->esProductor($actor)) {
            return $this->tieneProductosDeProductor($actor, $pedido);
        }

        return false;
    }

    public function update(Usuario $actor, Pedido $pedido): bool
    {
        if ($this->esAdministrador($actor)) {
            return true;
        }

        if ($this->esProductor($actor)) {
            return $this->tieneProductosDeProductor($actor, $pedido);
        }

        return false;
    }

    public function calificar(Usuario $actor, Pedido $pedido): bool
    {
        return $this->esMismoUsuario($actor, $pedido->consumidor_id) && $pedido->estado === EstadoPedido::Entregado;
    }

    private function tieneProductosDeProductor(Usuario $actor, Pedido $pedido): bool
    {
        $fincasIds = $actor->productores()->get(['_id'])->map(fn ($p) => new ObjectId((string) $p->getKey()))->all();
        if ($fincasIds === []) {
            return false;
        }

        $misProductosIds = Producto::query()
            ->whereIn('productor_id', $fincasIds)
            ->get(['_id'])
            ->map(fn ($pr) => (string) $pr->getKey())
            ->all();

        foreach ($pedido->items ?? [] as $item) {
            $prodId = (string) ($item['producto_id'] ?? '');
            if (in_array($prodId, $misProductosIds, true)) {
                return true;
            }
        }

        return false;
    }
}

