<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\FiltrosProductosRequest;
use App\Models\Producto;
use App\Services\Catalogo\CatalogoService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

/** Catálogo público: búsqueda, filtros y ficha de trazabilidad. */
final class CatalogoController extends Controller
{
    public function __construct(private readonly CatalogoService $catalogo) {}

    public function index(FiltrosProductosRequest $request): View
    {
        return view('catalogo.index', [
            'pagina' => $this->catalogo->pagina($request->filtros()),
            'filtros' => $request->validated(),
        ]);
    }

    public function show(string $id): View
    {
        $ficha = $this->catalogo->ficha($id);

        // Un producto inactivo solo lo ven quienes pueden gestionarlo.
        abort_unless(
            $ficha->producto->estado === 'activo' || Gate::allows('update', $ficha->producto),
            404,
        );

        return view('catalogo.show', ['ficha' => $ficha]);
    }
}
