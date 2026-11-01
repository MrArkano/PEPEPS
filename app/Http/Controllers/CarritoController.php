<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Carrito\ActualizarCantidadRequest;
use App\Http\Requests\Carrito\AgregarItemRequest;
use App\Http\Requests\Pedidos\CheckoutRequest;
use App\Models\Producto;
use App\Repositories\Contracts\ProductorRepositorio;
use App\Services\Pedidos\CarritoService;
use App\Services\Pedidos\PedidoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class CarritoController extends Controller
{
    public function __construct(private readonly CarritoService $carrito) {}

    public function index(): View
    {
        return view('carrito.index', [
            'detalle' => $this->carrito->detalle(),
            'subtotal' => $this->carrito->subtotal(),
        ]);
    }

    public function agregar(AgregarItemRequest $request): RedirectResponse
    {
        $productoId = $request->string('producto_id')->toString();
        $cantidad = $request->integer('cantidad', 1);

        $this->carrito->agregar($productoId, $cantidad);

        return back()->with('estado', 'Producto añadido al carrito.');
    }

    public function actualizar(ActualizarCantidadRequest $request, Producto|string $producto): RedirectResponse
    {
        $id = $producto instanceof Producto ? (string) $producto->getKey() : (string) $producto;
        $this->carrito->actualizar($id, $request->integer('cantidad'));

        return redirect()->route('carrito.index')->with('estado', 'Carrito actualizado.');
    }

    public function remover(Producto|string $producto): RedirectResponse
    {
        $id = $producto instanceof Producto ? (string) $producto->getKey() : (string) $producto;
        $this->carrito->remover($id);

        return redirect()->route('carrito.index')->with('estado', 'Producto retirado del carrito.');
    }

    public function vaciar(): RedirectResponse
    {
        $this->carrito->vaciar();

        return redirect()->route('carrito.index')->with('estado', 'Carrito vaciado.');
    }

    public function checkout(Request $request, ProductorRepositorio $productores): View|RedirectResponse
    {
        if ($this->carrito->totalItems() === 0) {
            return redirect()->route('catalogo.index')->with('error', 'Tu carrito está vacío. Agrega productos antes de comprar.');
        }

        $usuario = $request->user();
        $direccionesGuardadas = $usuario->direcciones ?? [];

        return view('carrito.checkout', [
            'detalle' => $this->carrito->detalle(),
            'subtotal' => $this->carrito->subtotal(),
            'municipios' => $productores->municipios(),
            'direccionesGuardadas' => $direccionesGuardadas,
        ]);
    }

    public function procesarCheckout(CheckoutRequest $request, PedidoService $servicio): RedirectResponse
    {
        $pedido = $servicio->crearPedido(
            $request->user(),
            $request->validated(),
            $this->carrito,
        );

        return redirect()->route('pedidos.show', $pedido->getKey())
            ->with('estado', '¡Tu pedido ha sido registrado con éxito!');
    }
}
