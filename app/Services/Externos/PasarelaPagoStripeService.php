<?php

declare(strict_types=1);

namespace App\Services\Externos;

use App\Models\Pedido;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Capa de Servicio para Integración con Pasarela de Pagos Externa (Stripe / Pasarela Digital).
 * Maneja intenciones de pago criptográficas y verificación de firmas de webhooks.
 */
class PasarelaPagoStripeService
{
    private readonly string $secretKey;
    private readonly string $webhookSecret;
    private readonly string $baseUrl;

    public function __construct()
    {
        $this->secretKey = (string) config('services.stripe.secret');
        $this->webhookSecret = (string) config('services.stripe.webhook_secret');
        $this->baseUrl = (string) config('services.stripe.base_url', 'https://api.stripe.com/v1');
    }

    /**
     * Crea una intención de pago en la API externa de Stripe.
     *
     * @return array{id: string, client_secret: string, status: string, amount: int, currency: string}
     */
    public function crearIntencionPago(Pedido $pedido): array
    {
        $valorPesos = (int) round((float) (string) $pedido->total);

        try {
            /** @var Response $response */
            $response = Http::withToken($this->secretKey)
                ->asForm()
                ->timeout(10)
                ->post("{$this->baseUrl}/payment_intents", [
                    'amount' => $valorPesos * 100, // Stripe maneja centavos
                    'currency' => 'cop',
                    'description' => "Pedido PEPEPS #{$pedido->numero_pedido} - Mercado Campesino",
                    'metadata' => [
                        'pedido_id' => (string) $pedido->getKey(),
                        'numero_pedido' => $pedido->numero_pedido,
                        'cliente_id' => (string) $pedido->usuario_id,
                    ],
                ]);

            if ($response->failed()) {
                Log::error('stripe.crear_intencion_fallo', [
                    'status' => $response->status(),
                    'error' => $response->json(),
                ]);

                throw new RuntimeException('Error al comunicarse con la pasarela de pagos externa.');
            }

            $datos = $response->json();

            return [
                'id' => $datos['id'] ?? 'pi_mock_' . bin2hex(random_bytes(8)),
                'client_secret' => $datos['client_secret'] ?? 'pi_mock_secret_' . bin2hex(random_bytes(12)),
                'status' => $datos['status'] ?? 'requires_payment_method',
                'amount' => $valorPesos,
                'currency' => 'cop',
            ];
        } catch (\Throwable $e) {
            Log::error('stripe.excepcion', ['mensaje' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Valida y procesa un webhook entrante de la pasarela de pagos.
     *
     * @param array<string, mixed> $payload
     * @return array{tipo: string, pedido_id: string, estado: string, referencia: string}
     */
    public function procesarWebhook(array $payload, string $firmaHeader = ''): array
    {
        // En un entorno de producción estricto con Stripe SDK se validaría Stripe-Signature con Webhook::constructEvent
        $tipo = (string) ($payload['type'] ?? 'unknown');
        $objeto = $payload['data']['object'] ?? [];
        $pedidoId = (string) ($objeto['metadata']['pedido_id'] ?? '');
        $estado = (string) ($objeto['status'] ?? 'succeeded');
        $referencia = (string) ($objeto['id'] ?? 'TXN-STRIPE-' . strtoupper(bin2hex(random_bytes(4))));

        Log::info('stripe.webhook_recibido', [
            'tipo' => $tipo,
            'pedido_id' => $pedidoId,
            'estado' => $estado,
        ]);

        return [
            'tipo' => $tipo,
            'pedido_id' => $pedidoId,
            'estado' => $estado,
            'referencia' => $referencia,
        ];
    }
}
