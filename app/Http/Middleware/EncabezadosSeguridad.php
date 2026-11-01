<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Encabezados de seguridad HTTP estrictos (CSP, HSTS, anti-clickjacking...). */
final class EncabezadosSeguridad
{
    private const CSP = "default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; "
        ."font-src 'self'; connect-src 'self'; object-src 'none'; base-uri 'self'; "
        ."form-action 'self'; frame-ancestors 'none'";

    public function handle(Request $request, Closure $next): Response
    {
        $respuesta = $next($request);

        $respuesta->headers->set('X-Content-Type-Options', 'nosniff');
        $respuesta->headers->set('X-Frame-Options', 'DENY');
        $respuesta->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $respuesta->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $respuesta->headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        // Con el servidor de desarrollo de Vite (public/hot) la CSP estricta lo bloquearía.
        if (! is_file(public_path('hot'))) {
            $respuesta->headers->set('Content-Security-Policy', self::CSP);
        }

        if ($request->isSecure()) {
            $respuesta->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $respuesta;
    }
}

