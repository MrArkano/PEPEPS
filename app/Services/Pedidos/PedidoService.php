<?php

declare(strict_types=1);

namespace App\Services\Pedidos;

use App\Enums\EstadoEntrega;
use App\Enums\EstadoPago;
use App\Enums\EstadoPedido;
use App\Enums\MetodoPago;
use App\Enums\TipoEntrega;
use App\Enums\TipoResponsable;
use App\Exceptions\Pedidos\CarritoVacio;
use App\Exceptions\Pedidos\StockInsuficiente;
use App\Models\Entrega;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Usuario;
use App\Repositories\Contracts\EntregaRepositorio;
use App\Repositories\Contracts\PagoRepositorio;
use App\Repositories\Contracts\PedidoRepositorio;
use App\Repositories\Contracts\ProductoRepositorio;
use App\Services\Auditoria\AuditoriaService;
use MongoDB\BSON\Decimal128;
use MongoDB\BSON\ObjectId;

final class PedidoService
{
    private const TARIFA_ENVIO_BASE = '4000.00';

    public function __construct(
        private readonly PedidoRepositorio $pedidos,
        private readonly ProductoRepositorio $productos,
        private readonly PagoRepositorio $pagos,
        private readonly EntregaRepositorio $entregas,
        private readonly AuditoriaService $auditoria,
    ) {}

    /**
     * @param array{
     *     tipo_entrega: string,
     *     metodo_pago: string,
     *     direccion: string,
     *     municipio: string,
     *     barrio?: string|null,
     *     referencia?: string|null,
     * } $datos
     */
    public function crearPedido(Usuario $consumidor, array $datos, CarritoService $carrito): Pedido
    {
        $detalle = $carrito->detalle();
        if ($detalle->isEmpty()) {
            throw new CarritoVacio;
        }

        // 1. Validar existencias de todos los ítems antes de procesar
        foreach ($detalle as $linea) {
            $producto = $linea['producto'];
            $cantidad = $linea['cantidad'];
            $disponible = $producto->cantidadDisponible();

            if ($disponible < $cantidad) {
                throw new StockInsuficiente($producto->nombre, $disponible);
            }
        }

        // 2. Construir ítems embebidos con snapshot de nombre y precio
        $itemsEmbebidos = [];
        $subtotalAcumulado = '0.00';
        $primerProductorId = null;

        foreach ($detalle as $linea) {
            $producto = $linea['producto'];
            $cantidad = (int) $linea['cantidad'];
            $precioStr = (string) $producto->precio;
            $subtotalItemStr = bcmul($precioStr, (string) $cantidad, 2);

            $subtotalAcumulado = bcadd($subtotalAcumulado, $subtotalItemStr, 2);

            if ($primerProductorId === null && $producto->productor_id) {
                $primerProductorId = $producto->productor_id;
            }

            $itemsEmbebidos[] = [
                'producto_id' => new ObjectId((string) $producto->getKey()),
                'nombre_producto' => $producto->nombre,
                'cantidad' => $cantidad,
                'precio_unitario' => new Decimal128($precioStr),
                'subtotal' => new Decimal128($subtotalItemStr),
            ];

            // 3. Descontar inventario
            $nuevoStock = $producto->cantidadDisponible() - $cantidad;
            $inv = $producto->inventario ?? [];
            $inv['cantidad_disponible'] = max(0, $nuevoStock);
            if ($nuevoStock <= 0) {
                $inv['estado'] = 'agotado';
            }
            $inv['fecha_actualizacion'] = now();
            $producto->inventario = $inv;
            $this->productos->guardar($producto);
        }

        $tipoEntregaEnum = TipoEntrega::from($datos['tipo_entrega']);
        $costoEnvio = $tipoEntregaEnum === TipoEntrega::Domicilio ? self::TARIFA_ENVIO_BASE : '0.00';
        $total = bcadd($subtotalAcumulado, $costoEnvio, 2);

        // 4. Instanciar y persistir Pedido
        $pedido = new Pedido;
        $pedido->consumidor_id = new ObjectId((string) $consumidor->getKey());
        $pedido->fecha = now();
        $pedido->estado = EstadoPedido::Pendiente;
        $pedido->tipo_entrega = $tipoEntregaEnum;
        $pedido->items = $itemsEmbebidos;
        $pedido->direccion_entrega = [
            'direccion' => $datos['direccion'],
            'municipio' => $datos['municipio'],
            'barrio' => $datos['barrio'] ?? '',
            'referencia' => $datos['referencia'] ?? '',
        ];
        $pedido->subtotal = $subtotalAcumulado;
        $pedido->costo_envio = $costoEnvio;
        $pedido->total = $total;

        $this->pedidos->guardar($pedido);
        $pedidoId = new ObjectId((string) $pedido->getKey());

        // 5. Registrar Pago inicial asociado
        $pago = new Pago;
        $pago->pedido_id = $pedidoId;
        $pago->metodo_pago = MetodoPago::from($datos['metodo_pago']);
        $pago->valor = $total;
        $pago->estado = EstadoPago::Pendiente;
        $pago->fecha_pago = now();
        $pago->referencia_transaccion = 'TXN-' . strtoupper(bin2hex(random_bytes(4))) . '-PEPEPS';
        $this->pagos->guardar($pago);

        // 6. Registrar Entrega asociada
        $entrega = new Entrega;
        $entrega->pedido_id = $pedidoId;
        // Si es recogida en punto, el responsable es el productor; si es domicilio, se asigna como productor inicial o transportador
        $entrega->responsable_id = $primerProductorId ?? new ObjectId((string) $consumidor->getKey());
        $entrega->tipo_responsable = $tipoEntregaEnum === TipoEntrega::RecogidaPunto ? TipoResponsable::Productor : TipoResponsable::Transportador;
        $entrega->estado_entrega = EstadoEntrega::Programada;
        $entrega->fecha_programada = now()->addDays(2);
        $entrega->fecha_entrega = null;
        $entrega->observaciones = 'Pedido registrado en plataforma.';
        $this->entregas->guardar($entrega);

        // 7. Vaciar carrito
        $carrito->vaciar();

        return $pedido;
    }

    public function cambiarEstado(Pedido $pedido, EstadoPedido $nuevoEstado, ?Usuario $actor = null): void
    {
        $estadoAnterior = $pedido->estado->value;

        if ($estadoAnterior !== $nuevoEstado->value) {
            $this->auditoria->registrarCambio(
                $actor ?? auth()->user(),
                'pedidos',
                (string) $pedido->getKey(),
                'actualizacion_estado',
                'estado',
                $estadoAnterior,
                $nuevoEstado->value,
            );
        }

        $pedido->estado = $nuevoEstado;
        $this->pedidos->guardar($pedido);

        // Si el pedido se marca como entregado, sincronizar la entrega correspondiente si está programada o en camino
        if ($nuevoEstado === EstadoPedido::Entregado) {
            $entrega = $this->entregas->delPedido((string) $pedido->getKey());
            if ($entrega && $entrega->estado_entrega !== EstadoEntrega::Entregado) {
                $entrega->estado_entrega = EstadoEntrega::Entregado;
                $entrega->fecha_entrega = now();
                $this->entregas->guardar($entrega);
            }
        }

        // Si el pedido se cancela, cancelar la entrega
        if ($nuevoEstado === EstadoPedido::Cancelado) {
            $entrega = $this->entregas->delPedido((string) $pedido->getKey());
            if ($entrega && $entrega->estado_entrega !== EstadoEntrega::Cancelada) {
                $entrega->estado_entrega = EstadoEntrega::Cancelada;
                $this->entregas->guardar($entrega);
            }
        }
    }
}

