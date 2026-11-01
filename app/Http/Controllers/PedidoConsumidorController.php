<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\MetodoPago;
use App\Http\Requests\Calificaciones\CalificarEntregaRequest;
use App\Http\Requests\Calificaciones\CalificarItemRequest;
use App\Models\Entrega;
use App\Models\Pedido;
use App\Repositories\Contracts\EntregaRepositorio;
use App\Repositories\Contracts\PagoRepositorio;
use App\Repositories\Contracts\PedidoRepositorio;
use App\Services\Calificaciones\CalificacionService;
use App\Services\Pagos\PagoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class PedidoConsumidorController extends Controller
{
    public function __construct(
        private readonly PedidoRepositorio $pedidos,
        private readonly PagoRepositorio $pagos,
        private readonly EntregaRepositorio $entregas,
    ) {}

    public function index(Request $request): View
    {
        $consumidorId = (string) $request->user()->getKey();

        return view('pedidos.index', [
            'pedidos' => $this->pedidos->delConsumidor($consumidorId, 10),
        ]);
    }

    public function show(Pedido $pedido): View
    {
        Gate::authorize('view', $pedido);

        $pedidoId = (string) $pedido->getKey();

        return view('pedidos.show', [
            'pedido' => $pedido,
            'pagos' => $this->pagos->delPedido($pedidoId),
            'entrega' => $this->entregas->delPedido($pedidoId),
        ]);
    }

    public function registrarPago(Request $request, Pedido $pedido, PagoService $pagoService): RedirectResponse
    {
        Gate::authorize('view', $pedido);

        $request->validate([
            'metodo_pago' => ['required', 'in:PSE,tarjeta_credito,tarjeta_debito,transferencia,efectivo'],
        ]);

        $metodo = MetodoPago::from($request->string('metodo_pago')->toString());
        $pagoService->registrarPago($pedido, $metodo);

        return back()->with('estado', 'Nuevo comprobante de pago registrado con éxito.');
    }

    public function calificarItem(CalificarItemRequest $request, Pedido $pedido, CalificacionService $servicio): RedirectResponse
    {
        $servicio->calificarItem(
            $pedido,
            $request->string('producto_id')->toString(),
            $request->integer('calificacion'),
            $request->input('comentario'),
        );

        return back()->with('estado', '¡Gracias por calificar el producto!');
    }

    public function calificarEntrega(CalificarEntregaRequest $request, Entrega $entrega, CalificacionService $servicio): RedirectResponse
    {
        $servicio->calificarEntrega(
            $entrega,
            $request->integer('calificacion'),
            $request->input('comentario'),
        );

        return back()->with('estado', '¡Gracias por calificar el servicio de entrega!');
    }
}

