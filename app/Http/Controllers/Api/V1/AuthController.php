<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Auth\CredencialesInvalidas;
use App\Exceptions\Auth\DemasiadosIntentos;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginApiRequest;
use App\Http\Requests\Api\RegistroApiRequest;
use App\Models\Usuario;
use App\Repositories\Contracts\UsuarioRepositorio;
use App\Services\Auth\AutenticacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Controlador API de Autenticación y Emisión de Tokens Criptográficos (Laravel Sanctum).
 * Permite registro, inicio de sesión seguro y revocación de tokens.
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly AutenticacionService $autenticacionService,
        private readonly UsuarioRepositorio $usuarioRepositorio,
    ) {}

    /**
     * Inicio de sesión vía API REST.
     * Retorna token Bearer de Sanctum para autenticación posterior.
     */
    public function login(LoginApiRequest $request): JsonResponse
    {
        $correo = (string) $request->input('correo');
        $contrasena = (string) $request->input('contrasena');

        /** @var Usuario|null $usuario */
        $usuario = $this->usuarioRepositorio->buscarPorCorreo($correo);

        if (! $usuario || ! Hash::check($contrasena, (string) $usuario->contrasena_hash)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Credenciales inválidas. Revise su correo y contraseña.',
            ], 401);
        }

        if ($usuario->estado !== 'activo') {
            return response()->json([
                'status' => 'error',
                'message' => 'Su cuenta se encuentra inactiva o suspendida.',
            ], 403);
        }

        // Crear token criptográfico personal con Sanctum
        $token = $usuario->createToken('api_token_' . $usuario->rol->value)->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Autenticación exitosa.',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'usuario' => [
                    'id' => (string) $usuario->getKey(),
                    'nombre_completo' => $usuario->nombre_completo,
                    'correo' => $usuario->correo,
                    'rol' => $usuario->rol->value,
                    'estado' => $usuario->estado,
                ],
            ],
        ], 200);
    }

    /**
     * Registro público de consumidores vía API.
     */
    public function registro(RegistroApiRequest $request): JsonResponse
    {
        $correo = (string) $request->input('correo');

        if ($this->usuarioRepositorio->buscarPorCorreo($correo)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ya existe un usuario registrado con este correo electrónico.',
            ], 422);
        }

        $usuario = $this->autenticacionService->registrarConsumidor($request->validated());
        $token = $usuario->createToken('api_token_Consumidor')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Consumidor registrado exitosamente.',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'usuario' => [
                    'id' => (string) $usuario->getKey(),
                    'nombre_completo' => $usuario->nombre_completo,
                    'correo' => $usuario->correo,
                    'rol' => $usuario->rol->value,
                ],
            ],
        ], 201);
    }

    /**
     * Revoca el token actual del usuario autenticado.
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        // Revocar el token con el que se realizó la petición
        $usuario->currentAccessToken()?->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Sesión API cerrada y token revocado correctamente.',
        ], 200);
    }

    /**
     * Información del usuario actualmente autenticado por token.
     */
    public function perfil(Request $request): JsonResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => (string) $usuario->getKey(),
                'nombre' => $usuario->nombre,
                'apellido' => $usuario->apellido,
                'nombre_completo' => $usuario->nombre_completo,
                'correo' => $usuario->correo,
                'telefono' => $usuario->telefono,
                'rol' => $usuario->rol->value,
                'direcciones' => $usuario->direcciones ?? [],
            ],
        ], 200);
    }
}
