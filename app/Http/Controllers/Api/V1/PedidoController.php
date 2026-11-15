<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\EstadoEntrega;
use App\Enums\EstadoPago;
use App\Enums\EstadoPedido;
use App\Enums\MetodoPago;
use App\Enums\Rol;
use App\Enums\TipoEntrega;
use App\Enums\TipoResponsable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CrearPedidoApiRequest;
use App\Models\Entrega;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Usuario;
use App\Repositories\Contracts\EntregaRepositorio;
use App\Repositories\Contracts\PagoRepositorio;
use App\Repositories\Contracts\PedidoRepositorio;
use App\Repositories\Contracts\ProductoRepositorio;
use App\Services\Auditoria\AuditoriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MongoDB\BSON\Decimal128;
use MongoDB\BSON\ObjectId;

class PedidoController extends Controller
{
    public function __construct(
        private readonly PedidoRepositorio $pedidos,
        private readonly ProductoRepositorio $productos,
        private readonly PagoRepositorio $pagos,
        private readonly EntregaRepositorio $entregas,
        private readonly AuditoriaService $auditoria,
    ) {}

    /**
     * Listado de pedidos del usuario autenticado (o de todos si es Administrador).
     */
    public function index(Request $request): JsonResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        if ($usuario->tieneRol(Rol::Administrador)) {
            $pedidosList = $this->pedidos->todos();
        } else {
            $pedidosList = $this->pedidos->delConsumidor((string) $usuario->getKey());
        }

        $formateados = $pedidosList->map(function (Pedido $p) {
            return [
                'id' => (string) $p->getKey(),
                'numero_pedido' => $p->numero_pedido,
                'fecha' => $p->fecha?->toIso8601String(),
                'estado' => $p->estado->value,
                'total' => (float) (string) $p->total,
                'total_formateado' => '$' . number_format((float) (string) $p->total, 0, ',', '.') . ' COP',
                'cantidad_items' => count($p->items ?? []),
                'tipo_entrega' => $p->tipo_entrega?->value,
                'municipio' => $p->direccion_entrega['municipio'] ?? '',
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $formateados,
        ], 200);
    }

    /**
     * Detalle de un pedido específico por ID.
     */
    public function show(string $id, Request $request): JsonResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        try {
            $pedido = $this->pedidos->encontrarOFallar($id);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => "Pedido con ID {$id} no encontrado.",
            ], 404);
        }

        // Control de autorización (Policies / Roles):
        $esDuenio = (string) $pedido->consumidor_id === (string) $usuario->getKey();
        $esAdmin = $usuario->tieneRol(Rol::Administrador);

        if (! $esDuenio && ! $esAdmin) {
            return response()->json([
                'status' => 'error',
                'message' => 'No tiene permisos para acceder a este pedido.',
            ], 403);
        }

        $pago = $this->pagos->delPedido((string) $pedido->getKey())->first();
        $entrega = $this->entregas->delPedido((string) $pedido->getKey());

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => (string) $pedido->getKey(),
                'numero_pedido' => $pedido->numero_pedido,
                'fecha' => $pedido->fecha?->toIso8601String(),
                'estado' => $pedido->estado->value,
                'subtotal' => (float) (string) $pedido->subtotal,
                'costo_envio' => (float) (string) $pedido->costo_envio,
                'total' => (float) (string) $pedido->total,
                'total_formateado' => '$' . number_format((float) (string) $pedido->total, 0, ',', '.') . ' COP',
                'items' => $pedido->items ?? [],
                'direccion_entrega' => $pedido->direccion_entrega,
                'pago' => $pago ? [
                    'metodo' => $pago->metodo_pago->value,
                    'estado' => $pago->estado->value,
                    'referencia' => $pago->referencia_transaccion,
                    'fecha_pago' => $pago->fecha_pago?->toIso8601String(),
                ] : null,
                'entrega' => $entrega ? [
                    'estado' => $entrega->estado_entrega->value,
                    'fecha_programada' => $entrega->fecha_programada?->toIso8601String(),
                    'fecha_entrega' => $entrega->fecha_entrega?->toIso8601String(),
                ] : null,
            ],
        ], 200);
    }

    /**
     * Creación de un pedido campesino vía API REST.
     */
    public function store(CrearPedidoApiRequest $request): JsonResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        $datos = $request->validated();

        $itemsPayload = $datos['items'];
        $subtotalAcumulado = '0.00';
        $itemsEmbebidos = [];
        $primerProductorId = null;

        // 1. Validar existencias de cada producto
        foreach ($itemsPayload as $itemData) {
            $producto = $this->productos->encontrarOFallar($itemData['producto_id']);
            $cantidad = (int) $itemData['cantidad'];

            if ($producto->cantidadDisponible() < $cantidad) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Stock insuficiente para el producto '{$producto->nombre}'. Disponible: {$producto->cantidadDisponible()}.",
                ], 422);
            }

            $precioStr = (string) $producto->precio;
            $subtotalLinea = bcmul($precioStr, (string) $cantidad, 2);
            $subtotalAcumulado = bcadd($subtotalAcumulado, $subtotalLinea, 2);

            if ($primerProductorId === null && $producto->productor_id) {
                $primerProductorId = $producto->productor_id;
            }

            $itemsEmbebidos[] = [
                'producto_id' => new ObjectId((string) $producto->getKey()),
                'nombre_producto' => $producto->nombre,
                'cantidad' => $cantidad,
                'precio_unitario' => new Decimal128($precioStr),
                'subtotal' => new Decimal128($subtotalLinea),
            ];

            // Descontar inventario
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

        $costoEnvio = '4000.00';
        $total = bcadd($subtotalAcumulado, $costoEnvio, 2);

        // 2. Persistir Pedido
        $pedido = new Pedido;
        $pedido->consumidor_id = new ObjectId((string) $usuario->getKey());
        $pedido->fecha = now();
        $pedido->estado = EstadoPedido::Pendiente;
        $pedido->tipo_entrega = TipoEntrega::Domicilio;
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

        // 3. Registrar Pago inicial
        $metodo = MetodoPago::tryFrom($datos['metodo_pago']) ?? MetodoPago::Efectivo;
        $pago = new Pago;
        $pago->pedido_id = $pedidoId;
        $pago->metodo_pago = $metodo;
        $pago->valor = $total;
        $pago->estado = EstadoPago::Pendiente;
        $pago->fecha_pago = now();
        $pago->referencia_transaccion = 'TXN-' . strtoupper(bin2hex(random_bytes(4))) . '-PEPEPS';
        $this->pagos->guardar($pago);

        // 4. Registrar Entrega inicial
        $entrega = new Entrega;
        $entrega->pedido_id = $pedidoId;
        $entrega->responsable_id = $primerProductorId ?? new ObjectId((string) $usuario->getKey());
        $entrega->tipo_responsable = TipoResponsable::Transportador;
        $entrega->estado_entrega = EstadoEntrega::Programada;
        $entrega->fecha_programada = now()->addDays(2);
        $entrega->fecha_entrega = null;
        $entrega->observaciones = 'Pedido registrado vía API REST.';
        $this->entregas->guardar($entrega);

        // 5. Auditoría
        $this->auditoria->registrarCambio(
            $usuario,
            'pedidos',
            (string) $pedido->getKey(),
            'creacion_api',
            'estado',
            null,
            EstadoPedido::Pendiente->value,
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Pedido creado satisfactoriamente en PEPEPS.',
            'data' => [
                'id' => (string) $pedido->getKey(),
                'numero_pedido' => $pedido->numero_pedido,
                'total' => (float) $total,
                'total_formateado' => '$' . number_format((float) $total, 0, ',', '.') . ' COP',
                'estado' => $pedido->estado->value,
                'pago' => [
                    'metodo' => $pago->metodo_pago->value,
                    'referencia' => $pago->referencia_transaccion,
                    'estado' => $pago->estado->value,
                ],
            ],
        ], 201);
    }
}
