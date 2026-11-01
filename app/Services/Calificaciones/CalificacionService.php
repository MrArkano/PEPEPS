<?php

declare(strict_types=1);

namespace App\Services\Calificaciones;

use App\Models\Entrega;
use App\Models\Pedido;
use App\Repositories\Contracts\EntregaRepositorio;
use App\Repositories\Contracts\PedidoRepositorio;

final class CalificacionService
{
    public function __construct(
        private readonly PedidoRepositorio $pedidos,
        private readonly EntregaRepositorio $entregas,
    ) {}

    public function calificarItem(Pedido $pedido, string $productoId, int $calificacion, ?string $comentario): void
    {
        $items = $pedido->items ?? [];

        foreach ($items as &$item) {
            $itemId = (string) ($item['producto_id'] ?? '');
            if ($itemId === $productoId) {
                $item['calificacion_producto'] = max(1, min(5, $calificacion));
                if ($comentario !== null && trim($comentario) !== '') {
                    $item['comentario_producto'] = trim($comentario);
                }
                break;
            }
        }
        unset($item);

        $pedido->items = $items;
        $this->pedidos->guardar($pedido);
    }

    public function calificarEntrega(Entrega $entrega, int $calificacion, ?string $comentario): void
    {
        $entrega->calificacion_entrega = max(1, min(5, $calificacion));
        if ($comentario !== null && trim($comentario) !== '') {
            $entrega->comentario_entrega = trim($comentario);
        }

        $this->entregas->guardar($entrega);
    }
}

