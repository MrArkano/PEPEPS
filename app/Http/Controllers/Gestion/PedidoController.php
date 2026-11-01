<?php

declare(strict_types=1);

namespace App\Http\Controllers\Gestion;

use App\Enums\EstadoPago;
use App\Enums\EstadoPedido;
use App\Enums\Rol;
use App\Http\Controllers\Controller;
use App\Http\Requests\Entregas\ActualizarEntregaRequest;
use App\Http\Requests\Pagos\ConciliarPagoRequest;
use App\Http\Requests\Pedidos\ActualizarEstadoPedidoRequest;
use App\Models\Entrega;
use App\Models\Pago;
use App\Models\Pedido;
use App\Repositories\Contracts\EntregaRepositorio;
use App\Repositories\Contracts\PagoRepositorio;
use App\Repositories\Contracts\PedidoRepositorio;
use App\Repositories\Contracts\ProductorRepositorio;
use App\Services\Entregas\EntregaService;
use App\Services\Pagos\PagoService;
use App\Services\Pedidos\PedidoService;
use App\Services\Productos\AlcanceProductor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class PedidoController extends Controller
{
    public function __construct(
        private readonly PedidoRepositorio $pedidos,
        private readonly PagoRepositorio $pagos,
        private readonly EntregaRepositorio $entregas,
        private readonly ProductorRepositorio $productores,
        private readonly AlcanceProductor $alcance,
    ) {}

    public function index(Request $request): View
    {
        $usuario = $request->user();
        $fincasIds = $this->alcance->idsDe($usuario);
        $estado = $request->input('estado');

        return view('gestion.pedidos.index', [
            'pedidos' => $this->pedidos->paraProductores($fincasIds, $estado, 10),
            'pendientes' => $this->pedidos->pendientes($fincasIds),
            'estadoSeleccionado' => $estado,
        ]);
    }

    public function show(Pedido $pedido): View
    {
        Gate::authorize('view', $pedido);

        $pedidoId = (string) $pedido->getKey();

        return view('gestion.pedidos.show', [
            'pedido' => $pedido,
            'pagos' => $this->pagos->delPedido($pedidoId),
            'entrega' => $this->entregas->delPedido($pedidoId),
            'productores' => $this->productores->todos(),
        ]);
    }

    public function cambiarEstado(ActualizarEstadoPedidoRequest $request, Pedido $pedido, PedidoService $servicio): RedirectResponse
    {
        $nuevoEstado = EstadoPedido::from($request->string('estado')->toString());
        $servicio->cambiarEstado($pedido, $nuevoEstado, $request->user());

        return back()->with('estado', "Estado del pedido actualizado a «{$nuevoEstado->value}».");
    }

    public function conciliarPago(ConciliarPagoRequest $request, Pago $pago, PagoService $servicio): RedirectResponse
    {
        $nuevoEstado = EstadoPago::from($request->string('estado')->toString());
        $servicio->conciliar($pago, $nuevoEstado);

        return back()->with('estado', "Pago conciliado con éxito (Estado: {$nuevoEstado->value}).");
    }

    public function actualizarEntrega(ActualizarEntregaRequest $request, Entrega $entrega, EntregaService $servicio): RedirectResponse
    {
        $servicio->actualizarLogistica($entrega, $request->validated());

        return back()->with('estado', 'Logística de entrega actualizada.');
    }
}

