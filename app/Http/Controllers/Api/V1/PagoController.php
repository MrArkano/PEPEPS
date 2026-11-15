<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\EstadoPago;
use App\Enums\Rol;
use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Repositories\Contracts\PagoRepositorio;
use App\Repositories\Contracts\PedidoRepositorio;
use App\Services\Externos\PasarelaPagoStripeService;
use App\Services\Pagos\PagoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    public function __construct(
        private readonly PedidoRepositorio $pedidos,
        private readonly PagoRepositorio $pagos,
        private readonly PagoService $pagoService,
        private readonly PasarelaPagoStripeService $pasarelaService,
    ) {}

    /**
     * Inicia una intención de pago con la API externa de Stripe para un pedido existente.
     */
    public function checkoutIntent(string $pedidoId, Request $request): JsonResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        try {
            $pedido = $this->pedidos->encontrarOFallar($pedidoId);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => "Pedido con ID {$pedidoId} no encontrado.",
            ], 404);
        }

        $esDuenio = (string) $pedido->consumidor_id === (string) $usuario->getKey();
        $esAdmin = $usuario->tieneRol(Rol::Administrador);

        if (! $esDuenio && ! $esAdmin) {
            return response()->json([
                'status' => 'error',
                'message' => 'No tiene autorización para procesar el pago de este pedido.',
            ], 403);
        }

        try {
            $intencion = $this->pasarelaService->crearIntencionPago($pedido);

            return response()->json([
                'status' => 'success',
                'message' => 'Intención de pago generada exitosamente con la pasarela externa.',
                'data' => [
                    'payment_intent_id' => $intencion['id'],
                    'client_secret' => $intencion['client_secret'],
                    'amount' => $intencion['amount'],
                    'currency' => $intencion['currency'],
                    'pedido_id' => $pedidoId,
                    'numero_pedido' => $pedido->numero_pedido,
                ],
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Fallo de comunicación con la pasarela de pagos externa.',
            ], 500);
        }
    }

    /**
     * Webhook asíncrono para recibir confirmaciones de pago desde la pasarela externa.
     * Exento de CSRF.
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        $firma = (string) $request->header('Stripe-Signature', '');

        $resultado = $this->pasarelaService->procesarWebhook($payload, $firma);

        if ($resultado['tipo'] === 'payment_intent.succeeded' && $resultado['pedido_id'] !== '') {
            $pago = $this->pagos->delPedido($resultado['pedido_id'])->first();
            if ($pago) {
                $this->pagoService->conciliar($pago, EstadoPago::Aprobado);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Webhook procesado correctamente.',
            'event' => $resultado['tipo'],
        ], 200);
    }
}
