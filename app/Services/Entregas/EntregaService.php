<?php

declare(strict_types=1);

namespace App\Services\Entregas;

use App\Enums\EstadoEntrega;
use App\Enums\EstadoPedido;
use App\Enums\TipoResponsable;
use App\Models\Entrega;
use App\Repositories\Contracts\EntregaRepositorio;
use App\Repositories\Contracts\PedidoRepositorio;
use Carbon\Carbon;
use MongoDB\BSON\ObjectId;

final class EntregaService
{
    public function __construct(
        private readonly EntregaRepositorio $entregas,
        private readonly PedidoRepositorio $pedidos,
    ) {}

    /**
     * @param array{
     *     responsable_id?: string|null,
     *     tipo_responsable?: string|null,
     *     estado_entrega: string,
     *     fecha_programada?: string|null,
     *     fecha_entrega?: string|null,
     *     observaciones?: string|null
     * } $datos
     */
    public function actualizarLogistica(Entrega $entrega, array $datos): Entrega
    {
        if (! empty($datos['responsable_id'])) {
            $entrega->responsable_id = new ObjectId($datos['responsable_id']);
        }

        if (! empty($datos['tipo_responsable'])) {
            $entrega->tipo_responsable = TipoResponsable::from($datos['tipo_responsable']);
        }

        $nuevoEstado = EstadoEntrega::from($datos['estado_entrega']);
        $entrega->estado_entrega = $nuevoEstado;

        if (! empty($datos['fecha_programada'])) {
            $entrega->fecha_programada = Carbon::parse($datos['fecha_programada']);
        }

        if ($nuevoEstado === EstadoEntrega::Entregado) {
            $entrega->fecha_entrega = ! empty($datos['fecha_entrega']) ? Carbon::parse($datos['fecha_entrega']) : now();
        } elseif ($nuevoEstado === EstadoEntrega::Cancelada) {
            $entrega->fecha_entrega = null;
        }

        if (array_key_exists('observaciones', $datos)) {
            $entrega->observaciones = $datos['observaciones'] ?? '';
        }

        $this->entregas->guardar($entrega);

        // Si la entrega pasa a entregado, actualizar también el estado del pedido
        $pedido = $this->pedidos->encontrarOFallar((string) $entrega->pedido_id);
        if ($nuevoEstado === EstadoEntrega::Entregado && $pedido->estado !== EstadoPedido::Entregado) {
            $pedido->estado = EstadoPedido::Entregado;
            $this->pedidos->guardar($pedido);
        }

        return $entrega;
    }
}

