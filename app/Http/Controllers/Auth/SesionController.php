<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AutenticacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

final class SesionController extends Controller
{
    public function __construct(private readonly AutenticacionService $autenticacion) {}

    public function crear(): View
    {
        return view('auth.ingresar');
    }

    public function almacenar(LoginRequest $request): RedirectResponse
    {
        $this->autenticacion->iniciarSesion(
            $request->string('correo')->toString(),
            $request->string('contrasena')->toString(),
            (string) $request->ip(),
        );

        // Previene fijación de sesión.
        $request->session()->regenerate();

        return redirect()->intended(route('panel'));
    }

    public function destruir(Request $request): RedirectResponse
    {
        $this->autenticacion->cerrarSesion();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('estado', 'Sesión cerrada correctamente.');
    }
}

