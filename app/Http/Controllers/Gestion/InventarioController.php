<?php

declare(strict_types=1);

namespace App\Http\Controllers\Gestion;

use App\Http\Controllers\Controller;
use App\Http\Requests\FiltrosProductosRequest;
use App\Http\Requests\Gestion\InventarioRequest;
use App\Models\Producto;
use App\Repositories\Contracts\ProductoRepositorio;
use App\Repositories\Contracts\ProductorRepositorio;
use App\Services\Productos\AlcanceProductor;
use App\Services\Productos\InventarioService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

final class InventarioController extends Controller
{
    public function __construct(
        private readonly InventarioService $inventario,
        private readonly ProductoRepositorio $productos,
        private readonly ProductorRepositorio $productores,
        private readonly AlcanceProductor $alcance,
    ) {}

    /** Inventario completo por productor, con filtro de productos críticos. */
    public function index(FiltrosProductosRequest $request): View
    {
        $usuario = $request->user();
        $alcance = $this->alcance->idsDe($usuario);

        return view('gestion.inventario.index', [
            'productos' => $this->productos->buscar($request->filtros($alcance), 10),
            'fincas' => $alcance === null
                ? $this->productores->todos()
                : $this->productores->delUsuario((string) $usuario->getKey()),
            'criticos' => $this->productos->criticos($alcance),
            'filtros' => $request->validated(),
        ]);
    }

    public function actualizar(InventarioRequest $request, Producto $producto): RedirectResponse
    {
        $this->inventario->actualizar($producto, $request->validated());

        return back()->with('estado', "Inventario de «{$producto->nombre}» actualizado.");
    }
}

