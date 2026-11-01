<?php

declare(strict_types=1);

namespace App\Http\Controllers\Gestion;

use App\Http\Controllers\Controller;
use App\Http\Requests\FiltrosProductosRequest;
use App\Http\Requests\Gestion\ProductoRequest;
use App\Models\Producto;
use App\Repositories\Contracts\CategoriaRepositorio;
use App\Repositories\Contracts\ProductoRepositorio;
use App\Repositories\Contracts\ProductorRepositorio;
use App\Services\Productos\AlcanceProductor;
use App\Services\Productos\ProductoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class ProductoController extends Controller
{
    public function __construct(
        private readonly ProductoService $servicio,
        private readonly ProductoRepositorio $productos,
        private readonly ProductorRepositorio $productores,
        private readonly CategoriaRepositorio $categorias,
        private readonly AlcanceProductor $alcance,
    ) {}

    public function index(FiltrosProductosRequest $request): View
    {
        $alcance = $this->alcance->idsDe($request->user());

        return view('gestion.productos.index', [
            'productos' => $this->productos->buscar($request->filtros($alcance), 10),
            'fincas' => $this->fincasDelActor($request),
            'filtros' => $request->validated(),
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Producto::class);

        return view('gestion.productos.formulario', [
            'producto' => new Producto,
            'fincas' => $this->fincasDelActor($request),
            'categorias' => $this->categorias->activas(),
        ]);
    }

    public function store(ProductoRequest $request): RedirectResponse
    {
        $producto = $this->servicio->crear($request->validated());

        return redirect()->route('gestion.productos.index')
            ->with('estado', "Producto «{$producto->nombre}» creado.");
    }

    public function edit(Request $request, Producto $producto): View
    {
        Gate::authorize('update', $producto);

        return view('gestion.productos.formulario', [
            'producto' => $producto,
            'fincas' => $this->fincasDelActor($request),
            'categorias' => $this->categorias->activas(),
        ]);
    }

    public function update(ProductoRequest $request, Producto $producto): RedirectResponse
    {
        $this->servicio->actualizar($producto, $request->validated());

        return redirect()->route('gestion.productos.index')
            ->with('estado', "Producto «{$producto->nombre}» actualizado.");
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        Gate::authorize('delete', $producto);

        $this->servicio->eliminar($producto);

        return redirect()->route('gestion.productos.index')
            ->with('estado', "Producto «{$producto->nombre}» eliminado.");
    }

    /** @return \Illuminate\Support\Collection<int, \App\Models\Productor> */
    private function fincasDelActor(Request $request)
    {
        $ids = $this->alcance->idsDe($request->user());

        return $ids === null
            ? $this->productores->todos()
            : $this->productores->delUsuario((string) $request->user()->getKey());
    }
}

