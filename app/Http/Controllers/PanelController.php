<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Repositories\Contracts\ProductoRepositorio;
use App\Repositories\Contracts\ProductorRepositorio;
use App\Services\Productos\AlcanceProductor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PanelController extends Controller
{
    /** Envía a cada usuario a su panel según el rol. */
    public function inicio(Request $request): RedirectResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        return redirect()->route($usuario->rol->rutaPanel());
    }

    public function administrador(): View
    {
        return view('panel.administrador');
    }

    public function productor(
        Request $request,
        ProductorRepositorio $productores,
        ProductoRepositorio $productos,
        AlcanceProductor $alcance,
    ): View {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        return view('panel.productor', [
            'fincas' => $productores->delUsuario((string) $usuario->getKey()),
            // Alerta de stock bajo: productos agotados o con 10 unidades o menos.
            'criticos' => $productos->criticos($alcance->idsDe($usuario)),
        ]);
    }

    public function consumidor(): View
    {
        return view('panel.consumidor');
    }
}
