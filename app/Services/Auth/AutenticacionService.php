<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Exceptions\Auth\CredencialesInvalidas;
use App\Exceptions\Auth\DemasiadosIntentos;
use App\Models\Usuario;
use App\Repositories\Contracts\UsuarioRepositorio;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

final class AutenticacionService
{
    private const MAX_INTENTOS = 5;

    private const BLOQUEO_SEGUNDOS = 60;

    public function __construct(
        private readonly UsuarioRepositorio $usuarios,
        private readonly StatefulGuard $guard,
        private readonly Hasher $hasher,
    ) {}

    /**
     * @throws DemasiadosIntentos
     * @throws CredencialesInvalidas
     */
    public function iniciarSesion(string $correo, string $contrasena, string $ip): void
    {
        $llave = hash('sha256', $correo.'|'.$ip);

        if (RateLimiter::tooManyAttempts($llave, self::MAX_INTENTOS)) {
            throw new DemasiadosIntentos(RateLimiter::availableIn($llave));
        }

        // 'estado' => 'activo' impide el ingreso de cuentas desactivadas.
        $correcto = $this->guard->attempt([
            'correo' => $correo,
            'password' => $contrasena,
            'estado' => 'activo',
        ]);

        if (! $correcto) {
            RateLimiter::hit($llave, self::BLOQUEO_SEGUNDOS);
            Log::warning('auth.login_fallido', [
                'correo_hash' => hash('sha256', $correo),
                'ip' => $ip,
            ]);

            throw new CredencialesInvalidas;
        }

        RateLimiter::clear($llave);
    }

    public function cerrarSesion(): void
    {
        $this->guard->logout();
    }

    /**
     * El registro público siempre crea Consumidores; los demás roles solo los
     * asigna un Administrador.
     *
     * @param  array{nombre: string, apellido: string, correo: string, telefono: string, contrasena: string}  $datos
     */
    public function registrarConsumidor(array $datos): Usuario
    {
        $contrasena = $datos['contrasena'];
        unset($datos['contrasena']);

        $usuario = $this->usuarios->crearConsumidor($datos, $this->hasher->make($contrasena));

        Log::info('auth.registro', ['usuario_id' => (string) $usuario->getKey()]);

        return $usuario;
    }
}

