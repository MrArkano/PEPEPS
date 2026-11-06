<?php

use App\Exceptions\Auth\DemasiadosIntentos;
use App\Exceptions\Auth\ErrorAutenticacion;
use App\Exceptions\ErrorDeNegocio;
use App\Http\Middleware\EncabezadosSeguridad;
use App\Http\Middleware\RequiereRol;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->alias(['rol' => RequiereRol::class]);
        $middleware->appendToGroup('web', EncabezadosSeguridad::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Errores de reglas de negocio: se informan al usuario sin romper el flujo.
        $exceptions->render(function (ErrorDeNegocio $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 409);
            }

            return back()->with('error', $e->getMessage());
        });

        // Manejo centralizado de errores de autenticación del dominio.
        $exceptions->render(function (ErrorAutenticacion $e, Request $request) {
            $codigo = $e instanceof DemasiadosIntentos ? 429 : 422;

            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], $codigo);
            }

            return back()
                ->withInput($request->only('correo'))
                ->withErrors(['correo' => $e->getMessage()]);
        });
    })->create();
