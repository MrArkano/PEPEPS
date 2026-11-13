<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Encabezados de seguridad HTTP estrictos alineados a ISO/IEC 27001:2022 / ISO/IEC 27002:2022
 * (Controles A.8.20, A.8.24, A.8.26 y A.8.28: mitigación OWASP Top 10, anti-clickjacking,
 * CSP nivel 3, HSTS preloaded y prevención de fugas de metadatos de servidor).
 */
final class EncabezadosSeguridad
{
    private const CSP = "default-src 'self'; "
        ."script-src 'self'; "
        ."style-src 'self'; "
        ."img-src 'self' data:; "
        ."font-src 'self'; "
        ."connect-src 'self'; "
        ."object-src 'none'; "
        ."base-uri 'self'; "
        ."form-action 'self'; "
        ."frame-ancestors 'none'";

    public function handle(Request $request, Closure $next): Response
    {
        $respuesta = $next($request);

        // Previene ataques de confusión MIME (ISO 27002 A.8.28)
        $respuesta->headers->set('X-Content-Type-Options', 'nosniff');

        // Previene clickjacking embebido en frames/iframes (ISO 27002 A.8.26)
        $respuesta->headers->set('X-Frame-Options', 'DENY');

        // Política estricta de referencia para evitar fuga de tokens en URL (ISO 27002 A.8.12)
        $respuesta->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Deshabilita acceso a hardware sensible del cliente (ISO 27002 A.8.28)
        $respuesta->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), vr=()');

        // Aislamiento de origen cruzado para mitigar ataques Spectre / XS-Leaks
        $respuesta->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $respuesta->headers->set('Cross-Origin-Resource-Policy', 'same-origin');

        // Remueve encabezados que expongan tecnologías del servidor (ISO 27002 A.8.12)
        $respuesta->headers->remove('X-Powered-By');
        $respuesta->headers->remove('Server');

        // Content Security Policy estricta (excluye modo de desarrollo Vite en vivo si existe)
        if (! is_file(public_path('hot'))) {
            $respuesta->headers->set('Content-Security-Policy', self::CSP);
        }

        // HSTS con subdominios y preloading (ISO 27002 A.8.20 / A.8.24)
        if ($request->isSecure() || $request->header('X-Forwarded-Proto') === 'https') {
            $respuesta->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $respuesta;
    }
}
