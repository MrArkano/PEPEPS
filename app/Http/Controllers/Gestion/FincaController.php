<?php

declare(strict_types=1);

namespace App\Http\Controllers\Gestion;

use App\Enums\Rol;
use App\Http\Controllers\Controller;
use App\Http\Requests\Gestion\FincaRequest;
use App\Models\Productor;
use App\Repositories\Contracts\ProductorRepositorio;
use App\Repositories\Contracts\UsuarioRepositorio;
use App\Services\Productos\FincaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class FincaController extends Controller
{
    public function __construct(
        private readonly FincaService $servicio,
        private readonly ProductorRepositorio $productores,
        private readonly UsuarioRepositorio $usuarios,
    ) {}

    public function index(Request $request): View
    {
        $usuario = $request->user();
        $soloPropias = ! $usuario->tieneRol(Rol::Administrador);

        return view('gestion.fincas.index', [
            'fincas' => $this->productores->paginar($soloPropias ? (string) $usuario->getKey() : null, 10),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Productor::class);

        return view('gestion.fincas.formulario', [
            'finca' => new Productor,
            'productores' => $this->usuarios->conRol(Rol::Productor),
        ]);
    }

    public function store(FincaRequest $request): RedirectResponse
    {
        $finca = $this->servicio->crear($request->user(), $request->validated());

        return redirect()->route('gestion.fincas.index')
            ->with('estado', "Finca «{$finca->nombre_finca_asociacion}» creada.");
    }

    public function edit(Productor $finca): View
    {
        Gate::authorize('update', $finca);

        return view('gestion.fincas.formulario', [
            'finca' => $finca,
            'productores' => $this->usuarios->conRol(Rol::Productor),
        ]);
    }

    public function update(FincaRequest $request, Productor $finca): RedirectResponse
    {
        $this->servicio->actualizar($request->user(), $finca, $request->validated());

        return redirect()->route('gestion.fincas.index')
            ->with('estado', "Finca «{$finca->nombre_finca_asociacion}» actualizada.");
    }

    public function destroy(Productor $finca): RedirectResponse
    {
        Gate::authorize('delete', $finca);

        $this->servicio->eliminar($finca);

        return redirect()->route('gestion.fincas.index')
            ->with('estado', "Finca «{$finca->nombre_finca_asociacion}» eliminada.");
    }
}

