<?php

declare(strict_types=1);

namespace App\Services\Pagos;

use App\Enums\EstadoPago;
use App\Enums\EstadoPedido;
use App\Enums\MetodoPago;
use App\Models\Pago;
use App\Models\Pedido;
use App\Repositories\Contracts\PagoRepositorio;
use App\Repositories\Contracts\PedidoRepositorio;
use MongoDB\BSON\ObjectId;

final class PagoService
{
    public function __construct(
        private readonly PagoRepositorio $pagos,
        private readonly PedidoRepositorio $pedidos,
    ) {}

    public function registrarPago(Pedido $pedido, MetodoPago $metodo): Pago
    {
        $pago = new Pago;
        $pago->pedido_id = new ObjectId((string) $pedido->getKey());
        $pago->metodo_pago = $metodo;
        $pago->valor = $pedido->total;
        $pago->estado = EstadoPago::Pendiente;
        $pago->fecha_pago = now();
        $pago->referencia_transaccion = 'TXN-' . strtoupper(bin2hex(random_bytes(4))) . '-PEPEPS';

        $this->pagos->guardar($pago);

        return $pago;
    }

    /**
     * Concilia el estado del pago y actualiza el pedido si corresponde.
     */
    public function conciliar(Pago $pago, EstadoPago $nuevoEstado): void
    {
        $pago->estado = $nuevoEstado;
        $this->pagos->guardar($pago);

        $pedido = $this->pedidos->encontrarOFallar((string) $pago->pedido_id);

        if ($nuevoEstado === EstadoPago::Aprobado && $pedido->estado === EstadoPedido::Pendiente) {
            $pedido->estado = EstadoPedido::Confirmado;
            $this->pedidos->guardar($pedido);
        }

        if ($nuevoEstado === EstadoPago::Reembolsado) {
            $pedido->estado = EstadoPedido::Cancelado;
            $this->pedidos->guardar($pedido);
        }
    }
}

