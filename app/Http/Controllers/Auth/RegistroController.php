<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegistroRequest;
use App\Services\Auth\AutenticacionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

final class RegistroController extends Controller
{
    public function __construct(private readonly AutenticacionService $autenticacion) {}

    public function crear(): View
    {
        return view('auth.registro');
    }

    public function almacenar(RegistroRequest $request): RedirectResponse
    {
        $this->autenticacion->registrarConsumidor($request->validated());

        return redirect()->route('login')
            ->with('estado', 'Cuenta creada. Ya puedes iniciar sesión.');
    }
}

