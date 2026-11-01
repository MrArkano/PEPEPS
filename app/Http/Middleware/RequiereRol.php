<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\Rol;
use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Uso: ->middleware('rol:Productor,Consumidor').
 * El Administrador tiene control total, por lo que siempre pasa.
 * Cierra la sesión si la cuenta fue desactivada mientras estaba abierta.
 */
final class RequiereRol
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        /** @var Usuario|null $usuario */
        $usuario = $request->user();

        if ($usuario === null) {
            return redirect()->route('login');
        }

        if ($usuario->estado !== 'activo') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['correo' => 'Tu cuenta no está activa.']);
        }

        $permitidos = [Rol::Administrador, ...array_map(Rol::from(...), $roles)];

        abort_unless($usuario->tieneRol(...$permitidos), Response::HTTP_FORBIDDEN, 'No tienes permiso para acceder a esta sección.');

        return $next($request);
    }
}

